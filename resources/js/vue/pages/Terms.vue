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
    meta: { version: string; effective_date: string; last_updated: string };
}>();

const { t, tm, locale } = useI18n();

const ns = "legal.terms";
const title = computed(() => t(`${ns}.title`));
const intro = computed(() => t(`${ns}.intro`));
const sections = computed<LegalSection[]>(
    () => tm(`${ns}.sections`) as unknown as LegalSection[],
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

        <article
            class="max-w-3xl mx-auto px-4 py-8 sm:py-14 text-muted leading-relaxed"
        >
            <header class="mb-8 sm:mb-10">
                <Link
                    href="/"
                    class="text-xs font-semibold uppercase tracking-wider text-teal-400 hover:text-teal-300 transition-colors"
                >
                    &larr; {{ t("nav.back_to_menu") }}
                </Link>

                <h1
                    class="mt-4 text-3xl sm:text-4xl font-black tracking-tight text-white"
                >
                    {{ title }}
                </h1>

                <div
                    class="mt-4 flex flex-wrap gap-x-5 gap-y-1 text-[13px] text-muted/80"
                >
                    <span>
                        {{ t("legal.meta.version") }}
                        <span class="text-white/80">{{ meta.version }}</span>
                    </span>
                    <span>
                        {{ t("legal.meta.effective_date") }}
                        <span class="text-white/80">{{
                            fmt(meta.effective_date)
                        }}</span>
                    </span>
                    <span>
                        {{ t("legal.meta.last_updated") }}
                        <span class="text-white/80">{{
                            fmt(meta.last_updated)
                        }}</span>
                    </span>
                </div>

                <p v-if="intro" class="mt-6 text-base text-muted">
                    {{ intro }}
                </p>
            </header>

            <section
                v-for="(section, i) in sections"
                :key="i"
                class="mb-7 sm:mb-9"
            >
                <h2
                    class="text-lg sm:text-xl font-bold text-white tracking-tight mb-3"
                >
                    {{ i + 1 }}. {{ section.heading }}
                </h2>

                <p
                    v-for="(p, pi) in section.paragraphs"
                    :key="pi"
                    class="mb-3 text-[15px]"
                >
                    {{ p }}
                </p>

                <ul
                    v-if="section.list"
                    class="list-disc pl-5 space-y-1.5 text-[15px] marker:text-teal-500"
                >
                    <li v-for="(item, li) in section.list" :key="li">
                        {{ item }}
                    </li>
                </ul>
            </section>
        </article>
    </AppLayout>
</template>