<template>
  <div class="mb-8">
    <div class="flex items-center gap-3 mb-2">
      <div
        class="w-12 h-12 rounded-xl bg-gradient-to-br from-[#FFEA00] to-[#D1C000] flex items-center justify-center shadow-md"
      >
        <component :is="icon" class="w-7 h-7 text-[#474100]" />
      </div>
      <div>
        <h1 class="text-3xl font-bold text-gray-900">{{ title }}</h1>
        <p v-if="subtitle" class="text-sm text-gray-600 mt-1">{{ subtitle }}</p>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  title: { type: String, required: true },
  subtitle: { type: String, default: '' },
  icon: { type: Object, required: true },
})
</script>
