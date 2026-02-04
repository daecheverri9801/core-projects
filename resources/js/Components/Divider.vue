<!-- resources/js/Components/Divider.vue -->
<template>
  <div class="relative my-4 w-full">
    <div class="absolute inset-0 flex items-center">
      <div class="w-full border-t border-gray-200"></div>
    </div>
    <div v-if="label" class="relative flex justify-center">
      <span
        class="bg-white px-3 text-xs font-medium uppercase tracking-wide text-gray-500"
      >
        {{ label }}
      </span>
    </div>
  </div>
</template>

<script setup>
defineProps({
  label: { type: String, default: '' },
})
</script>
