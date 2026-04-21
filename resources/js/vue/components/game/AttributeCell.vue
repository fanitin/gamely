<script setup lang="ts">
import { computed } from "vue";
import { ArrowUp, ArrowDown } from "lucide-vue-next";

interface Props {
    result: "exact" | "close" | "wrong";
    label: string;
    value?: string | number | Array<{ id: number; name: string; url?: string }>;
    arrow?: "up" | "down";
    type?: "platforms" | "default";
}

const props = withDefaults(defineProps<Props>(), {
    type: "default"
});

const bgColor = computed(() => {
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
            bgColor,
            'rounded-xl p-3 text-white transition-all duration-300 min-h-[100px] flex flex-col',
        ]"
    >
        <div class="text-xs font-medium text-white/70 uppercase tracking-wide mb-2">
            {{ label }}
        </div>

        <div v-if="isPlatforms" class="flex-1 flex items-center justify-center">
            <div class="grid grid-cols-3 gap-2 w-full">
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
        </div>

        <div v-else class="flex-1 flex items-center justify-between gap-2">
            <div class="text-sm font-bold flex-1 overflow-hidden">
                <div class="line-clamp-3 break-words" :title="displayValue">
                    {{ displayValue }}
                </div>
            </div>
            <div v-if="arrow" class="shrink-0">
                <ArrowUp v-if="arrow === 'up'" class="w-5 h-5" />
                <ArrowDown v-if="arrow === 'down'" class="w-5 h-5" />
            </div>
        </div>
    </div>
</template>
