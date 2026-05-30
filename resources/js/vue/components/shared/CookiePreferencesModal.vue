<script setup lang="ts">
import { ref, watch } from "vue";
import { useI18n } from "vue-i18n";
import { ShieldCheck, BarChart3, Megaphone } from "lucide-vue-next";
import AppModal from "@/vue/components/ui/AppModal.vue";
import AppButton from "@/vue/components/ui/AppButton.vue";
import { useCookieConsent } from "@/vue/composables/useCookieConsent";

const { t, locale } = useI18n();
const {
    modalOpen,
    closePreferences,
    savePreferences,
    acceptAll,
    getDraft,
    hasGlobalPrivacyControl,
} = useCookieConsent();

const draft = ref({ analytics: false, ads: false });

watch(modalOpen, (open) => {
    if (!open) return;
    const base = getDraft();
    if (hasGlobalPrivacyControl()) {
        draft.value = { analytics: false, ads: false };
    } else {
        draft.value = { ...base };
    }
});

const categories = [
    { key: "necessary", icon: ShieldCheck, alwaysOn: true },
    { key: "analytics", icon: BarChart3, alwaysOn: false },
    { key: "ads", icon: Megaphone, alwaysOn: false },
] as const;

const onSave = () => savePreferences(draft.value, locale.value);
const onAcceptAll = () => acceptAll(locale.value);
</script>

<template>
    <AppModal
        :is-open="modalOpen"
        :title="t('cookies.modal.title')"
        size="lg"
        @close="closePreferences"
    >
        <p class="text-[13.5px] leading-relaxed text-muted -mt-2 mb-2">
            {{ t("cookies.modal.description") }}
        </p>

        <div class="divide-y divide-white/5">
            <div
                v-for="cat in categories"
                :key="cat.key"
                class="flex items-start gap-4 py-4"
            >
                <div
                    class="shrink-0 w-9 h-9 rounded-lg bg-white/5 border border-white/10 text-muted flex items-center justify-center mt-0.5"
                >
                    <component :is="cat.icon" class="w-[18px] h-[18px]" />
                </div>

                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2">
                        <h4 class="text-sm font-bold text-white">
                            {{ t(`cookies.categories.${cat.key}.title`) }}
                        </h4>
                        <span
                            v-if="cat.alwaysOn"
                            class="text-[10px] font-bold uppercase tracking-wider text-teal-400/90 bg-teal-500/10 border border-teal-500/20 rounded px-1.5 py-0.5"
                        >
                            {{ t("cookies.modal.always_on") }}
                        </span>
                    </div>
                    <p class="text-[12.5px] leading-relaxed text-muted mt-1">
                        {{ t(`cookies.categories.${cat.key}.description`) }}
                    </p>
                </div>

                <button
                    type="button"
                    role="switch"
                    :aria-checked="cat.alwaysOn ? true : draft[cat.key as 'analytics' | 'ads']"
                    :aria-label="t(`cookies.categories.${cat.key}.title`)"
                    :disabled="cat.alwaysOn"
                    class="relative shrink-0 w-12 h-7 rounded-full transition-colors duration-200 outline-none focus-visible:ring-2 focus-visible:ring-teal-500/50"
                    :class="[
                        (cat.alwaysOn || draft[cat.key as 'analytics' | 'ads'])
                            ? 'bg-teal-500'
                            : 'bg-onyx-light',
                        cat.alwaysOn ? 'opacity-60 cursor-not-allowed' : 'cursor-pointer',
                    ]"
                    @click="
                        !cat.alwaysOn &&
                            (draft[cat.key as 'analytics' | 'ads'] =
                                !draft[cat.key as 'analytics' | 'ads'])
                    "
                >
                    <span
                        class="absolute top-1 left-1 w-5 h-5 rounded-full bg-white shadow-md transition-transform duration-200"
                        :class="
                            (cat.alwaysOn || draft[cat.key as 'analytics' | 'ads'])
                                ? 'translate-x-5'
                                : 'translate-x-0'
                        "
                    />
                </button>
            </div>
        </div>

        <div class="flex flex-col-reverse sm:flex-row gap-2 mt-6">
            <AppButton variant="outline" full-width @click="onSave">
                {{ t("cookies.modal.save") }}
            </AppButton>
            <AppButton variant="primary" full-width @click="onAcceptAll">
                {{ t("cookies.modal.accept_all") }}
            </AppButton>
        </div>
    </AppModal>
</template>
