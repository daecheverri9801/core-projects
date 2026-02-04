<!-- resources/js/Components/MetricTile.vue -->
<template>
  <div class="rounded-2xl border border-gray-200 bg-white p-4">
    <p class="text-xs font-semibold text-gray-600 uppercase tracking-wider">{{ label }}</p>
    <p class="mt-2 text-lg font-semibold text-gray-900">
      {{ displayValue }}
      <span v-if="suffix" class="text-sm font-semibold text-gray-500"> {{ suffix }}</span>
    </p>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  label: { type: String, required: true },
  value: { type: [String, Number, null], default: '—' },
  suffix: { type: String, default: '' },
})

const displayValue = computed(() => {
  if (props.value === null || props.value === undefined || props.value === '') return '—'
  return String(props.value)
})
</script>
