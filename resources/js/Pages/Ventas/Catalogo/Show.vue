<template>
  <VentasLayout :empleado="empleado">
    <Head
      :title="`${inmueble.tipo === 'apartamento' ? 'Apartamento' : 'Local'} ${inmueble.numero}`"
    />

    <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100">
      <!-- Header -->
      <div class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
          <Link
            href="/catalogo"
            class="inline-flex items-center text-sm text-gray-600 hover:text-[#1e3a5f] mb-4 transition-colors"
          >
            <ArrowLeftIcon class="h-4 w-4 mr-2" />
            Volver al Catálogo
          </Link>
          <div class="flex items-center justify-between">
            <div>
              <div class="flex items-center space-x-3">
                <span
                  :class="[
                    inmueble.tipo === 'apartamento'
                      ? 'bg-blue-100 text-blue-800'
                      : 'bg-purple-100 text-purple-800',
                    'px-3 py-1 rounded-full text-sm font-medium',
                  ]"
                >
                  {{ inmueble.tipo === 'apartamento' ? 'Apartamento' : 'Local Comercial' }}
                </span>
                <span
                  class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-medium"
                >
                  {{ inmueble.estado }}
                </span>
              </div>
              <h1 class="text-3xl font-bold text-gray-900 mt-2">
                {{ inmueble.tipo === 'apartamento' ? 'Apartamento' : 'Local' }}
                {{ inmueble.numero }}
              </h1>
              <p class="mt-1 text-lg text-gray-600">{{ inmueble.proyecto }}</p>
            </div>
            <div class="text-right">
              <p class="text-sm text-gray-500">Precio Final</p>
              <p class="text-4xl font-bold text-[#1e3a5f]">
                {{ formatCurrency(inmueble.valor_final) }}
              </p>
            </div>
          </div>
        </div>
      </div>

      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
          <!-- Columna Principal -->
          <div class="lg:col-span-2 space-y-6">
            <!-- Información General -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
              <h2 class="text-xl font-bold text-gray-900 mb-4">Información General</h2>
              <div class="space-y-3">
                <div class="flex items-center text-gray-700">
                  <MapPinIcon class="h-5 w-5 mr-3 text-gray-400" />
                  <div>
                    <p class="text-sm text-gray-500">Ubicación</p>
                    <p class="font-medium">{{ inmueble.direccion }}, {{ inmueble.ubicacion }}</p>
                  </div>
                </div>
                <div class="flex items-center text-gray-700">
                  <BuildingOfficeIcon class="h-5 w-5 mr-3 text-gray-400" />
                  <div>
                    <p class="text-sm text-gray-500">Tipo de Inmueble</p>
                    <p class="font-medium">{{ inmueble.tipo_inmueble }}</p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Características -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
              <h2 class="text-xl font-bold text-gray-900 mb-4">Características</h2>
              <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                <div
                  v-for="caracteristica in caracteristicas"
                  :key="caracteristica.label"
                  class="bg-gray-50 rounded-lg p-4"
                >
                  <component :is="caracteristica.icon" class="h-6 w-6 text-[#1e3a5f] mb-2" />
                  <p class="text-sm text-gray-500">{{ caracteristica.label }}</p>
                  <p class="text-lg font-bold text-gray-900">{{ caracteristica.value }}</p>
                </div>
              </div>
            </div>

            <!-- Desglose de Precio -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
              <h2 class="text-xl font-bold text-gray-900 mb-4">Desglose de Precio</h2>
              <div class="space-y-3">
                <div
                  v-for="item in desglosePrecio"
                  :key="item.label"
                  class="flex justify-between items-center py-2 border-b border-gray-100 last:border-0"
                >
                  <span class="text-gray-700">{{ item.label }}</span>
                  <span class="font-semibold text-gray-900">{{ formatCurrency(item.value) }}</span>
                </div>
                <div class="flex justify-between items-center pt-4 border-t-2 border-gray-200">
                  <span class="text-lg font-bold text-gray-900">Precio Final</span>
                  <span class="text-2xl font-bold text-[#1e3a5f]">{{
                    formatCurrency(inmueble.valor_final)
                  }}</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Columna Lateral -->
          <div class="space-y-6">
            <!-- Resumen Rápido -->
            <div
              class="bg-gradient-to-br from-[#1e3a5f] to-[#2c5282] rounded-xl shadow-lg p-6 text-white sticky top-6"
            >
              <h3 class="text-xl font-bold mb-4">Resumen Rápido</h3>
              <div class="space-y-4">
                <div>
                  <p class="text-sm text-blue-200">Número</p>
                  <p class="text-2xl font-bold">{{ inmueble.numero }}</p>
                </div>
                <div>
                  <p class="text-sm text-blue-200">Proyecto</p>
                  <p class="font-semibold">{{ inmueble.proyecto }}</p>
                </div>
                <div>
                  <p class="text-sm text-blue-200">Torre - Piso</p>
                  <p class="font-semibold">{{ inmueble.torre }} - Piso {{ inmueble.piso }}</p>
                </div>
                <div class="pt-4 border-t border-blue-400">
                  <p class="text-sm text-blue-200">Valor por m²</p>
                  <p class="text-xl font-bold">{{ formatCurrency(inmueble.valor_m2) }}</p>
                </div>
              </div>

              <!-- Botón de Acción -->
              <Link
                :href="`/ventas/create?inmueble_tipo=${inmueble.tipo}&inmueble_id=${inmueble.id}`"
                class="mt-6 w-full bg-[#f4c430] text-gray-900 py-3 rounded-lg font-bold text-center hover:bg-[#e5b520] transition-colors flex items-center justify-center"
              >
                <CheckCircleIcon class="h-5 w-5 mr-2" />
                Iniciar Venta
              </Link>
            </div>

            <!-- Información del Proyecto -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
              <h3 class="text-lg font-bold text-gray-900 mb-4">Sobre el Proyecto</h3>
              <Link
                :href="`/proyectos/${inmueble.id_proyecto}`"
                class="text-[#1e3a5f] hover:text-[#2c5282] font-medium flex items-center"
              >
                Ver detalles del proyecto
                <ArrowLeftIcon class="h-4 w-4 ml-2 rotate-180" />
              </Link>
            </div>
          </div>
        </div>
      </div>
    </div>
  </VentasLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/inertia-vue3' // ✅ Cambiado a v0.6.0
import VentasLayout from '@/Components/VentasLayout.vue'
import {
  ArrowLeftIcon,
  HomeIcon,
  MapPinIcon,
  CurrencyDollarIcon,
  BuildingOfficeIcon,
  ChartBarIcon,
  CheckCircleIcon,
} from '@heroicons/vue/24/outline'

const props = defineProps({
  inmueble: Object,
  empleado: Object,
})

// Formatear moneda
const formatCurrency = (value) => {
  return new Intl.NumberFormat('es-CO', {
    style: 'currency',
    currency: 'COP',
    minimumFractionDigits: 0,
  }).format(value)
}

// Características del inmueble
const caracteristicas = [
  { label: 'Área Construida', value: `${props.inmueble.area_construida} m²`, icon: ChartBarIcon },
  { label: 'Área Privada', value: `${props.inmueble.area_privada} m²`, icon: ChartBarIcon },
  ...(props.inmueble.habitaciones
    ? [{ label: 'Habitaciones', value: props.inmueble.habitaciones, icon: HomeIcon }]
    : []),
  ...(props.inmueble.banos
    ? [{ label: 'Baños', value: props.inmueble.banos, icon: HomeIcon }]
    : []),
  { label: 'Torre', value: props.inmueble.torre, icon: BuildingOfficeIcon },
  { label: 'Piso', value: props.inmueble.piso, icon: BuildingOfficeIcon },
  { label: 'Parqueaderos', value: props.inmueble.parqueaderos, icon: HomeIcon },
]

// Desglose de precio
const desglosePrecio = [
  { label: 'Valor Base', value: props.inmueble.valor_base },
  ...(props.inmueble.prima_altura > 0
    ? [{ label: 'Prima por Altura', value: props.inmueble.prima_altura }]
    : []),
  ...(props.inmueble.valor_politica > 0
    ? [{ label: 'Ajuste por Política', value: props.inmueble.valor_politica }]
    : []),
]
</script>
