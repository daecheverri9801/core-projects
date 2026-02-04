<!-- resources/js/Components/ButtonSecondary.vue -->
<template>
  <component
    :is="asComponent"
    v-bind="componentAttrs"
    class="inline-flex items-center justify-center gap-2 rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-800 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 transition disabled:opacity-50 disabled:pointer-events-none"
  >
    <slot />
  </component>
</template>

<script setup>
import { computed, useAttrs } from 'vue'
import { Link } from '@inertiajs/vue3'

const props = defineProps({
  href: { type: String, default: null }, // si viene, renderiza Link
  type: { type: String, default: 'button' }, // usado cuando es <button>
  disabled: { type: Boolean, default: false },
})

const attrs = useAttrs()

const asComponent = computed(() => (props.href ? Link : 'button'))

const componentAttrs = computed(() => {
  if (props.href) {
    return {
      href: props.href,
      ...attrs,
    }
  }

  return {
    type: props.type,
    disabled: props.disabled,
    ...attrs,
  }
})
</script>
