<!-- resources/js/Components/ModalShell.vue -->
<template>
  <transition name="fade">
    <div v-if="open" class="fixed inset-0 z-50">
      <div class="absolute inset-0 bg-black/50" @click="$emit('close')" />

      <div class="relative mx-auto flex min-h-full items-center justify-center p-4">
        <div class="w-full max-w-2xl rounded-2xl bg-white shadow-xl border border-gray-200">
          <div class="flex items-start justify-between gap-4 border-b border-gray-200 px-5 py-4">
            <div class="min-w-0">
              <p class="text-base font-semibold text-gray-900">{{ title }}</p>
              <p v-if="subtitle" class="mt-0.5 text-sm text-gray-600">{{ subtitle }}</p>
            </div>

            <button
              type="button"
              @click="$emit('close')"
              class="rounded-xl border border-gray-300 bg-white px-3 py-1.5 text-sm font-semibold text-gray-800 hover:bg-gray-50 transition"
            >
              Cerrar
            </button>
          </div>

          <div class="px-5 py-5 max-h-[75vh] overflow-auto">
            <slot />
          </div>
        </div>
      </div>
    </div>
  </transition>
</template>

<script setup>
defineProps({
  open: { type: Boolean, default: false },
  title: { type: String, default: '' },
  subtitle: { type: String, default: '' },
})

defineEmits(['close'])
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
