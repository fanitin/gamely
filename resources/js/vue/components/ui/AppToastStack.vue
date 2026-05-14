<script setup lang="ts">
import { computed } from "vue";
import { CheckCircle2, CircleAlert, Info, TriangleAlert, X } from "lucide-vue-next";
import { useToast, type ToastItem } from "@/vue/composables/useToast";

const { toasts, dismissToast } = useToast();

const iconByType = {
    success: CheckCircle2,
    info: Info,
    warning: TriangleAlert,
    error: CircleAlert,
};

const toneByType = {
    success: "border-success-500/40 bg-success-500/10 text-success-400",
    info: "border-primary-500/40 bg-primary-500/10 text-primary-400",
    warning: "border-warning-500/40 bg-warning-500/10 text-warning-400",
    error: "border-danger-500/40 bg-danger-500/10 text-danger-400",
};

const getIcon = (toast: ToastItem) => iconByType[toast.type];
const getTone = (toast: ToastItem) => toneByType[toast.type];
const hasToasts = computed(() => toasts.length > 0);
</script>

<template>
    <div
        v-if="hasToasts"
        class="fixed top-4 right-4 z-[120] w-[min(92vw,24rem)] pointer-events-none"
        aria-live="polite"
        aria-atomic="true"
    >
        <TransitionGroup
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0 translate-y-2"
            enter-to-class="opacity-100 translate-y-0"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100 translate-y-0"
            leave-to-class="opacity-0 translate-y-2"
            move-class="transition duration-150"
            tag="div"
            class="space-y-2"
        >
            <div
                v-for="toast in toasts"
                :key="toast.id"
                class="pointer-events-auto border rounded-xl px-4 py-3 backdrop-blur-md shadow-lg bg-onyx-dark/90 border-white/10"
                :class="getTone(toast)"
                role="status"
            >
                <div class="flex items-start gap-3">
                    <component :is="getIcon(toast)" class="w-5 h-5 mt-0.5 shrink-0" />
                    <div class="min-w-0 flex-1">
                        <p v-if="toast.title" class="text-sm font-semibold text-white">
                            {{ toast.title }}
                        </p>
                        <p class="text-sm leading-5 text-white/90 break-words">
                            {{ toast.message }}
                        </p>
                    </div>
                    <button
                        type="button"
                        class="text-white/60 hover:text-white transition-colors"
                        @click="dismissToast(toast.id)"
                        aria-label="Close notification"
                    >
                        <X class="w-4 h-4" />
                    </button>
                </div>
            </div>
        </TransitionGroup>
    </div>
</template>
