<template>
  <VentasLayout :empleado="empleado">
    <template #title>Detalle del Cliente</template>

    <VentasPageHeader
      title="Información del Cliente"
      :subtitle="`Detalles completos de ${cliente.nombre}`"
      :icon="UserIcon"
    />

    <!-- Acciones Rápidas -->
    <div class="flex items-center gap-3 mb-6">
      <Link
        href="/clientes"
        class="inline-flex items-center gap-2 px-4 py-2 border border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-50 transition"
      >
        <ArrowLeftIcon class="w-5 h-5" /> Volver
      </Link>
      <!-- <Link
        :href="`/clientes/${cliente.documento}/edit`"
        class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-[#FFEA00] to-[#D1C000] text-[#474100] font-semibold rounded-lg hover:shadow-lg transition-all duration-200"
      >
        <PencilSquareIcon class="w-5 h-5" /> Editar
      </Link>
      <button
        @click="confirmDelete"
        class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 text-white font-semibold rounded-lg hover:bg-red-700 transition"
      >
        <TrashIcon class="w-5 h-5" /> Eliminar
      </button> -->
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Información Principal -->
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

        <!-- Historial de Ventas -->
        <VentasCard>
          <template #header>
            <div class="flex items-center justify-between">
              <h3 class="text-lg font-semibold text-gray-900">Historial de Ventas</h3>
              <span
                class="px-3 py-1 bg-[#FFFDE6] text-[#474100] text-sm font-semibold rounded-full"
              >
                {{ cliente.ventas?.length || 0 }} ventas
              </span>
            </div>
          </template>

          <div v-if="cliente.ventas && cliente.ventas.length > 0" class="space-y-4">
            <div
              v-for="venta in cliente.ventas"
              :key="venta.id_venta"
              class="p-4 border border-gray-200 rounded-lg hover:border-[#FFEA00] transition"
            >
              <div class="flex items-center justify-between mb-2">
                <h4 class="font-semibold text-gray-900">Venta #{{ venta.id_venta }}</h4>
                <span
                  class="px-2 py-1 text-xs font-semibold rounded-full"
                  :class="getEstadoVentaBadge(venta.estado_venta?.estado_venta)"
                >
                  {{ venta.estado_venta?.estado_venta || '—' }}
                </span>
              </div>
              <div class="grid grid-cols-2 gap-2 text-sm">
                <div>
                  <span class="text-gray-500">Fecha:</span>
                  <span class="ml-2 text-gray-900">{{ formatDate(venta.fecha_venta) }}</span>
                </div>
                <div>
                  <span class="text-gray-500">Valor Total:</span>
                  <span class="ml-2 font-semibold text-gray-900">{{
                    formatCurrency(venta.valor_total)
                  }}</span>
                </div>
              </div>
            </div>
          </div>
          <div v-else class="text-center py-8 text-gray-500">
            <p>No hay ventas registradas para este cliente</p>
          </div>
        </VentasCard>
      </div>

      <!-- Panel Lateral -->
      <div class="space-y-6">
        <!-- Resumen -->
        <VentasCard>
          <template #header>
            <h3 class="text-lg font-semibold text-gray-900">Resumen</h3>
          </template>

          <div class="space-y-4">
            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
              <span class="text-sm text-gray-600">Total Ventas</span>
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
          </div>
        </VentasCard>

        <!-- Acciones Rápidas -->
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
    <!-- Modal de Confirmación -->
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
import { ref, computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import VentasLayout from '@/Components/VentasLayout.vue'
import FlashMessages from '@/Components/FlashMessages.vue'
import VentasPageHeader from '../Components/VentasPageHeader.vue'
import VentasCard from '../Components/VentasCard.vue'
import ConfirmDialog from '@/Components/ConfirmDialog.vue'
import {
  UserIcon,
  ArrowLeftIcon,
  PencilSquareIcon,
  TrashIcon,
  PlusIcon,
} from '@heroicons/vue/24/outline'

const props = defineProps({
  cliente: { type: Object, required: true },
  empleado: { type: Object, default: null },
})

// Estado del modal de confirmación
const showConfirmDialog = ref(false)
const dialogOptions = ref({
  title: '¿Eliminar Cliente?',
  message: `¿Está seguro de eliminar a ${props.cliente.nombre}? Esta acción no se puede deshacer y se eliminarán todos los datos asociados.`,
  confirmText: 'Sí, eliminar',
  cancelText: 'Cancelar',
  variant: 'danger',
})

const totalVentas = computed(() => {
  if (!props.cliente.ventas || props.cliente.ventas.length === 0) return 0
  return props.cliente.ventas.reduce((sum, v) => sum + (Number(v.valor_total) || 0), 0)
})

function getTipoClienteBadge(tipo) {
  const badges = {
    'Persona Natural': 'bg-blue-100 text-blue-800',
    'Persona Jurídica': 'bg-green-100 text-green-800',
    Inversionista: 'bg-purple-100 text-purple-800',
    Corporativo: 'bg-orange-100 text-orange-800',
  }
  return badges[tipo] || 'bg-gray-100 text-gray-800'
}

function getEstadoVentaBadge(estado) {
  const badges = {
    Promesa: 'bg-yellow-100 text-yellow-800',
    Separado: 'bg-blue-100 text-blue-800',
    'En Financiación': 'bg-purple-100 text-purple-800',
    Escriturado: 'bg-green-100 text-green-800',
    Entregado: 'bg-emerald-100 text-emerald-800',
    Cancelado: 'bg-red-100 text-red-800',
  }
  return badges[estado] || 'bg-gray-100 text-gray-800'
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
