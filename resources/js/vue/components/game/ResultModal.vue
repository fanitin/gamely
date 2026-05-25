<script setup lang="ts">
import { computed, ref, watch } from "vue";
import { useI18n } from "vue-i18n";
import { Copy, Check, X } from "lucide-vue-next";
import AppModal from "@/vue/components/ui/AppModal.vue";
import { useStats, type ModeValue } from "@/vue/composables/useStats";
import { getComparisonKeysForMode } from "@/vue/composables/useComparisonKeys";

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
const emit = defineEmits<{ (e: "close"): void }>();

const { t, locale } = useI18n();
const { getModeDistribution } = useStats();

type RangeBucket = { key: string; label: string; min: number; max: number | null };

const RANGES: RangeBucket[] = [
    { key: "1-3",   label: "1–3",   min: 1,  max: 3 },
    { key: "4-6",   label: "4–6",   min: 4,  max: 6 },
    { key: "7-9",   label: "7–9",   min: 7,  max: 9 },
    { key: "10-12", label: "10–12", min: 10, max: 12 },
    { key: "13-15", label: "13–15", min: 13, max: 15 },
    { key: "16-18", label: "16–18", min: 16, max: 18 },
    { key: "19-21", label: "19–21", min: 19, max: 21 },
    { key: "22+",   label: "22+",   min: 22, max: null },
];

const MODE_ACCENT_KEY: Record<ModeValue, "classic" | "screens" | "character"> = {
    classic: "classic",
    game_screenshots: "screens",
    character: "character",
};

const bins = ref<Array<{ attempts: number; players: number }>>([]);
const totalPlayers = ref(0);
const average = ref<number | null>(null);
const copied = ref(false);
const lastKey = ref<string | null>(null);

const accentKey = computed(() => MODE_ACCENT_KEY[props.mode]);

const buckets = computed(() =>
    RANGES.map((bucket) => {
        const players = bins.value
            .filter((bin) =>
                bucket.max === null
                    ? bin.attempts >= bucket.min
                    : bin.attempts >= bucket.min && bin.attempts <= bucket.max,
            )
            .reduce((sum, bin) => sum + bin.players, 0);
        const share = totalPlayers.value > 0 ? (players / totalPlayers.value) * 100 : 0;
        return { ...bucket, players, share };
    }),
);

const yourBucketIndex = computed(() =>
    buckets.value.findIndex((b) =>
        b.max === null
            ? props.attemptsCount >= b.min
            : props.attemptsCount >= b.min && props.attemptsCount <= b.max,
    ),
);

const maxShare = computed(() =>
    Math.max(...buckets.value.map((b) => b.share), 1),
);


const best = computed<number | null>(() => {
    const withPlayers = bins.value.filter((b) => b.players > 0).map((b) => b.attempts);
    return withPlayers.length > 0 ? Math.min(...withPlayers) : null;
});

const fasterThan = computed<number | null>(() => {
    if (totalPlayers.value === 0) return null;
    let worse = 0;
    for (const b of bins.value) {
        if (b.attempts > props.attemptsCount) worse += b.players;
    }
    return Math.round((worse / totalPlayers.value) * 100);
});

const modeLabel = computed(() => t(`modes.${props.mode}.title`));

const formattedDate = computed(() => {
    try {
        const d = new Date(props.challengeDate + "T00:00:00Z");
        return new Intl.DateTimeFormat(locale.value, {
            day: "numeric",
            month: "short",
            year: "numeric",
            timeZone: "UTC",
        }).format(d);
    } catch {
        return props.challengeDate;
    }
});

const emojiMap: Record<ComparisonResult["result"], string> = {
    exact: "🟩",
    close: "🟧",
    wrong: "🟥",
};

const rawRows = computed(() =>
    props.attempts.map((attempt) =>
        getComparisonKeysForMode(props.mode, Object.keys(attempt.comparison))
            .map((key) => attempt.comparison[key])
            .filter((cell): cell is ComparisonResult => Boolean(cell))
            .map((cell) => emojiMap[cell.result] ?? "🟥")
            .join(""),
    ),
);

const sharedRows = computed(() => [...rawRows.value].reverse().slice(0, 5));
const extraAttempts = computed(() => Math.max(rawRows.value.length - sharedRows.value.length, 0));

const shareLink = computed(() => {
    if (typeof window === "undefined") return "";
    const path = props.mode === "game_screenshots" ? "screenshots" : props.mode;
    return `${window.location.origin}/${path}`;
});

const shareText = computed(() => {
    const header = t("win_stats.share_header", {
        date: props.challengeDate,
        name: props.entityName,
        mode: modeLabel.value,
        attempts: props.attemptsCount,
    });
    const extras = extraAttempts.value > 0 ? `\n➕(${extraAttempts.value})` : "";
    return `${header}\n${sharedRows.value.join("\n")}${extras}\n${shareLink.value}`;
});

const copyShare = async () => {
    await navigator.clipboard.writeText(shareText.value);
    copied.value = true;
    setTimeout(() => { copied.value = false; }, 1400);
};

const loadDistribution = async () => {
    try {
        const data = await getModeDistribution(props.mode, props.challengeDate);
        bins.value = Array.isArray(data.bins) ? data.bins : [];
        totalPlayers.value = Number.isFinite(data.total_players) ? data.total_players : 0;
        average.value = data.average ?? null;
    } catch {
        bins.value = [];
        totalPlayers.value = 0;
        average.value = null;
    }
};

watch(
    () => [props.show, props.mode, props.challengeDate] as const,
    async ([isOpen, mode, challengeDate]) => {
        if (!isOpen) return;
        const key = `${mode}:${challengeDate}`;
        if (lastKey.value === key) return;
        await loadDistribution();
        lastKey.value = key;
    },
    { immediate: true },
);
</script>

<template>
    <AppModal :is-open="props.show" :title="''" size="lg" :hide-header="true" @close="emit('close')">
        <div :data-accent="accentKey" class="win-stats">
            <div class="flex items-start justify-between gap-4 -mt-2">
                <div class="space-y-1.5">
                    <p class="text-[11px] font-medium uppercase tracking-[0.18em] text-white/45">
                        {{ modeLabel }} · {{ formattedDate }}
                    </p>
                    <h2 class="text-[22px] font-semibold text-white tracking-tight leading-tight">
                        {{ entityName }}
                    </h2>
                </div>
                <button
                    type="button"
                    class="shrink-0 -mr-2 -mt-1 rounded-lg p-1 text-white/45 hover:text-white hover:bg-white/10 transition-colors focus:outline-none focus:ring-2 focus:ring-white/20"
                    @click="emit('close')"
                >
                    <X class="w-5 h-5" />
                </button>
            </div>

            <div class="mt-5 grid grid-cols-3 border-y border-white/10">
                <div class="px-3 py-4 flex flex-col items-center justify-center">
                    <p
                        class="font-mono text-[28px] font-semibold tracking-tight tabular-nums leading-none"
                        :style="{ color: 'var(--win-accent)' }"
                    >
                        {{ attemptsCount }}
                    </p>
                    <p class="mt-2 text-[10.5px] font-medium uppercase tracking-[0.14em] text-white/45">
                        {{ t("win_stats.your_tries") }}
                    </p>
                </div>
                <div class="px-3 py-4 flex flex-col items-center justify-center border-l border-white/10">
                    <p class="font-mono text-[26px] font-semibold tracking-tight tabular-nums leading-none text-white">
                        <template v-if="best === null">—</template>
                        <template v-else>{{ best }}</template>
                    </p>
                    <p class="mt-2 text-[10.5px] font-medium uppercase tracking-[0.14em] text-white/45">
                        {{ t("win_stats.best") }}
                    </p>
                </div>
                <div class="px-3 py-4 flex flex-col items-center justify-center border-l border-white/10">
                    <p class="font-mono text-[26px] font-semibold tracking-tight tabular-nums leading-none text-white">
                        <template v-if="average === null">—</template>
                        <template v-else>{{ average }}</template>
                    </p>
                    <p class="mt-2 text-[10.5px] font-medium uppercase tracking-[0.14em] text-white/45">
                        {{ t("win_stats.average") }}
                    </p>
                </div>
            </div>

            <div class="pt-6">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-[12px] font-medium uppercase tracking-[0.18em] text-white/45">
                        {{ t("win_stats.distribution_title") }}
                    </h3>
                    <span v-if="fasterThan !== null" class="font-mono text-[12px] tabular-nums text-white/45">
                        {{ t("win_stats.faster_than", { percent: fasterThan }) }}
                    </span>
                </div>
                <div class="space-y-1.5">
                    <div
                        v-for="(b, i) in buckets"
                        :key="b.key"
                        class="flex items-center gap-3"
                    >
                        <div
                            class="w-12 font-mono text-[11px] tabular-nums text-right"
                            :class="i === yourBucketIndex ? 'text-[color:var(--win-accent)]' : 'text-white/45'"
                        >
                            {{ b.label }}
                        </div>
                        <div class="flex-1 h-[18px] relative">
                            <div
                                class="absolute inset-y-0 left-0 rounded-[3px]"
                                :style="{
                                    width: `${(b.share / Math.max(maxShare, 1)) * 100}%`,
                                    background: i === yourBucketIndex ? 'var(--win-accent)' : 'rgba(255,255,255,0.10)',
                                }"
                            ></div>
                            <div
                                v-if="i === yourBucketIndex"
                                class="absolute -top-[2px] -bottom-[2px] w-[2px] bg-white"
                                :style="{
                                    left: `${(b.share / Math.max(maxShare, 1)) * 100}%`,
                                    transform: 'translateX(-1px)',
                                }"
                            ></div>
                        </div>
                        <div
                            class="w-12 font-mono text-[11px] tabular-nums text-right"
                            :class="i === yourBucketIndex ? 'text-white' : 'text-white/55'"
                        >
                            {{ b.share.toFixed(1) }}%
                        </div>
                    </div>
                </div>
            </div>

            <div class="pt-5">
                <div class="rounded-xl px-4 py-4 bg-black/25 border border-white/10">
                    <pre class="font-mono text-[12.5px] leading-[1.55] whitespace-pre-wrap wrap-break-word text-center text-white/85">{{ shareText }}</pre>
                </div>
                <div class="mt-4 flex items-center justify-end">
                    <button
                        type="button"
                        class="inline-flex items-center gap-2 px-4 h-10 rounded-full text-[13px] font-semibold tracking-tight transition-colors border"
                        :class="copied
                            ? 'border-(--win-accent)/40 text-(--win-accent) bg-(--win-accent)/10'
                            : 'border-white/10 text-white bg-white/4 hover:bg-white/8'"
                        @click="copyShare"
                    >
                        <Check v-if="copied" class="w-4 h-4" />
                        <Copy v-else class="w-4 h-4" />
                        <span class="tabular-nums">
                            {{ copied ? t("share.copied") : t("win_stats.copy") }}
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </AppModal>
</template>

<style scoped>
.win-stats {
    --win-accent: var(--color-mode-classic);
}
.win-stats[data-accent="classic"]   { --win-accent: var(--color-mode-classic); }
.win-stats[data-accent="screens"]   { --win-accent: var(--color-mode-screens); }
.win-stats[data-accent="character"] { --win-accent: var(--color-mode-character); }
</style>
