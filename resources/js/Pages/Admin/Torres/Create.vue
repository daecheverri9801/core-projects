<!-- resources/js/Pages/Admin/Torres/Create.vue -->
<template>
  <TopBannerLayout :empleado="empleado" panel-name="Proyectos">
    <div class="space-y-6">
      <PageHeader
        title="Crear torres"
        kicker="Torres"
        subtitle="Crea una o varias torres en una sola operación."
      >
      </PageHeader>

      <!-- Banner Flujo (2/8) -->
      <AppCard padding="md" v-if="flowProyectoId">
        <div class="flex flex-col gap-4">
          <div class="flex items-start justify-between gap-4">
            <div class="min-w-0">
              <p class="text-sm font-semibold text-gray-900">Flujo de configuración</p>
              <p class="mt-1 text-sm text-gray-700">
                Proyecto <span class="font-semibold">#{{ flowProyectoId }}</span> · Paso
                <span class="font-semibold">2/8</span> (Torres)
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
              :href="steps[0].href"
              class="rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-800 hover:bg-gray-50 transition"
            >
              Anterior: Políticas
            </Link>

            <Link
              :href="steps[2].href"
              class="rounded-xl bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700 transition"
            >
              Siguiente: Pisos
            </Link>
          </div>
        </div>
      </AppCard>

      <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <div class="lg:col-span-8 space-y-6">
          <AppCard padding="md">
            <SectionHeader
              title="Configuración general"
              subtitle="Estos datos aplican para todas las torres que agregues."
              icon="BuildingOffice2Icon"
            />

            <div class="mt-5 grid grid-cols-1 md:grid-cols-2 gap-4">
              <FormField
                class="md:col-span-2"
                label="Proyecto"
                required
                :error="form.errors.id_proyecto"
              >
                <SelectInput v-model="form.id_proyecto">
                  <option value="" disabled>Seleccione un proyecto</option>
                  <option v-for="p in proyectos" :key="p.id_proyecto" :value="p.id_proyecto">
                    {{ p.nombre }}
                  </option>
                </SelectInput>
              </FormField>

              <FormField label="Estado" required :error="form.errors.id_estado">
                <SelectInput v-model="form.id_estado">
                  <option value="" disabled>Seleccione un estado</option>
                  <option v-for="e in estados" :key="e.id_estado" :value="e.id_estado">
                    {{ e.nombre }}
                  </option>
                </SelectInput>
              </FormField>

              <div class="md:col-span-1">
                <div class="rounded-2xl border border-gray-200 bg-gray-50 p-4">
                  <p class="text-xs font-semibold text-gray-700 uppercase tracking-wide">Consejo</p>
                  <p class="mt-1 text-sm text-gray-700">
                    Agrega todas las torres necesarias y define el nivel de prima altura en cada
                    una.
                  </p>
                </div>
              </div>
            </div>
          </AppCard>

          <AppCard padding="md">
            <div class="flex items-start justify-between gap-3">
              <div class="min-w-0">
                <p class="text-sm font-semibold text-gray-900">Torres a crear</p>
                <p class="text-sm text-gray-600 mt-1">
                  Define nombre, número de pisos y nivel inicio prima altura por torre.
                </p>
              </div>

              <button
                type="button"
                @click="addTorre()"
                class="shrink-0 rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm font-semibold text-gray-800 hover:bg-gray-50 transition inline-flex items-center gap-2"
              >
                <PlusIcon class="w-5 h-5" />
                Agregar
              </button>
            </div>

            <div class="mt-5 space-y-4">
              <div
                v-for="(t, idx) in form.torres"
                :key="idx"
                class="rounded-2xl border border-gray-200 bg-white p-4"
              >
                <div class="flex items-center justify-between gap-3">
                  <p class="text-sm font-semibold text-gray-900">Torre {{ idx + 1 }}</p>

                  <button
                    v-if="form.torres.length > 1"
                    type="button"
                    @click="removeTorre(idx)"
                    class="rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-sm font-semibold text-red-700 hover:bg-red-100 transition inline-flex items-center gap-2"
                  >
                    <TrashIcon class="w-5 h-5" />
                    Quitar
                  </button>
                </div>

                <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                  <FormField label="Nombre" required :error="err(`torres.${idx}.nombre_torre`)">
                    <TextInput v-model="t.nombre_torre" type="text" placeholder="Ej: Torre 1" />
                  </FormField>

                  <FormField
                    label="Número de pisos"
                    :error="err(`torres.${idx}.numero_pisos`)"
                    hint="Entero (mín. 1)"
                  >
                    <TextInput
                      v-model.number="t.numero_pisos"
                      type="number"
                      min="1"
                      max="32767"
                      placeholder="Ej: 20"
                    />
                  </FormField>

                  <FormField
                    label="Nivel inicio prima altura"
                    required
                    :error="err(`torres.${idx}.nivel_inicio_prima`)"
                    hint="Desde qué piso se aplica la prima"
                  >
                    <TextInput
                      v-model.number="t.nivel_inicio_prima"
                      type="number"
                      min="1"
                      placeholder="2"
                    />
                  </FormField>
                </div>
              </div>

              <p v-if="form.errors.torres" class="text-sm text-red-600">
                {{ form.errors.torres }}
              </p>
            </div>
          </AppCard>

          <AppCard padding="md">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
              <div class="text-sm text-gray-700">
                <p class="font-semibold text-gray-900">Siguiente paso</p>
                <p class="mt-1">
                  Después de guardar, continúa con <span class="font-semibold">Pisos</span>.
                </p>
              </div>

              <div class="flex items-center gap-2">
                <button
                  type="button"
                  @click="saveAndNext_Pisos"
                  :disabled="form.processing || !canSubmit"
                  class="rounded-xl bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700 transition disabled:opacity-60 inline-flex items-center gap-2"
                >
                  <CheckIcon class="w-5 h-5" />
                  Guardar y continuar
                </button>

                <Link
                  v-if="flowProyectoId"
                  :href="steps[2].href"
                  class="rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-800 hover:bg-gray-50 transition inline-flex items-center gap-2"
                >
                  <ArrowRightIcon class="w-5 h-5" />
                  Ir a Pisos
                </Link>
              </div>
            </div>
          </AppCard>

          <div class="lg:hidden">
            <button
              type="button"
              @click="saveAndNext_Pisos"
              :disabled="form.processing || !canSubmit"
              class="w-full rounded-xl bg-brand-600 px-4 py-3 text-sm font-semibold text-white hover:bg-brand-700 transition disabled:opacity-60"
            >
              {{ form.processing ? 'Guardando…' : 'Guardar' }}
            </button>

            <Link
              :href="route('admin.torres.index')"
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
                  <li>Selecciona el proyecto y estado antes de guardar.</li>
                  <li>El nivel inicio prima altura es obligatorio por torre.</li>
                  <li>Puedes crear todas las torres del proyecto en una sola operación.</li>
                </ul>
              </div>
            </div>
          </AppCard>

          <AppCard padding="md">
            <p class="text-sm font-semibold text-gray-900">Resumen</p>
            <div class="mt-3 space-y-2 text-sm">
              <InlineStatus :ok="!!form.id_proyecto" label="Proyecto seleccionado" />
              <InlineStatus :ok="!!form.id_estado" label="Estado seleccionado" />
              <InlineStatus :ok="form.torres.length > 0" :label="`Torres: ${form.torres.length}`" />
              <InlineStatus :ok="canSubmit" label="Formulario listo" />
            </div>
          </AppCard>
        </div>
      </div>
    </div>
  </TopBannerLayout>
</template>

<script setup>
import { computed, onMounted } from 'vue'
import { useForm, Link, usePage } from '@inertiajs/vue3'

import TopBannerLayout from '@/Components/TopBannerLayout.vue'
import AppCard from '@/Components/AppCard.vue'
import PageHeader from '@/Components/PageHeader.vue'
import SectionHeader from '@/Components/SectionHeader.vue'

import FormField from '@/Components/FormField.vue'
import TextInput from '@/Components/TextInput.vue'
import SelectInput from '@/Components/SelectInput.vue'
import InlineStatus from '@/Components/InlineStatus.vue'

import {
  PlusIcon,
  TrashIcon,
  InformationCircleIcon,
  BuildingOffice2Icon,
  ArrowRightIcon,
  CheckIcon,
} from '@heroicons/vue/24/outline'

const props = defineProps({
  proyectos: { type: Array, default: () => [] },
  estados: { type: Array, default: () => [] },
  empleado: { type: Object, default: null },
})

const page = usePage()
const flowProyectoId = computed(() => {
  const url = page?.url || ''
  const qs = url.split('?')[1] || ''
  const sp = new URLSearchParams(qs)
  return sp.get('proyecto')
})

const activeStep = 'torres'
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
  id_estado: '',
  torres: [{ nombre_torre: 'Torre 1', numero_pisos: null, nivel_inicio_prima: 2 }],
})

onMounted(() => {
  if (flowProyectoId.value && !form.id_proyecto) {
    form.id_proyecto = String(flowProyectoId.value)
  }
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
  form.torres = form.torres.map((t, i) => ({
    ...t,
    nombre_torre: t.nombre_torre?.trim() ? t.nombre_torre : `Torre ${i + 1}`,
  }))
}

const canSubmit = computed(() => {
  if (!form.id_proyecto || !form.id_estado) return false
  if (!Array.isArray(form.torres) || form.torres.length === 0) return false
  return form.torres.every(
    (t) => String(t.nombre_torre || '').trim().length > 0 && Number(t.nivel_inicio_prima) >= 1
  )
})

function saveAndNext_Pisos() {
  form.post(route('admin.torres.store'), {
    preserveScroll: true,
    onSuccess: () => router.visit(`/pisos-torre/create?proyecto=${form.id_proyecto}`),
  })
}

</script>
