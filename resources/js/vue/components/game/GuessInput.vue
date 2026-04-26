<script setup lang="ts">
import { ref } from "vue";
import { watchDebounced, onClickOutside } from "@vueuse/core";
import { useI18n } from "vue-i18n";
import axios from "axios";
import { Search } from "lucide-vue-next";
import { route } from "ziggy-js";
const { t } = useI18n();

interface SearchResult {
    id: number | string;
    name: string;
    display_name?: string;
    image?: string;
    meta?: string;
}

const props = withDefaults(defineProps<{
    type?: string;
    placeholder?: string;
    excludeIds?: number[];
}>(), {
    type: "game",
    excludeIds: () => [],
});

const getPlaceholder = (): string => {
    if (props.placeholder) return props.placeholder;
    const placeholderKey = `game.search_${props.type}`;
    return t(placeholderKey);
};

const emit = defineEmits<{
    (e: "select", item: SearchResult): void;
}>();

const query = ref("");
const results = ref<SearchResult[]>([]);
const isOpen = ref(false);
const isLoading = ref(false);
const containerRef = ref(null);

onClickOutside(containerRef, () => {
    isOpen.value = false;
});

const search = async () => {
    if (query.value.length < 2) {
        results.value = [];
        isOpen.value = false;
        return;
    }

    isLoading.value = true;

    try {
        const response = await axios.get(route("api.game-search"), {
            params: {
                query: query.value,
                type: props.type,
            },
        });

        const allResults = response.data;

        if (props.excludeIds && props.excludeIds.length > 0) {
            results.value = allResults.filter((item: SearchResult) =>
                !props.excludeIds.includes(Number(item.id))
            );
        } else {
            results.value = allResults;
        }

        isOpen.value = true;
    } catch (err) {
        console.error("Search failed", err);
    } finally {
        isLoading.value = false;
    }
};

watchDebounced(query, search, { debounce: 300 });

const select = (item: SearchResult) => {
    emit("select", item);
    query.value = "";
    isOpen.value = false;
    results.value = [];
};

const handleEnter = () => {
    if (results.value.length > 0 && isOpen.value) {
        select(results.value[0]);
    }
};
</script>

<template>
    <div ref="containerRef" class="relative w-full max-w-xl mx-auto"
        @keydown.esc="isOpen = false"
    >
        <div class="relative group">
            <div
                class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none"
            >
                <Search
                    class="h-5 w-5 text-muted group-focus-within:text-teal-400 transition-colors"
                />
            </div>
            <input
                v-model="query"
                type="text"
                class="block w-full pl-12 pr-4 py-4 bg-onyx border border-onyx-light/30 rounded-2xl text-white placeholder-muted focus:outline-none focus:ring-2 focus:ring-teal-500/50 focus:border-teal-500 transition-all text-lg"
                :placeholder="getPlaceholder()"
                @click="isOpen = query.length >= 2 && (results.length > 0 || !isLoading)"
                @keydown.enter="handleEnter"
            />
        </div>

        <div
            v-if="isOpen && results.length > 0"
            class="absolute z-50 w-full mt-2 bg-onyx-light border border-onyx-light/50 rounded-2xl shadow-2xl overflow-hidden max-h-80 overflow-y-auto custom-scrollbar"
        >
            <button
                v-for="item in results"
                :key="item.id"
                class="w-full px-6 py-4 flex items-center gap-4 text-left hover:bg-onyx transition-colors border-b border-onyx-light/30 last:border-0"
                @click="select(item)"
            >
                <div
                    v-if="item.image"
                    class="w-12 h-12 rounded-lg overflow-hidden shrink-0 bg-onyx"
                >
                    <img :src="item.image" :alt="item.name" class="w-full h-full object-cover" />
                </div>
                <div>
                    <div class="text-white font-bold">{{ item.display_name || item.name }}</div>
                    <div v-if="item.meta" class="text-muted text-sm">
                        {{ item.meta }}
                    </div>
                </div>
            </button>
        </div>

        <div
            v-else-if="isOpen && query.length >= 2 && !isLoading"
            class="absolute z-50 w-full mt-2 bg-onyx-light border border-onyx-light/50 rounded-2xl p-6 text-center text-muted"
        >
            {{ t("game.no_results") }}
        </div>
    </div>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 8px;
}

.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
    border-radius: 10px;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.2);
    border-radius: 10px;
    transition: background 0.2s;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: rgba(255, 255, 255, 0.3);
}

.custom-scrollbar {
    scrollbar-width: thin;
    scrollbar-color: rgba(255, 255, 255, 0.2) transparent;
}
</style>

