<script setup lang="ts">
import { Head } from "@inertiajs/vue3";
import { useI18n } from "vue-i18n";
import AppLayout from "@/vue/layouts/AppLayout.vue";
import ModeCard from "@/vue/components/ui/ModeCard.vue";
import {
    Gamepad2,
    Image as ImageIcon,
    VenetianMask,
    Sparkles,
    Flame,
    Star,
} from "lucide-vue-next";

const { t } = useI18n();

interface GameMode {
    id: string;
    href: string;
    title: string;
    desc: string;
    icon: any;
    badge?: {
        text: string;
        icon: any;
        colorClass: string;
    };
}

const modes: GameMode[] = [
    {
        id: "classic",
        href: "/classic",
        title: "modes.classic.title",
        desc: "modes.classic.description",
        icon: Gamepad2,
    },
    {
        id: "game_screenshots",
        href: "/screenshots",
        title: "modes.game_screenshots.title",
        desc: "modes.game_screenshots.description",
        icon: ImageIcon,
    },
    {
        id: "character",
        href: "/character",
        title: "modes.character.title",
        desc: "modes.character.description",
        icon: VenetianMask,
    },
];
</script>

<template>
    <AppLayout>
        <Head :title="t('nav.home')" />

        <div
            class="max-w-3xl mx-auto px-4 py-12 sm:py-20 flex flex-col items-center"
        >
            <div class="text-center mb-12 sm:mb-16 w-full relative">
                <div
                    class="absolute inset-0 bg-teal-500/20 blur-[100px] rounded-full w-48 h-48 mx-auto -top-10 -z-10"
                ></div>
                <h1
                    class="text-5xl sm:text-7xl font-black mb-6 text-white tracking-tighter drop-shadow-lg"
                >
                    Game<span class="text-teal-500">ly</span>
                </h1>
                <p
                    class="text-muted text-lg sm:text-xl max-w-xl mx-auto font-medium tracking-wide"
                >
                    {{ t("nav.site_description") }}
                </p>
            </div>

            <div class="w-full flex flex-col gap-5 sm:gap-6">
                <ModeCard
                    v-for="mode in modes"
                    :key="mode.id"
                    :href="mode.href"
                    :title="t(mode.title)"
                    :desc="t(mode.desc)"
                    :icon="mode.icon"
                    :badge="mode.badge"
                />
            </div>
        </div>
    </AppLayout>
</template>
