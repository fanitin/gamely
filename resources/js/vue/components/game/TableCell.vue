<script setup lang="ts">
import { computed } from "vue";
import { ArrowUp, ArrowDown } from "lucide-vue-next";

interface Props {
    result: "exact" | "close" | "wrong";
    value?: string | number | Array<{ id: number; name: string; url?: string }>;
    arrow?: "up" | "down";
    type?: "platforms" | "default";
    isHeader?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    type: "default",
    isHeader: false
});

const bgColorClass = computed(() => {
    if (props.isHeader) return "bg-transparent";

    switch (props.result) {
        case "exact":
            return "bg-success-500";
        case "close":
            return "bg-warning-500";
        case "wrong":
            return "bg-danger-500";
        default:
            return "bg-danger-500";
    }
});

const bgArrowOverlay = computed(() => {
    if (!props.arrow) return "";

    switch (props.result) {
        case "exact":
            return "bg-success-600/40";
        case "close":
            return "bg-warning-600/40";
        case "wrong":
            return "bg-danger-600/40";
        default:
            return "bg-danger-600/40";
    }
});

const isPlatforms = computed(() => props.type === "platforms" && Array.isArray(props.value));

const displayValue = computed(() => {
    if (!props.value) return "-";

    if (Array.isArray(props.value)) {
        return props.value.map(item => item.name).join(", ");
    }

    return props.value.toString();
});
</script>

<template>
    <div
        :class="[
            bgColorClass,
            'relative rounded-lg p-3 text-white transition-all duration-300 min-h-[80px] flex items-center justify-center overflow-hidden',
        ]"
    >
        <!-- Стрелка на фоне с затемнением -->
        <div
            v-if="arrow && !isHeader"
            :class="[
                bgArrowOverlay,
                'absolute inset-0 flex items-center justify-center pointer-events-none'
            ]"
        >
            <ArrowUp v-if="arrow === 'up'" class="w-16 h-16 opacity-20" />
            <ArrowDown v-if="arrow === 'down'" class="w-16 h-16 opacity-20" />
        </div>

        <!-- Платформы -->
        <div v-if="isPlatforms && !isHeader" class="relative z-10 grid grid-cols-3 gap-2 w-full">
            <div
                v-for="platform in (value as Array<{id: number; name: string; url?: string}>)"
                :key="platform.id"
                class="flex items-center justify-center"
                :title="platform.name"
            >
                <img
                    v-if="platform.url"
                    :src="platform.url"
                    :alt="platform.name"
                    class="w-8 h-8 object-contain opacity-90"
                />
                <span v-else class="text-[10px] font-bold text-center leading-tight">
                    {{ platform.name }}
                </span>
            </div>
        </div>

        <!-- Обычное значение -->
        <div v-else class="relative z-10 text-sm font-bold text-center px-1">
            <div class="line-clamp-3 break-words" :title="displayValue">
                {{ displayValue }}
            </div>
        </div>
    </div>
</template>
