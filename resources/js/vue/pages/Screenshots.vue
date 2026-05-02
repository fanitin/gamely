<script setup lang="ts">
import { Head, Link } from "@inertiajs/vue3";
import { useI18n } from "vue-i18n";
import { computed } from "vue";
import { ArrowLeft, Trophy, AlertCircle, ChevronLeft, ChevronRight } from "lucide-vue-next";
import AppLayout from "@/vue/layouts/AppLayout.vue";
import AppButton from "@/vue/components/ui/AppButton.vue";
import GuessInput from "@/vue/components/game/GuessInput.vue";
import HintCard from "@/vue/components/game/HintCard.vue";
import AttemptRow from "@/vue/components/game/AttemptRow.vue";
import { useScreenshotsGame } from "@/vue/composables/useScreenshotsGame";

const { t } = useI18n();
const {
    attempts,
    attemptsCount,
    isWon,
    isLoading,
    isLoadingChallenge,
    error,
    canGuess,
    guessedGameIds,
    visibleScreenshots,
    visibleScreenshotsCount,
    totalScreenshots,
    currentScreenshotIndex,
    hints,
    makeGuess,
    goToScreenshot,
    nextScreenshot,
    prevScreenshot,
} = useScreenshotsGame();

const currentScreenshot = computed(() => {
    return visibleScreenshots.value[currentScreenshotIndex.value];
});

const reversedAttempts = computed(() => [...attempts.value].reverse());

const handleSelect = async (item: { id: number | string }) => {
    if (!canGuess.value) return;
    await makeGuess(Number(item.id));
};

const progressPercentage = computed(() => {
    if (totalScreenshots.value === 0) return 0;
    return (visibleScreenshotsCount.value / totalScreenshots.value) * 100;
});
</script>

<template>
    <AppLayout>
        <Head :title="t('modes.game_screenshots.title')" />

        <div class="max-w-5xl mx-auto px-4 py-8">
            <div class="flex items-center justify-between mb-8">
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
                        {{ t("modes.game_screenshots.title") }}
                    </h1>
                    <p class="text-muted">
                        {{ t("modes.game_screenshots.description") }}
                    </p>
                </div>

                <div class="w-[120px]"></div>
            </div>

            <div
                v-if="error"
                class="mb-6 bg-crimson-500/10 border border-crimson-500/30 rounded-xl p-4 flex items-center gap-3 animate-fade-in"
            >
                <AlertCircle class="w-5 h-5 text-crimson-500 shrink-0" />
                <span class="text-white">{{ error }}</span>
            </div>

            <div v-if="isLoadingChallenge" class="text-center py-20">
                <div
                    class="inline-block w-12 h-12 border-4 border-teal-500 border-t-transparent rounded-full animate-spin"
                ></div>
                <p class="text-muted mt-4">{{ t("game.loading") }}</p>
            </div>

            <template v-else>
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

                <div
                    v-if="isWon"
                    class="mb-6 bg-forest-500/10 border border-forest-500/30 rounded-xl p-6 text-center space-y-3 animate-fade-in"
                >
                    <Trophy class="w-12 h-12 text-forest-500 mx-auto" />
                    <h2 class="text-2xl font-bold text-white">
                        {{ t("game.you_won") }}!
                    </h2>
                    <p class="text-muted">
                        {{ t("game.attempts_count", { count: attempts.length }) }}
                    </p>
                </div>

                <div class="mb-6">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm text-muted">
                            {{ t("screenshots.revealed") }}
                        </span>
                        <span class="text-sm font-bold text-white">
                            {{ visibleScreenshotsCount }} / {{ totalScreenshots }}
                        </span>
                    </div>
                    <div class="h-2 bg-onyx-light rounded-full overflow-hidden">
                        <div
                            class="h-full bg-gradient-to-r from-teal-600 to-teal-400 rounded-full transition-all duration-500"
                            :style="{ width: `${progressPercentage}%` }"
                        ></div>
                    </div>
                </div>

                <div class="mb-8 relative">
                    <div class="bg-onyx rounded-2xl border border-onyx-light/30 overflow-hidden">
                        <div class="relative aspect-video bg-onyx-dark">
                            <img
                                v-if="currentScreenshot"
                                :src="currentScreenshot.url"
                                :alt="t('screenshots.screenshot_alt', { number: currentScreenshotIndex + 1 })"
                                class="w-full h-full object-contain"
                            />
                            <div
                                v-else
                                class="w-full h-full flex items-center justify-center text-muted"
                            >
                                {{ t("screenshots.no_screenshot") }}
                            </div>

                            <button
                                v-if="currentScreenshotIndex > 0"
                                class="absolute left-4 top-1/2 -translate-y-1/2 w-12 h-12 bg-onyx-dark/70 hover:bg-onyx-dark rounded-full flex items-center justify-center text-white transition-all"
                                @click="prevScreenshot"
                            >
                                <ChevronLeft class="w-6 h-6" />
                            </button>
                            <button
                                v-if="currentScreenshotIndex < visibleScreenshotsCount - 1"
                                class="absolute right-4 top-1/2 -translate-y-1/2 w-12 h-12 bg-onyx-dark/70 hover:bg-onyx-dark rounded-full flex items-center justify-center text-white transition-all"
                                @click="nextScreenshot"
                            >
                                <ChevronRight class="w-6 h-6" />
                            </button>
                        </div>

                        <div
                            v-if="visibleScreenshots.length > 1"
                            class="p-4 border-t border-onyx-light/30"
                        >
                            <div class="flex gap-2 overflow-x-auto pb-2 custom-scrollbar">
                                <button
                                    v-for="(screenshot, index) in visibleScreenshots"
                                    :key="screenshot.id"
                                    class="shrink-0 w-24 h-14 rounded-lg overflow-hidden border-2 transition-all"
                                    :class="[
                                        currentScreenshotIndex === index
                                            ? 'border-teal-500 ring-2 ring-teal-500/30'
                                            : 'border-transparent hover:border-onyx-light'
                                    ]"
                                    @click="goToScreenshot(index)"
                                >
                                    <img
                                        :src="screenshot.url"
                                        :alt="t('screenshots.thumbnail_alt', { number: index + 1 })"
                                        class="w-full h-full object-cover"
                                    />
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="canGuess" class="mb-8">
                    <GuessInput
                        type="game"
                        :placeholder="t('game.search_game')"
                        :exclude-ids="guessedGameIds"
                        @select="handleSelect"
                    />
                </div>

                <div v-if="isLoading" class="text-center mb-8">
                    <div
                        class="inline-block w-8 h-8 border-4 border-teal-500 border-t-transparent rounded-full animate-spin"
                    ></div>
                </div>

                <div v-if="attempts.length > 0" class="space-y-6 mb-8">
                    <h2 class="text-2xl font-bold text-white">
                        {{ t("game.your_attempts") }}
                    </h2>

                    <div class="space-y-3">
                        <div class="grid grid-cols-[60px_1fr_1fr_1fr] gap-2">
                            <div class="text-center text-xs font-black uppercase tracking-wider text-muted py-2">
                                #
                            </div>
                            <div class="text-center text-xs font-black uppercase tracking-wider text-muted py-2">
                                {{ t("attributes.game") }}
                            </div>
                            <div class="text-center text-xs font-black uppercase tracking-wider text-muted py-2">
                                {{ t("attributes.franchises_collections") }}
                            </div>
                            <div class="text-center text-xs font-black uppercase tracking-wider text-muted py-2">
                                {{ t("attributes.developers_publishers") }}
                            </div>
                        </div>

                        <AttemptRow
                            v-for="(attempt, index) in reversedAttempts"
                            :key="index"
                            :attempt="attempt"
                            :attempt-number="attempts.length - index"
                            mode="screenshots"
                        />
                    </div>
                </div>

                <div class="h-[300px]"></div>
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

.custom-scrollbar::-webkit-scrollbar {
    height: 6px;
}

.custom-scrollbar::-webkit-scrollbar-track {
    background: rgba(255, 255, 255, 0.05);
    border-radius: 3px;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.2);
    border-radius: 3px;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: rgba(255, 255, 255, 0.3);
}
</style>
