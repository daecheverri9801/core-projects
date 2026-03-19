<template>
  <TopBannerLayout :empleado="empleado">
    <div class="space-y-6">
      <PageHeader
        title="Editar política de comisión"
        kicker="Políticas de comisión"
        :subtitle="subtitle"
      >
        <template #actions>
          <div class="flex items-center gap-2">
            <Link
              href="/politicas-comision"
              class="rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-800 hover:bg-gray-50 transition"
            >
              Volver
            </Link>

            <button
              type="button"
              @click="submit"
              :disabled="form.processing"
              class="rounded-xl bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700 transition disabled:opacity-60"
            >
              Actualizar
            </button>
          </div>
        </template>
      </PageHeader>

      <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <div class="lg:col-span-8 space-y-6">
          <AppCard padding="md">
            <SectionHeader
              title="Configuración"
              subtitle="Edita los campos de la política de comisión."
              icon="TagIcon"
            />

            <div class="mt-5 grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="md:col-span-2">
                <FormField label="Proyecto" required :error="form.errors.id_proyecto">
                  <SelectInput v-model="form.id_proyecto">
                    <option value="" disabled>Seleccione un proyecto</option>
                    <option v-for="p in proyectos" :key="p.id_proyecto" :value="p.id_proyecto">
                      {{ p.nombre }}
                    </option>
                  </SelectInput>
                </FormField>
              </div>

              <FormField label="Empleado" required :error="form.errors.id_empleado">
                <SelectInput v-model="form.id_empleado">
                  <option value="" disabled>Seleccione un empleado</option>
                  <option
                    v-for="e in empleadosComisionables"
                    :key="e.id_empleado"
                    :value="e.id_empleado"
                  >
                    {{ e.nombre }} · {{ e.cargo }}
                  </option>
                </SelectInput>
              </FormField>

              <FormField label="Tipo de comisión" required :error="form.errors.tipo_comision">
                <SelectInput v-model="form.tipo_comision">
                  <option value="" disabled>Seleccione un tipo</option>
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
                :error="form.errors.porcentaje"
                hint="Ej: 3.000"
              >
                <TextInput
                  v-model.number="form.porcentaje"
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
                    {{ proyectoActual?.fecha_inicio || '—' }}
                  </div>
                </FormField>

                <FormField label="Vigente hasta">
                  <div
                    class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm font-medium text-gray-900"
                  >
                    {{ proyectoActual?.fecha_finalizacion || '—' }}
                  </div>
                </FormField>
              </div>
            </div>
          </AppCard>

          <div class="lg:hidden">
            <button
              type="button"
              @click="submit"
              :disabled="form.processing"
              class="w-full rounded-xl bg-brand-600 px-4 py-3 text-sm font-semibold text-white hover:bg-brand-700 transition disabled:opacity-60"
            >
              {{ form.processing ? 'Actualizando…' : 'Actualizar' }}
            </button>

            <Link
              href="/politicas-comision"
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
                <p class="font-semibold text-gray-900">Referencia</p>
                <div class="mt-2 text-sm text-gray-700 space-y-1">
                  <p>
                    <span class="text-gray-500">ID:</span>
                    <span class="font-semibold text-gray-900">{{
                      politica?.id_politica_comision ?? '—'
                    }}</span>
                  </p>
                  <p class="truncate">
                    <span class="text-gray-500">Proyecto actual:</span>
                    <span class="font-semibold text-gray-900">{{ proyectoActualNombre }}</span>
                  </p>
                  <p class="truncate">
                    <span class="text-gray-500">Empleado actual:</span>
                    <span class="font-semibold text-gray-900">{{
                      empleadoActual?.nombre || '—'
                    }}</span>
                  </p>
                  <p class="truncate">
                    <span class="text-gray-500">Cargo:</span>
                    <span class="font-semibold text-gray-900">{{
                      empleadoActual?.cargo || '—'
                    }}</span>
                  </p>
                </div>
              </div>
            </div>
          </AppCard>

          <AppCard padding="md">
            <p class="text-sm font-semibold text-gray-900">Validación rápida</p>
            <div class="mt-3 space-y-2 text-sm">
              <InlineStatus :ok="!!form.id_proyecto" label="Proyecto" />
              <InlineStatus :ok="!!form.id_empleado" label="Empleado" />
              <InlineStatus :ok="!!form.tipo_comision" label="Tipo comisión" />
              <InlineStatus
                :ok="form.porcentaje !== null && form.porcentaje !== ''"
                label="Porcentaje"
              />
            </div>
          </AppCard>
        </div>
      </div>
    </div>
  </TopBannerLayout>
</template>

<script setup>
import { computed, watch } from 'vue'
import { useForm, Link } from '@inertiajs/vue3'

import TopBannerLayout from '@/Components/TopBannerLayout.vue'
import AppCard from '@/Components/AppCard.vue'
import PageHeader from '@/Components/PageHeader.vue'
import SectionHeader from '@/Components/SectionHeader.vue'

import FormField from '@/Components/FormField.vue'
import TextInput from '@/Components/TextInput.vue'
import SelectInput from '@/Components/SelectInput.vue'
import InlineStatus from '@/Components/InlineStatus.vue'

import { TagIcon, InformationCircleIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  politica: { type: Object, default: () => ({}) },
  proyectos: { type: Array, default: () => [] },
  empleadosComisionables: { type: Array, default: () => [] },
  empleado: { type: Object, default: null },
})

const politica = computed(() => props.politica)

const normalizarFecha = (f) => {
  if (!f) return ''
  return String(f).split('T')[0].split(' ')[0]
}

const form = useForm({
  id_proyecto: props.politica?.id_proyecto || '',
  id_empleado: props.politica?.id_empleado || '',
  tipo_comision: props.politica?.tipo_comision || '',
  porcentaje: props.politica?.porcentaje ?? null,
})

const proyectoActualNombre = computed(() => {
  const id = String(form.id_proyecto || '')
  const p = (props.proyectos || []).find((x) => String(x.id_proyecto) === id)
  return p?.nombre || '—'
})

const empleadoActual = computed(() => {
  const id = String(form.id_empleado || '')
  return (props.empleadosComisionables || []).find((x) => String(x.id_empleado) === id) || null
})

const subtitle = computed(() => {
  const id = props.politica?.id_politica_comision
  return `Actualiza proyecto, cargo, porcentaje y vigencia. (ID: ${id ?? '—'})`
})

const proyectoActual = computed(() => {
  const id = String(form.id_proyecto || '')
  return (props.proyectos || []).find((p) => String(p.id_proyecto) === id) || null
})

const empleadoSeleccionadoActual = computed(() => {
  const id = String(form.id_empleado || '')
  return (props.empleadosComisionables || []).find((e) => String(e.id_empleado) === id) || null
})

const tiposComisionDisponibles = computed(() => {
  const cargo = empleadoSeleccionadoActual.value?.cargo || ''

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
  () => empleadoSeleccionadoActual.value?.cargo,
  (cargo) => {
    if (cargo === 'Asesora Comercial' && form.tipo_comision === 'venta_equipo') {
      form.tipo_comision = 'venta_propia'
    }
  }
)

function submit() {
  form.put(`/politicas-comision/${props.politica.id_politica_comision}`)
}
</script>
