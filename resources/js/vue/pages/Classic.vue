<script setup lang="ts">
import { Head } from "@inertiajs/vue3";
import { ref, watch } from "vue";
import { useI18n } from "vue-i18n";
import AppLayout from "@/vue/layouts/AppLayout.vue";
import GameGrid from "@/vue/components/game/GameGrid.vue";
import GuessInput from "@/vue/components/game/GuessInput.vue";
import ResultModal from "@/vue/components/game/ResultModal.vue";
import { useGame } from "@/vue/composables/useGame";

const { t } = useI18n();
const { attempts, isLoading, submitGuess, isFinished, gameState } = useGame("classic");

const showResultModal = ref(false);

watch(isFinished, (val) => {
    if (val) {
        setTimeout(() => {
            showResultModal.value = true;
        }, 1000);
    }
});

const columns = [
    t("attributes.game"),
    t("attributes.release_year"),
    t("attributes.genres"),
    t("attributes.platforms"),
    t("attributes.developer"),
    t("attributes.rating"),
];
</script>

<template>
    <AppLayout>
        <Head :title="t('modes.classic.title')" />

        <div class="max-w-6xl mx-auto px-4 py-8">
            <div class="text-center mb-12">
                <h1 class="text-3xl font-black text-white mb-2 uppercase tracking-tighter">
                    {{ t("modes.classic.title") }}
                </h1>
                <p class="text-muted">
                    {{ t("modes.classic.description") }}
                </p>
            </div>

            <div v-if="!isFinished" class="mb-12 sticky top-4 z-40">
                <GuessInput
                    type="game"
                    @select="item => submitGuess(item.id)"
                />
            </div>

            <GameGrid
                :columns="columns"
                :attempts="attempts"
                :is-loading="isLoading"
            />

            <ResultModal
                :show="showResultModal"
                :game-state="gameState"
                :attempts="attempts"
                @close="showResultModal = false"
            />
        </div>
    </AppLayout>
</template>
