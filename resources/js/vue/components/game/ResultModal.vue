<script setup lang="ts">
import { ref, watch } from "vue";
import { useI18n } from "vue-i18n";
import {
    Dialog,
    DialogPanel,
    DialogTitle,
    TransitionChild,
    TransitionRoot,
} from "@headlessui/vue";
import { Trophy, X } from "lucide-vue-next";
import { useStats, type ModeValue } from "@/vue/composables/useStats";
import GameBarChart from "./GameBarChart.vue";
import GameAttemptsShare from "./GameAttemptsShare.vue";

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

const emit = defineEmits<{
    (e: "close"): void;
}>();

const { t } = useI18n();
const { getModeDistribution } = useStats();

const bins = ref<Array<{ attempts: number; players: number }>>([]);
const totalPlayers = ref(0);
const isStatsLoaded = ref(false);

watch(() => props.show, async (newShow) => {
    if (newShow && !isStatsLoaded.value) {
        const data = await getModeDistribution(props.mode, props.challengeDate);
        bins.value = data.bins;
        totalPlayers.value = data.total_players;
        isStatsLoaded.value = true;
    }
});
</script>

<template>
    <TransitionRoot as="template" :show="props.show">
        <Dialog as="div" class="relative z-50" @close="emit('close')">
            <TransitionChild
                as="template"
                enter="ease-out duration-300"
                enter-from="opacity-0"
                enter-to="opacity-100"
                leave="ease-in duration-200"
                leave-from="opacity-100"
                leave-to="opacity-0"
            >
                <div class="fixed inset-0 bg-black/80 backdrop-blur-sm transition-opacity" />
            </TransitionChild>

            <div class="fixed inset-0 z-10 overflow-y-auto">
                <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                    <TransitionChild
                        as="template"
                        enter="ease-out duration-300"
                        enter-from="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        enter-to="opacity-100 translate-y-0 sm:scale-100"
                        leave="ease-in duration-200"
                        leave-from="opacity-100 translate-y-0 sm:scale-100"
                        leave-to="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    >
                        <DialogPanel class="relative transform overflow-hidden rounded-3xl bg-onyx-light border border-white/10 px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:p-8">
                            <div class="absolute right-0 top-0 pr-4 pt-4 z-10">
                                <button
                                    type="button"
                                    class="rounded-full bg-white/5 p-2 text-muted hover:text-white hover:bg-white/10 transition-all focus:outline-none"
                                    @click="emit('close')"
                                >
                                    <X class="h-6 w-6" />
                                </button>
                            </div>

                            <div class="space-y-6">
                                <div class="text-center space-y-2">
                                    <Trophy class="w-12 h-12 text-success-500 mx-auto" />
                                    <DialogTitle as="h3" class="text-2xl font-bold text-white uppercase tracking-tighter">
                                        {{ t("game.you_won") }}!
                                    </DialogTitle>
                                    <p class="text-muted">{{ t("game.attempts_count", { count: attemptsCount }) }}</p>
                                </div>

                                <GameBarChart
                                    v-if="isStatsLoaded"
                                    :bins="bins"
                                    :total-players="totalPlayers"
                                />

                                <GameAttemptsShare
                                    :mode="mode"
                                    :attempts-count="attemptsCount"
                                    :attempts="attempts"
                                    :entity-name="entityName"
                                    :challenge-date="challengeDate"
                                />
                            </div>
                        </DialogPanel>
                    </TransitionChild>
                </div>
            </div>
        </Dialog>
    </TransitionRoot>
</template>
