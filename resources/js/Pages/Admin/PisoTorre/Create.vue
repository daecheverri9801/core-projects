<!-- resources/js/Pages/Admin/PisoTorre/Create.vue -->
<template>
  <SidebarBannerLayout :empleado="empleado">
    <div class="space-y-6">
      <PageHeader
        title="Crear Pisos"
        kicker="Carga masiva"
        subtitle="Crea varios pisos para un proyecto en una sola operación."
      >
        <template #actions>
          <Link
            :href="route('pisostorre.index')"
            class="rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-800 hover:bg-gray-50 transition"
          >
            Volver
          </Link>
        </template>
      </PageHeader>

      <AppCard padding="md" class="max-w-5xl">
        <!-- Configuración general -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <label class="form-label">Proyecto *</label>
            <select v-model="form.id_proyecto" @change="loadTorres" class="form-input">
              <option value="">Seleccione un proyecto</option>
              <option v-for="p in proyectos" :key="p.id_proyecto" :value="p.id_proyecto">
                {{ p.nombre }}
              </option>
            </select>
            <p v-if="form.errors.id_proyecto" class="form-error">{{ form.errors.id_proyecto }}</p>
          </div>

          <div>
            <label class="form-label">Torre *</label>
            <select v-model="form.id_torre" :disabled="!torres.length" class="form-input">
              <option value="">Seleccione una torre</option>
              <option v-for="t in torres" :key="t.id_torre" :value="t.id_torre">
                {{ t.nombre_torre }}
              </option>
            </select>
            <p v-if="form.errors.id_torre" class="form-error">{{ form.errors.id_torre }}</p>
          </div>

          <div class="md:col-span-1">
            <label class="form-label">Uso (por defecto)</label>
            <input
              v-model="defaults.uso"
              type="text"
              maxlength="40"
              class="form-input"
              placeholder="Opcional (ej: Residencial)"
            />
            <p class="text-xs text-gray-500 mt-1">Se aplicará a filas sin “Uso” definido.</p>
          </div>
        </div>

        <!-- Carga masiva -->
        <div class="mt-6 border-t pt-6">
          <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <div>
              <h3 class="text-sm font-semibold text-gray-900">Pisos a crear</h3>
              <p class="text-sm text-gray-600">
                Agrega filas y registra <span class="font-semibold">Nivel</span> y
                <span class="font-semibold">Uso</span>.
              </p>
            </div>

            <div class="flex items-center gap-2">
              <button
                type="button"
                @click="addRow()"
                class="rounded-xl bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700 transition"
              >
                + Agregar fila
              </button>
              <button
                type="button"
                @click="addRange()"
                class="rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-800 hover:bg-gray-50 transition"
              >
                + Rango
              </button>
            </div>
          </div>

          <!-- Tabla filas -->
          <div class="mt-4 overflow-x-auto">
            <table class="min-w-[900px] w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th
                    class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider"
                  >
                    Nivel *
                  </th>
                  <th
                    class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider"
                  >
                    Uso
                  </th>
                  <th
                    class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider"
                  >
                    Acciones
                  </th>
                </tr>
              </thead>

              <tbody class="divide-y divide-gray-200 bg-white">
                <tr
                  v-for="(row, idx) in rows"
                  :key="row._key"
                  class="hover:bg-brand-50/40 transition"
                >
                  <td class="px-6 py-4">
                    <input
                      v-model.number="row.nivel"
                      type="number"
                      class="form-input"
                      placeholder="Ej: 1"
                      @blur="row.nivel = normalizeInt(row.nivel)"
                    />
                    <p v-if="rowErrors(idx, 'nivel')" class="form-error">
                      {{ rowErrors(idx, 'nivel') }}
                    </p>
                  </td>

                  <td class="px-6 py-4">
                    <input
                      v-model="row.uso"
                      type="text"
                      maxlength="40"
                      class="form-input"
                      placeholder="Opcional"
                    />
                    <p v-if="rowErrors(idx, 'uso')" class="form-error">
                      {{ rowErrors(idx, 'uso') }}
                    </p>
                  </td>

                  <td class="px-6 py-4">
                    <div class="flex items-center justify-end gap-2">
                      <button
                        type="button"
                        class="rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm font-semibold text-gray-800 hover:bg-gray-50 transition"
                        @click="duplicateRow(idx)"
                        title="Duplicar"
                      >
                        Duplicar
                      </button>
                      <button
                        type="button"
                        class="rounded-xl border border-red-300 bg-white px-3 py-2 text-sm font-semibold text-red-700 hover:bg-red-50 transition"
                        @click="removeRow(idx)"
                        title="Eliminar"
                      >
                        Eliminar
                      </button>
                    </div>
                  </td>
                </tr>

                <tr v-if="rows.length === 0">
                  <td colspan="3" class="px-6 py-10 text-center text-sm text-gray-600">
                    Agrega al menos una fila para crear pisos.
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Validación local -->
          <div
            v-if="localValidationError"
            class="mt-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800"
          >
            {{ localValidationError }}
          </div>

          <!-- Botones -->
          <div class="mt-6 flex items-center justify-between gap-3">
            <div class="text-sm text-gray-600">
              Filas: <span class="font-semibold text-gray-900">{{ rows.length }}</span>
            </div>

            <div class="flex items-center gap-3">
              <Link
                :href="route('pisostorre.index')"
                class="rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-800 hover:bg-gray-50 transition"
              >
                Cancelar
              </Link>

              <button
                type="button"
                @click="submit"
                :disabled="form.processing"
                class="rounded-xl bg-brand-600 px-5 py-2 text-sm font-semibold text-white hover:bg-brand-700 transition disabled:opacity-50"
              >
                {{ form.processing ? 'Guardando...' : 'Guardar pisos' }}
              </button>
            </div>
          </div>
        </div>

        <FlashMessages />
      </AppCard>

      <!-- Modal rango -->
      <RangeModal
        :open="rangeModal.open"
        title="Agregar rango de niveles"
        :desde="rangeModal.desde"
        :hasta="rangeModal.hasta"
        @cancel="rangeModal.open = false"
        @confirm="applyRange"
      />
    </div>
  </SidebarBannerLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, router, useForm } from '@inertiajs/vue3'

import SidebarBannerLayout from '@/Components/SidebarBannerLayout.vue'
import AppCard from '@/Components/AppCard.vue'
import PageHeader from '@/Components/PageHeader.vue'
import ButtonPrimary from '@/Components/ButtonPrimary.vue'
import QuickSearch from '@/Components/QuickSearch.vue'
import ConfirmDialog from '@/Components/ConfirmDialog.vue'
import RangeModal from '@/Components/RangeModal.vue'
import FlashMessages from '@/Components/FlashMessages.vue'

import { PlusIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  proyectos: { type: Array, default: () => [] },
  empleado: { type: Object, default: null },
})

/**
 * Form principal (Inertia)
 * - id_proyecto solo para UI/validación; en back realmente usaremos id_torre + pisos[]
 */
const form = useForm({
  id_proyecto: '',
  id_torre: '',
  pisos: [], // se llena al enviar
})

const defaults = ref({
  uso: '',
})



const torres = ref([])

const rows = ref([{ _key: crypto.randomUUID?.() ?? String(Date.now()), nivel: '', uso: '' }])

const localValidationError = ref('')

function normalizeInt(v) {
  if (v === '' || v === null || v === undefined) return ''
  const n = Number(v)
  if (Number.isNaN(n)) return ''
  return Math.trunc(n)
}





async function loadTorres() {
  form.id_torre = ''
  torres.value = []
  localValidationError.value = ''
  if (!form.id_proyecto) return

  try {
    const res = await fetch(`/api/torres-por-proyecto/${form.id_proyecto}`)
    if (!res.ok) throw new Error('Error cargando torres')
    torres.value = await res.json()
  } catch (e) {
    console.error(e)
  }
}

function addRow(seed = null) {
  rows.value.push({
    _key: crypto.randomUUID?.() ?? String(Date.now() + Math.random()),
    nivel: seed?.nivel ?? '',
    uso: seed?.uso ?? '',
  })
}

function removeRow(idx) {
  rows.value.splice(idx, 1)
}

function duplicateRow(idx) {
  const r = rows.value[idx]
  addRow({ nivel: r?.nivel ?? '', uso: r?.uso ?? '' })
}

const rangeModal = ref({ open: false, desde: 1, hasta: 1 })

function addRange() {
  rangeModal.value = { open: true, desde: 1, hasta: 1 }
}

function applyRange({ desde, hasta }) {
  for (let n = desde; n <= hasta; n++) {
    rows.value.push({
      _key: crypto.randomUUID?.() ?? String(Date.now() + Math.random()),
      nivel: n,
      uso: '',
    })
  }
  rangeModal.value.open = false
  localValidationError.value = ''
}

/**
 * Helpers para leer errores por fila si el backend devuelve:
 * pisos.0.nivel, pisos.2.uso, etc.
 */
function rowErrors(idx, field) {
  const key = `pisos.${idx}.${field}`
  return form.errors?.[key]
}

function validateLocal() {
  localValidationError.value = ''

  if (!form.id_proyecto) {
    localValidationError.value = 'Selecciona un proyecto.'
    return false
  }
  if (!form.id_torre) {
    localValidationError.value = 'Selecciona una torre.'
    return false
  }
  if (!rows.value.length) {
    localValidationError.value = 'Agrega al menos un piso.'
    return false
  }

  const niveles = rows.value.map((r) => normalizeInt(r.nivel)).filter((n) => n !== '')

  if (niveles.length !== rows.value.length) {
    localValidationError.value = 'Todos los niveles deben ser numéricos.'
    return false
  }
  if (niveles.some((n) => n < 1)) {
    localValidationError.value = 'El nivel mínimo es 1.'
    return false
  }

  // duplicados en el payload
  const set = new Set()
  for (const n of niveles) {
    if (set.has(n)) {
      localValidationError.value = `Nivel duplicado en la carga: ${n}.`
      return false
    }
    set.add(n)
  }

  return true
}

function submit() {
  if (!validateLocal()) return

  // armar payload limpio
  const payloadPisos = rows.value.map((r) => ({
    nivel: normalizeInt(r.nivel),
    uso: (r.uso || defaults.value.uso || '').trim() || null,
  }))

  form
    .transform((data) => ({
      id_torre: data.id_torre,
      pisos: payloadPisos,
    }))
    .post(route('pisostorre.store'), {
      onError: () => {
        // errores ya quedan en form.errors (incluye pisos.*)
        localValidationError.value = ''
      },
    })
}
</script>

<style scoped>
.form-label {
  @apply block text-sm font-medium text-gray-700 mb-1;
}
.form-input {
  @apply w-full rounded-xl border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500;
}
.form-error {
  @apply text-sm text-red-600 mt-1;
}
</style>
