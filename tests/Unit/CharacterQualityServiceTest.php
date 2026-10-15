<?php

namespace Tests\Unit;

use App\Enums\PopularityTier;
use App\Models\Game;
use App\Services\CharacterQualityService;
use PHPUnit\Framework\TestCase;

class CharacterQualityServiceTest extends TestCase
{
    private CharacterQualityService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new CharacterQualityService();
    }

    public function test_detects_credits_entries(): void
    {
        foreach (['Additional Voices', 'Voice Talent', 'Narrator', 'Multiplayer Announcer (Voice)', 'French Cast', 'German Actor', 'Liberty City Hardcore Vocals', 'Praying Mantis - Stunts', 'Extras'] as $name) {
            $this->assertSame('credits', $this->service->junkReason($name), $name);
        }
    }

    public function test_detects_indexed_npcs(): void
    {
        foreach (['Male Pedestrian 14', 'Masked Guard #4', 'Civilian Female 09', 'Hindi Thug 02', 'Pirate 2', 'Thrashball Fan #2'] as $name) {
            $this->assertSame('indexed', $this->service->junkReason($name), $name);
        }

        $this->assertSame('generic', $this->service->junkReason('Villager B'));
    }

    public function test_detects_generic_roles(): void
    {
        foreach (['Barkeep', 'Dispatcher', 'Outlaw Leader', 'Crowd', 'Various', 'Unknown', 'Player Character', 'Male Pedestrian', 'Russian Soldier', 'Generic Woman', 'Man', 'NPC'] as $name) {
            $this->assertSame('generic', $this->service->junkReason($name), $name);
        }
    }

    public function test_detects_empty_or_symbol_names(): void
    {
        foreach (['', ' ', '-', '#', '1'] as $name) {
            $this->assertSame('empty', $this->service->junkReason($name), json_encode($name));
        }
    }

    public function test_keeps_real_characters(): void
    {
        foreach (['Geralt of Rivia', 'Sniper Wolf', 'Master Chief', 'Big Boss', 'Solid Snake', 'Mario', 'Ed', 'Sayaka Maizono', 'Itinerant Merchant Woman', 'Even-Toned Female', 'Larry the Lobster', 'Captain Hector Barbossa', 'Android No. 16', 'Teletraan 1', 'Cécile Cosima Caminades', 'The Stranger', 'Sam B', 'Kasparov (21st Street Crew)'] as $name) {
            $this->assertNull($this->service->junkReason($name), $name);
        }
    }

    public function test_has_popular_game_checks_tiers(): void
    {
        $legendary = new Game(['popularity_tier' => PopularityTier::LEGENDARY]);
        $niche = new Game(['popularity_tier' => PopularityTier::NICHE]);
        $notable = new Game(['popularity_tier' => PopularityTier::NOTABLE]);

        $this->assertTrue($this->service->hasPopularGame([$niche, $legendary]));
        $this->assertFalse($this->service->hasPopularGame([$niche, $notable]));
        $this->assertTrue($this->service->hasPopularGame([$notable], [PopularityTier::NOTABLE]));
        $this->assertFalse($this->service->hasPopularGame([]));
    }

    public function test_resolve_tiers(): void
    {
        $this->assertSame(PopularityTier::top(), $this->service->resolveTiers([]));
        $this->assertSame([PopularityTier::NOTABLE, PopularityTier::NICHE], $this->service->resolveTiers(['Notable', ' niche ']));
        $this->assertNull($this->service->resolveTiers(['epic']));
    }
}
