<template>
  <section class="rounded-2xl border border-brand-200/60 bg-white/80 backdrop-blur shadow-sm overflow-hidden">
    <header class="px-5 py-4 bg-brand-50 border-b border-brand-200/60">
      <h3 class="text-base font-semibold text-gray-900">{{ title }}</h3>
      <p v-if="subtitle" class="text-sm text-gray-600 mt-1">{{ subtitle }}</p>
    </header>

    <div class="p-5">
      <slot />
    </div>
  </section>
</template>

<script setup>
defineProps({
  title: { type: String, required: true },
  subtitle: { type: String, default: '' },
})
</script>
