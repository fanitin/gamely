<script setup lang="ts">
import { Head, Link } from "@inertiajs/vue3";
import { useI18n } from "vue-i18n";
import AppLayout from "@/vue/layouts/AppLayout.vue";

const { t } = useI18n();

interface ModeCard {
    id: string;
    href: string;
    title: string;
    desc: string;
}

const modes: ModeCard[] = [
    {
        id: "classic",
        href: "/classic",
        title: "modes.classic.title",
        desc: "modes.classic.description",
    },
    {
        id: "characters",
        href: "/characters",
        title: "modes.characters.title",
        desc: "modes.characters.description",
    },
    {
        id: "covers",
        href: "#",
        title: "modes.covers.title",
        desc: "modes.covers.description",
    },
    {
        id: "photos",
        href: "#",
        title: "modes.photos.title",
        desc: "modes.photos.description",
    },
];
</script>

<template>
    <AppLayout>
        <Head :title="t('nav.home')" />

        <div class="max-w-5xl mx-auto px-4 py-16">
            <div class="text-center mb-16">
                <h1
                    class="text-5xl font-extrabold mb-4 text-white tracking-tight"
                >
                    Game<span class="text-primary-500">ly</span>
                </h1>
                <p class="text-muted text-lg max-w-2xl mx-auto">
                    {{ t("how_to_play.intro") }}
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <Link
                    v-for="mode in modes"
                    :key="mode.id"
                    :href="mode.href"
                    class="group block"
                >
                    <div
                        class="h-full bg-onyx border border-white/5 rounded-2xl p-10 text-center transition-all duration-300 transform group-hover:scale-[1.02] group-hover:bg-onyx-light/20 group-hover:border-white/10 group-active:scale-[0.98] shadow-lg"
                    >
                        <h2
                            class="text-2xl font-bold text-white mb-3 transition-colors group-hover:text-primary-400"
                        >
                            {{ t(mode.title) }}
                        </h2>
                        <p class="text-muted text-base leading-relaxed">
                            {{ t(mode.desc) }}
                        </p>
                    </div>
                </Link>
            </div>
        </div>
    </AppLayout>
</template>
