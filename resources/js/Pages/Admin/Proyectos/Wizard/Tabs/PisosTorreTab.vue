<template>
  <AppCard padding="md">
    <div class="flex items-start justify-between gap-3">
      <div>
        <p class="text-sm font-semibold text-gray-900">Pestaña 4 · Pisos por torre</p>
        <p class="text-sm text-gray-600 mt-1">Selecciona una torre y crea pisos en lote.</p>
      </div>

      <button
        type="button"
        @click="submit"
        :disabled="disabled || form.processing || !canSubmit"
        class="rounded-xl bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700 transition disabled:opacity-60"
      >
        {{ form.processing ? 'Guardando…' : 'Guardar pisos' }}
      </button>
    </div>

    <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
      <FormField label="Torre" required :error="form.errors.id_torre">
        <SelectInput v-model="form.id_torre" :disabled="disabled">
          <option value="" disabled>Seleccione una torre</option>
          <option v-for="t in torres" :key="t.id_torre" :value="t.id_torre">{{ t.nombre_torre }}</option>
        </SelectInput>
      </FormField>

      <FormField label="Uso por defecto" :error="null">
        <TextInput v-model="defaults.uso" :disabled="disabled" placeholder="Opcional (ej: Residencial)" />
      </FormField>

      <div class="flex items-center justify-end gap-2">
        <button
          type="button"
          @click="addRow()"
          class="rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm font-semibold text-gray-800 hover:bg-gray-50 transition"
          :disabled="disabled"
        >
          + Fila
        </button>
        <button
          type="button"
          @click="addRangeSimple()"
          class="rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm font-semibold text-gray-800 hover:bg-gray-50 transition"
          :disabled="disabled"
        >
          + Rango 1..N
        </button>
      </div>
    </div>

    <div class="mt-6 border-t pt-6 overflow-x-auto">
      <table class="min-w-[900px] w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Nivel *</th>
            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Uso</th>
            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Acciones</th>
          </tr>
        </thead>

        <tbody class="divide-y divide-gray-200 bg-white">
          <tr v-for="(r, idx) in rows" :key="r._key" class="hover:bg-brand-50/40 transition">
            <td class="px-6 py-4">
              <TextInput v-model.number="r.nivel" type="number" min="1" :disabled="disabled" />
              <p v-if="rowErr(idx, 'nivel')" class="mt-1 text-sm text-red-600">{{ rowErr(idx, 'nivel') }}</p>
            </td>
            <td class="px-6 py-4">
              <TextInput v-model="r.uso" :disabled="disabled" />
              <p v-if="rowErr(idx, 'uso')" class="mt-1 text-sm text-red-600">{{ rowErr(idx, 'uso') }}</p>
            </td>
            <td class="px-6 py-4">
              <div class="flex justify-end gap-2">
                <button
                  type="button"
                  class="rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm font-semibold text-gray-800 hover:bg-gray-50 transition"
                  @click="duplicate(idx)"
                  :disabled="disabled"
                >
                  Duplicar
                </button>
                <button
                  type="button"
                  class="rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-sm font-semibold text-red-700 hover:bg-red-100 transition"
                  @click="remove(idx)"
                  :disabled="disabled || rows.length === 1"
                >
                  Eliminar
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>

      <p v-if="form.errors.pisos" class="mt-3 text-sm text-red-600">{{ form.errors.pisos }}</p>
    </div>
  </AppCard>
</template>

<script setup>
import { computed, reactive, ref } from 'vue'
import { useForm } from '@inertiajs/vue3'

import AppCard from '@/Components/AppCard.vue'
import FormField from '@/Components/FormField.vue'
import TextInput from '@/Components/TextInput.vue'
import SelectInput from '@/Components/SelectInput.vue'

const props = defineProps({
  proyectoId: [String, Number, null],
  torres: { type: Array, default: () => [] },
  disabled: Boolean,
})

const emit = defineEmits(['saved'])

const defaults = reactive({ uso: '' })

const rows = ref([{ _key: crypto.randomUUID?.() ?? String(Date.now()), nivel: 1, uso: '' }])

const form = useForm({
  id_torre: '',
  pisos: [],
})

function addRow(seed = {}) {
  rows.value.push({
    _key: crypto.randomUUID?.() ?? String(Date.now() + Math.random()),
    nivel: seed.nivel ?? '',
    uso: seed.uso ?? '',
  })
}

function remove(idx) {
  rows.value.splice(idx, 1)
}

function duplicate(idx) {
  addRow({ nivel: rows.value[idx]?.nivel ?? '', uso: rows.value[idx]?.uso ?? '' })
}

function addRangeSimple() {
  // rango rápido 1..N usando prompt simple (evita modal extra)
  const n = Number(window.prompt('¿Hasta qué nivel deseas crear? (Ej: 20)', '10'))
  if (!Number.isInteger(n) || n < 1) return
  rows.value = []
  for (let i = 1; i <= n; i++) addRow({ nivel: i, uso: '' })
}

function rowErr(idx, field) {
  return form.errors?.[`pisos.${idx}.${field}`] || null
}

const canSubmit = computed(() => {
  if (!form.id_torre) return false
  if (!rows.value.length) return false
  const niveles = rows.value.map((x) => Number(x.nivel))
  if (niveles.some((n) => !Number.isInteger(n) || n < 1)) return false
  if (new Set(niveles).size !== niveles.length) return false
  return true
})

function submit() {
  form.clearErrors()

  form.pisos = rows.value.map((r) => ({
    nivel: Number(r.nivel),
    uso: (r.uso || defaults.uso || '').trim() || null,
  }))

  form.post('/proyectos/wizard/pisos-torre', {
    preserveScroll: true,
    onSuccess: () => emit('saved'),
  })
}
</script>
