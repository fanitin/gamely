<script setup lang="ts">
import { useI18n } from "vue-i18n";
import AttributeCell from "./AttributeCell.vue";

const { t } = useI18n();

interface Attempt {
    guessed: {
        name: string;
        display_name: string;
        cover_url: string;
        release_year: number | null;
        rating: number | null;
        genres: Array<{ id: number; name: string }>;
        platforms: Array<{ id: number; name: string; url?: string }>;
        developers: Array<{ id: number; name: string }>;
        publishers: Array<{ id: number; name: string }>;
    };
    comparison: {
        release_year: { result: "exact" | "close" | "wrong"; value?: number; arrow?: "up" | "down" };
        rating: { result: "exact" | "close" | "wrong"; value?: number; arrow?: "up" | "down" };
        genres: { result: "exact" | "close" | "wrong" };
        platforms: { result: "exact" | "close" | "wrong" };
        developers: { result: "exact" | "close" | "wrong" };
        publishers: { result: "exact" | "close" | "wrong" };
    };
}

defineProps<{
    attempt: Attempt;
}>();
</script>

<template>
    <div class="animate-fade-in">
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-7 gap-3">
            <div class="rounded-xl p-3 bg-onyx-light border border-white/10 min-h-[100px] flex flex-col">
                <div class="text-xs font-medium text-white/70 uppercase tracking-wide mb-2">
                    {{ t('attributes.game') }}
                </div>
                <div class="flex-1 flex items-center justify-center">
                    <div v-if="attempt.guessed.cover_url" class="w-full flex justify-center">
                        <img
                            :src="attempt.guessed.cover_url"
                            :alt="attempt.guessed.display_name"
                            class="max-w-[60px] max-h-[80px] rounded object-cover"
                        />
                    </div>
                    <div v-else class="text-sm font-bold text-white text-center px-2">
                        {{ attempt.guessed.display_name }}
                    </div>
                </div>
            </div>

            <AttributeCell
                :result="attempt.comparison.platforms.result"
                :label="t('attributes.platforms')"
                :value="attempt.guessed.platforms"
                type="platforms"
            />

            <AttributeCell
                :result="attempt.comparison.genres.result"
                :label="t('attributes.genres')"
                :value="attempt.guessed.genres"
            />

            <AttributeCell
                :result="attempt.comparison.developers.result"
                :label="t('attributes.developer')"
                :value="attempt.guessed.developers"
            />

            <AttributeCell
                :result="attempt.comparison.publishers.result"
                :label="t('attributes.publisher')"
                :value="attempt.guessed.publishers"
            />

            <AttributeCell
                :result="attempt.comparison.rating.result"
                :label="t('attributes.rating')"
                :value="attempt.comparison.rating.value"
                :arrow="attempt.comparison.rating.arrow"
            />

            <AttributeCell
                :result="attempt.comparison.release_year.result"
                :label="t('attributes.release_year')"
                :value="attempt.comparison.release_year.value"
                :arrow="attempt.comparison.release_year.arrow"
            />
        </div>
    </div>
</template>

<style scoped>
@keyframes fade-in {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fade-in {
    animation: fade-in 0.3s ease-out;
}
</style>
