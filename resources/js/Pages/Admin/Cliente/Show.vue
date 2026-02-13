<!-- resources/js/Pages/Admin/Ventas/Clientes/Show.vue -->
<template>
  <TopBannerLayout :empleado="empleado" panel-name="Panel administrador">
    <Head title="Detalle del cliente" />

    <div class="space-y-6">
      <VentasPageHeader
        title="Información del cliente"
        :subtitle="`Detalles completos de ${cliente?.nombre || '—'}`"
        :icon="UserIcon"
      />

      <!-- Acciones -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div class="flex items-center gap-2">
          <Link
            href="/admin/clientes"
            class="inline-flex items-center gap-2 rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-800 hover:bg-gray-50 transition"
          >
            <ArrowLeftIcon class="w-5 h-5" />
            Volver
          </Link>

          <Link
            :href="`/admin/clientes/${cliente.documento}/edit`"
            class="inline-flex items-center gap-2 rounded-xl bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700 transition"
          >
            <PencilSquareIcon class="w-5 h-5" />
            Editar
          </Link>
        </div>

        <button
          type="button"
          @click="confirmDelete"
          class="inline-flex items-center justify-center gap-2 rounded-xl border border-red-200 bg-red-50 px-4 py-2 text-sm font-semibold text-red-700 hover:bg-red-100 transition"
        >
          <TrashIcon class="w-5 h-5" />
          Eliminar
        </button>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Principal -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Datos personales -->
          <VentasCard>
            <template #header>
              <div class="flex items-center justify-between gap-3">
                <h3 class="text-lg font-semibold text-gray-900">Datos personales</h3>

                <span
                  class="inline-flex items-center gap-2 rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-700"
                >
                  <span
                    class="h-2 w-2 rounded-full"
                    :class="tipoClienteDot(cliente?.tipo_cliente?.tipo_cliente)"
                  />
                  {{ cliente?.tipo_cliente?.tipo_cliente || '—' }}
                </span>
              </div>
            </template>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Nombre</p>
                <p class="mt-1 text-base font-semibold text-gray-900">
                  {{ cliente?.nombre || '—' }}
                </p>
              </div>

              <div>
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">
                  Tipo de documento
                </p>
                <p class="mt-1 text-base text-gray-900">
                  {{ cliente?.tipo_documento?.tipo_documento || '—' }}
                </p>
              </div>

              <div class="md:col-span-2">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Documento</p>
                <p class="mt-1 text-base font-mono font-semibold text-gray-900">
                  {{ cliente?.documento || '—' }}
                </p>
              </div>
            </div>
          </VentasCard>

          <!-- Contacto -->
          <VentasCard>
            <template #header>
              <h3 class="text-lg font-semibold text-gray-900">Información de contacto</h3>
            </template>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div class="md:col-span-2">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Dirección</p>
                <p class="mt-1 text-base text-gray-900">
                  {{ cliente?.direccion || 'No registrada' }}
                </p>
              </div>

              <div>
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Teléfono</p>
                <p class="mt-1 text-base text-gray-900">
                  {{ cliente?.telefono || 'No registrado' }}
                </p>
              </div>

              <div>
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Correo</p>
                <p class="mt-1 text-base text-gray-900">
                  {{ cliente?.correo || 'No registrado' }}
                </p>
              </div>
            </div>
          </VentasCard>

          <!-- Ventas -->
          <VentasCard>
            <template #header>
              <div class="flex items-center justify-between gap-3">
                <h3 class="text-lg font-semibold text-gray-900">Historial de ventas</h3>
                <span
                  class="rounded-full bg-brand-50 px-3 py-1 text-xs font-semibold text-brand-800"
                >
                  {{ cliente?.ventas?.length || 0 }} ventas
                </span>
              </div>
            </template>

            <div v-if="cliente?.ventas?.length" class="space-y-3">
              <div
                v-for="venta in cliente.ventas"
                :key="venta.id_venta"
                class="rounded-2xl border border-gray-200 p-4 hover:border-brand-300 transition"
              >
                <div class="flex items-start justify-between gap-3">
                  <div class="min-w-0">
                    <p class="text-sm font-semibold text-gray-900">Venta #{{ venta.id_venta }}</p>
                    <p class="mt-1 text-xs text-gray-600">
                      {{ formatDate(venta.fecha_venta) }}
                    </p>
                  </div>

                  <span
                    class="shrink-0 rounded-full px-2.5 py-1 text-xs font-semibold"
                    :class="getEstadoVentaBadge(venta.estado_venta?.estado_venta)"
                  >
                    {{ venta.estado_venta?.estado_venta || '—' }}
                  </span>
                </div>

                <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-2 text-sm">
                  <div class="flex items-center justify-between sm:justify-start sm:gap-2">
                    <span class="text-gray-500">Valor total:</span>
                    <span class="font-semibold text-gray-900">{{
                      formatCurrency(venta.valor_total)
                    }}</span>
                  </div>
                  <div class="flex items-center justify-between sm:justify-start sm:gap-2">
                    <span class="text-gray-500">Fecha:</span>
                    <span class="text-gray-900">{{ formatDate(venta.fecha_venta) }}</span>
                  </div>
                </div>
              </div>
            </div>

            <div v-else class="py-10 text-center text-sm text-gray-500">
              No hay ventas registradas para este cliente
            </div>
          </VentasCard>
        </div>

        <!-- Lateral -->
        <div class="space-y-6">
          <VentasCard>
            <template #header>
              <h3 class="text-lg font-semibold text-gray-900">Resumen</h3>
            </template>

            <div class="space-y-3">
              <div class="rounded-2xl border border-gray-200 p-4">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">
                  Total ventas
                </p>
                <p class="mt-1 text-2xl font-bold text-gray-900">
                  {{ cliente?.ventas?.length || 0 }}
                </p>
              </div>

              <div class="rounded-2xl border border-gray-200 p-4">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">
                  Valor total
                </p>
                <p class="mt-1 text-2xl font-bold text-gray-900">
                  {{ formatCurrency(totalVentas) }}
                </p>
              </div>

              <div class="rounded-2xl border border-gray-200 p-4">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">
                  Registrado
                </p>
                <p class="mt-1 text-sm font-semibold text-gray-900">
                  {{ formatDate(cliente?.created_at) }}
                </p>
              </div>
            </div>
          </VentasCard>
        </div>
      </div>

      <FlashMessages />

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
    </div>
  </TopBannerLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'

import TopBannerLayout from '@/Components/TopBannerLayout.vue'
import FlashMessages from '@/Components/FlashMessages.vue'
import VentasPageHeader from '@/Components/VentasPageHeader.vue'
import VentasCard from '@/Components/VentasCard.vue'
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

const showConfirmDialog = ref(false)

const dialogOptions = ref({
  title: '¿Eliminar cliente?',
  message: `¿Está seguro de eliminar a ${props.cliente.nombre}? Esta acción no se puede deshacer y se eliminarán todos los datos asociados.`,
  confirmText: 'Sí, eliminar',
  cancelText: 'Cancelar',
  variant: 'danger',
})

const totalVentas = computed(() => {
  const ventas = props.cliente?.ventas || []
  return ventas.reduce((sum, v) => sum + (Number(v.valor_total) || 0), 0)
})

function tipoClienteDot(tipo) {
  const dots = {
    'Persona Natural': 'bg-blue-500',
    'Persona Jurídica': 'bg-green-500',
    Inversionista: 'bg-purple-500',
    Corporativo: 'bg-orange-500',
  }
  return dots[tipo] || 'bg-gray-400'
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
  router.delete(`/admin/clientes/${props.cliente.documento}`)
  showConfirmDialog.value = false
}

function handleCancel() {
  showConfirmDialog.value = false
}
</script>
