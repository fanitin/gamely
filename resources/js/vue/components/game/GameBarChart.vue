<script setup lang="ts">
import { computed } from "vue";
import { useI18n } from "vue-i18n";

interface Props {
    bins: Array<{ attempts: number; players: number }>;
    totalPlayers: number;
}

const props = defineProps<Props>();
const { t } = useI18n();

type RangeBucket = {
    key: string;
    label: string;
    min: number;
    max: number | null;
};

const rangeBuckets: RangeBucket[] = [
    { key: "1-3", label: "1-3", min: 1, max: 3 },
    { key: "4-6", label: "4-6", min: 4, max: 6 },
    { key: "7-9", label: "7-9", min: 7, max: 9 },
    { key: "10-12", label: "10-12", min: 10, max: 12 },
    { key: "13-15", label: "13-15", min: 13, max: 15 },
    { key: "16-18", label: "16-18", min: 16, max: 18 },
    { key: "19-21", label: "19-21", min: 19, max: 21 },
    { key: "22+", label: "22+", min: 22, max: null },
];

const groupedBins = computed(() =>
    rangeBuckets.map((bucket) => {
        const players = props.bins
            .filter((bin) =>
                bucket.max === null
                    ? bin.attempts >= bucket.min
                    : bin.attempts >= bucket.min && bin.attempts <= bucket.max,
            )
            .reduce((sum, bin) => sum + bin.players, 0);

        const percentage = props.totalPlayers > 0 ? (players / props.totalPlayers) * 100 : 0;

        return {
            ...bucket,
            players,
            percentage,
        };
    }),
);

const maxPercentage = computed(() =>
    Math.max(...groupedBins.value.map((bin) => bin.percentage), 1),
);
</script>

<template>
    <div class="space-y-3">
        <h3 class="text-sm uppercase tracking-wide text-white/85">{{ t("win_stats.distribution_title") }}</h3>
        <div class="rounded-xl border border-white/15 bg-black/25 p-4">
            <div class="h-56 grid grid-cols-8 gap-3 items-end border-b border-white/20 pb-2">
                <div
                    v-for="bin in groupedBins"
                    :key="bin.key"
                    class="flex flex-col items-center justify-end h-full"
                >
                    <div class="text-xs font-semibold text-white mb-2 tabular-nums leading-none">
                        {{ bin.percentage.toFixed(1) }}%
                    </div>
                    <div class="w-full max-w-9 h-36 flex items-end">
                        <div
                            class="w-full rounded-t-md bg-primary-400"
                            :style="{ height: `${(bin.percentage / maxPercentage) * 100}%` }"
                        ></div>
                    </div>
                    <div class="mt-2 text-xs font-medium text-white/90 tabular-nums leading-none">{{ bin.label }}</div>
                </div>
            </div>
        </div>
        <p class="text-sm text-white/90">{{ t("win_stats.total_players", { count: totalPlayers }) }}</p>
    </div>
</template>
