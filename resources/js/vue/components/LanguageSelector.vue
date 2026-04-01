<script setup>
import { ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { Globe } from 'lucide-vue-next'
import AppModal from '@/vue/components/ui/AppModal.vue'
import AppSelect from '@/vue/components/ui/AppSelect.vue'

const { t, locale } = useI18n()
const isOpen = ref(false)

const languageOptions = [
    { value: 'en', label: 'English' },
    { value: 'ru', label: 'Русский' },
    { value: 'ua', label: 'Українська' }
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
                    @update:modelValue="handleLanguageChange"
                />
                
                <p class="mt-6 text-sm text-slate-400 font-medium tracking-wide">
                    {{ t('nav.more_languages_soon') }}
                </p>
            </div>
        </AppModal>
    </div>
</template>
