<script setup>
import { useI18n } from "vue-i18n";
import { useCookieConsent } from "@/vue/composables/useCookieConsent";
const { t } = useI18n();
const { openPreferences } = useCookieConsent();
</script>

<template>
    <footer
        class="mt-auto border-t border-white/5 bg-onyx-dark/50 relative z-10 select-none"
    >
        <div class="max-w-7xl mx-auto px-4 py-4">
            <div
                class="flex flex-col sm:flex-row justify-between items-center gap-4 text-sm text-muted/80"
            >
                <div class="font-medium">
                    &copy; {{ new Date().getFullYear() }}
                    <span class="text-white/80">Gamely</span>
                    <span class="mx-2 opacity-20">|</span>
                    {{ t("footer.by") }} <span class="text-muted">Faniko</span>
                </div>

                <nav
                    class="flex gap-6 font-semibold uppercase tracking-wider text-[11px]"
                >
                    <a href="#" class="hover:text-white transition-colors">
                        {{ t("footer.privacy") }}
                    </a>
                    <a href="#" class="hover:text-white transition-colors">
                        {{ t("footer.contact") }}
                    </a>
<!--                    <button-->
<!--                        type="button"-->
<!--                        class="uppercase tracking-wider hover:text-white transition-colors"-->
<!--                        @click="openPreferences"-->
<!--                    >-->
<!--                        {{ t("footer.cookie_settings") }}-->
<!--                    </button>-->
                </nav>
            </div>

            <p
                class="mt-2 text-[13px] text-muted/80 text-center sm:text-left italic opacity-80"
            >
                {{ t("footer.disclaimer") }}
                <a href="https://igdb.com" class="hover:text-white underline">
                    IGDB</a>.
            </p>
        </div>
    </footer>
</template>
