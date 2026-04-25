import { ref, computed } from "vue";
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

export function useClassicGame() {
    const attempts = ref<Attempt[]>([]);
    const isWon = ref(false);
    const isLoading = ref(false);
    const error = ref<string | null>(null);
    const guessedGameIds = ref<number[]>([]);

    const todayKey = new Date().toISOString().split("T")[0];
    const completedToday = useLocalStorage(`classic_completed_${todayKey}`, false);

    const canGuess = computed(() => !isWon.value && !completedToday.value && !isLoading.value);

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
                isWon.value = true;
                completedToday.value = true;

                const stats = useLocalStorage("classic_stats", {
                    total: 0,
                    wins: 0,
                    distribution: {} as Record<number, number>,
                });

                stats.value.total++;
                stats.value.wins++;
                const attemptsCount = attempts.value.length + 1;
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

    const reset = () => {
        attempts.value = [];
        isWon.value = false;
        error.value = null;
        completedToday.value = false;
    };

    return {
        attempts,
        isWon,
        isLoading,
        error,
        canGuess,
        guessedGameIds,
        makeGuess,
        reset,
    };
}
