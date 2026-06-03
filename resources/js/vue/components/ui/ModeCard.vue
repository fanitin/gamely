<script setup lang="ts">
import { computed } from "vue";
import { useI18n } from "vue-i18n";
import { Link } from "@inertiajs/vue3";
import { ArrowRight } from "lucide-vue-next";

const { t } = useI18n();

type ModeKey = "classic" | "screens" | "character";

const props = defineProps<{
    href: string;
    title: string;
    desc: string;
    icon: any;
    modeKey: ModeKey;
    avgAttempts?: string;
    nextIn?: string;
}>();

const colorClasses = computed(() => {
    const map: Record<
        ModeKey,
        {
            iconBg: string;
            iconText: string;
            iconBorder: string;
            title: string;
            ctaBg: string;
            edge: string;
            border: string;
        }
    > = {
        classic: {
            iconBg: "group-hover:bg-mode-classic/10",
            iconText: "group-hover:text-mode-classic",
            iconBorder: "group-hover:border-mode-classic/30",
            title: "group-hover:text-mode-classic",
            ctaBg: "group-hover:bg-mode-classic group-hover:border-mode-classic group-hover:text-onyx-dark",
            edge: "before:bg-mode-classic",
            border: "group-hover:border-mode-classic/30",
        },
        screens: {
            iconBg: "group-hover:bg-mode-screens/10",
            iconText: "group-hover:text-mode-screens",
            iconBorder: "group-hover:border-mode-screens/30",
            title: "group-hover:text-mode-screens",
            ctaBg: "group-hover:bg-mode-screens group-hover:border-mode-screens group-hover:text-onyx-dark",
            edge: "before:bg-mode-screens",
            border: "group-hover:border-mode-screens/30",
        },
        character: {
            iconBg: "group-hover:bg-mode-character/10",
            iconText: "group-hover:text-mode-character",
            iconBorder: "group-hover:border-mode-character/30",
            title: "group-hover:text-mode-character",
            ctaBg: "group-hover:bg-mode-character group-hover:border-mode-character group-hover:text-onyx-dark",
            edge: "before:bg-mode-character",
            border: "group-hover:border-mode-character/30",
        },
    };
    return map[props.modeKey];
});
</script>

<template>
    <Link
        :href="href"
        class="group block w-full outline-none focus-visible:ring-2 focus-visible:ring-white/30 rounded-2xl"
    >
        <div
            class="relative overflow-hidden bg-onyx-dark/90 border border-white/10 rounded-2xl p-5 sm:p-6 lg:p-8 flex items-center gap-4 sm:gap-5 lg:gap-7 transition-all duration-300 ease-out hover:-translate-y-px hover:bg-onyx-dark/80"
            :class="[
                colorClasses.border,
                `before:content-[''] before:absolute before:left-0 before:top-[18%] before:bottom-[18%] before:w-[2px] before:rounded-full before:opacity-0 before:transition-all before:duration-300 group-hover:before:opacity-100 group-hover:before:top-[8%] group-hover:before:bottom-[8%]`,
                colorClasses.edge,
            ]"
        >
            <div
                class="relative shrink-0 flex items-center justify-center w-12 h-12 sm:w-14 sm:h-14 lg:w-16 lg:h-16 rounded-xl lg:rounded-2xl bg-white/5 border border-white/10 text-white transition-colors duration-300"
                :class="[
                    colorClasses.iconBg,
                    colorClasses.iconBorder,
                    colorClasses.iconText,
                ]"
            >
                <component :is="icon" class="w-5 h-5 sm:w-6 sm:h-6 lg:w-7 lg:h-7" stroke-width="1.75" />
            </div>

            <div class="relative flex-1 min-w-0">
                <h2
                    class="text-lg sm:text-xl lg:text-2xl font-bold text-white tracking-tight transition-colors duration-300"
                    :class="colorClasses.title"
                >
                    {{ title }}
                </h2>
                <p class="text-muted text-sm sm:text-[15px] lg:text-base leading-snug lg:mt-1 line-clamp-2">
                    {{ desc }}
                </p>
            </div>

            <div class="relative hidden sm:flex items-center gap-5 lg:gap-7 shrink-0">
                <div v-if="avgAttempts" class="flex flex-col items-end">
                    <span class="font-mono text-sm lg:text-base text-white font-medium tabular-nums">
                        {{ avgAttempts }}
                    </span>
                    <span
                        class="font-mono text-[10px] lg:text-[11px] uppercase tracking-wider text-muted/70 mt-0.5"
                    >
                        {{ t("mode_card.avg") }}
                    </span>
                </div>

                <div v-if="nextIn" class="flex flex-col items-end">
                    <span class="font-mono text-sm lg:text-base text-white font-medium tabular-nums">
                        {{ nextIn }}
                    </span>
                    <span
                        class="font-mono text-[10px] lg:text-[11px] uppercase tracking-wider text-muted/70 mt-0.5"
                    >
                        {{ t("mode_card.next_in") }}
                    </span>
                </div>

                <div
                    class="w-9 h-9 lg:w-11 lg:h-11 rounded-full bg-white/5 border border-white/10 text-muted flex items-center justify-center transition-all duration-300"
                    :class="colorClasses.ctaBg"
                >
                    <ArrowRight class="w-4 h-4 lg:w-5 lg:h-5" stroke-width="2" />
                </div>
            </div>

            <div
                v-if="avgAttempts || nextIn"
                class="sm:hidden absolute right-5 top-5 flex items-center gap-3 font-mono text-[10px] uppercase tracking-wider text-muted/70"
            >
                <span v-if="avgAttempts">{{ avgAttempts }} avg</span>
                <span v-if="avgAttempts && nextIn">·</span>
                <span v-if="nextIn">{{ nextIn }}</span>
            </div>
        </div>
    </Link>
</template>
