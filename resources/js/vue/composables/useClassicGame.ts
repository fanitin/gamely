import { ref, computed, watch } from "vue";
import axios from "axios";
import { route } from "ziggy-js";
import { useLocalStorage } from "@vueuse/core";

interface GuessedGame {
    id: number;
    name: string;
    display_name: string;
    cover_url: string;
    release_year: number | null;
    rating: number | null;
    genres: Array<{ id: number; name: string }>;
    platforms: Array<{ id: number; name: string; url?: string }>;
    developers: Array<{ id: number; name: string }>;
    publishers: Array<{ id: number; name: string }>;
}

interface ComparisonResult {
    result: "exact" | "close" | "wrong";
    value?: any;
    arrow?: "up" | "down";
}

interface Attempt {
    guessed: GuessedGame;
    comparison: {
        release_year: ComparisonResult;
        rating: ComparisonResult;
        genres: ComparisonResult;
        platforms: ComparisonResult;
        developers: ComparisonResult;
        publishers: ComparisonResult;
    };
}

export interface Hint {
    type: string;
    value: string | number | null;
    unlockAt: number;
}

interface GameState {
    attempts: Attempt[];
    guessedGameIds: number[];
    isWon: boolean;
    lastCorrectGuess: GuessedGame | null;
}

const getDefaultState = (): GameState => ({
    attempts: [],
    guessedGameIds: [],
    isWon: false,
    lastCorrectGuess: null,
});

const cleanupOldEntries = () => {
    const now = new Date();
    const maxAge = 7;

    for (let i = localStorage.length - 1; i >= 0; i--) {
        const key = localStorage.key(i);
        if (!key || !key.startsWith('classic_game_')) continue;

        const dateStr = key.replace('classic_game_', '');
        const entryDate = new Date(dateStr);

        if (!isNaN(entryDate.getTime())) {
            const diffDays = Math.floor((now.getTime() - entryDate.getTime()) / (1000 * 60 * 60 * 24));
            if (diffDays > maxAge) {
                localStorage.removeItem(key);
            }
        }
    }
};

export function useClassicGame() {
    const todayKey = new Date().toISOString().split("T")[0];

    cleanupOldEntries();

    const gameState = useLocalStorage<GameState>(
        `classic_game_${todayKey}`,
        getDefaultState()
    );

    const attempts = ref<Attempt[]>(gameState.value.attempts);
    const isWon = ref(gameState.value.isWon);
    const isLoading = ref(false);
    const isLoadingChallenge = ref(true);
    const error = ref<string | null>(null);
    const guessedGameIds = ref<number[]>(gameState.value.guessedGameIds);
    const lastCorrectGuess = ref<GuessedGame | null>(gameState.value.lastCorrectGuess);
    const hints = ref<Hint[]>([]);
    const challengeId = ref<number | null>(null);

    const completedToday = useLocalStorage(`classic_completed_${todayKey}`, gameState.value.isWon);

    const saveState = () => {
        gameState.value = {
            attempts: attempts.value,
            guessedGameIds: guessedGameIds.value,
            isWon: isWon.value,
            lastCorrectGuess: lastCorrectGuess.value,
        };
    };

    watch([attempts, guessedGameIds, isWon, lastCorrectGuess], saveState, { deep: true });

    const canGuess = computed(() => !isWon.value && !completedToday.value && !isLoading.value && !isLoadingChallenge.value);

    const attemptsCount = computed(() => attempts.value.length);

    const loadChallenge = async () => {
        isLoadingChallenge.value = true;
        error.value = null;

        try {
            const response = await axios.get(route("api.challenge.classic"));
            if (response.data.success) {
                challengeId.value = response.data.challenge_id;

                if (response.data.hints) {
                    hints.value = Object.entries(response.data.hints).map(([type, hintData]: [string, any]) => ({
                        type,
                        value: hintData.value,
                        unlockAt: hintData.unlock_at,
                    }));
                }
            } else {
                error.value = response.data.error || "Failed to load challenge";
            }
        } catch (err: any) {
            console.error("Failed to load challenge:", err);
            error.value = err.response?.data?.error || "Network error";
        } finally {
            isLoadingChallenge.value = false;
        }
    };

    loadChallenge();

    const makeGuess = async (gameId: number) => {
        if (!canGuess.value) return;

        isLoading.value = true;
        error.value = null;

        try {
            const response = await axios.post(route("api.guess"), {
                entity_id: gameId,
                mode: "classic",
            });

            const data = response.data;

            if (!data.success) {
                error.value = data.error;
                return;
            }


            if (data.is_correct) {
                attempts.value.push({
                    guessed: data.comparison.guessed,
                    comparison: data.comparison.comparison,
                });
                guessedGameIds.value.push(gameId);
                lastCorrectGuess.value = data.comparison.guessed;

                isWon.value = true;
                completedToday.value = true;

                const stats = useLocalStorage("classic_stats", {
                    total: 0,
                    wins: 0,
                    distribution: {} as Record<number, number>,
                });

                stats.value.total++;
                stats.value.wins++;
                const attemptsCount = attempts.value.length;
                stats.value.distribution[attemptsCount] = (stats.value.distribution[attemptsCount] || 0) + 1;
            } else {
                attempts.value.push({
                    guessed: data.comparison.guessed,
                    comparison: data.comparison.comparison,
                });

                guessedGameIds.value.push(gameId);
            }
        } catch (err: any) {
            console.error("Guess failed:", err);
            error.value = err.response?.data?.error || "Network error";
        } finally {
            isLoading.value = false;
        }
    };

    return {
        attempts,
        isWon,
        isLoading,
        isLoadingChallenge,
        error,
        canGuess,
        guessedGameIds,
        lastCorrectGuess,
        hints,
        attemptsCount,
        makeGuess,
    };
}
