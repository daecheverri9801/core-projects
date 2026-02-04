<template>
  <component
    :is="as"
    v-bind="asProps"
    class="inline-flex items-center justify-center gap-2 rounded-xl bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white
           shadow-sm hover:bg-brand-700 active:bg-brand-800 transition disabled:opacity-50 disabled:pointer-events-none"
  >
    <slot />
  </component>
</template>

<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'

const props = defineProps({
  href: { type: String, default: '' },
  type: { type: String, default: 'button' },
  disabled: { type: Boolean, default: false },
})

const as = computed(() => (props.href ? Link : 'button'))
const asProps = computed(() => {
  if (props.href) return { href: props.href }
  return { type: props.type, disabled: props.disabled }
})
</script>
