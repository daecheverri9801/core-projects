<template>
  <AppCard padding="md">
    <div class="flex items-start justify-between gap-3">
      <div>
        <p class="text-sm font-semibold text-gray-900">Pestaña 3 · Torres</p>
        <p class="text-sm text-gray-600 mt-1">Crea una o varias torres para este proyecto.</p>
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

    <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
      <FormField label="Estado torre" required :error="form.errors.id_estado">
        <SelectInput v-model="form.id_estado" :disabled="disabled">
          <option value="" disabled>Seleccione un estado</option>
          <option v-for="e in estadosProyecto" :key="e.id_estado" :value="e.id_estado">{{ e.nombre }}</option>
        </SelectInput>
      </FormField>

      <div class="rounded-2xl border border-gray-200 bg-gray-50 p-4">
        <p class="text-xs font-semibold text-gray-700 uppercase tracking-wide">Proyecto</p>
        <p class="mt-1 text-sm text-gray-900 font-semibold">
          {{ proyectoId ? `ID: ${proyectoId}` : '—' }}
        </p>
        <p class="text-xs text-gray-600 mt-1">Las torres se asociarán a este proyecto.</p>
      </div>
    </div>

    <div class="mt-6 border-t pt-6">
      <div class="flex items-center justify-between gap-3">
        <div>
          <p class="text-sm font-semibold text-gray-900">Torres a crear</p>
          <p class="text-sm text-gray-600">Nombre, # pisos (opcional) y nivel inicio prima altura.</p>
        </div>

        <button
          type="button"
          @click="addTorre"
          class="rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm font-semibold text-gray-800 hover:bg-gray-50 transition"
          :disabled="disabled"
        >
          + Agregar
        </button>
      </div>

      <div class="mt-4 space-y-3">
        <div v-for="(t, idx) in form.torres" :key="idx" class="rounded-2xl border border-gray-200 bg-white p-4">
          <div class="flex items-center justify-between">
            <p class="text-sm font-semibold text-gray-900">Torre {{ idx + 1 }}</p>
            <button
              v-if="form.torres.length > 1"
              type="button"
              @click="removeTorre(idx)"
              class="rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-sm font-semibold text-red-700 hover:bg-red-100 transition"
              :disabled="disabled"
            >
              Quitar
            </button>
          </div>

          <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
            <FormField label="Nombre" required :error="err(`torres.${idx}.nombre_torre`)">
              <TextInput v-model="t.nombre_torre" :disabled="disabled" />
            </FormField>

            <FormField label="Número pisos" :error="err(`torres.${idx}.numero_pisos`)">
              <TextInput v-model.number="t.numero_pisos" type="number" min="1" :disabled="disabled" />
            </FormField>

            <FormField label="Nivel inicio prima" required :error="err(`torres.${idx}.nivel_inicio_prima`)">
              <TextInput v-model.number="t.nivel_inicio_prima" type="number" min="1" :disabled="disabled" />
            </FormField>
          </div>
        </div>

        <p v-if="form.errors.torres" class="text-sm text-red-600">{{ form.errors.torres }}</p>
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
import SelectInput from '@/Components/SelectInput.vue'

const props = defineProps({
  proyectoId: [String, Number, null],
  estadosProyecto: Array,
  disabled: Boolean,
})

const emit = defineEmits(['saved'])

const form = useForm({
  id_proyecto: props.proyectoId || '',
  id_estado: '',
  torres: [{ nombre_torre: 'Torre 1', numero_pisos: null, nivel_inicio_prima: 2 }],
})

function err(path) {
  return form.errors?.[path] || null
}

function addTorre() {
  const next = form.torres.length + 1
  form.torres.push({ nombre_torre: `Torre ${next}`, numero_pisos: null, nivel_inicio_prima: 2 })
}

function removeTorre(idx) {
  form.torres.splice(idx, 1)
}

const canSubmit = computed(() => {
  if (!props.proyectoId) return false
  if (!form.id_estado) return false
  return form.torres.every((t) => String(t.nombre_torre || '').trim() && Number(t.nivel_inicio_prima) >= 1)
})

function submit() {
  form.id_proyecto = props.proyectoId || ''
  form.post('/proyectos/wizard/torres', {
    preserveScroll: true,
    onSuccess: () => emit('saved'),
  })
}
</script>
