<script setup lang="ts">
import { computed, onMounted, ref } from "vue";
import { useI18n } from "vue-i18n";
import { BarChart3, Trophy } from "lucide-vue-next";
import AppButton from "@/vue/components/ui/AppButton.vue";
import { useStats, type ModeValue } from "@/vue/composables/useStats";

type ModeKey = "classic" | "screens" | "character";

interface Props {
    mode: ModeValue;
    entityName: string;
    attemptsCount: number;
    coverUrl?: string | null;
    challengeDate: string;
}

const props = defineProps<Props>();
const emit = defineEmits<{ (e: "open-stats"): void }>();

const { t } = useI18n();
const { getModeDistribution } = useStats();

const MODE_KEY: Record<ModeValue, ModeKey> = {
    classic: "classic",
    game_screenshots: "screens",
    character: "character",
};
const modeKey = computed(() => MODE_KEY[props.mode]);

const styles = computed(() => {
    const map: Record<
        ModeKey,
        { edge: string; eyebrow: string; ring: string; button: string }
    > = {
        classic: {
            edge: "before:bg-mode-classic",
            eyebrow: "text-mode-classic",
            ring: "ring-mode-classic/40",
            button: "bg-mode-classic hover:bg-mode-classic/90 shadow-mode-classic/20",
        },
        screens: {
            edge: "before:bg-mode-screens",
            eyebrow: "text-mode-screens",
            ring: "ring-mode-screens/40",
            button: "bg-mode-screens hover:bg-mode-screens/90 shadow-mode-screens/20",
        },
        character: {
            edge: "before:bg-mode-character",
            eyebrow: "text-mode-character",
            ring: "ring-mode-character/40",
            button: "bg-mode-character hover:bg-mode-character/90 shadow-mode-character/20",
        },
    };
    return map[modeKey.value];
});

const coverClass = computed(() =>
    props.mode === "character" ? "w-24 h-24" : "w-20 h-28",
);

const fasterThan = ref<number | null>(null);
onMounted(async () => {
    try {
        const data = await getModeDistribution(props.mode, props.challengeDate);
        const bins = Array.isArray(data.bins) ? data.bins : [];
        const total = Number(data.total_players) || 0;
        if (total > 0) {
            let worse = 0;
            for (const b of bins) {
                if (b.attempts > props.attemptsCount) worse += b.players;
            }
            fasterThan.value = Math.round((worse / total) * 100);
        }
    } catch {
        fasterThan.value = null;
    }
});
</script>

<template>
    <div
        class="relative overflow-hidden bg-onyx-dark/90 border border-white/10 rounded-2xl p-5 sm:p-6 lg:p-7 mb-8 animate-fade-in before:content-[''] before:absolute before:left-0 before:top-[12%] before:bottom-[12%] before:w-[2px] before:rounded-full"
        :class="styles.edge"
    >
        <div class="flex flex-col sm:flex-row sm:items-center gap-5 sm:gap-6">
            <div class="flex items-center gap-4 sm:gap-5 flex-1 min-w-0">
                <img
                    v-if="coverUrl"
                    :src="coverUrl"
                    :alt="entityName"
                    class="shrink-0 object-cover rounded-xl ring-1"
                    :class="[coverClass, styles.ring]"
                />
                <div class="min-w-0">
                    <p
                        class="flex items-center gap-1.5 font-mono text-[11px] uppercase tracking-[0.2em] mb-1.5"
                        :class="styles.eyebrow"
                    >
                        {{ t("win.you_won") }}
                    </p>
                    <h2
                        class="text-2xl sm:text-3xl font-bold text-white tracking-tight leading-tight truncate"
                    >
                        {{ entityName }}
                    </h2>
                    <p class="text-sm text-muted mt-2">
                        {{ t("win.solved_in", { count: attemptsCount }) }}
                        <span v-if="fasterThan !== null" class="text-white/40">
                            · {{ t("win_stats.faster_than", { percent: fasterThan }) }}
                        </span>
                    </p>
                </div>
            </div>

            <AppButton
                variant="base"
                class="shrink-0 w-full sm:w-auto gap-2 py-3 text-onyx-dark shadow-lg"
                :class="styles.button"
                @click="emit('open-stats')"
            >
                {{ t("win.today_stats") }}
            </AppButton>
        </div>
    </div>
</template>

<style scoped>
@keyframes fade-in {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fade-in {
    animation: fade-in 0.3s ease-out;
}
</style>
