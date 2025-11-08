<template>
  <SidebarBannerLayout :empleado="empleado">
    <div class="flex min-h-screen bg-brand-50">
      <div class="flex-1 flex flex-col">
        <main class="p-8 overflow-auto max-w-6xl mx-auto w-full">
          <!-- Encabezado con botón editar -->
          <div class="flex justify-between items-center mb-6">
            <h2 class="text-3xl font-bold text-gray-900">Proyecto: {{ proyecto.nombre }}</h2>
            <Link
              :href="`/proyectos/${proyecto.id_proyecto}/edit`"
              class="inline-flex items-center gap-2 rounded bg-brand-500 px-5 py-3 text-white font-semibold shadow hover:bg-brand-600 transition"
            >
              <PencilIcon class="w-5 h-5" />
              Editar
            </Link>
          </div>

          <!-- Tabs -->
          <div class="mb-6 border-b border-gray-200">
            <nav class="-mb-px flex gap-6">
              <button @click="goTab('detalle')" :class="tabClass('detalle')">Detalle</button>
              <button @click="goTab('torres')" :class="tabClass('torres')">Torres</button>
              <!-- Futuras pestañas: Pisos, Apartamentos, etc. -->
            </nav>
          </div>

          <!-- Tab: Detalle -->
          <div v-if="currentTab === 'detalle'" class="bg-white rounded-lg shadow p-6 space-y-6">
            <div>
              <h3 class="text-lg font-semibold text-gray-700 mb-2">Descripción</h3>
              <p class="text-gray-900 whitespace-pre-line">{{ proyecto.descripcion || '-' }}</p>
            </div>

            <div class="grid grid-cols-2 gap-6">
              <div>
                <h3 class="text-lg font-semibold text-gray-700 mb-2">Estado</h3>
                <p class="text-gray-900">{{ proyecto.estado_proyecto?.nombre || '-' }}</p>
              </div>
              <div>
                <h3 class="text-lg font-semibold text-gray-700 mb-2">Ubicación</h3>
                <p class="text-gray-900">
                  {{ proyecto.ubicacion?.direccion || '-' }},
                  {{ proyecto.ubicacion?.ciudad?.nombre || '-' }}
                </p>
              </div>
            </div>

            <div class="grid grid-cols-2 gap-6">
              <div>
                <h3 class="text-lg font-semibold text-gray-700 mb-2">Fecha Inicio</h3>
                <p class="text-gray-900">{{ proyecto.fecha_inicio || '-' }}</p>
              </div>
              <div>
                <h3 class="text-lg font-semibold text-gray-700 mb-2">Fecha Finalización</h3>
                <p class="text-gray-900">{{ proyecto.fecha_finalizacion || '-' }}</p>
              </div>
            </div>

            <div class="grid grid-cols-3 gap-6">
              <div>
                <h3 class="text-lg font-semibold text-gray-700 mb-2">Presupuesto Inicial</h3>
                <p class="text-gray-900">{{ formatCurrency(proyecto.presupuesto_inicial) }}</p>
              </div>
              <div>
                <h3 class="text-lg font-semibold text-gray-700 mb-2">Presupuesto Final</h3>
                <p class="text-gray-900">{{ formatCurrency(proyecto.presupuesto_final) }}</p>
              </div>
              <div>
                <h3 class="text-lg font-semibold text-gray-700 mb-2">Metros Construidos</h3>
                <p class="text-gray-900">{{ proyecto.metros_construidos || '-' }}</p>
              </div>
            </div>

            <div class="grid grid-cols-4 gap-6">
              <div>
                <h3 class="text-lg font-semibold text-gray-700 mb-2">Cantidad Locales</h3>
                <p class="text-gray-900">{{ proyecto.cantidad_locales || '-' }}</p>
              </div>
              <div>
                <h3 class="text-lg font-semibold text-gray-700 mb-2">Cantidad Apartamentos</h3>
                <p class="text-gray-900">{{ proyecto.cantidad_apartamentos || '-' }}</p>
              </div>
              <div>
                <h3 class="text-lg font-semibold text-gray-700 mb-2">Parqueaderos Vehículo</h3>
                <p class="text-gray-900">{{ proyecto.cantidad_parqueaderos_vehiculo || '-' }}</p>
              </div>
              <div>
                <h3 class="text-lg font-semibold text-gray-700 mb-2">Parqueaderos Moto</h3>
                <p class="text-gray-900">{{ proyecto.cantidad_parqueaderos_moto || '-' }}</p>
              </div>
            </div>

            <div class="grid grid-cols-3 gap-6">
              <div>
                <h3 class="text-lg font-semibold text-gray-700 mb-2">Estrato</h3>
                <p class="text-gray-900">{{ proyecto.estrato || '-' }}</p>
              </div>
              <div>
                <h3 class="text-lg font-semibold text-gray-700 mb-2">Número Pisos</h3>
                <p class="text-gray-900">{{ proyecto.numero_pisos || '-' }}</p>
              </div>
              <div>
                <h3 class="text-lg font-semibold text-gray-700 mb-2">Número Torres</h3>
                <p class="text-gray-900">{{ proyecto.numero_torres || '-' }}</p>
              </div>
              <div>
                <h3 class="text-lg font-semibold text-gray-700 mb-2">Prima Base Altura</h3>
                <p class="text-gray-900">{{ formatCurrency(proyecto.prima_altura_base) }}</p>
              </div>

              <div>
                <h3 class="text-lg font-semibold text-gray-700 mb-2">Incremento Prima Altura</h3>
                <p class="text-gray-900">{{ formatCurrency(proyecto.prima_altura_incremento) }}</p>
              </div>

              <div>
                <h3 class="text-lg font-semibold text-gray-700 mb-2">Prima Altura Activa</h3>
                <p class="text-gray-900">{{ proyecto.prima_altura_activa || '-' }}</p>
              </div>

              <div class="mt-6 pt-6 border-t">
                <div class="flex items-center justify-between mb-4">
                  <h3 class="text-lg font-semibold text-brand-900">Políticas de Precio</h3>
                  <Link
                    :href="`/politicas-precio-proyecto/crear?proyecto=${proyecto.id_proyecto}`"
                    class="btn-sm-primary"
                  >
                    + Nueva Política
                  </Link>
                </div>

                <div
                  v-if="proyecto.politicas_precio && proyecto.politicas_precio.length > 0"
                  class="space-y-3"
                >
                  <div
                    v-for="politica in proyecto.politicas_precio"
                    :key="politica.id_politica_precio"
                    class="border rounded-lg p-4 hover:bg-gray-50 transition"
                  >
                    <div class="flex items-start justify-between">
                      <div class="flex-1 grid grid-cols-4 md:grid-cols-2 gap-1">
                        <div>
                          <span class="text-xs text-gray-500">Ventas/Escalón</span>
                          <p class="font-medium">{{ politica.ventas_por_escalon ?? '—' }}</p>
                        </div>
                        <div>
                          <span class="text-xs text-gray-500">% Aumento</span>
                          <p class="font-medium">{{ parseFloat(politica.porcentaje_aumento) }}%</p>
                        </div>
                        <div>
                          <span class="text-xs text-gray-500">Aplica Desde</span>
                          <p class="font-medium">
                            {{ politica.aplica_desde?.split('T')[0] || 'No definida' }}
                          </p>
                        </div>
                        <div>
                          <span class="text-xs text-gray-500">Estado</span>
                          <p>
                            <span :class="politica.estado ? 'badge-success' : 'badge-warn'">
                              {{ politica.estado ? 'Activa' : 'Inactiva' }}
                            </span>
                          </p>
                        </div>
                      </div>
                      <div class="flex items-center gap-2 ml-4">
                        <Link
                          :href="`/politicas-precio-proyecto/${politica.id_politica_precio}`"
                          class="icon-btn-sm info"
                          title="Ver detalle"
                        >
                          <EyeIcon class="w-4 h-4" />
                        </Link>
                        <Link
                          :href="`/politicas-precio-proyecto/${politica.id_politica_precio}/editar`"
                          class="icon-btn-sm warn"
                          title="Editar"
                        >
                          <PencilSquareIcon class="w-4 h-4" />
                        </Link>
                      </div>
                    </div>
                  </div>
                </div>

                <div
                  v-else
                  class="text-center py-8 text-gray-500 text-sm border rounded-lg bg-gray-50"
                >
                  No hay políticas de precio configuradas para este proyecto
                </div>
              </div>
            </div>
          </div>

          <!-- Tab: Torres -->
          <div v-else-if="currentTab === 'torres'" class="space-y-6">
            <div class="bg-white rounded-lg shadow p-6">
              <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
                <h3 class="text-xl font-semibold">Torres del proyecto</h3>
                <div class="flex items-center gap-2 w-full sm:w-auto">
                  <input
                    v-model="search"
                    @keyup.enter="searchTorres"
                    type="text"
                    placeholder="Buscar torre..."
                    class="flex-1 sm:flex-none rounded-md border border-gray-300 px-4 py-2 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 w-full sm:w-72"
                  />
                  <button
                    @click="searchTorres"
                    class="rounded bg-brand-500 px-4 py-2 text-white font-semibold hover:bg-brand-600"
                  >
                    Buscar
                  </button>
                  <Link
                    :href="route('admin.torres.create', { id_proyecto: proyecto.id_proyecto })"
                    class="rounded bg-brand-600 px-4 py-2 text-white font-semibold hover:bg-brand-700"
                  >
                    Nueva Torre
                  </Link>
                </div>
              </div>

              <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                  <thead class="bg-gray-50">
                    <tr>
                      <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                        ID
                      </th>
                      <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                        Nombre
                      </th>
                      <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                        Estado
                      </th>
                      <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                        Pisos
                      </th>
                      <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">
                        Acciones
                      </th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-gray-100 bg-white">
                    <tr v-for="torre in torres.data" :key="torre.id_torre">
                      <td class="px-4 py-3 text-sm text-gray-700">{{ torre.id_torre }}</td>
                      <td class="px-4 py-3 text-sm text-gray-900 font-medium">
                        {{ torre.nombre_torre }}
                      </td>
                      <td class="px-4 py-3 text-sm">
                        <span
                          class="px-2 py-1 rounded text-xs font-semibold bg-brand-100 text-brand-800"
                        >
                          {{ torre.estado?.nombre || '-' }}
                        </span>
                      </td>
                      <td class="px-4 py-3 text-sm text-gray-700">
                        {{ torre.numero_pisos ?? '-' }}
                      </td>
                      <td class="px-4 py-3">
                        <div class="flex justify-end gap-2">
                          <Link
                            :href="route('admin.torres.show', torre.id_torre)"
                            class="px-3 py-1 rounded border text-gray-700 hover:bg-gray-50"
                            >Ver</Link
                          >
                          <Link
                            :href="route('admin.torres.edit', torre.id_torre)"
                            class="px-3 py-1 rounded border text-gray-700 hover:bg-gray-50"
                            >Editar</Link
                          >
                          <button
                            @click="confirmDelete(torre)"
                            class="px-3 py-1 rounded border border-red-300 text-red-600 hover:bg-red-50"
                          >
                            Eliminar
                          </button>
                        </div>
                      </td>
                    </tr>
                    <tr v-if="torres.data.length === 0">
                      <td colspan="5" class="px-4 py-6 text-center text-gray-500">
                        No hay torres registradas
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>

              <div v-if="torres.links && torres.links.length > 0" class="mt-6 flex justify-end">
                <nav class="flex items-center gap-1">
                  <template v-for="(link, idx) in torres.links" :key="idx">
                    <span
                      v-if="!link.url"
                      class="px-3 py-1 text-sm text-gray-400"
                      v-html="link.label"
                    ></span>
                    <Link
                      v-else
                      :href="link.url"
                      class="px-3 py-1 text-sm rounded border"
                      :class="
                        link.active
                          ? 'bg-brand-600 text-white border-brand-600'
                          : 'hover:bg-gray-50'
                      "
                      v-html="link.label"
                      preserve-state
                      replace
                    />
                  </template>
                </nav>
              </div>
            </div>

            <!-- Modal eliminación -->
            <transition name="fade">
              <div
                v-if="deleteModal.open"
                class="fixed inset-0 bg-black/40 flex items-center justify-center z-50"
              >
                <div class="bg-white rounded-lg shadow max-w-md w-full p-6">
                  <h3 class="text-lg font-semibold mb-2">Eliminar torre</h3>
                  <p class="text-gray-600 mb-6">
                    ¿Seguro que deseas eliminar la torre
                    <strong>{{ deleteModal.torre?.nombre_torre }}</strong
                    >?
                  </p>
                  <div class="flex justify-end gap-3">
                    <button
                      @click="deleteModal.open = false"
                      class="px-4 py-2 rounded border hover:bg-gray-50"
                    >
                      Cancelar
                    </button>
                    <button
                      @click="doDelete"
                      class="px-4 py-2 rounded bg-red-600 text-white hover:bg-red-700"
                    >
                      Eliminar
                    </button>
                  </div>
                </div>
              </div>
            </transition>
          </div>
        </main>
      </div>
    </div>
  </SidebarBannerLayout>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import { Link, usePage } from '@inertiajs/inertia-vue3'
import { Inertia } from '@inertiajs/inertia'
import SidebarBannerLayout from '@/Components/SidebarBannerLayout.vue'

import { PencilIcon, EyeIcon, PencilSquareIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  proyecto: Object,
  empleado: Object,
  tab: String,
  torres: Object,
  filters: Object,
})

function formatCurrency(val) {
  if (val === null || val === undefined) return '—'
  const num = Number(val)
  if (isNaN(num)) return '—'
  return new Intl.NumberFormat('es-CO', {
    style: 'currency',
    currency: 'COP',
    maximumFractionDigits: 0,
  }).format(num)
}

const empleadoCompleto = computed(() => {
  if (!props.empleado) return 'Usuario'
  return [props.empleado.nombre, props.empleado.apellido].filter(Boolean).join(' ')
})

const menuOpen = ref(false)
const sidebarOpen = ref(true)
function toggleMenu() {
  menuOpen.value = !menuOpen.value
}
function toggleSidebar() {
  sidebarOpen.value = !sidebarOpen.value
}
function closeMenu() {
  menuOpen.value = false
}
function handleClickOutside(event) {
  const menu = document.getElementById('user-menu')
  if (menu && !menu.contains(event.target)) closeMenu()
}
onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})
onBeforeUnmount(() => {
  document.removeEventListener('click', handleClickOutside)
})
function logout() {
  Inertia.post('/logout')
}

const currentRoute = computed(() => {
  const comp = usePage().component
  return typeof comp === 'string' ? comp.toLowerCase() : ''
})

// Tabs
const currentTab = ref(props.tab || 'detalle')
function goTab(t) {
  currentTab.value = t
  Inertia.get(
    route('admin.proyectos.show', props.proyecto.id_proyecto),
    { tab: t },
    { preserveState: true, replace: true }
  )
}
function tabClass(t) {
  return [
    'px-3 py-2 border-b-2 text-sm font-semibold',
    currentTab.value === t
      ? 'border-brand-600 text-brand-700'
      : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
  ]
}

// Torres: búsqueda y eliminación
const search = ref(props.filters?.search || '')
function searchTorres() {
  Inertia.get(
    route('admin.proyectos.show', props.proyecto.id_proyecto),
    {
      tab: 'torres',
      search: search.value,
    },
    { preserveState: true, replace: true }
  )
}

const deleteModal = ref({ open: false, torre: null })
function confirmDelete(torre) {
  deleteModal.value = { open: true, torre }
}
function doDelete() {
  if (!deleteModal.value.torre) return
  Inertia.delete(route('admin.torres.destroy', deleteModal.value.torre.id_torre), {
    onSuccess: () => {
      Inertia.get(
        route('admin.proyectos.show', props.proyecto.id_proyecto),
        { tab: 'torres' },
        { preserveState: true, replace: true }
      )
    },
    onFinish: () => {
      deleteModal.value = { open: false, torre: null }
    },
  })
}
</script>

<style scoped>
/* Transición para menú */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

/* Transición para sidebar */
nav {
  transition-property: width;
  transition-duration: 300ms;
  transition-timing-function: ease;
}

.btn-sm-primary {
  @apply inline-flex items-center gap-1 px-3 py-1.5 text-sm rounded-md bg-brand-600 text-white hover:bg-brand-700;
}
.icon-btn-sm {
  @apply inline-flex items-center justify-center w-8 h-8 rounded-md border transition;
}
.icon-btn-sm.info {
  @apply border-brand-300 text-brand-700 hover:bg-brand-50;
}
.icon-btn-sm.warn {
  @apply border-amber-300 text-amber-700 hover:bg-amber-50;
}
.badge-success {
  @apply inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800;
}
.badge-warn {
  @apply inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-amber-100 text-amber-800;
}
</style>
