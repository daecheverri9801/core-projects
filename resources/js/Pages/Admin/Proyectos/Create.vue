<!-- resources/js/Pages/Proyectos/Create.vue -->
<template>
  <TopBannerLayout :empleado="empleado" panel-name="Proyectos">
    <div class="space-y-6">
      <!-- Header -->
      <PageHeader
        title="Crear proyecto"
        kicker="Proyectos"
        subtitle="Registra la información base, ubicación y parámetros financieros del proyecto."
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
              Guardar
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
              subtitle="Define los datos principales para identificar el proyecto."
              icon="FolderIcon"
            />

            <div class="mt-5 grid grid-cols-1 md:grid-cols-2 gap-4">
              <FormField label="Nombre" required :error="form.errors.nombre">
                <TextInput v-model="form.nombre" placeholder="Ej: Conjunto Residencial Aurora" />
              </FormField>

              <FormField label="Estado" required :error="form.errors.id_estado">
                <SelectInput v-model="form.id_estado" placeholder="Seleccione un estado">
                  <option value="" disabled>Seleccione un estado</option>
                  <option v-for="estado in estados" :key="estado.id_estado" :value="estado.id_estado">
                    {{ estado.nombre }}
                  </option>
                </SelectInput>
              </FormField>

              <div class="md:col-span-2">
                <FormField label="Descripción" :error="form.errors.descripcion" hint="Opcional. Máx. 500 caracteres.">
                  <TextArea v-model="form.descripcion" rows="3" placeholder="Describe brevemente el proyecto…" />
                </FormField>
              </div>
            </div>
          </AppCard>

          <!-- Fechas -->
          <AppCard padding="md">
            <SectionHeader
              title="Fechas del proyecto"
              subtitle="Define el rango estimado para seguimiento."
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

          <!-- Estrato / Pisos / Torres -->
          <AppCard padding="md">
            <SectionHeader
              title="Estructura"
              subtitle="Información del edificio/proyecto para parametrización."
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
              subtitle="Asocia una ubicación existente o crea una nueva."
              icon="MapPinIcon"
            />

            <div class="mt-5 grid grid-cols-1 gap-4">
              <FormField label="Ubicación" required :error="form.errors.id_ubicacion">
                <div class="flex flex-col md:flex-row gap-2 md:items-center">
                  <SelectInput v-model="form.id_ubicacion" placeholder="Seleccione una ubicación">
                    <option value="" disabled>Seleccione una ubicación</option>
                    <option
                      v-for="ubicacion in ubicacionesLocal"
                      :key="ubicacion.id_ubicacion"
                      :value="ubicacion.id_ubicacion"
                    >
                      {{ ubicacion.direccion }}, {{ ubicacion?.ciudad?.nombre }}
                    </option>
                  </SelectInput>

                  <button
                    type="button"
                    @click="openModal = true"
                    class="rounded-xl bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700 transition"
                  >
                    Crear ubicación
                  </button>
                </div>
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
              Guardar proyecto
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
                <p class="font-semibold text-gray-900">Recomendaciones</p>
                <ul class="mt-2 space-y-2 text-sm text-gray-700 list-disc pl-5">
                  <li>Completa primero “Información general” y “Ubicación”.</li>
                  <li>Los valores financieros pueden ajustarse más adelante.</li>
                  <li>Si activas prima altura, define base e incremento.</li>
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
            </div>
          </AppCard>
        </div>
      </div>

      <!-- Modal: Crear Ubicación -->
      <ModalShell :open="openModal" title="Crear nueva ubicación" @close="closeModal">
        <form @submit.prevent="submitUbicacion" class="space-y-4">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <FormField label="País" required :error="errorsUbicacion.id_pais">
              <SelectInput v-model="selectedPais" @change="onPaisChange" :disabled="loadingJerarquia">
                <option value="" disabled>Seleccione un país</option>
                <option v-for="pais in paises" :key="pais.id_pais" :value="pais.id_pais">
                  {{ pais.nombre }}
                </option>
              </SelectInput>
            </FormField>

            <FormField label="Departamento" required :error="errorsUbicacion.id_departamento">
              <SelectInput
                v-model="selectedDepartamento"
                @change="onDepartamentoChange"
                :disabled="!selectedPais || loadingJerarquia"
              >
                <option value="" disabled>Seleccione un departamento</option>
                <option
                  v-for="dep in departamentosFiltrados"
                  :key="dep.id_departamento"
                  :value="dep.id_departamento"
                >
                  {{ dep.nombre }}
                </option>
              </SelectInput>
            </FormField>

            <div class="md:col-span-2">
              <FormField label="Ciudad" required :error="errorsUbicacion.id_ciudad">
                <SelectInput v-model="selectedCiudad" :disabled="!selectedDepartamento || loadingJerarquia">
                  <option value="" disabled>Seleccione una ciudad</option>
                  <option v-for="ciudad in ciudadesFiltradas" :key="ciudad.id_ciudad" :value="ciudad.id_ciudad">
                    {{ ciudad.nombre }}
                  </option>
                </SelectInput>
              </FormField>
            </div>

            <FormField label="Barrio" :error="errorsUbicacion.barrio">
              <TextInput v-model="formUbicacion.barrio" maxlength="120" placeholder="Opcional" />
            </FormField>

            <div class="md:col-span-2">
              <FormField label="Dirección" required :error="errorsUbicacion.direccion">
                <TextInput v-model="formUbicacion.direccion" maxlength="300" placeholder="Ej: Calle 10 # 12-34" />
              </FormField>
            </div>
          </div>

          <div class="flex justify-end gap-2 pt-2">
            <button
              type="button"
              @click="closeModal"
              class="rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-800 hover:bg-gray-50 transition"
            >
              Cancelar
            </button>

            <button
              type="submit"
              :disabled="loadingUbicacion"
              class="rounded-xl bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700 transition disabled:opacity-60"
            >
              Guardar ubicación
            </button>
          </div>
        </form>
      </ModalShell>
    </div>
  </TopBannerLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useForm, Link, router } from '@inertiajs/vue3'

import TopBannerLayout from '@/Components/TopBannerLayout.vue'
import AppCard from '@/Components/AppCard.vue'
import PageHeader from '@/Components/PageHeader.vue'

import SectionHeader from '@/Components/SectionHeader.vue'
import FormField from '@/Components/FormField.vue'
import TextInput from '@/Components/TextInput.vue'
import TextArea from '@/Components/TextArea.vue'
import SelectInput from '@/Components/SelectInput.vue'
import Toggle from '@/Components/Toggle.vue'
import ModalShell from '@/Components/ModalShell.vue'
import InlineStatus from '@/Components/InlineStatus.vue'

import {
  FolderIcon,
  CalendarDaysIcon,
  BanknotesIcon,
  BuildingOffice2Icon,
  Squares2X2Icon,
  CreditCardIcon,
  ArrowTrendingUpIcon,
  MapPinIcon,
  InformationCircleIcon,
} from '@heroicons/vue/24/outline'

const props = defineProps({
  estados: { type: Array, default: () => [] },
  ubicaciones: { type: Array, default: () => [] },
  empleado: { type: Object, default: null },
})

/** Icon alias */
const InfoIcon = InformationCircleIcon

/** Evitar mutar props directamente */
const ubicacionesLocal = ref([...(props.ubicaciones ?? [])])

/** Formulario proyecto */
const form = useForm({
  nombre: '',
  descripcion: '',
  fecha_inicio: '',
  fecha_finalizacion: '',
  presupuesto_inicial: '',
  presupuesto_final: '',
  metros_construidos: '',
  cantidad_locales: '',
  cantidad_apartamentos: '',
  cantidad_parqueaderos_vehiculo: '',
  cantidad_parqueaderos_moto: '',
  estrato: '',
  numero_pisos: '',
  numero_torres: '',
  porcentaje_cuota_inicial_min: '',
  valor_min_separacion: '',
  plazo_cuota_inicial_meses: '',
  plazo_max_separacion_dias: '',
  id_estado: '',
  id_ubicacion: '',
  prima_altura_base: null,
  prima_altura_incremento: null,
  prima_altura_activa: false,
})

function submit() {
  router.post('/proyectos', form)
}

/** Modal ubicación */
const openModal = ref(false)
const paises = ref([])
const selectedPais = ref('')
const departamentosFiltrados = ref([])
const selectedDepartamento = ref('')
const ciudadesFiltradas = ref([])
const selectedCiudad = ref('')
const loadingJerarquia = ref(false)

const formUbicacion = ref({
  barrio: '',
  direccion: '',
})

const errorsUbicacion = ref({})
const loadingUbicacion = ref(false)

async function cargarJerarquia() {
  loadingJerarquia.value = true
  try {
    const response = await fetch('/ubicacion/jerarquia')
    const data = await response.json()
    if (data?.success) paises.value = data.data || []
  } catch (e) {
    // opcional: podrías enviar toast
    console.error('Error cargando jerarquía:', e)
  } finally {
    loadingJerarquia.value = false
  }
}

onMounted(() => {
  cargarJerarquia()
})

function onPaisChange() {
  selectedDepartamento.value = ''
  selectedCiudad.value = ''
  departamentosFiltrados.value = []
  ciudadesFiltradas.value = []
  if (!selectedPais.value) return

  const pais = paises.value.find((p) => String(p.id_pais) === String(selectedPais.value))
  departamentosFiltrados.value = pais?.departamentos ?? []
}

function onDepartamentoChange() {
  selectedCiudad.value = ''
  ciudadesFiltradas.value = []
  if (!selectedDepartamento.value) return

  const departamento = departamentosFiltrados.value.find(
    (d) => String(d.id_departamento) === String(selectedDepartamento.value)
  )
  ciudadesFiltradas.value = departamento?.ciudades ?? []
}

async function submitUbicacion() {
  errorsUbicacion.value = {}

  if (!selectedCiudad.value) {
    errorsUbicacion.value.id_ciudad = 'La ciudad es obligatoria'
    return
  }
  if (!formUbicacion.value.direccion) {
    errorsUbicacion.value.direccion = 'La dirección es obligatoria'
    return
  }

  loadingUbicacion.value = true

  router.post(
    '/ubicacion',
    {
      id_ciudad: selectedCiudad.value,
      barrio: formUbicacion.value.barrio,
      direccion: formUbicacion.value.direccion,
    },
    {
      preserveScroll: true,
      onSuccess: (page) => {
        /**
         * Espera: backend envía props.ubicacionNueva
         * Ej: return back()->with([...]) o Inertia::render con ubicacionNueva
         */
        const nuevaUbicacion = page?.props?.ubicacionNueva

        if (nuevaUbicacion) {
          // guardar en lista local y seleccionar
          ubicacionesLocal.value = [nuevaUbicacion, ...ubicacionesLocal.value]
          form.id_ubicacion = nuevaUbicacion.id_ubicacion
        }

        closeModal()

        // reset
        selectedPais.value = ''
        selectedDepartamento.value = ''
        selectedCiudad.value = ''
        departamentosFiltrados.value = []
        ciudadesFiltradas.value = []
        formUbicacion.value = { barrio: '', direccion: '' }
        errorsUbicacion.value = {}
      },
      onError: (errors) => {
        errorsUbicacion.value = errors || {}
      },
      onFinish: () => {
        loadingUbicacion.value = false
      },
    }
  )
}

function closeModal() {
  openModal.value = false
}
</script>
