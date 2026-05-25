<script setup lang="ts">
import { computed } from "vue";
import { ArrowUp, ArrowDown } from "lucide-vue-next";

interface MultiValue {
    label: string;
    value: string;
}

const props = defineProps<{
    result: "exact" | "close" | "wrong" | "missing";
    value?: string | number | Array<MultiValue>;
    arrow?: "up" | "down";
}>();

const bgColor = computed(() => {
    switch (props.result) {
        case "exact":
            return "bg-forest-500 border-forest-600 shadow-[inset_0_0_0_2px_var(--color-forest-400)]";
        case "close":
            return "bg-gold-500 border-gold-600 shadow-[inset_0_0_0_2px_var(--color-gold-400)]";
        case "wrong":
            return "bg-crimson-500 border-crimson-600 shadow-[inset_0_0_0_2px_var(--color-crimson-400)]";
        case "missing":
            return "bg-onyx-light border-onyx-dark shadow-[inset_0_0_0_2px_rgba(255,255,255,0.08)]";
        default:
            return "bg-crimson-500 border-crimson-600 shadow-[inset_0_0_0_2px_var(--color-crimson-400)]";
    }
});

const bgArrowOverlay = computed(() => {
    if (!props.arrow) return "";

    switch (props.result) {
        case "exact":
            return "bg-forest-600/30";
        case "close":
            return "bg-gold-600/30";
        case "wrong":
            return "bg-crimson-600/30";
        default:
            return "bg-crimson-600/30";
    }
});

const isMultiValue = computed(() => Array.isArray(props.value));

const displayValue = computed(() => {
    if (!props.value) return "-";
    if (typeof props.value === 'string' || typeof props.value === 'number') {
        return props.value.toString();
    }
    return "-";
});
</script>

<template>
    <div
        :class="[
            bgColor,
            'relative border-[3px] p-3 text-white transition-all duration-300 min-h-[80px] flex items-center justify-center overflow-hidden',
        ]"
    >
        <div
            v-if="arrow"
            :class="[
                bgArrowOverlay,
                'absolute inset-0 flex items-center justify-center pointer-events-none'
            ]"
        >
            <ArrowUp v-if="arrow === 'up'" class="w-20 h-20 opacity-15" />
            <ArrowDown v-if="arrow === 'down'" class="w-20 h-20 opacity-15" />
        </div>

        <div v-if="isMultiValue" class="relative z-10 w-full max-w-[85%] mx-auto space-y-2">
            <div
                v-for="(item, index) in (value as Array<MultiValue>)"
                :key="index"
                class="text-sm leading-snug"
            >
                <span class="text-white/70 font-medium">{{ item.label }}:</span>
                <span class="ml-1.5 font-bold">{{ item.value }}</span>
            </div>
        </div>

        <div v-else class="relative z-10 text-sm font-bold text-center w-full max-w-[85%] mx-auto break-words leading-snug">
            {{ displayValue }}
        </div>
    </div>
</template>
