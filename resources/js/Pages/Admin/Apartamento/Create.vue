<template>
  <TopBannerLayout :empleado="empleado">
    <div class="space-y-6">
      <PageHeader
        title="Crear apartamentos"
        kicker="Inventario"
        subtitle="Crea uno o varios apartamentos por filas."
      >
      </PageHeader>

      <!-- Banner Flujo (5/8) -->
      <AppCard padding="md" v-if="flowProyectoId">
        <div class="flex flex-col gap-4">
          <div class="flex items-start justify-between gap-4">
            <div class="min-w-0">
              <p class="text-sm font-semibold text-gray-900">Flujo de configuración</p>
              <p class="mt-1 text-sm text-gray-700">
                Proyecto <span class="font-semibold">#{{ flowProyectoId }}</span> · Paso
                <span class="font-semibold">5/8</span> (Apartamentos)
              </p>
            </div>

            <Link
              :href="`/proyectos/${flowProyectoId}`"
              class="shrink-0 rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-800 hover:bg-gray-50 transition"
            >
              Volver al proyecto
            </Link>
          </div>

          <div class="overflow-x-auto">
            <ol class="min-w-[900px] grid grid-cols-8 gap-2">
              <li v-for="s in steps" :key="s.key">
                <Link
                  :href="s.href"
                  class="block rounded-xl border px-3 py-2 text-xs font-semibold transition"
                  :class="
                    s.key === activeStep
                      ? 'border-brand-400 bg-brand-50 text-brand-900'
                      : 'border-gray-200 bg-white text-gray-700 hover:bg-gray-50'
                  "
                >
                  <div class="flex items-center justify-between gap-2">
                    <span class="truncate">{{ s.label }}</span>
                    <span class="text-[10px] opacity-70">{{ s.n }}</span>
                  </div>
                </Link>
              </li>
            </ol>
          </div>

          <div class="flex items-center justify-between gap-2">
            <Link
              :href="steps[3].href"
              class="rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-800 hover:bg-gray-50 transition"
            >
              Anterior: Tipos apto
            </Link>

            <Link
              :href="steps[5].href"
              class="rounded-xl bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700 transition"
            >
              Siguiente: Locales
            </Link>
          </div>
        </div>
      </AppCard>

      <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Form -->
        <div class="lg:col-span-8 space-y-6">
          <!-- Config general -->
          <AppCard padding="md">
            <SectionHeader
              title="Parámetros generales"
              subtitle="Selecciona proyecto y torre para cargar pisos y tipos."
              icon="BuildingOffice2Icon"
            />
            <div class="mt-5 grid grid-cols-1 md:grid-cols-2 gap-4">
              <FormField label="Proyecto" required :error="errors.id_proyecto">
                <SelectInput v-model="base.id_proyecto" @change="onProyectoChange">
                  <option value="" disabled>Seleccione un proyecto</option>
                  <option
                    v-for="p in proyectos"
                    :key="p.id_proyecto"
                    :value="String(p.id_proyecto)"
                  >
                    {{ p.nombre }}
                  </option>
                </SelectInput>
              </FormField>
              <FormField label="Torre" required :error="errors.id_torre">
                <SelectInput
                  v-model="base.id_torre"
                  :disabled="torresLocal.length === 0"
                  @change="onTorreChange"
                >
                  <option value="" disabled>Seleccione una torre</option>
                  <option v-for="t in torresLocal" :key="t.id_torre" :value="String(t.id_torre)">
                    {{ t.nombre_torre }}
                  </option>
                </SelectInput>
              </FormField>
            </div>
          </AppCard>
          <!-- Repeater -->
          <AppCard padding="md">
            <div class="flex items-start justify-between gap-3">
              <div class="min-w-0">
                <p class="text-sm font-semibold text-gray-900">Apartamentos a crear</p>
                <p class="mt-1 text-sm text-gray-600">
                  Cada fila define Piso, Tipo, Estado y Número.
                </p>
              </div>
              <div class="flex items-center gap-2">
                <button
                  type="button"
                  class="rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm font-semibold text-gray-800 hover:bg-gray-50 transition inline-flex items-center gap-2 disabled:opacity-60"
                  @click="addRow"
                  :disabled="!base.id_torre"
                >
                  <PlusIcon class="w-5 h-5" /> Agregar fila
                </button>
                <button
                  type="button"
                  class="rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm font-semibold text-gray-800 hover:bg-gray-50 transition inline-flex items-center gap-2 disabled:opacity-60"
                  @click="rangeOpen = true"
                  :disabled="!base.id_torre"
                >
                  <SquaresPlusIcon class="w-5 h-5" /> Rango
                </button>
              </div>
            </div>
            <div class="mt-5 overflow-x-auto">
              <table class="min-w-[1100px] w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">
                      Piso *
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">
                      Tipo *
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">
                      Estado *
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">
                      Número *
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">
                      Preview valor
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
                      <SelectInput v-model="row.id_piso_torre" :disabled="pisosLocal.length === 0">
                        <option value="">Seleccione</option>
                        <option
                          v-for="p in pisosLocal"
                          :key="p.id_piso_torre"
                          :value="String(p.id_piso_torre)"
                        >
                          Nivel {{ p.nivel }}
                        </option>
                      </SelectInput>
                      <p v-if="rowErrors[idx]?.id_piso_torre" class="form-error">
                        {{ rowErrors[idx].id_piso_torre }}
                      </p>
                    </td>
                    <!-- Tipo -->
                    <td class="px-4 py-3 align-top">
                      <SelectInput v-model="row.id_tipo_apartamento" :disabled="!base.id_proyecto">
                        <option value="">Seleccione</option>
                        <option
                          v-for="t in tiposFiltrados"
                          :key="t.id_tipo_apartamento"
                          :value="String(t.id_tipo_apartamento)"
                        >
                          {{ t.nombre }} — {{ formatCurrency(t.valor_estimado) }}
                        </option>
                      </SelectInput>
                      <p v-if="rowErrors[idx]?.id_tipo_apartamento" class="form-error">
                        {{ rowErrors[idx].id_tipo_apartamento }}
                      </p>
                    </td>
                    <!-- Estado -->
                    <td class="px-4 py-3 align-top">
                      <SelectInput v-model="row.id_estado_inmueble">
                        <option value="">Seleccione</option>
                        <option
                          v-for="e in estados"
                          :key="e.id_estado_inmueble"
                          :value="String(e.id_estado_inmueble)"
                        >
                          {{ e.nombre }}
                        </option>
                      </SelectInput>
                      <p v-if="rowErrors[idx]?.id_estado_inmueble" class="form-error">
                        {{ rowErrors[idx].id_estado_inmueble }}
                      </p>
                    </td>
                    <!-- Número -->
                    <td class="px-4 py-3 align-top">
                      <TextInput
                        v-model="row.numero"
                        type="text"
                        maxlength="20"
                        placeholder="Ej: 302"
                      />
                      <p v-if="rowErrors[idx]?.numero" class="form-error">
                        {{ rowErrors[idx].numero }}
                      </p>
                    </td>
                    <!-- Preview valor -->
                    <td class="px-4 py-3 align-top">
                      <div class="text-sm font-semibold text-gray-900">
                        {{ formatCurrency(previewTotal(row)) }}
                      </div>
                      <div class="text-xs text-gray-500 mt-1">Valor Base + Prima Altura</div>
                    </td>
                    <!-- Acción -->
                    <td class="px-4 py-3 align-top">
                      <div class="flex justify-end">
                        <button
                          type="button"
                          class="rounded-xl border border-red-300 bg-white px-3 py-2 text-sm font-semibold text-red-700 hover:bg-red-50 transition disabled:opacity-50"
                          @click="removeRow(idx)"
                          :disabled="rows.length === 1"
                        >
                          Quitar
                        </button>
                      </div>
                    </td>
                  </tr>
                  <tr v-if="rows.length === 0">
                    <td colspan="6" class="px-4 py-10 text-center text-sm text-gray-600">
                      Agrega al menos una fila.
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
            <div class="mt-6 flex items-center justify-between gap-3">
              <div class="text-sm text-gray-600">
                Filas: <span class="font-semibold text-gray-900">{{ rows.length }}</span>
              </div>
              <div class="flex items-center gap-3">
                <Link
                  :href="route('apartamentos.index')"
                  class="rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-800 hover:bg-gray-50 transition"
                >
                  Cancelar
                </Link>
                <button
                  type="button"
                  class="rounded-xl bg-brand-600 px-5 py-2 text-sm font-semibold text-white hover:bg-brand-700 transition disabled:opacity-60 inline-flex items-center gap-2"
                  @click="saveAndNext_Locales"
                  :disabled="processing || !canSubmit"
                >
                  <CheckIcon class="w-5 h-5" />
                  Guardar y continuar
                </button>
              </div>
            </div>
            <p v-if="errors.general" class="mt-3 text-sm text-red-600">{{ errors.general }}</p>
          </AppCard>
          <!-- Mobile submit -->
          <div class="lg:hidden">
            <button
              type="button"
              @click="saveAndNext_Locales"
              :disabled="processing || !canSubmit"
              class="w-full rounded-xl bg-brand-600 px-4 py-3 text-sm font-semibold text-white hover:bg-brand-700 transition disabled:opacity-60 inline-flex items-center justify-center gap-2"
            >
              <CheckIcon class="w-5 h-5" /> {{ processing ? 'Guardando…' : 'Guardar' }}
            </button>
          </div>
        </div>
        <!-- Aside -->
        <div class="lg:col-span-4 space-y-6">
          <AppCard padding="md">
            <div class="flex items-start gap-3">
              <span class="mt-0.5 rounded-2xl border border-brand-300/60 bg-brand-200 p-2">
                <InformationCircleIcon class="w-5 h-5 text-brand-900" />
              </span>
              <div class="min-w-0">
                <p class="font-semibold text-gray-900">Validación rápida</p>
                <div class="mt-2 space-y-2 text-sm">
                  <InlineStatus :ok="!!base.id_proyecto" label="Proyecto seleccionado" />
                  <InlineStatus :ok="!!base.id_torre" label="Torre seleccionada" />
                  <InlineStatus
                    :ok="pisosLocal.length > 0"
                    :label="`Pisos cargados: ${pisosLocal.length}`"
                  />
                  <InlineStatus
                    :ok="tiposFiltrados.length > 0"
                    :label="`Tipos disponibles: ${tiposFiltrados.length}`"
                  />
                  <InlineStatus :ok="canSubmit" label="Formulario listo" />
                </div>
              </div>
            </div>
          </AppCard>
          <AppCard padding="md">
            <p class="text-sm font-semibold text-gray-900">Notas</p>
            <ul class="mt-2 space-y-2 text-sm text-gray-700 list-disc pl-5">
              <li>El número debe ser único dentro de la torre.</li>
              <li>El piso debe pertenecer a la torre seleccionada.</li>
            </ul>
          </AppCard>
        </div>
      </div>
      <FlashMessages />
      <!-- Modal rango -->
      <RangeModal
        :open="rangeOpen"
        title="Agregar rango de números"
        :desde="1"
        :hasta="1"
        @cancel="rangeOpen = false"
        @confirm="onRangeConfirm"
      />
    </div>
  </TopBannerLayout>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue'

import { Link, router, usePage } from '@inertiajs/vue3'

import TopBannerLayout from '@/Components/TopBannerLayout.vue'
import FlashMessages from '@/Components/FlashMessages.vue'
import AppCard from '@/Components/AppCard.vue'
import PageHeader from '@/Components/PageHeader.vue'
import SectionHeader from '@/Components/SectionHeader.vue'
import FormField from '@/Components/FormField.vue'
import TextInput from '@/Components/TextInput.vue'
import SelectInput from '@/Components/SelectInput.vue'
import InlineStatus from '@/Components/InlineStatus.vue'
import RangeModal from '@/Components/RangeModal.vue'

import {
  PlusIcon,
  InformationCircleIcon,
  BuildingOffice2Icon,
  CheckIcon,
  SquaresPlusIcon,
} from '@heroicons/vue/24/outline'

const props = defineProps({
  proyectos: {
    type: Array,
    default: () => [],
  },

  tipos: {
    type: Array,
    default: () => [],
  },

  estados: {
    type: Array,
    default: () => [],
  },

  torres: {
    type: Array,
    default: () => [],
  },

  pisos: {
    type: Array,
    default: () => [],
  },

  empleado: {
    type: Object,
    default: null,
  },
})

const page = usePage()

const flowProyectoId = computed(() => {
  const url = page?.url || ''

  const qs = url.split('?')[1] || ''

  const sp = new URLSearchParams(qs)

  return sp.get('proyecto')
})

const activeStep = 'apartamentos'

const steps = computed(() => {
  if (!flowProyectoId.value) {
    return []
  }

  const pid = flowProyectoId.value

  return [
    {
      n: '1/8',
      key: 'politicas',
      label: 'Políticas',
      href: `/politicas-precio-proyecto/crear?proyecto=${pid}`,
    },
    {
      n: '2/8',
      key: 'torres',
      label: 'Torres',
      href: `/admin/torres/create?proyecto=${pid}`,
    },
    {
      n: '3/8',
      key: 'pisos',
      label: 'Pisos',
      href: `/pisos-torre/create?proyecto=${pid}`,
    },
    {
      n: '4/8',
      key: 'tipos',
      label: 'Tipos apto',
      href: `/tipos-apartamento/create?proyecto=${pid}`,
    },
    {
      n: '5/8',
      key: 'apartamentos',
      label: 'Apartamentos',
      href: `/admin/apartamentos/create?proyecto=${pid}`,
    },
    {
      n: '6/8',
      key: 'locales',
      label: 'Locales',
      href: `/locales/create?proyecto=${pid}`,
    },
    {
      n: '7/8',
      key: 'parqueaderos',
      label: 'Parqueaderos',
      href: `/parqueaderos/create?proyecto=${pid}`,
    },
    {
      n: '8/8',
      key: 'zonas',
      label: 'Zonas sociales',
      href: `/zonas-sociales/create?proyecto=${pid}`,
    },
  ]
})

const base = reactive({
  id_proyecto: '',
  id_torre: '',
})

const torresLocal = ref(Array.isArray(props.torres) ? props.torres : [])

const pisosLocal = ref(Array.isArray(props.pisos) ? props.pisos : [])

const rows = ref([newRow()])

const rowErrors = ref([])

const errors = ref({})

const processing = ref(false)

const rangeOpen = ref(false)

/* ============================================================
 * Helpers filas
 * ============================================================ */

function key() {
  return crypto.randomUUID
    ? crypto.randomUUID()
    : `${Date.now()}_${Math.random().toString(16).slice(2)}`
}

function newRow(overrides = {}) {
  return {
    _key: key(),

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
  if (rows.value.length <= 1) {
    return
  }

  rows.value.splice(idx, 1)
}

/* ============================================================
 * Selecciones
 * ============================================================ */

const torreSeleccionada = computed(() => {
  return torresLocal.value.find((torre) => Number(torre.id_torre) === Number(base.id_torre)) || null
})

const tiposFiltrados = computed(() => {
  return props.tipos.filter((tipo) => Number(tipo.id_proyecto) === Number(base.id_proyecto))
})

const canSubmit = computed(() => {
  if (!base.id_proyecto || !base.id_torre) {
    return false
  }

  if (!rows.value.length) {
    return false
  }

  return rows.value.every(
    (row) =>
      String(row.numero || '').trim().length > 0 &&
      !!row.id_piso_torre &&
      !!row.id_tipo_apartamento &&
      !!row.id_estado_inmueble
  )
})

/* ============================================================
 * Inicialización
 * ============================================================ */

onMounted(async () => {
  if (!flowProyectoId.value) {
    return
  }

  base.id_proyecto = String(flowProyectoId.value)

  await cargarTorres()

  /*
   * Mantiene comportamiento actual:
   * selecciona primera torre en flujo.
   */
  if (torresLocal.value.length) {
    base.id_torre = String(torresLocal.value[0].id_torre)

    await cargarPisos()
  }
})

/* ============================================================
 * Proyecto
 * ============================================================ */

async function onProyectoChange() {
  base.id_torre = ''

  torresLocal.value = []
  pisosLocal.value = []

  rows.value = [newRow()]

  errors.value = {}
  rowErrors.value = []

  if (!base.id_proyecto) {
    return
  }

  await cargarTorres()
}

async function cargarTorres() {
  try {
    const res = await fetch(`/torres-por-proyecto/${base.id_proyecto}`, {
      headers: {
        Accept: 'application/json',
      },
    })

    if (!res.ok) {
      throw new Error('Error cargando torres')
    }

    torresLocal.value = await res.json()
  } catch (error) {
    console.error(error)

    errors.value.general = 'No se pudieron cargar las torres del proyecto.'
  }
}

/* ============================================================
 * Torre
 * ============================================================ */

async function onTorreChange() {
  pisosLocal.value = []

  rows.value = [newRow()]

  errors.value = {}
  rowErrors.value = []

  if (!base.id_torre) {
    return
  }

  await cargarPisos()
}

async function cargarPisos() {
  try {
    const res = await fetch(`/api/pisos-por-torre/${base.id_torre}`, {
      headers: {
        Accept: 'application/json',
      },
    })

    if (!res.ok) {
      throw new Error('Error cargando pisos')
    }

    pisosLocal.value = await res.json()
  } catch (error) {
    console.error(error)

    errors.value.general = 'No se pudieron cargar los pisos de la torre.'
  }
}

/* ============================================================
 * Tipo seleccionado
 * ============================================================ */

function tipoRow(row) {
  return (
    props.tipos.find(
      (tipo) => Number(tipo.id_tipo_apartamento) === Number(row.id_tipo_apartamento)
    ) || null
  )
}

function pisoRow(row) {
  return (
    pisosLocal.value.find((piso) => Number(piso.id_piso_torre) === Number(row.id_piso_torre)) ||
    null
  )
}

/* ============================================================
 * Valor base
 * ============================================================ */

function valorEstimadoTipoRow(row) {
  const tipo = tipoRow(row)

  return tipo ? Number(tipo.valor_estimado || 0) : 0
}

/* ============================================================
 * Prima altura PREVIEW
 * ============================================================ */

function primaAlturaRow(row) {
  const tipo = tipoRow(row)

  const piso = pisoRow(row)

  if (!tipo || !piso) {
    return 0
  }

  const nivelActual = Number(piso.nivel || 0)

  /*
   * ========================================================
   * NUEVA CONFIGURACIÓN
   * ========================================================
   *
   * null = tipo legacy.
   */
  if (tipo.prima_altura_activa !== null && typeof tipo.prima_altura_activa !== 'undefined') {
    const activa =
      tipo.prima_altura_activa === true ||
      tipo.prima_altura_activa === 1 ||
      tipo.prima_altura_activa === '1'

    if (!activa) {
      return 0
    }

    const nivelInicio = Number(tipo.nivel_inicio_prima || 0)

    if (nivelInicio <= 0 || nivelActual < nivelInicio) {
      return 0
    }

    const basePrima = Number(tipo.prima_altura_base || 0)

    const incremento = Number(tipo.prima_altura_incremento || 0)

    return basePrima + (nivelActual - nivelInicio) * incremento
  }

  /*
   * ========================================================
   * FALLBACK LEGACY
   * ========================================================
   */
  const torre = torreSeleccionada.value

  if (!torre || !torre.proyecto) {
    return 0
  }

  const proyecto = torre.proyecto

  if (!proyecto.prima_altura_activa) {
    return 0
  }

  const nivelInicio = Number(torre.nivel_inicio_prima ?? 2)

  if (nivelActual < nivelInicio) {
    return 0
  }

  const basePrima = Number(proyecto.prima_altura_base || 0)

  const incremento = Number(proyecto.prima_altura_incremento || 0)

  return basePrima + (nivelActual - nivelInicio) * incremento
}

/* ============================================================
 * Preview
 * ============================================================ */

function previewTotal(row) {
  return valorEstimadoTipoRow(row) + primaAlturaRow(row)
}

/* ============================================================
 * Rango
 * ============================================================ */

function onRangeConfirm({ desde, hasta }) {
  const nuevos = []

  for (let numero = Number(desde); numero <= Number(hasta); numero++) {
    nuevos.push(
      newRow({
        numero: String(numero),
      })
    )
  }

  if (rows.value.length === 1 && !String(rows.value[0].numero || '').trim()) {
    rows.value = nuevos
  } else {
    rows.value.push(...nuevos)
  }

  rangeOpen.value = false
}

/* ============================================================
 * Guardar
 * ============================================================ */

function saveAndNext_Locales() {
  processing.value = true

  errors.value = {}
  rowErrors.value = []

  router.post(
    route('apartamentos.store'),
    {
      id_torre: base.id_torre,

      apartamentos: rows.value.map((row) => ({
        numero: String(row.numero || '').trim(),

        id_piso_torre: row.id_piso_torre,

        id_tipo_apartamento: row.id_tipo_apartamento,

        id_estado_inmueble: row.id_estado_inmueble,
      })),
    },
    {
      preserveScroll: true,

      onSuccess: () => {
        router.visit(`/locales/create?proyecto=${base.id_proyecto}`)
      },

      onError: (responseErrors) => {
        errors.value = responseErrors || {}

        const rowE = rows.value.map(() => ({}))

        for (const [key, value] of Object.entries(responseErrors || {})) {
          const match = key.match(
            /^apartamentos\.(\d+)\.(numero|id_piso_torre|id_tipo_apartamento|id_estado_inmueble)$/
          )

          if (!match) {
            continue
          }

          const index = Number(match[1])

          const field = match[2]

          if (!Number.isNaN(index) && rowE[index]) {
            rowE[index][field] = Array.isArray(value) ? value[0] : value
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

/* ============================================================
 * Formato
 * ============================================================ */

function formatCurrency(value) {
  return new Intl.NumberFormat('es-CO', {
    style: 'currency',
    currency: 'COP',
    maximumFractionDigits: 0,
  }).format(Number(value || 0))
}
</script>
