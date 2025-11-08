<template>
  <div class="info-item">
    <dt class="info-label">{{ label }}</dt>
    <dd class="info-value">
      <slot>{{ value }}</slot>
    </dd>
  </div>
</template>

<script setup>
defineProps({
  label: { type: String, required: true },
  value: { type: [String, Number], default: '' },
})
</script>

<style scoped>
.info-item {
  @apply flex flex-col;
}
.info-label {
  @apply text-sm font-medium text-gray-500 mb-1;
}
.info-value {
  @apply text-base text-gray-900;
}
</style>
