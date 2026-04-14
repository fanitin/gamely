import { ref, computed } from "vue";
import axios from "axios";
import type { GameAttempt, GameStatus } from "../types/game";

export function useGame(modeId: string) {
    const attempts = ref<GameAttempt[]>([]);
    const isLoading = ref(false);
    const gameState = ref<GameStatus>("PLAYING");
    const error = ref<string | null>(null);

    const attemptsCount = computed(() => attempts.value.length);
    const isFinished = computed(() => gameState.value !== "PLAYING");

    const submitGuess = async (entityId: number | string) => {
        if (isFinished.value) return;

        isLoading.value = true;
        error.value = null;

        try {
            const response = await axios.post("/api/guess", {
                mode: modeId,
                id: entityId,
            });

            const result = response.data as GameAttempt;
            attempts.value.unshift(result);

            if (result.is_correct) {
                gameState.value = "WON";
            }
        } catch (err) {
            error.value = "Failed to submit guess";
            console.error(err);
        } finally {
            isLoading.value = false;
        }
    };

    return {
        attempts,
        attemptsCount,
        isLoading,
        gameState,
        isFinished,
        error,
        submitGuess,
    };
}
