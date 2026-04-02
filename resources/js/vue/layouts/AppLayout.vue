<script setup>
import { Link } from "@inertiajs/vue3";
import LanguageSelector from "@/vue/components/shared/LanguageSelector.vue";
import HowToPlayModal from "@/vue/components/shared/HowToPlayModal.vue";
import AppFooter from "@/vue/components/layout/AppFooter.vue";
import { computed, ref } from "vue";
import { useI18n } from "vue-i18n";
import { CircleHelp, BarChart3, Library } from "lucide-vue-next";

const { locale, t } = useI18n();
const isHelpOpen = ref(false);

const currentLanguage = computed(() => locale.value.toUpperCase());
</script>

<template>
    <div class="min-h-screen flex flex-col bg-slate-950 text-white relative">
        <div
            class="absolute inset-0 z-0 bg-cover bg-center bg-no-repeat opacity-20 pointer-events-none"
            style="background-image: url(&quot;&quot;)"
        ></div>
        <div
            class="absolute inset-0 z-0 bg-gradient-to-b from-slate-950/80 via-slate-950/95 to-slate-950 pointer-events-none"
        ></div>

        <header
            class="border-b border-white/10 relative z-10 bg-slate-950/50 backdrop-blur-md"
        >
            <div
                class="max-w-5xl mx-auto px-4 h-14 flex items-center justify-between"
            >
                <Link
                    href="/"
                    class="text-xl font-bold tracking-tight bg-gradient-to-r from-emerald-400 to-emerald-600 bg-clip-text text-transparent"
                >
                    Gamely
                </Link>

                <div class="flex items-center gap-1 sm:gap-2">
                    <a
                        href="/games"
                        class="p-2 text-slate-400 hover:text-white hover:bg-white/10 rounded-lg transition-all active:scale-90"
                        :title="t('nav.games')"
                    >
                        <Library class="w-5 h-5" />
                    </a>

                    <button
                        class="p-2 text-slate-400 hover:text-white hover:bg-white/10 rounded-lg transition-all active:scale-90"
                        :title="t('nav.stats')"
                    >
                        <BarChart3 class="w-5 h-5" />
                    </button>

                    <button
                        @click="isHelpOpen = true"
                        class="p-2 text-slate-400 hover:text-white hover:bg-white/10 rounded-lg transition-all active:scale-90"
                        :title="t('nav.how_to_play')"
                    >
                        <CircleHelp class="w-5 h-5" />
                    </button>

                    <div class="w-px h-4 bg-white/10 mx-1 hidden sm:block"></div>

                    <LanguageSelector />

                    <span
                        class="text-xs font-bold text-slate-500 w-6 text-center select-none"
                    >
                        {{ currentLanguage }}
                    </span>
                </div>
            </div>
        </header>

        <HowToPlayModal :isOpen="isHelpOpen" @close="isHelpOpen = false" />

        <main class="flex-1 relative z-10">
            <slot />
        </main>

        <AppFooter />
    </div>
</template>
