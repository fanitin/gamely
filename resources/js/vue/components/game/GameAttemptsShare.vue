<script setup lang="ts">
import { computed, ref } from "vue";
import { useI18n } from "vue-i18n";
import { Copy } from "lucide-vue-next";
import type { ModeValue } from "@/vue/composables/useStats";
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

const copied = ref(false);

const modeLabel = computed(() => t(`modes.${props.mode}.title`));
const shareLink = computed(() => `${window.location.origin}`);

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
    ),
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
    const extras =
        extraAttempts.value > 0 ? `\n➕(${extraAttempts.value})` : "";
    return `${header}\n${visibleRows.value.join("\n")}${extras}\n${shareLink.value}`;
});

const copyShare = async () => {
    await navigator.clipboard.writeText(shareText.value);
    copied.value = true;
    setTimeout(() => {
        copied.value = false;
    }, 1400);
};
</script>

<template>
    <div class="rounded-xl bg-white/5 border border-white/10 p-4 space-y-4">
        <div class="rounded-lg border border-white/10 bg-black/20 px-4 py-3">
            <pre
                class="text-xs sm:text-sm whitespace-pre-wrap text-white leading-relaxed text-center font-mono tracking-wide"
                >{{ shareText }}</pre
            >
        </div>
        <button
            type="button"
            class="w-full bg-primary-600 hover:bg-primary-500 text-white font-bold py-3 rounded-xl transition-all inline-flex items-center justify-center gap-2"
            @click="copyShare"
        >
            <Copy class="w-4 h-4" />
            {{ copied ? t("share.copied") : t("win_stats.copy") }}
        </button>
    </div>
</template>
