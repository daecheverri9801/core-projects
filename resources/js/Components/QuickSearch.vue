<template>
  <div class="relative">
    <MagnifyingGlassIcon
      class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-500"
    />

    <input
      :value="modelValue"
      @input="$emit('update:modelValue', $event.target.value)"
      :placeholder="placeholder"
      type="text"
      class="w-full rounded-xl border border-brand-300 bg-white px-10 py-2.5 text-sm
             text-gray-900 placeholder:text-gray-500 shadow-sm
             focus:outline-none focus:ring-2 focus:ring-brand-400 focus:border-brand-400"
    />

    <button
      v-if="modelValue"
      @click="$emit('update:modelValue', '')"
      class="absolute right-2 top-1/2 -translate-y-1/2 rounded-lg p-2
             text-gray-600 hover:bg-gray-100 transition"
    >
      <XMarkIcon class="w-5 h-5" />
    </button>
  </div>
</template>

<script setup>
import { MagnifyingGlassIcon, XMarkIcon } from '@heroicons/vue/24/outline'

defineProps({
  modelValue: String,
  placeholder: String,
})

defineEmits(['update:modelValue'])
</script>
