<script setup>
import { Head, Link } from '@inertiajs/inertia-vue3'
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
  clientes: Object,
})

function formatDate(date) {
  if (!date) return '—'
  return new Date(date).toLocaleDateString('es-CO', {
    year: 'numeric',
    month: 'numeric',
    day: 'numeric',
  })
}

// Formatear moneda
const formatCurrency = (value) =>
  new Intl.NumberFormat('es-CO', {
    style: 'currency',
    currency: 'COP',
    minimumFractionDigits: 0,
  }).format(value || 0)
</script>

<template>
  <VentasLayout>
    <Head title="Detalle de Venta" />

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
        <PencilIcon class="w-5 h-5" /> Editar Venta
      </Link>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      <!-- Información principal -->
      <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-6">
        <div>
          <h1 class="text-3xl font-bold text-gray-900 mb-4">Venta #{{ venta.id_venta }}</h1>
          <p class="text-sm text-gray-500">Registrada el {{ formatDate(venta.fecha_venta) }}</p>
        </div>

        <!-- Información del cliente e inmueble -->
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

        <!-- Información económica -->
        <div class="border-t border-gray-200 pt-4">
          <h2 class="text-lg font-bold text-gray-900 mb-2">Resumen Económico</h2>
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
        </div>

        <!-- RESUMEN DEL PLAN DE AMORTIZACIÓN -->
        <div class="pt-6 border-t border-gray-200">
          <h2 class="text-lg font-bold text-gray-900 mb-3">Plan de Amortización</h2>

          <!-- NO EXISTE -->
          <div
            v-if="!venta.plan_amortizacion"
            class="p-4 bg-yellow-50 rounded-lg border border-yellow-200"
          >
            <p class="text-sm text-yellow-800 mb-3">Esta venta no tiene un plan de amortización.</p>
            <Link
              :href="`/planes-amortizacion-venta/create?id_venta=${venta.id_venta}`"
              class="inline-flex items-center gap-2 px-4 py-2 bg-[#f4c430] text-gray-900 rounded-lg font-semibold hover:bg-[#e5b520]"
            >
              <PlusIcon class="w-5 h-5" /> Crear Plan
            </Link>
          </div>

          <!-- EXISTE -->
          <div v-else class="p-4 bg-gray-50 rounded-lg border border-gray-200">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <InfoRow label="Tipo de Plan" :value="venta.plan_amortizacion.tipo_plan ?? '—'" />
              <InfoRow label="Plazo" :value="`${venta.plan_amortizacion.plazo_meses} meses`" />
              <InfoRow label="Interés" :value="`${venta.plan_amortizacion.valor_interes_anual}%`" />
              <InfoRow label="Inicio" :value="formatDate(venta.plan_amortizacion.fecha_inicio)" />
            </div>

            <div class="mt-4 flex items-center justify-between">
              <span class="text-gray-700 text-sm">
                Total Cuotas: <strong>{{ venta.plan_amortizacion.cuotas?.length ?? 0 }}</strong>
              </span>

              <Link
                :href="`/planes-amortizacion-venta/${venta.plan_amortizacion.id_plan}`"
                class="inline-flex items-center gap-2 text-[#1e3a5f] font-semibold hover:underline"
              >
                Ver detalle <ArrowLeftIcon class="w-4 h-4 rotate-180" />
              </Link>
            </div>
          </div>
        </div>

        <!-- Descripción -->
        <div v-if="venta.descripcion" class="pt-4 border-t border-gray-200">
          <h2 class="text-lg font-bold text-gray-900 mb-2">Observaciones</h2>
          <p class="text-gray-700">{{ venta.descripcion }}</p>
        </div>
      </div>

      <!-- COLUMNA LATERAL -->
      <div class="space-y-6">
        <!-- Estado de Venta -->
        <div
          class="bg-gradient-to-br from-[#1e3a5f] to-[#2c5282] rounded-xl shadow-lg p-6 text-white"
        >
          <h3 class="text-xl font-bold mb-2">Estado de la Venta</h3>
          <p class="text-lg font-semibold">
            {{ venta.apartamento?.estado_inmueble?.nombre || venta.local?.estado_inmueble?.nombre }}
          </p>
          <p class="text-sm text-blue-200">
            Forma de Pago: {{ venta.forma_pago?.forma_pago ?? '—' }}
          </p>
        </div>

        <!-- DATOS ASOCIADOS -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 space-y-3">
          <h3 class="text-lg font-bold text-gray-900">Datos Asociados</h3>

          <p class="flex items-center text-gray-700">
            <CreditCardIcon class="w-5 h-5 mr-2 text-gray-400" /> Pagos:
            {{ venta.pagos?.length ?? 0 }}
          </p>

          <p class="flex items-center text-gray-700">
            <DocumentTextIcon class="w-5 h-5 mr-2 text-gray-400" />
            Plan Amortización:
            <span class="ml-1 font-semibold">
              {{
                venta.plan_amortizacion
                  ? `${venta.plan_amortizacion.cuotas?.length ?? 0} cuotas`
                  : 'No generado'
              }}
            </span>
          </p>

          <!-- Botón crear/ver plan -->
          <div class="pt-4 mt-2 border-t border-gray-200">
            <!-- SI NO EXISTE -->
            <Link
              v-if="!venta.plan_amortizacion"
              :href="`/planes-amortizacion-venta/create?id_venta=${venta.id_venta}`"
              class="w-full inline-flex items-center justify-center gap-2 px-4 py-2 bg-[#f4c430] text-gray-900 rounded-lg font-semibold hover:bg-[#e5b520] transition"
            >
              <PlusIcon class="w-5 h-5" /> Crear Plan de Amortización
            </Link>

            <!-- SI EXISTE -->
            <div v-else class="space-y-3">
              <Link
                :href="`/planes-amortizacion-venta/${venta.plan_amortizacion.id_plan}`"
                class="w-full inline-flex items-center justify-center gap-2 px-4 py-2 bg-[#1e3a5f] text-white rounded-lg font-semibold hover:bg-[#2c5282] transition"
              >
                <EyeIcon class="w-5 h-5" /> Ver Plan de Amortización
              </Link>

              <Link
                :href="`/planes-amortizacion-venta/${venta.plan_amortizacion.id_plan}/edit`"
                class="w-full inline-flex items-center justify-center gap-2 px-4 py-2 bg-yellow-500 text-white rounded-lg font-semibold hover:bg-yellow-600 transition"
              >
                <PencilIcon class="w-5 h-5" /> Editar Plan
              </Link>
            </div>
          </div>
        </div>
      </div>
    </div>
  </VentasLayout>
</template>
