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
  empleado: Object,
  imagenTipoAptoUrl: String,
})

const exporting = ref(false)

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

const formatCurrency = (value) => {
  const num = Math.ceil(Number(value || 0))
  return new Intl.NumberFormat('es-CO', {
    style: 'currency',
    currency: 'COP',
    minimumFractionDigits: 0,
  }).format(num)
}

/* =========================
   HELPERS
========================= */
const esVenta = () => props.venta.tipo_operacion === 'venta'
const esSeparacion = () => props.venta.tipo_operacion === 'separacion'

const inmueble = computed(() => props.venta.apartamento || props.venta.local || null)
const esApartamento = computed(() => !!props.venta.apartamento)
const esLocal = computed(() => !!props.venta.local)

const cuotaSeparacionOperacion = computed(() => {
  return Number(props.venta.valor_separacion || props.venta.proyecto?.valor_min_separacion || 0)
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

const cuotasCuotaInicial = computed(() => {
  return cuotasPlan.value.filter((cuota) => {
    const concepto = String(cuota.concepto || '').toLowerCase()
    return (
      concepto.includes('cuota inicial') ||
      concepto.includes('saldo cuota inicial') ||
      concepto.includes('cuota inicial manual')
    )
  })
})

const numeroCuotas = computed(() => {
  if (cuotasCuotaInicial.value.length) {
    return cuotasCuotaInicial.value.length
  }
  return Number(props.venta.plazo_cuota_inicial_meses || 0)
})

const valorCuotaMensual = computed(() => {
  const cuotasCI = cuotasCuotaInicial.value
  if (cuotasCI.length) {
    return Number(cuotasCI[0]?.valor_cuota || 0)
  }
  const n = Number(props.venta.plazo_cuota_inicial_meses || 0)
  const saldoAmortizar = Number(
    props.venta.saldo_cuota_inicial ??
      Math.max(
        0,
        Number(props.venta.cuota_inicial || 0) -
          Number(props.venta.valor_separacion || props.venta.proyecto?.valor_min_separacion || 0)
      )
  )
  return n > 0 ? Math.round(saldoAmortizar / n) : 0
})

const desgloseEconomico = computed(() => {
  return {
    valor_total: props.venta.valor_total || 0,
    valor_cuota_inicial: props.venta.cuota_inicial || 0,
    cuota_separacion: cuotaSeparacionOperacion.value || 0,
    saldo_cuota_inicial:
      props.venta.saldo_cuota_inicial ??
      (props.venta.cuota_inicial || 0) - (cuotaSeparacionOperacion.value || 0) ??
      0,
    no_cuotas: Number(numeroCuotas.value || 0),
    frecuencia_cuota_inicial_meses: props.venta.frecuencia_cuota_inicial_meses || 0,
    valor_cuota_mensual: Number(valorCuotaMensual.value || 0),
    saldo_restante: props.venta.valor_restante || 0,
  }
})

// ===== Parqueadero =====
const parqueaderoInfo = computed(() => {
  const a = props.venta?.apartamento
  if (!a) return { tiene: false, texto: 'No aplica (Local)' }
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
   HELPERS PDF (estilo cotizador)
========================= */
function formatPercent(v) {
  if (v === null || v === undefined || v === '') return '—'

  return `${Number(v || 0).toLocaleString('es-CO', {
    minimumFractionDigits: 0,
    maximumFractionDigits: 4,
  })}%`
}

function setFont(doc, size = 12, style = 'normal') {
  doc.setFont('times', style); // helvetica, times, courier
  doc.setFontSize(size);
}

function imageFormatFromSrc(src = '') {
  const clean = String(src).split('?')[0].toLowerCase()
  if (clean.endsWith('.jpg') || clean.endsWith('.jpeg')) return 'JPEG'
  return 'PNG'
}

function loadImageSafe(src) {
  return new Promise((resolve) => {
    if (!src) {
      resolve(null)
      return
    }
    const img = new Image()
    img.crossOrigin = 'anonymous'
    img.onload = () => resolve({ img, format: imageFormatFromSrc(src), src })
    img.onerror = () => resolve(null)
    img.src = src
  })
}

function drawImageContain(doc, imageData, x, y, w, h) {
  if (!imageData?.img) return false
  const img = imageData.img
  const imgW = img.naturalWidth || img.width
  const imgH = img.naturalHeight || img.height
  if (!imgW || !imgH) return false
  const ratio = Math.min(w / imgW, h / imgH)
  const drawW = imgW * ratio
  const drawH = imgH * ratio
  const drawX = x + (w - drawW) / 2
  const drawY = y + (h - drawH) / 2
  doc.addImage(img, imageData.format, drawX, drawY, drawW, drawH)
  return true
}

function drawInlineKV(doc, label, value, x, y, options = {}) {
  const {
    labelSize = 10,
    valueSize = 10,
    labelStyle = 'bold',
    valueStyle = 'normal',
    maxWidth = null,
    lineHeight = 5,
  } = options

  const safeLabel = `${String(label ?? '')}${label ? ': ' : ''}`
  const safeValue = value === null || value === undefined || value === '' ? '—' : String(value)

  setFont(doc, labelSize, labelStyle)
  doc.text(safeLabel, x, y)
  const labelWidth = doc.getTextWidth(safeLabel)
  setFont(doc, valueSize, valueStyle)

  if (maxWidth) {
    const lines = doc.splitTextToSize(safeValue, Math.max(maxWidth - labelWidth, 15))
    doc.text(lines, x + labelWidth, y)
    return y + Math.max(lines.length, 1) * lineHeight
  }

  doc.text(safeValue, x + labelWidth, y)
  return y + lineHeight
}

// Header estilo cotizador
function drawHeaderCotizacion(doc, logoAyc, titulo, currentPage, totalPages) {
  const pageWidth = doc.internal.pageSize.getWidth()
  const M = 13

  if (logoAyc) {
    drawImageContain(doc, logoAyc, 10, 0, 60, 43)
  } else {
    setFont(doc, 18, 'bold')
    doc.setTextColor(0, 0, 0)
    doc.text(titulo, 18, 25)
  }

  setFont(doc, 10, 'normal')
  doc.setTextColor(0, 0, 0)
  const textPage = `Página ${currentPage} de ${totalPages}`
  const textWidth = doc.getTextWidth(textPage)
  doc.text(textPage, pageWidth - textWidth - M, 22)

  doc.setDrawColor(0, 0, 0)
  doc.setLineWidth(0.45)
  doc.line(M, 33, pageWidth - M, 33)
}

function aplicarEncabezadoEnTodasLasPaginas(doc, logoAyc, titulo) {
  const totalPages = doc.internal.getNumberOfPages()
  for (let i = 1; i <= totalPages; i++) {
    doc.setPage(i)
    drawHeaderCotizacion(doc, logoAyc, titulo, i, totalPages)
  }
}

// Footer estilo cotizador
function drawFooterCotizacion(doc, asesor, logoOlize) {
  const pageHeight = doc.internal.pageSize.getHeight()
  const footerY = pageHeight - 30

  setFont(doc, 9, 'normal')
  doc.setTextColor(128, 128, 128)
  doc.text('Datos del Asesor', 13, footerY)

  doc.setTextColor(128, 128, 128)
  setFont(doc, 9, 'normal')
  doc.text(`${asesor?.nombre ?? ''} ${asesor?.apellido ?? ''}`.trim() || '—', 13, footerY + 5)
  doc.text(asesor?.telefono ?? '—', 13, footerY + 10)
  doc.text(asesor?.email ?? '—', 13, footerY + 15)

  if (logoOlize) {
    drawImageContain(doc, logoOlize, 160, footerY - 2, 36, 24)
  } else {
    setFont(doc, 10, 'bold')
    doc.setTextColor(128, 128, 128)
    doc.text('Olize Constructora', 160, footerY + 12)
  }
}

function aplicarFooterEnTodasLasPaginas(doc, asesor, logoOlize) {
  const totalPages = doc.internal.getNumberOfPages()
  for (let i = 1; i <= totalPages; i++) {
    doc.setPage(i)
    drawFooterCotizacion(doc, asesor, logoOlize)
  }
}

function normalizarTextoBasico(value) {
  return String(value || '')
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .toLowerCase()
    .trim()
}

/* =========================
   PDF EXPORT (formato cotizador)
========================= */
async function exportVentaPDF() {
  const v = props.venta
  if (!v) return

  exporting.value = true

  try {
    const doc = new jsPDF({ orientation: 'portrait', unit: 'mm', format: 'a4' })
    const pageWidth = doc.internal.pageSize.getWidth()
    const M = 12.7

    const formatMoney = (value) => {
      const num = Math.ceil(Number(value || 0))
      return new Intl.NumberFormat('es-CO', {
        style: 'currency',
        currency: 'COP',
        maximumFractionDigits: 0,
      }).format(num)
    }

    const formatDatePDF = (date) => {
      if (!date) return '—'
      const d = new Date(date)
      if (Number.isNaN(d.getTime())) return '—'
      return d.toLocaleDateString('es-CO', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
      })
    }

    const formatDateTable = (date) => {
      if (!date) return '—'
      const d = new Date(date)
      if (Number.isNaN(d.getTime())) return '—'
      return d.toLocaleDateString('es-CO', {
        year: 'numeric',
        month: '2-digit',
      })
    }

    const safe = (x) => (x === null || x === undefined || x === '' ? '—' : String(x))

    const tipoPlanLabel = (tipo) => {
      const labels = {
        cuota_inicial_mensual: 'Cuota inicial mensual',
        cuota_inicial_contado: 'Cuota inicial de contado',
        pago_total_diferido: 'Pago total diferido',
        especial_manual: 'Plan especial manual',
        condiciones_proyecto: 'Condiciones económicas del proyecto',
      }
      return labels[tipo] || tipo || '—'
    }

    const getPlanSnapshot = () => {
      if (!v.plan_pago_snapshot) return null
      if (typeof v.plan_pago_snapshot === 'object') return v.plan_pago_snapshot
      try {
        return JSON.parse(v.plan_pago_snapshot)
      } catch {
        return null
      }
    }

    const planSnapshot = getPlanSnapshot()
    const proyecto = v.proyecto || {}
    const asesor = v.empleado || props.empleado || {}

    const esApto = !!v.apartamento

    const adicionalSeleccionado = v.parqueadero || null
    const adicionalTipo = normalizarTextoBasico(adicionalSeleccionado?.tipo)
    const adicionalNumero = adicionalSeleccionado?.numero || ''
    const adicionalPrecio = Number(adicionalSeleccionado?.precio || 0)

    // Determinar si el inmueble tiene parqueadero base
    let tieneParqueaderoBase = false
    if (esApto) {
      tieneParqueaderoBase = v.apartamento?.tiene_parqueadero === true
    }

    // 1. Determinar si el adicional ES un parqueadero de vehículo
    const esParqueaderoVehiculo =
      adicionalTipo.includes('parqueadero') ||
      adicionalTipo.includes('vehiculo') ||
      adicionalTipo.includes('vehículo') ||
      adicionalTipo === 'moto' ||
      adicionalTipo === 'carro' ||
      adicionalTipo === 'auto'

    // 2. Determinar si el adicional ES un depósito
    const esDeposito =
      adicionalTipo.includes('deposito') ||
      adicionalTipo.includes('depósito') ||
      adicionalTipo === 'bodega' ||
      adicionalTipo === 'almacén'

    // 3. Variables específicas
    const tieneParqueaderoAdicional = esParqueaderoVehiculo
    const tieneCuartoUtil = esDeposito

    // 4. Textos para mostrar
    const parqueaderoAdicionalTexto = tieneParqueaderoAdicional ? 'Sí' : 'No'
    const cuartoUtilTexto = tieneCuartoUtil ? 'Sí' : 'No'

    const tieneDepositoBase = Boolean(v.apartamento?.deposito)
    const parqueaderoTexto = tieneParqueaderoBase ? 'Sí' : 'No'
    const depositoTexto = tieneDepositoBase || esDeposito ? 'Sí' : 'No'

    const adicionalTexto = adicionalSeleccionado
      ? `${adicionalSeleccionado.tipo}${adicionalNumero ? ' ' + adicionalNumero : ''}`
      : 'No aplica'

    // Cargar imágenes
    const logoAyc = await loadImageSafe('/images/logo-ayc.png')
    const logoOlize = await loadImageSafe('/images/logo-olize.png')

    let planoInmueble = null
    if (esApto && props.imagenTipoAptoUrl) {
      planoInmueble = await loadImageSafe(props.imagenTipoAptoUrl)
    }

    const nombreProyecto = safe(proyecto?.nombre)
    const tituloPDF = `${esVenta() ? 'Venta' : 'Separación'} · ${nombreProyecto}`

    const valorBase = Number(v.valor_base || 0)
    const valorParqueadero = Number(v.parqueadero?.precio || 0)
    const valorTotal = Number(v.valor_total || 0)
    const valorDescuento = Number(v.valor_descuento || 0)
    const valorTotalSinDescuento = Number(
      v.valor_total_sin_descuento || valorBase + valorParqueadero || valorTotal
    )

    const cuotaSep = Number(v.valor_separacion || proyecto?.valor_min_separacion || 0)
    const cuotaInicial = Number(v.cuota_inicial || 0)
    const saldoCuotaInicial = Number(v.saldo_cuota_inicial ?? Math.max(cuotaInicial - cuotaSep, 0))
    const cuotaMensual = Number(valorCuotaMensual.value || 0)
    const valorRestante = Number(v.valor_restante || Math.max(valorTotal - cuotaInicial, 0) || 0)

    const frecuenciaTexto = (() => {
      if (esSeparacion()) return 'No Aplica'
      if (v.plan_pago_tipo === 'especial_manual') return 'No aplica - cuotas manuales'
      if (!v.frecuencia_cuota_inicial_meses) return 'No aplica'
      return `${v.frecuencia_cuota_inicial_meses} meses`
    })()
    // Porcentajes del plan
    const porcentajeCuotaInicial = Number(planSnapshot?.porcentaje_cuota_inicial || 0)
    const porcentajeEscritura = Number(planSnapshot?.porcentaje_escritura || 0)

    // ==================
    // PÁGINA 1
    // ==================

    // Título principal (igual que cotizador: alineado derecha, debajo del header)
    setFont(doc, 22, 'normal')
    doc.setTextColor(0, 0, 0)
    doc.text(`Negociación ${nombreProyecto}`, pageWidth - M, 43, { align: 'right' })

    setFont(doc, 11, 'normal')
    doc.text(
      `Fecha de ${esVenta() ? 'Venta' : 'Separación'} : ${formatDatePDF(v.fecha_venta)}`,
      pageWidth - M,
      50,
      { align: 'right' }
    )

    // --- Datos del Cliente ---
    setFont(doc, 18, 'normal')
    doc.setTextColor(0, 0, 0)
    doc.text('Datos del Cliente:', M, 65)

    drawInlineKV(doc, '', safe(v.cliente?.nombre), M + 5, 74, {
      labelSize: 11,
      valueSize: 11,
      labelStyle: 'normal',
      valueStyle: 'normal',
    })
    drawInlineKV(doc, '', safe(v.documento_cliente), M + 5, 80, {
      labelSize: 11,
      valueSize: 11,
      labelStyle: 'normal',
      valueStyle: 'normal',
    })
    drawInlineKV(doc, '', safe(v.cliente?.telefono), pageWidth / 2 + 5, 74, {
      labelSize: 11,
      valueSize: 11,
      labelStyle: 'normal',
      valueStyle: 'normal',
    })

    drawInlineKV(doc, '', safe(v.cliente?.correo), pageWidth / 2 + 5, 80, {
      labelSize: 11,
      valueSize: 11,
      labelStyle: 'normal',
      valueStyle: 'normal',
    })
    drawInlineKV(doc, '', safe(v.cliente?.direccion), M + 5, 86, {
      labelSize: 11,
      valueSize: 11,
      labelStyle: 'normal',
      valueStyle: 'normal',
    })

    // --- Información del Inmueble ---
    setFont(doc, 18, 'normal')
    doc.setTextColor(0, 0, 0)
    doc.text('Información del Inmueble:', M, 100)

    // Plano / imagen tipo apartamento (igual que cotizador)
    const planoX = 40
    const planoY = 108
    const planoW = 130
    const planoH = 100

    doc.setDrawColor(0, 0, 0)
    doc.setLineWidth(0.25)
    doc.rect(planoX, planoY, planoW, planoH)

    if (planoInmueble) {
      drawImageContain(doc, planoInmueble, planoX + 2, planoY + 2, planoW - 4, planoH - 4)
    } else {
      doc.setFillColor(242, 242, 242)
      doc.rect(planoX + 0.2, planoY + 0.2, planoW - 0.4, planoH - 0.4, 'F')
      setFont(doc, 11, 'normal')
      doc.setTextColor(120, 120, 120)
      doc.text('Plano no disponible', planoX + planoW / 2, planoY + planoH / 2, { align: 'center' })
      setFont(doc, 9, 'normal')
      doc.text(
        'No se encontró imagen asociada al tipo de apartamento.',
        planoX + planoW / 2,
        planoY + planoH / 2 + 7,
        { align: 'center' }
      )
    }

    // Datos inmueble - columna izquierda
    const col1X = M + 5
    const col2X = pageWidth / 2 + 5
    let yDatos = 220

    if (esApto) {
      drawInlineKV(doc, 'Piso', safe(v.apartamento?.piso_torre?.nivel), col1X, yDatos)
      drawInlineKV(doc, 'Número', safe(v.apartamento?.numero), col1X, yDatos + 6)
      drawInlineKV(
        doc,
        'Habitaciones',
        safe(
          v.apartamento?.tipo_apartamento?.cantidad_habitaciones ||
            v.apartamento?.tipoApartamento?.cantidad_habitaciones
        ),
        col1X,
        yDatos + 12
      )
      drawInlineKV(
        doc,
        'Baños',
        safe(
          v.apartamento?.tipo_apartamento?.cantidad_banos ||
            v.apartamento?.tipoApartamento?.cantidad_banos
        ),
        col1X,
        yDatos + 18
      )
      drawInlineKV(doc, 'Parqueadero', parqueaderoTexto, col1X, yDatos + 24)
      drawInlineKV(doc, 'Parqueadero Adicional', parqueaderoAdicionalTexto, col2X, yDatos)
      drawInlineKV(doc, 'Cuarto Útil', cuartoUtilTexto, col2X, yDatos + 6)
      drawInlineKV(
        doc,
        'Área Construida',
        `${safe(v.apartamento?.tipo_apartamento?.area_construida || v.apartamento?.tipoApartamento?.area_construida)} m²`,
        col2X,
        yDatos + 12
      )
      drawInlineKV(
        doc,
        'Área Privada',
        `${safe(v.apartamento?.tipo_apartamento?.area_privada || v.apartamento?.tipoApartamento?.area_privada)} m²`,
        col2X,
        yDatos + 18
      )
    } else {
      drawInlineKV(doc, 'Número', safe(v.local?.numero), col1X, yDatos)
      drawInlineKV(doc, 'Piso', safe(v.local?.piso_torre?.nivel), col1X, yDatos + 6)
      drawInlineKV(doc, 'Torre', safe(v.local?.torre?.nombre_torre), col1X, yDatos + 12)
      drawInlineKV(doc, 'Área Total', `${safe(v.local?.area_total_local)} m²`, col2X, yDatos)
      drawInlineKV(doc, 'Tipo', 'Local Comercial', col2X, yDatos + 6)
    }

    // ==================
    // PÁGINA 2
    // ==================
    doc.addPage()
    // --- Desglose Económico ---
    setFont(doc, 22, 'normal')
    doc.text('Desglose Económico:', M, 43)

    setFont(doc, 16, 'normal')
    doc.text('Tipo de Negocio:', M, 55)

    setFont(doc, 14, 'normal')
    drawInlineKV(doc, 'Porcentaje Cuota Inicial', formatPercent(porcentajeCuotaInicial), 16, 65)

    setFont(doc, 14, 'normal')
    drawInlineKV(doc, 'Cuota de Separación', formatMoney(cuotaSep), 16, 71)

    setFont(doc, 14, 'normal')
    drawInlineKV(
      doc,
      'Beneficio',
      safe(planSnapshot?.beneficio_comercial || 'No aplica'),
      113,
      65,
      {
        maxWidth: 78,
      }
    )

    drawInlineKV(doc, 'Valor Total', formatMoney(valorTotal), M, 80, {
      labelSize: 16,
      valueSize: 14,
    })

    drawInlineKV(doc, 'Deposito Adicional', formatMoney(valorParqueadero), 18, 88)
    drawInlineKV(doc, 'Valor Cuota Inicial', formatMoney(cuotaInicial), 18, 94)
    drawInlineKV(doc, 'Cuota de Separación', formatMoney(cuotaSep), 18, 100)
    drawInlineKV(doc, 'Saldo Cuota Inicial', formatMoney(saldoCuotaInicial), 18, 106)

    drawInlineKV(
      doc,
      'No. Cuotas',
      esSeparacion() ? 'No Aplica' : safe(numeroCuotas.value),
      113,
      88
    )

    drawInlineKV(
      doc,
      'Valor Cuota Mensual',
      esSeparacion() ? 'No Aplica' : formatMoney(cuotaMensual),
      113,
      94
    )

    drawInlineKV(
      doc,
      'Saldo Escrituración',
      esSeparacion() ? 'No Aplica' : formatMoney(valorRestante),
      113,
      100
    )

    // setFont(doc, 14, 'normal')
    // drawInlineKV(doc, 'Valor Inmueble', formatMoney(valorBase || valorTotal), M + 5, 160)
    // drawInlineKV(doc, 'Parqueadero/Depósito Adic.', formatMoney(valorParqueadero), M + 5, 166)
    // drawInlineKV(doc, 'Valor Total sin Descuento', formatMoney(valorTotalSinDescuento), M + 5, 172)
    // drawInlineKV(doc, 'Descuento Aplicado', formatMoney(valorDescuento), M + 5, 178)
    // drawInlineKV(doc, 'Valor Total', formatMoney(valorTotal), M, 186, {
    //   labelSize: 14,
    //   valueSize: 12,
    //   labelStyle: 'bold',
    // })

    // drawInlineKV(doc, 'Cuota de Separación', formatMoney(cuotaSep), pageWidth / 2 + 5, 160)
    // drawInlineKV(
    //   doc,
    //   'Cuota Inicial',
    //   esSeparacion() ? 'No Aplica' : formatMoney(cuotaInicial),
    //   pageWidth / 2 + 5,
    //   166
    // )
    // drawInlineKV(
    //   doc,
    //   'Saldo Cuota Inicial',
    //   esSeparacion() ? 'No Aplica' : formatMoney(saldoCuotaInicial),
    //   pageWidth / 2 + 5,
    //   172
    // )
    // drawInlineKV(
    //   doc,
    //   'Plazo Cuota Inicial',
    //   esSeparacion() ? 'No Aplica' : `${safe(v.plazo_cuota_inicial_meses)} meses`,
    //   pageWidth / 2 + 5,
    //   178
    // )
    // drawInlineKV(doc, 'Frecuencia Pago', frecuenciaTexto, pageWidth / 2 + 5, 184)
    // drawInlineKV(
    //   doc,
    //   'No. Cuotas',
    //   esSeparacion() ? 'No Aplica' : safe(numeroCuotas.value),
    //   pageWidth / 2 + 5,
    //   190
    // )
    // drawInlineKV(
    //   doc,
    //   'Valor Cuota Mensual',
    //   esSeparacion() ? 'No Aplica' : formatMoney(cuotaMensual),
    //   pageWidth / 2 + 5,
    //   196
    // )
    // drawInlineKV(
    //   doc,
    //   'Valor Restante',
    //   esSeparacion() ? 'No Aplica' : formatMoney(valorRestante),
    //   pageWidth / 2 + 5,
    //   202
    // )

    if (esSeparacion()) {
      drawInlineKV(
        doc,
        'Fecha Límite Separación',
        formatDatePDF(v.fecha_limite_separacion),
        M + 5,
        194
      )
    }

    // drawInlineKV(
    //   doc,
    //   'Forma de Pago',
    //   safe(v.forma_pago?.forma_pago || v.formaPago?.forma_pago),
    //   M + 5,
    //   210
    // )

    const cuotasRows = (v.plan_amortizacion?.cuotas || [])
      .slice()
      .sort((a, b) => Number(a.numero_cuota || 0) - Number(b.numero_cuota || 0))

    if (cuotasRows.length) {
      setFont(doc, 18, 'normal')
      doc.text('Tabla de Amortización:', M, 123)

      let finalYTable = 0

      autoTable(doc, {
        startY: 129,
        tableWidth: 'auto',
        head: [['# Cuota', 'Mes', 'Valor', 'Valor Restante']],
        body: cuotasRows.map((c, index) => {
          const numeroCuota = index === 0 ? '0' : String(index)

          const concepto = (() => {
            if (index === 0) return 'Valor Separación'
            if (index === cuotasRows.length - 1) return 'Valor Restante'
            return formatDateTable(c.fecha_vencimiento)
          })()

          return [numeroCuota, concepto, formatMoney(c.valor_cuota), formatMoney(c.saldo)]
        }),
        theme: 'grid',
        margin: {
          left: M,
          right: M,
          top: 43,
          bottom: 42,
        },
        headStyles: {
          fillColor: [255, 255, 255],
          textColor: [0, 0, 0],
          lineColor: [0, 0, 0],
          lineWidth: 0.2,
          font: 'times',
          fontStyle: 'normal',
          fontSize: 11,
          halign: 'center',
        },
        bodyStyles: {
          textColor: [0, 0, 0],
          lineColor: [0, 0, 0],
          lineWidth: 0.15,
          font: 'times',
          fontStyle: 'normal',
          fontSize: 9,
          halign: 'center',
          cellPadding: 2,
        },
        columnStyles: {
          0: { cellWidth: 25 },
          1: { cellWidth: 55 },
          2: { cellWidth: 52 },
          3: { cellWidth: 52 },
        },
        // Encabezado se repite en cada página
        head: [['# Cuota', 'Mes', 'Valor', 'Valor Restante']],
        didDrawPage: function (data) {
          finalYTable = data.cursor.y
        },
      })
    }

    // ==================
    // PÁGINA FINAL: Observaciones
    // ==================
    doc.addPage()

    setFont(doc, 16, 'normal')
    doc.text('Aclaraciones Importantes', M, 43)

    const aclaraciones = [
      'CLAUSULA PENAL: DIEZ POR CIENTO (10%) SOBRE EL VALOR APORTADO AL MOMENTO DEL RETIRO VOLUNTARIO O POR INCUMPLIMIENTO DE LOS PLAZOS DE PAGO.',
      'LOS RENDER USADOS EN LA PUBLICIDAD SON UNA APROXIMACIÓN A LA REALIDAD. Las áreas, animaciones y diseños pueden variar en el desarrollo arquitectónico y constructivo. Solo es válido lo acordado en la promesa de compraventa.',
      'Todo material publicitario (brochures, web, redes, prensa), renders e imágenes tiene carácter ilustrativo e informativo. No modifica lo pactado contractualmente salvo que se incorpore expresamente.',
      'Salvo indicación expresa, no se incluyen muebles, electrodomésticos, decoración ni equipamiento mostrado en piezas publicitarias. La entrega se realiza conforme a la ficha técnica y el inventario de entrega.',
      'Las áreas, distribuciones y especificaciones pueden registrar ajustes razonables debido a tolerancias constructivas, instalaciones u obligaciones técnicas. Dichos ajustes no afectarán la funcionalidad esencial del inmueble.',
    ]

    function drawJustifiedText(doc, text, x, y, maxWidth, lineHeight = 4.4, fontSize = 9) {
      setFont(doc, fontSize, 'normal')

      // Dividir el texto en líneas
      const lines = doc.splitTextToSize(text, maxWidth)
      let currentY = y

      lines.forEach((line, index) => {
        const isLastLine = index === lines.length - 1

        if (isLastLine || line.trim().length === 0) {
          // Última línea o línea vacía: alineación izquierda normal
          doc.text(line.trim(), x, currentY)
        } else {
          // Justificar la línea
          const words = line.trim().split(/\s+/)
          const wordCount = words.length

          if (wordCount <= 1) {
            doc.text(line, x, currentY)
          } else {
            // Calcular el ancho total de las palabras sin espacios
            let totalWordsWidth = 0
            const wordWidths = words.map((word) => doc.getTextWidth(word))
            totalWordsWidth = wordWidths.reduce((sum, width) => sum + width, 0)

            // Espacio disponible para distribuir
            const availableSpace = maxWidth - totalWordsWidth
            const spaceBetweenWords = availableSpace / (wordCount - 1)

            // Construir la línea posicionando cada palabra
            let currentX = x
            words.forEach((word, idx) => {
              doc.text(word, currentX, currentY)
              if (idx < words.length - 1) {
                currentX += wordWidths[idx] + spaceBetweenWords
              }
            })
          }
        }

        currentY += lineHeight
      })

      return currentY
    }

    let yAclaracion = 54
    let yFinalAclaraciones = yAclaracion

    aclaraciones.forEach((texto, index) => {
      setFont(doc, 9, 'normal')
      doc.text(`${index + 1}.`, M + 1, yAclaracion)

      yAclaracion = drawJustifiedText(
        doc,
        texto,
        M + 8,
        yAclaracion,
        170, // maxWidth
        4.4, // lineHeight
        9 // fontSize
      )

      yAclaracion += 1
    })

    yFinalAclaraciones = yAclaracion

    setFont(doc, 16, 'normal')
    doc.text('Aceptacion del Cliente:', M, yFinalAclaraciones + 15)
    doc.text('Recibido por:', 113, yFinalAclaraciones + 15)

    doc.text('Firma:', M, yFinalAclaraciones + 50)
    doc.text('Firma:', 113, yFinalAclaraciones + 50)
    doc.text(safe(v.cliente?.nombre), M, yFinalAclaraciones + 56)
    doc.text('Constructora', 113, yFinalAclaraciones + 56)
    doc.text(safe(v.documento_cliente), M, yFinalAclaraciones + 62)

    // Aplicar encabezado y footer en TODAS las páginas (igual que cotizador)
    aplicarEncabezadoEnTodasLasPaginas(doc, logoAyc, tituloPDF)
    aplicarFooterEnTodasLasPaginas(doc, asesor, logoOlize)

    const nombreArchivoProyecto = safe(proyecto?.nombre)
      .replace(/[^\w\s-]/g, '')
      .replace(/\s+/g, '-')
    const nombreArchivoCliente = safe(v.cliente?.nombre)
      .replace(/[^\w\s-]/g, '')
      .replace(/\s+/g, '-')
    const numeroInmueble = safe(v.apartamento?.numero || v.local?.numero).replace(/\s+/g, '-')

    doc.save(`Operacion-${nombreArchivoProyecto}-${nombreArchivoCliente}-${numeroInmueble}.pdf`)
  } finally {
    exporting.value = false
  }
}
</script>

<template>
  <VentasLayout :empleado="empleado">
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
            {{ esVenta() ? 'Venta' : 'Separación' }}
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
              <span class="text-gray-600">Habitaciones</span>
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

            <div class="flex justify-between bg-gray-50 rounded-lg px-4 py-3">
              <span class="text-gray-600">Parqueadero/Deposito Adicional</span>
              <div v-if="venta?.parqueadero" class="mt-2 text-sm text-gray-900">
                <span class="font-semibold text-gray-900">
                  {{ venta.parqueadero.tipo }}
                </span>
              </div>
              <div v-else class="font-semibold text-gray-900">No</div>
            </div>
          </div>
        </div>

        <div class="border-t border-gray-200 pt-4">
          <h2 class="text-lg font-bold text-gray-900 mb-3">Desglose Económico</h2>
          <ul class="space-y-2">
            <li class="flex justify-between text-gray-700">
              <span>Plan de venta</span>
              <span class="font-semibold">{{
                venta.plan_pago_nombre || 'Condiciones del proyecto'
              }}</span>
            </li>

            <li class="flex justify-between text-gray-700">
              <span>Descuento aplicado</span>
              <span class="font-semibold">{{ formatCurrency(venta.valor_descuento || 0) }}</span>
            </li>

            <li class="flex justify-between text-gray-700">
              <span>Valor Apartamento:</span>
              <span class="font-semibold">{{
                formatCurrency(venta.valor_base || venta.valor_total)
              }}</span>
            </li>

            <li class="flex justify-between text-gray-700">
              <span>Valor Parqueadero/Deposito Adicional:</span>
              <span class="font-semibold">{{
                formatCurrency(venta.parqueadero?.precio || 0)
              }}</span>
            </li>

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
                esSeparacion() ? 'No Aplica' : formatCurrency(desgloseEconomico.saldo_cuota_inicial)
              }}</span>
            </li>

            <li class="flex justify-between text-gray-700">
              <span>Frecuencia Pago Cuota Inicial:</span>
              <span class="font-semibold">{{
                esSeparacion()
                  ? 'No Aplica'
                  : desgloseEconomico.frecuencia_cuota_inicial_meses + ' meses'
              }}</span>
            </li>

            <li class="flex justify-between text-gray-700">
              <span>No. Cuotas:</span>
              <span class="font-semibold">{{
                esSeparacion() ? 'No Aplica' : desgloseEconomico.no_cuotas
              }}</span>
            </li>

            <li class="flex justify-between text-gray-700">
              <span>Valor Cuota Mensual:</span>
              <span class="font-semibold">{{
                esSeparacion() ? 'No Aplica' : formatCurrency(desgloseEconomico.valor_cuota_mensual)
              }}</span>
            </li>

            <li
              class="flex justify-between font-semibold text-gray-900 border-t border-gray-200 pt-2"
            >
              <span>Saldo Restante:</span>
              <span>{{
                esSeparacion() ? 'No Aplica' : formatCurrency(desgloseEconomico.saldo_restante)
              }}</span>
            </li>

            <li v-if="esSeparacion()" class="flex justify-between text-gray-700">
              <span>Fecha Límite Separación:</span>
              <span>{{ formatDate(venta.fecha_limite_separacion) }}</span>
            </li>
          </ul>
        </div>

        <div class="pt-4 border-t border-gray-200">
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
