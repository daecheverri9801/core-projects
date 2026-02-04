<!-- resources/js/Components/InlineStatus.vue -->
<template>
  <div class="flex items-center justify-between gap-3">
    <p class="text-gray-700">{{ label }}</p>
    <span
      class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-semibold"
      :class="ok ? 'border-green-200 bg-green-50 text-green-800' : 'border-amber-200 bg-amber-50 text-amber-800'"
    >
      {{ ok ? 'OK' : 'Pendiente' }}
    </span>
  </div>
</template>

<script setup>
defineProps({
  label: { type: String, required: true },
  ok: { type: Boolean, default: false },
})
</script>
