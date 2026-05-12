<script setup lang="ts">
import { ref, watch } from "vue";
import { useI18n } from "vue-i18n";
import { Trophy } from "lucide-vue-next";
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
    <AppModal :is-open="props.show" :title="''" size="lg" @close="emit('close')">
        <div class="space-y-6">
            <div class="text-center space-y-2">
                <Trophy class="w-12 h-12 text-success-500 mx-auto" />
                <h3 class="text-2xl font-bold text-white uppercase tracking-tighter">
                    {{ t("game.you_won") }}!
                </h3>
                <p class="text-muted">{{ t("game.attempts_count", { count: attemptsCount }) }}</p>
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
