<!-- resources/js/Components/SummaryTile.vue -->
<template>
  <div class="rounded-2xl border border-gray-200 bg-white p-4">
    <p class="text-xs font-semibold text-gray-600 uppercase tracking-wider">{{ label }}</p>
    <div class="mt-2">
      <slot />
    </div>
  </div>
</template>

<script setup>
defineProps({
  label: { type: String, required: true },
})
</script>
