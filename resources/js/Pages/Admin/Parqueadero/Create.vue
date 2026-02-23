<template>
  <TopBannerLayout :empleado="empleado">
    <div class="space-y-6">
      <PageHeader
        title="Crear Parqueaderos"
        kicker="Inventario"
        subtitle="Crea uno o varios parqueaderos por fila."
      >
      </PageHeader>

      <!-- Banner Flujo (7/8) -->
      <AppCard padding="md" v-if="flowProyectoId">
        <div class="flex flex-col gap-4">
          <div class="flex items-start justify-between gap-4">
            <div class="min-w-0">
              <p class="text-sm font-semibold text-gray-900">Flujo de configuración</p>
              <p class="mt-1 text-sm text-gray-700">
                Proyecto <span class="font-semibold">#{{ flowProyectoId }}</span> · Paso
                <span class="font-semibold">7/8</span> (Parqueaderos)
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
              :href="steps[5].href"
              class="rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-800 hover:bg-gray-50 transition"
            >
              Anterior: Locales
            </Link>

            <Link
              :href="steps[7].href"
              class="rounded-xl bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700 transition"
            >
              Siguiente: Zonas sociales
            </Link>
          </div>
        </div>
      </AppCard>

      <AppCard padding="md">
        <!-- Parámetros generales -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="form-label">Proyecto *</label>
            <select v-model="base.id_proyecto" @change="onProyectoChange" class="form-input">
              <option value="">Seleccione un proyecto</option>
              <option v-for="p in proyectos" :key="p.id_proyecto" :value="String(p.id_proyecto)">
                {{ p.nombre }}
              </option>
            </select>
            <p v-if="errors.id_proyecto" class="form-error">{{ errors.id_proyecto }}</p>
          </div>
          <div>
            <label class="form-label">Torre *</label>
            <select
              v-model="base.id_torre"
              :disabled="torresLocal.length === 0"
              @change="onTorreChange"
              class="form-input"
            >
              <option value="">Seleccione una torre</option>
              <option v-for="t in torresLocal" :key="t.id_torre" :value="String(t.id_torre)">
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
              <p class="text-sm font-semibold text-gray-900">Parqueaderos a crear</p>
              <p class="text-sm text-gray-600">
                Cada fila define Número, Tipo, Precio (opcional) y Apartamento (opcional).
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
              <button
                type="button"
                class="btn-secondary"
                @click="rangeOpen = true"
                :disabled="!base.id_torre"
              >
                + Rango
              </button>
            </div>
          </div>
          <!-- Tabla filas -->
          <div class="mt-4 overflow-x-auto">
            <table class="min-w-[1200px] w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">
                    Número *
                  </th>
                  <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">
                    Tipo *
                  </th>
                  <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">
                    Precio (opcional)
                  </th>
                  <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">
                    Apartamento (opcional)
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
                  <!-- Número -->
                  <td class="px-4 py-3 align-top">
                    <input
                      v-model="row.numero"
                      type="text"
                      maxlength="20"
                      class="form-input"
                      placeholder="Ej: P-101 o 101"
                      :disabled="!base.id_torre"
                    />
                    <p v-if="rowErrors[idx]?.numero" class="form-error">
                      {{ rowErrors[idx].numero }}
                    </p>
                  </td>
                  <!-- Tipo -->
                  <td class="px-4 py-3 align-top">
                    <select v-model="row.tipo" class="form-input" :disabled="!base.id_torre">
                      <option value="">Seleccione</option>
                      <option v-for="t in tipos" :key="t" :value="t">{{ t }}</option>
                    </select>
                    <p v-if="rowErrors[idx]?.tipo" class="form-error">{{ rowErrors[idx].tipo }}</p>
                  </td>
                  <!-- Precio -->
                  <td class="px-4 py-3 align-top">
                    <input
                      v-model="row.precio"
                      type="number"
                      min="0"
                      step="1"
                      class="form-input"
                      placeholder="Ej: 25000000"
                      :disabled="!base.id_torre"
                    />
                    <p v-if="rowErrors[idx]?.precio" class="form-error">
                      {{ rowErrors[idx].precio }}
                    </p>
                  </td>
                  <!-- Apartamento -->
                  <td class="px-4 py-3 align-top">
                    <select
                      v-model="row.id_apartamento"
                      class="form-input"
                      :disabled="!base.id_torre || apartamentosLocal.length === 0"
                    >
                      <option :value="''">Sin asignar</option>
                      <option
                        v-for="a in apartamentosLocal"
                        :key="a.id_apartamento"
                        :value="String(a.id_apartamento)"
                      >
                        {{ a.numero }} — {{ a.torre }}
                      </option>
                    </select>
                    <p class="text-xs text-gray-500 mt-1">Si no se asigna, quedará disponible.</p>
                    <p v-if="rowErrors[idx]?.id_apartamento" class="form-error">
                      {{ rowErrors[idx].id_apartamento }}
                    </p>
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
                  <td colspan="5" class="px-4 py-8 text-center text-sm text-gray-500">
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
              @click="saveAndNext_ZonasSociales"
              :disabled="processing || !canSubmit"
              class="btn-primary"
            >
              Guardar y continuar
            </button>
            <Link :href="route('parqueaderos.index')" class="btn-secondary">Cancelar</Link>
          </div>
          <p v-if="errors.general" class="mt-3 text-sm text-red-600">{{ errors.general }}</p>
        </div>
      </AppCard>
      <!-- Modal rango -->
      <RangeModal
        :open="rangeOpen"
        title="Agregar rango de números"
        subtitle="Se crearán filas por cada número del rango. Tipo/Precio/Apartamento quedan para seleccionar."
        @cancel="onRangeCancel"
        @confirm="onRangeConfirm"
      />
    </div>

    <FlashMessages />
  </TopBannerLayout>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'

import TopBannerLayout from '@/Components/TopBannerLayout.vue'
import FlashMessages from '@/Components/FlashMessages.vue'
import AppCard from '@/Components/AppCard.vue'
import PageHeader from '@/Components/PageHeader.vue'
import ButtonPrimary from '@/Components/ButtonPrimary.vue'
import RangeModal from '@/Components/RangeModal.vue'

import { ArrowLeftIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  proyectos: { type: Array, default: () => [] },
  torres: { type: Array, default: () => [] },
  tipos: { type: Array, default: () => ['Vehiculo', 'Moto'] },
  empleado: { type: Object, default: null },
})

const page = usePage()
const flowProyectoId = computed(() => {
  const url = page?.url || ''
  const qs = url.split('?')[1] || ''
  const sp = new URLSearchParams(qs)
  return sp.get('proyecto')
})

const activeStep = 'parqueaderos'
const steps = computed(() => {
  if (!flowProyectoId.value) return []
  const pid = flowProyectoId.value
  return [
    {
      n: '1/8',
      key: 'politicas',
      label: 'Políticas',
      href: `/politicas-precio-proyecto/crear?proyecto=${pid}`,
    },
    { n: '2/8', key: 'torres', label: 'Torres', href: `/admin/torres/create?proyecto=${pid}` },
    { n: '3/8', key: 'pisos', label: 'Pisos', href: `/pisos-torre/create?proyecto=${pid}` },
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
    { n: '6/8', key: 'locales', label: 'Locales', href: `/locales/create?proyecto=${pid}` },
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

/* ↓↓↓ Tu código original de parqueaderos (sin cambios de lógica) ↓↓↓ */
const base = reactive({ id_proyecto: '', id_torre: '' })
const torresLocal = ref(Array.isArray(props.torres) ? props.torres : [])
const apartamentosLocal = ref([])
const rows = ref([newRow()])
const rowErrors = ref([])
const errors = ref({})
const processing = ref(false)
const rangeOpen = ref(false)

const canSubmit = computed(() => {
  if (!base.id_proyecto || !base.id_torre) return false
  if (!rows.value.length) return false
  return rows.value.every((r) => String(r.numero || '').trim() && String(r.tipo || '').trim())
})

onMounted(async () => {
  if (flowProyectoId.value && !base.id_proyecto) {
    base.id_proyecto = String(flowProyectoId.value)
    await onProyectoChange()
  }
})

function onRangeCancel() {
  rangeOpen.value = false
}

function onRangeConfirm(payload) {
  errors.value.general = ''
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
    errors.value.general = 'Rango inválido. Verifica Desde y Hasta.'
    return
  }

  const padLen = payload?.pad ? Number(payload.pad) : /^\d+$/.test(desdeRaw) ? desdeRaw.length : 0
  const nuevos = []
  for (let n = desdeNum; n <= hastaNum; n++) {
    const numero = padLen ? String(n).padStart(padLen, '0') : String(n)
    nuevos.push(newRow({ numero }))
  }

  if (rows.value.length === 1 && !String(rows.value[0].numero || '').trim()) rows.value = nuevos
  else rows.value.push(...nuevos)

  rangeOpen.value = false
}

function cryptoKey() {
  return `${Date.now()}_${Math.random().toString(16).slice(2)}`
}

function newRow(overrides = {}) {
  return { _key: cryptoKey(), numero: '', tipo: '', precio: '', id_apartamento: '', ...overrides }
}

function addRow() {
  rows.value.push(newRow())
}
function removeRow(idx) {
  if (rows.value.length <= 1) return
  rows.value.splice(idx, 1)
}

async function onProyectoChange() {
  base.id_torre = ''
  torresLocal.value = []
  apartamentosLocal.value = []
  rows.value = [newRow()]
  rowErrors.value = []
  errors.value = {}

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
  apartamentosLocal.value = []
  rows.value = [newRow()]
  rowErrors.value = []
  errors.value.general = ''

  if (!base.id_torre) return

  try {
    const res = await fetch(`/api/apartamentos-por-torre/${base.id_torre}`)
    if (!res.ok) throw new Error('Error cargando apartamentos')
    apartamentosLocal.value = await res.json()
  } catch (e) {
    console.error(e)
    errors.value.general = 'No se pudieron cargar los apartamentos de la torre.'
  }
}

function saveAndNext_ZonasSociales() {
  router.post(
    route('parqueaderos.store'),
    {
      id_torre: base.id_torre,
      parqueaderos: rows.value.map((r) => ({
        numero: String(r.numero || '').trim(),
        tipo: r.tipo,
        precio: String(r.precio || '').trim() === '' ? null : Number(r.precio),
        id_apartamento: r.id_apartamento ? Number(r.id_apartamento) : null,
      })),
    },
    {
      preserveScroll: true,
      onSuccess: () => router.visit(`/zonas-sociales/create?proyecto=${base.id_proyecto}`),
      onError: (e) => {
        errors.value = e || {}
        const rowE = rows.value.map(() => ({}))
        for (const [k, v] of Object.entries(e || {})) {
          const m = k.match(/^parqueaderos\.(\d+)\.(numero|tipo|precio|id_apartamento)$/)
          if (m) {
            const idx = Number(m[1])
            const field = m[2]
            if (!Number.isNaN(idx) && rowE[idx]) rowE[idx][field] = Array.isArray(v) ? v[0] : v
          }
        }
        rowErrors.value = rowE
      },
      onFinish: () => (processing.value = false),
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
