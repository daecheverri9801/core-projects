<template>
  <section
    class="rounded-2xl border border-brand-300/60 bg-white shadow-sm"
    :class="paddingClass"
  >
    <slot />
  </section>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  padding: { type: String, default: 'md' }, // none | sm | md | lg
})

const paddingClass = computed(() => {
  if (props.padding === 'none') return ''
  if (props.padding === 'sm') return 'p-4'
  if (props.padding === 'lg') return 'p-8'
  return 'p-5'
})
</script>
