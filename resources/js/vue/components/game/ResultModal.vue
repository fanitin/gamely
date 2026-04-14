<script setup lang="ts">
import { computed } from "vue";
import { useI18n } from "vue-i18n";
import {
    Dialog,
    DialogPanel,
    DialogTitle,
    TransitionChild,
    TransitionRoot,
} from "@headlessui/vue";
import { Share2, X } from "lucide-vue-next";
import type { GameAttempt, GameStatus, CellStatus } from "../../types/game";

interface Props {
    show: boolean;
    gameState: GameStatus;
    attempts: GameAttempt[];
}

const props = defineProps<Props>();

const emit = defineEmits<{
    (e: "close"): void;
}>();

const { t } = useI18n();

const isWin = computed(() => props.gameState === "WON");

const share = () => {
    const emojiMap: Record<CellStatus, string> = {
        correct: "🟩",
        partial: "🟨",
        wrong: "⬜",
    };

    let text = `GameWordle #Daily\n`;
    const shareAttempts = [...props.attempts].reverse();

    text += shareAttempts
        .map((a) => a.cells.map((c) => emojiMap[c.status]).join(""))
        .join("\n");

    text += "\nhttps://gamely.app";

    navigator.clipboard.writeText(text);
    alert(t("share.copied"));
};
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
                <div
                    class="fixed inset-0 bg-black/80 backdrop-blur-sm transition-opacity"
                />
            </TransitionChild>

            <div class="fixed inset-0 z-10 overflow-y-auto">
                <div
                    class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0"
                >
                    <TransitionChild
                        as="template"
                        enter="ease-out duration-300"
                        enter-from="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        enter-to="opacity-100 translate-y-0 sm:scale-100"
                        leave="ease-in duration-200"
                        leave-from="opacity-100 translate-y-0 sm:scale-100"
                        leave-to="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    >
                        <DialogPanel
                            class="relative transform overflow-hidden rounded-3xl bg-onyx-light border border-white/10 px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:p-8"
                        >
                            <div class="absolute right-0 top-0 pr-4 pt-4">
                                <button
                                    type="button"
                                    class="rounded-full bg-white/5 p-2 text-muted hover:text-white hover:bg-white/10 transition-all"
                                    @click="emit('close')"
                                >
                                    <X class="h-6 w-6" />
                                </button>
                            </div>

                            <div class="text-center">
                                <DialogTitle
                                    as="h3"
                                    class="text-4xl font-black mb-4 uppercase tracking-tighter"
                                >
                                    <span
                                        v-if="isWin"
                                        class="text-success-500"
                                        >{{ t("result.win_title") }}</span
                                    >
                                    <span v-else class="text-danger-500">{{
                                        t("result.lose_title")
                                    }}</span>
                                </DialogTitle>

                                <div class="mt-8 grid grid-cols-2 gap-4">
                                    <div
                                        class="bg-white/5 rounded-2xl p-6 border border-white/5"
                                    >
                                        <div
                                            class="text-3xl font-black text-white"
                                        >
                                            {{ props.attempts.length }}
                                        </div>
                                        <div
                                            class="text-xs text-muted uppercase tracking-widest mt-1"
                                        >
                                            {{ t("result.played") }}
                                        </div>
                                    </div>
                                    <div
                                        class="bg-white/5 rounded-2xl p-6 border border-white/5"
                                    >
                                        <div
                                            class="text-3xl font-black text-white"
                                        >
                                            100%
                                        </div>
                                        <div
                                            class="text-xs text-muted uppercase tracking-widest mt-1"
                                        >
                                            {{ t("result.win_rate") }}
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-8 flex gap-4">
                                    <button
                                        type="button"
                                        class="flex-1 bg-primary-600 hover:bg-primary-500 text-white font-bold py-4 rounded-2xl flex items-center justify-center gap-3 transition-all shadow-lg active:scale-95"
                                        @click="share"
                                    >
                                        <Share2 class="w-5 h-5" />
                                        {{ t("game.share") }}
                                    </button>
                                </div>
                            </div>
                        </DialogPanel>
                    </TransitionChild>
                </div>
            </div>
        </Dialog>
    </TransitionRoot>
</template>
