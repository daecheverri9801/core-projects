<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import VentasLayout from '@/Components/VentasLayout.vue'
import jsPDF from 'jspdf'
import autoTable from 'jspdf-autotable'

import {
  ArrowLeftIcon,
  PencilIcon,
  CreditCardIcon,
  UserIcon,
  HomeIcon,
  DocumentTextIcon,
  ArrowDownTrayIcon,
} from '@heroicons/vue/24/outline'

const props = defineProps({
  venta: Object,
  imagenTipoAptoUrl: String,
})

/* =========================
   FORMATOS
========================= */
function formatDate(date) {
  if (!date) return '—'
  return new Date(date).toLocaleDateString('es-CO', {
    year: 'numeric',
    month: 'numeric',
    day: 'numeric',
  })
}

function formatDateISO(date) {
  if (!date) return ''
  try {
    return new Date(date).toISOString().split('T')[0]
  } catch {
    return ''
  }
}

const formatCurrency = (value) =>
  new Intl.NumberFormat('es-CO', {
    style: 'currency',
    currency: 'COP',
    minimumFractionDigits: 0,
  }).format(value || 0)

/* =========================
   HELPERS
========================= */
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
const plan = computed(() => props.venta.plan_amortizacion || null)
const cuotasPlan = computed(() => plan.value?.cuotas || [])

const numeroCuotas = computed(() => {
  if (cuotasPlan.value.length) return cuotasPlan.value.length
  return props.venta.plazo_cuota_inicial_meses || 0
})

const valorCuotaMensual = computed(() => {
  const n = Number(numeroCuotas.value || 0)
  const saldoAmortizar =
    Number(props.venta.cuota_inicial || 0) - Number(cuotaSeparacionProyecto.value || 0)
  return (saldoAmortizar > 0 ? saldoAmortizar : 0) / n || 0
})

const desgloseEconomico = computed(() => {
  return {
    valor_total: props.venta.valor_total || 0,
    valor_cuota_inicial: props.venta.cuota_inicial || 0,
    cuota_separacion: cuotaSeparacionProyecto.value || 0,
    saldo_cuota_inicial:
      (props.venta.cuota_inicial || 0) - (cuotaSeparacionProyecto.value || 0) || 0,
    no_cuotas: Number(numeroCuotas.value || 0),
    frecuencia_cuota_inicial_meses: props.venta.frecuencia_cuota_inicial_meses || 0,
    valor_cuota_mensual: Number(valorCuotaMensual.value || 0),
    saldo_restante: props.venta.valor_restante || 0,
  }
})

// ===== Parqueadero =====
const parqueaderoInfo = computed(() => {
  // normalmente solo aplica a apto
  const a = props.venta?.apartamento
  if (!a) return { tiene: false, texto: 'No aplica (Local)' }

  // Caso 2: relación 1 a N -> a.parqueaderos[]
  const arr = a.parqueaderos
  if (Array.isArray(arr) && arr.length) {
    const nums = arr
      .map((x) => x?.numero ?? x?.codigo ?? x?.nombre ?? x?.id_parqueadero)
      .filter(Boolean)
      .join(', ')
    return { tiene: true, texto: nums ? `Sí` : 'Sí' }
  }

  return { tiene: false, texto: 'No' }
})

/* =========================
   PDF EXPORT (estructura tipo amortización)
========================= */
async function exportVentaPDF() {
  const v = props.venta
  if (!v) return

  const doc = new jsPDF({ orientation: 'portrait', unit: 'mm', format: 'letter' })

  // =========================
  // Helpers
  // =========================
  const formatMoney = (value) =>
    new Intl.NumberFormat('es-CO', {
      style: 'currency',
      currency: 'COP',
      maximumFractionDigits: 0,
    }).format(Number(value || 0))

  const formatDate = (date) => {
    if (!date) return '—'
    const d = new Date(date)
    if (Number.isNaN(d.getTime())) return '—'
    return d.toLocaleDateString('es-CO', { year: 'numeric', month: '2-digit', day: '2-digit' })
  }

  const safe = (x) => (x === null || x === undefined || x === '' ? '—' : String(x))

  const getZonas = (proyecto) => {
    const arr =
      proyecto?.zonas_sociales || proyecto?.zonasSociales || proyecto?.zonas_sociales_proyecto || []
    if (!Array.isArray(arr) || !arr.length) return '—'
    return arr
      .map((z) => z?.nombre)
      .filter(Boolean)
      .join(', ')
  }

  const parqueaderoTexto = (() => {
    const a = v.apartamento
    if (!a) return 'No aplica (Local)'

    if (a.parqueadero) {
      const numero =
        a.parqueadero.numero ??
        a.parqueadero.codigo ??
        a.parqueadero.nombre ??
        a.parqueadero.id_parqueadero ??
        '—'
      return `Sí (${numero})`
    }

    if (Array.isArray(a.parqueaderos) && a.parqueaderos.length) {
      const nums = a.parqueaderos
        .map((x) => x?.numero ?? x?.codigo ?? x?.nombre ?? x?.id_parqueadero)
        .filter(Boolean)
        .join(', ')
      return nums ? `Sí` : 'Sí'
    }

    if (typeof a.tiene_parqueadero === 'boolean') {
      return a.tiene_parqueadero ? 'Sí' : 'No'
    }

    return 'No'
  })()

  const proyecto = v.proyecto || {}
  const barrio = proyecto?.ubicacion?.barrio ?? '—'
  const direccion = proyecto?.ubicacion?.direccion ?? '—'
  const proyectoUbicacion = `${barrio} - ${direccion}`
  const proyectoZonas = getZonas(proyecto)

  const esApto = !!v.apartamento
  const inm = v.apartamento || v.local || {}

  const inmuebleInfo = esApto
    ? [
        [
          'Tipo',
          safe(v.apartamento?.tipo_apartamento?.nombre || v.apartamento?.tipoApartamento?.nombre),
        ],
        ['Número', safe(v.apartamento?.numero)],
        ['Torre', safe(v.apartamento?.torre?.nombre_torre)],
        ['Piso', safe(v.apartamento?.piso_torre?.nivel)],
        [
          'Alcobas',
          safe(
            v.apartamento?.tipo_apartamento?.cantidad_habitaciones ||
              v.apartamento?.tipoApartamento?.cantidad_habitaciones
          ),
        ],
        [
          'Baños',
          safe(
            v.apartamento?.tipo_apartamento?.cantidad_banos ||
              v.apartamento?.tipoApartamento?.cantidad_banos
          ),
        ],
        [
          'Área construida',
          `${safe(v.apartamento?.tipo_apartamento?.area_construida || v.apartamento?.tipoApartamento?.area_construida)} m²`,
        ],
        [
          'Área privada',
          `${safe(v.apartamento?.tipo_apartamento?.area_privada || v.apartamento?.tipoApartamento?.area_privada)} m²`,
        ],
        ['Parqueadero', parqueaderoTexto],
      ]
    : [
        ['Tipo', 'Local Comercial'],
        ['Número', safe(v.local?.numero)],
        ['Torre', safe(v.local?.torre?.nombre_torre)],
        ['Piso', safe(v.local?.piso_torre?.nivel)],
        ['Área total', `${safe(v.local?.area_total_local)} m²`],
      ]

  const cuotaSep = Number(proyecto?.valor_min_separacion || 0)
  const cuotaInicial = Number(v.cuota_inicial || 0)
  const saldoCuotaInicial = Math.max(0, cuotaInicial - cuotaSep)
  const valorTotal = Number(v.valor_total || 0)
  const valorRestante = Number(v.valor_restante || Math.max(0, valorTotal - cuotaInicial) || 0)

  const desglose = [
    ['Valor total', formatMoney(valorTotal)],
    ['Cuota inicial', formatMoney(cuotaInicial)],
    ['Cuota separación (proyecto)', formatMoney(cuotaSep)],
    ['Saldo cuota inicial', formatMoney(saldoCuotaInicial)],
    ['Plazo cuota inicial (meses)', safe(v.plazo_cuota_inicial_meses)],
    ['Frecuencia pago cuota inicial (meses)', safe(v.frecuencia_cuota_inicial_meses)],
    ['Saldo restante', formatMoney(valorRestante)],
    ['Forma de pago', safe(v.forma_pago?.forma_pago || v.formaPago?.forma_pago)],
    ['Fecha operación', formatDate(v.fecha_venta)],
  ]

  // =========================
  // Encabezado / Marca
  // =========================
  const headerY = 18

  // Logo (si existe). Si falla, continúa.
  try {
    const logo = new Image()
    logo.src = '/images/logo-ayc.png'
    doc.addImage(logo, 'PNG', 16, 12, 14, 10)
  } catch (e) {}

  doc.setFont('Helvetica', 'bold')
  doc.setFontSize(18)
  doc.setTextColor(30, 58, 95)
  doc.text('REPORTE DE OPERACIÓN', 105, headerY, { align: 'center' })

  doc.setFont('Helvetica', 'normal')
  doc.setFontSize(10)
  doc.setTextColor(90, 90, 90)
  doc.text(
    `${esApto ? 'Apartamento' : 'Local'} · ${v.tipo_operacion?.toUpperCase() || '—'} #${v.id_venta}`,
    105,
    headerY + 6,
    { align: 'center' }
  )

  // Línea
  doc.setDrawColor(220, 220, 220)
  doc.line(15, 30, 200, 30)

  // =========================
  // Estilos secciones
  // =========================
  let y = 36

  const sectionTitle = (title) => {
    doc.setFillColor(245, 247, 250)
    doc.roundedRect(15, y, 185, 10, 2, 2, 'F')

    doc.setFont('Helvetica', 'bold')
    doc.setFontSize(12)
    doc.setTextColor(30, 58, 95)
    doc.text(title, 18, y + 7)

    y += 14
  }

  const keyValueTable = (rows, options = {}) => {
    autoTable(doc, {
      startY: y,
      theme: 'grid',
      styles: {
        font: 'Helvetica',
        fontSize: 10,
        textColor: [40, 40, 40],
        cellPadding: 2.5,
        valign: 'middle',
      },
      headStyles: {
        fillColor: [30, 58, 95],
        textColor: [255, 255, 255],
        fontStyle: 'bold',
      },
      bodyStyles: {
        fillColor: [255, 255, 255],
      },
      alternateRowStyles: { fillColor: [248, 250, 252] },
      columnStyles: {
        0: { cellWidth: 60, fontStyle: 'bold', textColor: [55, 65, 81] },
        1: { cellWidth: 125 },
      },
      margin: { left: 15, right: 15 },
      body: rows.map(([k, val]) => [k, val]),
      ...options,
      didDrawPage: (data) => {
        // Footer por página
        const pageCount = doc.internal.getNumberOfPages()
        const currentPage = doc.internal.getCurrentPageInfo().pageNumber
        doc.setFont('Helvetica', 'normal')
        doc.setFontSize(9)
        doc.setTextColor(120, 120, 120)
        doc.text(`Generado: ${new Date().toLocaleString('es-CO')}`, 15, 277)
        doc.text(`Página ${currentPage} de ${pageCount}`, 105, 277, { align: 'center' })
        doc.text('Constructora A&C', 200 - 15, 277, { align: 'right' })
      },
    })
    y = doc.lastAutoTable.finalY + 8
  }

  const ensureSpace = (needed = 30) => {
    // Letter height ~279mm, footer 277
    if (y + needed > 265) {
      doc.addPage()
      y = 18
      // línea suave al inicio
      doc.setDrawColor(230, 230, 230)
      doc.line(15, y, 200, y)
      y += 10
    }
  }

  // =========================
  // 1) Datos del Proyecto
  // =========================
  ensureSpace(45)
  sectionTitle('1. Datos del Proyecto')
  keyValueTable([
    ['Nombre', safe(proyecto?.nombre)],
    ['Ubicación', safe(proyectoUbicacion)],
    ['Zonas sociales', safe(proyectoZonas)],
  ])

  // =========================
  // 2) Datos del Cliente
  // =========================
  ensureSpace(45)
  sectionTitle('2. Datos del Cliente')
  keyValueTable([
    ['Nombre', safe(v.cliente?.nombre)],
    ['Documento', safe(v.documento_cliente)],
    ['Teléfono', safe(v.cliente?.telefono)],
    ['Correo', safe(v.cliente?.correo)],
    ['Dirección', safe(v.cliente?.direccion)],
  ])

  // =========================
  // 3) Datos del Asesor
  // =========================
  ensureSpace(45)
  sectionTitle('3. Datos del Asesor')
  keyValueTable([
    ['Nombre', safe(`${v.empleado?.nombre || ''} ${v.empleado?.apellido || ''}`.trim())],
    ['Correo', safe(v.empleado?.email)],
    ['Teléfono', safe(v.empleado?.telefono)],
  ])

  // =========================
  // 4) Información del Inmueble
  // =========================
  ensureSpace(60)
  sectionTitle('4. Información del Inmueble')
  keyValueTable(inmuebleInfo)

  // =========================
  // 5) Imagen tipo apartamento (solo si existe)
  // =========================
  if (esApto && props.imagenTipoAptoUrl) {
    ensureSpace(80)
    sectionTitle('5. Imagen tipo de apartamento')

    // Caja contenedora
    const boxX = 15
    const boxW = 185
    const boxH = 60
    doc.setDrawColor(220, 220, 220)
    doc.setFillColor(250, 250, 250)
    doc.roundedRect(boxX, y, boxW, boxH, 2, 2, 'FD')

    // Cargar imagen y redimensionar manteniendo proporción (contain)
    const img = await new Promise((resolve) => {
      const im = new Image()
      im.crossOrigin = 'anonymous'
      im.onload = () => resolve(im)
      im.onerror = () => resolve(null)
      im.src = props.imagenTipoAptoUrl
    })

    if (img) {
      const iw = img.naturalWidth || img.width
      const ih = img.naturalHeight || img.height
      const scale = Math.min((boxW - 10) / iw, (boxH - 10) / ih) // padding 5mm
      const w = iw * scale
      const h = ih * scale
      const x = boxX + (boxW - w) / 2
      const yy = y + (boxH - h) / 2

      // jsPDF requiere base64 o elemento compatible; addImage soporta HTMLImageElement en muchos casos.
      // Si en tu entorno falla, convierte a canvas/base64.
      try {
        doc.addImage(img, 'JPEG', x, yy, w, h)
      } catch (e) {
        // fallback: intentar PNG
        try {
          doc.addImage(img, 'PNG', x, yy, w, h)
        } catch (e2) {
          doc.setFontSize(10)
          doc.setTextColor(120, 120, 120)
          doc.text('No fue posible incrustar la imagen en el PDF.', 18, y + 12)
        }
      }
    } else {
      doc.setFontSize(10)
      doc.setTextColor(120, 120, 120)
      doc.text('Imagen no disponible.', 18, y + 12)
    }

    y += boxH + 10
  }

  // =========================
  // 6) Desglose Económico
  // =========================
  ensureSpace(60)
  sectionTitle('6. Desglose Económico')
  keyValueTable(desglose)

  // Guardar
  const nombrePDF = `Venta_${safe(proyecto?.nombre).replaceAll(' ', '_')}_${v.cliente?.nombre}_Apto_${v.apartamento?.numero}.pdf`
  doc.save(nombrePDF)
}
</script>

<template>
  <VentasLayout>
    <Head title="Detalle de Operación" />

    <div class="flex justify-between items-center mb-6">
      <div class="flex items-center gap-3">
        <Link
          href="/ventas"
          class="inline-flex items-center text-sm text-gray-600 hover:text-[#1e3a5f] transition"
        >
          <ArrowLeftIcon class="w-4 h-4 mr-2" />
          Volver
        </Link>

        <button
          type="button"
          @click="exportVentaPDF"
          :disabled="exporting"
          class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-800 hover:bg-gray-50 transition disabled:opacity-60 disabled:cursor-not-allowed"
        >
          <span
            v-if="exporting"
            class="animate-spin w-4 h-4 border-2 border-gray-500 border-t-transparent rounded-full"
          />
          <ArrowDownTrayIcon v-else class="w-5 h-5 text-gray-600" />
          Exportar PDF
        </button>
      </div>

      <Link
        v-if="venta.tipo_operacion === 'separacion'"
        :href="route('ventas.convertir.form', venta.id_venta)"
        class="inline-flex items-center gap-2 bg-[#f4c430] text-gray-900 px-4 py-2 rounded-lg font-semibold hover:bg-[#e5b520] transition"
      >
        <PencilIcon class="w-5 h-5" /> Convertir a Venta
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

            <div class="flex justify-between bg-gray-50 rounded-lg px-4 py-3">
              <span class="text-gray-600">Parqueadero</span>
              <span class="font-semibold text-gray-900">{{ parqueaderoInfo.texto }}</span>
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
              <span>Frecuencia Pago Cuota Inicial:</span>
              <span class="font-semibold"
                >{{ desgloseEconomico.frecuencia_cuota_inicial_meses || '—' }} meses</span
              >
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
              class="w-full h-56 md:h-72 rounded-xl object-contain bg-gray-50"
              loading="lazy"
            />
          </div>
        </div>
      </div>
    </div>
  </VentasLayout>
</template>
