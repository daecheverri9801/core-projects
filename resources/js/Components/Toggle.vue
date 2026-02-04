<!-- resources/js/Components/Toggle.vue -->
<template>
  <div class="rounded-2xl border border-gray-200 bg-gray-50 p-4">
    <label class="flex items-start justify-between gap-4 cursor-pointer select-none">
      <div class="min-w-0">
        <p class="text-sm font-semibold text-gray-900">{{ label }}</p>
        <p v-if="description" class="mt-0.5 text-sm text-gray-600">
          {{ description }}
        </p>
      </div>

      <button
        type="button"
        @click="toggle"
        class="relative inline-flex h-6 w-11 items-center rounded-full transition"
        :class="modelValue ? 'bg-brand-600' : 'bg-gray-300'"
        aria-label="toggle"
      >
        <span
          class="inline-block h-5 w-5 transform rounded-full bg-white transition"
          :class="modelValue ? 'translate-x-5' : 'translate-x-1'"
        />
      </button>
    </label>
  </div>
</template>

<script setup>
const props = defineProps({
  modelValue: { type: Boolean, default: false },
  label: { type: String, required: true },
  description: { type: String, default: '' },
})

const emit = defineEmits(['update:modelValue'])

function toggle() {
  emit('update:modelValue', !props.modelValue)
}
</script>
