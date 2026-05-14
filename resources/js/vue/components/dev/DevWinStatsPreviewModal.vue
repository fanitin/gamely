<script setup lang="ts">
import { computed } from "vue";
import { useI18n } from "vue-i18n";
import { Trophy, X } from "lucide-vue-next";
import AppModal from "@/vue/components/ui/AppModal.vue";
import GameBarChart from "@/vue/components/game/GameBarChart.vue";
import GameAttemptsShare from "@/vue/components/game/GameAttemptsShare.vue";

const props = defineProps<{
    isOpen: boolean;
}>();

const emit = defineEmits<{
    (e: "close"): void;
}>();

const { t } = useI18n();

const mode = "classic";
const attemptsCount = 27;
const entityName = "Mock Massive Numbers";
const challengeDate = "2026-05-14";

const bins = Array.from({ length: 28 }, (_, index) => ({
    attempts: index + 1,
    players: Math.max(4, Math.round(1400 * Math.exp(-index / 6) + (index % 4) * 83)),
}));

const totalPlayers = computed(() => bins.reduce((sum, bin) => sum + bin.players, 0));

const states: Array<"exact" | "close" | "wrong"> = ["exact", "close", "wrong", "exact", "close"];

const attempts = Array.from({ length: attemptsCount }, (_, attemptIndex) => ({
    comparison: {
        genres: { result: states[(attemptIndex + 0) % states.length] },
        developers_publishers: { result: states[(attemptIndex + 1) % states.length] },
        franchises_collections: { result: states[(attemptIndex + 2) % states.length] },
        player_perspectives: { result: states[(attemptIndex + 3) % states.length] },
        game_modes: { result: states[(attemptIndex + 4) % states.length] },
        rating: { result: states[(attemptIndex + 2) % states.length] },
        release_year: { result: states[(attemptIndex + 1) % states.length] },
    },
}));
</script>

<template>
    <AppModal :is-open="props.isOpen" :title="''" size="lg" :hide-header="true" @close="emit('close')">
        <div class="space-y-4">
            <div class="flex items-start justify-between gap-4">
                <div class="flex items-start gap-3">
                    <Trophy class="w-9 h-9 text-success-500 shrink-0 mt-0.5" />
                    <div class="space-y-1">
                        <h3 class="text-xl font-bold text-white uppercase tracking-tight">
                            {{ t("game.you_won") }}! (DEV)
                        </h3>
                        <p class="text-sm text-muted">{{ t("game.attempts_count", { count: attemptsCount }) }}</p>
                    </div>
                </div>
                <button
                    type="button"
                    class="rounded-lg p-1 text-muted hover:text-white hover:bg-white/10 transition-colors focus:outline-none focus:ring-2 focus:ring-teal-500/50"
                    @click="emit('close')"
                >
                    <X class="w-5 h-5" />
                </button>
            </div>

            <GameBarChart :bins="bins" :total-players="totalPlayers" />

            <GameAttemptsShare
                :mode="mode"
                :attempts-count="attemptsCount"
                :attempts="attempts"
                :entity-name="entityName"
                :challenge-date="challengeDate"
            />
        </div>
    </AppModal>
</template>
