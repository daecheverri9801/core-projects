<template>
  <transition name="fade">
    <div
      v-if="open"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4"
      @mousedown.self="$emit('cancel')"
    >
      <div class="w-full max-w-lg rounded-2xl bg-white shadow-lg border border-gray-200">
        <div class="px-5 py-4 border-b">
          <h3 class="text-base font-semibold text-gray-900">{{ title }}</h3>
        </div>

        <div class="px-5 py-4">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="form-label">Desde</label>
              <input
                v-model.number="localDesde"
                type="number"
                class="form-input"
                inputmode="numeric"
              />
            </div>
            <div>
              <label class="form-label">Hasta</label>
              <input
                v-model.number="localHasta"
                type="number"
                class="form-input"
                inputmode="numeric"
              />
            </div>
          </div>

          <p v-if="error" class="mt-3 text-sm text-red-700">
            {{ error }}
          </p>

          <p class="mt-3 text-xs text-gray-500">
            Se agregan filas por cada nivel (incluye extremos).
          </p>
        </div>

        <div class="px-5 py-4 border-t flex items-center justify-end gap-3">
          <button
            type="button"
            class="rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-800 hover:bg-gray-50 transition"
            @click="$emit('cancel')"
          >
            Cancelar
          </button>
          <button
            type="button"
            class="rounded-xl bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700 transition"
            @click="onConfirm"
          >
            Agregar
          </button>
        </div>
      </div>
    </div>
  </transition>
</template>

<script setup>
import { ref, watch } from 'vue'

const props = defineProps({
  open: { type: Boolean, default: false },
  title: { type: String, default: 'Agregar rango' },
  desde: { type: [Number, String], default: 1 },
  hasta: { type: [Number, String], default: 1 },
})

const emit = defineEmits(['cancel', 'confirm'])

const localDesde = ref(props.desde)
const localHasta = ref(props.hasta)
const error = ref('')

watch(
  () => props.open,
  (v) => {
    if (v) {
      localDesde.value = props.desde
      localHasta.value = props.hasta
      error.value = ''
    }
  }
)

function asInt(v) {
  const n = Number(v)
  if (!Number.isFinite(n)) return null
  return Math.trunc(n)
}

function onConfirm() {
  error.value = ''
  const d = asInt(localDesde.value)
  const h = asInt(localHasta.value)

  if (!d || !h || d < 1 || h < 1) {
    error.value = 'El rango debe ser numérico y mínimo 1.'
    return
  }
  if (h < d) {
    error.value = '“Hasta” debe ser mayor o igual a “Desde”.'
    return
  }

  emit('confirm', { desde: d, hasta: h })
}
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
.form-label {
  @apply block text-sm font-medium text-gray-700 mb-1;
}
.form-input {
  @apply w-full rounded-xl border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500;
}
</style>
