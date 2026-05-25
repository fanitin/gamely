<script setup lang="ts">
import { Head, Link } from "@inertiajs/vue3";
import { useI18n } from "vue-i18n";
import { computed, ref, watch } from "vue";
import { ArrowLeft, AlertCircle } from "lucide-vue-next";
import AppLayout from "@/vue/layouts/AppLayout.vue";
import AppButton from "@/vue/components/ui/AppButton.vue";
import GuessInput from "@/vue/components/game/GuessInput.vue";
import AttemptRow from "@/vue/components/game/AttemptRow.vue";
import { useClassicGame } from "@/vue/composables/useClassicGame";
import HintCard from "@/vue/components/game/HintCard.vue";
import ResultModal from "@/vue/components/game/ResultModal.vue";

const { t } = useI18n();
const {
    attempts,
    isWon,
    isLoading,
    isLoadingChallenge,
    error,
    canGuess,
    guessedGameIds,
    hints,
    attemptsCount,
    lastCorrectGuess,
    makeGuess,
} = useClassicGame();

const reversedAttempts = computed(() => [...attempts.value].reverse());

const showModal = ref(false);
watch(isWon, (newVal) => {
    if (newVal) {
        showModal.value = true;
    }
});

const handleSelect = async (item: { id: number | string; name: string }) => {
    if (!canGuess.value) return;
    await makeGuess(Number(item.id));
};
</script>

<template>
    <AppLayout>
        <Head :title="t('modes.classic.title')" />

        <div class="max-w-7xl mx-auto px-4 py-8">
            <div
                class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8 sm:mb-12"
            >
                <AppButton
                    :as="Link"
                    href="/"
                    variant="ghost"
                    size="sm"
                    class="group self-start"
                >
                    <ArrowLeft
                        class="w-4 h-4 mr-2 group-hover:-translate-x-1 transition-transform"
                    />
                    {{ t("nav.back_to_menu") }}
                </AppButton>

                <div class="text-center flex-1">
                    <h1
                        class="text-2xl sm:text-3xl font-black text-white mb-1 sm:mb-2 uppercase tracking-tighter"
                    >
                        {{ t("modes.classic.title") }}
                    </h1>
                    <p class="text-muted text-sm sm:text-base">
                        {{ t("modes.classic.description") }}
                    </p>
                </div>

                <div class="hidden sm:block w-[120px]"></div>
            </div>

            <div
                v-if="error"
                class="mb-6 bg-danger-500/10 border border-danger-500/30 rounded-xl p-4 flex items-center gap-3 animate-fade-in"
            >
                <AlertCircle class="w-5 h-5 text-danger-500 shrink-0" />
                <span class="text-white">{{ error }}</span>
            </div>

            <div v-if="isLoadingChallenge" class="text-center py-12">
                <div
                    class="inline-block w-12 h-12 border-4 border-primary-500 border-t-transparent rounded-full animate-spin mb-4"
                ></div>
                <p class="text-muted">{{ t("game.loading") }}</p>
            </div>

            <template v-else>
                <ResultModal
                    :show="showModal"
                    @close="showModal = false"
                    mode="classic"
                    :attempts-count="attempts.length"
                    :attempts="attempts"
                    :entity-name="
                        lastCorrectGuess?.display_name ||
                        lastCorrectGuess?.name ||
                        ''
                    "
                    :challenge-date="new Date().toISOString().split('T')[0]"
                />

                <div
                    v-if="hints.length > 0 && !isWon"
                    class="mb-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3"
                >
                    <HintCard
                        v-for="hint in hints"
                        :key="hint.type"
                        :type="hint.type"
                        :value="hint.value"
                        :unlock-at="hint.unlockAt"
                        :current-attempts="attemptsCount"
                    />
                </div>

                <div v-if="canGuess" class="mb-12 sticky top-4 z-40">
                    <GuessInput
                        type="game"
                        :placeholder="t('game.search_game')"
                        :exclude-ids="guessedGameIds"
                        :focus-trigger="attemptsCount"
                        @select="handleSelect"
                    />
                </div>

                <div v-if="isLoading" class="text-center mb-8">
                    <div
                        class="inline-block w-8 h-8 border-4 border-primary-500 border-t-transparent rounded-full animate-spin"
                    ></div>
                </div>

                <div v-if="attempts.length > 0" class="space-y-6 mb-8">
                    <h2 class="text-2xl font-bold text-white">
                        {{ t("game.your_attempts") }}
                    </h2>

                    <div class="space-y-3 lg:space-y-3">
                        <div
                            class="hidden lg:grid grid-cols-[50px_80px_repeat(5,1fr)_80px_80px_90px] gap-2 mb-2"
                        >
                            <div
                                class="text-center text-xs font-black uppercase tracking-wider text-muted"
                            >
                                #
                            </div>
                            <div
                                class="text-center text-xs font-black uppercase tracking-wider text-muted"
                            >
                                {{ t("attributes.game") }}
                            </div>
                            <div
                                class="text-center text-xs font-black uppercase tracking-wider text-muted"
                            >
                                {{ t("attributes.genres") }}
                            </div>
                            <div
                                class="text-center text-xs font-black uppercase tracking-wider text-muted"
                            >
                                {{ t("attributes.developers_publishers") }}
                            </div>
                            <div
                                class="text-center text-xs font-black uppercase tracking-wider text-muted"
                            >
                                {{ t("attributes.franchises_collections") }}
                            </div>
                            <div
                                class="text-center text-xs font-black uppercase tracking-wider text-muted"
                            >
                                {{ t("attributes.player_perspective") }}
                            </div>
                            <div
                                class="text-center text-xs font-black uppercase tracking-wider text-muted"
                            >
                                {{ t("attributes.game_mode") }}
                            </div>
                            <div
                                class="text-center text-xs font-black uppercase tracking-wider text-muted"
                            >
                                {{ t("attributes.rating") }}
                            </div>
                            <div
                                class="text-center text-xs font-black uppercase tracking-wider text-muted"
                            >
                                {{ t("attributes.release_year") }}
                            </div>
                            <div
                                class="text-center text-xs font-black uppercase tracking-wider text-muted"
                            >
                                {{ t("attributes.popularity") }}
                            </div>
                        </div>

                        <AttemptRow
                            v-for="(attempt, index) in reversedAttempts"
                            :key="index"
                            :attempt="attempt"
                            :attempt-number="attempts.length - index"
                        />
                    </div>
                </div>
            </template>
        </div>
    </AppLayout>
</template>

<style scoped>
@keyframes fade-in {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fade-in {
    animation: fade-in 0.3s ease-out;
}
</style>
