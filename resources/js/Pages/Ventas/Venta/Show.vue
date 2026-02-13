<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { computed } from 'vue'
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
  imagenTipoAptoUrl: String,
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

const inmueble = computed(() => props.venta.apartamento || props.venta.local || null)
const esApartamento = computed(() => !!props.venta.apartamento)
const esLocal = computed(() => !!props.venta.local)

const cuotaSeparacionProyecto = computed(() => {
  return Number(props.venta.proyecto?.valor_min_separacion || 0)
})

// ===== Info inmueble =====
const infoInmueble = computed(() => {
  const v = props.venta
  if (!inmueble.value) return null

  if (esApartamento.value) {
    const a = v.apartamento
    return {
      numero: a?.numero ?? '—',
      piso: a?.piso_torre?.nivel ?? '—',
      torre: a?.torre?.nombre_torre ?? '—',
      tipo: a?.tipo_apartamento?.nombre ?? '—',
      alcobas: a?.tipo_apartamento?.cantidad_habitaciones ?? '—',
      banos: a?.tipo_apartamento?.cantidad_banos ?? '—',
      area_construida: a?.tipo_apartamento?.area_construida ?? '—',
      area_privada: a?.tipo_apartamento?.area_privada ?? '—',
    }
  }

  const l = v.local
  return {
    numero: l?.numero ?? '—',
    piso: l?.piso_torre?.nivel ?? '—',
    torre: l?.torre?.nombre_torre ?? '—',
    tipo: 'Local Comercial',
    alcobas: '—',
    banos: '—',
    area_construida: l?.area_total_local ?? '—',
    area_privada: l?.area_total_local ?? '—',
  }
})

// ===== Desglose económico =====
// Nota: "Saldo Cuota Inicial" y "Valor cuota mensual" requieren criterio.
// Aquí:
// - Saldo cuota inicial = valor_restante (lo que queda por pagar después de la CI)
// - No. cuotas = plazo_cuota_inicial_meses (si no hay plan) o plan.cuotas.length (si existe)
// - Valor cuota mensual = promedio: saldo_cuota_inicial / no_cuotas (si no hay plan)
//   o si hay plan: usamos el primer valor_cuota (si todas son iguales)
const plan = computed(() => props.venta.plan_amortizacion || null)
const cuotasPlan = computed(() => plan.value?.cuotas || [])

const numeroCuotas = computed(() => {
  if (cuotasPlan.value.length) return cuotasPlan.value.length
  return props.venta.plazo_cuota_inicial_meses || 0
})

const valorCuotaMensual = computed(() => {
  if (cuotasPlan.value.length) return cuotasPlan.value[0]?.valor_cuota || 0
  const n = Number(numeroCuotas.value || 0)
  if (!n) return 0
  return Number(props.venta.cuota_inicial || 0) / n || 0
})

const desgloseEconomico = computed(() => {
  return {
    valor_cuota_inicial: props.venta.cuota_inicial || 0,
    cuota_separacion: cuotaSeparacionProyecto.value || 0,
    saldo_cuota_inicial: props.venta.valor_restante || 0,
    no_cuotas: Number(numeroCuotas.value || 0),
    valor_cuota_mensual: Number(valorCuotaMensual.value || 0),
    saldo_restante: props.venta.valor_restante || 0,
  }
})
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

        <div class="border-t border-gray-200 pt-4" v-if="infoInmueble">
          <h2 class="text-lg font-bold text-gray-900 mb-3">Información del Inmueble</h2>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="flex justify-between bg-gray-50 rounded-lg px-4 py-3">
              <span class="text-gray-600">Número</span>
              <span class="font-semibold text-gray-900">{{ infoInmueble.numero }}</span>
            </div>

            <div class="flex justify-between bg-gray-50 rounded-lg px-4 py-3">
              <span class="text-gray-600">Piso</span>
              <span class="font-semibold text-gray-900">{{ infoInmueble.piso }}</span>
            </div>

            <div class="flex justify-between bg-gray-50 rounded-lg px-4 py-3">
              <span class="text-gray-600">Torre</span>
              <span class="font-semibold text-gray-900">{{ infoInmueble.torre }}</span>
            </div>

            <div class="flex justify-between bg-gray-50 rounded-lg px-4 py-3">
              <span class="text-gray-600">Tipo</span>
              <span class="font-semibold text-gray-900">{{ infoInmueble.tipo }}</span>
            </div>

            <div class="flex justify-between bg-gray-50 rounded-lg px-4 py-3">
              <span class="text-gray-600">Alcobas</span>
              <span class="font-semibold text-gray-900">{{ infoInmueble.alcobas }}</span>
            </div>

            <div class="flex justify-between bg-gray-50 rounded-lg px-4 py-3">
              <span class="text-gray-600">Baños</span>
              <span class="font-semibold text-gray-900">{{ infoInmueble.banos }}</span>
            </div>

            <div class="flex justify-between bg-gray-50 rounded-lg px-4 py-3">
              <span class="text-gray-600">Área Construida</span>
              <span class="font-semibold text-gray-900">{{ infoInmueble.area_construida }} m²</span>
            </div>

            <div class="flex justify-between bg-gray-50 rounded-lg px-4 py-3">
              <span class="text-gray-600">Área Privada</span>
              <span class="font-semibold text-gray-900">{{ infoInmueble.area_privada }} m²</span>
            </div>
          </div>
        </div>

        <div class="border-t border-gray-200 pt-4">
          <h2 class="text-lg font-bold text-gray-900 mb-3">Desglose Económico</h2>

          <ul class="space-y-2">
            <li class="flex justify-between text-gray-700">
              <span>Valor Total:</span>
              <span class="font-semibold">{{ formatCurrency(venta.valor_total) }}</span>
            </li>

            <li class="flex justify-between text-gray-700">
              <span>Valor Cuota Inicial:</span>
              <span class="font-semibold">{{
                formatCurrency(desgloseEconomico.valor_cuota_inicial)
              }}</span>
            </li>

            <li class="flex justify-between text-gray-700">
              <span>Cuota de Separación:</span>
              <span class="font-semibold">{{
                formatCurrency(desgloseEconomico.cuota_separacion)
              }}</span>
            </li>

            <li class="flex justify-between text-gray-700">
              <span>Saldo Cuota Inicial:</span>
              <span class="font-semibold">{{
                formatCurrency(desgloseEconomico.saldo_cuota_inicial)
              }}</span>
            </li>

            <li class="flex justify-between text-gray-700">
              <span>No. Cuotas:</span>
              <span class="font-semibold">{{ desgloseEconomico.no_cuotas || '—' }}</span>
            </li>

            <li class="flex justify-between text-gray-700">
              <span>Valor Cuota Mensual:</span>
              <span class="font-semibold">{{
                formatCurrency(desgloseEconomico.valor_cuota_mensual)
              }}</span>
            </li>

            <li
              class="flex justify-between font-semibold text-gray-900 border-t border-gray-200 pt-2"
            >
              <span>Saldo Restante:</span>
              <span>{{ formatCurrency(desgloseEconomico.saldo_restante) }}</span>
            </li>

            <li class="flex justify-between text-gray-700">
              <span>Fecha Límite Separación:</span>
              <span>{{ formatDate(venta.fecha_limite_separacion) }}</span>
            </li>
          </ul>

          <p v-if="cuotasPlan.length" class="text-xs text-gray-500 mt-3">
            Datos de cuotas tomados del plan de amortización (#{{ plan?.id_plan ?? '—' }}).
          </p>
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

        <div v-if="venta.apartamento && imagenTipoAptoUrl" class="mt-4">
          <p class="text-sm text-gray-500 mb-2">Imagen tipo apartamento</p>

          <div class="overflow-hidden rounded-xl border border-gray-200 bg-gray-50">
            <img
              :src="imagenTipoAptoUrl"
              :alt="venta.apartamento?.tipoApartamento?.nombre || 'Tipo apartamento'"
              class="w-24 h-24 rounded-lg object-cover"
              loading="lazy"
            />
          </div>
        </div>
      </div>
    </div>
  </VentasLayout>
</template>
