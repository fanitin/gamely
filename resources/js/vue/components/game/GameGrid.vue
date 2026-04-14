<script setup lang="ts">
import AttributeCell from "./AttributeCell.vue";
import type { GameAttempt } from "../../types/game";

const props = withDefaults(defineProps<{
    columns: string[];
    attempts: GameAttempt[];
    isLoading: boolean;
}>(), {
    attempts: () => [],
    isLoading: false,
});
</script>

<template>
    <div class="w-full overflow-x-auto pb-8">
        <div class="min-w-[600px] flex flex-col gap-3">
            <div
                class="grid gap-3"
                :style="{ gridTemplateColumns: `repeat(${props.columns.length}, 1fr)` }"
            >
                <div
                    v-for="col in props.columns"
                    :key="col"
                    class="text-center pb-2 border-b-2 border-white/5"
                >
                    <span class="text-[10px] uppercase font-black tracking-widest text-muted">
                        {{ col }}
                    </span>
                </div>
            </div>

            <transition-group
                name="list"
                tag="div"
                class="flex flex-col gap-3"
            >
                <div
                    v-for="(attempt, index) in props.attempts"
                    :key="attempt.id || index"
                    class="grid gap-3"
                    :style="{ gridTemplateColumns: `repeat(${props.columns.length}, 1fr)` }"
                >
                    <AttributeCell
                        v-for="(cell, cIndex) in attempt.cells"
                        :key="cIndex"
                        :cell="cell"
                    />
                </div>
            </transition-group>

            <div
                v-if="props.isLoading"
                class="grid gap-3 opacity-50 animate-pulse"
                :style="{ gridTemplateColumns: `repeat(${props.columns.length}, 1fr)` }"
            >
                <div
                    v-for="i in props.columns.length"
                    :key="i"
                    class="w-full aspect-square bg-onyx-light border-2 border-white/5 rounded-xl"
                ></div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.list-enter-active,
.list-leave-active {
    transition: all 0.5s ease;
}
.list-enter-from {
    opacity: 0;
    transform: translateY(-30px);
}
</style>
