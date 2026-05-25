<script setup lang="ts">
import { computed, ref, watch } from "vue";
import { useI18n } from "vue-i18n";
import { MousePointerClick } from "lucide-vue-next";
import AppModal from "@/vue/components/ui/AppModal.vue";
import PersonalDailyWinsChart from "@/vue/components/shared/PersonalDailyWinsChart.vue";
import { usePersonalStats, type PersonalMode } from "@/vue/composables/usePersonalStats";

type RangeKey = "30" | "90" | "365";

const props = defineProps<{
    isOpen: boolean;
}>();

const emit = defineEmits<{
    (e: "close"): void;
}>();

const { t } = useI18n();
const { getViewModel, refreshDerivedStats } = usePersonalStats();

const viewModel = computed(() => getViewModel());

const hasDailyAttempts = computed(() =>
    Object.values(viewModel.value.daily_attempts).some((day) =>
        Object.values(day).some((attempts) => Number(attempts) > 0),
    ),
);

const MODE_OPTIONS: Array<{ key: PersonalMode; color: string; labelKey: string }> = [
    { key: "classic",          color: "#14B8A6", labelKey: "modes.classic.title" },
    { key: "game_screenshots", color: "#F59E0B", labelKey: "modes.game_screenshots.title" },
    { key: "character",        color: "#8B5CF6", labelKey: "modes.character.title" },
];

const RANGES: Array<{ key: RangeKey; labelKey: string }> = [
    { key: "30",  labelKey: "personal_stats.range_30d" },
    { key: "90",  labelKey: "personal_stats.range_90d" },
    { key: "365", labelKey: "personal_stats.range_1y" },
];

const selectedMode = ref<PersonalMode>("classic");
const selectedRange = ref<RangeKey>("30");

watch(
    () => props.isOpen,
    (isOpen) => {
        if (isOpen) {
            refreshDerivedStats();
        }
    },
);
</script>

<template>
    <AppModal
        :is-open="props.isOpen"
        :title="t('personal_stats.title')"
        size="7xl"
        @close="emit('close')"
    >
        <div class="space-y-7">
            <div
                class="grid grid-cols-2 sm:grid-cols-5 border-y border-white/10 divide-y sm:divide-y-0 sm:divide-x divide-white/10"
            >
                <div class="px-3 py-4 sm:py-5 text-center">
                    <p class="text-3xl font-semibold tracking-tight tabular-nums text-white leading-none">
                        {{ viewModel.wins_total }}
                    </p>
                    <p class="mt-2 text-[11px] font-medium uppercase tracking-wider text-muted">
                        {{ t('personal_stats.wins_total') }}
                    </p>
                </div>
                <div class="px-3 py-4 sm:py-5 text-center">
                    <p class="text-3xl font-semibold tracking-tight tabular-nums text-white leading-none">
                        {{ viewModel.average_attempts }}
                    </p>
                    <p class="mt-2 text-[11px] font-medium uppercase tracking-wider text-muted">
                        {{ t('personal_stats.avg_attempts') }}
                    </p>
                </div>
                <div class="px-3 py-4 sm:py-5 text-center">
                    <p class="text-3xl font-semibold tracking-tight tabular-nums text-white leading-none">
                        {{ viewModel.first_try_wins }}
                    </p>
                    <p class="mt-2 text-[11px] font-medium uppercase tracking-wider text-muted">
                        {{ t('personal_stats.first_try_wins') }}
                    </p>
                </div>
                <div class="px-3 py-4 sm:py-5 text-center">
                    <p class="text-3xl font-semibold tracking-tight tabular-nums text-white leading-none">
                        {{ viewModel.current_streak_days }}
                    </p>
                    <p class="mt-2 text-[11px] font-medium uppercase tracking-wider text-muted">
                        {{ t('personal_stats.current_streak') }}
                    </p>
                </div>
                <div class="px-3 py-4 sm:py-5 text-center">
                    <p class="text-3xl font-semibold tracking-tight tabular-nums text-white leading-none">
                        {{ viewModel.best_streak_days }}
                    </p>
                    <p class="mt-2 text-[11px] font-medium uppercase tracking-wider text-muted">
                        {{ t('personal_stats.best_streak') }}
                    </p>
                </div>
            </div>

            <div v-if="hasDailyAttempts" class="space-y-3">
                <h3 class="text-[13px] font-medium uppercase tracking-wider text-muted">
                    {{ t("personal_stats.daily_attempts") }}
                </h3>

                <div class="rounded-xl border border-white/10 bg-white/3 p-3 sm:p-4">
                    <p class="hidden md:inline-flex items-center gap-2 text-[12px] text-muted font-medium mb-3">
                        {{ t('personal_stats.focus_hint') }}
                        <template v-if="selectedRange === '365'">
                            · {{ t('personal_stats.weekly_averages') }}
                        </template>
                    </p>
                    <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                        <div
                            class="inline-flex p-1 bg-black/25 border border-white/5 rounded-full"
                            role="tablist"
                            :aria-label="t('personal_stats.pick_mode')"
                        >
                            <button
                                v-for="mode in MODE_OPTIONS"
                                :key="mode.key"
                                type="button"
                                role="tab"
                                :aria-pressed="selectedMode === mode.key"
                                @click="selectedMode = mode.key"
                                class="inline-flex items-center gap-2 py-[7px] pl-3 pr-[14px] rounded-full text-[13px] font-semibold tracking-tight transition-colors duration-150"
                                :class="selectedMode === mode.key
                                    ? 'bg-white/10 text-white'
                                    : 'text-white/65 hover:text-white'"
                            >
                                <span
                                    class="w-2.5 h-2.5 rounded-full transition-shadow duration-150"
                                    :style="{
                                        background: mode.color,
                                        boxShadow: selectedMode === mode.key
                                            ? `0 0 0 3px ${mode.color}38`
                                            : 'none',
                                    }"
                                />
                                {{ t(mode.labelKey) }}
                            </button>
                        </div>

                        <div
                            class="inline-flex p-[3px] bg-black/25 border border-white/5 rounded-full"
                            role="tablist"
                            :aria-label="t('personal_stats.pick_range')"
                        >
                            <button
                                v-for="r in RANGES"
                                :key="r.key"
                                type="button"
                                role="tab"
                                :aria-pressed="selectedRange === r.key"
                                @click="selectedRange = r.key"
                                class="px-3.5 py-1.5 rounded-full font-mono text-xs font-semibold tracking-wider transition-colors duration-150"
                                :class="selectedRange === r.key
                                    ? 'bg-white/10 text-white'
                                    : 'text-white/55 hover:text-white'"
                            >
                                {{ t(r.labelKey) }}
                            </button>
                        </div>
                    </div>

                    <PersonalDailyWinsChart
                        :daily-attempts="viewModel.daily_attempts"
                        :mode="selectedMode"
                        :range="selectedRange"
                        :color="MODE_OPTIONS.find((m) => m.key === selectedMode)?.color ?? '#14B8A6'"
                    />
                </div>
            </div>

            <p v-else class="text-base text-muted">
                {{ t("personal_stats.empty") }}
            </p>
        </div>
    </AppModal>
</template>
