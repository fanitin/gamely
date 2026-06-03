<script setup lang="ts">
import { useI18n } from "vue-i18n";
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
                class="rounded-2xl bg-onyx-dark/95 backdrop-blur-xl border border-white/10 p-5"
                style="
                    box-shadow:
                        0 1px 0 rgba(255, 255, 255, 0.06) inset,
                        0 16px 40px -12px rgba(0, 0, 0, 0.6),
                        0 4px 12px -2px rgba(26, 28, 29, 0.5);
                "
            >
                <h3
                    class="text-base font-semibold text-white tracking-tight mb-2"
                >
                    {{ t("cookies.banner.title") }}
                </h3>

                <p class="text-sm leading-relaxed text-muted mb-5">
                    {{ t("cookies.banner.description") }}
                    <a
                        href="/cookie-policy"
                        class="text-teal-400 hover:text-teal-300 transition-colors"
                    >
                        {{ t("cookies.banner.policy") }}
                    </a>
                </p>

                <AppButton
                    variant="primary"
                    full-width
                    class="mb-3"
                    @click="acceptAll(locale)"
                >
                    {{ t("cookies.banner.accept_all") }}
                </AppButton>

                <div class="flex items-center justify-between">
                    <AppButton variant="link" @click="rejectNonEssential(locale)">
                        {{ t("cookies.banner.reject") }}
                    </AppButton>
                    <AppButton variant="link" @click="openPreferences">
                        {{ t("cookies.banner.manage") }}
                    </AppButton>
                </div>
            </div>
        </div>
    </Transition>
</template>
