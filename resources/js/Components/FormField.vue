<!-- resources/js/Components/FormField.vue -->
<template>
  <div>
    <div class="flex items-center gap-2">
      <label v-if="label" class="text-sm font-semibold text-gray-800">
        {{ label }}
        <span v-if="required" class="text-red-500">*</span>
      </label>

      <span v-if="hint" class="text-xs text-gray-500">{{ hint }}</span>
    </div>

    <div class="mt-1.5">
      <slot />
    </div>

    <p v-if="error" class="mt-1.5 text-sm text-red-600">
      {{ error }}
    </p>

    <p v-else-if="help" class="mt-1.5 text-xs text-gray-500">
      {{ help }}
    </p>
  </div>
</template>

<script setup>
defineProps({
  label: { type: String, default: '' },
  required: { type: Boolean, default: false },
  hint: { type: String, default: '' },
  help: { type: String, default: '' },
  error: { type: String, default: '' },
})
</script>
