<template>
  <component
    :is="as"
    v-bind="asProps"
    class="inline-flex items-center justify-center rounded-2xl border p-2 transition"
    :class="variantClass"
    :title="title"
  >
    <component :is="iconComp" class="w-5 h-5" :class="iconClass" />
    <span class="sr-only">{{ title }}</span>
  </component>
</template>

<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import * as HeroIcons from '@heroicons/vue/24/outline'

const props = defineProps({
  href: { type: String, default: '' },
  icon: { type: String, required: true },
  title: { type: String, required: true },
  variant: { type: String, default: 'brand' }, // brand | info | warn | danger
  disabled: { type: Boolean, default: false },
})

const as = computed(() => (props.href ? Link : 'button'))
const asProps = computed(() => {
  if (props.href) return { href: props.href }
  return { type: 'button', disabled: props.disabled }
})

const iconComp = computed(() => HeroIcons[props.icon] || HeroIcons.Squares2X2Icon)

const variantClass = computed(() => {
  if (props.variant === 'danger')
    return 'border-red-200 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-red-200'
  if (props.variant === 'warn')
    return 'border-amber-200 bg-white hover:bg-amber-50 focus:outline-none focus:ring-2 focus:ring-amber-200'
  if (props.variant === 'info')
    return 'border-blue-200 bg-white hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-blue-200'
  return 'border-brand-300/60 bg-white hover:bg-brand-50 focus:outline-none focus:ring-2 focus:ring-brand-300'
})

const iconClass = computed(() => {
  if (props.variant === 'danger') return 'text-red-700'
  if (props.variant === 'warn') return 'text-amber-700'
  if (props.variant === 'info') return 'text-blue-700'
  return 'text-brand-900'
})
</script>
