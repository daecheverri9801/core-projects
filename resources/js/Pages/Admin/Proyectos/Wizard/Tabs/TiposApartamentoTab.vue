<template>
  <AppCard padding="md">
    <div class="flex items-start justify-between gap-3">
      <div>
        <p class="text-sm font-semibold text-gray-900">Pestaña 5 · Tipos de apartamento</p>
        <p class="text-sm text-gray-600 mt-1">Crea tipos en lote y adjunta imagen opcional.</p>
      </div>

      <button
        type="button"
        @click="submit"
        :disabled="disabled || form.processing || !canSubmit"
        class="rounded-xl bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700 transition disabled:opacity-60"
      >
        {{ form.processing ? 'Guardando…' : 'Guardar' }}
      </button>
    </div>

    <div class="mt-6 flex items-center gap-2">
      <button
        type="button"
        class="rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm font-semibold text-gray-800 hover:bg-gray-50 transition"
        @click="addRow"
        :disabled="disabled"
      >
        + Agregar tipo
      </button>

      <button
        type="button"
        class="rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm font-semibold text-gray-800 hover:bg-gray-50 transition"
        @click="resetRows"
        :disabled="disabled || form.tipos.length === 1"
      >
        Limpiar
      </button>
    </div>

    <div class="mt-6 divide-y rounded-2xl border overflow-hidden">
      <div v-for="(t, idx) in form.tipos" :key="t._key" class="p-4 md:p-6 bg-white">
        <div class="flex items-start justify-between gap-3">
          <div>
            <p class="text-sm font-semibold text-gray-900">Tipo #{{ idx + 1 }}</p>
            <p class="text-xs text-gray-500">Nombre requerido. Valor estimado se calcula (área construida × valor m²).</p>
          </div>

          <button
            type="button"
            class="rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-sm font-semibold text-red-700 hover:bg-red-100 transition"
            @click="removeRow(idx)"
            :disabled="disabled || form.tipos.length === 1"
          >
            Quitar
          </button>
        </div>

        <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
          <div class="md:col-span-2">
            <FormField label="Nombre" required :error="fieldError(idx,'nombre')">
              <TextInput v-model="t.nombre" :disabled="disabled" maxlength="100" />
            </FormField>
          </div>

          <FormField label="Área construida (m²)" :error="fieldError(idx,'area_construida')">
            <TextInput v-model.number="t.area_construida" type="number" min="0" step="0.01" :disabled="disabled" />
          </FormField>

          <FormField label="Valor m² (COP)" :error="fieldError(idx,'valor_m2')">
            <TextInput v-model.number="t.valor_m2" type="number" min="0" step="0.01" :disabled="disabled" />
          </FormField>

          <FormField label="Imagen (opcional)" :error="fieldError(idx,'imagen')" class="md:col-span-2">
            <input
              type="file"
              accept="image/*"
              class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm"
              :disabled="disabled"
              @change="onFileChange($event, idx)"
            />
            <p v-if="t._fileName" class="text-xs text-gray-500 mt-1">Seleccionada: <span class="font-medium">{{ t._fileName }}</span></p>
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
  disabled: Boolean,
})

const emit = defineEmits(['saved'])

function newRow() {
  return {
    _key: crypto.randomUUID ? crypto.randomUUID() : String(Date.now() + Math.random()),
    nombre: '',
    area_construida: null,
    valor_m2: null,
    imagen: null,
    _fileName: '',
  }
}

const form = useForm({
  id_proyecto: props.proyectoId || '',
  tipos: [newRow()],
})

function addRow() {
  form.tipos.push(newRow())
}
function removeRow(idx) {
  if (form.tipos.length <= 1) return
  form.tipos.splice(idx, 1)
}
function resetRows() {
  form.tipos = [newRow()]
}
function onFileChange(e, idx) {
  const file = e?.target?.files?.[0]
  form.tipos[idx].imagen = file || null
  form.tipos[idx]._fileName = file?.name || ''
}
function fieldError(idx, field) {
  return form.errors?.[`tipos.${idx}.${field}`] || null
}

const canSubmit = computed(() => {
  if (!props.proyectoId) return false
  return form.tipos.every((t) => String(t.nombre || '').trim().length > 0)
})

function submit() {
  form.id_proyecto = props.proyectoId || ''
  form.post('/proyectos/wizard/tipos-apartamento', {
    preserveScroll: true,
    forceFormData: true,
    onSuccess: () => emit('saved'),
  })
}
</script>
