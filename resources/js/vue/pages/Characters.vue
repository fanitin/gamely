<script setup lang="ts">
import { Head, Link } from "@inertiajs/vue3";
import { ref, watch, computed } from "vue";
import { useI18n } from "vue-i18n";
import { ArrowLeft } from "lucide-vue-next";
import AppLayout from "@/vue/layouts/AppLayout.vue";
import AppButton from "@/vue/components/ui/AppButton.vue";
import GameGrid from "@/vue/components/game/GameGrid.vue";
import GuessInput from "@/vue/components/game/GuessInput.vue";
import ResultModal from "@/vue/components/game/ResultModal.vue";
import { useGame } from "@/vue/composables/useGame";

const { t } = useI18n();
const { attempts, isLoading, submitGuess, isFinished, gameState } = useGame("characters");

const showResultModal = ref(false);

watch(isFinished, (val) => {
    if (val) {
        setTimeout(() => {
            showResultModal.value = true;
        }, 1000);
    }
});

const columns = computed(() => [
    t("attributes.character"),
    t("attributes.series"),
    t("attributes.gender"),
    t("attributes.species"),
    t("attributes.first_appearance"),
]);
</script>

<template>
    <AppLayout>
        <Head :title="t('modes.characters.title')" />

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
                        {{ t("modes.characters.title") }}
                    </h1>
                    <p class="text-muted">
                        {{ t("modes.characters.description") }}
                    </p>
                </div>

                <div class="w-[120px]"></div>
            </div>

            <div v-if="!isFinished" class="mb-12 sticky top-4 z-40">
                <GuessInput
                    type="character"
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
