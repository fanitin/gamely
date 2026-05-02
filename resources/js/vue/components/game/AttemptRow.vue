<script setup lang="ts">
import { computed } from "vue";
import { useI18n } from "vue-i18n";
import AttributeCell from "./AttributeCell.vue";

const { t } = useI18n();

type GameMode = "classic" | "screenshots" | "character";

interface Attempt {
    guessed: {
        name: string;
        display_name?: string;
        cover_url?: string;
        mug_shot_url?: string;
        gender?: string;
        species?: string;
        first_appearance_year?: number | null;
        release_year?: number | null;
        rating?: number | null;
        genres?: Array<{ id: number; name: string }>;
        developers?: Array<{ id: number; name: string }>;
        publishers?: Array<{ id: number; name: string }>;
        franchises?: Array<{ id: number; name: string }>;
        collections?: Array<{ id: number; name: string }>;
        game_modes?: Array<{ id: number; name: string }>;
        player_perspectives?: Array<{ id: number; name: string }>;
    };
    comparison: {
        release_year?: { result: "exact" | "close" | "wrong"; value?: number; arrow?: "up" | "down" };
        rating?: { result: "exact" | "close" | "wrong"; value?: number; arrow?: "up" | "down" };
        genres?: { result: "exact" | "close" | "wrong" };
        developers_publishers?: { result: "exact" | "close" | "wrong" };
        franchises_collections?: { result: "exact" | "close" | "wrong" };
        game_modes?: { result: "exact" | "close" | "wrong" | "missing" };
        player_perspectives?: { result: "exact" | "close" | "wrong" | "missing" };
        gender?: { result: "exact" | "close" | "wrong" | "missing" };
        species?: { result: "exact" | "close" | "wrong" | "missing" };
        first_appearance_year?: { result: "exact" | "close" | "wrong" | "missing"; value?: number; arrow?: "up" | "down" };
        franchises?: { result: "exact" | "close" | "wrong" | "missing" };
        collections?: { result: "exact" | "close" | "wrong" | "missing" };
    };
}

const props = withDefaults(defineProps<{
    attempt: Attempt;
    attemptNumber: number;
    mode?: GameMode;
}>(), {
    mode: "classic",
});

const gridClass = computed(() => {
    if (props.mode === "screenshots") {
        return "grid-cols-[60px_1fr_1fr_1fr]";
    }
    if (props.mode === "character") {
        return "grid-cols-[50px_80px_repeat(5,1fr)]";
    }
    return "grid-cols-[50px_80px_repeat(6,1fr)_80px_80px]";
});

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
    <div class="grid gap-2 animate-fade-in items-stretch" :class="gridClass">
        <div class="rounded-xl bg-onyx-light border border-onyx-light/50 flex items-center justify-center min-h-[80px] p-3">
            <span class="text-lg font-black text-teal-500">#{{ attemptNumber }}</span>
        </div>

        <div class="rounded-xl bg-onyx-light border border-onyx-light/50 flex items-center gap-3 min-h-[80px] p-3 overflow-hidden">
            <div v-if="attempt.guessed.cover_url || attempt.guessed.mug_shot_url" class="w-14 h-14 shrink-0">
                <img
                    :src="attempt.guessed.cover_url || attempt.guessed.mug_shot_url"
                    :alt="attempt.guessed.display_name || attempt.guessed.name"
                    class="w-full h-full rounded-lg object-cover"
                />
            </div>
            <div class="min-w-0 flex-1">
                <p class="text-sm font-bold text-white truncate">
                    {{ attempt.guessed.display_name || attempt.guessed.name }}
                </p>
            </div>
        </div>

        <template v-if="mode === 'classic'">
            <AttributeCell
                :result="attempt.comparison.genres?.result || 'wrong'"
                :value="formatArrayValue(attempt.guessed.genres)"
            />

            <AttributeCell
                :result="attempt.comparison.developers_publishers?.result || 'wrong'"
                :value="buildDevelopersPublishersValue()"
            />

            <AttributeCell
                :result="attempt.comparison.franchises_collections?.result || 'wrong'"
                :value="buildFranchisesCollectionsValue()"
            />

            <AttributeCell
                :result="attempt.comparison.player_perspectives?.result || 'wrong'"
                :value="buildPerspectiveValue()"
            />

            <AttributeCell
                :result="attempt.comparison.game_modes?.result || 'wrong'"
                :value="buildGameModeValue()"
            />

            <AttributeCell
                :result="attempt.comparison.rating?.result || 'wrong'"
                :value="attempt.comparison.rating?.value"
                :arrow="attempt.comparison.rating?.arrow"
            />

            <AttributeCell
                :result="attempt.comparison.release_year?.result || 'wrong'"
                :value="attempt.comparison.release_year?.value"
                :arrow="attempt.comparison.release_year?.arrow"
            />
        </template>

        <template v-else-if="mode === 'screenshots'">
            <AttributeCell
                :result="attempt.comparison.franchises_collections?.result || 'wrong'"
                :value="buildFranchisesCollectionsValue()"
            />

            <AttributeCell
                :result="attempt.comparison.developers_publishers?.result || 'wrong'"
                :value="buildDevelopersPublishersValue()"
            />
        </template>

        <template v-else-if="mode === 'character'">
            <AttributeCell
                :result="attempt.comparison.franchises?.result || 'wrong'"
                :value="formatArrayValue(attempt.guessed.franchises)"
            />

            <AttributeCell
                :result="attempt.comparison.collections?.result || 'wrong'"
                :value="formatArrayValue(attempt.guessed.collections)"
            />

            <AttributeCell
                :result="attempt.comparison.gender?.result || 'wrong'"
                :value="attempt.guessed.gender"
            />

            <AttributeCell
                :result="attempt.comparison.species?.result || 'wrong'"
                :value="attempt.guessed.species"
            />

            <AttributeCell
                :result="attempt.comparison.first_appearance_year?.result || 'wrong'"
                :value="attempt.comparison.first_appearance_year?.value"
                :arrow="attempt.comparison.first_appearance_year?.arrow"
            />
        </template>
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
