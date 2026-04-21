<script setup lang="ts">
import { Head, Link } from "@inertiajs/vue3";
import { useI18n } from "vue-i18n";
import { computed } from "vue";
import { ArrowLeft, Trophy, AlertCircle } from "lucide-vue-next";
import AppLayout from "@/vue/layouts/AppLayout.vue";
import AppButton from "@/vue/components/ui/AppButton.vue";
import GuessInput from "@/vue/components/game/GuessInput.vue";
import AttemptRow from "@/vue/components/game/AttemptRow.vue";
import { useClassicGame } from "@/vue/composables/useClassicGame";

const { t } = useI18n();
const { attempts, isWon, isLoading, error, canGuess, guessedGameIds, makeGuess } = useClassicGame();

const reversedAttempts = computed(() => [...attempts.value].reverse());

const handleSelect = async (item: { id: number | string; name: string }) => {
    if (!canGuess.value) return;
    await makeGuess(Number(item.id));
};
</script>

<template>
    <AppLayout>
        <Head :title="t('modes.classic.title')" />

        <div class="max-w-6xl mx-auto px-4 py-8">
            <div class="flex items-center justify-between mb-12">
                <AppButton
                    :as="Link"
                    href="/"
                    variant="ghost"
                    size="sm"
                    class="group"
                >
                    <ArrowLeft class="w-4 h-4 mr-2 group-hover:-translate-x-1 transition-transform" />
                    {{ t("nav.back_to_menu") }}
                </AppButton>

                <div class="text-center flex-1">
                    <h1 class="text-3xl font-black text-white mb-2 uppercase tracking-tighter">
                        {{ t("modes.classic.title") }}
                    </h1>
                    <p class="text-muted">
                        {{ t("modes.classic.description") }}
                    </p>
                </div>

                <div class="w-[120px]"></div>
            </div>

            <div
                v-if="error"
                class="mb-6 bg-danger-500/10 border border-danger-500/30 rounded-xl p-4 flex items-center gap-3 animate-fade-in"
            >
                <AlertCircle class="w-5 h-5 text-danger-500 shrink-0" />
                <span class="text-white">{{ error }}</span>
            </div>

            <div
                v-if="isWon"
                class="mb-6 bg-success-500/10 border border-success-500/30 rounded-xl p-6 text-center space-y-3 animate-fade-in"
            >
                <Trophy class="w-12 h-12 text-success-500 mx-auto" />
                <h2 class="text-2xl font-bold text-white">
                    {{ t("game.you_won") }}!
                </h2>
                <p class="text-muted">
                    {{ t("game.attempts_count", { count: attempts.length + 1 }) }}
                </p>
            </div>

            <div v-if="canGuess" class="mb-12 sticky top-4 z-40">
                <GuessInput
                    type="game"
                    :placeholder="t('game.search_game')"
                    :exclude-ids="guessedGameIds"
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

                <div class="space-y-4">
                    <AttemptRow
                        v-for="(attempt, index) in reversedAttempts"
                        :key="index"
                        :attempt="attempt"
                    />
                </div>
            </div>
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

