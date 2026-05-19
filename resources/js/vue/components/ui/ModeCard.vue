<script setup lang="ts">
import { Link } from "@inertiajs/vue3";
import { ChevronRight } from "lucide-vue-next";

defineProps<{
    href: string;
    title: string;
    desc: string;
    icon: any;
    badge?: {
        text: string;
        icon: any;
        colorClass: string;
    };
}>();
</script>

<template>
    <Link
        :href="href"
        class="group block w-full outline-none focus-visible:ring-2 focus-visible:ring-teal-500 rounded-3xl"
    >
        <div
            class="relative overflow-hidden bg-onyx-dark/60 backdrop-blur-xl border border-white/10 rounded-3xl p-6 sm:p-8 transition-all duration-300 ease-out group-hover:scale-[1.01] group-hover:bg-onyx-dark/80 group-hover:border-teal-500/30 group-active:scale-[0.99] shadow-2xl flex flex-col sm:flex-row gap-6 items-start sm:items-center"
        >
            <div
                class="absolute -inset-x-full inset-y-0 w-1/2 z-0 bg-linear-to-r from-transparent via-white/5 to-transparent group-hover:animate-shimmer transform -skew-x-12 opacity-0 group-hover:opacity-100 transition-opacity duration-500"
            ></div>

            <div
                class="shrink-0 relative z-10 flex items-center justify-center w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-white/5 border border-white/10 group-hover:border-teal-500/30 group-hover:bg-teal-500/10 transition-colors duration-300"
            >
                <component
                    :is="icon"
                    class="w-8 h-8 sm:w-10 sm:h-10 text-white group-hover:text-teal-400 transition-colors duration-300"
                    stroke-width="1.5"
                />
            </div>

            <div class="flex-1 relative z-10 min-w-0">
                <div class="flex items-center gap-3 mb-2 flex-wrap">
                    <h2
                        class="text-2xl sm:text-3xl font-bold text-white tracking-tight truncate"
                    >
                        {{ title }}
                    </h2>

                    <span
                        v-if="badge"
                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold border uppercase tracking-wider"
                        :class="badge.colorClass"
                    >
                        <component :is="badge.icon" class="w-3 h-3" />
                        {{ badge.text }}
                    </span>
                </div>

                <p
                    class="text-muted text-base sm:text-lg leading-relaxed max-w-xl"
                >
                    {{ desc }}
                </p>
            </div>

            <div
                class="hidden sm:flex flex-col items-end gap-3 shrink-0 relative z-10"
            >
                <div
                    class="w-10 h-10 rounded-full bg-white/5 flex items-center justify-center group-hover:bg-teal-500 group-hover:text-onyx transition-all duration-300"
                >
                    <ChevronRight
                        class="w-5 h-5 text-muted group-hover:text-onyx-dark transition-colors"
                    />
                </div>
            </div>
        </div>
    </Link>
</template>

<style scoped>
@keyframes shimmer {
    100% {
        transform: translateX(300%) skewX(-12deg);
    }
}
.animate-shimmer {
    animation: shimmer 1.5s infinite;
}
</style>
