<?php

namespace Tests\Unit\Enums;

use App\Enums\CharacterAlignment;
use App\Enums\CharacterSetting;
use PHPUnit\Framework\TestCase;

class HasValuesTest extends TestCase
{
    public function test_try_from_input_normalizes_case_spaces_and_hyphens(): void
    {
        $this->assertSame(CharacterSetting::SCI_FI, CharacterSetting::tryFromInput('Sci-Fi'));
        $this->assertSame(CharacterSetting::SCI_FI, CharacterSetting::tryFromInput('  sci fi '));
        $this->assertSame(CharacterAlignment::ANTI_HERO, CharacterAlignment::tryFromInput('Anti Hero'));
        $this->assertSame(CharacterAlignment::HERO, CharacterAlignment::tryFromInput('hero'));
    }

    public function test_try_from_input_returns_null_for_unknown_or_empty(): void
    {
        $this->assertNull(CharacterSetting::tryFromInput('steampunk'));
        $this->assertNull(CharacterSetting::tryFromInput(''));
        $this->assertNull(CharacterSetting::tryFromInput('   '));
        $this->assertNull(CharacterSetting::tryFromInput(null));
    }

    public function test_values_lists_all_cases(): void
    {
        $this->assertSame(['hero', 'anti_hero', 'neutral', 'villain'], CharacterAlignment::values());
    }
}
