<!-- resources/js/Pages/PoliticasPrecioProyecto/Create.vue -->
<template>
  <TopBannerLayout :empleado="empleado">
    <div class="space-y-6">
      <PageHeader
        title="Nueva política de precio"
        kicker="Políticas de precio"
        subtitle="Define escalones de aumento por ventas y su vigencia."
      >
      </PageHeader>

      <!-- Banner Flujo (1/8) -->
      <AppCard padding="md" v-if="flowProyectoId">
        <div class="flex flex-col gap-4">
          <div class="flex items-start justify-between gap-4">
            <div class="min-w-0">
              <p class="text-sm font-semibold text-gray-900">Flujo de configuración</p>
              <p class="mt-1 text-sm text-gray-700">
                Proyecto <span class="font-semibold">#{{ flowProyectoId }}</span> · Paso
                <span class="font-semibold">1/8</span> (Políticas)
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

          <div class="flex items-center justify-end gap-2">
            <Link
              :href="steps[1].href"
              class="rounded-xl bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700 transition"
            >
              Siguiente: Torres
            </Link>
          </div>
        </div>
      </AppCard>

      <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Form -->
        <div class="lg:col-span-8 space-y-6">
          <AppCard padding="md">
            <SectionHeader
              title="Configuración"
              subtitle="Completa los campos para crear la política."
              icon="TagIcon"
            />

            <div class="mt-5 grid grid-cols-1 md:grid-cols-2 gap-4">
              <!-- Proyecto -->
              <div class="md:col-span-2">
                <FormField label="Proyecto" required :error="form.errors.id_proyecto">
                  <SelectInput v-model="form.id_proyecto">
                    <option value="">Seleccione un proyecto</option>
                    <option v-for="p in proyectos" :key="p.id_proyecto" :value="p.id_proyecto">
                      {{ p.nombre }}
                    </option>
                  </SelectInput>
                </FormField>
              </div>

              <!-- Ventas por escalón -->
              <FormField
                label="Ventas por escalón"
                :error="form.errors.ventas_por_escalon"
                hint="Ej: 10"
              >
                <TextInput
                  v-model.number="form.ventas_por_escalon"
                  type="number"
                  min="1"
                  placeholder="10"
                />
              </FormField>

              <!-- % Aumento -->
              <FormField label="% Aumento" required :error="form.errors.porcentaje_aumento" hint="Ej: 5.5">
                <TextInput
                  v-model.number="form.porcentaje_aumento"
                  type="number"
                  step="0.001"
                  min="0"
                  max="999.999"
                  placeholder="5.5"
                />
              </FormField>

              <!-- Aplica desde -->
              <FormField label="Aplica desde" :error="form.errors.aplica_desde">
                <TextInput v-model="form.aplica_desde" type="date" />
              </FormField>

              <!-- Estado -->
              <div class="md:col-span-2">
                <Toggle
                  v-model="form.estado"
                  label="Política activa"
                  description="Si está activa, podrá aplicarse según las reglas del sistema."
                />
              </div>

              <button
                type="button"
                @click="saveAndNext_Torres"
                :disabled="form.processing || !form.id_proyecto"
                class="rounded-xl bg-brand-600 px-5 py-2 text-sm font-semibold text-white hover:bg-brand-700 transition disabled:opacity-50 inline-flex items-center gap-2"
              >
                <CheckIcon class="w-5 h-5" />
                Guardar y continuar
              </button>
            </div>
          </AppCard>

          <!-- Mobile submit -->
          <div class="lg:hidden">
            <button
              type="button"
              @click="saveAndNext_Torres"
              :disabled="form.processing"
              class="w-full rounded-xl bg-brand-600 px-4 py-3 text-sm font-semibold text-white hover:bg-brand-700 transition disabled:opacity-60"
            >
              {{ form.processing ? 'Guardando…' : 'Guardar' }}
            </button>

            <Link
              href="/politicas-precio-proyecto"
              class="mt-3 block w-full text-center rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm font-semibold text-gray-800 hover:bg-gray-50 transition"
            >
              Cancelar
            </Link>
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
                <p class="font-semibold text-gray-900">Recomendaciones</p>
                <ul class="mt-2 space-y-2 text-sm text-gray-700 list-disc pl-5">
                  <li>Define primero el proyecto al que aplica.</li>
                  <li>Usa “Aplica desde” para controlar la vigencia.</li>
                </ul>
              </div>
            </div>
          </AppCard>

          <AppCard padding="md">
            <p class="text-sm font-semibold text-gray-900">Validación rápida</p>
            <div class="mt-3 space-y-2 text-sm">
              <InlineStatus :ok="!!form.id_proyecto" label="Proyecto" />
              <InlineStatus
                :ok="form.estado === true || form.estado === false"
                label="Estado definido"
              />
              <InlineStatus :ok="!form.processing" label="Listo para guardar" />
            </div>
          </AppCard>
        </div>
      </div>
    </div>
  </TopBannerLayout>
</template>

<script setup>
import { computed, onMounted } from 'vue'
import { useForm, Link, usePage, router } from '@inertiajs/vue3'

import TopBannerLayout from '@/Components/TopBannerLayout.vue'
import AppCard from '@/Components/AppCard.vue'
import PageHeader from '@/Components/PageHeader.vue'
import SectionHeader from '@/Components/SectionHeader.vue'

import FormField from '@/Components/FormField.vue'
import TextInput from '@/Components/TextInput.vue'
import SelectInput from '@/Components/SelectInput.vue'
import Toggle from '@/Components/Toggle.vue'
import InlineStatus from '@/Components/InlineStatus.vue'

import { TagIcon, InformationCircleIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  proyectos: { type: Array, default: () => [] },
  proyectoSeleccionado: { type: [String, Number], default: null },
  empleado: { type: Object, default: null },
})

const page = usePage()
const flowProyectoId = computed(() => {
  const url = page?.url || ''
  const qs = url.split('?')[1] || ''
  const sp = new URLSearchParams(qs)
  return sp.get('proyecto') || null
})

const activeStep = 'politicas'

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
  id_proyecto: props.proyectoSeleccionado || '',
  ventas_por_escalon: null,
  porcentaje_aumento: null,
  aplica_desde: '',
  estado: true,
})

onMounted(() => {
  if (!form.id_proyecto && flowProyectoId.value) {
    form.id_proyecto = String(flowProyectoId.value)
  }
})

function saveAndNext_Torres() {
  form.post('/politicas-precio-proyecto', {
    preserveScroll: true,
    onSuccess: () => router.visit(`/admin/torres/create?proyecto=${form.id_proyecto}`),
  })
}
</script>
