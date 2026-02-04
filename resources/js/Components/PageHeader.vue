<template>
  <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
    <div class="min-w-0">
      <p v-if="kicker" class="text-sm text-gray-600">{{ kicker }}</p>
      <h2 class="text-2xl sm:text-3xl font-bold text-gray-900">
        <slot>{{ title }}</slot>
      </h2>
      <p v-if="subtitle" class="mt-1 text-sm text-gray-600 max-w-3xl">
        {{ subtitle }}
      </p>
    </div>

    <div class="flex items-center gap-2 sm:justify-end">
      <slot name="actions" />
    </div>
  </div>
</template>

<script setup>
defineProps({
  title: { type: String, default: '' },
  kicker: { type: String, default: '' },
  subtitle: { type: String, default: '' },
})
</script>
