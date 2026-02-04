<!-- resources/js/Components/TextArea.vue -->
<template>
  <textarea
    :rows="rows"
    :value="modelValue"
    :placeholder="placeholder"
    :disabled="disabled"
    @input="$emit('update:modelValue', $event.target.value)"
    class="block w-full rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm text-gray-900 placeholder:text-gray-400 shadow-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 disabled:bg-gray-100 disabled:cursor-not-allowed"
  />
</template>

<script setup>
defineProps({
  modelValue: { type: [String, Number, null], default: '' },
  rows: { type: [String, Number], default: 3 },
  placeholder: { type: String, default: '' },
  disabled: { type: Boolean, default: false },
})

defineEmits(['update:modelValue'])
</script>
