<script setup lang="ts">
import { computed } from "vue";
import { MoveUp, MoveDown } from "lucide-vue-next";
import type { GameCell, TagItem } from "../../types/game";

const props = defineProps<{
    cell: GameCell,
}>();

const statusClasses = computed(() => {
    return {
        "bg-success-600 border-success-500": props.cell.status === "correct",
        "bg-warning-600 border-warning-500": props.cell.status === "partial",
        "bg-onyx-light border-white/10": props.cell.status === "wrong",
    };
});

const isTags = computed(() => Array.isArray(props.cell.value));
const tags = computed(() => props.cell.value as TagItem[]);
</script>

<template>
    <div
        class="relative w-full aspect-square flex flex-col items-center justify-center p-2 border-2 rounded-xl transition-all duration-500"
        :class="statusClasses"
    >
        <template v-if="props.cell.type === 'year'">
            <span class="text-xl font-bold text-white">{{ props.cell.value }}</span>
            <div v-if="props.cell.meta?.direction === 'up'" class="mt-1">
                <MoveUp class="w-5 h-5 text-white/80 animate-bounce" />
            </div>
            <div v-else-if="props.cell.meta?.direction === 'down'" class="mt-1">
                <MoveDown class="w-5 h-5 text-white/80 animate-bounce" />
            </div>
        </template>

        <template v-else-if="isTags">
            <div class="flex flex-wrap justify-center gap-1.5">
                <div
                    v-for="(item, index) in tags"
                    :key="index"
                    class="group relative"
                >
                    <div
                        class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider transition-all duration-200 cursor-default hover:scale-125 hover:z-20 hover:shadow-xl hover:brightness-110"
                        :class="[
                            item.matched
                                ? 'bg-white text-success-700'
                                : 'bg-black/20 text-white/70',
                        ]"
                    >
                        {{ item.label }}
                    </div>
                </div>
            </div>
        </template>

        <template v-else>
            <span class="text-xs font-bold text-white text-center uppercase tracking-tight">
                {{ props.cell.value }}
            </span>
        </template>
    </div>
</template>
