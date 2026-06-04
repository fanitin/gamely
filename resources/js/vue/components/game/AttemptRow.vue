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
        popularity_tier?: string | null;
        franchise_start_year?: number | null;
        genres?: Array<{ id: number; name: string }>;
        developers?: Array<{ id: number; name: string }>;
        publishers?: Array<{ id: number; name: string }>;
        franchises?: Array<{ id: number; name: string }>;
        collections?: Array<{ id: number; name: string }>;
        game_modes?: Array<{ id: number; name: string }>;
        player_perspectives?: Array<{ id: number; name: string }>;
    };
    comparison: {
        release_year?: {
            result: "exact" | "close" | "wrong";
            value?: number;
            arrow?: "up" | "down";
        };
        genres?: { result: "exact" | "close" | "wrong" };
        developers_publishers?: { result: "exact" | "close" | "wrong" };
        franchises_collections?: { result: "exact" | "close" | "wrong" };
        game_modes?: { result: "exact" | "close" | "wrong" | "missing" };
        player_perspectives?: {
            result: "exact" | "close" | "wrong" | "missing";
        };
        popularity?: {
            result: "exact" | "close" | "wrong";
            value?: string;
            arrow?: "up" | "down";
        };
        franchise_start_year?: {
            result: "exact" | "close" | "wrong";
            value?: number;
            arrow?: "up" | "down";
        };
        gender?: { result: "exact" | "close" | "wrong" | "missing" };
        species?: { result: "exact" | "close" | "wrong" | "missing" };
        first_appearance_year?: {
            result: "exact" | "close" | "wrong" | "missing";
            value?: number;
            arrow?: "up" | "down";
        };
        franchises?: { result: "exact" | "close" | "wrong" | "missing" };
        collections?: { result: "exact" | "close" | "wrong" | "missing" };
    };
}

interface MultiValue {
    label: string;
    value: string;
}

interface CellDescriptor {
    key: string;
    label: string;
    result: "exact" | "close" | "wrong" | "missing";
    value?: string | number | Array<MultiValue>;
    arrow?: "up" | "down";
}

const props = withDefaults(
    defineProps<{
        attempt: Attempt;
        attemptNumber: number;
        mode?: GameMode;
    }>(),
    {
        mode: "classic",
    },
);

const desktopGridClass = computed(() => {
    if (props.mode === "screenshots") {
        return "lg:grid-cols-[60px_1fr_90px_1fr_1fr_90px_90px]";
    }
    if (props.mode === "character") {
        return "lg:grid-cols-[50px_80px_repeat(5,1fr)]";
    }
    return "lg:grid-cols-[50px_80px_repeat(5,1fr)_80px_90px]";
});

function formatArrayValue(
    arr: Array<{ id: number; name: string }> | undefined,
): string {
    if (!arr || arr.length === 0) return "-";
    return arr.map((item) => item.name).join(", ");
}

function buildDevelopersPublishersValue(): string | Array<MultiValue> {
    const developers = props.attempt.guessed.developers || [];
    const publishers = props.attempt.guessed.publishers || [];

    const devIds = developers
        .map((d) => d.id)
        .sort()
        .join(",");
    const pubIds = publishers
        .map((p) => p.id)
        .sort()
        .join(",");

    const devNames = developers.map((d) => d.name).join(", ");
    const pubNames = publishers.map((p) => p.name).join(", ");

    if (devIds === pubIds && devIds) {
        return devNames;
    }

    const result: Array<MultiValue> = [];

    if (developers.length > 0) {
        result.push({ label: t("attributes.developer"), value: devNames });
    }

    if (publishers.length > 0) {
        result.push({ label: t("attributes.publisher"), value: pubNames });
    }

    return result.length > 0 ? result : "-";
}

function buildFranchisesCollectionsValue(): string | Array<MultiValue> {
    const franchises = props.attempt.guessed.franchises || [];
    const collections = props.attempt.guessed.collections || [];

    const franchiseNames = franchises.map((f) => f.name).join(", ");
    const collectionNames = collections.map((c) => c.name).join(", ");

    if (franchises.length === 0 && collections.length > 0) {
        return collectionNames;
    }

    if (collections.length === 0 && franchises.length > 0) {
        return franchiseNames;
    }

    if (
        franchises.length === 1 &&
        collections.length === 1 &&
        franchiseNames === collectionNames
    ) {
        return franchiseNames;
    }

    const result: Array<MultiValue> = [];

    if (franchises.length > 0) {
        result.push({ label: t("attributes.franchise"), value: franchiseNames });
    }

    if (collections.length > 0) {
        result.push({ label: t("attributes.collection"), value: collectionNames });
    }

    return result.length > 0 ? result : "-";
}

function buildPerspectiveValue(): string {
    const perspectives = props.attempt.guessed.player_perspectives || [];
    if (perspectives.length === 0) return "-";
    return perspectives.map((p) => p.name).join(", ");
}

function buildGameModeValue(): string {
    const modes = props.attempt.guessed.game_modes || [];
    if (modes.length === 0) return "-";
    return modes.map((m) => m.name).join(", ");
}

const coverUrl = computed(
    () => props.attempt.guessed.cover_url || props.attempt.guessed.mug_shot_url,
);

const guessedName = computed(
    () => props.attempt.guessed.display_name || props.attempt.guessed.name,
);

const cells = computed<CellDescriptor[]>(() => {
    const c = props.attempt.comparison;

    if (props.mode === "screenshots") {
        return [
            {
                key: "release_year",
                label: t("attributes.release_year"),
                result: c.release_year?.result || "wrong",
                value: c.release_year?.value,
                arrow: c.release_year?.arrow,
            },
            {
                key: "franchises_collections",
                label: t("attributes.franchises_collections"),
                result: c.franchises_collections?.result || "wrong",
                value: buildFranchisesCollectionsValue(),
            },
            {
                key: "developers_publishers",
                label: t("attributes.developers_publishers"),
                result: c.developers_publishers?.result || "wrong",
                value: buildDevelopersPublishersValue(),
            },
            {
                key: "popularity",
                label: t("attributes.popularity"),
                result: c.popularity?.result || "wrong",
                value: c.popularity?.value,
                arrow: c.popularity?.arrow,
            },
            {
                key: "franchise_start_year",
                label: t("attributes.franchise_year"),
                result: c.franchise_start_year?.result || "wrong",
                value: c.franchise_start_year?.value,
                arrow: c.franchise_start_year?.arrow,
            },
        ];
    }

    if (props.mode === "character") {
        return [
            {
                key: "franchises",
                label: t("attributes.franchises"),
                result: c.franchises?.result || "wrong",
                value: formatArrayValue(props.attempt.guessed.franchises),
            },
            {
                key: "collections",
                label: t("attributes.collections"),
                result: c.collections?.result || "wrong",
                value: formatArrayValue(props.attempt.guessed.collections),
            },
            {
                key: "gender",
                label: t("attributes.gender"),
                result: c.gender?.result || "wrong",
                value: props.attempt.guessed.gender,
            },
            {
                key: "species",
                label: t("attributes.species"),
                result: c.species?.result || "wrong",
                value: props.attempt.guessed.species,
            },
            {
                key: "first_appearance_year",
                label: t("attributes.first_appearance_year"),
                result: c.first_appearance_year?.result || "wrong",
                value: c.first_appearance_year?.value,
                arrow: c.first_appearance_year?.arrow,
            },
        ];
    }

    return [
        {
            key: "genres",
            label: t("attributes.genres"),
            result: c.genres?.result || "wrong",
            value: formatArrayValue(props.attempt.guessed.genres),
        },
        {
            key: "developers_publishers",
            label: t("attributes.developers_publishers"),
            result: c.developers_publishers?.result || "wrong",
            value: buildDevelopersPublishersValue(),
        },
        {
            key: "franchises_collections",
            label: t("attributes.franchises_collections"),
            result: c.franchises_collections?.result || "wrong",
            value: buildFranchisesCollectionsValue(),
        },
        {
            key: "player_perspectives",
            label: t("attributes.player_perspective"),
            result: c.player_perspectives?.result || "wrong",
            value: buildPerspectiveValue(),
        },
        {
            key: "game_modes",
            label: t("attributes.game_mode"),
            result: c.game_modes?.result || "wrong",
            value: buildGameModeValue(),
        },
        {
            key: "release_year",
            label: t("attributes.release_year"),
            result: c.release_year?.result || "wrong",
            value: c.release_year?.value,
            arrow: c.release_year?.arrow,
        },
        {
            key: "popularity",
            label: t("attributes.popularity"),
            result: c.popularity?.result || "wrong",
            value: c.popularity?.value,
            arrow: c.popularity?.arrow,
        },
    ];
});
</script>

<template>
    <div
        class="animate-fade-in bg-onyx-light/30 lg:bg-transparent rounded-2xl lg:rounded-none border lg:border-0 border-onyx-light/30 overflow-hidden lg:overflow-visible"
    >
        <div
            class="grid gap-2 items-stretch grid-cols-1"
            :class="desktopGridClass"
        >
            <div
                class="hidden lg:flex bg-onyx-light border-[3px] border-onyx-dark shadow-[inset_0_0_0_2px_rgba(255,255,255,0.08)] items-center justify-center min-h-[80px] p-3"
            >
                <span class="text-lg font-black text-teal-500"
                    >#{{ attemptNumber }}</span
                >
            </div>

            <div
                class="flex items-center gap-3 p-3 lg:bg-onyx-light lg:border-[3px] lg:border-onyx-dark lg:shadow-[inset_0_0_0_2px_rgba(255,255,255,0.08)] lg:min-h-[80px] border-b border-onyx-light/30 lg:border-b-0 overflow-hidden"
            >
                <div v-if="coverUrl" class="w-12 h-12 lg:w-14 lg:h-14 shrink-0">
                    <img
                        :src="coverUrl"
                        :alt="guessedName"
                        class="w-full h-full object-cover rounded-md lg:rounded-none"
                    />
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-bold text-white truncate">
                        {{ guessedName }}
                    </p>
                </div>
                <span
                    class="lg:hidden text-base font-black text-teal-500 shrink-0"
                    >#{{ attemptNumber }}</span
                >
            </div>

            <div
                class="grid grid-cols-2 gap-2 p-3 lg:p-0 lg:contents"
            >
                <AttributeCell
                    v-for="cell in cells"
                    :key="cell.key"
                    :label="cell.label"
                    :result="cell.result"
                    :value="cell.value"
                    :arrow="cell.arrow"
                />
            </div>
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
