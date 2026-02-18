<!-- resources/js/Pages/Admin/TipoApartamento/Create.vue -->
<template>
  <TopBannerLayout :empleado="empleado" panel-name="Proyectos">
    <div class="space-y-6">
      <PageHeader
        title="Crear tipos de apartamento"
        kicker="Tipos de apartamento"
        subtitle="Crea uno o varios tipos en una sola operación. Puedes asociar imagen por tipo."
      >
      </PageHeader>

      <!-- Banner Flujo (4/8) -->
      <AppCard padding="md" v-if="flowProyectoId">
        <div class="flex flex-col gap-4">
          <div class="flex items-start justify-between gap-4">
            <div class="min-w-0">
              <p class="text-sm font-semibold text-gray-900">Flujo de configuración</p>
              <p class="mt-1 text-sm text-gray-700">
                Proyecto <span class="font-semibold">#{{ flowProyectoId }}</span> · Paso
                <span class="font-semibold">4/8</span> (Tipos de apartamento)
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
              :href="steps[2].href"
              class="rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-800 hover:bg-gray-50 transition"
            >
              Anterior: Pisos
            </Link>

            <Link
              :href="steps[4].href"
              class="rounded-xl bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700 transition"
            >
              Siguiente: Apartamentos
            </Link>
          </div>
        </div>
      </AppCard>

      <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <div class="lg:col-span-8 space-y-6">
          <AppCard padding="md">
            <SectionHeader
              title="Configuración general"
              subtitle="Selecciona el proyecto y define cuántos tipos vas a crear."
              icon="HomeModernIcon"
            />

            <div class="mt-5 grid grid-cols-1 md:grid-cols-3 gap-4">
              <FormField
                label="Proyecto"
                required
                :error="form.errors.id_proyecto"
                class="md:col-span-2"
              >
                <SelectInput v-model="form.id_proyecto">
                  <option value="" disabled>Seleccione proyecto...</option>
                  <option v-for="p in proyectos" :key="p.id_proyecto" :value="p.id_proyecto">
                    {{ p.nombre }}
                  </option>
                </SelectInput>
              </FormField>

              <div class="rounded-2xl border border-brand-200 bg-brand-50 p-4">
                <p class="text-xs font-semibold text-gray-700 uppercase tracking-wide">
                  Total a crear
                </p>
                <p class="mt-1 text-2xl font-bold text-gray-900">{{ form.tipos.length }}</p>
                <p class="mt-1 text-xs text-gray-600">
                  Puedes adjuntar imagen en cada tipo (opcional).
                </p>
              </div>

              <div class="md:col-span-3 flex flex-wrap items-center gap-2">
                <button
                  type="button"
                  @click="addRow"
                  class="rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm font-semibold text-gray-800 hover:bg-gray-50 transition inline-flex items-center gap-2"
                >
                  <PlusIcon class="w-5 h-5" />
                  Agregar tipo
                </button>

                <button
                  type="button"
                  @click="resetRows"
                  :disabled="form.tipos.length === 1"
                  class="rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm font-semibold text-gray-800 hover:bg-gray-50 transition disabled:opacity-60 inline-flex items-center gap-2"
                >
                  <ArrowPathIcon class="w-5 h-5" />
                  Limpiar
                </button>
              </div>
            </div>
          </AppCard>

          <AppCard padding="md">
            <div class="flex items-start justify-between gap-3">
              <div class="min-w-0">
                <p class="text-sm font-semibold text-gray-900">Detalle de tipos</p>
                <p class="mt-1 text-sm text-gray-600">
                  Solo <span class="font-semibold">Nombre</span> es obligatorio. El sistema calcula
                  el valor estimado.
                </p>
              </div>

              <button
                type="button"
                @click="addRow"
                class="shrink-0 rounded-xl bg-brand-600 px-3 py-2 text-sm font-semibold text-white hover:bg-brand-700 transition inline-flex items-center gap-2"
              >
                <PlusIcon class="w-5 h-5" />
                Agregar
              </button>
            </div>

            <div class="mt-5 space-y-4">
              <div
                v-for="(t, idx) in form.tipos"
                :key="t._key"
                class="rounded-2xl border border-gray-200 bg-white p-4"
              >
                <div class="flex items-start justify-between gap-3">
                  <div class="min-w-0">
                    <p class="text-sm font-semibold text-gray-900">Tipo {{ idx + 1 }}</p>
                    <p class="mt-1 text-xs text-gray-500">
                      Completa datos y adjunta imagen opcional.
                    </p>
                  </div>

                  <button
                    type="button"
                    @click="removeRow(idx)"
                    :disabled="form.tipos.length === 1"
                    class="rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-sm font-semibold text-red-700 hover:bg-red-100 transition disabled:opacity-60 inline-flex items-center gap-2"
                    title="Quitar"
                  >
                    <TrashIcon class="w-5 h-5" />
                    Quitar
                  </button>
                </div>

                <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div class="md:col-span-2">
                    <FormField label="Nombre" required :error="fieldError(idx, 'nombre')">
                      <TextInput
                        v-model="t.nombre"
                        type="text"
                        maxlength="100"
                        placeholder="Ej: Tipo A - 3H"
                      />
                    </FormField>
                  </div>

                  <FormField
                    label="Área construida"
                    :error="fieldError(idx, 'area_construida')"
                    hint="m²"
                  >
                    <TextInput
                      v-model.number="t.area_construida"
                      type="number"
                      step="0.01"
                      min="0"
                      placeholder="0"
                    />
                  </FormField>

                  <FormField
                    label="Área privada"
                    :error="fieldError(idx, 'area_privada')"
                    hint="m²"
                  >
                    <TextInput
                      v-model.number="t.area_privada"
                      type="number"
                      step="0.01"
                      min="0"
                      placeholder="0"
                    />
                  </FormField>

                  <FormField label="Habitaciones" :error="fieldError(idx, 'cantidad_habitaciones')">
                    <TextInput
                      v-model.number="t.cantidad_habitaciones"
                      type="number"
                      step="1"
                      min="0"
                      placeholder="0"
                    />
                  </FormField>

                  <FormField label="Baños" :error="fieldError(idx, 'cantidad_banos')">
                    <TextInput
                      v-model.number="t.cantidad_banos"
                      type="number"
                      step="1"
                      min="0"
                      placeholder="0"
                    />
                  </FormField>

                  <FormField label="Valor m²" :error="fieldError(idx, 'valor_m2')" hint="COP">
                    <TextInput
                      v-model.number="t.valor_m2"
                      type="number"
                      step="0.01"
                      min="0"
                      placeholder="0"
                    />
                  </FormField>

                  <FormField
                    label="Imagen"
                    :error="fieldError(idx, 'imagen')"
                    hint="JPG/PNG/WEBP (máx 2MB)"
                  >
                    <input
                      type="file"
                      accept="image/*"
                      class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500"
                      @change="onFileChange($event, idx)"
                    />
                    <p v-if="t._fileName" class="mt-1 text-xs text-gray-500">
                      Seleccionada:
                      <span class="font-semibold text-gray-900">{{ t._fileName }}</span>
                    </p>
                  </FormField>

                  <div class="md:col-span-2">
                    <div class="rounded-2xl border border-gray-200 bg-gray-50 p-4">
                      <p class="text-xs font-semibold text-gray-700 uppercase tracking-wide">
                        Estimación (preview)
                      </p>
                      <p class="mt-1 text-sm text-gray-700">
                        Área construida × Valor m² =
                        <span class="font-semibold text-gray-900">{{ previewEstimado(t) }}</span>
                      </p>
                      <p class="mt-1 text-xs text-gray-500">
                        Nota: el cálculo final lo realiza el backend.
                      </p>
                    </div>
                  </div>
                </div>
              </div>

              <p v-if="form.errors.tipos" class="text-sm text-red-600">
                {{ form.errors.tipos }}
              </p>
            </div>
          </AppCard>

          <div class="lg:hidden">
            <button
              type="button"
              @click="saveAndNext_Apartamentos"
              :disabled="form.processing || !canSubmit"
              class="w-full rounded-xl bg-brand-600 px-4 py-3 text-sm font-semibold text-white hover:bg-brand-700 transition disabled:opacity-60 inline-flex items-center justify-center gap-2"
            >
              <CheckIcon class="w-5 h-5" />
              {{ form.processing ? 'Guardando…' : 'Guardar tipos' }}
            </button>

            <Link
              :href="route('tipos-apartamento.index')"
              class="mt-3 block w-full text-center rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm font-semibold text-gray-800 hover:bg-gray-50 transition"
            >
              Cancelar
            </Link>
          </div>
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
                  <li>Selecciona el proyecto antes de guardar.</li>
                  <li>El nombre es obligatorio para cada tipo.</li>
                  <li>Adjunta imagen solo si la necesitas en ventas/reportes.</li>
                </ul>
              </div>
            </div>
          </AppCard>

          <AppCard padding="md">
            <p class="text-sm font-semibold text-gray-900">Resumen</p>
            <div class="mt-3 space-y-2 text-sm">
              <InlineStatus :ok="!!form.id_proyecto" label="Proyecto seleccionado" />
              <InlineStatus :ok="form.tipos.length > 0" :label="`Tipos: ${form.tipos.length}`" />
              <InlineStatus :ok="canSubmit" label="Formulario listo" />
            </div>
          </AppCard>

          <AppCard padding="md">
            <button
              type="button"
              @click="saveAndNext_Apartamentos"
              :disabled="form.processing || !canSubmit"
              class="rounded-xl bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700 transition disabled:opacity-60 inline-flex items-center gap-2"
            >
              Guardar y continuar
            </button>
          </AppCard>
        </div>
      </div>

      <FlashMessages />
    </div>
  </TopBannerLayout>
</template>

<script setup>
import { computed, onMounted } from 'vue'
import { Link, useForm, usePage, router } from '@inertiajs/vue3'

import TopBannerLayout from '@/Components/TopBannerLayout.vue'
import AppCard from '@/Components/AppCard.vue'
import PageHeader from '@/Components/PageHeader.vue'
import SectionHeader from '@/Components/SectionHeader.vue'

import FormField from '@/Components/FormField.vue'
import TextInput from '@/Components/TextInput.vue'
import SelectInput from '@/Components/SelectInput.vue'
import InlineStatus from '@/Components/InlineStatus.vue'
import FlashMessages from '@/Components/FlashMessages.vue'

import {
  PlusIcon,
  TrashIcon,
  InformationCircleIcon,
  ArrowLeftIcon,
  ArrowRightIcon,
  CheckIcon,
  ArrowPathIcon,
  HomeModernIcon,
} from '@heroicons/vue/24/outline'

const props = defineProps({
  empleado: { type: Object, default: null },
  proyectos: { type: Array, required: true },
})

const page = usePage()
const flowProyectoId = computed(() => {
  const url = page?.url || ''
  const qs = url.split('?')[1] || ''
  const sp = new URLSearchParams(qs)
  return sp.get('proyecto')
})

const activeStep = 'tipos'
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

function newRow() {
  return {
    _key: crypto.randomUUID ? crypto.randomUUID() : String(Date.now() + Math.random()),
    nombre: '',
    area_construida: null,
    area_privada: null,
    cantidad_habitaciones: null,
    cantidad_banos: null,
    valor_m2: null,
    imagen: null,
    _fileName: '',
  }
}

const form = useForm({
  id_proyecto: '',
  tipos: [newRow()],
})

onMounted(() => {
  if (flowProyectoId.value && !form.id_proyecto) {
    form.id_proyecto = String(flowProyectoId.value)
  }
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
  return form.errors?.[`tipos.${idx}.${field}`]
}

const canSubmit = computed(() => {
  if (!form.id_proyecto) return false
  if (!Array.isArray(form.tipos) || form.tipos.length === 0) return false
  return form.tipos.every((t) => String(t?.nombre || '').trim().length > 0)
})

function previewEstimado(t) {
  const a = Number(t?.area_construida || 0)
  const v = Number(t?.valor_m2 || 0)
  if (!a || !v) return '—'
  const val = Math.ceil(a * v)
  return new Intl.NumberFormat('es-CO', {
    style: 'currency',
    currency: 'COP',
    maximumFractionDigits: 0,
  }).format(val)
}

function saveAndNext_Apartamentos() {
  form.post(route('tipos-apartamento.store'), {
    forceFormData: true,
    preserveScroll: true,
    onSuccess: () => router.visit(`/admin/apartamentos/create?proyecto=${form.id_proyecto}`),
  })
}
</script>
