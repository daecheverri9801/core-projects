<template>
  <div
    class="bg-gradient-to-br rounded-xl p-5 shadow-sm border border-gray-100 hover:shadow-md transition-all duration-200"
    :class="gradientClass"
  >
    <div class="flex items-center justify-between">
      <div>
        <p class="text-sm font-medium text-gray-700 mb-1">{{ label }}</p>
        <p class="text-2xl font-bold text-gray-900">{{ value }}</p>
      </div>
      <div class="w-12 h-12 rounded-lg flex items-center justify-center" :class="iconBgClass">
        <component :is="icon" class="w-6 h-6" :class="iconColorClass" />
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

// ✅ Definir props UNA sola vez
const props = defineProps({
  label: { type: String, required: true },
  value: { type: [String, Number], required: true },
  icon: { type: Object, required: true },
  variant: { type: String, default: 'yellow' },
})

// ✅ Computed properties usando props
const gradientClass = computed(() => {
  const variants = {
    yellow: 'from-[#FFFDE6] to-[#FFF9B8]',
    blue: 'from-blue-50 to-blue-100',
    green: 'from-green-50 to-green-100',
    purple: 'from-purple-50 to-purple-100',
  }
  return variants[props.variant] || variants.yellow
})

const iconBgClass = computed(() => {
  const variants = {
    yellow: 'bg-[#FFEA00]',
    blue: 'bg-blue-500',
    green: 'bg-green-500',
    purple: 'bg-purple-500',
  }
  return variants[props.variant] || variants.yellow
})

const iconColorClass = computed(() => {
  const variants = {
    yellow: 'text-[#474100]',
    blue: 'text-white',
    green: 'text-white',
    purple: 'text-white',
  }
  return variants[props.variant] || variants.yellow
})
</script>
