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

          <!-- Detalles del Proyecto -->
          <div class="bg-white rounded-lg shadow p-6 space-y-6">
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
            </div>

            <!-- Políticas de Precio -->
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
