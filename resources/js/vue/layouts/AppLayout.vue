<script setup>
import { Link } from '@inertiajs/vue3'
import LanguageSelector from '@/vue/components/LanguageSelector.vue'
import { computed } from 'vue';

const currentLanguage = computed(() => {
    return (localStorage.getItem('gw_locale') || 'en').toUpperCase()
})
</script>

<template>
    <div class="min-h-screen flex flex-col bg-slate-950 text-white relative">

        <div class="absolute inset-0 z-0 bg-cover bg-center bg-no-repeat opacity-20 pointer-events-none" style="background-image: url('');"></div>
        <div class="absolute inset-0 z-0 bg-gradient-to-b from-slate-950/80 via-slate-950/95 to-slate-950 pointer-events-none"></div>

        <header class="border-b border-white/10 relative z-10 bg-slate-950/50 backdrop-blur-md">
            <div class="max-w-5xl mx-auto px-4 h-14 flex items-center justify-between">
                <Link href="/" class="text-xl font-bold tracking-tight bg-gradient-to-r from-emerald-400 to-emerald-600 bg-clip-text text-transparent">
                    Gamely
                </Link>

                <div class="flex items-center gap-4">
                    <LanguageSelector />
                    <span>
                        {{ currentLanguage }}
                    </span>
                </div>
            </div>
        </header>

        <main class="flex-1 relative z-10">
            <slot />
        </main>

        <footer class="border-t border-white/10 py-4 text-center text-sm text-white/30 relative z-10">
            &copy; {{ new Date().getFullYear() }} Gamely
        </footer>
    </div>
</template>
