<script setup lang="ts">
import { Head, Link } from "@inertiajs/vue3";
import { computed } from "vue";
import { useI18n } from "vue-i18n";
import AppLayout from "@/vue/layouts/AppLayout.vue";

interface LegalSection {
    heading: string;
    paragraphs?: string[];
    list?: string[];
}

const props = defineProps<{
    ns: string;
    meta: { version: string; effective_date: string; last_updated: string };
}>();

const { t, tm, locale } = useI18n();

const title = computed(() => t(`${props.ns}.title`));
const intro = computed(() => t(`${props.ns}.intro`));
const sections = computed<LegalSection[]>(
    () => tm(`${props.ns}.sections`) as unknown as LegalSection[],
);

function fmt(date: string): string {
    const d = new Date(date);
    if (isNaN(d.getTime())) return date;
    return new Intl.DateTimeFormat(locale.value, {
        year: "numeric",
        month: "long",
        day: "numeric",
    }).format(d);
}
</script>

<template>
    <AppLayout>
        <Head :title="title" />

        <article class="max-w-2xl mx-auto px-4 py-8 sm:py-12 text-muted">
            <Link
                href="/"
                class="text-xs font-semibold uppercase tracking-wider text-teal-400 hover:text-teal-300 transition-colors"
            >
                &larr; {{ t("nav.back_to_menu") }}
            </Link>

            <h1 class="mt-5 text-2xl sm:text-3xl font-bold text-white">
                {{ title }}
            </h1>

            <p class="mt-2 text-[13px] text-muted/70 tabular-nums">
                {{ t("legal.meta.last_updated") }} {{ fmt(meta.last_updated) }}
                &middot; {{ t("legal.meta.version") }} {{ meta.version }}
            </p>

            <p v-if="intro" class="mt-6 text-[15px] leading-relaxed">
                {{ intro }}
            </p>

            <section v-for="(section, i) in sections" :key="i" class="mt-7">
                <h2 class="text-base font-semibold text-white">
                    {{ i + 1 }}. {{ section.heading }}
                </h2>

                <p
                    v-for="(p, pi) in section.paragraphs"
                    :key="pi"
                    class="mt-2 text-[15px] leading-relaxed"
                >
                    {{ p }}
                </p>

                <ul
                    v-if="section.list"
                    class="mt-2 list-disc pl-5 space-y-1 text-[15px] leading-relaxed marker:text-muted/50"
                >
                    <li v-for="(item, li) in section.list" :key="li">
                        {{ item }}
                    </li>
                </ul>

                <slot :name="`after-${i}`" />
            </section>
        </article>
    </AppLayout>
</template>