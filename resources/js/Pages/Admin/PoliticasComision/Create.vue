<template>
  <TopBannerLayout :empleado="empleado">
    <div class="space-y-6">
      <PageHeader
        title="Nueva política"
        kicker="Políticas"
        subtitle="Configura políticas de precio y comisión para el proyecto."
      >
      </PageHeader>

      <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Formularios -->
        <div class="lg:col-span-8 space-y-6">
          <!-- FORMULARIO COMISIÓN -->
          <AppCard padding="md">
            <SectionHeader
              title="Política de comisión"
              subtitle="Define el porcentaje de comisión por empleado y tipo de comisión."
              icon="CurrencyDollarIcon"
            />

            <div class="mt-5 grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="md:col-span-2">
                <FormField label="Proyecto" required :error="formComision.errors.id_proyecto">
                  <SelectInput v-model="formComision.id_proyecto">
                    <option value="">Seleccione un proyecto</option>
                    <option v-for="p in proyectos" :key="p.id_proyecto" :value="p.id_proyecto">
                      {{ p.nombre }}
                    </option>
                  </SelectInput>
                </FormField>
              </div>

              <FormField label="Empleado" required :error="formComision.errors.id_empleado">
                <SelectInput v-model="formComision.id_empleado">
                  <option value="">Seleccione un empleado</option>
                  <option
                    v-for="e in empleadosComisionables"
                    :key="e.id_empleado"
                    :value="e.id_empleado"
                  >
                    {{ e.nombre }} · {{ e.cargo }}
                  </option>
                </SelectInput>
              </FormField>

              <FormField
                label="Tipo de comisión"
                required
                :error="formComision.errors.tipo_comision"
              >
                <SelectInput v-model="formComision.tipo_comision">
                  <option value="">Seleccione un tipo</option>
                  <option
                    v-for="tipo in tiposComisionDisponibles"
                    :key="tipo.value"
                    :value="tipo.value"
                  >
                    {{ tipo.label }}
                  </option>
                </SelectInput>
              </FormField>

              <FormField
                label="Porcentaje"
                required
                :error="formComision.errors.porcentaje"
                hint="Ej: 3.000"
              >
                <TextInput
                  v-model.number="formComision.porcentaje"
                  type="number"
                  step="0.001"
                  min="0"
                  max="999.999"
                  placeholder="3.000"
                />
              </FormField>

              <div class="md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-4">
                <FormField label="Vigente desde">
                  <div
                    class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm font-medium text-gray-900"
                  >
                    {{ proyectoComisionActual?.fecha_inicio || 'Se asignará según el proyecto' }}
                  </div>
                </FormField>

                <FormField label="Vigente hasta">
                  <div
                    class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm font-medium text-gray-900"
                  >
                    {{
                      proyectoComisionActual?.fecha_finalizacion || 'Se asignará según el proyecto'
                    }}
                  </div>
                </FormField>
              </div>

              <div class="md:col-span-2 flex justify-end">
                <button
                  type="button"
                  @click="guardarPoliticaComision"
                  :disabled="formComision.processing"
                  class="rounded-xl bg-brand-600 px-5 py-2 text-sm font-semibold text-white hover:bg-brand-700 transition disabled:opacity-50 inline-flex items-center gap-2"
                >
                  <CheckIcon class="w-5 h-5" />
                  {{ formComision.processing ? 'Guardando…' : 'Guardar política de comisión' }}
                </button>
              </div>
            </div>
          </AppCard>

          <!-- MOBILE -->
          <div class="lg:hidden space-y-3">
            <Link
              href="/politicas-precio-proyecto"
              class="block w-full text-center rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm font-semibold text-gray-800 hover:bg-gray-50 transition"
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
                  <li>Define primero el proyecto al que aplican las políticas.</li>
                  <li>Guarda cada formulario por separado.</li>
                  <li>La política de comisión toma la vigencia del proyecto automáticamente.</li>
                </ul>
              </div>
            </div>
          </AppCard>

          <AppCard padding="md">
            <p class="text-sm font-semibold text-gray-900">Validación rápida</p>
            <div class="mt-3 space-y-2 text-sm">
              <InlineStatus :ok="!!formComision.id_proyecto" label="Proyecto en comisión" />
              <InlineStatus :ok="!!formComision.id_empleado" label="Empleado en comisión" />
              <InlineStatus :ok="!!formComision.tipo_comision" label="Tipo comisión definido" />
              <InlineStatus
                :ok="!formPrecio.processing && !formComision.processing"
                label="Listo para guardar"
              />
            </div>
          </AppCard>
        </div>
      </div>
    </div>
  </TopBannerLayout>
</template>

<script setup>
import { computed, onMounted, watch } from 'vue'
import { useForm, Link, usePage } from '@inertiajs/vue3'

import TopBannerLayout from '@/Components/TopBannerLayout.vue'
import AppCard from '@/Components/AppCard.vue'
import PageHeader from '@/Components/PageHeader.vue'
import SectionHeader from '@/Components/SectionHeader.vue'

import FormField from '@/Components/FormField.vue'
import TextInput from '@/Components/TextInput.vue'
import SelectInput from '@/Components/SelectInput.vue'
import Toggle from '@/Components/Toggle.vue'
import InlineStatus from '@/Components/InlineStatus.vue'

import {
  TagIcon,
  InformationCircleIcon,
  CheckIcon,
  CurrencyDollarIcon,
} from '@heroicons/vue/24/outline'

const props = defineProps({
  proyectos: { type: Array, default: () => [] },
  empleadosComisionables: { type: Array, default: () => [] },
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

const formPrecio = useForm({
  id_proyecto: props.proyectoSeleccionado || '',
  ventas_por_escalon: null,
  porcentaje_aumento: null,
  aplica_desde: '',
  estado: true,
})

const formComision = useForm({
  id_proyecto: props.proyectoSeleccionado || '',
  id_empleado: '',
  tipo_comision: '',
  porcentaje: null,
  from_politicas_precio_create: true,
})

onMounted(() => {
  const proyectoInicial = props.proyectoSeleccionado || flowProyectoId.value || ''
  if (proyectoInicial) {
    formPrecio.id_proyecto = String(proyectoInicial)
    formComision.id_proyecto = String(proyectoInicial)
  }
})

watch(
  () => formPrecio.id_proyecto,
  (value) => {
    if (String(formComision.id_proyecto || '') !== String(value || '')) {
      formComision.id_proyecto = value
    }
  }
)

watch(
  () => formComision.id_proyecto,
  (value) => {
    if (String(formPrecio.id_proyecto || '') !== String(value || '')) {
      formPrecio.id_proyecto = value
    }
  }
)

const proyectoComisionActual = computed(() => {
  const id = String(formComision.id_proyecto || '')
  return (props.proyectos || []).find((p) => String(p.id_proyecto) === id) || null
})

const empleadoComisionActual = computed(() => {
  const id = String(formComision.id_empleado || '')
  return (props.empleadosComisionables || []).find((e) => String(e.id_empleado) === id) || null
})

const tiposComisionDisponibles = computed(() => {
  const cargo = empleadoComisionActual.value?.cargo || ''

  if (cargo === 'Asesora Comercial') {
    return [{ value: 'venta_propia', label: 'Venta propia' }]
  }

  if (cargo === 'Directora Comercial') {
    return [
      { value: 'venta_propia', label: 'Venta propia' },
      { value: 'venta_equipo', label: 'Venta del equipo' },
    ]
  }

  return [
    { value: 'venta_propia', label: 'Venta propia' },
    { value: 'venta_equipo', label: 'Venta del equipo' },
  ]
})

watch(
  () => empleadoComisionActual.value?.cargo,
  (cargo) => {
    if (cargo === 'Asesora Comercial' && formComision.tipo_comision === 'venta_equipo') {
      formComision.tipo_comision = 'venta_propia'
    }
  }
)

function guardarPoliticaComision() {
  formComision.post('/politicas-comision', {
    preserveScroll: true,
  })
}
</script>
