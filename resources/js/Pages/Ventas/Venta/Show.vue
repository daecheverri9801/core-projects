<script setup>
import { Head, Link } from '@inertiajs/vue3'
import VentasLayout from '@/Components/VentasLayout.vue'
import {
  ArrowLeftIcon,
  PencilIcon,
  CreditCardIcon,
  UserIcon,
  HomeIcon,
  DocumentTextIcon,
} from '@heroicons/vue/24/outline'

const props = defineProps({
  venta: Object,
})

function formatDate(date) {
  if (!date) return '—'
  return new Date(date).toLocaleDateString('es-CO', {
    year: 'numeric',
    month: 'numeric',
    day: 'numeric',
  })
}

const formatCurrency = (value) =>
  new Intl.NumberFormat('es-CO', {
    style: 'currency',
    currency: 'COP',
    minimumFractionDigits: 0,
  }).format(value || 0)

const esVenta = () => props.venta.tipo_operacion === 'venta'
const esSeparacion = () => props.venta.tipo_operacion === 'separacion'
</script>

<template>
  <VentasLayout>
    <Head title="Detalle de Operación" />

    <div class="flex justify-between items-center mb-6">
      <Link
        href="/ventas"
        class="inline-flex items-center text-sm text-gray-600 hover:text-[#1e3a5f] transition"
      >
        <ArrowLeftIcon class="w-4 h-4 mr-2" />
        Volver
      </Link>
      <Link
        :href="`/ventas/${venta.id_venta}/edit`"
        class="inline-flex items-center gap-2 bg-[#f4c430] text-gray-900 px-4 py-2 rounded-lg font-semibold hover:bg-[#e5b520] transition"
      >
        <PencilIcon class="w-5 h-5" /> Editar Operación
      </Link>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-6">
        <div>
          <h1 class="text-3xl font-bold text-gray-900 mb-2">
            {{ esVenta() ? 'Venta' : 'Separación' }} #{{ venta.id_venta }}
          </h1>
          <p class="text-sm text-gray-500">Registrada el {{ formatDate(venta.fecha_venta) }}</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div class="flex items-start gap-3">
            <UserIcon class="w-6 h-6 text-gray-400 mt-1" />
            <div>
              <p class="text-sm text-gray-500">Cliente</p>
              <p class="font-semibold text-gray-900">
                {{ venta.cliente?.nombre ?? 'No asignado' }}
              </p>
              <p class="text-xs text-gray-600 mt-1">{{ venta.documento_cliente }}</p>
            </div>
          </div>

          <div class="flex items-start gap-3">
            <HomeIcon class="w-6 h-6 text-gray-400 mt-1" />
            <div>
              <p class="text-sm text-gray-500">Inmueble</p>
              <p class="font-semibold text-gray-900">
                <span v-if="venta.apartamento">Apartamento {{ venta.apartamento.numero }}</span>
                <span v-else-if="venta.local">Local {{ venta.local.numero }}</span>
                <span v-else>—</span>
              </p>
              <p class="text-xs text-gray-600 mt-1">{{ venta.proyecto?.nombre ?? '' }}</p>
            </div>
          </div>
        </div>

        <!-- Bloque económico -->
        <div class="border-t border-gray-200 pt-4">
          <h2 class="text-lg font-bold text-gray-900 mb-2">Resumen Económico</h2>

          <template v-if="esVenta()">
            <ul class="space-y-2">
              <li class="flex justify-between text-gray-700">
                <span>Valor Total:</span>
                <span>{{ formatCurrency(venta.valor_total) }}</span>
              </li>
              <li class="flex justify-between text-gray-700">
                <span>Cuota Inicial:</span>
                <span>{{ formatCurrency(venta.cuota_inicial) }}</span>
              </li>
              <li
                class="flex justify-between font-semibold text-gray-900 border-t border-gray-200 pt-2"
              >
                <span>Valor Restante:</span>
                <span>{{ formatCurrency(venta.valor_restante) }}</span>
              </li>
            </ul>
          </template>

          <template v-else>
            <ul class="space-y-2">
              <li class="flex justify-between text-gray-700">
                <span>Valor de Separación:</span>
                <span>{{ formatCurrency(venta.valor_separacion) }}</span>
              </li>
              <li class="flex justify-between text-gray-700">
                <span>Fecha Límite Separación:</span>
                <span>{{ formatDate(venta.fecha_limite_separacion) }}</span>
              </li>
            </ul>
          </template>
        </div>

        <div v-if="venta.descripcion" class="pt-4 border-t border-gray-200">
          <h2 class="text-lg font-bold text-gray-900 mb-2">Observaciones</h2>
          <p class="text-gray-700">{{ venta.descripcion }}</p>
        </div>
      </div>

      <!-- Lateral -->
      <div class="space-y-6">
        <div
          class="bg-gradient-to-br from-[#1e3a5f] to-[#2c5282] rounded-xl shadow-lg p-6 text-white"
        >
          <h3 class="text-xl font-bold mb-2">Estado de la Operación</h3>
          <p>
            {{
              venta.apartamento?.estado_inmueble?.nombre ||
              venta.local?.estado_inmueble?.nombre ||
              '—'
            }}
          </p>
          <p class="text-sm text-blue-200 mt-1">
            Forma de Pago: {{ venta.forma_pago?.forma_pago ?? '—' }}
          </p>
          <p class="text-sm text-blue-200 mt-1">Tipo: {{ esVenta() ? 'Venta' : 'Separación' }}</p>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 space-y-3">
          <h3 class="text-lg font-bold text-gray-900">Datos Asociados</h3>
          <p class="flex items-center text-gray-700">
            <CreditCardIcon class="w-5 h-5 mr-2 text-gray-400" /> Pagos:
            {{ venta.pagos?.length ?? 0 }}
          </p>
          <p class="flex items-center text-gray-700">
            <DocumentTextIcon class="w-5 h-5 mr-2 text-gray-400" /> Plan de Amortización:
            <span class="ml-1">
              {{
                venta.plan_amortizacion
                  ? (venta.plan_amortizacion.cuotas?.length ?? 0)
                  : 'No generado'
              }}
            </span>
          </p>
        </div>
      </div>
    </div>
  </VentasLayout>
</template>
