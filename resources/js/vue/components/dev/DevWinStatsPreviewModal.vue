<script setup lang="ts">
import { computed, ref, watch } from "vue";
import axios, { type AxiosRequestConfig } from "axios";
import ResultModal from "@/vue/components/game/ResultModal.vue";
import type { ModeValue, ModeDistributionResponse } from "@/vue/composables/useStats";

const props = defineProps<{
    isOpen: boolean;
}>();

const emit = defineEmits<{
    (e: "close"): void;
}>();

const mode: ModeValue = "classic";
const attemptsCount = 27;
const entityName = "Mock Massive Numbers";
const challengeDate = "2026-05-14";

const bins = Array.from({ length: 28 }, (_, index) => ({
    attempts: index + 1,
    players: Math.max(4, Math.round(1400 * Math.exp(-index / 6) + (index % 4) * 83)),
}));

const totalPlayers = bins.reduce((sum, bin) => sum + bin.players, 0);
const averageAttempts = Number(
    (bins.reduce((sum, bin) => sum + bin.attempts * bin.players, 0) / totalPlayers).toFixed(2),
);

const mockResponse: ModeDistributionResponse = {
    mode,
    date: challengeDate,
    total_players: totalPlayers,
    average: averageAttempts,
    bins,
};

const states: Array<"exact" | "close" | "wrong"> = ["exact", "close", "wrong", "exact", "close"];

const attempts = Array.from({ length: attemptsCount }, (_, attemptIndex) => ({
    comparison: {
        genres: { result: states[(attemptIndex + 0) % states.length] },
        developers_publishers: { result: states[(attemptIndex + 1) % states.length] },
        franchises_collections: { result: states[(attemptIndex + 2) % states.length] },
        player_perspectives: { result: states[(attemptIndex + 3) % states.length] },
        game_modes: { result: states[(attemptIndex + 4) % states.length] },
        release_year: { result: states[(attemptIndex + 1) % states.length] },
    },
}));

const interceptorId = ref<number | null>(null);

const isMockedDistributionRequest = (config: AxiosRequestConfig): boolean => {
    const url = typeof config.url === "string" ? config.url : "";
    return url.includes("/api/stats/modes/") && url.includes("/distribution");
};

const installMockInterceptor = () => {
    if (interceptorId.value !== null) return;
    interceptorId.value = axios.interceptors.request.use((config) => {
        if (!isMockedDistributionRequest(config)) return config;
        config.adapter = async () =>
            ({
                data: mockResponse,
                status: 200,
                statusText: "OK",
                headers: {},
                config,
            }) as any;
        return config;
    });
};

const removeMockInterceptor = () => {
    if (interceptorId.value === null) return;
    axios.interceptors.request.eject(interceptorId.value);
    interceptorId.value = null;
};

watch(
    () => props.isOpen,
    (isOpen) => {
        if (isOpen) {
            installMockInterceptor();
        } else {
            removeMockInterceptor();
        }
    },
    { immediate: true },
);

const show = computed(() => props.isOpen);
</script>

<template>
    <ResultModal
        :show="show"
        :mode="mode"
        :attempts-count="attemptsCount"
        :attempts="attempts"
        :entity-name="entityName"
        :challenge-date="challengeDate"
        @close="emit('close')"
    />
</template>
