<script setup>
import { computed } from "vue";
import { useI18n } from "vue-i18n";
import AppModal from "@/vue/components/ui/AppModal.vue";
import AppButton from "@/vue/components/ui/AppButton.vue";

defineProps({
    isOpen: {
        type: Boolean,
        required: true,
    },
});

defineEmits(["close"]);

const { t } = useI18n();

const examples = computed(() => [
    {
        property: "genres",
        name: "Action, RPG",
        status: "exact",
        description: t("how_to_play.exact_match"),
    },
    {
        property: "genres",
        name: "Action, RPG",
        status: "close",
        description: t("how_to_play.partial_match"),
    },
    {
        property: "release_year",
        name: "2015 ▲",
        status: "close",
        description: t("how_to_play.close_match"),
    },
    {
        property: "platforms",
        name: "PS5",
        status: "wrong",
        description: t("how_to_play.no_match"),
    },
]);
</script>

<template>
    <AppModal
        :isOpen="isOpen"
        :title="t('nav.how_to_play')"
        size="lg"
        @close="$emit('close')"
    >
        <div class="space-y-6 text-muted">
            <div class="space-y-1">
                <p class="text-sm font-semibold text-white/90 leading-relaxed">
                    {{ t("how_to_play.intro") }}
                </p>
                <p class="text-xs text-muted/60">
                    {{ t("how_to_play.infinite_hint") }}
                </p>
            </div>

            <div class="space-y-4 pt-4 border-t border-white/10">
                <div
                    v-for="(example, index) in examples"
                    :key="index"
                    class="flex items-center gap-4 py-1"
                >
                    <div
                        class="min-w-[80px] h-10 px-2 flex items-center justify-center rounded-xl font-bold text-xs uppercase tracking-wider transition-colors shrink-0"
                        :class="{
                            'bg-forest-500 text-white':
                                example.status === 'exact',
                            'bg-gold-500 text-white':
                                example.status === 'close',
                            'bg-onyx-light border border-white/5 text-muted':
                                example.status === 'wrong',
                        }"
                    >
                        {{ example.name }}
                    </div>

                    <div class="flex-1 min-w-0">
                        <span
                            class="block text-[10px] font-bold text-muted/60 uppercase tracking-widest mb-0.5"
                        >
                            {{ t(`attributes.${example.property}`) }}
                        </span>
                        <p class="text-xs text-muted leading-relaxed">
                            {{ example.description }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="pt-4 border-t border-white/10">
                <p class="text-xs text-muted leading-relaxed">
                    {{ t("how_to_play.hints_info") }}
                </p>
            </div>

            <AppButton
                full-width
                variant="primary"
                size="lg"
                @click="$emit('close')"
            >
                {{ t("game.submit") }}
            </AppButton>
        </div>
    </AppModal>
</template>
