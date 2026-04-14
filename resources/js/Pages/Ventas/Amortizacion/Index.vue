<script setup>
import { ref, watch, reactive, computed } from 'vue'
import VentasLayout from '@/Components/VentasLayout.vue'
import jsPDF from 'jspdf'
import autoTable from 'jspdf-autotable'
import {
  ClipboardDocumentListIcon,
  BuildingOffice2Icon,
  UserIcon,
  MagnifyingGlassIcon,
  IdentificationIcon,
  DocumentTextIcon,
  SparklesIcon,
  BanknotesIcon,
  CalendarDaysIcon,
} from '@heroicons/vue/24/outline'

const props = defineProps({
  proyectos: Array,
  clientes: Array,
  empleado: Object,
})

const proyectoId = ref('')
const ventasCliente = ref([])
const ventaSeleccionada = ref(null)
const clientesFiltrados = ref([])
const amortizacion = ref([])
const cargando = ref(false)
const mostrarResumen = ref(false)

const clienteBusqueda = reactive({
  documento: '',
  error: '',
})

const clienteSeleccionado = ref(null)

const empleado = computed(() => props.empleado || null)
const proyectoSeleccionado = computed(
  () => props.proyectos.find((p) => String(p.id_proyecto) === String(proyectoId.value)) || null
)

/* --------------------------
   HELPERS UI
--------------------------- */
function fieldClass(disabled = false) {
  return [
    'w-full rounded-xl border px-4 py-3 text-sm shadow-sm transition',
    'focus:outline-none focus:ring-2 focus:ring-[#FFEA00] focus:border-[#FFEA00]',
    disabled
      ? 'bg-gray-100 border-gray-200 text-gray-500 cursor-not-allowed'
      : 'bg-white border-gray-300 text-gray-900',
  ].join(' ')
}

function labelClass() {
  return 'mb-2 block text-sm font-semibold text-gray-700'
}

function normalizarDocumento(value) {
  return String(value || '').replace(/\D/g, '')
}

function limpiarClienteSeleccionado() {
  clienteSeleccionado.value = null
  ventasCliente.value = []
  ventaSeleccionada.value = null
  mostrarResumen.value = false
  amortizacion.value = []
}

function limpiarBusquedaCliente() {
  clienteBusqueda.documento = ''
  clienteBusqueda.error = ''
  limpiarClienteSeleccionado()
}

function buscarCliente() {
  clienteBusqueda.error = ''
  mostrarResumen.value = false
  amortizacion.value = []
  ventasCliente.value = []
  ventaSeleccionada.value = null

  const documento = normalizarDocumento(clienteBusqueda.documento)

  if (!proyectoId.value) {
    limpiarClienteSeleccionado()
    clienteBusqueda.error = 'Selecciona primero un proyecto.'
    return
  }

  if (!documento) {
    limpiarClienteSeleccionado()
    clienteBusqueda.error = 'Ingresa el número de documento del cliente.'
    return
  }

  const encontrado = (clientesFiltrados.value || []).find(
    (c) => normalizarDocumento(c.documento) === documento
  )

  if (!encontrado) {
    limpiarClienteSeleccionado()
    clienteBusqueda.error = 'No se encontró un cliente de ese proyecto con ese documento.'
    return
  }

  clienteSeleccionado.value = encontrado
  cargarVentas()
}

/* --------------------------
   CARGAR CLIENTES DEL PROYECTO
--------------------------- */
async function cargarClientes() {
  if (!proyectoId.value) {
    clientesFiltrados.value = []
    clienteBusqueda.documento = ''
    ventasCliente.value = []
    ventaSeleccionada.value = null
    clienteSeleccionado.value = null
    mostrarResumen.value = false
    amortizacion.value = []
    return
  }

  const res = await fetch(
    `/plan-amortizacion-venta/clientes-por-proyecto?id_proyecto=${proyectoId.value}`
  )
  clientesFiltrados.value = await res.json()

  clienteBusqueda.documento = ''
  ventasCliente.value = []
  ventaSeleccionada.value = null
  clienteSeleccionado.value = null
  mostrarResumen.value = false
  amortizacion.value = []
  clienteBusqueda.error = ''
}

watch(proyectoId, () => {
  cargarClientes()
})

watch(
  () => clienteBusqueda.documento,
  (value) => {
    const limpio = normalizarDocumento(value)

    if (limpio !== value) {
      clienteBusqueda.documento = limpio
    }

    if (!limpio) {
      clienteBusqueda.error = ''
      limpiarClienteSeleccionado()
      return
    }

    if (
      clienteSeleccionado.value &&
      normalizarDocumento(clienteSeleccionado.value.documento) !== limpio
    ) {
      limpiarClienteSeleccionado()
    }

    if (clienteBusqueda.error) {
      clienteBusqueda.error = ''
    }
  }
)

/* --------------------------
   FORMATO MONEDA
--------------------------- */
function formatMoney(v) {
  return Number(v).toLocaleString('es-CO', {
    style: 'currency',
    currency: 'COP',
    maximumFractionDigits: 0,
  })
}

/* --------------------------
   FORMATO FECHA YYYY-MM-DD
--------------------------- */
function formatDate(dateStr) {
  if (!dateStr) return ''
  return new Date(dateStr).toISOString().split('T')[0]
}

/* --------------------------
   CARGAR VENTAS DEL CLIENTE
--------------------------- */
async function cargarVentas() {
  mostrarResumen.value = false
  amortizacion.value = []

  if (!proyectoId.value || !clienteSeleccionado.value?.documento) return

  const res = await fetch(
    `/plan-amortizacion-venta/ventas-por-cliente?id_proyecto=${proyectoId.value}&documento_cliente=${clienteSeleccionado.value.documento}`
  )
  ventasCliente.value = await res.json()
}

/* --------------------------
   GENERAR AMORTIZACIÓN
--------------------------- */
function generarAmortizacion() {
  if (!ventaSeleccionada.value) return alert('Seleccione una venta válida.')

  cargando.value = true
  amortizacion.value = []
  mostrarResumen.value = false

  setTimeout(() => {
    const v = ventaSeleccionada.value

    const cuotaInicial = v.cuota_inicial
    const valorSeparacion = v.valor_separacion ?? 0
    const saldoCuotaInicial = cuotaInicial - valorSeparacion
    const plazo = v.plazo

    if (!v.fecha_venta) {
      alert('La venta no tiene fecha de venta definida.')
      cargando.value = false
      return
    }

    const fechaVenta = new Date(v.fecha_venta)
    const yearBase = fechaVenta.getFullYear()
    const monthBase = fechaVenta.getMonth()

    const cuotaMensual = Math.round(saldoCuotaInicial / plazo)
    const residuo = saldoCuotaInicial - cuotaMensual * plazo

    let saldo = saldoCuotaInicial

    amortizacion.value = []

    for (let i = 1; i <= plazo; i++) {
      const fechaCuota = new Date(yearBase, monthBase + (i - 1), 1)
      const fechaStr = `${fechaCuota.getFullYear()}-${String(fechaCuota.getMonth() + 1).padStart(
        2,
        '0'
      )}`

      let valor = cuotaMensual
      if (i === plazo) valor += residuo

      saldo -= valor

      amortizacion.value.push({
        numero: i,
        fecha: fechaStr,
        valor_cuota: valor,
        saldo_final: Math.max(saldo, 0),
      })
    }

    ventaSeleccionada.value.saldo_cuota_inicial = saldoCuotaInicial
    ventaSeleccionada.value.valor_separacion = valorSeparacion

    mostrarResumen.value = true
    cargando.value = false
  }, 400)
}

/* --------------------------
   EXPORTAR PDF
--------------------------- */
function exportPDF() {
  if (!ventaSeleccionada.value) return

  const v = ventaSeleccionada.value

  const doc = new jsPDF({
    orientation: 'portrait',
    unit: 'mm',
    format: 'letter',
  })

  const logo = new Image()
  logo.src = '/images/logo-ayc.png'
  doc.addImage(logo, 'PNG', 16, 13, 14, 10)

  doc.setFont('Helvetica', 'bold')
  doc.setFontSize(20)
  doc.setTextColor(30, 58, 95)
  doc.text('PLAN DE AMORTIZACIÓN – CUOTA INICIAL', 105, 20, { align: 'center' })

  doc.setFillColor(245, 247, 250)
  doc.roundedRect(15, 40, 185, 45, 3, 3, 'F')

  doc.setFontSize(11)
  doc.setTextColor(0, 0, 0)

  const asesor = v.empleado ?? '—'
  const fechaFormateada = v.fecha_venta.split('T')[0]

  const saldoCuotaInicial = v.cuota_inicial - v.valor_separacion
  const valorRestante = v.valor_total - v.cuota_inicial

  const info = [
    `Proyecto: ${v.proyecto}`,
    `Cliente: ${v.cliente}`,
    `Inmueble: ${v.inmueble}`,
    `Precio del inmueble: ${formatMoney(v.valor_total)}`,
    `Tipo de pago: ${v.forma_pago ?? '—'}`,
    `Cuota inicial: ${formatMoney(v.cuota_inicial)}`,
    `Valor separación: ${formatMoney(v.valor_separacion)}`,
    `Saldo cuota inicial: ${formatMoney(saldoCuotaInicial)}`,
    `Valor restante: ${formatMoney(valorRestante)}`,
    `Plazo: ${v.plazo} meses`,
    `Fecha de venta: ${fechaFormateada}`,
    `Asesor: ${asesor}`,
  ]

  const mitad = Math.ceil(info.length / 2)
  const col1 = info.slice(0, mitad)
  const col2 = info.slice(mitad)

  const col1X = 22
  const col2X = 112
  const startY = 48
  const lineHeight = 6

  doc.setFontSize(10)
  doc.setTextColor(40, 40, 40)

  let y = startY
  col1.forEach((linea) => {
    doc.text(linea, col1X, y, { align: 'left' })
    y += lineHeight
  })

  y = startY
  col2.forEach((linea) => {
    doc.text(linea, col2X, y, { align: 'left' })
    y += lineHeight
  })

  autoTable(doc, {
    startY: 90,
    headStyles: {
      fillColor: [30, 58, 95],
      textColor: [255, 255, 255],
      halign: 'center',
      fontSize: 11,
    },
    styles: {
      halign: 'center',
      fontSize: 10,
    },
    alternateRowStyles: {
      fillColor: [245, 247, 250],
    },
    head: [['#', 'Mes', 'Valor Cuota', 'Saldo Pendiente']],
    body: amortizacion.value.map((c) => [
      c.numero,
      c.fecha,
      formatMoney(c.valor_cuota),
      formatMoney(c.saldo_final),
    ]),
    didDrawPage: function () {
      const pageCount = doc.internal.getNumberOfPages()
      const currentPage = doc.internal.getCurrentPageInfo().pageNumber

      const now = new Date()
      const formattedDate =
        now.getFullYear() +
        '-' +
        String(now.getMonth() + 1).padStart(2, '0') +
        '-' +
        String(now.getDate()).padStart(2, '0') +
        ' ' +
        String(now.getHours()).padStart(2, '0') +
        ':' +
        String(now.getMinutes()).padStart(2, '0')

      doc.setFontSize(9)
      doc.setTextColor(120, 120, 120)

      doc.text(`Generado el: ${formattedDate}`, 15, 277)
      doc.text(`Página ${currentPage} de ${pageCount}`, 105, 277, { align: 'center' })
      doc.text('Constructora A&C', 200 - 15, 277, { align: 'right' })
    },
  })

  const nombrePDF = `Amortizacion - ${v.proyecto} - ${v.inmueble}.pdf`
  doc.save(nombrePDF)
}
</script>

<template>
  <VentasLayout :empleado="empleado">
    <div class="space-y-6 p-4 sm:p-6">
      <!-- Header -->
      <section class="overflow-hidden rounded-3xl border border-gray-200 bg-white shadow-sm">
        <div class="bg-gradient-to-r from-[#FFEA00] via-[#FFF15C] to-[#FFF9B8] px-6 py-6">
          <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
            <div class="min-w-0">
              <div class="flex items-center gap-3 text-[#474100]">
                <div
                  class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white/70 shadow-sm"
                >
                  <ClipboardDocumentListIcon class="h-6 w-6" />
                </div>
                <div>
                  <h1 class="text-2xl font-extrabold tracking-tight sm:text-3xl">
                    Plan de amortización
                  </h1>
                  <p class="mt-1 text-sm text-[#474100]/80">
                    Genera el detalle de cuotas iniciales de una venta registrada.
                  </p>
                </div>
              </div>
            </div>

            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
              <div class="rounded-2xl border border-[#474100]/10 bg-white/70 px-4 py-3 shadow-sm">
                <p class="text-[11px] font-semibold uppercase tracking-wide text-[#474100]/70">
                  Proyecto
                </p>
                <p class="mt-1 truncate text-sm font-bold text-[#474100]">
                  {{ proyectoSeleccionado?.nombre || 'Pendiente' }}
                </p>
              </div>

              <div class="rounded-2xl border border-[#474100]/10 bg-white/70 px-4 py-3 shadow-sm">
                <p class="text-[11px] font-semibold uppercase tracking-wide text-[#474100]/70">
                  Cliente
                </p>
                <p class="mt-1 truncate text-sm font-bold text-[#474100]">
                  {{ clienteSeleccionado?.nombre || 'Pendiente' }}
                </p>
              </div>

              <div class="rounded-2xl border border-[#474100]/10 bg-white/70 px-4 py-3 shadow-sm">
                <p class="text-[11px] font-semibold uppercase tracking-wide text-[#474100]/70">
                  Venta
                </p>
                <p class="mt-1 truncate text-sm font-bold text-[#474100]">
                  {{ ventaSeleccionada?.inmueble || 'Pendiente' }}
                </p>
              </div>
            </div>
          </div>
        </div>
      </section>

      <div class="grid grid-cols-1 gap-6 xl:grid-cols-12">
        <!-- Principal -->
        <section class="xl:col-span-8 space-y-6">
          <div class="rounded-3xl border border-gray-200 bg-white shadow-sm">
            <div
              class="flex flex-col gap-3 border-b border-gray-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between"
            >
              <div class="flex items-center gap-2">
                <SparklesIcon class="h-5 w-5 text-[#1e3a5f]" />
                <h2 class="text-base font-extrabold text-gray-900">Datos para generar amortización</h2>
              </div>

              <div
                class="inline-flex w-fit items-center rounded-full border border-[#FFEA00]/40 bg-[#FFFDE6] px-3 py-1 text-xs font-bold text-[#756C00]"
              >
                Selecciona proyecto, cliente y venta
              </div>
            </div>

            <div class="p-5 grid grid-cols-1 gap-5 md:grid-cols-2">
              <!-- Proyecto -->
              <div class="md:col-span-2">
                <label :class="labelClass()">Proyecto</label>
                <div class="relative">
                  <select v-model="proyectoId" :class="fieldClass(false)" class="pl-11">
                    <option value="">Seleccione...</option>
                    <option v-for="p in proyectos" :key="p.id_proyecto" :value="p.id_proyecto">
                      {{ p.nombre }}
                    </option>
                  </select>
                </div>
              </div>

              <!-- Cliente -->
              <div class="md:col-span-2">
                <label :class="labelClass()">Cliente</label>

                <div class="rounded-2xl border border-gray-200 bg-gray-50/60 p-4">
                  <div class="flex flex-col gap-3 lg:flex-row">
                    <div class="relative flex-1">
                      <input
                        v-model="clienteBusqueda.documento"
                        type="text"
                        inputmode="numeric"
                        maxlength="20"
                        autocomplete="off"
                        :class="fieldClass(!proyectoId)"
                        class="pl-11"
                        placeholder="Digita el número de documento"
                        :disabled="!proyectoId"
                        @keyup.enter="buscarCliente"
                      />
                    </div>

                    <div class="flex gap-2">
                      <button
                        type="button"
                        @click="buscarCliente"
                        :disabled="!proyectoId"
                        class="inline-flex items-center justify-center gap-2 rounded-2xl bg-[#1e3a5f] px-5 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-[#2c5282] disabled:cursor-not-allowed disabled:opacity-60"
                      >
                        <MagnifyingGlassIcon class="h-4 w-4" />
                        Buscar
                      </button>

                      <button
                        type="button"
                        @click="limpiarBusquedaCliente"
                        class="inline-flex items-center justify-center rounded-2xl border border-gray-300 bg-white px-4 py-3 text-sm font-semibold text-gray-700 transition hover:bg-gray-50"
                      >
                        Limpiar
                      </button>
                    </div>
                  </div>

                  <p v-if="clienteBusqueda.error" class="mt-2 text-xs text-red-500">
                    {{ clienteBusqueda.error }}
                  </p>

                  <div
                    v-if="clienteSeleccionado"
                    class="mt-4 rounded-2xl border border-sky-200 bg-sky-50 p-4"
                  >
                    <div class="mb-3 flex items-center gap-2">
                      <div
                        class="flex h-10 w-10 items-center justify-center rounded-xl bg-white text-sky-700 shadow-sm"
                      >
                        <IdentificationIcon class="h-5 w-5" />
                      </div>
                      <div>
                        <h3 class="text-sm font-extrabold text-sky-900">Cliente encontrado</h3>
                        <p class="text-xs text-sky-700/80">
                          Selecciona ahora la venta asociada
                        </p>
                      </div>
                    </div>

                    <div class="grid grid-cols-1 gap-3 text-sm md:grid-cols-2">
                      <div class="rounded-xl bg-white/80 px-4 py-3">
                        <p class="text-[11px] font-semibold uppercase tracking-wide text-sky-700">
                          Nombre
                        </p>
                        <p class="mt-1 font-semibold text-gray-900">
                          {{ clienteSeleccionado.nombre || '—' }}
                        </p>
                      </div>

                      <div class="rounded-xl bg-white/80 px-4 py-3">
                        <p class="text-[11px] font-semibold uppercase tracking-wide text-sky-700">
                          Documento
                        </p>
                        <p class="mt-1 font-semibold text-gray-900">
                          {{ clienteSeleccionado.documento || '—' }}
                        </p>
                      </div>
                    </div>
                  </div>

                  <p v-else class="mt-3 text-xs text-gray-500">
                    Busca el cliente por número de documento dentro del proyecto seleccionado.
                  </p>
                </div>
              </div>

              <!-- Venta -->
              <div class="md:col-span-2">
                <label :class="labelClass()">Venta</label>
                <div class="relative">
                  <DocumentTextIcon
                    class="pointer-events-none absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400"
                  />
                  <select
                    v-model="ventaSeleccionada"
                    :class="fieldClass(!clienteSeleccionado || ventasCliente.length === 0)"
                    class="pl-11"
                    :disabled="!clienteSeleccionado || ventasCliente.length === 0"
                  >
                    <option value="">Seleccione...</option>
                    <option v-for="v in ventasCliente" :key="v.id_venta" :value="v">
                      {{ v.inmueble }} - {{ formatMoney(v.valor_total) }}
                    </option>
                  </select>
                </div>

                <p v-if="clienteSeleccionado && ventasCliente.length === 0" class="mt-2 text-xs text-amber-600">
                  El cliente no tiene ventas disponibles para este proyecto.
                </p>
              </div>
            </div>
          </div>

          <!-- Resumen -->
          <div
            v-if="mostrarResumen && ventaSeleccionada"
            class="rounded-3xl border border-gray-200 bg-white shadow-sm overflow-hidden"
          >
            <div class="border-b border-gray-200 px-5 py-4">
              <h2 class="text-base font-extrabold text-gray-900">Resumen de la operación</h2>
            </div>

            <div class="grid grid-cols-1 gap-4 p-5 md:grid-cols-3">
              <div class="rounded-2xl bg-gray-50 px-4 py-3">
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Proyecto</div>
                <div class="mt-1 font-semibold text-[#1e3a5f]">{{ ventaSeleccionada.proyecto }}</div>
              </div>

              <div class="rounded-2xl bg-gray-50 px-4 py-3">
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Cliente</div>
                <div class="mt-1 font-semibold text-[#1e3a5f]">{{ ventaSeleccionada.cliente }}</div>
              </div>

              <div class="rounded-2xl bg-gray-50 px-4 py-3">
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Inmueble</div>
                <div class="mt-1 font-semibold text-[#1e3a5f]">{{ ventaSeleccionada.inmueble }}</div>
              </div>

              <div class="rounded-2xl bg-gray-50 px-4 py-3">
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Valor Total</div>
                <div class="mt-1 font-semibold text-[#1e3a5f]">
                  {{ formatMoney(ventaSeleccionada.valor_total) }}
                </div>
              </div>

              <div class="rounded-2xl bg-gray-50 px-4 py-3">
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Tipo de Pago</div>
                <div class="mt-1 font-semibold text-[#1e3a5f]">{{ ventaSeleccionada.forma_pago }}</div>
              </div>

              <div class="rounded-2xl bg-gray-50 px-4 py-3">
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Cuota Inicial</div>
                <div class="mt-1 font-semibold text-[#1e3a5f]">
                  {{ formatMoney(ventaSeleccionada.cuota_inicial) }}
                </div>
              </div>

              <div class="rounded-2xl bg-gray-50 px-4 py-3">
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Valor Separación</div>
                <div class="mt-1 font-semibold text-[#1e3a5f]">
                  {{ formatMoney(ventaSeleccionada.valor_separacion) }}
                </div>
              </div>

              <div class="rounded-2xl bg-gray-50 px-4 py-3">
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Saldo Cuota Inicial</div>
                <div class="mt-1 font-semibold text-[#1e3a5f]">
                  {{ formatMoney(ventaSeleccionada.saldo_cuota_inicial) }}
                </div>
              </div>

              <div class="rounded-2xl bg-gray-50 px-4 py-3">
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Plazo</div>
                <div class="mt-1 font-semibold text-[#1e3a5f]">
                  {{ ventaSeleccionada.plazo }} meses
                </div>
              </div>

              <div class="rounded-2xl bg-gray-50 px-4 py-3">
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Valor Restante</div>
                <div class="mt-1 font-semibold text-[#1e3a5f]">
                  {{ formatMoney(ventaSeleccionada.valor_total - ventaSeleccionada.cuota_inicial) }}
                </div>
              </div>

              <div class="rounded-2xl bg-gray-50 px-4 py-3">
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Fecha Venta</div>
                <div class="mt-1 font-semibold text-[#1e3a5f]">
                  {{ formatDate(ventaSeleccionada.fecha_venta) }}
                </div>
              </div>
            </div>
          </div>

          <!-- Tabla -->
          <div
            v-if="amortizacion.length"
            class="rounded-3xl border border-gray-200 bg-white shadow-sm overflow-hidden"
          >
            <div class="flex flex-col gap-3 border-b border-gray-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
              <h2 class="text-base font-extrabold text-gray-900">Tabla de amortización</h2>

              <button
                @click="exportPDF"
                class="inline-flex items-center justify-center gap-2 rounded-2xl bg-[#1e3a5f] px-5 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-[#2c5282]"
              >
                <DocumentTextIcon class="h-5 w-5" />
                Exportar PDF
              </button>
            </div>

            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-[#FFFDE6]">
                  <tr>
                    <th class="px-4 py-3 text-center text-xs font-bold uppercase tracking-wider text-[#756C00]">#</th>
                    <th class="px-4 py-3 text-center text-xs font-bold uppercase tracking-wider text-[#756C00]">Mes</th>
                    <th class="px-4 py-3 text-center text-xs font-bold uppercase tracking-wider text-[#756C00]">Valor Cuota</th>
                    <th class="px-4 py-3 text-center text-xs font-bold uppercase tracking-wider text-[#756C00]">Saldo Pendiente</th>
                  </tr>
                </thead>

                <tbody class="divide-y divide-gray-200 bg-white">
                  <tr
                    v-for="c in amortizacion"
                    :key="c.numero"
                    class="odd:bg-gray-50 hover:bg-yellow-50/40 transition"
                  >
                    <td class="px-4 py-3 text-center text-sm text-gray-900">{{ c.numero }}</td>
                    <td class="px-4 py-3 text-center text-sm text-gray-900">{{ c.fecha }}</td>
                    <td class="px-4 py-3 text-center text-sm font-semibold text-[#1e3a5f]">
                      {{ formatMoney(c.valor_cuota) }}
                    </td>
                    <td class="px-4 py-3 text-center text-sm font-semibold text-[#1e3a5f]">
                      {{ formatMoney(c.saldo_final) }}
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </section>

        <!-- Lateral -->
        <aside class="xl:col-span-4 space-y-6">
          <div class="sticky top-6 space-y-6">
            <div class="rounded-3xl border border-gray-200 bg-white shadow-sm overflow-hidden">
              <div class="bg-gradient-to-r from-[#1e3a5f] to-[#2c5282] px-5 py-4">
                <div class="flex items-center gap-2 text-white">
                  <BanknotesIcon class="h-5 w-5" />
                  <h3 class="text-base font-extrabold">Resumen</h3>
                </div>
              </div>

              <div class="p-5 space-y-4">
                <div class="rounded-2xl bg-gray-50 px-4 py-3">
                  <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Proyecto</p>
                  <p class="mt-1 text-sm font-bold text-gray-900">
                    {{ proyectoSeleccionado?.nombre || 'Pendiente' }}
                  </p>
                </div>

                <div class="rounded-2xl bg-gray-50 px-4 py-3">
                  <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Cliente</p>
                  <p class="mt-1 text-sm font-bold text-gray-900">
                    {{ clienteSeleccionado?.nombre || 'Pendiente' }}
                  </p>
                </div>

                <div class="rounded-2xl bg-gray-50 px-4 py-3">
                  <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Venta</p>
                  <p class="mt-1 text-sm font-bold text-gray-900">
                    {{ ventaSeleccionada?.inmueble || 'Pendiente' }}
                  </p>
                </div>

                <div class="rounded-2xl border border-[#FFEA00]/50 bg-[#FFFDE6] px-4 py-4">
                  <p class="text-xs font-semibold uppercase tracking-wide text-[#756C00]">
                    Cuota inicial
                  </p>
                  <p class="mt-1 text-xl font-extrabold text-[#474100]">
                    {{ ventaSeleccionada ? formatMoney(ventaSeleccionada.cuota_inicial) : '—' }}
                  </p>
                </div>

                <button
                  @click="generarAmortizacion"
                  :disabled="cargando || !ventaSeleccionada"
                  class="inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-[#FFEA00] to-[#D1C000] px-5 py-3.5 text-sm font-extrabold text-[#474100] shadow-sm transition hover:shadow-md disabled:cursor-not-allowed disabled:opacity-60"
                >
                  <ClipboardDocumentListIcon class="h-5 w-5" />
                  {{ cargando ? 'Calculando...' : 'Generar amortización' }}
                </button>

                <div
                  v-if="cargando"
                  class="rounded-2xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm font-medium text-blue-700"
                >
                  Calculando amortización. Espera un momento.
                </div>

                <div class="rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3 text-xs text-gray-600">
                  Primero selecciona el proyecto, luego busca el cliente por documento y finalmente
                  elige la venta para generar el plan.
                </div>
              </div>
            </div>
          </div>
        </aside>
      </div>
    </div>
  </VentasLayout>
</template>