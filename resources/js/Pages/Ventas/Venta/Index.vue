<script setup>
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import { ref, computed, watch } from 'vue'
import VentasLayout from '@/Components/VentasLayout.vue'
import FlashMessages from '@/Components/FlashMessages.vue'
import ConfirmDialog from '@/Components/ConfirmDialog.vue'
import {
  EyeIcon,
  PlusIcon,
  MagnifyingGlassIcon,
  FunnelIcon,
  CalendarDaysIcon,
  CurrencyDollarIcon,
  UserGroupIcon,
  BuildingOffice2Icon,
  ClockIcon,
  XMarkIcon,
  ArrowUpRightIcon,
  ChartBarIcon,
} from '@heroicons/vue/24/outline'

/** ===== Props ===== */
const props = defineProps({
  ventas: { type: Array, default: () => [] },
  empleado: Object,

  // DEBUG
  debug_proyecto: Object,
  debug_priceengine: Object,
  debug_venta: Object,
})

/** ===== DEBUG (toggle) ===== */
const debugEnabled = true // pon true si lo necesitas
const debug = {
  proyecto: props.debug_proyecto,
  pe: props.debug_priceengine,
  venta: props.debug_venta,
}

/** ===== Utils ===== */
function formatMoney(v) {
  return Number(v || 0).toLocaleString('es-CO', {
    style: 'currency',
    currency: 'COP',
    maximumFractionDigits: 0,
  })
}

function formatDate(date) {
  if (!date) return '—'
  return new Date(date).toLocaleDateString('es-CO', {
    year: 'numeric',
    month: 'short',
    day: '2-digit',
  })
}

function getInmuebleLabel(venta) {
  if (venta?.apartamento) return `Apto ${venta.apartamento.numero}`
  if (venta?.local) return `Local ${venta.local.numero}`
  return '—'
}

/** ===== UI State ===== */
const q = ref('')
const showFilters = ref(false)
const selectedProyecto = ref('')
const selectedTipoOperacion = ref('') // venta | separacion
const selectedEstado = ref('') // string
const dateFrom = ref('')
const dateTo = ref('')

/** ===== Modal eliminar ===== */
const showConfirmDialog = ref(false)
const ventaToDelete = ref(null)
const dialogOptions = ref({
  title: '¿Eliminar operación?',
  message: '',
  confirmText: 'Sí, eliminar',
  cancelText: 'Cancelar',
  variant: 'danger',
})

function confirmDelete(venta) {
  ventaToDelete.value = venta
  dialogOptions.value.message = `¿Seguro de eliminar la operación #${venta.id_venta}? Esta acción no se puede deshacer.`
  showConfirmDialog.value = true
}
function handleConfirm() {
  if (ventaToDelete.value?.id_venta) {
    router.delete(`/ventas/${ventaToDelete.value.id_venta}`, { preserveScroll: true })
  }
  showConfirmDialog.value = false
  ventaToDelete.value = null
}
function handleCancel() {
  showConfirmDialog.value = false
  ventaToDelete.value = null
}

/** ===== Derived data ===== */
const proyectosList = computed(() => {
  const map = new Map()
  props.ventas.forEach((v) => {
    if (v?.proyecto?.id_proyecto) {
      map.set(v.proyecto.id_proyecto, v.proyecto.nombre)
    }
  })
  return Array.from(map.entries())
    .map(([id, nombre]) => ({ id, nombre }))
    .sort((a, b) => a.nombre.localeCompare(b.nombre))
})

const estadosList = computed(() => {
  const set = new Set()
  props.ventas.forEach((v) => {
    const e = v?.apartamento?.estado_inmueble?.nombre || v?.local?.estado_inmueble?.nombre
    if (e) set.add(e)
  })
  return Array.from(set).sort((a, b) => a.localeCompare(b))
})

const filteredVentas = computed(() => {
  const term = q.value.toLowerCase().trim()
  const from = dateFrom.value ? new Date(dateFrom.value) : null
  const to = dateTo.value ? new Date(dateTo.value) : null

  return (props.ventas || []).filter((v) => {
    // search
    const cliente = String(v?.documento_cliente || '').toLowerCase()
    const proyecto = String(v?.proyecto?.nombre || '').toLowerCase()
    const inmueble = String(getInmuebleLabel(v)).toLowerCase()
    const matchSearch =
      !term || cliente.includes(term) || proyecto.includes(term) || inmueble.includes(term)

    // proyecto
    const matchProyecto =
      !selectedProyecto.value || v?.proyecto?.id_proyecto == selectedProyecto.value

    // tipo operacion
    const matchTipo =
      !selectedTipoOperacion.value || v?.tipo_operacion === selectedTipoOperacion.value

    // estado inmueble
    const est = v?.apartamento?.estado_inmueble?.nombre || v?.local?.estado_inmueble?.nombre || ''
    const matchEstado = !selectedEstado.value || est === selectedEstado.value

    // date
    const fv = v?.fecha_venta ? new Date(v.fecha_venta) : null
    const matchFrom = !from || (fv && fv >= from)
    const matchTo = !to || (fv && fv <= to)

    return matchSearch && matchProyecto && matchTipo && matchEstado && matchFrom && matchTo
  })
})

const totalOperaciones = computed(() => filteredVentas.value.length)
const totalValor = computed(() =>
  filteredVentas.value.reduce((sum, v) => sum + (Number(v?.valor_total) || 0), 0)
)
const ventasCount = computed(
  () => filteredVentas.value.filter((v) => v?.tipo_operacion === 'venta').length
)
const separacionesCount = computed(
  () => filteredVentas.value.filter((v) => v?.tipo_operacion === 'separacion').length
)

function badgeEstadoClass(venta) {
  const est = venta?.apartamento?.estado_inmueble?.nombre || venta?.local?.estado_inmueble?.nombre
  const map = {
    Disponible: 'bg-green-50 text-green-800 border-green-200',
    Separado: 'bg-blue-50 text-blue-800 border-blue-200',
    Promesa: 'bg-yellow-50 text-yellow-800 border-yellow-200',
    Cancelado: 'bg-red-50 text-red-800 border-red-200',
    Escriturado: 'bg-emerald-50 text-emerald-800 border-emerald-200',
    Entregado: 'bg-emerald-50 text-emerald-800 border-emerald-200',
    'En Financiación': 'bg-purple-50 text-purple-800 border-purple-200',
  }
  return map[est] || 'bg-gray-50 text-gray-800 border-gray-200'
}

function badgeTipoOperacionClass(tipo) {
  const map = {
    venta: 'bg-emerald-50 text-emerald-800 border-emerald-200',
    separacion: 'bg-blue-50 text-blue-800 border-blue-200',
  }
  return map[tipo] || 'bg-gray-50 text-gray-800 border-gray-200'
}

function clearFilters() {
  q.value = ''
  selectedProyecto.value = ''
  selectedTipoOperacion.value = ''
  selectedEstado.value = ''
  dateFrom.value = ''
  dateTo.value = ''
}
</script>

<template>
  <VentasLayout :empleado="empleado">
    <Head title="Ventas" />

    <!-- DEBUG -->
    <div v-if="debugEnabled" class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
      <div class="p-4 bg-blue-50 border border-blue-200 rounded-xl shadow-sm">
        <h3 class="text-blue-900 font-semibold mb-2">ProyectoPricingService</h3>
        <p><strong>Proyecto:</strong> {{ debug.proyecto?.nombre ?? '—' }}</p>
        <p><strong>Ventas activas:</strong> {{ debug.proyecto?.ventas_activas ?? '—' }}</p>
        <p><strong>Bloque actual:</strong> {{ debug.proyecto?.bloque_actual ?? '—' }}</p>
        <p><strong>Factor:</strong> {{ debug.proyecto?.factor ?? '—' }}</p>
        <hr class="my-2" />
        <p class="font-semibold">Políticas</p>
        <ul class="text-sm pl-3 list-disc">
          <li v-for="p in debug.proyecto?.politicas ?? []" :key="p.id">
            {{ p.aplica_desde }} → {{ p.porcentaje_aumento }}%
          </li>
        </ul>
      </div>

      <div class="p-4 bg-green-50 border border-green-200 rounded-xl shadow-sm">
        <h3 class="text-green-900 font-semibold mb-2">PriceEngine</h3>
        <p><strong>Bloque:</strong> {{ debug.pe?.bloque ?? '—' }}</p>
        <p><strong>Factor acumulado:</strong> {{ debug.pe?.factor ?? '—' }}</p>
        <hr class="my-2" />
        <p class="font-semibold">Políticas detectadas</p>
        <ul class="text-sm pl-3 list-disc">
          <li v-for="p in debug.pe?.politicas ?? []" :key="p.id">
            {{ p.ventas_por_escalon }} → {{ p.porcentaje_aumento }}%
          </li>
        </ul>
      </div>

      <div class="p-4 bg-yellow-50 border border-yellow-200 rounded-xl shadow-sm">
        <h3 class="text-yellow-900 font-semibold mb-2">VentaService</h3>
        <p><strong>Operación:</strong> {{ debug.venta?.tipo ?? '—' }}</p>
        <p><strong>Valor total:</strong> {{ formatMoney(debug.venta?.valor_total) }}</p>
        <p><strong>Cuota inicial:</strong> {{ formatMoney(debug.venta?.cuota_inicial) }}</p>
        <p><strong>Valor final aplicado:</strong> {{ formatMoney(debug.venta?.valor_final) }}</p>
        <hr class="my-2" />
        <p><strong>Estado asignado:</strong> {{ debug.venta?.estado_inmueble ?? '—' }}</p>
      </div>
    </div>

    <!-- Hero / Header -->
    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm overflow-hidden">
      <div class="bg-gradient-to-r from-[#FFEA00] to-[#FFF15C] px-6 py-6">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
          <div class="min-w-0">
            <div class="flex items-center gap-2 text-[#474100]">
              <ChartBarIcon class="w-6 h-6" />
              <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Ventas</h1>
            </div>
            <p class="mt-1 text-sm text-[#474100]/80">
              Gestiona operaciones, consulta estados y accede rápidamente a cada detalle.
            </p>
          </div>

          <div class="flex items-center gap-2">
            <button
              type="button"
              @click="showFilters = !showFilters"
              class="inline-flex items-center gap-2 rounded-xl border border-[#474100]/20 bg-white/70 px-4 py-2.5 text-sm font-semibold text-[#474100] hover:bg-white transition"
            >
              <FunnelIcon class="w-5 h-5" />
              Filtros
            </button>

            <Link
              href="/ventas/create"
              class="inline-flex items-center gap-2 rounded-xl bg-[#1e3a5f] px-4 py-2.5 text-sm font-semibold text-white hover:bg-[#2c5282] transition"
            >
              <PlusIcon class="w-5 h-5" />
              Nueva operación
            </Link>
          </div>
        </div>

        <!-- Search -->
        <div class="mt-5">
          <div class="relative">
            <MagnifyingGlassIcon
              class="w-5 h-5 text-gray-500 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none"
            />
            <input
              v-model="q"
              type="text"
              placeholder="Buscar por cliente (documento), inmueble o proyecto…"
              class="w-full rounded-xl border border-white/70 bg-white/80 pl-10 pr-3 py-3 text-sm text-gray-900 placeholder:text-gray-500 focus:outline-none focus:ring-2 focus:ring-[#1e3a5f] focus:border-transparent transition"
            />
          </div>
        </div>
      </div>

      <!-- KPIs -->
      <div class="px-6 py-5 bg-white">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
          <div class="rounded-2xl border border-gray-200 bg-gray-50 p-4">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">
                  Operaciones
                </p>
                <p class="mt-1 text-2xl font-extrabold text-gray-900">{{ totalOperaciones }}</p>
              </div>
              <div class="rounded-xl bg-white border border-gray-200 p-2">
                <UserGroupIcon class="w-6 h-6 text-[#1e3a5f]" />
              </div>
            </div>
          </div>

          <!-- <div class="rounded-2xl border border-gray-200 bg-gray-50 p-4">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">
                  Valor total
                </p>
                <p class="mt-1 text-xl sm:text-2xl font-extrabold text-gray-900">
                  {{ formatMoney(totalValor) }}
                </p>
              </div>
              <div class="rounded-xl bg-white border border-gray-200 p-2">
                <CurrencyDollarIcon class="w-6 h-6 text-[#1e3a5f]" />
              </div>
            </div>
          </div> -->

          <div class="rounded-2xl border border-gray-200 bg-gray-50 p-4">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Ventas</p>
                <p class="mt-1 text-2xl font-extrabold text-gray-900">{{ ventasCount }}</p>
              </div>
              <div class="rounded-xl bg-white border border-gray-200 p-2">
                <ArrowUpRightIcon class="w-6 h-6 text-emerald-600" />
              </div>
            </div>
          </div>

          <div class="rounded-2xl border border-gray-200 bg-gray-50 p-4">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">
                  Separaciones
                </p>
                <p class="mt-1 text-2xl font-extrabold text-gray-900">{{ separacionesCount }}</p>
              </div>
              <div class="rounded-xl bg-white border border-gray-200 p-2">
                <ClockIcon class="w-6 h-6 text-blue-600" />
              </div>
            </div>
          </div>
        </div>

        <!-- Filtros panel -->
        <div v-if="showFilters" class="mt-5 rounded-2xl border border-gray-200 bg-white p-4">
          <div class="flex items-center justify-between gap-3 mb-3">
            <p class="text-sm font-semibold text-gray-900">Filtros</p>
            <button
              type="button"
              @click="showFilters = false"
              class="inline-flex items-center gap-2 text-sm font-semibold text-gray-600 hover:text-gray-900"
            >
              <XMarkIcon class="w-5 h-5" />
              Cerrar
            </button>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-3">
            <div>
              <label class="block text-xs font-semibold text-gray-600 mb-1">Proyecto</label>
              <select
                v-model="selectedProyecto"
                class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1e3a5f] focus:border-transparent"
              >
                <option value="">Todos</option>
                <option v-for="p in proyectosList" :key="p.id" :value="p.id">
                  {{ p.nombre }}
                </option>
              </select>
            </div>

            <div>
              <label class="block text-xs font-semibold text-gray-600 mb-1">Tipo</label>
              <select
                v-model="selectedTipoOperacion"
                class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1e3a5f] focus:border-transparent"
              >
                <option value="">Todos</option>
                <option value="venta">Venta</option>
                <option value="separacion">Separación</option>
              </select>
            </div>

            <div>
              <label class="block text-xs font-semibold text-gray-600 mb-1">Estado inmueble</label>
              <select
                v-model="selectedEstado"
                class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1e3a5f] focus:border-transparent"
              >
                <option value="">Todos</option>
                <option v-for="e in estadosList" :key="e" :value="e">{{ e }}</option>
              </select>
            </div>

            <div>
              <label class="block text-xs font-semibold text-gray-600 mb-1">Desde</label>
              <input
                v-model="dateFrom"
                type="date"
                class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1e3a5f] focus:border-transparent"
              />
            </div>

            <div>
              <label class="block text-xs font-semibold text-gray-600 mb-1">Hasta</label>
              <input
                v-model="dateTo"
                type="date"
                class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1e3a5f] focus:border-transparent"
              />
            </div>
          </div>

          <div class="flex items-center justify-end gap-2 mt-4">
            <button
              type="button"
              @click="clearFilters"
              class="rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-800 hover:bg-gray-50 transition"
            >
              Limpiar
            </button>
          </div>
        </div>
      </div>

      <!-- Tabla -->
      <div class="border-t border-gray-200">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr class="text-xs font-semibold text-gray-600 uppercase tracking-wide">
                <th class="px-5 py-3 text-left">Cliente</th>
                <th class="px-5 py-3 text-left">Inmueble</th>
                <th class="px-5 py-3 text-left">Proyecto</th>
                <th class="px-5 py-3 text-left">Fecha</th>
                <th class="px-5 py-3 text-left">Tipo</th>
                <th class="px-5 py-3 text-left">Valor</th>
                <th class="px-5 py-3 text-left">Frecuencia Pagos</th>
                <th class="px-5 py-3 text-left">Cuotas (CI)</th>
                <th class="px-5 py-3 text-left">Estado</th>
                <th class="px-5 py-3 text-right">Acciones</th>
              </tr>
            </thead>

            <tbody class="divide-y divide-gray-100 bg-white">
              <tr
                v-for="venta in filteredVentas"
                :key="venta.id_venta"
                class="hover:bg-gray-50 transition"
              >
                <td class="px-5 py-4">
                  <div class="flex items-center gap-3">
                    <div
                      class="w-10 h-10 rounded-xl bg-[#1e3a5f]/10 flex items-center justify-center"
                    >
                      <UserGroupIcon class="w-5 h-5 text-[#1e3a5f]" />
                    </div>
                    <div class="min-w-0">
                      <p class="text-sm font-semibold text-gray-900 truncate">
                        {{ venta.cliente?.nombre || 'Cliente' }}
                      </p>
                      <p class="text-xs text-gray-500 truncate">
                        {{ venta.documento_cliente || '—' }}
                      </p>
                    </div>
                  </div>
                </td>

                <td class="px-5 py-4 text-sm text-gray-900 font-semibold">
                  {{ getInmuebleLabel(venta) }}
                </td>

                <td class="px-5 py-4">
                  <div class="flex items-center gap-2 text-sm text-gray-900">
                    <BuildingOffice2Icon class="w-5 h-5 text-gray-400" />
                    <span class="truncate">{{ venta.proyecto?.nombre ?? '—' }}</span>
                  </div>
                </td>

                <td class="px-5 py-4 text-sm text-gray-700">
                  <div class="inline-flex items-center gap-2">
                    <CalendarDaysIcon class="w-5 h-5 text-gray-400" />
                    <span>{{ formatDate(venta.fecha_venta) }}</span>
                  </div>
                </td>

                <td class="px-5 py-4">
                  <span
                    class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-semibold"
                    :class="badgeTipoOperacionClass(venta.tipo_operacion)"
                  >
                    {{ venta.tipo_operacion === 'separacion' ? 'Separación' : 'Venta' }}
                  </span>
                </td>

                <td class="px-5 py-4">
                  <p class="text-sm font-extrabold text-[#1e3a5f]">
                    {{ formatMoney(venta.valor_total) }}
                  </p>
                </td>

                <td class="px-5 py-4 text-sm text-gray-700">
                  {{ venta.frecuencia_cuota_inicial_meses ?? '—' }} meses
                </td>

                <td class="px-5 py-4 text-sm text-gray-700">
                  {{ venta.plazo_cuota_inicial_meses ?? '—' }} meses
                </td>

                <td class="px-5 py-4">
                  <span
                    class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-semibold"
                    :class="badgeEstadoClass(venta)"
                  >
                    {{
                      venta.apartamento?.estado_inmueble?.nombre ||
                      venta.local?.estado_inmueble?.nombre ||
                      '—'
                    }}
                  </span>
                </td>

                <td class="px-5 py-4">
                  <div class="flex items-center justify-end gap-2">
                    <Link
                      :href="`/ventas/${venta.id_venta}`"
                      class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm font-semibold text-gray-800 hover:bg-gray-50 transition"
                      title="Ver"
                    >
                      <EyeIcon class="w-5 h-5 text-blue-700" />
                      <span class="hidden sm:inline">Ver</span>
                    </Link>

                    <!-- Si luego quieres reactivar eliminar con modal -->
                    <!--
                    <button
                      type="button"
                      @click="confirmDelete(venta)"
                      class="inline-flex items-center gap-2 rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-sm font-semibold text-red-700 hover:bg-red-100 transition"
                      title="Eliminar"
                    >
                      <TrashIcon class="w-5 h-5" />
                      <span class="hidden sm:inline">Eliminar</span>
                    </button>
                    -->
                  </div>
                </td>
              </tr>

              <tr v-if="filteredVentas.length === 0">
                <td colspan="10" class="px-6 py-12">
                  <div class="text-center">
                    <p class="text-sm font-semibold text-gray-900">Sin resultados</p>
                    <p class="mt-1 text-sm text-gray-600">
                      No hay operaciones que coincidan con los filtros o la búsqueda.
                    </p>
                    <button
                      type="button"
                      @click="clearFilters"
                      class="mt-4 inline-flex items-center justify-center rounded-xl bg-[#1e3a5f] px-4 py-2.5 text-sm font-semibold text-white hover:bg-[#2c5282] transition"
                    >
                      Limpiar búsqueda
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <FlashMessages />

    <!-- Modal eliminar (si lo reactivas) -->
    <ConfirmDialog
      :open="showConfirmDialog"
      :title="dialogOptions.title"
      :message="dialogOptions.message"
      :confirm-text="dialogOptions.confirmText"
      :cancel-text="dialogOptions.cancelText"
      :variant="dialogOptions.variant"
      @confirm="handleConfirm"
      @cancel="handleCancel"
    />
  </VentasLayout>
</template>
