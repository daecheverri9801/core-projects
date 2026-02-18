<template>
  <div class="rounded-xl border border-gray-200 bg-white p-3">
    <p class="text-xs font-medium text-gray-500">{{ label }}</p>

    <p class="mt-1 text-sm font-semibold text-gray-900 break-words">
      {{ displayValue }}
    </p>

    <p v-if="hint" class="mt-1 text-xs text-gray-500">
      {{ hint }}
    </p>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  label: { type: String, required: true },
  value: { type: [String, Number, Boolean, null], default: null },
  hint: { type: String, default: '' },
  format: { type: String, default: '' }, // '', 'currency', 'number'
  currency: { type: String, default: 'COP' },
})

function isEmpty(v) {
  return v === null || v === undefined || (typeof v === 'string' && v.trim() === '')
}

const displayValue = computed(() => {
  if (isEmpty(props.value)) return '—'

  // boolean
  if (typeof props.value === 'boolean') return props.value ? 'Sí' : 'No'

  // currency
  if (props.format === 'currency') {
    const n = Number(props.value)
    if (!Number.isFinite(n)) return String(props.value)
    return new Intl.NumberFormat('es-CO', {
      style: 'currency',
      currency: props.currency,
      maximumFractionDigits: 0,
    }).format(n)
  }

  // number
  if (props.format === 'number') {
    const n = Number(props.value)
    if (!Number.isFinite(n)) return String(props.value)
    return new Intl.NumberFormat('es-CO', {
      maximumFractionDigits: 2,
    }).format(n)
  }

  return String(props.value)
})
</script>
