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

// ── helpers ─────────────────────────────────────────────────────────────
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

const startOfWeek = (date: Date): Date => {
    const day = (date.getDay() + 6) % 7;
    const out = new Date(date);
    out.setDate(date.getDate() - day);
    return out;
};

interface Point { date: Date; value: number | null; }

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
            const value = raw != null && Number(raw) > 0 ? Number(raw) : null;
            rows.push({ date, value });
        });
    return rows;
});

const displayed = computed<{ rows: Point[]; bucket: "day" | "week" }>(() => {
    if (props.range === "365") {
        const groups = new Map<string, { date: Date; sum: number; count: number }>();
        sortedRows.value.forEach((row) => {
            const wkStart = startOfWeek(row.date);
            const key = wkStart.toISOString().slice(0, 10);
            const bucket = groups.get(key) ?? { date: wkStart, sum: 0, count: 0 };
            if (row.value != null) {
                bucket.sum += row.value;
                bucket.count += 1;
            }
            groups.set(key, bucket);
        });
        const rows: Point[] = [...groups.values()]
            .sort((a, b) => a.date.getTime() - b.date.getTime())
            .map((g) => ({
                date: g.date,
                value: g.count > 0 ? Number((g.sum / g.count).toFixed(1)) : null,
            }));
        return { rows, bucket: "week" };
    }

    const days = Number(props.range);
    const cutoff = new Date();
    cutoff.setHours(0, 0, 0, 0);
    cutoff.setDate(cutoff.getDate() - (days - 1));
    return {
        rows: sortedRows.value.filter((row) => row.date >= cutoff),
        bucket: "day",
    };
});

const chartData = computed<ChartData<"line">>(() => ({
    labels: displayed.value.rows.map((r) =>
        displayed.value.bucket === "week" ? formatMonth(r.date) : formatDay(r.date),
    ),
    datasets: [
        {
            label: t(`modes.${props.mode}.title`),
            data: displayed.value.rows.map((r) => r.value),
            borderColor: props.color,
            backgroundColor: props.color,
            borderWidth: 4,
            pointRadius: displayed.value.bucket === "week" ? 3 : 4,
            pointHoverRadius: 6,
            pointBorderWidth: 2,
            pointBorderColor: "#0f172a",
            pointBackgroundColor: props.color,
            tension: 0.35,
            fill: false,
            spanGaps: true,
        },
    ],
}));

const chartOptions = computed<ChartOptions<"line">>(() => ({
    responsive: true,
    maintainAspectRatio: false,
    animation: { duration: 250 },
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
                    if (displayed.value.bucket === "week") {
                        const end = new Date(row.date);
                        end.setDate(end.getDate() + 6);
                        return t("personal_stats.week_of", {
                            from: formatDay(row.date),
                            to: formatDay(end),
                        });
                    }
                    return formatFullDay(row.date);
                },
                label: (item) => {
                    const v = item.parsed.y;
                    if (v == null) return "";
                    const suffix =
                        displayed.value.bucket === "week"
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
            grid: { color: "rgba(255, 255, 255, 0.08)" },
            border: { display: false },
            ticks: {
                color: "rgba(255, 255, 255, 0.6)",
                precision: 0,
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
