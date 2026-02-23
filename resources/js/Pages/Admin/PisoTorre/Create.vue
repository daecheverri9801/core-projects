<!-- resources/js/Pages/Admin/PisoTorre/Create.vue -->
<template>
  <TopBannerLayout :empleado="empleado">
    <div class="space-y-6">
      <PageHeader
        title="Crear Pisos"
        kicker="Carga masiva"
        subtitle="Crea varios pisos para una torre en una sola operación."
      >
      </PageHeader>

      <!-- Banner Flujo (3/8) -->
      <AppCard padding="md" v-if="flowProyectoId">
        <div class="flex flex-col gap-4">
          <div class="flex items-start justify-between gap-4">
            <div class="min-w-0">
              <p class="text-sm font-semibold text-gray-900">Flujo de configuración</p>
              <p class="mt-1 text-sm text-gray-700">
                Proyecto <span class="font-semibold">#{{ flowProyectoId }}</span> · Paso
                <span class="font-semibold">3/8</span> (Pisos)
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
              :href="steps[1].href"
              class="rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-800 hover:bg-gray-50 transition"
            >
              Anterior: Torres
            </Link>

            <Link
              :href="steps[3].href"
              class="rounded-xl bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700 transition"
            >
              Siguiente: Tipos apto
            </Link>
          </div>
        </div>
      </AppCard>

      <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <div class="lg:col-span-8 space-y-6">
          <AppCard padding="md">
            <SectionHeader
              title="Configuración"
              subtitle="Selecciona proyecto y torre. Define un uso por defecto si lo necesitas."
              icon="Squares2X2Icon"
            />

            <div class="mt-5 grid grid-cols-1 md:grid-cols-3 gap-4">
              <FormField label="Proyecto" required :error="form.errors.id_proyecto">
                <SelectInput v-model="form.id_proyecto" @change="loadTorres">
                  <option value="" disabled>Seleccione un proyecto</option>
                  <option v-for="p in proyectos" :key="p.id_proyecto" :value="p.id_proyecto">
                    {{ p.nombre }}
                  </option>
                </SelectInput>
              </FormField>

              <FormField label="Torre" required :error="form.errors.id_torre">
                <SelectInput v-model="form.id_torre" :disabled="!torres.length">
                  <option value="" disabled>Seleccione una torre</option>
                  <option v-for="t in torres" :key="t.id_torre" :value="t.id_torre">
                    {{ t.nombre_torre }}
                  </option>
                </SelectInput>
              </FormField>

              <FormField
                label="Uso (por defecto)"
                :error="null"
                hint="Se aplicará a filas sin uso definido"
              >
                <TextInput
                  v-model="defaults.uso"
                  type="text"
                  maxlength="40"
                  placeholder="Opcional (ej: Residencial)"
                />
              </FormField>
            </div>
          </AppCard>

          <AppCard padding="md">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
              <div class="min-w-0">
                <p class="text-sm font-semibold text-gray-900">Pisos a crear</p>
                <p class="text-sm text-gray-600 mt-1">
                  Agrega filas o rangos. Registra <b>Nivel</b> y <b>Uso</b>.
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
                      <TextInput
                        v-model.number="row.nivel"
                        type="number"
                        placeholder="Ej: 1"
                        @blur="row.nivel = normalizeInt(row.nivel)"
                      />
                      <p v-if="rowErrors(idx, 'nivel')" class="text-sm text-red-600 mt-1">
                        {{ rowErrors(idx, 'nivel') }}
                      </p>
                    </td>

                    <td class="px-6 py-4">
                      <TextInput
                        v-model="row.uso"
                        type="text"
                        maxlength="40"
                        placeholder="Opcional"
                      />
                      <p v-if="rowErrors(idx, 'uso')" class="text-sm text-red-600 mt-1">
                        {{ rowErrors(idx, 'uso') }}
                      </p>
                    </td>

                    <td class="px-6 py-4">
                      <div class="flex items-center justify-end gap-2">
                        <button
                          type="button"
                          class="rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm font-semibold text-gray-800 hover:bg-gray-50 transition"
                          @click="duplicateRow(idx)"
                        >
                          Duplicar
                        </button>
                        <button
                          type="button"
                          class="rounded-xl border border-red-300 bg-white px-3 py-2 text-sm font-semibold text-red-700 hover:bg-red-50 transition"
                          @click="removeRow(idx)"
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

            <div
              v-if="localValidationError"
              class="mt-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800"
            >
              {{ localValidationError }}
            </div>

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
                  @click="saveAndNext_TiposApto"
                  :disabled="form.processing"
                  class="rounded-xl bg-brand-600 px-5 py-2 text-sm font-semibold text-white hover:bg-brand-700 transition disabled:opacity-50 inline-flex items-center gap-2"
                >
                  <CheckIcon class="w-5 h-5" />
                  Guardar y continuar
                </button>
              </div>
            </div>

            <FlashMessages />
          </AppCard>
        </div>

        <div class="lg:col-span-4 space-y-6">
          <AppCard padding="md">
            <div class="flex items-start gap-3">
              <span class="mt-0.5 rounded-2xl border border-brand-300/60 bg-brand-200 p-2">
                <InformationCircleIcon class="w-5 h-5 text-brand-900" />
              </span>
              <div class="min-w-0">
                <p class="font-semibold text-gray-900">Tips</p>
                <ul class="mt-2 space-y-2 text-sm text-gray-700 list-disc pl-5">
                  <li>Selecciona proyecto y torre antes de cargar niveles.</li>
                  <li>Evita duplicar niveles dentro de la misma carga.</li>
                  <li>Usa “Rango” para crear niveles consecutivos rápidamente.</li>
                </ul>
              </div>
            </div>
          </AppCard>

          <AppCard padding="md">
            <p class="text-sm font-semibold text-gray-900">Resumen</p>
            <div class="mt-3 space-y-2 text-sm">
              <InlineStatus :ok="!!form.id_proyecto" label="Proyecto seleccionado" />
              <InlineStatus :ok="!!form.id_torre" label="Torre seleccionada" />
              <InlineStatus :ok="rows.length > 0" :label="`Filas: ${rows.length}`" />
            </div>
          </AppCard>
        </div>
      </div>

      <RangeModal
        :open="rangeModal.open"
        title="Agregar rango de niveles"
        :desde="rangeModal.desde"
        :hasta="rangeModal.hasta"
        @cancel="rangeModal.open = false"
        @confirm="applyRange"
      />
    </div>
  </TopBannerLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { Link, useForm, usePage } from '@inertiajs/vue3'

import TopBannerLayout from '@/Components/TopBannerLayout.vue'
import AppCard from '@/Components/AppCard.vue'
import PageHeader from '@/Components/PageHeader.vue'
import SectionHeader from '@/Components/SectionHeader.vue'
import FormField from '@/Components/FormField.vue'
import TextInput from '@/Components/TextInput.vue'
import SelectInput from '@/Components/SelectInput.vue'
import InlineStatus from '@/Components/InlineStatus.vue'
import RangeModal from '@/Components/RangeModal.vue'
import FlashMessages from '@/Components/FlashMessages.vue'

import {
  InformationCircleIcon,
  Squares2X2Icon,
  ArrowLeftIcon,
  CheckIcon,
} from '@heroicons/vue/24/outline'

const props = defineProps({
  proyectos: { type: Array, default: () => [] },
  empleado: { type: Object, default: null },
})

const page = usePage()
const flowProyectoId = computed(() => {
  const url = page?.url || ''
  const qs = url.split('?')[1] || ''
  const sp = new URLSearchParams(qs)
  return sp.get('proyecto')
})

const activeStep = 'pisos'
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

const form = useForm({
  id_proyecto: '',
  id_torre: '',
  pisos: [],
})

const defaults = ref({ uso: '' })
const torres = ref([])
const rows = ref([{ _key: crypto.randomUUID?.() ?? String(Date.now()), nivel: '', uso: '' }])
const localValidationError = ref('')
const rangeModal = ref({ open: false, desde: 1, hasta: 1 })

onMounted(() => {
  if (flowProyectoId.value && !form.id_proyecto) {
    form.id_proyecto = String(flowProyectoId.value)
    loadTorres()
  }
})

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

function rowErrors(idx, field) {
  const key = `pisos.${idx}.${field}`
  return form.errors?.[key]
}

function validateLocal() {
  localValidationError.value = ''
  if (!form.id_proyecto) return (localValidationError.value = 'Selecciona un proyecto.') && false
  if (!form.id_torre) return (localValidationError.value = 'Selecciona una torre.') && false
  if (!rows.value.length) return (localValidationError.value = 'Agrega al menos un piso.') && false

  const niveles = rows.value.map((r) => normalizeInt(r.nivel)).filter((n) => n !== '')
  if (niveles.length !== rows.value.length)
    return (localValidationError.value = 'Todos los niveles deben ser numéricos.') && false
  if (niveles.some((n) => n < 1))
    return (localValidationError.value = 'El nivel mínimo es 1.') && false

  const set = new Set()
  for (const n of niveles) {
    if (set.has(n))
      return (localValidationError.value = `Nivel duplicado en la carga: ${n}.`) && false
    set.add(n)
  }
  return true
}

function saveAndNext_TiposApto() {
  if (!validateLocal()) return

  const payloadPisos = rows.value.map((r) => ({
    nivel: normalizeInt(r.nivel),
    uso: (r.uso || defaults.uso || '').trim() || null,
  }))

  form
    .transform(() => ({ id_torre: form.id_torre, pisos: payloadPisos, stay: 1 }))
    .post(route('pisostorre.store'), {
      preserveScroll: true,
      preserveState: true,
      onSuccess: () => router.visit(`/admin/tipos-apartamento/create?proyecto=${form.id_proyecto}`),
    })
}
</script>
