<?php

namespace App\Console\Commands;

use App\Enums\PopularityTier;
use App\Models\Character;
use App\Models\Game;
use App\Services\CharacterCsvService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;

#[Signature('characters:export
    {dir? : Output directory (default: storage/app/exports/characters-<timestamp>)}
    {--tier=* : Only games of these popularity tiers (legendary, popular, notable, niche)}
    {--limit= : Max number of games to export}
    {--offset= : Skip this many games (for slicing into batches)}')]
#[Description('Export characters.csv, games.csv and PROMPT.md so an AI can enrich characters and add new ones')]
class ExportCharactersCsv extends Command
{
    public const GAME_COLUMNS = [
        'igdb_id', 'name', 'release_year', 'popularity_tier', 'genres', 'franchises', 'developers', 'characters_count',
    ];

    public function __construct(private CharacterCsvService $csv)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $tiers = $this->resolveTiers();

        if ($tiers === null) {
            return self::FAILURE;
        }

        $dir = rtrim($this->argument('dir') ?: storage_path('app/exports/characters-'.now()->format('Ymd-His')), '/');

        if (! is_dir($dir) && ! mkdir($dir, 0775, true)) {
            $this->error("Cannot create directory {$dir}");

            return self::FAILURE;
        }

        $gamesQuery = $this->gamesQuery($tiers);
        $gameIds = $gamesQuery->pluck('games.id');

        $gamesCount = $this->writeGames($dir.'/games.csv', $gamesQuery);
        $charactersCount = $this->writeCharacters($dir.'/characters.csv', $gameIds->all());
        file_put_contents($dir.'/PROMPT.md', $this->buildPrompt());

        $this->info("Exported {$gamesCount} games and {$charactersCount} characters to {$dir}");
        $this->line('Files: games.csv, characters.csv, PROMPT.md');

        return self::SUCCESS;
    }

    private function resolveTiers(): ?array
    {
        $tiers = [];

        foreach ((array) $this->option('tier') as $tier) {
            $resolved = PopularityTier::tryFrom(mb_strtolower(trim($tier)));

            if (! $resolved) {
                $this->error("Unknown tier '{$tier}'. Allowed: ".implode(', ', array_map(fn ($t) => $t->value, PopularityTier::cases())));

                return null;
            }

            $tiers[] = $resolved->value;
        }

        return $tiers;
    }

    private function gamesQuery(array $tiers): Builder
    {
        $query = Game::query()
            ->with(['genres', 'franchises', 'developers'])
            ->withCount('characters')
            ->orderByDesc('rating_count')
            ->orderBy('id');

        if ($tiers !== []) {
            $query->whereIn('popularity_tier', $tiers);
        }

        if ($this->option('offset')) {
            $query->skip((int) $this->option('offset'));
        }

        if ($this->option('limit')) {
            $query->take((int) $this->option('limit'));
        } elseif ($this->option('offset')) {
            $query->take(PHP_INT_MAX);
        }

        return $query;
    }

    private function writeGames(string $path, Builder $query): int
    {
        $handle = fopen($path, 'w');
        fputcsv($handle, self::GAME_COLUMNS);
        $count = 0;

        foreach ($query->cursor() as $game) {
            fputcsv($handle, [
                $game->igdb_id,
                $game->name,
                $game->release_date?->year,
                $game->popularity_tier?->value,
                $game->genres->pluck('name')->implode(CharacterCsvService::LIST_SEPARATOR),
                $game->franchises->pluck('name')->implode(CharacterCsvService::LIST_SEPARATOR),
                $game->developers->pluck('name')->implode(CharacterCsvService::LIST_SEPARATOR),
                $game->characters_count,
            ]);
            $count++;
        }

        fclose($handle);

        return $count;
    }

    private function writeCharacters(string $path, array $gameIds): int
    {
        $handle = fopen($path, 'w');
        fputcsv($handle, $this->csv->headers());
        $count = 0;

        Character::query()
            ->with(['games', 'gender', 'species'])
            ->whereHas('games', fn (Builder $q) => $q->whereIn('games.id', $gameIds))
            ->orderBy('id')
            ->chunk(500, function ($characters) use ($handle, &$count) {
                foreach ($characters as $character) {
                    fputcsv($handle, array_values($this->csv->toRow($character)));
                    $count++;
                }
            });

        fclose($handle);

        return $count;
    }

    private function buildPrompt(): string
    {
        $separator = CharacterCsvService::LIST_SEPARATOR;
        $lines = [
            '# Task: enrich and extend the video game character dataset',
            '',
            'You are given two CSV files:',
            '',
            '- `games.csv` — the games in our database. Column `characters_count` shows how many characters are already linked to each game.',
            '- `characters.csv` — the characters we already have (may be empty or sparse). Many cells are empty.',
            '',
            'Only include real, notable characters: protagonists, antagonists, companions, bosses, recurring cast. Never include voice actors, credits entries, unnamed or numbered NPCs (e.g. "Guard 2", "Male Pedestrian"), radio hosts or background characters.',
            '',
            '## What to do',
            '',
            '1. Fill in the empty cells of existing rows in `characters.csv` using well-known, verifiable facts (official wikis, Fandom, Wikipedia, Wikidata).',
            '2. Add new rows for the most notable characters of the games in `games.csv`, prioritising games with a low `characters_count`. Aim for the protagonist, main antagonist and key supporting cast (roughly 3-10 per game, more for big franchises).',
            '3. Return a single CSV with exactly the same header as `characters.csv`, containing the updated existing rows and the new rows. Do not drop columns, do not reorder them, do not add new ones.',
            '',
            '## Rules',
            '',
            '- `games`: list of `igdb_id` values from `games.csv` separated by `'.$separator.'` (e.g. `1942'.$separator.'11185`). Every row must reference at least one game. Only use ids that exist in `games.csv`.',
            '- `slug`: lowercase, ASCII, words joined by `-`, unique across the whole file. Keep the existing slug for existing rows. For new characters use the character name, add the franchise if the name is ambiguous (e.g. `link-zelda`, `jack-bioshock`).',
            '- `id` and `igdb_id`: keep the exported values for existing rows, leave empty for new rows.',
            '- Lists (`akas`, `abilities`, `games`): values separated by `'.$separator.'` with no spaces around it.',
            '- Booleans (`is_playable`, `is_silent`, `is_mascot`): `1` or `0`. `is_silent` means a silent protagonist with no dialogue. `is_mascot` means the character is the mascot of the developer or franchise.',
            '- Numbers (`age`, `height_cm`, `weight_kg`, `debut_year`): integers only. `debut_year` is the release year of the first game the character appeared in. `age` is the canonical age in the first appearance when known.',
            '- `birthday`: free text such as `March 10` or `1985-03-10`.',
            '- Controlled vocabularies below: use exactly one value from the list, lowercase. If nothing fits well use `other`; if unknown leave the cell empty. Never invent facts.',
            '- Free text columns (`occupation`, `affiliation`, `origin`, `signature_item`, `voice_actor`, `creator`, `catchphrase`, `full_name`, `description`): short English text. `occupation` is a 1-3 word job or class (e.g. `bounty hunter`, `plumber`, `witcher`). `affiliation` is the main organisation or faction. `origin` is the homeland, city or country, fictional or real. `voice_actor` is the English voice actor of the first appearance.',
            '- `description`: 1-2 sentences, no spoilers about the character identity that would make the guessing game trivial.',
            '- `image_url`: a direct link to an official or wiki image of the character, if available.',
            '- `source_url`: the wiki page the facts were taken from.',
            '- Empty cells are allowed everywhere except `slug`, `name`, `games`.',
            '- Output valid CSV: quote values containing commas, quotes or line breaks.',
            '',
            '## Controlled vocabularies',
            '',
        ];

        foreach ($this->csv->vocabularies() as $column => $values) {
            $lines[] = '- `'.$column.'`: '.implode(', ', $values);
        }

        $lines[] = '';
        $lines[] = '## Column reference';
        $lines[] = '';
        $lines[] = '`'.implode('`, `', $this->csv->headers()).'`';
        $lines[] = '';

        return implode(PHP_EOL, $lines);
    }
}
