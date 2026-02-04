<template>
  <transition name="fade">
    <div
      v-if="open"
      class="fixed inset-0 z-50 flex items-center justify-center"
      aria-modal="true"
      role="dialog"
    >
      <div class="absolute inset-0 bg-black/40" @click="$emit('cancel')" />

      <div class="relative w-full max-w-md mx-4 rounded-2xl bg-white shadow-lg border border-brand-200/60">
        <div class="p-5 border-b border-brand-200/60 bg-brand-50 rounded-t-2xl">
          <h3 class="text-base font-semibold text-gray-900">
            {{ title }}
          </h3>
          <p v-if="message" class="mt-1 text-sm text-gray-600">{{ message }}</p>
        </div>

        <div class="p-5">
          <slot />
          <div class="mt-6 flex justify-end gap-2">
            <button
              type="button"
              @click="$emit('cancel')"
              class="rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-800 hover:bg-gray-50 transition"
            >
              {{ cancelText }}
            </button>

            <button
              type="button"
              @click="$emit('confirm')"
              class="rounded-xl bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700 transition"
            >
              {{ confirmText }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </transition>
</template>

<script setup>
defineProps({
  open: { type: Boolean, default: false },
  title: { type: String, default: 'Confirmar' },
  message: { type: String, default: '' },
  cancelText: { type: String, default: 'Cancelar' },
  confirmText: { type: String, default: 'Confirmar' },
})

defineEmits(['cancel', 'confirm'])
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.15s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
