<script setup lang="ts">
import { Head } from "@inertiajs/vue3";
import { useI18n } from "vue-i18n";
import { ref, computed, onMounted, onBeforeUnmount } from "vue";
import AppLayout from "@/vue/layouts/AppLayout.vue";
import ModeCard from "@/vue/components/ui/ModeCard.vue";
import {
    Gamepad2,
    Image as ImageIcon,
    VenetianMask,
} from "lucide-vue-next";

const { t } = useI18n();

const props = defineProps<{
    avgAttempts: { classic: number | null; game_screenshots: number | null; character: number | null };
    nextResetAt: string;
}>();

const FALLBACK_AVG = { classic: 4.2, game_screenshots: 5.8, character: 6.1 };

function avgFor(key: keyof typeof FALLBACK_AVG): string {
    const val = props.avgAttempts?.[key];
    return (val != null ? val : FALLBACK_AVG[key]).toFixed(1);
}

type ModeKey = "classic" | "screens" | "character";

interface GameMode {
    id: ModeKey;
    href: string;
    title: string;
    desc: string;
    icon: any;
    avgKey: keyof typeof FALLBACK_AVG;
}

const modes: GameMode[] = [
    {
        id: "classic",
        href: "/classic",
        title: "modes.classic.title",
        desc: "modes.classic.description",
        icon: Gamepad2,
        avgKey: "classic",
    },
    {
        id: "screens",
        href: "/screenshots",
        title: "modes.game_screenshots.title",
        desc: "modes.game_screenshots.description",
        icon: ImageIcon,
        avgKey: "game_screenshots",
    },
    {
        id: "character",
        href: "/character",
        title: "modes.character.title",
        desc: "modes.character.description",
        icon: VenetianMask,
        avgKey: "character",
    },
];

const now = ref(Date.now());
let timer: number | undefined;
onMounted(() => {
    timer = window.setInterval(() => (now.value = Date.now()), 1000);
});
onBeforeUnmount(() => {
    if (timer) clearInterval(timer);
});

const nextResetMs = computed(() => Date.parse(props.nextResetAt));

const nextIn = computed(() => {
    const diff = Math.max(0, nextResetMs.value - now.value);
    const h = Math.floor(diff / 3_600_000);
    const m = Math.floor((diff % 3_600_000) / 60_000);
    return `${String(h).padStart(2, "0")}:${String(m).padStart(2, "0")}`;
});
</script>

<template>
    <AppLayout>
        <Head :title="t('nav.home')" />

        <div class="max-w-2xl lg:max-w-3xl mx-auto px-4 py-6 sm:py-14 flex flex-col items-center">
            <div class="text-center sm:text-left mb-6 sm:mb-12">
                <h1
                    class="text-4xl sm:text-6xl font-black mb-2 sm:mb-3 text-white tracking-tighter leading-none text-center"
                >
                    Game<span class="text-teal-400">ly</span>
                </h1>
                <p class="text-muted text-sm sm:text-lg max-w-md leading-relaxed mx-auto sm:mx-0">
                    {{ t("nav.site_description") }}
                </p>
            </div>

            <div class="w-full flex flex-col gap-3 sm:gap-4 lg:gap-5">
                <ModeCard
                    v-for="mode in modes"
                    :key="mode.id"
                    :href="mode.href"
                    :title="t(mode.title)"
                    :desc="t(mode.desc)"
                    :icon="mode.icon"
                    :mode-key="mode.id"
                    :avg-attempts="avgFor(mode.avgKey)"
                    :next-in="nextIn"
                />
            </div>
        </div>
    </AppLayout>
</template>
