<script setup lang="ts">
import { computed } from "vue";
import { useI18n } from "vue-i18n";
import AppModal from "@/vue/components/ui/AppModal.vue";
import PersonalDailyWinsChart from "@/vue/components/shared/PersonalDailyWinsChart.vue";

type PersonalMode = "classic" | "game_screenshots" | "character";
type DailyAttempts = Record<string, Partial<Record<PersonalMode, number>>>;

const props = defineProps<{
    isOpen: boolean;
}>();

const emit = defineEmits<{
    (e: "close"): void;
}>();

const { t } = useI18n();

const formatDateKey = (date: Date): string => {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, "0");
    const day = String(date.getDate()).padStart(2, "0");
    return `${year}-${month}-${day}`;
};

const buildMockDailyAttempts = (): DailyAttempts => {
    const series: DailyAttempts = {};
    const start = new Date();
    start.setHours(0, 0, 0, 0);
    start.setDate(start.getDate() - 59);

    for (let index = 0; index < 60; index += 1) {
        const current = new Date(start.getTime());
        current.setDate(start.getDate() + index);

        const key = formatDateKey(current);
        const classic = ((index * 7) % 26) + 1;
        const screenshots = index % 3 === 0 ? ((index * 5) % 20) + 1 : undefined;
        const character = index % 2 === 0 ? ((index * 9) % 30) + 1 : undefined;

        series[key] = {
            classic,
            game_screenshots: screenshots,
            character,
        };
    }

    return series;
};

const dailyAttempts = buildMockDailyAttempts();

const summary = computed(() => {
    let winsTotal = 0;
    let attemptsSum = 0;
    let firstTryWins = 0;

    Object.values(dailyAttempts).forEach((day) => {
        Object.values(day).forEach((attempts) => {
            const value = Number(attempts) || 0;
            if (value <= 0) {
                return;
            }
            winsTotal += 1;
            attemptsSum += value;
            if (value === 1) {
                firstTryWins += 1;
            }
        });
    });

    return {
        winsTotal,
        averageAttempts: winsTotal > 0 ? Number((attemptsSum / winsTotal).toFixed(2)) : 0,
        firstTryWins,
        currentStreakDays: 12,
        bestStreakDays: 41,
    };
});
</script>

<template>
    <AppModal
        :is-open="props.isOpen"
        :title="`${t('personal_stats.title')} (DEV)`"
        size="7xl"
        @close="emit('close')"
    >
        <div class="space-y-7">
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-5">
                <div class="rounded-xl border border-white/10 bg-white/5 p-4">
                    <p class="text-sm uppercase tracking-wide text-muted">{{ t('personal_stats.wins_total') }}</p>
                    <p class="mt-2 text-3xl font-semibold text-white">{{ summary.winsTotal }}</p>
                </div>
                <div class="rounded-xl border border-white/10 bg-white/5 p-4">
                    <p class="text-sm uppercase tracking-wide text-muted">{{ t('personal_stats.avg_attempts') }}</p>
                    <p class="mt-2 text-3xl font-semibold text-white">{{ summary.averageAttempts }}</p>
                </div>
                <div class="rounded-xl border border-white/10 bg-white/5 p-4">
                    <p class="text-sm uppercase tracking-wide text-muted">{{ t('personal_stats.first_try_wins') }}</p>
                    <p class="mt-2 text-3xl font-semibold text-white">{{ summary.firstTryWins }}</p>
                </div>
                <div class="rounded-xl border border-white/10 bg-white/5 p-4">
                    <p class="text-sm uppercase tracking-wide text-muted">{{ t('personal_stats.current_streak') }}</p>
                    <p class="mt-2 text-3xl font-semibold text-white">{{ summary.currentStreakDays }}</p>
                </div>
                <div class="rounded-xl border border-white/10 bg-white/5 p-4">
                    <p class="text-sm uppercase tracking-wide text-muted">{{ t('personal_stats.best_streak') }}</p>
                    <p class="mt-2 text-3xl font-semibold text-white">{{ summary.bestStreakDays }}</p>
                </div>
            </div>

            <div class="space-y-3">
                <h3 class="text-base uppercase tracking-wide text-muted">{{ t("personal_stats.daily_attempts") }}</h3>
                <PersonalDailyWinsChart :daily-attempts="dailyAttempts" />
            </div>
        </div>
    </AppModal>
</template>
