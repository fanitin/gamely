import { ref, computed, watch } from "vue";
import axios from "axios";
import { route } from "ziggy-js";
import { useLocalStorage } from "@vueuse/core";

interface Screenshot {
    id: number;
    url: string;
}

interface ComparisonResult {
    result: "exact" | "close" | "wrong";
    value?: any;
    arrow?: "up" | "down";
}

interface GuessedGame {
    id: number;
    name: string;
    display_name: string;
    cover_url: string;
    release_year: number | null;
    rating: number | null;
    genres: Array<{ id: number; name: string }>;
    developers: Array<{ id: number; name: string }>;
    publishers: Array<{ id: number; name: string }>;
    franchises: Array<{ id: number; name: string }>;
    collections: Array<{ id: number; name: string }>;
    game_modes: Array<{ id: number; name: string }>;
    player_perspectives: Array<{ id: number; name: string }>;
}

interface Attempt {
    gameId: number;
    guessed: GuessedGame;
    comparison: {
        release_year: ComparisonResult;
        rating: ComparisonResult;
        genres: ComparisonResult;
        developers_publishers: ComparisonResult;
        franchises_collections: ComparisonResult;
        game_modes: ComparisonResult;
        player_perspectives: ComparisonResult;
    };
    isCorrect: boolean;
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
    visibleScreenshotsCount: number;
}

const getDefaultState = (): GameState => ({
    attempts: [],
    guessedGameIds: [],
    isWon: false,
    lastCorrectGuess: null,
    visibleScreenshotsCount: 1,
});

const cleanupOldEntries = () => {
    const now = new Date();
    const maxAge = 7;

    for (let i = localStorage.length - 1; i >= 0; i--) {
        const key = localStorage.key(i);
        if (!key || !key.startsWith("screenshots_")) continue;

        const dateStr = key.replace("screenshots_", "").replace("_completed", "");
        const entryDate = new Date(dateStr);

        if (!isNaN(entryDate.getTime())) {
            const diffDays = Math.floor(
                (now.getTime() - entryDate.getTime()) / (1000 * 60 * 60 * 24)
            );
            if (diffDays > maxAge) {
                localStorage.removeItem(key);
            }
        }
    }
};

export function useScreenshotsGame() {
    const todayKey = new Date().toISOString().split("T")[0];
    cleanupOldEntries();

    const gameState = useLocalStorage<GameState>(
        `screenshots_${todayKey}`,
        getDefaultState()
    );

    const attempts = ref<Attempt[]>(gameState.value.attempts || []);
    const isWon = ref(gameState.value.isWon || false);
    const isLoading = ref(false);
    const isLoadingChallenge = ref(true);
    const error = ref<string | null>(null);
    const guessedGameIds = ref<number[]>(gameState.value.guessedGameIds || []);
    const lastCorrectGuess = ref<GuessedGame | null>(gameState.value.lastCorrectGuess || null);
    const visibleScreenshotsCount = ref(gameState.value.visibleScreenshotsCount || 1);

    const screenshots = ref<Screenshot[]>([]);
    const totalScreenshots = ref(0);
    const challengeId = ref<number | null>(null);
    const hints = ref<Hint[]>([]);

    const completedToday = useLocalStorage(
        `screenshots_completed_${todayKey}`,
        gameState.value.isWon
    );

    const saveState = () => {
        gameState.value = {
            attempts: attempts.value,
            guessedGameIds: guessedGameIds.value,
            isWon: isWon.value,
            lastCorrectGuess: lastCorrectGuess.value,
            visibleScreenshotsCount: visibleScreenshotsCount.value,
        };
    };

    watch(
        [attempts, guessedGameIds, isWon, lastCorrectGuess, visibleScreenshotsCount],
        saveState,
        { deep: true }
    );

    const canGuess = computed(
        () => !isWon.value && !completedToday.value && !isLoading.value && !isLoadingChallenge.value
    );

    const visibleScreenshots = computed(() => {
        return screenshots.value.slice(0, visibleScreenshotsCount.value);
    });

    const currentScreenshotIndex = ref(0);

    const attemptsCount = computed(() => attempts.value.length);

    const loadChallenge = async () => {
        isLoadingChallenge.value = true;
        error.value = null;

        try {
            const response = await axios.get(route("api.challenge.screenshots"));
            if (response.data.success) {
                screenshots.value = response.data.screenshots;
                totalScreenshots.value = response.data.total_screenshots;
                challengeId.value = response.data.challenge_id;

                if (response.data.hints) {
                    hints.value = Object.entries(response.data.hints).map(([type, data]: [string, any]) => ({
                        type,
                        value: data.value,
                        unlockAt: data.unlock_at,
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

    const revealNextScreenshot = () => {
        if (visibleScreenshotsCount.value < totalScreenshots.value) {
            visibleScreenshotsCount.value++;
            currentScreenshotIndex.value = visibleScreenshotsCount.value - 1;
        }
    };

    const makeGuess = async (gameId: number) => {
        if (!canGuess.value) return;

        isLoading.value = true;
        error.value = null;

        try {
            const response = await axios.post(route("api.guess"), {
                entity_id: gameId,
                mode: "game_screenshots",
            });

            const data = response.data;

            if (!data.success) {
                error.value = data.error;
                return;
            }

            if (data.is_correct) {
                attempts.value.push({
                    gameId,
                    guessed: data.comparison.guessed,
                    comparison: data.comparison.comparison,
                    isCorrect: true,
                });
                guessedGameIds.value.push(gameId);
                lastCorrectGuess.value = data.comparison.guessed;
                isWon.value = true;
                completedToday.value = true;

                const stats = useLocalStorage("screenshots_stats", {
                    total: 0,
                    wins: 0,
                    distribution: {} as Record<number, number>,
                });
                stats.value.total++;
                stats.value.wins++;
                const totalAttempts = attempts.value.length;
                stats.value.distribution[totalAttempts] =
                    (stats.value.distribution[totalAttempts] || 0) + 1;
            } else {
                attempts.value.push({
                    gameId,
                    guessed: data.comparison.guessed,
                    comparison: data.comparison.comparison,
                    isCorrect: false,
                });
                guessedGameIds.value.push(gameId);

                const wrongAttempts = attempts.value.filter((a) => !a.isCorrect).length;
                if (wrongAttempts % 2 === 0 && visibleScreenshotsCount.value < totalScreenshots.value) {
                    revealNextScreenshot();
                }
            }
        } catch (err: any) {
            console.error("Guess failed:", err);
            error.value = err.response?.data?.error || "Network error";
        } finally {
            isLoading.value = false;
        }
    };

    const goToScreenshot = (index: number) => {
        if (index >= 0 && index < visibleScreenshotsCount.value) {
            currentScreenshotIndex.value = index;
        }
    };

    const nextScreenshot = () => {
        if (currentScreenshotIndex.value < visibleScreenshotsCount.value - 1) {
            currentScreenshotIndex.value++;
        }
    };

    const prevScreenshot = () => {
        if (currentScreenshotIndex.value > 0) {
            currentScreenshotIndex.value--;
        }
    };

    loadChallenge();

    return {
        attempts,
        attemptsCount,
        isWon,
        isLoading,
        isLoadingChallenge,
        error,
        canGuess,
        guessedGameIds,
        lastCorrectGuess,
        screenshots,
        visibleScreenshots,
        visibleScreenshotsCount,
        totalScreenshots,
        currentScreenshotIndex,
        hints,
        makeGuess,
        goToScreenshot,
        nextScreenshot,
        prevScreenshot,
        loadChallenge,
    };
}
