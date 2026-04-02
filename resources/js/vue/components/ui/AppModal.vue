<script setup>
import {
    Dialog,
    DialogPanel,
    DialogTitle,
    TransitionRoot,
    TransitionChild,
} from "@headlessui/vue";
import { X } from "lucide-vue-next";

const props = defineProps({
    isOpen: {
        type: Boolean,
        required: true,
    },
    title: {
        type: String,
        default: "",
    },
    size: {
        type: String,
        default: "md", // sm, md, lg, xl, 2xl
    },
});

defineEmits(["close"]);

const sizeClasses = {
    sm: "max-w-sm",
    md: "max-w-md",
    lg: "max-w-lg",
    xl: "max-w-xl",
    "2xl": "max-w-2xl",
};
</script>

<template>
    <TransitionRoot appear :show="isOpen" as="template">
        <Dialog as="div" @close="$emit('close')" class="relative z-50">
            <TransitionChild
                as="template"
                enter="duration-150 ease-out"
                enter-from="opacity-0"
                enter-to="opacity-100"
                leave="duration-100 ease-in"
                leave-from="opacity-100"
                leave-to="opacity-0"
            >
                <div class="fixed inset-0 bg-black/60 backdrop-blur-xs" />
            </TransitionChild>

            <div class="fixed inset-0 overflow-y-auto">
                <div
                    class="flex min-h-full items-center justify-center p-4 text-center"
                >
                    <TransitionChild
                        as="template"
                        enter="duration-150 ease-out"
                        enter-from="opacity-0 scale-95"
                        enter-to="opacity-100 scale-100"
                        leave="duration-100 ease-in"
                        leave-from="opacity-100 scale-100"
                        leave-to="opacity-0 scale-95"
                    >
                        <DialogPanel
                            :class="[
                                'w-full transform overflow-visible rounded-2xl bg-slate-900/95 border border-white/10 p-8 text-left align-middle shadow-2xl backdrop-blur-md',
                                sizeClasses[props.size] || sizeClasses.md,
                            ]"
                        >
                            <div class="flex items-center justify-between mb-6">
                                <DialogTitle
                                    as="h3"
                                    class="text-xl font-medium leading-6 text-white"
                                >
                                    {{ title }}
                                </DialogTitle>
                                <button
                                    type="button"
                                    class="rounded-lg p-1 text-slate-400 hover:text-white hover:bg-white/10 transition-colors focus:outline-none focus:ring-2 focus:ring-emerald-500/50"
                                    @click="$emit('close')"
                                >
                                    <X class="w-6 h-6" />
                                </button>
                            </div>

                            <slot />
                        </DialogPanel>
                    </TransitionChild>
                </div>
            </div>
        </Dialog>
    </TransitionRoot>
</template>
