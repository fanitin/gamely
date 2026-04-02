<script setup>
import { useI18n } from "vue-i18n";
const { t } = useI18n();
</script>

<template>
    <footer
        class="mt-auto border-t border-white/5 bg-slate-950/50 relative z-10 select-none"
    >
        <div class="max-w-5xl mx-auto px-4 py-4">
            <div
                class="flex flex-col sm:flex-row justify-between items-center gap-4 text-sm text-slate-500"
            >
                <div class="font-medium">
                    &copy; {{ new Date().getFullYear() }}
                    <span class="text-slate-300">Gamely</span>
                    <span class="mx-2 opacity-20">|</span>
                    by <span class="text-slate-400">Faniko</span>
                </div>

                <nav
                    class="flex gap-6 font-semibold uppercase tracking-wider text-[11px]"
                >
                    <a href="#" class="hover:text-white transition-colors"
                        >Privacy</a
                    >
                    <a href="#" class="hover:text-white transition-colors"
                        >Contact</a
                    >
                </nav>
            </div>

            <p
                class="mt-2 text-[13px] text-slate-500 text-center sm:text-left italic opacity-80"
            >
                Not affiliated with Sony, Microsoft or Nintendo. Data by
                <a href="https://igdb.com" class="hover:text-white underline">
                    IGDB </a
                >.
            </p>
        </div>
    </footer>
</template>
