<script setup>
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

const examples = [
    {
        property: "genre",
        name: "Action, RPG",
        status: "exact",
        description: t("how_to_play.example_genre_match"),
    },
    {
        property: "genre",
        name: "Action, RPG",
        status: "close",
        description: t("how_to_play.example_genre_partial"),
    },
    {
        property: "year",
        name: "2015 ▲",
        status: "close",
        description: t("how_to_play.example_year_close"),
    },
    {
        property: "platform",
        name: "PS5",
        status: "wrong",
        description: t("how_to_play.example_wrong"),
    },
];
</script>

<template>
    <AppModal
        :isOpen="isOpen"
        :title="t('nav.how_to_play')"
        size="lg"
        @close="$emit('close')"
    >
        <div class="space-y-6 text-slate-300">
            <div class="space-y-2">
                <p class="text-sm leading-relaxed font-medium text-white/90">
                    {{ t("how_to_play.intro") }}
                </p>
                <p class="text-xs text-slate-500 italic">
                    {{ t("how_to_play.infinite_hint") }}
                </p>
            </div>

            <div class="space-y-5 pt-4 border-t border-white/10">
                <div
                    v-for="(example, index) in examples"
                    :key="index"
                    class="space-y-2"
                >
                    <div class="flex items-center gap-4">
                        <div
                            class="min-w-[80px] h-10 px-2 flex items-center justify-center rounded-lg font-bold text-[13px] uppercase tracking-wider border border-white/5 shadow-inner"
                            :class="{
                                'bg-emerald-600 text-white shadow-emerald-500/10':
                                    example.status === 'exact',
                                'bg-amber-500 text-white shadow-amber-500/10':
                                    example.status === 'close',
                                'bg-slate-800/80 text-slate-400':
                                    example.status === 'wrong',
                            }"
                        >
                            {{ example.name }}
                        </div>
                        <div class="flex-1 space-y-0.5">
                            <p
                                class="text-[11px] font-bold text-slate-500 uppercase tracking-widest"
                            >
                                {{ t(`attributes.${example.property}`) }}
                            </p>
                            <p class="text-xs leading-snug text-slate-300">
                                {{ example.description }}
                            </p>
                        </div>
                    </div>
                </div>
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
