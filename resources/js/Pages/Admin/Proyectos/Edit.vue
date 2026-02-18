<!-- resources/js/Pages/Proyectos/Edit.vue -->
<template>
  <TopBannerLayout :empleado="empleado" panel-name="Proyectos">
    <div class="space-y-6">
      <!-- Header -->
      <PageHeader
        title="Editar proyecto"
        kicker="Proyectos"
        :subtitle="`Actualiza la información del proyecto #${props.proyecto?.id_proyecto ?? '—'}.`"
      >
        <template #actions>
          <div class="flex items-center gap-2">
            <Link
              href="/proyectos"
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
              Guardar cambios
            </button>
          </div>
        </template>
      </PageHeader>

      <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Form -->
        <div class="lg:col-span-8 space-y-6">
          <!-- Básico -->
          <AppCard padding="md">
            <SectionHeader
              title="Información general"
              subtitle="Edita los datos principales del proyecto."
              icon="FolderIcon"
            />

            <div class="mt-5 grid grid-cols-1 md:grid-cols-2 gap-4">
              <FormField label="Nombre" required :error="form.errors.nombre">
                <TextInput v-model="form.nombre" placeholder="Ej: Conjunto Residencial Aurora" />
              </FormField>

              <FormField label="Estado" required :error="form.errors.id_estado">
                <SelectInput v-model="form.id_estado">
                  <option value="" disabled>Seleccione un estado</option>
                  <option v-for="estado in estados" :key="estado.id_estado" :value="estado.id_estado">
                    {{ estado.nombre }}
                  </option>
                </SelectInput>
              </FormField>

              <div class="md:col-span-2">
                <FormField label="Descripción" :error="form.errors.descripcion" hint="Opcional.">
                  <TextArea v-model="form.descripcion" rows="3" placeholder="Describe brevemente el proyecto…" />
                </FormField>
              </div>
            </div>
          </AppCard>

          <!-- Fechas -->
          <AppCard padding="md">
            <SectionHeader
              title="Fechas del proyecto"
              subtitle="Actualiza el rango estimado para seguimiento."
              icon="CalendarDaysIcon"
            />

            <div class="mt-5 grid grid-cols-1 md:grid-cols-2 gap-4">
              <FormField label="Fecha inicio" :error="form.errors.fecha_inicio">
                <TextInput v-model="form.fecha_inicio" type="date" />
              </FormField>

              <FormField label="Fecha finalización" :error="form.errors.fecha_finalizacion">
                <TextInput v-model="form.fecha_finalizacion" type="date" />
              </FormField>
            </div>
          </AppCard>

          <!-- Presupuesto y métricas -->
          <AppCard padding="md">
            <SectionHeader
              title="Presupuesto y métricas"
              subtitle="Valores para control financiero y dimensionamiento."
              icon="BanknotesIcon"
            />

            <div class="mt-5 grid grid-cols-1 md:grid-cols-3 gap-4">
              <FormField label="Presupuesto inicial" :error="form.errors.presupuesto_inicial" hint="COP">
                <TextInput v-model="form.presupuesto_inicial" type="number" step="0.01" min="0" placeholder="0" />
              </FormField>

              <FormField label="Presupuesto final" :error="form.errors.presupuesto_final" hint="COP">
                <TextInput v-model="form.presupuesto_final" type="number" step="0.01" min="0" placeholder="0" />
              </FormField>

              <FormField label="Metros construidos" :error="form.errors.metros_construidos" hint="m²">
                <TextInput v-model="form.metros_construidos" type="number" step="0.01" min="0" placeholder="0" />
              </FormField>
            </div>
          </AppCard>

          <!-- Cantidades -->
          <AppCard padding="md">
            <SectionHeader
              title="Unidades y parqueaderos"
              subtitle="Parámetros de inventario base del proyecto."
              icon="BuildingOffice2Icon"
            />

            <div class="mt-5 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
              <FormField label="Cantidad locales" :error="form.errors.cantidad_locales">
                <TextInput v-model="form.cantidad_locales" type="number" min="0" placeholder="0" />
              </FormField>

              <FormField label="Cantidad apartamentos" :error="form.errors.cantidad_apartamentos">
                <TextInput v-model="form.cantidad_apartamentos" type="number" min="0" placeholder="0" />
              </FormField>

              <FormField label="Parqueaderos vehículo" :error="form.errors.cantidad_parqueaderos_vehiculo">
                <TextInput v-model="form.cantidad_parqueaderos_vehiculo" type="number" min="0" placeholder="0" />
              </FormField>

              <FormField label="Parqueaderos moto" :error="form.errors.cantidad_parqueaderos_moto">
                <TextInput v-model="form.cantidad_parqueaderos_moto" type="number" min="0" placeholder="0" />
              </FormField>
            </div>
          </AppCard>

          <!-- Estructura -->
          <AppCard padding="md">
            <SectionHeader
              title="Estructura"
              subtitle="Parámetros de estrato, pisos y torres."
              icon="Squares2X2Icon"
            />

            <div class="mt-5 grid grid-cols-1 md:grid-cols-3 gap-4">
              <FormField label="Estrato" :error="form.errors.estrato" hint="1 a 6">
                <TextInput v-model="form.estrato" type="number" min="1" max="6" placeholder="3" />
              </FormField>

              <FormField label="Número de pisos" :error="form.errors.numero_pisos">
                <TextInput v-model="form.numero_pisos" type="number" min="1" placeholder="1" />
              </FormField>

              <FormField label="Número de torres" :error="form.errors.numero_torres">
                <TextInput v-model="form.numero_torres" type="number" min="1" placeholder="1" />
              </FormField>
            </div>
          </AppCard>

          <!-- Financiación -->
          <AppCard padding="md">
            <SectionHeader
              title="Financiación"
              subtitle="Parámetros para separación y cuota inicial."
              icon="CreditCardIcon"
            />

            <div class="mt-5 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
              <FormField label="% Cuota inicial mínima" :error="form.errors.porcentaje_cuota_inicial_min" hint="0–100">
                <TextInput
                  v-model="form.porcentaje_cuota_inicial_min"
                  type="number"
                  min="0"
                  max="100"
                  step="0.01"
                  placeholder="0"
                />
              </FormField>

              <FormField label="Valor mínimo separación" :error="form.errors.valor_min_separacion" hint="COP">
                <TextInput v-model="form.valor_min_separacion" type="number" min="0" step="0.01" placeholder="0" />
              </FormField>

              <FormField label="Plazo cuota inicial (meses)" :error="form.errors.plazo_cuota_inicial_meses">
                <TextInput v-model="form.plazo_cuota_inicial_meses" type="number" min="1" placeholder="1" />
              </FormField>

              <FormField label="Plazo máximo separación (días)" :error="form.errors.plazo_max_separacion_dias" hint="1–365">
                <TextInput v-model="form.plazo_max_separacion_dias" type="number" min="1" max="365" placeholder="30" />
              </FormField>
            </div>
          </AppCard>

          <!-- Prima Altura -->
          <AppCard padding="md">
            <SectionHeader
              title="Configuración prima altura"
              subtitle="Activa y define el valor base e incremento por piso."
              icon="ArrowTrendingUpIcon"
            />

            <div class="mt-5 grid grid-cols-1 md:grid-cols-2 gap-4">
              <FormField label="Prima altura base" :error="form.errors.prima_altura_base" hint="COP">
                <TextInput
                  v-model.number="form.prima_altura_base"
                  type="number"
                  step="0.01"
                  min="0"
                  placeholder="Ej: 500000"
                />
              </FormField>

              <FormField label="Incremento por piso" :error="form.errors.prima_altura_incremento" hint="COP">
                <TextInput
                  v-model.number="form.prima_altura_incremento"
                  type="number"
                  step="0.01"
                  min="0"
                  placeholder="Ej: 100000"
                />
              </FormField>

              <div class="md:col-span-2">
                <Toggle
                  v-model="form.prima_altura_activa"
                  label="Activar prima altura en este proyecto"
                  description="Si está activo, el sistema aplicará el cálculo según piso."
                />
              </div>
            </div>
          </AppCard>

          <!-- Ubicación -->
          <AppCard padding="md">
            <SectionHeader
              title="Ubicación"
              subtitle="Asocia una ubicación existente."
              icon="MapPinIcon"
            />

            <div class="mt-5 grid grid-cols-1 gap-4">
              <FormField label="Ubicación" required :error="form.errors.id_ubicacion">
                <SelectInput v-model="form.id_ubicacion">
                  <option value="" disabled>Seleccione una ubicación</option>
                  <option
                    v-for="ubicacion in ubicaciones"
                    :key="ubicacion.id_ubicacion"
                    :value="ubicacion.id_ubicacion"
                  >
                    {{ ubicacion.direccion }}, {{ ubicacion.ciudad?.nombre }}
                  </option>
                </SelectInput>
              </FormField>
            </div>
          </AppCard>

          <!-- Mobile submit -->
          <div class="lg:hidden">
            <button
              type="button"
              @click="submit"
              :disabled="form.processing"
              class="w-full rounded-xl bg-brand-600 px-4 py-3 text-sm font-semibold text-white hover:bg-brand-700 transition disabled:opacity-60"
            >
              Guardar cambios
            </button>
          </div>
        </div>

        <!-- Aside -->
        <div class="lg:col-span-4 space-y-6">
          <AppCard padding="md">
            <div class="flex items-start gap-3">
              <span class="mt-0.5 rounded-2xl border border-brand-300/60 bg-brand-200 p-2">
                <InfoIcon class="w-5 h-5 text-brand-900" />
              </span>
              <div class="min-w-0">
                <p class="font-semibold text-gray-900">Cambios</p>
                <ul class="mt-2 space-y-2 text-sm text-gray-700 list-disc pl-5">
                  <li>Guarda cuando termines para aplicar los cambios.</li>
                  <li>Los campos requeridos: Nombre, Estado y Ubicación.</li>
                  <li>Prima altura: activa solo si está parametrizada.</li>
                </ul>
              </div>
            </div>
          </AppCard>

          <AppCard padding="md">
            <p class="text-sm font-semibold text-gray-900">Validación rápida</p>
            <div class="mt-3 space-y-2 text-sm">
              <InlineStatus :ok="!!form.nombre" label="Nombre" />
              <InlineStatus :ok="!!form.id_estado" label="Estado" />
              <InlineStatus :ok="!!form.id_ubicacion" label="Ubicación" />
              <InlineStatus :ok="!form.processing" label="Listo para guardar" />
            </div>
          </AppCard>

          <AppCard padding="md">
            <p class="text-sm font-semibold text-gray-900">Referencia</p>
            <div class="mt-2 text-sm text-gray-700 space-y-1">
              <p>
                <span class="text-gray-500">ID:</span>
                <span class="font-semibold text-gray-900">{{ props.proyecto?.id_proyecto ?? '—' }}</span>
              </p>
              <p class="truncate">
                <span class="text-gray-500">Ubicación actual:</span>
                <span class="font-semibold text-gray-900">
                  {{ ubicacionActualTexto }}
                </span>
              </p>
            </div>
          </AppCard>
        </div>
      </div>
    </div>
  </TopBannerLayout>
</template>

<script setup>
import { computed } from 'vue'
import { useForm, Link } from '@inertiajs/vue3'

import TopBannerLayout from '@/Components/TopBannerLayout.vue'
import AppCard from '@/Components/AppCard.vue'
import PageHeader from '@/Components/PageHeader.vue'

import SectionHeader from '@/Components/SectionHeader.vue'
import FormField from '@/Components/FormField.vue'
import TextInput from '@/Components/TextInput.vue'
import TextArea from '@/Components/TextArea.vue'
import SelectInput from '@/Components/SelectInput.vue'
import Toggle from '@/Components/Toggle.vue'
import InlineStatus from '@/Components/InlineStatus.vue'

import { InformationCircleIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  proyecto: { type: Object, default: () => ({}) },
  estados: { type: Array, default: () => [] },
  ubicaciones: { type: Array, default: () => [] },
  empleado: { type: Object, default: null },
})

/** Icon alias */
const InfoIcon = InformationCircleIcon

/** Form (conserva lógica) */
const form = useForm({
  nombre: props.proyecto?.nombre || '',
  descripcion: props.proyecto?.descripcion || '',
  fecha_inicio: props.proyecto?.fecha_inicio || '',
  fecha_finalizacion: props.proyecto?.fecha_finalizacion || '',
  presupuesto_inicial: props.proyecto?.presupuesto_inicial || '',
  presupuesto_final: props.proyecto?.presupuesto_final || '',
  metros_construidos: props.proyecto?.metros_construidos || '',
  cantidad_locales: props.proyecto?.cantidad_locales || '',
  cantidad_apartamentos: props.proyecto?.cantidad_apartamentos || '',
  cantidad_parqueaderos_vehiculo: props.proyecto?.cantidad_parqueaderos_vehiculo || '',
  cantidad_parqueaderos_moto: props.proyecto?.cantidad_parqueaderos_moto || '',
  estrato: props.proyecto?.estrato || '',
  numero_pisos: props.proyecto?.numero_pisos || '',
  numero_torres: props.proyecto?.numero_torres || '',
  porcentaje_cuota_inicial_min: props.proyecto?.porcentaje_cuota_inicial_min || '',
  valor_min_separacion: props.proyecto?.valor_min_separacion || '',
  plazo_cuota_inicial_meses: props.proyecto?.plazo_cuota_inicial_meses || '',
  plazo_max_separacion_dias: props.proyecto?.plazo_max_separacion_dias || '',
  id_estado: props.proyecto?.id_estado || '',
  id_ubicacion: props.proyecto?.id_ubicacion || '',
  prima_altura_base: props.proyecto?.prima_altura_base ?? null,
  prima_altura_incremento: props.proyecto?.prima_altura_incremento ?? null,
  prima_altura_activa: props.proyecto?.prima_altura_activa ?? false,
})

function submit() {
  form.put(`/proyectos/${props.proyecto.id_proyecto}`)
}

const ubicacionActualTexto = computed(() => {
  const id = String(props.proyecto?.id_ubicacion ?? '')
  const u = (props.ubicaciones ?? []).find((x) => String(x.id_ubicacion) === id)
  if (!u) return '—'
  const ciudad = u?.ciudad?.nombre ? `, ${u.ciudad.nombre}` : ''
  return `${u.direccion}${ciudad}`
})
</script>
