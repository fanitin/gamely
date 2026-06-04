import type { ModeValue } from "@/vue/composables/useStats";

const defaultComparisonOrder = [
    "genres",
    "developers_publishers",
    "franchises_collections",
    "player_perspectives",
    "game_modes",
    "release_year",
    "popularity",
] as const;

const modeComparisonKeys: Partial<Record<ModeValue, readonly string[]>> = {
    classic: defaultComparisonOrder,
    character: [
        "franchises",
        "collections",
        "gender",
        "species",
        "first_appearance_year",
    ],
    game_screenshots: [
        "release_year",
        "franchises_collections",
        "developers_publishers",
        "popularity",
        "franchise_start_year",
    ],
};

export const getComparisonKeysForMode = (
    mode: ModeValue,
    availableKeys: string[],
): string[] => {
    const preferred = modeComparisonKeys[mode];

    if (preferred && preferred.length > 0) {
        const filtered = preferred.filter((key) => availableKeys.includes(key));
        if (filtered.length > 0) {
            return filtered;
        }
    }

    return availableKeys;
};
