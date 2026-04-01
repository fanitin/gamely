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
})

const emit = defineEmits(['update:modelValue'])

const selectedOption = computed(() => {
    return props.options.find(opt => opt.value === props.modelValue)
})
</script>

<template>
    <Listbox :modelValue="modelValue" @update:modelValue="emit('update:modelValue', $event)">
        <div class="relative mt-1">
            <ListboxLabel v-if="label" class="block text-sm font-medium text-slate-300 mb-1">
                {{ label }}
            </ListboxLabel>
            
            <ListboxButton class="relative w-full cursor-default rounded-xl bg-slate-800/80 border border-white/10 py-4 pl-5 pr-12 text-left text-white shadow-lg focus:outline-none focus-visible:border-emerald-500 focus-visible:ring-2 focus-visible:ring-emerald-500/50 text-base sm:text-lg transition-colors hover:bg-slate-800 backdrop-blur-sm">
                <span class="block truncate font-medium tracking-wide">
                    {{ selectedOption ? selectedOption.label : placeholder }}
                </span>
                <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4">
                    <ChevronDown class="h-6 w-6 text-slate-400" aria-hidden="true" />
                </span>
            </ListboxButton>

            <transition
                leave-active-class="transition duration-100 ease-in"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <ListboxOptions class="absolute mt-2 max-h-80 w-full overflow-auto rounded-xl bg-slate-800 border border-white/10 py-2 text-base shadow-2xl ring-1 ring-black/5 focus:outline-none sm:text-lg z-50 backdrop-blur-md">
                    <ListboxOption
                        v-slot="{ active, selected }"
                        v-for="option in options"
                        :key="option.value"
                        :value="option.value"
                        as="template"
                    >
                        <li
                            :class="[
                                active ? 'bg-emerald-500/15 text-emerald-400' : 'text-slate-200 hover:bg-slate-700/50',
                                'relative cursor-default select-none py-4 pl-12 pr-4 transition-colors',
                            ]"
                        >
                            <span :class="[selected ? 'font-semibold text-emerald-400' : 'font-medium', 'block truncate']">
                                {{ option.label }}
                            </span>
                            <span
                                v-if="selected"
                                class="absolute inset-y-0 left-0 flex items-center pl-4 text-emerald-500"
                            >
                                <Check class="h-6 w-6" aria-hidden="true" />
                            </span>
                        </li>
                    </ListboxOption>
                </ListboxOptions>
            </transition>
        </div>
    </Listbox>
</template>
