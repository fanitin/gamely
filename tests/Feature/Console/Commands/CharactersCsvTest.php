<?php

namespace Tests\Feature\Console\Commands;

use App\Enums\CharacterHairColor;
use App\Enums\CharacterRole;
use App\Enums\CharacterSetting;
use App\Models\Character;
use App\Models\CharacterGender;
use App\Models\CharacterSpecies;
use App\Models\Game;
use App\Services\CharacterCsvService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CharactersCsvTest extends TestCase
{
    use RefreshDatabase;

    private string $dir;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dir = sys_get_temp_dir().'/gamely-csv-'.uniqid();
        mkdir($this->dir, 0775, true);
    }

    protected function tearDown(): void
    {
        array_map('unlink', glob($this->dir.'/*'));
        rmdir($this->dir);

        parent::tearDown();
    }

    public function test_import_creates_updates_and_links_characters(): void
    {
        $witcher = Game::create(['igdb_id' => 1942, 'name' => 'The Witcher 3', 'slug' => 'the-witcher-3', 'release_date' => '2015-05-19']);
        $witcher2 = Game::create(['igdb_id' => 1943, 'name' => 'The Witcher 2', 'slug' => 'the-witcher-2', 'release_date' => '2011-05-17']);
        CharacterGender::create(['igdb_id' => 1, 'name' => 'Male']);

        $existing = Character::create(['igdb_id' => 500, 'name' => 'Geralt', 'slug' => 'geralt-of-rivia']);
        $existing->games()->attach($witcher->id);

        $csv = implode("\n", [
            'slug,name,full_name,akas,games,gender,species,role,alignment,hair_color,setting,age,is_playable,abilities,occupation,unknown_col',
            'geralt-of-rivia,Geralt of Rivia,Geralt of Rivia,White Wolf|Butcher of Blaviken,1942|1943,male,human,protagonist,anti_hero,white,Fantasy,100,1,swordsmanship|signs,witcher,ignored',
            'yennefer,Yennefer of Vengerberg,,,1942,female,human,supporting,neutral,black,fantasy,,0,magic,sorceress,',
            'ghost,Nobody,,,99999,male,human,protagonist,hero,red,fantasy,,1,,,',
            'bad-values,Bad Values,,,1942,martian,human,sidekick,hero,rainbow,fantasy,old,maybe,,,',
        ]);
        file_put_contents($this->dir.'/in.csv', $csv);

        $this->artisan('characters:import', ['file' => $this->dir.'/in.csv'])
            ->expectsOutputToContain('Created: 2 | Updated: 1 | Unchanged: 0 | Skipped: 1')
            ->assertExitCode(0);

        $geralt = Character::where('slug', 'geralt-of-rivia')->first();
        $this->assertSame(500, $geralt->igdb_id);
        $this->assertSame('Geralt of Rivia', $geralt->name);
        $this->assertSame(['White Wolf', 'Butcher of Blaviken'], $geralt->akas);
        $this->assertSame(CharacterRole::PROTAGONIST, $geralt->role);
        $this->assertSame(CharacterHairColor::WHITE, $geralt->hair_color);
        $this->assertSame(CharacterSetting::FANTASY, $geralt->setting);
        $this->assertSame(100, $geralt->age);
        $this->assertTrue($geralt->is_playable);
        $this->assertSame(['swordsmanship', 'signs'], $geralt->abilities);
        $this->assertSame('witcher', $geralt->occupation);
        $this->assertSame('Male', $geralt->gender->name);
        $this->assertSame('Human', $geralt->species->name);
        $this->assertEqualsCanonicalizing([$witcher->id, $witcher2->id], $geralt->games->pluck('id')->all());

        $yennefer = Character::where('slug', 'yennefer')->first();
        $this->assertNull($yennefer->igdb_id);
        $this->assertSame('Female', $yennefer->gender->name);
        $this->assertFalse($yennefer->is_playable);
        $this->assertSame(1, CharacterSpecies::count());
        $this->assertSame(2, CharacterGender::count());

        $this->assertDatabaseMissing('characters', ['slug' => 'ghost']);

        $bad = Character::where('slug', 'bad-values')->first();
        $this->assertNull($bad->gender_id);
        $this->assertNull($bad->role);
        $this->assertNull($bad->hair_color);
        $this->assertNull($bad->age);
        $this->assertNull($bad->is_playable);
        $this->assertSame('Human', $bad->species->name);
    }

    public function test_import_dry_run_writes_nothing(): void
    {
        Game::create(['igdb_id' => 1942, 'name' => 'The Witcher 3', 'slug' => 'the-witcher-3']);
        file_put_contents($this->dir.'/in.csv', "slug,name,games\nciri,Ciri,1942\n");

        $this->artisan('characters:import', ['file' => $this->dir.'/in.csv', '--dry-run' => true])
            ->expectsOutputToContain('[DRY RUN] Created: 1')
            ->assertExitCode(0);

        $this->assertSame(0, Character::count());
    }

    public function test_import_fails_without_required_columns(): void
    {
        file_put_contents($this->dir.'/in.csv', "name,role\nCiri,protagonist\n");

        $this->artisan('characters:import', ['file' => $this->dir.'/in.csv'])
            ->expectsOutputToContain('Missing required columns: slug, games')
            ->assertExitCode(1);
    }

    public function test_export_writes_games_characters_and_prompt(): void
    {
        $game = Game::create(['igdb_id' => 1942, 'name' => 'The Witcher 3', 'slug' => 'the-witcher-3', 'release_date' => '2015-05-19', 'popularity_tier' => 'legendary', 'rating_count' => 5000]);
        $niche = Game::create(['igdb_id' => 7, 'name' => 'Tiny Game', 'slug' => 'tiny-game', 'popularity_tier' => 'niche', 'rating_count' => 5]);
        $gender = CharacterGender::create(['igdb_id' => 1, 'name' => 'Male']);

        $geralt = Character::create([
            'igdb_id' => 500, 'name' => 'Geralt', 'slug' => 'geralt-of-rivia', 'gender_id' => $gender->id,
            'role' => CharacterRole::PROTAGONIST, 'hair_color' => CharacterHairColor::WHITE,
            'akas' => ['White Wolf'], 'is_playable' => true, 'age' => 100,
        ]);
        $geralt->games()->attach($game->id);

        $tiny = Character::create(['igdb_id' => 501, 'name' => 'Tiny Hero', 'slug' => 'tiny-hero']);
        $tiny->games()->attach($niche->id);

        $this->artisan('characters:export', ['dir' => $this->dir, '--tier' => ['legendary']])
            ->expectsOutputToContain('Exported 1 games and 1 characters')
            ->assertExitCode(0);

        $games = array_map('str_getcsv', file($this->dir.'/games.csv', FILE_IGNORE_NEW_LINES));
        $this->assertSame(['igdb_id', 'name', 'release_year', 'popularity_tier', 'genres', 'franchises', 'developers', 'characters_count'], $games[0]);
        $this->assertSame(['1942', 'The Witcher 3', '2015', 'legendary', '', '', '', '1'], $games[1]);

        $characters = array_map('str_getcsv', file($this->dir.'/characters.csv', FILE_IGNORE_NEW_LINES));
        $this->assertSame(app(CharacterCsvService::class)->headers(), $characters[0]);
        $row = array_combine($characters[0], $characters[1]);
        $this->assertSame('500', $row['igdb_id']);
        $this->assertSame('geralt-of-rivia', $row['slug']);
        $this->assertSame('1942', $row['games']);
        $this->assertSame('male', $row['gender']);
        $this->assertSame('protagonist', $row['role']);
        $this->assertSame('white', $row['hair_color']);
        $this->assertSame('White Wolf', $row['akas']);
        $this->assertSame('1', $row['is_playable']);
        $this->assertSame('100', $row['age']);
        $this->assertSame('', $row['is_silent']);
        $this->assertCount(2, $characters);

        $prompt = file_get_contents($this->dir.'/PROMPT.md');
        $this->assertStringContainsString('`role`: protagonist, deuteragonist, antagonist, supporting, boss, minor', $prompt);
        $this->assertStringContainsString('`species`: human, elf', $prompt);
    }

    public function test_export_round_trips_through_import(): void
    {
        $game = Game::create(['igdb_id' => 1942, 'name' => 'The Witcher 3', 'slug' => 'the-witcher-3']);
        $geralt = Character::create(['igdb_id' => 500, 'name' => 'Geralt', 'slug' => 'geralt-of-rivia', 'role' => CharacterRole::PROTAGONIST, 'abilities' => ['signs']]);
        $geralt->games()->attach($game->id);

        $this->artisan('characters:export', ['dir' => $this->dir])->assertExitCode(0);
        $this->artisan('characters:import', ['file' => $this->dir.'/characters.csv'])
            ->expectsOutputToContain('Created: 0 | Updated: 0 | Unchanged: 1')
            ->assertExitCode(0);
    }
}
