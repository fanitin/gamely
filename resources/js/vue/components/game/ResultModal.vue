<script setup lang="ts">
import { ref, watch } from "vue";
import { useI18n } from "vue-i18n";
import { Trophy, X } from "lucide-vue-next";
import { useStats, type ModeValue } from "@/vue/composables/useStats";
import AppModal from "@/vue/components/ui/AppModal.vue";
import GameBarChart from "./GameBarChart.vue";
import GameAttemptsShare from "./GameAttemptsShare.vue";

type ComparisonResult = { result: "exact" | "close" | "wrong" };
type AttemptLike = { comparison: Record<string, ComparisonResult> };

interface Props {
    show: boolean;
    mode: ModeValue;
    attemptsCount: number;
    attempts: AttemptLike[];
    entityName: string;
    challengeDate: string;
}

const props = defineProps<Props>();

const emit = defineEmits<{
    (e: "close"): void;
}>();

const { t } = useI18n();
const { getModeDistribution } = useStats();

const bins = ref<Array<{ attempts: number; players: number }>>([]);
const totalPlayers = ref(0);
const lastLoadedKey = ref<string | null>(null);

const loadDistribution = async () => {
    try {
        const data = await getModeDistribution(props.mode, props.challengeDate);
        bins.value = Array.isArray(data.bins) ? data.bins : [];
        totalPlayers.value = Number.isFinite(data.total_players) ? data.total_players : 0;
    } catch {
        bins.value = [];
        totalPlayers.value = 0;
    }
};

watch(
    () => [props.show, props.mode, props.challengeDate] as const,
    async ([isOpen, mode, challengeDate]) => {
        if (!isOpen) {
            return;
        }
        const key = `${mode}:${challengeDate}`;
        if (lastLoadedKey.value === key) {
            return;
        }
        await loadDistribution();
        lastLoadedKey.value = key;
    },
    { immediate: true },
);
</script>

<template>
    <AppModal :is-open="props.show" :title="''" size="lg" :hide-header="true" @close="emit('close')">
        <div class="space-y-4">
            <div class="flex items-start justify-between gap-4">
                <div class="flex items-start gap-3">
                    <Trophy class="w-9 h-9 text-success-500 shrink-0 mt-0.5" />
                    <div class="space-y-1">
                        <h3 class="text-xl font-bold text-white uppercase tracking-tight">
                            {{ t("game.you_won") }}!
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
