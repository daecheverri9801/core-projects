<!-- resources/js/Pages/Admin/Apartamento/Create.vue -->
<template>
  <SidebarBannerLayout :empleado="empleado">
    <div class="space-y-6">
      <PageHeader
        title="Crear Apartamentos"
        kicker="Inventario"
        subtitle="Selecciona proyecto y torre. Configura cada apartamento por fila."
      >
        <template #actions>
          <ButtonPrimary :href="route('apartamentos.index')">Volver</ButtonPrimary>
        </template>
      </PageHeader>

      <AppCard padding="md">
        <!-- Parámetros generales -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="form-label">Proyecto</label>
            <select v-model="base.id_proyecto" @change="onProyectoChange" class="form-input">
              <option value="">Seleccione un proyecto</option>
              <option v-for="p in proyectos" :key="p.id_proyecto" :value="p.id_proyecto">
                {{ p.nombre }}
              </option>
            </select>
            <p v-if="errors.id_proyecto" class="form-error">{{ errors.id_proyecto }}</p>
          </div>

          <div>
            <label class="form-label">Torre</label>
            <select
              v-model="base.id_torre"
              :disabled="torresLocal.length === 0"
              @change="onTorreChange"
              class="form-input"
            >
              <option value="">Seleccione una torre</option>
              <option v-for="t in torresLocal" :key="t.id_torre" :value="t.id_torre">
                {{ t.nombre_torre }}
              </option>
            </select>
            <p v-if="errors.id_torre" class="form-error">{{ errors.id_torre }}</p>
          </div>
        </div>

        <div class="mt-6 border-t pt-6">
          <!-- Header filas -->
          <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <div>
              <p class="text-sm font-semibold text-gray-900">Apartamentos a crear</p>
              <p class="text-sm text-gray-600">
                Cada fila define Piso, Tipo, Estado, Número y el Valor Total calculado.
              </p>
            </div>

            <div class="flex items-center gap-2">
              <button
                type="button"
                class="btn-secondary"
                @click="addRow"
                :disabled="!base.id_torre"
              >
                + Agregar fila
              </button>
              <button type="button" class="btn-secondary" @click="rangeOpen = true">+ Rango</button>
            </div>
          </div>

          <!-- Tabla filas -->
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
                  v-for="(row, idx) in rows"
                  :key="row._key"
                  class="hover:bg-brand-50/40 transition"
                >
                  <!-- Piso -->
                  <td class="px-4 py-3 align-top">
                    <select
                      v-model="row.id_piso_torre"
                      class="form-input"
                      :disabled="pisosLocal.length === 0"
                    >
                      <option value="">Seleccione</option>
                      <option
                        v-for="p in pisosLocal"
                        :key="p.id_piso_torre"
                        :value="p.id_piso_torre"
                      >
                        Nivel {{ p.nivel }}
                      </option>
                    </select>
                    <p v-if="rowErrors[idx]?.id_piso_torre" class="form-error">
                      {{ rowErrors[idx].id_piso_torre }}
                    </p>
                  </td>

                  <!-- Tipo -->
                  <td class="px-4 py-3 align-top">
                    <select
                      v-model="row.id_tipo_apartamento"
                      class="form-input"
                      :disabled="!base.id_proyecto"
                    >
                      <option value="">Seleccione</option>
                      <option
                        v-for="t in tiposFiltrados"
                        :key="t.id_tipo_apartamento"
                        :value="t.id_tipo_apartamento"
                      >
                        {{ t.nombre }} — {{ formatCurrency(t.valor_estimado) }}
                      </option>
                    </select>
                    <p v-if="rowErrors[idx]?.id_tipo_apartamento" class="form-error">
                      {{ rowErrors[idx].id_tipo_apartamento }}
                    </p>
                  </td>

                  <!-- Estado -->
                  <td class="px-4 py-3 align-top">
                    <select v-model="row.id_estado_inmueble" class="form-input">
                      <option value="">Seleccione</option>
                      <option
                        v-for="e in estados"
                        :key="e.id_estado_inmueble"
                        :value="e.id_estado_inmueble"
                      >
                        {{ e.nombre }}
                      </option>
                    </select>
                    <p v-if="rowErrors[idx]?.id_estado_inmueble" class="form-error">
                      {{ rowErrors[idx].id_estado_inmueble }}
                    </p>
                  </td>

                  <!-- Número -->
                  <td class="px-4 py-3 align-top">
                    <input
                      v-model="row.numero"
                      type="text"
                      maxlength="20"
                      class="form-input"
                      placeholder="Ej: 302"
                    />
                    <p v-if="rowErrors[idx]?.numero" class="form-error">
                      {{ rowErrors[idx].numero }}
                    </p>
                  </td>

                  <!-- Valor total -->
                  <td class="px-4 py-3 align-top">
                    <div class="text-sm font-semibold text-gray-900">
                      {{ formatCurrency(valorTotalRow(row)) }}
                    </div>
                    <div class="text-xs text-gray-500 mt-1">
                      Tipo + política + prima altura (según piso)
                    </div>
                  </td>

                  <!-- Acción -->
                  <td class="px-4 py-3 align-top">
                    <div class="flex justify-end">
                      <button
                        type="button"
                        class="icon-danger"
                        @click="removeRow(idx)"
                        :disabled="rows.length === 1"
                      >
                        Quitar
                      </button>
                    </div>
                  </td>
                </tr>

                <tr v-if="rows.length === 0">
                  <td colspan="6" class="px-4 py-8 text-center text-sm text-gray-500">
                    Agrega al menos una fila.
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Acciones -->
          <div class="mt-6 flex items-center gap-3">
            <button
              type="button"
              class="btn-primary"
              :disabled="processing || !base.id_torre"
              @click="submit"
            >
              {{ processing ? 'Guardando…' : 'Guardar' }}
            </button>
            <Link href="/apartamentos" class="btn-secondary">Cancelar</Link>
          </div>

          <p v-if="errors.general" class="mt-3 text-sm text-red-600">{{ errors.general }}</p>
        </div>
      </AppCard>

      <!-- Modal rango (solo genera números; copia el resto vacío) -->
      <ConfirmDialog
        :open="rangeModal.open"
        title="Agregar rango de números"
        :message="''"
        cancel-text="Cancelar"
        confirm-text="Agregar"
        @cancel="rangeModal.open = false"
        @confirm="applyRange"
      >
        <template #content>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="form-label">Desde</label>
              <input
                v-model="rangeModal.desde"
                type="text"
                class="form-input"
                placeholder="Ej: 301"
              />
            </div>
            <div>
              <label class="form-label">Hasta</label>
              <input
                v-model="rangeModal.hasta"
                type="text"
                class="form-input"
                placeholder="Ej: 310"
              />
            </div>
            <p class="md:col-span-2 text-xs text-gray-500">
              Se agregan filas con el número. Piso/Tipo/Estado se dejan para seleccionar.
            </p>
            <p v-if="rangeModal.error" class="md:col-span-2 text-sm text-red-600">
              {{ rangeModal.error }}
            </p>
          </div>
        </template>
      </ConfirmDialog>
      <RangeModal
        :open="rangeOpen"
        title="Agregar rango de números"
        subtitle="Se crearán filas por cada número del rango. Piso/Tipo/Estado se dejan para seleccionar."
        @cancel="onRangeCancel"
        @confirm="onRangeConfirm"
      />
    </div>

    <FlashMessages />
  </SidebarBannerLayout>
</template>

<script setup>
import { ref, reactive, computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'

import SidebarBannerLayout from '@/Components/SidebarBannerLayout.vue'
import FlashMessages from '@/Components/FlashMessages.vue'
import AppCard from '@/Components/AppCard.vue'
import PageHeader from '@/Components/PageHeader.vue'
import ButtonPrimary from '@/Components/ButtonPrimary.vue'
import ConfirmDialog from '@/Components/ConfirmDialog.vue'
import RangeModal from '@/Components/RangeModal.vue'

const props = defineProps({
  proyectos: { type: Array, default: () => [] },
  tipos: { type: Array, default: () => [] },
  estados: { type: Array, default: () => [] },
  torres: { type: Array, default: () => [] }, // puede venir precargado
  empleado: { type: Object, default: null },
})

const base = reactive({
  id_proyecto: '',
  id_torre: '',
})

const torresLocal = ref(Array.isArray(props.torres) ? props.torres : [])
const pisosLocal = ref([]) // se carga por torre
const torreSeleccionada = computed(
  () => torresLocal.value.find((t) => Number(t.id_torre) === Number(base.id_torre)) || null
)

const rows = ref([newRow()])
const rowErrors = ref([])
const errors = ref({})
const processing = ref(false)

const rangeOpen = ref(false)

function onRangeCancel() {
  rangeOpen.value = false
}

/**
 * Espera que RangeModal emita @apply con:
 *  - payload.desde
 *  - payload.hasta
 *  - (opcional) payload.pad (si tu componente lo maneja)
 */
function onRangeConfirm(payload) {
  const desdeRaw = String(payload?.desde ?? '').trim()
  const hastaRaw = String(payload?.hasta ?? '').trim()

  const desdeNum = Number(desdeRaw)
  const hastaNum = Number(hastaRaw)

  if (
    !desdeRaw ||
    !hastaRaw ||
    !Number.isInteger(desdeNum) ||
    !Number.isInteger(hastaNum) ||
    hastaNum < desdeNum
  ) {
    // si tu RangeModal ya valida internamente, puedes eliminar este bloque
    errors.value.general = 'Rango inválido. Verifica Desde y Hasta.'
    return
  }

  const padLen = payload?.pad ? Number(payload.pad) : /^\d+$/.test(desdeRaw) ? desdeRaw.length : 0

  const nuevos = []
  for (let n = desdeNum; n <= hastaNum; n++) {
    const numero = padLen ? String(n).padStart(padLen, '0') : String(n)
    nuevos.push(newRow({ numero }))
  }

  if (rows.value.length === 1 && !String(rows.value[0].numero || '').trim()) {
    rows.value = nuevos
  } else {
    rows.value.push(...nuevos)
  }

  rangeOpen.value = false
}

function cryptoKey() {
  return `${Date.now()}_${Math.random().toString(16).slice(2)}`
}

function newRow(overrides = {}) {
  return {
    _key: cryptoKey(),
    id_piso_torre: '',
    id_tipo_apartamento: '',
    id_estado_inmueble: '',
    numero: '',
    ...overrides,
  }
}

function addRow() {
  rows.value.push(newRow())
}
function removeRow(idx) {
  if (rows.value.length <= 1) return
  rows.value.splice(idx, 1)
}

function formatCurrency(val) {
  const num = Number(val || 0)
  return new Intl.NumberFormat('es-CO', {
    style: 'currency',
    currency: 'COP',
    maximumFractionDigits: 0,
  }).format(num)
}

const tiposFiltrados = computed(() => props.tipos.filter((t) => t.id_proyecto === base.id_proyecto))

async function onProyectoChange() {
  base.id_torre = ''
  torresLocal.value = []
  pisosLocal.value = []
  rows.value = [newRow()]

  if (!base.id_proyecto) return

  try {
    const res = await fetch(`/api/torres-por-proyecto/${base.id_proyecto}`)
    if (!res.ok) throw new Error('Error cargando torres')
    torresLocal.value = await res.json()
  } catch (e) {
    console.error(e)
    errors.value.general = 'No se pudieron cargar las torres del proyecto.'
  }
}

async function onTorreChange() {
  pisosLocal.value = []
  rows.value = [newRow()] // reinicia filas al cambiar torre (evita incoherencias)
  if (!base.id_torre) return

  try {
    const res = await fetch(`/api/pisos-por-torre/${base.id_torre}`)
    if (!res.ok) throw new Error('Error cargando pisos')
    pisosLocal.value = await res.json()
  } catch (e) {
    console.error(e)
    errors.value.general = 'No se pudieron cargar los pisos de la torre.'
  }
}

/** Cálculos por fila: prima altura depende del piso; tipo depende del tipo. Política se calcula en backend.
 *  En UI se muestra referencia: valor_estimado + prima altura.
 */
function valorEstimadoTipoRow(row) {
  const t = props.tipos.find(
    (x) => Number(x.id_tipo_apartamento) === Number(row.id_tipo_apartamento)
  )
  return t ? Number(t.valor_estimado || 0) : 0
}

function primaAlturaRow(row) {
  const piso = pisosLocal.value.find((p) => Number(p.id_piso_torre) === Number(row.id_piso_torre))
  if (!piso) return 0

  const nivel = Number(piso.nivel || 0)
  const torre = torreSeleccionada.value
  if (!torre || !torre.proyecto) return 0

  const proyecto = torre.proyecto
  if (!proyecto.prima_altura_activa) return 0

  const nivelBase = Number(torre.nivel_inicio_prima ?? 2)
  if (nivel < nivelBase) return 0

  const baseVal = Number(proyecto.prima_altura_base || 0)
  const inc = Number(proyecto.prima_altura_incremento || 0)
  return baseVal + (nivel - nivelBase) * inc
}

function valorTotalRow(row) {
  return valorEstimadoTipoRow(row) + primaAlturaRow(row)
}

/** Modal rango */
const rangeModal = reactive({
  open: false,
  desde: '',
  hasta: '',
  error: '',
})
function openRange() {
  rangeModal.open = true
  rangeModal.desde = ''
  rangeModal.hasta = ''
  rangeModal.error = ''
}
function applyRange() {
  rangeModal.error = ''
  const desdeRaw = String(rangeModal.desde || '').trim()
  const hastaRaw = String(rangeModal.hasta || '').trim()
  if (!desdeRaw || !hastaRaw) {
    rangeModal.error = 'Debes indicar "Desde" y "Hasta".'
    return
  }

  const desdeNum = Number(desdeRaw)
  const hastaNum = Number(hastaRaw)

  if (
    !Number.isFinite(desdeNum) ||
    !Number.isFinite(hastaNum) ||
    !Number.isInteger(desdeNum) ||
    !Number.isInteger(hastaNum)
  ) {
    rangeModal.error = 'El rango debe ser numérico (enteros).'
    return
  }
  if (hastaNum < desdeNum) {
    rangeModal.error = '"Hasta" debe ser mayor o igual a "Desde".'
    return
  }

  const pad = /^\d+$/.test(desdeRaw) ? desdeRaw.length : 0
  const nuevos = []
  for (let n = desdeNum; n <= hastaNum; n++) {
    const numero = pad ? String(n).padStart(pad, '0') : String(n)
    nuevos.push(newRow({ numero }))
  }

  if (rows.value.length === 1 && !String(rows.value[0].numero || '').trim()) {
    rows.value = nuevos
  } else {
    rows.value.push(...nuevos)
  }

  rangeModal.open = false
}

function submit() {
  errors.value = {}
  rowErrors.value = []
  processing.value = true

  // Validación rápida front
  const perRow = rows.value.map(() => ({}))
  let hasErr = false

  if (!base.id_proyecto) {
    errors.value.id_proyecto = 'El proyecto es obligatorio'
    hasErr = true
  }
  if (!base.id_torre) {
    errors.value.id_torre = 'La torre es obligatoria'
    hasErr = true
  }

  rows.value.forEach((r, i) => {
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
    rowErrors.value = perRow
    processing.value = false
    return
  }

  router.post(
    '/apartamentos',
    {
      id_torre: base.id_torre,
      apartamentos: rows.value.map((r) => ({
        numero: String(r.numero || '').trim(),
        id_piso_torre: r.id_piso_torre,
        id_tipo_apartamento: r.id_tipo_apartamento,
        id_estado_inmueble: r.id_estado_inmueble,
      })),
    },
    {
      onError: (e) => {
        errors.value = e || {}
        processing.value = false

        // errores por índice: apartamentos.0.numero / id_piso_torre / id_tipo_apartamento / id_estado_inmueble
        const rowE = rows.value.map(() => ({}))
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
        rowErrors.value = rowE
      },
      onFinish: () => {
        processing.value = false
      },
    }
  )
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
.btn-primary {
  @apply inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-brand-600 text-white hover:bg-brand-700 disabled:opacity-50 transition;
}
.btn-secondary {
  @apply inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-gray-300 text-gray-800 hover:bg-gray-50 transition disabled:opacity-50;
}
.icon-danger {
  @apply inline-flex items-center justify-center rounded-xl border border-red-300 px-3 py-2 text-sm font-semibold text-red-600 hover:bg-red-50 transition disabled:opacity-50;
}
</style>
