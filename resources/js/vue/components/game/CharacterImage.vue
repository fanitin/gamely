<script setup lang="ts">
import { ref } from "vue";
import { useI18n } from "vue-i18n";
import { ZoomIn } from "lucide-vue-next";
import AppModal from "@/vue/components/ui/AppModal.vue";

const { t } = useI18n();

const props = defineProps<{
    imageUrl: string;
    blurAmount: string;
    isWon: boolean;
}>();

const isModalOpen = ref(false);
</script>

<template>
    <div class="flex flex-col items-center gap-3">
        <button
            type="button"
            class="relative group rounded-2xl overflow-hidden shadow-2xl border border-white/10 cursor-zoom-in focus:outline-none focus:ring-2 focus:ring-teal-500/50 transition-transform duration-200 hover:scale-[1.02] active:scale-[0.98]"
            style="width: 280px; height: 360px;"
            @click="isModalOpen = true"
        >
            <img
                :src="imageUrl"
                alt="Character"
                class="w-full h-full object-cover object-top transition-all duration-700"
                :style="{ filter: `blur(${blurAmount})`, transform: 'scale(1.06)' }"
            />

            <div class="absolute inset-0 bg-linear-to-t from-black/50 via-transparent to-transparent pointer-events-none" />

            <div
                class="absolute inset-0 flex flex-col items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none"
            >
                <div class="bg-black/60 backdrop-blur-sm rounded-xl px-4 py-2 flex items-center gap-2">
                    <ZoomIn class="w-5 h-5 text-white" />
                    <span class="text-sm font-semibold text-white">{{ t("character.expand") }}</span>
                </div>
            </div>

            <div
                v-if="!isWon"
                class="absolute bottom-2 left-0 right-0 flex justify-center pointer-events-none"
            >
                <span class="text-xs text-white/70 bg-black/50 backdrop-blur-sm px-3 py-1 rounded-full">
                    {{ t("character.blur_hint") }}
                </span>
            </div>

            <div
                v-if="isWon"
                class="absolute top-2 right-2 bg-success-500/90 backdrop-blur-sm rounded-lg px-2 py-1 pointer-events-none"
            >
                <span class="text-xs font-bold text-white">✓</span>
            </div>
        </button>

        <AppModal
            :is-open="isModalOpen"
            title=""
            size="xl"
            @close="isModalOpen = false"
        >
            <div class="flex flex-col items-center gap-4 -mt-4 -mx-2">
                <div class="relative w-full rounded-xl overflow-hidden" style="max-height: 70vh;">
                    <img
                        :src="imageUrl"
                        alt="Character"
                        class="w-full h-full object-contain object-top transition-all duration-700"
                        :style="{ filter: `blur(${blurAmount})` }"
                    />
                </div>

                <p v-if="!isWon" class="text-muted text-sm text-center">
                    {{ t("character.blur_hint") }}
                </p>
            </div>
        </AppModal>
    </div>
</template>
