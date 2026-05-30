<script setup lang="ts">
import { useI18n } from "vue-i18n";
import { Cookie, SlidersHorizontal } from "lucide-vue-next";
import AppButton from "@/vue/components/ui/AppButton.vue";
import { useCookieConsent } from "@/vue/composables/useCookieConsent";

withDefaults(
    defineProps<{
        corner?: "left" | "right";
    }>(),
    { corner: "right" },
);

const { t, locale } = useI18n();
const { bannerOpen, acceptAll, rejectNonEssential, openPreferences } =
    useCookieConsent();
</script>

<template>
    <Transition
        enter-active-class="transition duration-300 ease-out"
        enter-from-class="opacity-0 translate-y-4"
        enter-to-class="opacity-100 translate-y-0"
        leave-active-class="transition duration-200 ease-in"
        leave-from-class="opacity-100 translate-y-0"
        leave-to-class="opacity-0 translate-y-4"
    >
        <div
            v-if="bannerOpen"
            role="region"
            aria-label="Cookie consent"
            class="fixed bottom-4 sm:bottom-6 z-40 w-[calc(100vw-2rem)] sm:w-95"
            :class="corner === 'left' ? 'left-4 sm:left-6' : 'right-4 sm:right-6'"
        >
            <div
                class="rounded-2xl bg-onyx-dark border border-white/10 shadow-[0_8px_32px_rgba(0,0,0,0.55)] p-5"
            >
                <div class="flex items-start gap-3 mb-3">
                    <div
                        class="shrink-0 w-10 h-10 rounded-xl bg-teal-500/10 border border-teal-500/25 text-teal-400 flex items-center justify-center"
                    >
                        <Cookie class="w-5 h-5" />
                    </div>
                    <h3 class="text-[15px] font-bold text-white leading-tight pt-1">
                        {{ t("cookies.banner.title") }}
                    </h3>
                </div>

                <p class="text-[13px] leading-relaxed text-slate-300/90 mb-4">
                    {{ t("cookies.banner.description") }}
                    <a
                        href="/cookies"
                        class="text-teal-400 hover:text-teal-300 underline underline-offset-2"
                    >
                        {{ t("cookies.banner.policy") }}
                    </a>
                </p>

                <div class="flex flex-col gap-2">
                    <AppButton variant="primary" full-width @click="acceptAll(locale)">
                        {{ t("cookies.banner.accept_all") }}
                    </AppButton>
                    <div class="flex gap-2">
                        <AppButton
                            variant="outline"
                            size="sm"
                            full-width
                            @click="rejectNonEssential(locale)"
                        >
                            {{ t("cookies.banner.reject") }}
                        </AppButton>
                        <AppButton
                            variant="ghost"
                            size="sm"
                            full-width
                            @click="openPreferences"
                        >
                            <SlidersHorizontal class="w-4 h-4 mr-1.5" />
                            {{ t("cookies.banner.manage") }}
                        </AppButton>
                    </div>
                </div>
            </div>
        </div>
    </Transition>
</template>
