<template>
  <VentasLayout :empleado="empleado">
    <Head title="Catálogo de Inmuebles" />

    <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100">
      <!-- Header -->
      <div class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
          <div class="flex items-center justify-between">
            <div>
              <h1 class="text-3xl font-bold text-gray-900">Catálogo de Inmuebles</h1>
              <p class="mt-1 text-sm text-gray-600">Explora nuestros proyectos disponibles</p>
            </div>
            <button
              @click="showFilters = !showFilters"
              class="inline-flex items-center px-4 py-2 bg-[#1e3a5f] text-white rounded-lg hover:bg-[#2c5282] transition-colors"
            >
              <FunnelIcon class="h-5 w-5 mr-2" />
              Filtros
            </button>
          </div>
        </div>
      </div>

      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Estadísticas -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
          <div
            v-for="stat in stats"
            :key="stat.label"
            class="bg-white rounded-xl shadow-sm p-6 border border-gray-200 hover:shadow-md transition-shadow"
          >
            <div class="flex items-center">
              <div :class="[stat.color, 'p-3 rounded-lg']">
                <component :is="stat.icon" class="h-6 w-6 text-white" />
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">{{ stat.label }}</p>
                <p class="text-2xl font-bold text-gray-900">{{ stat.value }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Panel de Filtros -->
        <div
          v-if="showFilters"
          class="bg-white rounded-xl shadow-sm p-6 mb-8 border border-gray-200"
        >
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Filtros de Búsqueda</h3>
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Proyecto -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Proyecto</label>
              <select
                v-model="selectedProyecto"
                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1e3a5f] focus:ring focus:ring-[#1e3a5f] focus:ring-opacity-50"
              >
                <option value="">Todos los proyectos</option>
                <option
                  v-for="proyecto in proyectos"
                  :key="proyecto.id_proyecto"
                  :value="proyecto.id_proyecto"
                >
                  {{ proyecto.nombre }}
                </option>
              </select>
            </div>

            <!-- Tipo -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de Inmueble</label>
              <select
                v-model="selectedTipo"
                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1e3a5f] focus:ring focus:ring-[#1e3a5f] focus:ring-opacity-50"
              >
                <option value="">Todos</option>
                <option value="apartamento">Apartamentos</option>
                <option value="local">Locales</option>
              </select>
            </div>

            <!-- Precio Mínimo -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Precio Mínimo</label>
              <input
                v-model="precioMin"
                type="number"
                placeholder="$ 0"
                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1e3a5f] focus:ring focus:ring-[#1e3a5f] focus:ring-opacity-50"
              />
            </div>

            <!-- Precio Máximo -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Precio Máximo</label>
              <input
                v-model="precioMax"
                type="number"
                placeholder="$ 999,999,999"
                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1e3a5f] focus:ring focus:ring-[#1e3a5f] focus:ring-opacity-50"
              />
            </div>
          </div>

          <div class="flex justify-end gap-3 mt-6">
            <button
              @click="clearFilters"
              class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors"
            >
              Limpiar
            </button>
            <button
              @click="applyFilters"
              class="px-4 py-2 bg-[#1e3a5f] text-white rounded-lg hover:bg-[#2c5282] transition-colors"
            >
              Aplicar Filtros
            </button>
          </div>
        </div>

        <!-- Barra de Búsqueda -->
        <div class="mb-6">
          <div class="relative">
            <MagnifyingGlassIcon
              class="absolute left-3 top-1/2 transform -translate-y-1/2 h-5 w-5 text-gray-400"
            />
            <input
              v-model="searchTerm"
              type="text"
              placeholder="Buscar por número, proyecto o tipo..."
              class="w-full pl-10 pr-4 py-3 rounded-lg border-gray-300 shadow-sm focus:border-[#1e3a5f] focus:ring focus:ring-[#1e3a5f] focus:ring-opacity-50"
            />
          </div>
        </div>

        <!-- Grid de Inmuebles -->
        <div
          v-if="filteredInmuebles.length > 0"
          class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6"
        >
          <Link
            v-for="inmueble in filteredInmuebles"
            :key="`${inmueble.tipo}-${inmueble.id}`"
            :href="`/catalogo/${inmueble.tipo}/${inmueble.id}`"
            class="bg-white rounded-xl shadow-sm hover:shadow-xl transition-all duration-300 overflow-hidden border border-gray-200 group"
          >
            <!-- Header Card -->
            <div
              :class="[
                inmueble.tipo === 'apartamento'
                  ? 'bg-gradient-to-r from-blue-500 to-blue-600'
                  : 'bg-gradient-to-r from-purple-500 to-purple-600',
                'p-4',
              ]"
            >
              <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2">
                  <BuildingOfficeIcon class="h-6 w-6 text-white" />
                  <span class="text-white font-semibold text-lg"
                    >{{ inmueble.tipo === 'apartamento' ? 'Apto' : 'Local' }}
                    {{ inmueble.numero }}</span
                  >
                </div>
                <span
                  class="bg-white/20 backdrop-blur-sm text-white px-3 py-1 rounded-full text-xs font-medium"
                >
                  {{ inmueble.estado }}
                </span>
              </div>
            </div>

            <!-- Body Card -->
            <div class="p-6">
              <h3
                class="text-xl font-bold text-gray-900 mb-2 group-hover:text-[#1e3a5f] transition-colors"
              >
                {{ inmueble.proyecto }}
              </h3>

              <div class="space-y-2 mb-4">
                <div class="flex items-center text-sm text-gray-600">
                  <MapPinIcon class="h-4 w-4 mr-2 text-gray-400" />
                  {{ inmueble.ubicacion }}
                </div>
                <div class="flex items-center text-sm text-gray-600">
                  <BuildingOfficeIcon class="h-4 w-4 mr-2 text-gray-400" />
                  Torre {{ inmueble.torre }} - Piso {{ inmueble.piso }}
                </div>
              </div>

              <!-- Características -->
              <div class="bg-gray-50 rounded-lg p-4 mb-4">
                <p class="text-sm font-semibold text-gray-700 mb-2">{{ inmueble.tipo_inmueble }}</p>
                <div class="grid grid-cols-2 gap-2 text-sm text-gray-600">
                  <div>
                    <span class="font-medium">Área:</span> {{ inmueble.area_construida }} m²
                  </div>
                  <div v-if="inmueble.habitaciones">
                    <span class="font-medium">Hab:</span> {{ inmueble.habitaciones }}
                  </div>
                  <div v-if="inmueble.banos">
                    <span class="font-medium">Baños:</span> {{ inmueble.banos }}
                  </div>
                </div>
              </div>

              <!-- Precio -->
              <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                <div>
                  <p class="text-xs text-gray-500">Precio</p>
                  <p class="text-2xl font-bold text-[#1e3a5f]">
                    {{ formatCurrency(inmueble.valor) }}
                  </p>
                </div>
                <button
                  class="bg-[#f4c430] text-gray-900 px-4 py-2 rounded-lg font-semibold hover:bg-[#e5b520] transition-colors"
                >
                  Ver Detalles
                </button>
              </div>
            </div>
          </Link>
        </div>

        <!-- Sin Resultados -->
        <div v-else class="bg-white rounded-xl shadow-sm p-12 text-center border border-gray-200">
          <HomeIcon class="h-16 w-16 text-gray-400 mx-auto mb-4" />
          <h3 class="text-xl font-semibold text-gray-900 mb-2">No hay inmuebles disponibles</h3>
          <p class="text-gray-600 mb-6">
            No se encontraron inmuebles que coincidan con los filtros seleccionados.
          </p>
          <button
            @click="clearFilters"
            class="px-6 py-3 bg-[#1e3a5f] text-white rounded-lg hover:bg-[#2c5282] transition-colors"
          >
            Limpiar Filtros
          </button>
        </div>
      </div>
    </div>
  </VentasLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import VentasLayout from '@/Components/VentasLayout.vue'
import {
  MagnifyingGlassIcon,
  FunnelIcon,
  HomeIcon,
  BuildingOfficeIcon,
  MapPinIcon,
  CurrencyDollarIcon,
} from '@heroicons/vue/24/outline'

const props = defineProps({
  proyectos: Array,
  inmuebles: Array,
  empleado: Object,
  filters: Object,
})

// Filtros locales
const searchTerm = ref('')
const selectedProyecto = ref(props.filters?.proyecto || '')
const selectedTipo = ref(props.filters?.tipo || '')
const precioMin = ref(props.filters?.precio_min || '')
const precioMax = ref(props.filters?.precio_max || '')
const showFilters = ref(false)

// Estadísticas
const stats = computed(() => {
  const total = props.inmuebles?.length || 0
  const apartamentos = props.inmuebles?.filter((i) => i.tipo === 'apartamento').length || 0
  const locales = props.inmuebles?.filter((i) => i.tipo === 'local').length || 0
  const precioPromedio =
    total > 0 ? props.inmuebles.reduce((sum, i) => sum + parseFloat(i.valor), 0) / total : 0

  return [
    { label: 'Total Disponibles', value: total, icon: HomeIcon, color: 'bg-blue-500' },
    { label: 'Apartamentos', value: apartamentos, icon: BuildingOfficeIcon, color: 'bg-green-500' },
    { label: 'Locales', value: locales, icon: BuildingOfficeIcon, color: 'bg-purple-500' },
    {
      label: 'Precio Promedio',
      value: formatCurrency(precioPromedio),
      icon: CurrencyDollarIcon,
      color: 'bg-yellow-500',
    },
  ]
})

// Filtrar inmuebles
const filteredInmuebles = computed(() => {
  if (!props.inmuebles) return []

  return props.inmuebles.filter((inmueble) => {
    const matchSearch =
      searchTerm.value === '' ||
      inmueble.numero.toLowerCase().includes(searchTerm.value.toLowerCase()) ||
      inmueble.proyecto.toLowerCase().includes(searchTerm.value.toLowerCase()) ||
      inmueble.tipo_inmueble.toLowerCase().includes(searchTerm.value.toLowerCase())

    return matchSearch
  })
})

// Aplicar filtros
const applyFilters = () => {
  router.get(
    '/catalogo',
    {
      proyecto: selectedProyecto.value,
      tipo: selectedTipo.value,
      precio_min: precioMin.value,
      precio_max: precioMax.value,
    },
    {
      preserveState: true,
      preserveScroll: true,
    }
  )
}

// Limpiar filtros
const clearFilters = () => {
  selectedProyecto.value = ''
  selectedTipo.value = ''
  precioMin.value = ''
  precioMax.value = ''
  router.get('/catalogo')
}

// Formatear moneda
const formatCurrency = (value) => {
  return new Intl.NumberFormat('es-CO', {
    style: 'currency',
    currency: 'COP',
    minimumFractionDigits: 0,
  }).format(value)
}
</script>