<script setup>
import { computed } from 'vue'
import {
    Listbox,
    ListboxLabel,
    ListboxButton,
    ListboxOptions,
    ListboxOption,
} from '@headlessui/vue'
import { Check, ChevronDown } from 'lucide-vue-next'

const props = defineProps({
    modelValue: {
        type: [String, Number, Object],
        required: true,
    },
    options: {
        type: Array,
        required: true,
    },
    label: {
        type: String,
        default: '',
    },
    placeholder: {
        type: String,
        default: 'Select an option',
    },
    size: {
        type: String,
        default: 'md',
        validator: (value) => ['sm', 'md', 'lg'].includes(value)
    }
})

const emit = defineEmits(['update:modelValue'])

const selectedOption = computed(() => {
    return props.options.find(opt => opt.value === props.modelValue)
})

const sizeMap = computed(() => {
    switch (props.size) {
        case 'sm': 
            return { button: 'py-2 pl-3 pr-8 text-sm', option: 'py-2 pl-8 pr-3 text-sm', icon: 'w-4 h-4', checkPl: 'pl-2' };
        case 'lg': 
            return { button: 'py-4 pl-5 pr-12 text-lg', option: 'py-4 pl-12 pr-4 text-lg', icon: 'w-6 h-6', checkPl: 'pl-4' };
        case 'md':
        default: 
            return { button: 'py-3 pl-4 pr-10 text-base', option: 'py-3 pl-10 pr-4 text-base', icon: 'w-5 h-5', checkPl: 'pl-3' };
    }
})
</script>

<template>
    <Listbox :modelValue="modelValue" @update:modelValue="emit('update:modelValue', $event)">
        <div class="relative mt-1">
            <ListboxLabel v-if="label" class="block text-sm font-medium text-slate-300 mb-1">
                {{ label }}
            </ListboxLabel>
            
            <ListboxButton 
                class="relative w-full cursor-pointer rounded-xl bg-slate-800/80 border border-white/10 text-left text-white shadow-lg focus:outline-none focus-visible:border-emerald-500 focus-visible:ring-2 focus-visible:ring-emerald-500/50 transition-colors hover:bg-slate-800 backdrop-blur-sm"
                :class="sizeMap.button"
            >
                <span class="block truncate font-medium tracking-wide">
                    {{ selectedOption ? selectedOption.label : placeholder }}
                </span>
                <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                    <ChevronDown :class="['text-slate-400', sizeMap.icon]" aria-hidden="true" />
                </span>
            </ListboxButton>

                <ListboxOptions class="absolute mt-2 max-h-80 w-full overflow-auto custom-scrollbar rounded-xl bg-slate-800 border border-white/10 py-2 shadow-2xl ring-1 ring-black/5 focus:outline-none z-50 backdrop-blur-md">
                    <ListboxOption
                        v-slot="{ selected }"
                        v-for="option in options"
                        :key="option.value"
                        :value="option.value"
                        as="template"
                    >
                        <li
                            class="relative cursor-pointer select-none py-3 hover:bg-emerald-500/15 text-slate-200 hover:text-emerald-400"
                            :class="[sizeMap.option]"
                        >
                            <span :class="[selected ? 'font-semibold text-emerald-400' : 'font-medium', 'block truncate']">
                                {{ option.label }}
                            </span>
                            <span
                                v-if="selected"
                                class="absolute inset-y-0 left-0 flex items-center text-emerald-500"
                                :class="[sizeMap.checkPl]"
                            >
                                <Check :class="sizeMap.icon" aria-hidden="true" />
                            </span>
                        </li>
                    </ListboxOption>
                </ListboxOptions>
        </div>
    </Listbox>
</template>
