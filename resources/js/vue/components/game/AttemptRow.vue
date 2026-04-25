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
        developers: Array<{ id: number; name: string }>;
        publishers: Array<{ id: number; name: string }>;
        franchises: Array<{ id: number; name: string }>;
        collections: Array<{ id: number; name: string }>;
        game_modes: Array<{ id: number; name: string }>;
        player_perspectives: Array<{ id: number; name: string }>;
    };
    comparison: {
        release_year: { result: "exact" | "close" | "wrong"; value?: number; arrow?: "up" | "down" };
        rating: { result: "exact" | "close" | "wrong"; value?: number; arrow?: "up" | "down" };
        genres: { result: "exact" | "close" | "wrong" };
        developers_publishers: { result: "exact" | "close" | "wrong" };
        franchises_collections: { result: "exact" | "close" | "wrong" };
        game_modes: { result: "exact" | "close" | "wrong" };
        player_perspectives: { result: "exact" | "close" | "wrong" };
    };
}

const props = defineProps<{
    attempt: Attempt;
    attemptNumber: number;
}>();

function formatArrayValue(arr: Array<{ id: number; name: string }> | undefined): string {
    if (!arr || arr.length === 0) return "-";
    return arr.map(item => item.name).join(", ");
}

function buildDevelopersPublishersValue() {
    const developers = props.attempt.guessed.developers || [];
    const publishers = props.attempt.guessed.publishers || [];

    const devIds = developers.map(d => d.id).sort().join(',');
    const pubIds = publishers.map(p => p.id).sort().join(',');

    const devNames = developers.map(d => d.name).join(", ");
    const pubNames = publishers.map(p => p.name).join(", ");

    if (devIds === pubIds && devIds) {
        return devNames;
    }

    const result = [];

    if (developers.length > 0) {
        result.push({
            label: t('attributes.developer'),
            value: devNames
        });
    }

    if (publishers.length > 0) {
        result.push({
            label: t('attributes.publisher'),
            value: pubNames
        });
    }

    return result.length > 0 ? result : "-";
}

function buildFranchisesCollectionsValue() {
    const franchises = props.attempt.guessed.franchises || [];
    const collections = props.attempt.guessed.collections || [];

    const franchiseNames = franchises.map(f => f.name).join(", ");
    const collectionNames = collections.map(c => c.name).join(", ");

    if (franchises.length === 0 && collections.length > 0) {
        return collectionNames;
    }

    if (collections.length === 0 && franchises.length > 0) {
        return franchiseNames;
    }

    if (franchises.length === 1 && collections.length === 1 && franchiseNames === collectionNames) {
        return franchiseNames;
    }

    const result = [];

    if (franchises.length > 0) {
        result.push({
            label: t('attributes.franchise'),
            value: franchiseNames
        });
    }

    if (collections.length > 0) {
        result.push({
            label: t('attributes.collection'),
            value: collectionNames
        });
    }

    return result.length > 0 ? result : "-";
}

function buildPerspectiveValue() {
    const perspectives = props.attempt.guessed.player_perspectives || [];
    if (perspectives.length === 0) return "-";
    return perspectives.map(p => p.name).join(", ");
}

function buildGameModeValue() {
    const modes = props.attempt.guessed.game_modes || [];
    if (modes.length === 0) return "-";
    return modes.map(m => m.name).join(", ");
}
</script>

<template>
    <div class="grid grid-cols-[50px_80px_repeat(6,_1fr)_80px_80px] gap-2 animate-fade-in">
        <div class="rounded-lg bg-onyx-light border border-white/10 flex items-center justify-center min-h-[80px]">
            <span class="text-lg font-black text-primary-500">#{{ attemptNumber }}</span>
        </div>

        <div class="rounded-lg bg-onyx-light border border-white/10 flex items-center justify-center min-h-[80px] p-2">
            <div v-if="attempt.guessed.cover_url" class="w-full h-full flex items-center justify-center">
                <img
                    :src="attempt.guessed.cover_url"
                    :alt="attempt.guessed.display_name"
                    class="max-w-full max-h-full rounded object-cover"
                />
            </div>
            <div v-else class="text-xs font-bold text-white text-center px-1">
                {{ attempt.guessed.display_name }}
            </div>
        </div>

        <AttributeCell
            :result="attempt.comparison.genres.result"
            :value="formatArrayValue(attempt.guessed.genres)"
        />

        <AttributeCell
            :result="attempt.comparison.developers_publishers.result"
            :value="buildDevelopersPublishersValue()"
        />

        <AttributeCell
            :result="attempt.comparison.franchises_collections.result"
            :value="buildFranchisesCollectionsValue()"
        />

        <AttributeCell
            :result="attempt.comparison.game_modes.result"
            :value="buildGameModeValue()"
        />

        <AttributeCell
            :result="attempt.comparison.player_perspectives.result"
            :value="buildPerspectiveValue()"
        />

        <AttributeCell
            :result="attempt.comparison.rating.result"
            :value="attempt.comparison.rating.value"
            :arrow="attempt.comparison.rating.arrow"
        />

        <AttributeCell
            :result="attempt.comparison.release_year.result"
            :value="attempt.comparison.release_year.value"
            :arrow="attempt.comparison.release_year.arrow"
        />
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
