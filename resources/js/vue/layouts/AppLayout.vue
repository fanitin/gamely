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
            style="background-image: url(&quot;&quot;)"
        ></div>
        <div
            class="absolute inset-0 z-0 bg-gradient-to-b from-onyx-dark/80 via-onyx-dark/95 to-onyx-dark pointer-events-none"
        ></div>

        <header
            class="border-b border-white/10 relative z-10 bg-onyx-dark/50 backdrop-blur-md"
        >
            <div
                class="max-w-5xl mx-auto px-4 h-14 flex items-center justify-between"
            >
                <Link
                    href="/"
                    class="text-xl font-bold tracking-tight text-teal-500"
                >
                    Gamely
                </Link>

                <div class="flex items-center gap-1 sm:gap-2">
                    <a
                        href="/games"
                        class="p-2 text-muted hover:text-white hover:bg-white/10 rounded-lg transition-all active:scale-90"
                        :title="t('nav.games')"
                    >
                        <Library class="w-5 h-5" />
                    </a>

                    <button
                        @click="isPersonalStatsOpen = true"
                        class="p-2 text-muted hover:text-white hover:bg-white/10 rounded-lg transition-all active:scale-90"
                        :title="t('nav.stats')"
                    >
                        <BarChart3 class="w-5 h-5" />
                    </button>

                    <button
                        @click="isHelpOpen = true"
                        class="p-2 text-muted hover:text-white hover:bg-white/10 rounded-lg transition-all active:scale-90"
                        :title="t('nav.how_to_play')"
                    >
                        <CircleHelp class="w-5 h-5" />
                    </button>

                    <!-- TODO: Dev preview controls. Keep visible only in local development. -->
                    <template v-if="showDevTools">
                        <button
                            @click="isDevPersonalStatsPreviewOpen = true"
                            class="px-2 py-1 text-[10px] font-bold uppercase tracking-wide text-teal-300 border border-teal-500/40 rounded-md hover:bg-teal-500/15 transition-colors"
                            title="DEV: Personal Stats Preview"
                        >
                            DEV PS
                        </button>
                        <button
                            @click="isDevWinStatsPreviewOpen = true"
                            class="px-2 py-1 text-[10px] font-bold uppercase tracking-wide text-sky-300 border border-sky-500/40 rounded-md hover:bg-sky-500/15 transition-colors"
                            title="DEV: Win Stats Preview"
                        >
                            DEV WIN
                        </button>
                    </template>

                    <div
                        class="w-px h-4 bg-white/10 mx-1 hidden sm:block"
                    ></div>

                    <LanguageSelector />

                    <span
                        class="text-xs font-bold text-muted/80 w-6 text-center select-none"
                    >
                        {{ currentLanguage }}
                    </span>
                </div>
            </div>
        </header>

        <HowToPlayModal :isOpen="isHelpOpen" @close="isHelpOpen = false" />
        <PersonalStatsModal :isOpen="isPersonalStatsOpen" @close="isPersonalStatsOpen = false" />
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
