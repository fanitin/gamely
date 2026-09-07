<script setup lang="ts">
import { computed, watch, nextTick } from "vue";
import { usePage } from "@inertiajs/vue3";
import { useI18n } from "vue-i18n";
import { useAds } from "@/vue/composables/useAds";
import { adSlots, type AdSlotName } from "@/vue/config/adSlots";

const props = withDefaults(
    defineProps<{
        slot: AdSlotName;
        format?: "banner" | "tower";
    }>(),
    {
        format: "banner",
    },
);

const { t } = useI18n();
const { clientId, configured, enabled, isDev, showAd } = useAds();
const page = usePage();

const slotCode = computed(() => adSlots[props.slot]);
const isVisible = computed(() => configured && (isDev || enabled.value));

const render = async () => {
    if (!configured || !enabled.value || isDev) return;
    await nextTick();
    showAd();
};

watch(
    () => page.url,
    () => render(),
    { immediate: true },
);
</script>

<template>
    <div v-if="isVisible" class="my-6 w-full">
        <p
            class="text-center text-[10px] font-medium uppercase tracking-widest text-muted/60 mb-1"
        >
            {{ t("ads.label") }}
        </p>

        <div
            v-if="isDev"
            class="flex items-center justify-center border border-muted/30 text-muted/50 text-xs"
            :class="format === 'tower' ? 'w-40 h-[600px] mx-auto' : 'w-full h-[120px] sm:h-[250px]'"
        >
            {{ format === "tower" ? "160×600" : "RESPONSIVE" }}
        </div>

        <ins
            v-else
            :key="page.url"
            class="adsbygoogle block"
            :class="format === 'tower' ? 'w-40 mx-auto' : 'w-full'"
            :style="
                format === 'tower'
                    ? 'display:inline-block;width:160px;height:600px'
                    : 'display:block'
            "
            :data-ad-client="clientId"
            :data-ad-slot="slotCode"
            :data-ad-format="format === 'tower' ? undefined : 'auto'"
            :data-full-width-responsive="format === 'tower' ? undefined : 'true'"
        />
    </div>
</template>
