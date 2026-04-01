<script setup>
import { Head } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import AppLayout from '@/vue/layouts/AppLayout.vue'

const { t } = useI18n()
</script>

<template>
    <AppLayout>
        <Head :title="t('nav.home')" />

        <div class="max-w-5xl mx-auto px-4 py-16 text-center">
            <h1 class="text-4xl font-bold mb-4">GameWordle</h1>
            <p class="text-white/50">{{ t('modes.attributes') }} / {{ t('modes.screenshot') }}</p>
        </div>
    </AppLayout>
</template>

