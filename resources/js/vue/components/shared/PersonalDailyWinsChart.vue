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
    Filler,
    Legend,
    Tooltip,
    type ChartData,
    type ChartOptions,
} from "chart.js";
import type { PersonalMode } from "@/vue/composables/usePersonalStats";

type DailyAttempts = Record<string, Partial<Record<PersonalMode, number>>>;
type RangeKey = "30" | "90" | "365";

const props = defineProps<{
    dailyAttempts: DailyAttempts;
    mode: PersonalMode;
    range: RangeKey;
    color: string;
}>();

const { t } = useI18n();

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, Filler, Legend, Tooltip);

const parseDateKey = (key: string): Date | null => {
    const [year, month, day] = key.split("-").map(Number);
    if (!year || !month || !day) {
        return null;
    }
    return new Date(year, month - 1, day);
};

const formatDay = (date: Date): string =>
    `${String(date.getDate()).padStart(2, "0")}/${String(date.getMonth() + 1).padStart(2, "0")}`;

const formatFullDay = (date: Date): string =>
    `${formatDay(date)}/${date.getFullYear()}`;

const monthShortKey = (date: Date): string =>
    `personal_stats.months.${["jan", "feb", "mar", "apr", "may", "jun", "jul", "aug", "sep", "oct", "nov", "dec"][date.getMonth()]}`;

const formatMonth = (date: Date): string =>
    `${t(monthShortKey(date))} ${String(date.getFullYear()).slice(2)}`;

interface Point { date: Date; value: number | null; rawValue: number | null; }

const Y_CAP = 25;

const sortedRows = computed<Point[]>(() => {
    const rows: Point[] = [];
    Object.keys(props.dailyAttempts)
        .sort((a, b) => a.localeCompare(b))
        .forEach((dateKey) => {
            const date = parseDateKey(dateKey);
            if (!date) {
                return;
            }
            const raw = props.dailyAttempts[dateKey]?.[props.mode];
            const rawValue = raw != null && Number(raw) > 0 ? Number(raw) : null;
            rows.push({ date, value: rawValue, rawValue });
        });
    return rows;
});

const capValue = (value: number | null): number | null =>
    value == null ? value : Math.min(value, Y_CAP);

const displayed = computed<{ rows: Point[]; bucket: "day" | "month" }>(() => {
    if (props.range === "365") {
        const dataByMonth = new Map<string, { sum: number; count: number }>();
        sortedRows.value.forEach((row) => {
            const key = `${row.date.getFullYear()}-${row.date.getMonth()}`;
            const bucket = dataByMonth.get(key) ?? { sum: 0, count: 0 };
            if (row.rawValue != null) {
                bucket.sum += row.rawValue;
                bucket.count += 1;
            }
            dataByMonth.set(key, bucket);
        });

        const today = new Date();
        today.setHours(0, 0, 0, 0);
        const rows: Point[] = [];
        for (let i = 11; i >= 0; i--) {
            const d = new Date(today.getFullYear(), today.getMonth() - i, 1);
            const key = `${d.getFullYear()}-${d.getMonth()}`;
            const g = dataByMonth.get(key);
            const rawAvg = g && g.count > 0 ? Number((g.sum / g.count).toFixed(1)) : 0;
            rows.push({
                date: d,
                value: capValue(rawAvg),
                rawValue: rawAvg,
            });
        }
        return { rows, bucket: "month" };
    }

    const days = Number(props.range);
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    const dataByDay = new Map<string, number>();
    sortedRows.value.forEach((row) => {
        if (row.rawValue != null) {
            dataByDay.set(row.date.toDateString(), row.rawValue);
        }
    });
    const rows: Point[] = [];
    for (let i = days - 1; i >= 0; i--) {
        const d = new Date(today);
        d.setDate(today.getDate() - i);
        const rawValue = dataByDay.get(d.toDateString()) ?? 0;
        rows.push({ date: d, value: capValue(rawValue), rawValue });
    }
    return { rows, bucket: "day" };
});

const chartData = computed<ChartData<"line">>(() => ({
    labels: displayed.value.bucket === "month"
        ? displayed.value.rows.map((r) => formatMonth(r.date))
        : displayed.value.rows.map((r) => formatDay(r.date)),
    datasets: [
        {
            label: t(`modes.${props.mode}.title`),
            data: displayed.value.rows.map((r) => r.value),
            borderColor: props.color,
            backgroundColor: props.color,
            borderWidth: 3,
            pointRadius: 3,
            pointHoverRadius: 5,
            pointBorderWidth: 2,
            pointBorderColor: "#0f172a",
            pointBackgroundColor: props.color,
            tension: 0,
            fill: false,
        },
    ],
}));

const chartOptions = computed(() => ({
    responsive: true,
    maintainAspectRatio: false,
    animation: false,
    animations: { colors: false, x: false, y: false },
    transitions: { active: { animation: { duration: 100 } } },
    interaction: { mode: "index", intersect: false },
    plugins: {
        legend: { display: false },
        tooltip: {
            backgroundColor: "rgba(15, 23, 42, 0.95)",
            borderColor: "rgba(255, 255, 255, 0.18)",
            borderWidth: 1,
            padding: 10,
            titleColor: "rgba(255, 255, 255, 0.95)",
            bodyColor: "rgba(255, 255, 255, 0.95)",
            titleFont: { size: 13, weight: "700" },
            bodyFont: { size: 13, weight: "600" },
            usePointStyle: true,
            boxPadding: 6,
            callbacks: {
                title: (items) => {
                    if (!items.length) return "";
                    const row = displayed.value.rows[items[0].dataIndex];
                    if (!row) return "";
                    if (displayed.value.bucket === "month") {
                        return formatMonth(row.date);
                    }
                    return formatFullDay(row.date);
                },
                label: (item) => {
                    const row = displayed.value.rows[item.dataIndex];
                    if (!row) return "";
                    const v = row.rawValue;
                    if (v == null) return "";
                    const suffix =
                        displayed.value.bucket === "month"
                            ? ` ${t("personal_stats.avg_suffix")}`
                            : "";
                    return `${item.dataset.label}: ${v}${suffix}`;
                },
            },
        },
    },
    scales: {
        x: {
            grid: { color: "rgba(255, 255, 255, 0.06)" },
            border: { display: false },
            ticks: {
                color: "rgba(255, 255, 255, 0.6)",
                autoSkip: true,
                maxTicksLimit: 8,
                maxRotation: 0,
                minRotation: 0,
                font: (ctx) => ({
                    size: ctx.chart.width < 700 ? 12 : 13,
                    weight: "600",
                }),
            },
        },
        y: {
            beginAtZero: true,
            suggestedMax: Y_CAP,
            grid: { color: "rgba(255, 255, 255, 0.08)" },
            border: { display: false },
            ticks: {
                color: "rgba(255, 255, 255, 0.6)",
                precision: 0,
                callback: (value: number | string) => {
                    const num = Number(value);
                    return num >= Y_CAP ? `${Y_CAP}+` : num;
                },
                font: (ctx) => ({
                    size: ctx.chart.width < 700 ? 12 : 13,
                    weight: "600",
                }),
            },
        },
    },
}));
</script>

<template>
    <div class="h-[320px] sm:h-[420px]">
        <Line :data="chartData" :options="chartOptions" />
    </div>
</template>
