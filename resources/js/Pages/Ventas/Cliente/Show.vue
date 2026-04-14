<template>
  <VentasLayout :empleado="empleado">
    <template #title>Detalle del Cliente</template>

    <VentasPageHeader
      title="Información del Cliente"
      :subtitle="`Detalles completos de ${cliente.nombre}`"
      :icon="UserIcon"
    />

    <div class="flex items-center gap-3 mb-6">
      <Link
        href="/clientes"
        class="inline-flex items-center gap-2 px-4 py-2 border border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-50 transition"
      >
        <ArrowLeftIcon class="w-5 h-5" /> Volver
      </Link>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <div class="lg:col-span-2 space-y-6">
        <!-- Datos Personales -->
        <VentasCard>
          <template #header>
            <h3 class="text-lg font-semibold text-gray-900">Datos Personales</h3>
          </template>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-500 mb-1">Nombre Completo</label>
              <p class="text-base font-semibold text-gray-900">{{ cliente.nombre }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-500 mb-1">Tipo de Cliente</label>
              <span
                class="px-3 py-1 inline-flex text-sm font-semibold rounded-full"
                :class="getTipoClienteBadge(cliente.tipo_cliente?.tipo_cliente)"
              >
                {{ cliente.tipo_cliente?.tipo_cliente || '—' }}
              </span>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-500 mb-1">Tipo de Documento</label>
              <p class="text-base text-gray-900">
                {{ cliente.tipo_documento?.tipo_documento || '—' }}
              </p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-500 mb-1"
                >Número de Documento</label
              >
              <p class="text-base font-mono font-semibold text-gray-900">{{ cliente.documento }}</p>
            </div>
          </div>
        </VentasCard>

        <!-- Información de Contacto -->
        <VentasCard>
          <template #header>
            <h3 class="text-lg font-semibold text-gray-900">Información de Contacto</h3>
          </template>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-500 mb-1">Dirección</label>
              <p class="text-base text-gray-900">{{ cliente.direccion || 'No registrada' }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-500 mb-1">Teléfono</label>
              <p class="text-base text-gray-900">{{ cliente.telefono || 'No registrado' }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-500 mb-1">Correo Electrónico</label>
              <p class="text-base text-gray-900">{{ cliente.correo || 'No registrado' }}</p>
            </div>
          </div>
        </VentasCard>

        <!-- Bitácora de Seguimiento -->
        <VentasCard>
          <template #header>
            <div class="flex items-center justify-between">
              <h3 class="text-lg font-semibold text-gray-900">Bitácora de Seguimiento</h3>
              <span
                class="px-3 py-1 bg-[#FFFDE6] text-[#474100] text-sm font-semibold rounded-full"
              >
                {{ bitacorasOrdenadas.length }} registros
              </span>
            </div>
          </template>

          <!-- Formulario -->
          <div class="mb-5 p-3 rounded-xl bg-gray-50 border border-gray-200">
            <form @submit.prevent="submitBitacora" class="space-y-3">
              <div class="flex items-start justify-between gap-3 mb-3">
                <h4 class="text-sm font-semibold text-gray-900">Registrar seguimiento</h4>

                <button
                  type="submit"
                  :disabled="bitacoraForm.processing"
                  class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-[#FFEA00] to-[#D1C000] text-[#474100] text-sm font-semibold rounded-lg hover:shadow-lg transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed shrink-0"
                >
                  <svg
                    v-if="bitacoraForm.processing"
                    class="animate-spin h-4 w-4"
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                  >
                    <circle
                      class="opacity-25"
                      cx="12"
                      cy="12"
                      r="10"
                      stroke="currentColor"
                      stroke-width="4"
                    />
                    <path
                      class="opacity-75"
                      fill="currentColor"
                      d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                    />
                  </svg>

                  {{ bitacoraForm.processing ? 'Guardando...' : 'Registrar seguimiento' }}
                </button>
              </div>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Fecha <span class="text-red-500">*</span>
                  </label>
                  <input
                    v-model="bitacoraForm.fecha"
                    type="date"
                    :max="today"
                    class="w-full px-4 py-2.5 border rounded-lg transition focus:ring-2 focus:ring-[#FFEA00] focus:border-transparent"
                    :class="bitacoraForm.errors.fecha ? 'border-red-500' : 'border-gray-300'"
                  />
                  <p v-if="bitacoraForm.errors.fecha" class="text-xs text-red-500 mt-1">
                    {{ bitacoraForm.errors.fecha }}
                  </p>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2"> Asesor </label>
                  <input
                    :value="asesorSesion"
                    type="text"
                    disabled
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg bg-gray-100 text-gray-700 cursor-not-allowed"
                  />
                </div>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Comentario <span class="text-red-500">*</span>
                </label>
                <textarea
                  v-model="bitacoraForm.comentario"
                  rows="3"
                  maxlength="5000"
                  placeholder="Describe el contacto realizado, acuerdo, novedad o siguiente paso..."
                  class="w-full px-4 py-2.5 border rounded-lg transition focus:ring-2 focus:ring-[#FFEA00] focus:border-transparent resize-none"
                  :class="bitacoraForm.errors.comentario ? 'border-red-500' : 'border-gray-300'"
                />
                <div class="flex items-center justify-between mt-1">
                  <p v-if="bitacoraForm.errors.comentario" class="text-xs text-red-500">
                    {{ bitacoraForm.errors.comentario }}
                  </p>
                  <p v-else class="text-xs text-gray-400">
                    {{ bitacoraForm.comentario.length }}/5000
                  </p>
                </div>
              </div>
            </form>
          </div>
          <!-- Historial -->
          <div
            v-if="bitacorasOrdenadas.length > 0"
            class="max-h-[360px] overflow-y-auto pr-2 space-y-3 scroll-smooth"
          >
            <div
              v-for="bitacora in bitacorasOrdenadas"
              :key="bitacora.id_bitacora_cliente"
              class="relative pl-5"
            >
              <div
                class="absolute left-0 top-2 w-2.5 h-2.5 rounded-full bg-[#FFEA00] border border-[#D1C000]"
              />
              <div class="absolute left-[4px] top-4 bottom-[-12px] w-px bg-gray-200" />

              <div class="bg-white border border-gray-200 rounded-xl p-3 shadow-sm">
                <div
                  class="flex flex-col md:flex-row md:items-center md:justify-between gap-1.5 mb-2"
                >
                  <div class="flex flex-wrap items-center gap-x-2 gap-y-1 text-sm">
                    <span class="font-semibold text-gray-900">
                      {{ fullEmpleadoName(bitacora.empleado) }}
                    </span>
                    <span class="text-gray-300 hidden md:inline">•</span>
                    <span class="text-gray-600">
                      {{ formatDate(bitacora.fecha) }}
                    </span>
                  </div>

                  <p class="text-xs text-gray-500">
                    {{ formatDateTime(bitacora.created_at) }}
                  </p>
                </div>

                <div class="rounded-lg bg-gray-50 px-3 py-2.5">
                  <p class="text-sm text-gray-700 whitespace-pre-line leading-6">
                    {{ bitacora.comentario }}
                  </p>
                </div>
              </div>
            </div>
          </div>
          <div v-else class="text-center py-8 text-gray-500">
            <p>No hay seguimientos registrados para este cliente</p>
          </div>
        </VentasCard>

        <!-- Historial de Ventas -->
        <VentasCard>
          <template #header>
            <div class="flex items-center justify-between">
              <h3 class="text-lg font-semibold text-gray-900">Historial de Ventas</h3>
              <span
                class="px-3 py-1 bg-[#FFFDE6] text-[#474100] text-sm font-semibold rounded-full"
              >
                {{ cliente.ventas?.length || 0 }} operaciones
              </span>
            </div>
          </template>

          <div v-if="cliente.ventas && cliente.ventas.length > 0">
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th
                      class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                    >
                      Fecha
                    </th>
                    <th
                      class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                    >
                      Proyecto
                    </th>
                    <th
                      class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                    >
                      Inmueble
                    </th>
                    <th
                      class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider"
                    >
                      Valor total
                    </th>
                  </tr>
                </thead>

                <tbody class="bg-white divide-y divide-gray-200">
                  <tr
                    v-for="venta in ventasOrdenadas"
                    :key="venta.id_venta"
                    class="hover:bg-gray-50"
                  >
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                      {{ formatDateShort(venta.fecha_venta) }}
                    </td>

                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                      {{ venta.proyecto?.nombre || '—' }}
                    </td>

                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                      <span class="font-semibold">
                        {{ inmuebleLabel(venta) }}
                      </span>
                    </td>

                    <td
                      class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 text-right font-semibold"
                    >
                      {{ formatCurrency(venta.valor_total) }}
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <div class="mt-3 text-xs text-gray-500">
              Se muestran ventas y separaciones del cliente ordenadas por fecha (desc).
            </div>
          </div>

          <div v-else class="text-center py-8 text-gray-500">
            <p>No hay ventas registradas para este cliente</p>
          </div>
        </VentasCard>
      </div>

      <!-- Panel Lateral -->
      <div class="space-y-6">
        <VentasCard>
          <template #header>
            <h3 class="text-lg font-semibold text-gray-900">Resumen</h3>
          </template>

          <div class="space-y-4">
            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
              <span class="text-sm text-gray-600">Total Operaciones</span>
              <span class="text-lg font-bold text-gray-900">{{ cliente.ventas?.length || 0 }}</span>
            </div>

            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
              <span class="text-sm text-gray-600">Valor Total</span>
              <span class="text-lg font-bold text-gray-900">{{ formatCurrency(totalVentas) }}</span>
            </div>

            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
              <span class="text-sm text-gray-600">Registrado</span>
              <span class="text-sm text-gray-900">{{ formatDate(cliente.created_at) }}</span>
            </div>

            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
              <span class="text-sm text-gray-600">Asesor</span>
              <span class="text-sm text-gray-900">
                {{ cliente.asesor_responsable?.nombre || '—' }}
              </span>
            </div>

            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
              <span class="text-sm text-gray-600">Seguimientos</span>
              <span class="text-sm font-semibold text-gray-900">{{
                bitacorasOrdenadas.length
              }}</span>
            </div>

            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
              <span class="text-sm text-gray-600">Último contacto</span>
              <span class="text-sm text-gray-900">
                {{ ultimaBitacora ? formatDate(ultimaBitacora.fecha) : 'Sin registros' }}
              </span>
            </div>
          </div>
        </VentasCard>

        <VentasCard>
          <template #header>
            <h3 class="text-lg font-semibold text-gray-900">Acciones Rápidas</h3>
          </template>

          <div class="space-y-2">
            <Link
              href="/ventas/create"
              class="w-full inline-flex items-center justify-center gap-2 px-4 py-2 bg-gradient-to-r from-[#FFEA00] to-[#D1C000] text-[#474100] font-semibold rounded-lg hover:shadow-lg transition-all duration-200"
            >
              <PlusIcon class="w-5 h-5" /> Nueva Venta
            </Link>
          </div>
        </VentasCard>
      </div>
    </div>

    <FlashMessages />

    <ConfirmDialog
      :is-open="showConfirmDialog"
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

<script setup>
import { ref, computed, reactive } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import VentasLayout from '@/Components/VentasLayout.vue'
import FlashMessages from '@/Components/FlashMessages.vue'
import VentasPageHeader from '../Components/VentasPageHeader.vue'
import VentasCard from '../Components/VentasCard.vue'
import ConfirmDialog from '@/Components/ConfirmDialog.vue'
import { UserIcon, ArrowLeftIcon, PlusIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  cliente: { type: Object, required: true },
  empleado: { type: Object, default: null },
})

const showConfirmDialog = ref(false)
const dialogOptions = ref({
  title: '¿Eliminar Cliente?',
  message: `¿Está seguro de eliminar a ${props.cliente.nombre}? Esta acción no se puede deshacer y se eliminarán todos los datos asociados.`,
  confirmText: 'Sí, eliminar',
  cancelText: 'Cancelar',
  variant: 'danger',
})

const today = new Date().toISOString().split('T')[0]

const bitacoraForm = reactive({
  fecha: today,
  comentario: '',
  processing: false,
  errors: {},
})

const ventasOrdenadas = computed(() => {
  const arr = Array.isArray(props.cliente.ventas) ? [...props.cliente.ventas] : []
  return arr.sort((a, b) => {
    const da = a?.fecha_venta ? new Date(a.fecha_venta).getTime() : 0
    const db = b?.fecha_venta ? new Date(b.fecha_venta).getTime() : 0
    return db - da
  })
})

const bitacorasOrdenadas = computed(() => {
  const arr = Array.isArray(props.cliente.bitacoras) ? [...props.cliente.bitacoras] : []
  return arr.sort((a, b) => {
    const da = a?.fecha ? new Date(a.fecha).getTime() : 0
    const db = b?.fecha ? new Date(b.fecha).getTime() : 0
    if (db !== da) return db - da

    const ca = a?.created_at ? new Date(a.created_at).getTime() : 0
    const cb = b?.created_at ? new Date(b.created_at).getTime() : 0
    return cb - ca
  })
})

const ultimaBitacora = computed(() => {
  return bitacorasOrdenadas.value.length > 0 ? bitacorasOrdenadas.value[0] : null
})

const totalVentas = computed(() => {
  if (!props.cliente.ventas || props.cliente.ventas.length === 0) return 0
  return props.cliente.ventas.reduce((sum, v) => sum + (Number(v.valor_total) || 0), 0)
})

const asesorSesion = computed(() => {
  const nombre = props.empleado?.nombre || ''
  const apellido = props.empleado?.apellido || ''
  return `${nombre} ${apellido}`.trim() || 'No disponible'
})

function submitBitacora() {
  bitacoraForm.errors = {}

  if (!bitacoraForm.fecha) {
    bitacoraForm.errors.fecha = 'La fecha es obligatoria.'
  }

  if (!bitacoraForm.comentario.trim()) {
    bitacoraForm.errors.comentario = 'El comentario es obligatorio.'
  }

  if (Object.keys(bitacoraForm.errors).length > 0) return

  bitacoraForm.processing = true

  router.post(
    `/clientes/${props.cliente.documento}/bitacoras`,
    {
      fecha: bitacoraForm.fecha,
      comentario: bitacoraForm.comentario.trim(),
    },
    {
      preserveScroll: true,
      onSuccess: () => {
        bitacoraForm.fecha = today
        bitacoraForm.comentario = ''
        bitacoraForm.errors = {}
      },
      onError: (errors) => {
        bitacoraForm.errors = errors || {}
      },
      onFinish: () => {
        bitacoraForm.processing = false
      },
    }
  )
}

function fullEmpleadoName(empleado) {
  if (!empleado) return '—'
  const nombre = empleado.nombre || ''
  const apellido = empleado.apellido || ''
  return `${nombre} ${apellido}`.trim() || '—'
}

function inmuebleLabel(venta) {
  if (venta?.apartamento?.numero) return `Apto ${venta.apartamento.numero}`
  if (venta?.local?.numero) return `Local ${venta.local.numero}`
  return '—'
}

function getTipoClienteBadge(tipo) {
  const badges = {
    'Persona Natural': 'bg-blue-100 text-blue-800',
    'Persona Jurídica': 'bg-green-100 text-green-800',
    Inversionista: 'bg-purple-100 text-purple-800',
    Corporativo: 'bg-orange-100 text-orange-800',
  }
  return badges[tipo] || 'bg-gray-100 text-gray-800'
}

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

function formatDate(date) {
  if (!date) return '—'
  return new Date(date).toLocaleDateString('es-CO', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
  })
}

function formatDateShort(date) {
  if (!date) return '—'
  return new Date(date).toLocaleDateString('es-CO', {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
  })
}

function formatDateTime(date) {
  if (!date) return '—'
  return new Date(date).toLocaleString('es-CO', {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
    hour: '2-digit',
    minute: '2-digit',
  })
}

function confirmDelete() {
  showConfirmDialog.value = true
}

function handleConfirm() {
  router.delete(`/clientes/${props.cliente.documento}`)
  showConfirmDialog.value = false
}

function handleCancel() {
  showConfirmDialog.value = false
}
</script>
