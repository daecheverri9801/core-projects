<template>
  <AppCard padding="md">
    <div class="flex items-start justify-between gap-3">
      <div>
        <p class="text-sm font-semibold text-gray-900">Pestaña 7 · Parqueaderos</p>
        <p class="text-sm text-gray-600 mt-1">
          Al guardar aquí, se finaliza y se redirige al listado de proyectos.
        </p>
      </div>

      <button
        type="button"
        @click="submit"
        :disabled="disabled || form.processing || !canSubmit"
        class="rounded-xl bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700 transition disabled:opacity-60"
      >
        {{ form.processing ? 'Guardando…' : 'Guardar y finalizar' }}
      </button>
    </div>

    <div class="mt-6 flex items-center gap-2">
      <button
        type="button"
        class="rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm font-semibold text-gray-800 hover:bg-gray-50 transition"
        @click="addRow"
        :disabled="disabled"
      >
        + Agregar parqueadero
      </button>
    </div>

    <div class="mt-6 divide-y rounded-2xl border overflow-hidden">
      <div v-for="(p, idx) in form.parqueaderos" :key="p._key" class="p-4 md:p-6 bg-white">
        <div class="flex items-start justify-between">
          <p class="text-sm font-semibold text-gray-900">Parqueadero #{{ idx + 1 }}</p>
          <button
            type="button"
            class="rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-sm font-semibold text-red-700 hover:bg-red-100 transition"
            @click="removeRow(idx)"
            :disabled="disabled || form.parqueaderos.length === 1"
          >
            Quitar
          </button>
        </div>

        <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
          <FormField label="Número" required :error="fieldError(idx,'numero')">
            <TextInput v-model="p.numero" maxlength="20" :disabled="disabled" />
          </FormField>

          <FormField label="Tipo" required :error="fieldError(idx,'tipo')">
            <select v-model="p.tipo" class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm" :disabled="disabled">
              <option value="" disabled>Seleccione</option>
              <option value="Vehiculo">Vehiculo</option>
              <option value="Moto">Moto</option>
            </select>
          </FormField>

          <FormField label="Apartamento (opcional)" :error="fieldError(idx,'id_apartamento')">
            <select v-model="p.id_apartamento" class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm" :disabled="disabled">
              <option value="">Sin asignar</option>
              <option v-for="a in apartamentos" :key="a.id_apartamento" :value="a.id_apartamento">
                {{ a.numero }} — {{ a.torre }} ({{ a.proyecto }})
              </option>
            </select>
          </FormField>
        </div>
      </div>
    </div>
  </AppCard>
</template>

<script setup>
import { computed } from 'vue'
import { useForm } from '@inertiajs/vue3'

import AppCard from '@/Components/AppCard.vue'
import FormField from '@/Components/FormField.vue'
import TextInput from '@/Components/TextInput.vue'

const props = defineProps({
  proyectoId: [String, Number, null],
  apartamentos: { type: Array, default: () => [] },
  disabled: Boolean,
})

function newRow() {
  return {
    _key: crypto.randomUUID?.() ?? String(Date.now() + Math.random()),
    numero: '',
    tipo: '',
    id_apartamento: '',
  }
}

const form = useForm({
  parqueaderos: [newRow()],
})

function addRow() {
  form.parqueaderos.push(newRow())
}
function removeRow(idx) {
  if (form.parqueaderos.length <= 1) return
  form.parqueaderos.splice(idx, 1)
}

function fieldError(idx, field) {
  return form.errors?.[`parqueaderos.${idx}.${field}`] || null
}

const canSubmit = computed(() => {
  if (!props.proyectoId) return false
  return form.parqueaderos.every((p) => String(p.numero || '').trim() && String(p.tipo || '').trim())
})

function submit() {
  form.post('/proyectos/wizard/parqueaderos', {
    preserveScroll: false, // aquí sí redirige
  })
}
</script>
