<script setup>
import { Link } from "@inertiajs/vue3";
import LanguageSelector from "@/vue/components/shared/LanguageSelector.vue";
import HowToPlayModal from "@/vue/components/shared/HowToPlayModal.vue";
import PersonalStatsModal from "@/vue/components/shared/PersonalStatsModal.vue";
import DevPersonalStatsPreviewModal from "@/vue/components/dev/DevPersonalStatsPreviewModal.vue";
import DevWinStatsPreviewModal from "@/vue/components/dev/DevWinStatsPreviewModal.vue";
import AppFooter from "@/vue/components/layout/AppFooter.vue";
import AppToastStack from "@/vue/components/ui/AppToastStack.vue";
import { computed, ref } from "vue";
import { useI18n } from "vue-i18n";
import { CircleHelp, BarChart3, Library } from "lucide-vue-next";

const { locale, t } = useI18n();
const isHelpOpen = ref(false);
const isPersonalStatsOpen = ref(false);
const isDevPersonalStatsPreviewOpen = ref(false);
const isDevWinStatsPreviewOpen = ref(false);

const currentLanguage = computed(() => locale.value.toUpperCase());
const showDevTools = import.meta.env.DEV;
</script>

<template>
    <div class="min-h-screen flex flex-col bg-onyx-dark text-white relative">
        <AppToastStack />
        <div
            class="absolute inset-0 z-0 bg-cover bg-center bg-no-repeat opacity-20 pointer-events-none"
            style="
                background-image: url(&quot;/images/background/main-bg.jpg&quot;);
            "
        ></div>
        <div
            class="absolute inset-0 z-0 bg-gradient-to-b from-onyx-dark/30 via-onyx-dark/70 to-onyx-dark pointer-events-none"
        ></div>

        <header class="relative z-50 w-full border-b border-white/10 bg-onyx-dark/60 backdrop-blur-xl">
            <div
                class="max-w-5xl mx-auto h-16 flex items-center justify-between px-4 sm:px-6"
            >
                <Link
                    href="/"
                    class="text-xl font-black tracking-tight text-white flex items-center group"
                >
                    Game<span
                        class="text-teal-400 group-hover:text-teal-300 transition-colors"
                        >ly</span
                    >
                </Link>

                <div class="flex items-center gap-1 sm:gap-2">
                    <a
                        href="/games"
                        class="flex flex-col items-center justify-center min-w-[3.5rem] h-12 text-muted hover:text-white rounded-xl transition-all active:scale-95"
                        :title="t('nav.games')"
                    >
                        <Library class="w-5 h-5 mb-1" />
                        <span class="text-[10px] font-medium leading-none">{{
                            t("nav.games")
                        }}</span>
                    </a>

                    <button
                        @click="isPersonalStatsOpen = true"
                        class="flex flex-col items-center justify-center min-w-[3.5rem] h-12 text-muted hover:text-white rounded-xl transition-all active:scale-95"
                        :title="t('nav.stats')"
                    >
                        <BarChart3 class="w-5 h-5 mb-1" />
                        <span class="text-[10px] font-medium leading-none">{{
                            t("nav.stats")
                        }}</span>
                    </button>

                    <button
                        @click="isHelpOpen = true"
                        class="flex flex-col items-center justify-center min-w-[3.5rem] h-12 text-muted hover:text-white rounded-xl transition-all active:scale-95"
                        :title="t('nav.how_to_play')"
                    >
                        <CircleHelp class="w-5 h-5 mb-1" />
                        <span class="text-[10px] font-medium leading-none">{{
                            t("nav.how_to_play")
                        }}</span>
                    </button>

                    <!-- DEV TOOLING -->
                    <template v-if="showDevTools">
                        <button
                            @click="isDevPersonalStatsPreviewOpen = true"
                            class="px-2 py-1 text-[10px] font-bold uppercase tracking-wide text-teal-300 border border-teal-500/40 rounded-lg hover:bg-teal-500/15 transition-colors"
                            title="DEV: Personal Stats Preview"
                        >
                            DEV PS
                        </button>
                        <button
                            @click="isDevWinStatsPreviewOpen = true"
                            class="px-2 py-1 text-[10px] font-bold uppercase tracking-wide text-sky-300 border border-sky-500/40 rounded-lg hover:bg-sky-500/15 transition-colors"
                            title="DEV: Win Stats Preview"
                        >
                            DEV WIN
                        </button>
                    </template>

                    <div
                        class="w-px h-8 bg-white/10 mx-1 hidden sm:block"
                    ></div>

                    <LanguageSelector />
                </div>
            </div>
        </header>

        <HowToPlayModal :isOpen="isHelpOpen" @close="isHelpOpen = false" />
        <PersonalStatsModal
            :isOpen="isPersonalStatsOpen"
            @close="isPersonalStatsOpen = false"
        />
        <!-- TODO: Remove or move to dedicated dev panel if preview tooling grows. -->
        <DevPersonalStatsPreviewModal
            v-if="showDevTools"
            :isOpen="isDevPersonalStatsPreviewOpen"
            @close="isDevPersonalStatsPreviewOpen = false"
        />
        <DevWinStatsPreviewModal
            v-if="showDevTools"
            :isOpen="isDevWinStatsPreviewOpen"
            @close="isDevWinStatsPreviewOpen = false"
        />

        <main class="flex-1 relative z-10">
            <slot />
        </main>

        <AppFooter />
    </div>
</template>
