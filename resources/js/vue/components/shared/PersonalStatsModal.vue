<script setup lang="ts">
import { computed, watch } from "vue";
import { useI18n } from "vue-i18n";
import AppModal from "@/vue/components/ui/AppModal.vue";
import PersonalDailyWinsChart from "@/vue/components/shared/PersonalDailyWinsChart.vue";
import { usePersonalStats } from "@/vue/composables/usePersonalStats";

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
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-5">
                <div class="rounded-xl border border-white/10 bg-white/5 p-4">
                    <p class="text-sm uppercase tracking-wide text-muted">{{ t('personal_stats.wins_total') }}</p>
                    <p class="mt-2 text-3xl font-semibold text-white">{{ viewModel.wins_total }}</p>
                </div>
                <div class="rounded-xl border border-white/10 bg-white/5 p-4">
                    <p class="text-sm uppercase tracking-wide text-muted">{{ t('personal_stats.avg_attempts') }}</p>
                    <p class="mt-2 text-3xl font-semibold text-white">{{ viewModel.average_attempts }}</p>
                </div>
                <div class="rounded-xl border border-white/10 bg-white/5 p-4">
                    <p class="text-sm uppercase tracking-wide text-muted">{{ t('personal_stats.first_try_wins') }}</p>
                    <p class="mt-2 text-3xl font-semibold text-white">{{ viewModel.first_try_wins }}</p>
                </div>
                <div class="rounded-xl border border-white/10 bg-white/5 p-4">
                    <p class="text-sm uppercase tracking-wide text-muted">{{ t('personal_stats.current_streak') }}</p>
                    <p class="mt-2 text-3xl font-semibold text-white">{{ viewModel.current_streak_days }}</p>
                </div>
                <div class="rounded-xl border border-white/10 bg-white/5 p-4">
                    <p class="text-sm uppercase tracking-wide text-muted">{{ t('personal_stats.best_streak') }}</p>
                    <p class="mt-2 text-3xl font-semibold text-white">{{ viewModel.best_streak_days }}</p>
                </div>
            </div>

            <div v-if="hasDailyAttempts" class="space-y-3">
                <h3 class="text-base uppercase tracking-wide text-muted">{{ t("personal_stats.daily_attempts") }}</h3>
                <PersonalDailyWinsChart :daily-attempts="viewModel.daily_attempts" />
            </div>
            <p v-else class="text-base text-muted">{{ t("personal_stats.empty") }}</p>
        </div>
    </AppModal>
</template>
