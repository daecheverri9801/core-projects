<!-- resources/js/Components/KpiLine.vue -->
<template>
  <div>
    <p class="text-xs text-gray-500">{{ label }}</p>
    <p class="mt-1 font-semibold text-gray-900">{{ value ?? '—' }}</p>
  </div>
</template>

<script setup>
defineProps({
  label: { type: String, required: true },
  value: { type: [String, Number, null], default: '—' },
})
</script>
