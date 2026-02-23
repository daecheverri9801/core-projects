<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { computed } from 'vue'
import ContabilidadLayout from '@/Components/ContabilidadLayout.vue'

const props = defineProps({
  venta: Object,
  empleado: Object,
  imagenTipoAptoUrl: String,
})

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

const inmueble = computed(() => props.venta?.apartamento || props.venta?.local || null)
const esApto = computed(() => !!props.venta?.apartamento)

const estadoInmueble = computed(
  () =>
    props.venta?.apartamento?.estado_inmueble?.nombre ||
    props.venta?.local?.estado_inmueble?.nombre ||
    '—'
)

const parqueaderoTexto = computed(() => {
  if (!props.venta?.apartamento) return 'No aplica (Local)'
  const arr = props.venta.apartamento?.parqueaderos
  if (Array.isArray(arr) && arr.length) return 'Sí'
  return 'No'
})

const cuotaSeparacionProyecto = computed(() => {
  return Number(props.venta.proyecto?.valor_min_separacion || 0)
})

const ValorRestanteCI = computed(() => {
  return cuotaSeparacionProyecto.value
    ? Math.max(0, (props.venta.cuota_inicial || 0) - cuotaSeparacionProyecto.value)
    : props.venta.cuota_inicial || 0
})
</script>

<template>
  <ContabilidadLayout>
    <Head :title="`Contabilidad · Operación #${venta.id_venta}`" />

    <div class="space-y-6">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm text-gray-600">Contabilidad / Detalle</p>
        </div>

        <Link
          href="/contabilidad/ventas"
          class="px-4 py-2 rounded-lg border border-gray-300 bg-white hover:bg-gray-50 text-sm font-semibold text-gray-700 transition"
        >
          Volver
        </Link>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-1 gap-6">
        <!-- Columna principal (2/3) -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Tarjeta de información general -->
          <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex flex-wrap items-center gap-3 mb-4">
              <span
                class="text-sm px-3 py-1 rounded-full border font-semibold"
                :class="
                  venta.tipo_operacion === 'venta'
                    ? 'border-green-300 text-green-700 bg-green-50'
                    : 'border-blue-300 text-blue-700 bg-blue-50'
                "
              >
                {{ venta.tipo_operacion === 'venta' ? 'Venta' : 'Separación' }}
              </span>
              <span
                class="text-sm px-3 py-1 rounded-full border border-amber-300 text-amber-700 bg-amber-50"
              >
                {{ estadoInmueble }}
              </span>
              <span class="text-sm text-gray-600">
                Fecha: {{ formatDate(venta.fecha_venta) }}
              </span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
              <div class="bg-gray-50 rounded-lg border border-gray-200 p-4">
                <p class="text-xs text-gray-500 uppercase tracking-wide">Cliente</p>
                <p class="text-base font-semibold text-gray-900 mt-1">
                  {{ venta.cliente?.nombre || '—' }}
                </p>
                <p class="text-sm text-gray-600 mt-1">{{ venta.documento_cliente || '—' }}</p>
              </div>

              <div class="bg-gray-50 rounded-lg border border-gray-200 p-4">
                <p class="text-xs text-gray-500 uppercase tracking-wide">Proyecto</p>
                <p class="text-base font-semibold text-gray-900 mt-1">
                  {{ venta.proyecto?.nombre || '—' }}
                </p>
                <p class="text-sm text-gray-600 mt-1">
                  Forma pago: {{ venta.forma_pago?.forma_pago || '—' }}
                </p>
              </div>
            </div>

            <!-- Inmueble -->
            <div class="mt-5 bg-gray-50 rounded-lg border border-gray-200 p-4">
              <p class="text-sm font-semibold text-gray-900">Inmueble</p>
              <div class="mt-3 grid grid-cols-1 md:grid-cols-2 gap-3">
                <div class="flex justify-between text-sm">
                  <span class="text-gray-600">Tipo</span>
                  <span class="font-medium text-gray-900">{{
                    esApto ? 'Apartamento' : 'Local'
                  }}</span>
                </div>
                <div class="flex justify-between text-sm">
                  <span class="text-gray-600">Número</span>
                  <span class="font-medium text-gray-900">{{ inmueble?.numero ?? '—' }}</span>
                </div>
                <div v-if="esApto" class="flex justify-between text-sm">
                  <span class="text-gray-600">Parqueadero</span>
                  <span class="font-medium text-gray-900">{{ parqueaderoTexto }}</span>
                </div>
                <div class="flex justify-between text-sm">
                  <span class="text-gray-600">Torre</span>
                  <span class="font-medium text-gray-900">{{
                    inmueble?.torre?.nombre_torre ?? '—'
                  }}</span>
                </div>
                <div class="flex justify-between text-sm">
                  <span class="text-gray-600">Piso</span>
                  <span class="font-medium text-gray-900">{{
                    inmueble?.piso_torre?.nivel ?? '—'
                  }}</span>
                </div>
                <div class="flex justify-between text-sm">
                  <span class="text-gray-600">Vendido Por:</span>
                  <span class="font-medium text-gray-900"
                    >{{ venta.empleado?.nombre }} {{ venta.empleado?.apellido }}</span
                  >
                </div>
              </div>
            </div>

            <!-- Resumen económico -->
            <div class="bg-gray-50 rounded-lg border border-gray-200 p-4">
              <p class="text-sm font-semibold text-gray-900">Resumen económico</p>
              <div class="mt-3 grid grid-cols-1 md:grid-cols-2 gap-3">
                <div class="flex justify-between text-sm">
                  <span class="text-gray-600">Valor total</span>
                  <span class="font-semibold text-[#D1C000]">{{
                    formatMoney(venta.valor_total)
                  }}</span>
                </div>
                <div class="flex justify-between text-sm">
                  <span class="text-gray-600">Saldo restante</span>
                  <span class="font-semibold text-gray-900">{{
                    formatMoney(venta.valor_restante)
                  }}</span>
                </div>
                <div class="flex justify-between text-sm">
                  <span class="text-gray-600">Cuota inicial</span>
                  <span class="font-semibold text-gray-900">{{
                    formatMoney(venta.cuota_inicial)
                  }}</span>
                </div>
                <div class="flex justify-between text-sm">
                  <span class="text-gray-600">Pago Separación</span>
                  <span class="font-semibold text-gray-900"
                    >{{ formatMoney(cuotaSeparacionProyecto) }}
                  </span>
                </div>
                <div class="flex justify-between text-sm">
                  <span class="text-gray-600">Valor restante CI</span>
                  <span class="font-semibold text-gray-900"
                    >{{ formatMoney(ValorRestanteCI) }}
                  </span>
                </div>
                <div class="flex justify-between text-sm">
                  <span class="text-gray-600">Plazo CI</span>
                  <span class="font-semibold text-gray-900"
                    >{{ venta.plazo_cuota_inicial_meses ?? '—' }} mes(es)</span
                  >
                </div>
                <div class="flex justify-between text-sm">
                  <span class="text-gray-600">Frecuencia Pago CI</span>
                  <span class="font-semibold text-gray-900"
                    >{{ venta.frecuencia_cuota_inicial_meses ?? '—' }} mes(es)</span
                  >
                </div>
              </div>
            </div>

            <!-- Observaciones -->
            <div v-if="venta.descripcion" class="bg-gray-50 rounded-lg border border-gray-200 p-4">
              <p class="text-sm font-semibold text-gray-900">Observaciones</p>
              <p class="text-sm text-gray-700 mt-2">{{ venta.descripcion }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </ContabilidadLayout>
</template>
