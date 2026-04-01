<script setup>
import { Link } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'

const { t, locale } = useI18n()

const locales = ['en', 'ru', 'ua']

function switchLocale(lang) {
    locale.value = lang
    localStorage.setItem('gw_locale', lang)
}
</script>

<template>
    <div class="min-h-screen flex flex-col bg-gray-950 text-white">
        <header class="border-b border-white/10">
            <div class="max-w-5xl mx-auto px-4 h-14 flex items-center justify-between">
                <Link href="/" class="text-xl font-bold tracking-tight">
                    GameWordle
                </Link>

                <div class="flex items-center gap-4">
                    <div class="flex gap-1">
                        <button
                            v-for="lang in locales"
                            :key="lang"
                            :class="locale === lang ? 'text-white font-semibold' : 'text-white/40 hover:text-white/70'"
                            class="text-sm uppercase transition-colors px-1"
                            @click="switchLocale(lang)"
                        >
                            {{ lang }}
                        </button>
                    </div>
                </div>
            </div>
        </header>

        <main class="flex-1">
            <slot />
        </main>

        <footer class="border-t border-white/10 py-4 text-center text-sm text-white/30">
            &copy; {{ new Date().getFullYear() }} GameWordle
        </footer>
    </div>
</template>
