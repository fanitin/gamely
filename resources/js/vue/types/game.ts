export type GameStatus = "PLAYING" | "WON" | "LOST";

export type CellStatus = "correct" | "partial" | "wrong";

export interface GameCell {
    type: "text" | "year" | "tags";
    status: CellStatus;
    value: string | number | TagItem[];
    meta?: {
        direction?: "up" | "down" | "none";
    };
}

export interface TagItem {
    label: string;
    matched: boolean;
}

export interface GameAttempt {
    id?: number;
    is_correct: boolean;
    cells: GameCell[];
}

export interface GameState {
    attempts: GameAttempt[];
    status: GameStatus;
    attemptsCount: number;
    isLoading: boolean;
    error: string | null;
}
