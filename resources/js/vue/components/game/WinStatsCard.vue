<script setup lang="ts">
import { computed, onMounted, ref } from "vue";
import { useI18n } from "vue-i18n";
import { Trophy, Copy } from "lucide-vue-next";
import { useStats, type ModeValue } from "@/vue/composables/useStats";
import { getComparisonKeysForMode } from "@/vue/composables/useComparisonKeys";

type ComparisonResult = { result: "exact" | "close" | "wrong" };
type AttemptLike = { comparison: Record<string, ComparisonResult> };

interface Props {
    mode: ModeValue;
    attemptsCount: number;
    attempts: AttemptLike[];
    entityName: string;
    challengeDate: string;
}

const props = defineProps<Props>();
const { t } = useI18n();
const { getModeDistribution } = useStats();

const bins = ref<Array<{ attempts: number; players: number }>>([]);
const totalPlayers = ref(0);
const copied = ref(false);

const maxPlayersInBin = computed(() => Math.max(...bins.value.map((b) => b.players), 1));

const modeLabel = computed(() => t(`modes.${props.mode}.title`));
const shareLink = computed(() => `${window.location.origin}/${props.mode === "game_screenshots" ? "screenshots" : props.mode}`);

const emojiMap: Record<ComparisonResult["result"], string> = {
    exact: "🟩",
    close: "🟧",
    wrong: "🟥",
};

const rows = computed(() =>
    props.attempts.map((attempt) =>
        getComparisonKeysForMode(props.mode, Object.keys(attempt.comparison))
            .map((key) => attempt.comparison[key])
            .filter((cell): cell is ComparisonResult => Boolean(cell))
            .map((cell) => emojiMap[cell.result] ?? "🟥")
            .join(""),
    )
);

const visibleRows = computed(() => [...rows.value].reverse().slice(0, 5));
const extraAttempts = computed(() => Math.max(rows.value.length - visibleRows.value.length, 0));

const shareText = computed(() => {
    const header = t("win_stats.share_header", {
        date: props.challengeDate,
        name: props.entityName,
        mode: modeLabel.value,
        attempts: props.attemptsCount,
    });
    const extras = extraAttempts.value > 0 ? `\n➕(${extraAttempts.value})` : "";
    return `${header}\n${visibleRows.value.join("\n")}${extras}\n${shareLink.value}`;
});

const copyShare = async () => {
    await navigator.clipboard.writeText(shareText.value);
    copied.value = true;
    setTimeout(() => {
        copied.value = false;
    }, 1400);
};

onMounted(async () => {
    const data = await getModeDistribution(props.mode, props.challengeDate);
    bins.value = data.bins;
    totalPlayers.value = data.total_players;
});
</script>

<template>
    <div class="mb-6 bg-success-500/10 border border-success-500/30 rounded-xl p-6 space-y-5 animate-fade-in">
        <div class="text-center space-y-2">
            <Trophy class="w-12 h-12 text-success-500 mx-auto" />
            <h2 class="text-2xl font-bold text-white">{{ t("game.you_won") }}!</h2>
            <p class="text-muted">{{ t("game.attempts_count", { count: attemptsCount }) }}</p>
        </div>

        <div class="space-y-2">
            <h3 class="text-sm uppercase tracking-wide text-muted">{{ t("win_stats.distribution_title") }}</h3>
            <div class="space-y-2">
                <div v-for="bin in bins" :key="bin.attempts" class="flex items-center gap-3">
                    <div class="w-8 text-xs text-muted">{{ bin.attempts }}</div>
                    <div class="flex-1 bg-white/5 rounded h-6 overflow-hidden">
                        <div class="h-full bg-success-500/80" :style="{ width: `${(bin.players / maxPlayersInBin) * 100}%` }"></div>
                    </div>
                    <div class="w-10 text-right text-sm text-white">{{ bin.players }}</div>
                </div>
            </div>
            <p class="text-xs text-muted">{{ t("win_stats.total_players", { count: totalPlayers }) }}</p>
        </div>

        <div class="rounded-lg bg-white/5 border border-white/10 p-4 space-y-3">
            <pre class="text-xs sm:text-sm whitespace-pre-wrap text-white leading-relaxed">{{ shareText }}</pre>
            <button type="button" class="w-full bg-primary-600 hover:bg-primary-500 text-white font-bold py-3 rounded-xl transition-all inline-flex items-center justify-center gap-2" @click="copyShare">
                <Copy class="w-4 h-4" />
                {{ copied ? t("share.copied") : t("win_stats.copy") }}
            </button>
        </div>
    </div>
</template>
