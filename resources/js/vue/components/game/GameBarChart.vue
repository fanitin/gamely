<script setup lang="ts">
import { computed } from "vue";
import { useI18n } from "vue-i18n";

interface Props {
    bins: Array<{ attempts: number; players: number }>;
    totalPlayers: number;
}

const props = defineProps<Props>();
const { t } = useI18n();

const maxPlayersInBin = computed(() => Math.max(...props.bins.map((b) => b.players), 1));
</script>

<template>
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
</template>
