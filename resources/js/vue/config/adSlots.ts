export const adSlots = {
    home: "0000000000",
    game_top: "0000000000",
    game_bottom: "0000000000",
    tower_left: "0000000000",
    tower_right: "0000000000",
} as const;

export type AdSlotName = keyof typeof adSlots;
