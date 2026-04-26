<script setup lang="ts">
import { computed, ref } from "vue";
import { useI18n } from "vue-i18n";
import { Lock, ChevronDown, Eye } from "lucide-vue-next";

interface Props {
    type: string;
    value: string | number | null;
    unlockAt: number;
    currentAttempts: number;
}

const props = defineProps<Props>();

const { t } = useI18n();

const isExpanded = ref(false);

const isUnlocked = computed(() => props.currentAttempts >= props.unlockAt);

const attemptsLeft = computed(() => Math.max(0, props.unlockAt - props.currentAttempts));

const hintLabel = computed(() => {
    return t(`hints.types.${props.type}`, props.type);
});

const toggleExpand = () => {
    if (isUnlocked.value && props.value) {
        isExpanded.value = !isExpanded.value;
    }
};
</script>

<template>
    <div class="relative">
        <button
            class="w-full px-4 py-3 rounded-xl border transition-all duration-200 text-left"
            :class="[
                isUnlocked
                    ? 'bg-teal-500/10 border-teal-500/30 hover:bg-teal-500/20 cursor-pointer'
                    : 'bg-onyx border-onyx-light/30 cursor-not-allowed opacity-70'
            ]"
            :disabled="!isUnlocked"
            @click="toggleExpand"
        >
            <div class="flex items-center justify-between gap-2">
                <div class="flex items-center gap-2 min-w-0">
                    <div
                        class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0"
                        :class="[
                            isUnlocked
                                ? 'bg-teal-500/20 text-teal-400'
                                : 'bg-onyx-light/50 text-muted'
                        ]"
                    >
                        <Eye v-if="isUnlocked" class="w-4 h-4" />
                        <Lock v-else class="w-4 h-4" />
                    </div>

                    <div class="min-w-0">
                        <p class="text-sm font-medium text-white truncate">
                            {{ hintLabel }}
                        </p>
                        <p v-if="!isUnlocked" class="text-xs text-muted">
                            {{ t('hints.unlock_in', { count: attemptsLeft }) }}
                        </p>
                        <p v-else class="text-xs text-teal-400">
                            {{ t('hints.tap_to_reveal') }}
                        </p>
                    </div>
                </div>

                <ChevronDown
                    v-if="isUnlocked"
                    class="w-5 h-5 text-muted shrink-0 transition-transform duration-200"
                    :class="{ 'rotate-180': isExpanded }"
                />

                <span
                    v-else
                    class="px-2 py-1 bg-onyx-light/50 rounded-lg text-xs font-bold text-muted shrink-0"
                >
                    {{ unlockAt }}
                </span>
            </div>
        </button>

        <Transition
            enter-active-class="transition-all duration-200 ease-out"
            enter-from-class="opacity-0 -translate-y-2 max-h-0"
            enter-to-class="opacity-100 translate-y-0 max-h-40"
            leave-active-class="transition-all duration-150 ease-in"
            leave-from-class="opacity-100 translate-y-0 max-h-40"
            leave-to-class="opacity-0 -translate-y-2 max-h-0"
        >
            <div
                v-if="isExpanded && isUnlocked"
                class="mt-2 p-4 bg-onyx-light border border-onyx-light/50 rounded-xl overflow-hidden"
            >
                <p class="text-xs text-muted uppercase tracking-wider mb-1">
                    {{ hintLabel }}
                </p>
                <p class="text-lg font-bold text-white">
                    {{ value ?? t('hints.no_data') }}
                </p>
            </div>
        </Transition>
    </div>
</template>
