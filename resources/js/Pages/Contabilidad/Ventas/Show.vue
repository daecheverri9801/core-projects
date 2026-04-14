<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import ContabilidadLayout from '@/Components/ContabilidadLayout.vue'
import {
  BanknotesIcon,
  PhotoIcon,
  ArrowTopRightOnSquareIcon,
  CreditCardIcon,
  DocumentTextIcon,
  ClipboardDocumentListIcon,
} from '@heroicons/vue/24/outline'

const props = defineProps({
  venta: Object,
  empleado: Object,
  imagenTipoAptoUrl: String,
})

const previewComprobante = ref(null)

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

function abrirPreview(url) {
  previewComprobante.value = url
}

function cerrarPreview() {
  previewComprobante.value = null
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

const valorRestanteCI = computed(() => {
  return cuotaSeparacionProyecto.value
    ? Math.max(0, (props.venta.cuota_inicial || 0) - cuotaSeparacionProyecto.value)
    : props.venta.cuota_inicial || 0
})

const numeroCuotasCalculadas = computed(() => {
  const plazo = Number(props.venta.plazo_cuota_inicial_meses ?? 1)
  const frecuencia = Number(props.venta.frecuencia_cuota_inicial_meses ?? 1)

  if (frecuencia <= 0) return 1
  return Math.max(1, Math.ceil(plazo / frecuencia))
})

const valorCuotaMensual = computed(() => {
  const numeroCuotas = numeroCuotasCalculadas.value
  return numeroCuotas > 0 ? Math.round(valorRestanteCI.value / numeroCuotas) : 0
})

const cuotasPlan = computed(() => {
  const cuotas =
    props.venta?.plan_amortizacion?.cuotas || props.venta?.planAmortizacion?.cuotas || []
  return [...cuotas].sort((a, b) => Number(a.numero_cuota || 0) - Number(b.numero_cuota || 0))
})

const pagosVenta = computed(() => {
  return Array.isArray(props.venta?.pagos) ? props.venta.pagos : []
})

const pagosConCuota = computed(() => {
  return pagosVenta.value.filter((pago) => pago.id_cuota)
})

const pagosSinCuota = computed(() => {
  return pagosVenta.value.filter((pago) => !pago.id_cuota)
})

const pagosPorCuota = computed(() => {
  const mapa = {}

  for (const cuota of cuotasPlan.value) {
    mapa[cuota.id_cuota] = []
  }

  for (const pago of pagosConCuota.value) {
    const key = pago.id_cuota
    if (!mapa[key]) mapa[key] = []
    mapa[key].push(pago)
  }

  return mapa
})

function totalPagadoCuota(idCuota) {
  return (pagosPorCuota.value[idCuota] || []).reduce((acc, pago) => {
    return acc + Number(pago.valor || 0)
  }, 0)
}

function saldoPendienteCuota(cuota) {
  return Math.max(0, Number(cuota.valor_cuota || 0) - totalPagadoCuota(cuota.id_cuota))
}

function estadoPagoCuota(cuota) {
  const totalPagado = totalPagadoCuota(cuota.id_cuota)
  const valorCuota = Number(cuota.valor_cuota || 0)

  if (totalPagado <= 0) return 'Sin pagos'
  if (totalPagado >= valorCuota) return 'Cubierta'
  return 'Abonada'
}

function comprobanteUrl(pago) {
  return (
    pago?.comprobante_url || (pago?.comprobante_path ? `/storage/${pago.comprobante_path}` : null)
  )
}
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

      <div class="grid grid-cols-1 gap-6">
        <div class="space-y-6">
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
                  {{ venta.cliente?.nombre || '—' }} - {{ venta.documento_cliente || '—' }}
                </p>
                <p class="text-sm text-gray-600 mt-1">
                  {{ venta.cliente?.telefono || '—' }} - {{ venta.cliente?.correo || '—' }}
                </p>
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

            <div class="mt-5 bg-gray-50 rounded-lg border border-gray-200 p-4">
              <p class="text-sm font-semibold text-gray-900">Inmueble</p>

              <div class="mt-3 grid grid-cols-1 md:grid-cols-2 gap-3">
                <div class="flex justify-between text-sm">
                  <span class="text-gray-600">Tipo Inmueble</span>
                  <span class="font-medium text-gray-900">{{
                    esApto ? 'Apartamento' : 'Local'
                  }}</span>
                </div>

                <div class="flex justify-between text-sm">
                  <span class="text-gray-600">Tipo Apartamento</span>
                  <span class="font-medium text-gray-900">
                    {{ inmueble?.tipo_apartamento?.nombre ?? '—' }}
                  </span>
                </div>

                <div v-if="esApto" class="flex justify-between text-sm">
                  <span class="text-gray-600">Parqueadero</span>
                  <span class="font-medium text-gray-900">{{ parqueaderoTexto }}</span>
                </div>

                <div v-if="esApto" class="flex justify-between text-sm">
                  <span class="text-gray-600">Parqueadero Adicional</span>
                  <span class="font-medium text-gray-900">{{
                    venta.parqueadero ? 'Sí' : 'No'
                  }}</span>
                </div>

                <div class="flex justify-between text-sm">
                  <span class="text-gray-600">Número</span>
                  <span class="font-medium text-gray-900">{{ inmueble?.numero ?? '—' }}</span>
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
                  <span class="text-gray-600">Vendido por</span>
                  <span class="font-medium text-gray-900">
                    {{ venta.empleado?.nombre }} {{ venta.empleado?.apellido }}
                  </span>
                </div>
              </div>
            </div>

            <div class="mt-5 bg-gray-50 rounded-lg border border-gray-200 p-4">
              <p class="text-sm font-semibold text-gray-900">Resumen económico</p>

              <div class="mt-3 grid grid-cols-1 md:grid-cols-2 gap-3">
                <div class="flex justify-between text-sm">
                  <span class="text-gray-600">Valor total</span>
                  <span class="font-semibold text-[#D1C000]">{{
                    formatMoney(venta.valor_total)
                  }}</span>
                </div>

                <div class="flex justify-between text-sm">
                  <span class="text-gray-600">Valor cuota inicial</span>
                  <span class="font-semibold text-gray-900">{{
                    formatMoney(venta.cuota_inicial)
                  }}</span>
                </div>

                <div class="flex justify-between text-sm">
                  <span class="text-gray-600">Saldo restante</span>
                  <span class="font-semibold text-gray-900">{{
                    formatMoney(venta.valor_restante)
                  }}</span>
                </div>

                <div class="flex justify-between text-sm">
                  <span class="text-gray-600">Pago separación</span>
                  <span class="font-semibold text-gray-900">{{
                    formatMoney(cuotaSeparacionProyecto)
                  }}</span>
                </div>

                <div class="flex justify-between text-sm">
                  <span class="text-gray-600">Valor restante CI</span>
                  <span class="font-semibold text-gray-900">{{
                    formatMoney(valorRestanteCI)
                  }}</span>
                </div>

                <div class="flex justify-between text-sm">
                  <span class="text-gray-600">Valor cuota mensual</span>
                  <span class="font-semibold text-gray-900">{{
                    formatMoney(valorCuotaMensual)
                  }}</span>
                </div>

                <div class="flex justify-between text-sm">
                  <span class="text-gray-600">Plazo CI</span>
                  <span class="font-semibold text-gray-900"
                    >{{ venta.plazo_cuota_inicial_meses ?? '—' }} mes(es)</span
                  >
                </div>

                <div class="flex justify-between text-sm">
                  <span class="text-gray-600">Frecuencia pago CI</span>
                  <span class="font-semibold text-gray-900"
                    >{{ venta.frecuencia_cuota_inicial_meses ?? '—' }} mes(es)</span
                  >
                </div>
              </div>
            </div>

            <div class="mt-5 bg-gray-50 rounded-lg border border-gray-200 p-4">
              <p class="text-sm font-semibold text-gray-900">Observaciones</p>
              <p class="text-sm text-gray-700 mt-2">{{ venta.descripcion || '—' }}</p>
            </div>
          </div>

          <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center gap-2 mb-4">
              <ClipboardDocumentListIcon class="h-5 w-5 text-[#1e3a5f]" />
              <h2 class="text-lg font-bold text-gray-900">Plan de pagos y comprobantes</h2>
            </div>

            <div v-if="cuotasPlan.length" class="space-y-4">
              <div
                v-for="cuota in cuotasPlan"
                :key="cuota.id_cuota"
                class="rounded-2xl border border-gray-200 bg-gray-50 overflow-hidden"
              >
                <div class="border-b border-gray-200 bg-white px-5 py-4">
                  <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                      <div class="flex items-center gap-2">
                        <span class="text-sm font-extrabold text-gray-900">
                          Cuota #{{ cuota.numero_cuota }}
                        </span>

                        <span
                          class="inline-flex rounded-full border px-3 py-1 text-xs font-bold"
                          :class="
                            estadoPagoCuota(cuota) === 'Cubierta'
                              ? 'border-emerald-200 bg-emerald-50 text-emerald-700'
                              : estadoPagoCuota(cuota) === 'Abonada'
                                ? 'border-amber-200 bg-amber-50 text-amber-700'
                                : 'border-gray-200 bg-gray-100 text-gray-700'
                          "
                        >
                          {{ estadoPagoCuota(cuota) }}
                        </span>
                      </div>

                      <p class="mt-1 text-sm text-gray-500">
                        Vence: {{ formatDate(cuota.fecha_vencimiento) }}
                      </p>
                    </div>

                    <div class="grid grid-cols-1 gap-2 sm:grid-cols-3">
                      <div class="rounded-xl bg-[#FFFDE6] px-4 py-3">
                        <p class="text-[11px] font-semibold uppercase tracking-wide text-[#756C00]">
                          Valor cuota
                        </p>
                        <p class="mt-1 text-sm font-bold text-[#474100]">
                          {{ formatMoney(cuota.valor_cuota) }}
                        </p>
                      </div>

                      <div class="rounded-xl bg-emerald-50 px-4 py-3">
                        <p
                          class="text-[11px] font-semibold uppercase tracking-wide text-emerald-700"
                        >
                          Pagado
                        </p>
                        <p class="mt-1 text-sm font-bold text-emerald-800">
                          {{ formatMoney(totalPagadoCuota(cuota.id_cuota)) }}
                        </p>
                      </div>

                      <div class="rounded-xl bg-red-50 px-4 py-3">
                        <p class="text-[11px] font-semibold uppercase tracking-wide text-red-700">
                          Pendiente
                        </p>
                        <p class="mt-1 text-sm font-bold text-red-800">
                          {{ formatMoney(saldoPendienteCuota(cuota)) }}
                        </p>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="p-5">
                  <div v-if="(pagosPorCuota[cuota.id_cuota] || []).length" class="space-y-3">
                    <div
                      v-for="pago in pagosPorCuota[cuota.id_cuota]"
                      :key="pago.id_pago"
                      class="rounded-2xl border border-gray-200 bg-white p-4"
                    >
                      <div class="grid grid-cols-1 gap-4 lg:grid-cols-12">
                        <div class="lg:col-span-8 space-y-3">
                          <div class="flex flex-wrap items-center gap-2">

                            <span
                              v-if="comprobanteUrl(pago)"
                              class="inline-flex rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700"
                            >
                              Con comprobante
                            </span>

                            <span
                              v-else
                              class="inline-flex rounded-full border border-gray-200 bg-gray-100 px-3 py-1 text-xs font-bold text-gray-700"
                            >
                              Sin comprobante
                            </span>
                          </div>

                          <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                            <div class="rounded-xl bg-gray-50 px-4 py-3">
                              <p
                                class="text-[11px] font-semibold uppercase tracking-wide text-gray-500"
                              >
                                Fecha
                              </p>
                              <p class="mt-1 text-sm font-semibold text-gray-900">
                                {{ formatDate(pago.fecha) }}
                              </p>
                            </div>

                            <div class="rounded-xl bg-gray-50 px-4 py-3">
                              <p
                                class="text-[11px] font-semibold uppercase tracking-wide text-gray-500"
                              >
                                Valor
                              </p>
                              <p class="mt-1 text-sm font-semibold text-gray-900">
                                {{ formatMoney(pago.valor) }}
                              </p>
                            </div>

                            <div class="rounded-xl bg-gray-50 px-4 py-3">
                              <p
                                class="text-[11px] font-semibold uppercase tracking-wide text-gray-500"
                              >
                                Medio de pago
                              </p>
                              <p class="mt-1 text-sm font-semibold text-gray-900">
                                {{ pago.medio_pago?.medio_pago || '—' }}
                              </p>
                            </div>

                            <div class="rounded-xl bg-gray-50 px-4 py-3">
                              <p
                                class="text-[11px] font-semibold uppercase tracking-wide text-gray-500"
                              >
                                Concepto
                              </p>
                              <p class="mt-1 text-sm font-semibold text-gray-900">
                                {{ pago.concepto_pago?.concepto || '—' }}
                              </p>
                            </div>

                            <div class="rounded-xl bg-gray-50 px-4 py-3 md:col-span-2">
                              <p
                                class="text-[11px] font-semibold uppercase tracking-wide text-gray-500"
                              >
                                Referencia / descripción
                              </p>
                              <p class="mt-1 text-sm font-semibold text-gray-900">
                                {{ pago.referencia_pago || 'Sin referencia' }}
                              </p>
                              <p class="mt-1 text-xs text-gray-500">
                                {{ pago.descripcion || 'Sin descripción' }}
                              </p>
                            </div>
                          </div>
                        </div>

                        <div class="lg:col-span-4">
                          <div
                            class="h-full rounded-2xl border border-dashed border-gray-300 bg-gray-50 p-4"
                          >
                            <div class="flex items-center gap-2 mb-3">
                              <PhotoIcon class="h-5 w-5 text-[#1e3a5f]" />
                              <p class="text-sm font-bold text-gray-900">Comprobante</p>
                            </div>

                            <div v-if="comprobanteUrl(pago)" class="space-y-3">
                              <img
                                :src="comprobanteUrl(pago)"
                                alt="Comprobante"
                                class="h-40 w-full rounded-xl border border-gray-200 object-cover bg-white"
                              />

                              <div class="grid grid-cols-2 gap-2">
                                <button
                                  type="button"
                                  @click="abrirPreview(comprobanteUrl(pago))"
                                  class="inline-flex items-center justify-center rounded-xl border border-sky-200 bg-sky-50 px-3 py-2 text-xs font-bold text-sky-700 transition hover:bg-sky-100"
                                >
                                  Ver
                                </button>

                                <a
                                  :href="comprobanteUrl(pago)"
                                  target="_blank"
                                  class="inline-flex items-center justify-center gap-1 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs font-bold text-emerald-700 transition hover:bg-emerald-100"
                                >
                                  <ArrowTopRightOnSquareIcon class="h-4 w-4" />
                                  Abrir
                                </a>
                              </div>
                            </div>

                            <div
                              v-else
                              class="flex h-40 items-center justify-center rounded-xl border border-gray-200 bg-white px-4 text-center text-sm font-medium text-gray-500"
                            >
                              Sin comprobante cargado
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <div
                    v-else
                    class="rounded-2xl border border-dashed border-gray-300 bg-white px-6 py-8 text-center"
                  >
                    <p class="text-sm font-semibold text-gray-700">
                      Esta cuota no tiene pagos registrados.
                    </p>
                  </div>
                </div>
              </div>
            </div>

            <div
              v-else
              class="rounded-2xl border border-dashed border-gray-300 bg-gray-50 px-6 py-10 text-center"
            >
              <p class="text-sm font-semibold text-gray-700">
                Esta venta no tiene plan de amortización generado.
              </p>
            </div>

            <div v-if="pagosSinCuota.length" class="mt-6">
              <div class="mb-3 flex items-center gap-2">
                <CreditCardIcon class="h-5 w-5 text-[#1e3a5f]" />
                <h3 class="text-base font-bold text-gray-900">Pagos no asociados a cuota</h3>
              </div>

              <div class="space-y-3">
                <div
                  v-for="pago in pagosSinCuota"
                  :key="pago.id_pago"
                  class="rounded-2xl border border-gray-200 bg-gray-50 p-4"
                >
                  <div class="grid grid-cols-1 gap-4 lg:grid-cols-12">
                    <div class="lg:col-span-8">
                      <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                        <div class="rounded-xl bg-white px-4 py-3">
                          <p
                            class="text-[11px] font-semibold uppercase tracking-wide text-gray-500"
                          >
                            Fecha
                          </p>
                          <p class="mt-1 text-sm font-semibold text-gray-900">
                            {{ formatDate(pago.fecha) }}
                          </p>
                        </div>

                        <div class="rounded-xl bg-white px-4 py-3">
                          <p
                            class="text-[11px] font-semibold uppercase tracking-wide text-gray-500"
                          >
                            Valor
                          </p>
                          <p class="mt-1 text-sm font-semibold text-gray-900">
                            {{ formatMoney(pago.valor) }}
                          </p>
                        </div>

                        <div class="rounded-xl bg-white px-4 py-3">
                          <p
                            class="text-[11px] font-semibold uppercase tracking-wide text-gray-500"
                          >
                            Medio de pago
                          </p>
                          <p class="mt-1 text-sm font-semibold text-gray-900">
                            {{ pago.medio_pago?.medio_pago || '—' }}
                          </p>
                        </div>

                        <div class="rounded-xl bg-white px-4 py-3">
                          <p
                            class="text-[11px] font-semibold uppercase tracking-wide text-gray-500"
                          >
                            Concepto
                          </p>
                          <p class="mt-1 text-sm font-semibold text-gray-900">
                            {{ pago.concepto_pago?.concepto || '—' }}
                          </p>
                        </div>

                        <div class="rounded-xl bg-white px-4 py-3 md:col-span-2">
                          <p
                            class="text-[11px] font-semibold uppercase tracking-wide text-gray-500"
                          >
                            Referencia / descripción
                          </p>
                          <p class="mt-1 text-sm font-semibold text-gray-900">
                            {{ pago.referencia_pago || 'Sin referencia' }}
                          </p>
                          <p class="mt-1 text-xs text-gray-500">
                            {{ pago.descripcion || 'Sin descripción' }}
                          </p>
                        </div>
                      </div>
                    </div>

                    <div class="lg:col-span-4">
                      <div
                        class="h-full rounded-2xl border border-dashed border-gray-300 bg-white p-4"
                      >
                        <div class="flex items-center gap-2 mb-3">
                          <PhotoIcon class="h-5 w-5 text-[#1e3a5f]" />
                          <p class="text-sm font-bold text-gray-900">Comprobante</p>
                        </div>

                        <div v-if="comprobanteUrl(pago)" class="space-y-3">
                          <img
                            :src="comprobanteUrl(pago)"
                            alt="Comprobante"
                            class="h-40 w-full rounded-xl border border-gray-200 object-cover bg-white"
                          />

                          <div class="grid grid-cols-2 gap-2">
                            <button
                              type="button"
                              @click="abrirPreview(comprobanteUrl(pago))"
                              class="inline-flex items-center justify-center rounded-xl border border-sky-200 bg-sky-50 px-3 py-2 text-xs font-bold text-sky-700 transition hover:bg-sky-100"
                            >
                              Ver
                            </button>

                            <a
                              :href="comprobanteUrl(pago)"
                              target="_blank"
                              class="inline-flex items-center justify-center gap-1 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs font-bold text-emerald-700 transition hover:bg-emerald-100"
                            >
                              <ArrowTopRightOnSquareIcon class="h-4 w-4" />
                              Abrir
                            </a>
                          </div>
                        </div>

                        <div
                          v-else
                          class="flex h-40 items-center justify-center rounded-xl border border-gray-200 bg-gray-50 px-4 text-center text-sm font-medium text-gray-500"
                        >
                          Sin comprobante cargado
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div
      v-if="previewComprobante"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-4"
      @click.self="cerrarPreview"
    >
      <div class="relative w-full max-w-5xl rounded-3xl bg-white p-4 shadow-2xl">
        <div class="mb-4 flex items-center justify-between">
          <h3 class="text-base font-extrabold text-gray-900">Vista previa del comprobante</h3>

          <button
            type="button"
            @click="cerrarPreview"
            class="rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50"
          >
            Cerrar
          </button>
        </div>

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-gray-50">
          <img
            :src="previewComprobante"
            alt="Comprobante"
            class="max-h-[80vh] w-full object-contain"
          />
        </div>
      </div>
    </div>
  </ContabilidadLayout>
</template>
