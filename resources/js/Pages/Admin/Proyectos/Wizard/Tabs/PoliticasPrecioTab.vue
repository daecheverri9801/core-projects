<!-- resources/js/Pages/Admin/Proyectos/Wizard/Tabs/PoliticasPrecioTab.vue -->
<template>
  <AppCard padding="md">
    <div class="flex items-start justify-between gap-3">
      <div class="min-w-0">
        <p class="text-sm font-semibold text-gray-900">Pestaña 2 · Políticas de precio</p>
        <p class="text-sm text-gray-600 mt-1">Crea una política asociada al proyecto actual.</p>
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
      <FormField label="Ventas por escalón" :error="form.errors.ventas_por_escalon" hint="Ej: 10 (opcional)">
        <TextInput
          v-model.number="form.ventas_por_escalon"
          type="number"
          min="1"
          placeholder="10"
          :disabled="disabled"
        />
      </FormField>

      <FormField label="% Aumento" :error="form.errors.porcentaje_aumento" hint="Ej: 5.5 (opcional)">
        <TextInput
          v-model.number="form.porcentaje_aumento"
          type="number"
          step="0.001"
          min="0"
          max="999.999"
          placeholder="5.5"
          :disabled="disabled"
        />
      </FormField>

      <FormField label="Aplica desde" :error="form.errors.aplica_desde">
        <TextInput v-model="form.aplica_desde" type="date" :disabled="disabled" />
      </FormField>

      <div class="md:col-span-2">
        <Toggle
          v-model="form.estado"
          label="Política activa"
          description="Si está activa, podrá aplicarse según las reglas del sistema."
          :disabled="disabled"
        />
      </div>
    </div>

    <div class="mt-6 rounded-2xl border border-gray-200 bg-gray-50 p-4">
      <p class="text-xs font-semibold text-gray-700 uppercase tracking-wide">Asociación</p>
      <p class="mt-1 text-sm text-gray-900">
        Proyecto actual: <span class="font-semibold">{{ proyectoId || '—' }}</span>
      </p>
      <p class="mt-1 text-xs text-gray-600">Esta política quedará ligada al proyecto del wizard.</p>
    </div>
  </AppCard>
</template>

<script setup>
import { computed } from 'vue'
import { useForm } from '@inertiajs/vue3'

import AppCard from '@/Components/AppCard.vue'
import FormField from '@/Components/FormField.vue'
import TextInput from '@/Components/TextInput.vue'
import Toggle from '@/Components/Toggle.vue'

const props = defineProps({
  proyectoId: { type: [String, Number, null], default: null },
  disabled: { type: Boolean, default: false },
})

const emit = defineEmits(['saved'])

const form = useForm({
  id_proyecto: props.proyectoId || '',
  ventas_por_escalon: null,
  porcentaje_aumento: null,
  aplica_desde: '',
  estado: true,
})

const canSubmit = computed(() => {
  if (!props.proyectoId) return false
  // Política mínima: id_proyecto + estado (el resto opcional)
  return form.estado === true || form.estado === false
})

function submit() {
  form.id_proyecto = props.proyectoId || ''
  form.post('/proyectos/wizard/politicas-precio', {
    preserveScroll: true,
    onSuccess: () => {
      emit('saved')
      // opcional: reset parcial para crear otra política rápido
      form.ventas_por_escalon = null
      form.porcentaje_aumento = null
      form.aplica_desde = ''
      form.estado = true
      form.clearErrors()
    },
  })
}
</script>
