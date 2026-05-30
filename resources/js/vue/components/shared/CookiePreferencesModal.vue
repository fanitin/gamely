<script setup lang="ts">
import { ref, watch } from "vue";
import { useI18n } from "vue-i18n";
import AppModal from "@/vue/components/ui/AppModal.vue";
import AppButton from "@/vue/components/ui/AppButton.vue";
import AppSwitch from "@/vue/components/ui/AppSwitch.vue";
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
    { key: "necessary", alwaysOn: true },
    { key: "analytics", alwaysOn: false },
    { key: "ads", alwaysOn: false },
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
        <p class="text-sm leading-relaxed text-muted -mt-3 mb-5">
            {{ t("cookies.modal.description") }}
        </p>

        <div class="divide-y divide-white/5">
            <div
                v-for="cat in categories"
                :key="cat.key"
                class="flex items-start gap-5 py-5 first:pt-3 last:pb-3"
            >
                <AppSwitch
                    class="mt-0.5"
                    :checked="cat.alwaysOn ? true : draft[cat.key as 'analytics' | 'ads']"
                    :disabled="cat.alwaysOn"
                    :aria-label="t(`cookies.categories.${cat.key}.title`)"
                    @update:checked="
                        !cat.alwaysOn &&
                            (draft[cat.key as 'analytics' | 'ads'] = $event)
                    "
                />

                <div class="flex-1 min-w-0">
                    <div class="flex items-baseline justify-between gap-3 mb-1.5">
                        <h4 class="text-[15px] font-semibold text-white tracking-tight">
                            {{ t(`cookies.categories.${cat.key}.title`) }}
                        </h4>
                        <span
                            v-if="cat.alwaysOn"
                            class="shrink-0 text-[11px] font-medium text-muted lowercase tracking-wide"
                        >
                            {{ t("cookies.modal.always_on") }}
                        </span>
                    </div>
                    <p class="text-xs leading-relaxed text-muted">
                        {{ t(`cookies.categories.${cat.key}.description`) }}
                    </p>
                </div>
            </div>
        </div>

        <div class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-end gap-3 mt-6">
            <AppButton variant="link" class="sm:mr-auto" @click="onSave">
                {{ t("cookies.modal.save") }}
            </AppButton>
            <AppButton variant="primary" @click="onAcceptAll">
                {{ t("cookies.modal.accept_all") }}
            </AppButton>
        </div>
    </AppModal>
</template>
