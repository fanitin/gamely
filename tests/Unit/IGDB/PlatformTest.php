<?php

namespace Tests\Unit\IGDB;

use App\Models\Platform;
use App\Models\PlatformFamily;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlatformTest extends TestCase
{
    use RefreshDatabase;

    public function test_platform_belongs_to_family(): void
    {
        $family = PlatformFamily::create([
            'name' => 'PlayStation',
            'igdb_id' => 165,
            'slug' => 'playstation',
        ]);

        $platform = Platform::create([
            'name' => 'PlayStation 4',
            'igdb_id' => 48,
            'slug' => 'ps4',
            'platform_family_id' => $family->id,
        ]);

        $this->assertEquals($family->id, $platform->platformFamily->id);
        $this->assertInstanceOf(PlatformFamily::class, $platform->platformFamily);
    }
    
    public function test_platform_family_has_many_platforms(): void
    {
        $family = PlatformFamily::create([
            'name' => 'Xbox',
            'igdb_id' => 100,
            'slug' => 'xbox',
        ]);

        Platform::create([
            'name' => 'Xbox One',
            'igdb_id' => 49,
            'slug' => 'xbox-one',
            'platform_family_id' => $family->id,
        ]);

        $this->assertCount(1, $family->platforms);
        $this->assertInstanceOf(Platform::class, $family->platforms->first());
    }
}
