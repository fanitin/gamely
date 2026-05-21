<script setup lang="ts">
import { computed } from "vue";
import { useI18n } from "vue-i18n";
import { Line } from "vue-chartjs";
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    Legend,
    Tooltip,
    type ChartData,
    type ChartOptions,
    type ScriptableScaleContext,
} from "chart.js";
import type { PersonalMode } from "@/vue/composables/usePersonalStats";

type DailyAttempts = Record<string, Partial<Record<PersonalMode, number>>>;

const props = defineProps<{
    dailyAttempts: DailyAttempts;
}>();

const { t } = useI18n();

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, Legend, Tooltip);

function cssVar(name: string): string {
    return getComputedStyle(document.documentElement).getPropertyValue(name).trim();
}

type ModeEntry = { key: PersonalMode; color: string };

const modes = computed<ModeEntry[]>(() => [
    { key: "classic", color: cssVar("--color-mode-classic") },
    { key: "game_screenshots", color: cssVar("--color-mode-screens") },
    { key: "character", color: cssVar("--color-mode-character") },
]);

const sortedDates = computed(() =>
    Object.keys(props.dailyAttempts)
        .filter((date) => Object.values(props.dailyAttempts[date] ?? {}).some((attempts) => Number(attempts) > 0))
        .sort((left, right) => left.localeCompare(right)),
);

const formatDate = (date: string) => {
    const [year, month, day] = date.split("-");
    if (!year || !month || !day) {
        return date;
    }
    return `${day.padStart(2, "0")}/${month.padStart(2, "0")}/${year}`;
};

const labels = computed(() => sortedDates.value.map(formatDate));

const chartData = computed<ChartData<"line">>(() => ({
    labels: labels.value,
    datasets: modes.value.map((mode) => ({
        label: t(`modes.${mode.key}.title`),
        data: sortedDates.value.map((date) => Number(props.dailyAttempts[date]?.[mode.key]) || 0),
        borderColor: mode.color,
        backgroundColor: mode.color,
        borderWidth: 4,
        pointRadius: 4,
        pointHoverRadius: 6,
        pointBorderWidth: 2,
        pointBorderColor: cssVar("--color-onyx-dark"),
        tension: 0.35,
        fill: false,
    })),
}));

const chartOptions = computed<ChartOptions<"line">>(() => ({
    responsive: true,
    maintainAspectRatio: false,
    animation: false,
    transitions: {
        active: {
            animation: {
                duration: 0,
            },
        },
    },
    interaction: {
        mode: "index",
        intersect: false,
    },
    plugins: {
        legend: {
            display: true,
            position: "top",
            labels: {
                color: "rgba(255, 255, 255, 0.82)",
                usePointStyle: true,
                pointStyle: "circle",
                boxWidth: 12,
                boxHeight: 12,
                padding: 18,
                font: {
                    size: 14,
                    weight: "600",
                },
            },
        },
        tooltip: {
            backgroundColor: "rgba(15, 23, 42, 0.95)",
            borderColor: "rgba(255, 255, 255, 0.18)",
            borderWidth: 1,
            padding: 10,
            titleColor: "rgba(255, 255, 255, 0.95)",
            bodyColor: "rgba(255, 255, 255, 0.95)",
            titleFont: {
                size: 13,
                weight: "700",
            },
            bodyFont: {
                size: 13,
                weight: "600",
            },
        },
    },
    scales: {
        x: {
            grid: {
                color: "rgba(255, 255, 255, 0.08)",
            },
            ticks: {
                color: "rgba(255, 255, 255, 0.78)",
                autoSkip: true,
                maxTicksLimit: 8,
                maxRotation: 0,
                minRotation: 0,
                font: (ctx: ScriptableScaleContext) => ({
                    size: ctx.chart.width < 700 ? 12 : 14,
                    weight: "600",
                }),
            },
        },
        y: {
            beginAtZero: true,
            grid: {
                color: "rgba(255, 255, 255, 0.1)",
            },
            ticks: {
                color: "rgba(255, 255, 255, 0.78)",
                precision: 0,
                font: (ctx: ScriptableScaleContext) => ({
                    size: ctx.chart.width < 700 ? 12 : 14,
                    weight: "600",
                }),
            },
        },
    },
}));
</script>

<template>
    <div class="rounded-xl border border-white/10 bg-white/[0.03] p-3 sm:p-4">
        <div class="h-[320px] sm:h-[420px]">
            <Line :data="chartData" :options="chartOptions" />
        </div>
    </div>
</template>
