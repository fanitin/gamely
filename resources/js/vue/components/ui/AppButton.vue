<script setup>
import { computed } from "vue";

const props = defineProps({
    variant: {
        type: String,
        default: "primary",
    },
    size: {
        type: String,
        default: "md",
    },
    disabled: {
        type: Boolean,
        default: false,
    },
    loading: {
        type: Boolean,
        default: false,
    },
    as: {
        type: [String, Object],
        default: "button",
    },
    fullWidth: {
        type: Boolean,
        default: false,
    },
});

const baseClasses =
    "inline-flex items-center justify-center font-bold tracking-wide rounded-xl transition-all active:scale-95 disabled:opacity-50 disabled:pointer-events-none";

const variantClasses = {
    primary:
        "bg-teal-500 hover:bg-teal-400 active:bg-teal-600 text-white shadow-lg shadow-teal-500/20",
    secondary:
        "bg-indigo-500 hover:bg-indigo-400 active:bg-indigo-600 text-white shadow-lg shadow-indigo-500/20",
    ghost: "bg-transparent hover:bg-white/10 text-muted hover:text-white",
    danger: "bg-crimson-500 hover:bg-crimson-400 active:bg-crimson-600 text-white shadow-lg shadow-crimson-500/20",
    outline:
        "bg-transparent border-2 border-onyx-light hover:border-teal-500 text-muted hover:text-white",
};

const sizeClasses = {
    sm: "px-3 py-1.5 text-xs",
    md: "px-5 py-2.5 text-sm",
    lg: "px-8 py-4 text-base",
};

const classes = computed(() => {
    return [
        baseClasses,
        variantClasses[props.variant] || variantClasses.primary,
        sizeClasses[props.size] || sizeClasses.md,
        props.fullWidth ? "w-full" : "",
    ].join(" ");
});
</script>

<template>
    <component :is="as" :class="classes" :disabled="disabled || loading">
        <slot v-if="!loading" />
        <span v-else class="flex items-center gap-2">
            <svg
                class="animate-spin h-4 w-4 text-current"
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
            >
                <circle
                    class="opacity-25"
                    cx="12"
                    cy="12"
                    r="10"
                    stroke="currentColor"
                    stroke-width="4"
                ></circle>
                <path
                    class="opacity-75"
                    fill="currentColor"
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                ></path>
            </svg>
            <slot />
        </span>
    </component>
</template>
