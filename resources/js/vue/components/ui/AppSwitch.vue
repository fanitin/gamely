<script setup lang="ts">
import { computed } from "vue";

const props = withDefaults(
    defineProps<{
        checked: boolean;
        disabled?: boolean;
        size?: "sm" | "md";
        ariaLabel?: string;
    }>(),
    {
        disabled: false,
        size: "md",
        ariaLabel: "",
    },
);

const emit = defineEmits<{
    (e: "update:checked", value: boolean): void;
}>();

const trackSize = computed(() =>
    props.size === "sm" ? "w-9 h-5" : "w-11 h-6",
);
const thumbSize = computed(() =>
    props.size === "sm" ? "w-4 h-4" : "w-5 h-5",
);
const thumbTranslate = computed(() =>
    props.size === "sm" ? "translate-x-4" : "translate-x-5",
);

const onToggle = () => {
    if (props.disabled) return;
    emit("update:checked", !props.checked);
};
</script>

<template>
    <button
        type="button"
        role="switch"
        :aria-checked="checked"
        :aria-label="ariaLabel"
        :disabled="disabled"
        class="relative shrink-0 rounded-full transition-colors duration-200 outline-none focus-visible:ring-2 focus-visible:ring-teal-500/50"
        :class="[
            trackSize,
            checked ? 'bg-teal-500' : 'bg-onyx-light',
            disabled ? 'opacity-60 cursor-not-allowed' : 'cursor-pointer',
        ]"
        @click="onToggle"
    >
        <span
            class="absolute top-0.5 left-0.5 rounded-full bg-white shadow-sm transition-transform duration-200"
            :class="[thumbSize, checked ? thumbTranslate : 'translate-x-0']"
        />
    </button>
</template>
