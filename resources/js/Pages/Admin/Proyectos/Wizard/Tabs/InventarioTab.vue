<!-- resources/js/Pages/Admin/Proyectos/Wizard/Tabs/InventarioTab.vue -->
<template>
  <div class="space-y-6">
    <!-- APARTAMENTOS -->
    <AppCard padding="md">
      <div class="flex items-start justify-between gap-3">
        <div class="min-w-0">
          <p class="text-sm font-semibold text-gray-900">Pestaña 6 · Inventario · Apartamentos</p>
          <p class="text-sm text-gray-600 mt-1">
            Selecciona torre, agrega filas (o rango) y guarda en lote.
          </p>
        </div>

        <button
          type="button"
          @click="submitApartamentos"
          :disabled="disabled || apt.processing || !canSubmitApartamentos"
          class="rounded-xl bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700 transition disabled:opacity-60"
        >
          {{ apt.processing ? 'Guardando…' : 'Guardar apartamentos' }}
        </button>
      </div>

      <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
        <FormField label="Torre" required :error="apt.errors.id_torre">
          <SelectInput v-model="aptBase.id_torre" :disabled="disabled" @change="onAptTorreChange">
            <option value="" disabled>Seleccione una torre</option>
            <option v-for="t in torres" :key="t.id_torre" :value="t.id_torre">
              {{ t.nombre_torre }}
            </option>
          </SelectInput>
        </FormField>

        <div class="rounded-2xl border border-gray-200 bg-gray-50 p-4">
          <p class="text-xs font-semibold text-gray-700 uppercase tracking-wide">Proyecto</p>
          <p class="mt-1 text-sm text-gray-900 font-semibold">
            {{ proyectoId ? `ID: ${proyectoId}` : '—' }}
          </p>
          <p class="mt-1 text-xs text-gray-600">Inventario asociado al proyecto actual.</p>
        </div>
      </div>

      <div class="mt-6 border-t pt-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
          <div>
            <p class="text-sm font-semibold text-gray-900">Filas</p>
            <p class="text-sm text-gray-600">
              Cada fila define Piso, Tipo, Estado y Número. Valor total se muestra como referencia.
            </p>
          </div>

          <div class="flex items-center gap-2">
            <button
              type="button"
              class="btn-secondary"
              @click="addAptRow"
              :disabled="disabled || !aptBase.id_torre"
            >
              + Agregar fila
            </button>
            <button
              type="button"
              class="btn-secondary"
              @click="openAptRange"
              :disabled="disabled || !aptBase.id_torre"
            >
              + Rango
            </button>
          </div>
        </div>

        <div class="mt-4 overflow-x-auto">
          <table class="min-w-[1100px] w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">
                  Piso
                </th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">
                  Tipo
                </th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">
                  Estado
                </th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">
                  Número
                </th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">
                  Valor total
                </th>
                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">
                  Acción
                </th>
              </tr>
            </thead>

            <tbody class="divide-y divide-gray-200 bg-white">
              <tr
                v-for="(row, idx) in aptRows"
                :key="row._key"
                class="hover:bg-brand-50/40 transition"
              >
                <td class="px-4 py-3 align-top">
                  <select
                    v-model="row.id_piso_torre"
                    class="form-input"
                    :disabled="disabled || pisosByTorre.length === 0"
                  >
                    <option value="">Seleccione</option>
                    <option
                      v-for="p in pisosByTorre"
                      :key="p.id_piso_torre"
                      :value="p.id_piso_torre"
                    >
                      Nivel {{ p.nivel }}
                    </option>
                  </select>
                  <p v-if="aptRowErrors[idx]?.id_piso_torre" class="form-error">
                    {{ aptRowErrors[idx].id_piso_torre }}
                  </p>
                </td>

                <td class="px-4 py-3 align-top">
                  <select
                    v-model="row.id_tipo_apartamento"
                    class="form-input"
                    :disabled="disabled || !proyectoId"
                  >
                    <option value="">Seleccione</option>
                    <option
                      v-for="t in tiposApartamento"
                      :key="t.id_tipo_apartamento"
                      :value="t.id_tipo_apartamento"
                    >
                      {{ t.nombre }} — {{ formatCurrency(t.valor_estimado) }}
                    </option>
                  </select>
                  <p v-if="aptRowErrors[idx]?.id_tipo_apartamento" class="form-error">
                    {{ aptRowErrors[idx].id_tipo_apartamento }}
                  </p>
                </td>

                <td class="px-4 py-3 align-top">
                  <select v-model="row.id_estado_inmueble" class="form-input" :disabled="disabled">
                    <option value="">Seleccione</option>
                    <option
                      v-for="e in estadosInmueble"
                      :key="e.id_estado_inmueble"
                      :value="e.id_estado_inmueble"
                    >
                      {{ e.nombre }}
                    </option>
                  </select>
                  <p v-if="aptRowErrors[idx]?.id_estado_inmueble" class="form-error">
                    {{ aptRowErrors[idx].id_estado_inmueble }}
                  </p>
                </td>

                <td class="px-4 py-3 align-top">
                  <input
                    v-model="row.numero"
                    type="text"
                    maxlength="20"
                    class="form-input"
                    :disabled="disabled"
                    placeholder="Ej: 302"
                  />
                  <p v-if="aptRowErrors[idx]?.numero" class="form-error">
                    {{ aptRowErrors[idx].numero }}
                  </p>
                </td>

                <td class="px-4 py-3 align-top">
                  <div class="text-sm font-semibold text-gray-900">
                    {{ formatCurrency(valorTotalRow(row)) }}
                  </div>
                  <div class="text-xs text-gray-500 mt-1">Tipo + prima altura (referencia UI)</div>
                </td>

                <td class="px-4 py-3 align-top">
                  <div class="flex justify-end">
                    <button
                      type="button"
                      class="icon-danger"
                      @click="removeAptRow(idx)"
                      :disabled="disabled || aptRows.length === 1"
                    >
                      Quitar
                    </button>
                  </div>
                </td>
              </tr>

              <tr v-if="aptRows.length === 0">
                <td colspan="6" class="px-4 py-8 text-center text-sm text-gray-500">
                  Agrega al menos una fila.
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <p v-if="apt.errors.general" class="mt-3 text-sm text-red-600">{{ apt.errors.general }}</p>
      </div>
    </AppCard>

    <!-- MODAL RANGO APARTAMENTOS -->
    <ConfirmDialog
      :open="aptRange.open"
      title="Agregar rango de números"
      :message="''"
      cancel-text="Cancelar"
      confirm-text="Agregar"
      @cancel="aptRange.open = false"
      @confirm="applyAptRange"
    >
      <template #content>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="form-label">Desde</label>
            <input v-model="aptRange.desde" type="text" class="form-input" placeholder="Ej: 301" />
          </div>
          <div>
            <label class="form-label">Hasta</label>
            <input v-model="aptRange.hasta" type="text" class="form-input" placeholder="Ej: 310" />
          </div>

          <p class="md:col-span-2 text-xs text-gray-500">
            Se crean filas con el número. Piso/Tipo/Estado quedan por seleccionar.
          </p>

          <p v-if="aptRange.error" class="md:col-span-2 text-sm text-red-600">
            {{ aptRange.error }}
          </p>
        </div>
      </template>
    </ConfirmDialog>

    <!-- LOCALES -->
    <AppCard padding="md">
      <div class="flex items-start justify-between gap-3">
        <div class="min-w-0">
          <p class="text-sm font-semibold text-gray-900">Pestaña 6 · Inventario · Locales</p>
          <p class="text-sm text-gray-600 mt-1">
            Crea uno o varios locales. Guarda en lote y permanece en el wizard.
          </p>
        </div>

        <button
          type="button"
          @click="submitLocales"
          :disabled="disabled || loc.processing || !canSubmitLocales"
          class="rounded-xl bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700 transition disabled:opacity-60"
        >
          {{ loc.processing ? 'Guardando…' : 'Guardar locales' }}
        </button>
      </div>

      <div class="mt-6 flex items-center gap-2">
        <button type="button" class="btn-secondary" @click="addLocRow" :disabled="disabled">
          + Agregar local
        </button>
        <button
          type="button"
          class="btn-secondary"
          @click="resetLocRows"
          :disabled="disabled || locRows.length === 1"
        >
          Limpiar
        </button>
      </div>

      <div class="mt-6 divide-y rounded-2xl border overflow-hidden">
        <div v-for="(l, idx) in locRows" :key="l._key" class="p-4 md:p-6 bg-white">
          <div class="flex items-start justify-between">
            <p class="text-sm font-semibold text-gray-900">Local #{{ idx + 1 }}</p>
            <button
              type="button"
              class="rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-sm font-semibold text-red-700 hover:bg-red-100 transition"
              @click="removeLocRow(idx)"
              :disabled="disabled || locRows.length === 1"
            >
              Quitar
            </button>
          </div>

          <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
            <FormField label="Torre" required :error="locFieldError(idx, 'id_torre')">
              <SelectInput
                v-model="l.id_torre"
                :disabled="disabled"
                @change="onLocTorreChange(idx)"
              >
                <option value="" disabled>Seleccione una torre</option>
                <option v-for="t in torres" :key="t.id_torre" :value="t.id_torre">
                  {{ t.nombre_torre }}
                </option>
              </SelectInput>
            </FormField>

            <FormField label="Piso" required :error="locFieldError(idx, 'id_piso_torre')">
              <SelectInput
                v-model="l.id_piso_torre"
                :disabled="disabled || pisosForLoc(idx).length === 0"
              >
                <option value="" disabled>Seleccione un piso</option>
                <option
                  v-for="p in pisosForLoc(idx)"
                  :key="p.id_piso_torre"
                  :value="p.id_piso_torre"
                >
                  Nivel {{ p.nivel }}
                </option>
              </SelectInput>
            </FormField>

            <FormField
              label="Estado inmueble"
              required
              :error="locFieldError(idx, 'id_estado_inmueble')"
            >
              <SelectInput v-model="l.id_estado_inmueble" :disabled="disabled">
                <option value="" disabled>Seleccione</option>
                <option
                  v-for="e in estadosInmueble"
                  :key="e.id_estado_inmueble"
                  :value="e.id_estado_inmueble"
                >
                  {{ e.nombre }}
                </option>
              </SelectInput>
            </FormField>

            <FormField label="Número" required :error="locFieldError(idx, 'numero')">
              <TextInput
                v-model="l.numero"
                maxlength="20"
                :disabled="disabled"
                placeholder="Ej: L-05"
              />
            </FormField>

            <FormField label="Área total (m²)" :error="locFieldError(idx, 'area_total_local')">
              <TextInput
                v-model.number="l.area_total_local"
                type="number"
                min="0"
                step="0.01"
                :disabled="disabled"
              />
            </FormField>

            <FormField label="Valor m² (COP)" :error="locFieldError(idx, 'valor_m2')">
              <TextInput
                v-model.number="l.valor_m2"
                type="number"
                min="0"
                step="0.01"
                :disabled="disabled"
              />
            </FormField>

            <div class="md:col-span-2">
              <div class="rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3">
                <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                  <p class="text-sm font-medium text-gray-700">Valor total (calculado)</p>
                  <p class="text-lg font-semibold text-gray-900">{{ displayValorTotalLocal(l) }}</p>
                </div>
                <p class="mt-1 text-xs text-gray-500">Cálculo: Área total × Valor m².</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <p v-if="loc.errors.general" class="mt-3 text-sm text-red-600">{{ loc.errors.general }}</p>
    </AppCard>
  </div>
</template>

<script setup>
import { computed, reactive, ref } from 'vue'
import { useForm } from '@inertiajs/vue3'

import AppCard from '@/Components/AppCard.vue'
import FormField from '@/Components/FormField.vue'
import TextInput from '@/Components/TextInput.vue'
import SelectInput from '@/Components/SelectInput.vue'
import ConfirmDialog from '@/Components/ConfirmDialog.vue'

const props = defineProps({
  proyectoId: { type: [String, Number, null], default: null },
  torres: { type: Array, default: () => [] },
  pisos: { type: Array, default: () => [] }, // [{id_piso_torre,id_torre,nivel,uso}]
  tiposApartamento: { type: Array, default: () => [] }, // [{id_tipo_apartamento,nombre,valor_estimado}]
  estadosInmueble: { type: Array, default: () => [] },
  disabled: { type: Boolean, default: false },
})

const emit = defineEmits(['saved-apartamentos', 'saved-locales'])

// ----------------------
// Helpers
// ----------------------
function cryptoKey() {
  return `${Date.now()}_${Math.random().toString(16).slice(2)}`
}

function formatCurrency(val) {
  const num = Number(val || 0)
  return new Intl.NumberFormat('es-CO', {
    style: 'currency',
    currency: 'COP',
    maximumFractionDigits: 0,
  }).format(num)
}

// ======================
// APARTAMENTOS (LOTE)
// ======================
const aptBase = reactive({
  id_torre: '',
})

function newAptRow(overrides = {}) {
  return {
    _key: cryptoKey(),
    id_piso_torre: '',
    id_tipo_apartamento: '',
    id_estado_inmueble: '',
    numero: '',
    ...overrides,
  }
}

const aptRows = ref([newAptRow()])
const aptRowErrors = ref([])

const apt = useForm({
  id_torre: '',
  apartamentos: [],
})

const pisosByTorre = computed(() => {
  const idTorre = Number(aptBase.id_torre)
  if (!idTorre) return []
  return props.pisos.filter((p) => Number(p.id_torre) === idTorre)
})

function onAptTorreChange() {
  // reset filas al cambiar torre para evitar incoherencias
  aptRows.value = [newAptRow()]
  aptRowErrors.value = []
  apt.clearErrors()
}

function addAptRow() {
  aptRows.value.push(newAptRow())
}
function removeAptRow(idx) {
  if (aptRows.value.length <= 1) return
  aptRows.value.splice(idx, 1)
}

const aptRange = reactive({
  open: false,
  desde: '',
  hasta: '',
  error: '',
})

function openAptRange() {
  aptRange.open = true
  aptRange.desde = ''
  aptRange.hasta = ''
  aptRange.error = ''
}

function applyAptRange() {
  aptRange.error = ''
  const desdeRaw = String(aptRange.desde || '').trim()
  const hastaRaw = String(aptRange.hasta || '').trim()

  const desdeNum = Number(desdeRaw)
  const hastaNum = Number(hastaRaw)

  if (
    !desdeRaw ||
    !hastaRaw ||
    !Number.isInteger(desdeNum) ||
    !Number.isInteger(hastaNum) ||
    hastaNum < desdeNum
  ) {
    aptRange.error = 'Rango inválido. Usa enteros y "Hasta" >= "Desde".'
    return
  }

  const pad = /^\d+$/.test(desdeRaw) ? desdeRaw.length : 0
  const nuevos = []
  for (let n = desdeNum; n <= hastaNum; n++) {
    const numero = pad ? String(n).padStart(pad, '0') : String(n)
    nuevos.push(newAptRow({ numero }))
  }

  if (aptRows.value.length === 1 && !String(aptRows.value[0].numero || '').trim()) {
    aptRows.value = nuevos
  } else {
    aptRows.value.push(...nuevos)
  }

  aptRange.open = false
}

// Prima altura: como en tu implementación original, sin acceder a proyecto completo (solo referencia UI).
// Aquí no calculamos prima altura real si no tenemos datos del proyecto y nivel_inicio_prima por torre.
// Mostramos valor tipo como mínimo; si tienes proyecto disponible en props del wizard, puedes enriquecer.
function valorEstimadoTipoRow(row) {
  const t = props.tiposApartamento.find(
    (x) => Number(x.id_tipo_apartamento) === Number(row.id_tipo_apartamento)
  )
  return t ? Number(t.valor_estimado || 0) : 0
}

function primaAlturaRow(row) {
  // Si quieres cálculo real, necesitas:
  // - props.torres con nivel_inicio_prima
  // - props.proyecto con prima_altura_activa/base/incremento
  // Como este tab recibe solo torres/pisos, por defecto lo dejamos en 0.
  return 0
}

function valorTotalRow(row) {
  return valorEstimadoTipoRow(row) + primaAlturaRow(row)
}

const canSubmitApartamentos = computed(() => {
  if (!props.proyectoId) return false
  if (!aptBase.id_torre) return false
  if (!aptRows.value.length) return false

  return aptRows.value.every((r) => {
    return (
      String(r.numero || '').trim() &&
      r.id_piso_torre &&
      r.id_tipo_apartamento &&
      r.id_estado_inmueble
    )
  })
})

function submitApartamentos() {
  apt.clearErrors()
  aptRowErrors.value = []
  apt.processing = true

  // Validación rápida front
  const perRow = aptRows.value.map(() => ({}))
  let hasErr = false

  if (!aptBase.id_torre) {
    apt.setError('id_torre', 'La torre es obligatoria')
    hasErr = true
  }

  aptRows.value.forEach((r, i) => {
    if (!r.id_piso_torre) {
      perRow[i].id_piso_torre = 'El piso es obligatorio'
      hasErr = true
    }
    if (!r.id_tipo_apartamento) {
      perRow[i].id_tipo_apartamento = 'El tipo es obligatorio'
      hasErr = true
    }
    if (!r.id_estado_inmueble) {
      perRow[i].id_estado_inmueble = 'El estado es obligatorio'
      hasErr = true
    }
    if (!String(r.numero || '').trim()) {
      perRow[i].numero = 'El número es obligatorio'
      hasErr = true
    }
  })

  if (hasErr) {
    aptRowErrors.value = perRow
    apt.processing = false
    return
  }

  apt
    .transform(() => ({
      id_torre: aptBase.id_torre,
      apartamentos: aptRows.value.map((r) => ({
        numero: String(r.numero || '').trim(),
        id_piso_torre: r.id_piso_torre,
        id_tipo_apartamento: r.id_tipo_apartamento,
        id_estado_inmueble: r.id_estado_inmueble,
      })),
    }))
    .post('/proyectos/wizard/apartamentos', {
      preserveScroll: true,
      onError: (e) => {
        // Map errores indexados: apartamentos.0.numero, etc.
        const rowE = aptRows.value.map(() => ({}))
        for (const [k, v] of Object.entries(e || {})) {
          const m = k.match(
            /^apartamentos\.(\d+)\.(numero|id_piso_torre|id_tipo_apartamento|id_estado_inmueble)$/
          )
          if (m) {
            const idx = Number(m[1])
            const field = m[2]
            if (!Number.isNaN(idx) && rowE[idx]) rowE[idx][field] = Array.isArray(v) ? v[0] : v
          }
        }
        aptRowErrors.value = rowE
      },
      onSuccess: () => {
        emit('saved-apartamentos')
        // reset para seguir creando
        aptRows.value = [newAptRow()]
        aptBase.id_torre = ''
        aptRowErrors.value = []
        apt.clearErrors()
      },
      onFinish: () => {
        apt.processing = false
      },
    })
}

// ======================
// LOCALES (LOTE)
// ======================
function newLocRow() {
  return {
    _key: cryptoKey(),
    id_torre: '',
    id_piso_torre: '',
    id_estado_inmueble: '',
    numero: '',
    area_total_local: null,
    valor_m2: null,
  }
}

const locRows = ref([newLocRow()])

const loc = useForm({
  locales: [],
})

function addLocRow() {
  locRows.value.push(newLocRow())
}
function removeLocRow(idx) {
  if (locRows.value.length <= 1) return
  locRows.value.splice(idx, 1)
}
function resetLocRows() {
  locRows.value = [newLocRow()]
  loc.clearErrors()
}

function pisosForLoc(idx) {
  const idTorre = Number(locRows.value[idx]?.id_torre)
  if (!idTorre) return []
  return props.pisos.filter((p) => Number(p.id_torre) === idTorre)
}

function onLocTorreChange(idx) {
  // al cambiar torre, limpiar piso para que no quede inválido
  locRows.value[idx].id_piso_torre = ''
}

function locFieldError(idx, field) {
  // errores estilo: locales.0.numero ...
  return loc.errors?.[`locales.${idx}.${field}`] || null
}

function displayValorTotalLocal(l) {
  const area = Number(l.area_total_local)
  const v2 = Number(l.valor_m2)
  if (
    !Number.isFinite(area) ||
    !Number.isFinite(v2) ||
    l.area_total_local === null ||
    l.valor_m2 === null
  )
    return '—'
  return formatCurrency(area * v2)
}

const canSubmitLocales = computed(() => {
  if (!props.proyectoId) return false
  if (!locRows.value.length) return false
  return locRows.value.every((l) => {
    return String(l.numero || '').trim() && l.id_torre && l.id_piso_torre && l.id_estado_inmueble
  })
})

function submitLocales() {
  loc.clearErrors()

  loc
    .transform(() => ({
      locales: locRows.value.map((l) => ({
        numero: String(l.numero || '').trim(),
        id_torre: l.id_torre,
        id_piso_torre: l.id_piso_torre,
        id_estado_inmueble: l.id_estado_inmueble,
        area_total_local: l.area_total_local === '' ? null : l.area_total_local,
        valor_m2: l.valor_m2 === '' ? null : l.valor_m2,
      })),
    }))
    .post('/proyectos/wizard/locales', {
      preserveScroll: true,
      onSuccess: () => {
        emit('saved-locales')
        resetLocRows()
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
.btn-secondary {
  @apply inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-gray-300 text-gray-800 hover:bg-gray-50 transition disabled:opacity-50;
}
.icon-danger {
  @apply inline-flex items-center justify-center rounded-xl border border-red-300 px-3 py-2 text-sm font-semibold text-red-600 hover:bg-red-50 transition disabled:opacity-50;
}
</style>
