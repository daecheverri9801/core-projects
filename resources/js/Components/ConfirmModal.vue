<template>
  <transition name="fade">
    <div
      v-if="open"
      class="fixed inset-0 z-50 flex items-center justify-center"
      aria-modal="true"
      role="dialog"
    >
      <div class="absolute inset-0 bg-black/40" @click="$emit('close')" />

      <div class="relative w-full max-w-lg mx-4 rounded-2xl border border-brand-200/60 bg-white shadow-xl overflow-hidden">
        <div class="px-5 py-4 bg-brand-50 border-b border-brand-200/60">
          <h3 class="text-lg font-semibold text-gray-900">{{ title }}</h3>
          <p v-if="subtitle" class="text-sm text-gray-600 mt-1">{{ subtitle }}</p>
        </div>

        <div class="p-5">
          <slot />
        </div>

        <div class="px-5 py-4 border-t border-brand-200/60 bg-white flex justify-end gap-2">
          <button
            type="button"
            class="rounded-xl border border-brand-200/80 bg-white px-4 py-2 text-sm font-semibold text-gray-800 hover:bg-brand-50 transition"
            @click="$emit('close')"
          >
            Cancelar
          </button>

          <button
            type="button"
            class="rounded-xl bg-brand-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-brand-700 transition disabled:opacity-50"
            :disabled="loading"
            @click="$emit('confirm')"
          >
            <span v-if="!loading">{{ confirmText }}</span>
            <span v-else>Guardando…</span>
          </button>
        </div>
      </div>
    </div>
  </transition>
</template>

<script setup>
defineProps({
  open: { type: Boolean, default: false },
  title: { type: String, default: 'Confirmar' },
  subtitle: { type: String, default: '' },
  confirmText: { type: String, default: 'Confirmar' },
  loading: { type: Boolean, default: false },
})
defineEmits(['close', 'confirm'])
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
