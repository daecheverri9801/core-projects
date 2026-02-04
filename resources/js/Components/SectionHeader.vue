<!-- resources/js/Components/SectionHeader.vue -->
<template>
  <div class="flex items-start justify-between gap-4">
    <div class="flex items-start gap-3">
      <span v-if="Icon" class="mt-0.5 rounded-2xl border border-brand-300/60 bg-brand-200 p-2">
        <component :is="Icon" class="w-5 h-5 text-brand-900" />
      </span>
      <div class="min-w-0">
        <p class="text-base font-semibold text-gray-900">{{ title }}</p>
        <p v-if="subtitle" class="mt-0.5 text-sm text-gray-600">{{ subtitle }}</p>
      </div>
    </div>

    <slot name="right" />
  </div>
</template>

<script setup>
import { computed } from 'vue'
import * as Icons from '@heroicons/vue/24/outline'

const props = defineProps({
  title: { type: String, required: true },
  subtitle: { type: String, default: '' },
  icon: { type: String, default: '' }, // nombre del icono en heroicons (ej: "FolderIcon")
})

const Icon = computed(() => {
  if (!props.icon) return null
  return Icons[props.icon] || null
})
</script>
