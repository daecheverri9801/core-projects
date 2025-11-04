<template>
  <div>
    <label :for="id" class="block text-sm font-medium text-gray-700 mb-1">
      {{ label }} <span v-if="required" class="text-red-500">*</span>
    </label>
    <textarea
      :id="id"
      v-bind="$attrs"
      :value="modelValue"
      @input="$emit('update:modelValue', $event.target.value)"
      :maxlength="maxlength"
      :class="['textarea', error ? 'border-red-500' : 'border-gray-300']"
      rows="4"
    ></textarea>
    <p v-if="error" class="text-red-600 text-sm mt-1">{{ error }}</p>
  </div>
</template>

<script setup>
const props = defineProps({
  label: String,
  modelValue: String,
  error: String,
  required: Boolean,
  maxlength: [String, Number],
  id: {
    type: String,
    default: () => `textarea-${Math.random().toString(36).substr(2, 9)}`,
  },
})
</script>

<style scoped>
.textarea {
  width: 100%;
  border: 1px solid;
  border-radius: 0.375rem;
  padding: 0.5rem 1rem;
  font-size: 1rem;
  outline: none;
  transition: border-color 0.2s;
  resize: vertical;
}
.textarea:focus {
  border-color: #3b82f6;
  box-shadow: 0 0 0 1px #3b82f6;
}
</style>