<script setup>
import { ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { Globe } from 'lucide-vue-next'
import AppModal from '@/vue/components/ui/AppModal.vue'
import AppSelect from '@/vue/components/ui/AppSelect.vue'
import { set } from '@vueuse/core'

const { t, locale } = useI18n()
const isOpen = ref(false)

const languageOptions = [
    { value: 'en', label: 'English' },
    { value: 'ru', label: 'Русский' },
    { value: 'ua', label: 'Українська' },
    // Temporarily added 15 options to test scrolling as requested
    { value: 'es', label: 'Español (Test 1)' },
    { value: 'fr', label: 'Français (Test 2)' },
    { value: 'de', label: 'Deutsch (Test 3)' },
    { value: 'it', label: 'Italiano (Test 4)' },
    { value: 'pt', label: 'Português (Test 5)' },
    { value: 'pl', label: 'Polski (Test 6)' },
    { value: 'ja', label: '日本語 (Test 7)' },
    { value: 'ko', label: '한국어 (Test 8)' },
    { value: 'zh', label: '中文 (Test 9)' },
    { value: 'ar', label: 'العربية (Test 10)' },
    { value: 'tr', label: 'Türkçe (Test 11)' },
    { value: 'hi', label: 'हिन्दी (Test 12)' },
    { value: 'nl', label: 'Nederlands (Test 13)' },
    { value: 'sv', label: 'Svenska (Test 14)' },
    { value: 'fi', label: 'Suomi (Test 15)' },
]

function openModal() {
    isOpen.value = true
}

function closeModal() {
    isOpen.value = false
}

function handleLanguageChange(newLang) {
    locale.value = newLang
    localStorage.setItem('gw_locale', newLang)
    setTimeout(() => {
        closeModal()
    }, 150)
}
</script>

<template>
    <div>
        <button
            @click="openModal"
            class="p-2 text-slate-300 hover:text-white hover:bg-white/10 rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-emerald-500/50"
            :title="t('nav.change_locale')"
        >
            <Globe class="w-6 h-6" />
        </button>

        <AppModal :isOpen="isOpen" :title="t('nav.choose_language')" @close="closeModal">
            <div class="py-4 text-center">
                <AppSelect
                    v-model="locale"
                    :options="languageOptions"
                    size="md"
                    @update:modelValue="handleLanguageChange"
                />
                
                <p class="mt-6 text-sm text-slate-400 font-medium tracking-wide">
                    {{ t('nav.more_languages_soon') }}
                </p>
            </div>
        </AppModal>
    </div>
</template>
