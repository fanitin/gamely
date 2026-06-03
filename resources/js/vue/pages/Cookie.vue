<script setup lang="ts">
import LegalDocument from "@/vue/components/legal/LegalDocument.vue";
import AppButton from "@/vue/components/ui/AppButton.vue";
import { useCookieConsent } from "@/vue/composables/useCookieConsent";
import { useI18n } from "vue-i18n";

defineProps<{
    meta: { version: string; effective_date: string; last_updated: string };
}>();

const { t } = useI18n();
const { openPreferences } = useCookieConsent();
</script>

<template>
    <LegalDocument ns="legal.cookie" :meta="meta">
        <template #after-3>
            <AppButton
                variant="outline"
                size="sm"
                class="mt-3"
                @click="openPreferences"
            >
                {{ t("footer.cookie_settings") }}
            </AppButton>
        </template>
    </LegalDocument>
</template>