<script setup lang="ts">
import { Head, Link } from "@inertiajs/vue3";
import { useI18n } from "vue-i18n";
import { computed } from "vue";
import { ArrowLeft, Trophy, AlertCircle } from "lucide-vue-next";
import AppLayout from "@/vue/layouts/AppLayout.vue";
import AppButton from "@/vue/components/ui/AppButton.vue";
import GuessInput from "@/vue/components/game/GuessInput.vue";
import AttemptRow from "@/vue/components/game/AttemptRow.vue";
import { useCharacterAttributesGame } from "@/vue/composables/useCharacterAttributesGame";
import HintCard from "@/vue/components/game/HintCard.vue";

const { t } = useI18n();
const { attempts, isWon, isLoading, isLoadingChallenge, error, canGuess, guessedGameIds, hints, attemptsCount, makeGuess } = useCharacterAttributesGame();

const reversedAttempts = computed(() => [...attempts.value].reverse());

const handleSelect = async (item: { id: number | string; name: string }) => {
    if (!canGuess.value) return;
    await makeGuess(Number(item.id));
};
</script>

<template>
    <AppLayout>
        <Head :title="t('modes.character_attributes.title')" />

        <div class="max-w-5/6 mx-auto px-4 py-8">
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
                        {{ t("modes.character_attributes.title") }}
                    </h1>
                    <p class="text-muted">
                        {{ t("modes.character_attributes.description") }}
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

            <div v-if="isLoadingChallenge" class="text-center py-12">
                <div
                    class="inline-block w-12 h-12 border-4 border-primary-500 border-t-transparent rounded-full animate-spin mb-4"
                ></div>
                <p class="text-muted">{{ t("game.loading") }}</p>
            </div>

            <template v-else>

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

            <div v-if="hints.length > 0 && !isWon" class="mb-6 grid grid-cols-3 gap-3">
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
                    type="character"
                    :placeholder="t('game.search_character')"
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

                <div class="overflow-x-auto pb-4">
                    <div class="min-w-[900px] space-y-3">
                        <div class="grid grid-cols-[50px_80px_repeat(4,_1fr)] gap-2 mb-2">
                            <div class="text-center text-xs font-black uppercase tracking-wider text-muted">
                                #
                            </div>
                            <div class="text-center text-xs font-black uppercase tracking-wider text-muted">
                                {{ t("attributes.character") }}
                            </div>
                            <div class="text-center text-xs font-black uppercase tracking-wider text-muted">
                                {{ t("attributes.franchises") }}
                            </div>
                            <div class="text-center text-xs font-black uppercase tracking-wider text-muted">
                                {{ t("attributes.gender") }}
                            </div>
                            <div class="text-center text-xs font-black uppercase tracking-wider text-muted">
                                {{ t("attributes.species") }}
                            </div>
                            <div class="text-center text-xs font-black uppercase tracking-wider text-muted">
                                {{ t("attributes.first_appearance_year") }}
                            </div>
                        </div>

                        <AttemptRow
                            v-for="(attempt, index) in reversedAttempts"
                            :key="index"
                            mode="character_attributes"
                            :attempt="attempt"
                            :attempt-number="attempts.length - index"
                        />
                    </div>
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
