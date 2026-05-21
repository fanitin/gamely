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

type ModeKey = "classic" | "screens" | "character";

interface GameMode {
    id: ModeKey;
    href: string;
    title: string;
    desc: string;
    icon: any;
    avg: string; // TODO: replace with real stat from backend
}

const modes: GameMode[] = [
    {
        id: "classic",
        href: "/classic",
        title: "modes.classic.title",
        desc: "modes.classic.description",
        icon: Gamepad2,
        avg: "4.2",
    },
    {
        id: "screens",
        href: "/screenshots",
        title: "modes.game_screenshots.title",
        desc: "modes.game_screenshots.description",
        icon: ImageIcon,
        avg: "5.8",
    },
    {
        id: "character",
        href: "/character",
        title: "modes.character.title",
        desc: "modes.character.description",
        icon: VenetianMask,
        avg: "6.1",
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

const nextIn = computed(() => {
    const d = new Date(now.value);
    const next = Date.UTC(
        d.getUTCFullYear(),
        d.getUTCMonth(),
        d.getUTCDate() + 1,
        0,
        0,
        0,
    );
    const diff = Math.max(0, next - now.value);
    const h = Math.floor(diff / 3_600_000);
    const m = Math.floor((diff % 3_600_000) / 60_000);
    return `${String(h).padStart(2, "0")}:${String(m).padStart(2, "0")}`;
});
</script>

<template>
    <AppLayout>
        <Head :title="t('nav.home')" />

        <div class="max-w-2xl mx-auto px-4 py-10 sm:py-14 flex flex-col items-center">
            <div class="self-start mb-10 sm:mb-12">
                <h1
                    class="text-5xl sm:text-6xl font-black mb-3 text-white tracking-tighter leading-none"
                >
                    Game<span class="text-teal-400">ly</span>
                </h1>
                <p class="text-muted text-base sm:text-lg max-w-md leading-relaxed">
                    {{ t("nav.site_description") }}
                </p>
            </div>

            <div class="w-full flex flex-col gap-3 sm:gap-4">
                <ModeCard
                    v-for="mode in modes"
                    :key="mode.id"
                    :href="mode.href"
                    :title="t(mode.title)"
                    :desc="t(mode.desc)"
                    :icon="mode.icon"
                    :mode-key="mode.id"
                    :avg-attempts="mode.avg"
                    :next-in="nextIn"
                />
            </div>
        </div>
    </AppLayout>
</template>
