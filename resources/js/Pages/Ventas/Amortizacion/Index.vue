<script setup>
import { ref } from 'vue'
import VentasLayout from '@/Components/VentasLayout.vue'
import jsPDF from 'jspdf'
import autoTable from 'jspdf-autotable'

const props = defineProps({
  proyectos: Array,
  clientes: Array,
  empleado: Object,
})

const proyectoId = ref('')
const clienteId = ref('')
const ventasCliente = ref([])
const ventaSeleccionada = ref(null)

const amortizacion = ref([])
const cargando = ref(false)
const mostrarResumen = ref(false)

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

  if (!proyectoId.value || !clienteId.value) return

  const res = await fetch(
    `/plan-amortizacion-venta/ventas-por-cliente?id_proyecto=${proyectoId.value}&documento_cliente=${clienteId.value}`
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
    const valorSeparacion = v.valor_separacion ?? v.valor_min_separacion ?? 0
    const saldoCuotaInicial = cuotaInicial - valorSeparacion
    const plazo = v.plazo

    if (!v.fecha_venta) {
      alert('La venta no tiene fecha de venta definida.')
      cargando.value = false
      return
    }

    // AHORA: el calendario inicia en el mes de la venta
    const fechaVenta = new Date(v.fecha_venta)
    const yearBase = fechaVenta.getFullYear()
    const monthBase = fechaVenta.getMonth() // 0 = enero

    // Misma lógica de cuotas que queremos replicar en el dashboard
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
        fecha: fechaStr, // YYYY-MM
        valor_cuota: valor,
        saldo_final: Math.max(saldo, 0),
      })
    }

    // Resumen
    ventaSeleccionada.value.saldo_cuota_inicial = saldoCuotaInicial
    ventaSeleccionada.value.valor_separacion = valorSeparacion

    mostrarResumen.value = true
    cargando.value = false
  }, 400)
}

/* --------------------------
   EXPORTAR PDF ELEGANTE
--------------------------- */
function exportPDF() {
  if (!ventaSeleccionada.value) return

  const v = ventaSeleccionada.value

  const doc = new jsPDF({
    orientation: 'portrait',
    unit: 'mm',
    format: 'letter',
  })

  /* ============================
     LOGO
     ============================ */
  const logo = new Image()
  logo.src = '/images/logo-ayc.png'
  doc.addImage(logo, 'PNG', 16, 13, 14, 10)

  /* ============================
     TÍTULO
     ============================ */
  doc.setFont('Helvetica', 'bold')
  doc.setFontSize(20)
  doc.setTextColor(30, 58, 95)
  doc.text('PLAN DE AMORTIZACIÓN – CUOTA INICIAL', 105, 20, { align: 'center' })

  /* ============================
     ENCABEZADO CON FONDO
     ============================ */
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

  // const mitad = Math.ceil(info.length / 2) // 6 y 5
  // const col1 = info.slice(0, mitad)
  // const col2 = info.slice(mitad)

  // let y1 = 48
  // let y2 = 48

  // col1.forEach((linea) => {
  //   doc.text(linea, 20, y1)
  //   y1 += 6
  // })

  // col2.forEach((linea) => {
  //   doc.text(linea, 110, y2)
  //   y2 += 6
  // })

  // Separar en 2 columnas equilibradas
  const mitad = Math.ceil(info.length / 2) // 6 y 5
  const col1 = info.slice(0, mitad)
  const col2 = info.slice(mitad)

  // Coordenadas de inicio
  const col1X = 22
  const col2X = 112
  const startY = 48
  const lineHeight = 6

  // Ajuste tipográfico fino
  doc.setFontSize(10)
  doc.setTextColor(40, 40, 40)

  // Columna izquierda
  let y = startY
  col1.forEach((linea) => {
    doc.text(linea, col1X, y, { align: 'left' })
    y += lineHeight
  })

  // Columna derecha
  y = startY
  col2.forEach((linea) => {
    doc.text(linea, col2X, y, { align: 'left' })
    y += lineHeight
  })

  /* ============================
     TABLA
     ============================ */
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

    didDrawPage: function (data) {
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

  /* ============================
     NOMBRE DEL ARCHIVO PDF
     ============================ */
  const nombrePDF = `Amortizacion - ${v.proyecto} - ${v.inmueble}.pdf`
  doc.save(nombrePDF)
}
</script>

<template>
  <VentasLayout :empleado="empleado">
    <div class="p-6 space-y-6">
      <h1 class="text-3xl font-bold text-brand-900">Plan de Amortización</h1>

      <!-- FILTROS -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- Proyecto -->
        <div>
          <label class="font-semibold text-brand-800">Proyecto</label>
          <select
            v-model="proyectoId"
            @change="cargarVentas"
            class="w-full border rounded-lg p-2 shadow-sm"
          >
            <option value="">Seleccione...</option>
            <option v-for="p in proyectos" :key="p.id_proyecto" :value="p.id_proyecto">
              {{ p.nombre }}
            </option>
          </select>
        </div>

        <!-- Cliente -->
        <div>
          <label class="font-semibold text-brand-800">Cliente</label>
          <select
            v-model="clienteId"
            @change="cargarVentas"
            class="w-full border rounded-lg p-2 shadow-sm"
          >
            <option value="">Seleccione...</option>
            <option v-for="c in clientes" :key="c.documento" :value="c.documento">
              {{ c.nombre }}
            </option>
          </select>
        </div>

        <!-- Venta -->
        <div>
          <label class="font-semibold text-brand-800">Venta</label>
          <select v-model="ventaSeleccionada" class="w-full border rounded-lg p-2 shadow-sm">
            <option value="">Seleccione...</option>
            <option v-for="v in ventasCliente" :key="v.id_venta" :value="v">
              {{ v.inmueble }} - {{ formatMoney(v.valor_total) }}
            </option>
          </select>
        </div>
      </div>

      <!-- BOTÓN -->
      <button
        @click="generarAmortizacion"
        class="px-6 py-2 bg-brand-700 text-white rounded-lg shadow hover:bg-brand-800"
      >
        Generar Amortización
      </button>

      <!-- LOADING -->
      <div v-if="cargando" class="text-brand-700 font-semibold mt-4 animate-pulse">
        Calculando amortización...
      </div>

      <!-- RESUMEN -->
      <div
        v-if="mostrarResumen && ventaSeleccionada"
        class="grid grid-cols-1 md:grid-cols-3 gap-4 bg-white p-6 rounded-xl shadow mt-6 border"
      >
        <div>
          <div class="text-sm text-gray-500">Proyecto</div>
          <div class="font-semibold text-brand-900">{{ ventaSeleccionada.proyecto }}</div>
        </div>
        <div>
          <div class="text-sm text-gray-500">Cliente</div>
          <div class="font-semibold text-brand-900">{{ ventaSeleccionada.cliente }}</div>
        </div>
        <div>
          <div class="text-sm text-gray-500">Inmueble</div>
          <div class="font-semibold text-brand-900">{{ ventaSeleccionada.inmueble }}</div>
        </div>

        <div>
          <div class="text-sm text-gray-500">Valor Total</div>
          <div class="font-semibold text-brand-900">
            {{ formatMoney(ventaSeleccionada.valor_total) }}
          </div>
        </div>
        <div>
          <div class="text-sm text-gray-500">Tipo de Pago</div>
          <div class="font-semibold text-brand-900">{{ ventaSeleccionada.forma_pago }}</div>
        </div>
        <div>
          <div class="text-sm text-gray-500">Cuota Inicial</div>
          <div class="font-semibold text-brand-900">
            {{ formatMoney(ventaSeleccionada.cuota_inicial) }}
          </div>
        </div>
        <!-- Valor Separación -->
        <div>
          <div class="text-sm text-gray-500">Valor Separación</div>
          <div class="font-semibold text-brand-900">
            {{ formatMoney(ventaSeleccionada.valor_separacion) }}
          </div>
        </div>

        <!-- Saldo Cuota Inicial -->
        <div>
          <div class="text-sm text-gray-500">Saldo Cuota Inicial</div>
          <div class="font-semibold text-brand-900">
            {{ formatMoney(ventaSeleccionada.saldo_cuota_inicial) }}
          </div>
        </div>
        <div>
          <div class="text-sm text-gray-500">Plazo</div>
          <div class="font-semibold text-brand-900">{{ ventaSeleccionada.plazo }} meses</div>
        </div>
        <div>
          <div class="text-sm text-gray-500">Valor Restante</div>
          <div class="font-semibold text-brand-900">
            {{ formatMoney(ventaSeleccionada.valor_total - ventaSeleccionada.cuota_inicial) }}
          </div>
        </div>
        <div>
          <div class="text-sm text-gray-500">Fecha Venta</div>
          <div class="font-semibold text-brand-900">
            {{ formatDate(ventaSeleccionada.fecha_venta) }}
          </div>
        </div>
      </div>

      <!-- TABLA AMORTIZACION -->
      <div v-if="amortizacion.length" class="mt-8">
        <h2 class="text-xl font-bold text-brand-900 mb-4">Tabla de Amortización</h2>

        <table class="w-full border rounded-lg shadow">
          <thead class="bg-brand-100 text-brand-900">
            <tr>
              <th class="p-2 border text-center">#</th>
              <th class="p-2 border text-center">Mes</th>
              <th class="p-2 border text-center">Valor Cuota</th>
              <th class="p-2 border text-center">Saldo Pendiente</th>
            </tr>
          </thead>

          <tbody>
            <tr
              v-for="c in amortizacion"
              :key="c.numero"
              class="border-b odd:bg-gray-50 text-center"
            >
              <td class="p-2 border">{{ c.numero }}</td>
              <td class="p-2 border">{{ c.fecha }}</td>
              <td class="p-2 border">{{ formatMoney(c.valor_cuota) }}</td>
              <td class="p-2 border">{{ formatMoney(c.saldo_final) }}</td>
            </tr>
          </tbody>
        </table>

        <button
          @click="exportPDF"
          class="mt-4 px-6 py-2 bg-blue-700 text-white rounded-lg shadow hover:bg-blue-600"
        >
          Exportar PDF
        </button>
      </div>
    </div>
  </VentasLayout>
</template>
