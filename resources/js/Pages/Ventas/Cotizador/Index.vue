<script setup>
import { ref, computed } from 'vue'
import VentasLayout from '@/Components/VentasLayout.vue'
import jsPDF from 'jspdf'
import autoTable from 'jspdf-autotable'

const props = defineProps({
  proyectos: Array,
  clientes: Array,
  inmuebles: Object, // { apartamentos: [], locales: [] }
})

/* ------------------------------------------------------
 *  FORM VARIABLES
 * ------------------------------------------------------ */
const proyectoId = ref('')
const clienteId = ref('')
const inmuebleId = ref('')
const plazo = ref('')
const cargando = ref(false)

/* ------------------------------------------------------
 *  COMPUTED SELECTIONS
 * ------------------------------------------------------ */
const proyecto = computed(() => props.proyectos.find((p) => p.id_proyecto == proyectoId.value))

const cliente = computed(() => props.clientes.find((c) => c.documento == clienteId.value))

/* ------------------------------------------------------
 *  LISTA COMBINADA DE INMUEBLES
 * ------------------------------------------------------ */
const listaInmuebles = computed(() => {
  const aps = props.inmuebles.apartamentos ?? []
  const locs = props.inmuebles.locales ?? []

  return [
    ...aps.map((a) => ({
      tipo: 'apartamento',
      id: a.id_apartamento,
      numero: a.numero,
      valor_final: a.valor_final,
      torre: a.torre,
      pisoTorre: a.pisoTorre,
      tipoApartamento: a.tipo_apartamento || a.tipoApartamento || null,
      valor_min_separacion: a.valor_min_separacion || null,
    })),

    ...locs.map((l) => ({
      tipo: 'local',
      id: l.id_local,
      numero: l.numero,
      valor_final: l.valor_final,
      torre: l.torre,
      pisoTorre: null,
      tipoApartamento: null, // los locales no tienen tipoApartamento
    })),
  ]
})

const inmueble = computed(() => props.inmuebles.find((i) => i.id === Number(inmuebleId.value)))

/* ------------------------------------------------------
 *  PLAZOS DISPONIBLES
 * ------------------------------------------------------ */
const plazosDisponibles = computed(() => {
  if (!proyecto.value) return []
  return Array.from({ length: proyecto.value.plazo_cuota_inicial_meses }, (_, i) => i + 1)
})

/* ------------------------------------------------------
 *  FORMAT MONEDA
 * ------------------------------------------------------ */
function formatMoney(v) {
  return Number(v || 0).toLocaleString('es-CO', {
    style: 'currency',
    currency: 'COP',
    maximumFractionDigits: 0,
  })
}

/* ------------------------------------------------------
 *  GENERAR PDF
 * ------------------------------------------------------ */
async function generarPDF() {
  if (!proyecto.value) return alert('Debe seleccionar un proyecto.')
  if (!cliente.value) return alert('Debe seleccionar un cliente.')
  if (!inmueble.value) return alert('Debe seleccionar un inmueble.')
  if (!plazo.value) return alert('Debe seleccionar un plazo.')

  if (inmueble.value.tipo === 'apartamento') {
    if (!inmueble.value.tipoApartamento) {
      console.error('ERROR: inmueble.tipoApartamento es NULL')
      console.log('Inmueble:', inmueble.value)
      alert('Este apartamento no tiene un tipo definido.')
      return
    }

    if (!inmueble.value.tipoApartamento.nombre) {
      console.error('ERROR: No hay nombre en tipoApartamento')
      return
    }
  }

  cargando.value = true
  await new Promise((r) => setTimeout(r, 400))

  /* ------------------------------------------------------
   *  PDF
   * ------------------------------------------------------ */
  const doc = new jsPDF()

  /* LOGO */
  const logo = new Image()
  logo.src = '/images/logo-ayc.png'
  doc.addImage(logo, 'PNG', 15, 10, 25, 25)

  /* Encabezado */
  doc.setFont('Helvetica', 'bold')
  doc.setFontSize(18)
  doc.text(`Cotización - ${proyecto.value.nombre}`, 105, 20, { align: 'center' })

  const now = new Date()
  const fechaGen = now.toISOString().slice(0, 16).replace('T', ' ')
  doc.setFontSize(10)
  doc.text(`Fecha Generación: ${fechaGen}`, 105, 28, { align: 'center' })

  /* ------------------------------------------------------
   *  DATOS DEL CLIENTE (2 columnas)
   * ------------------------------------------------------ */
  doc.setFontSize(14)
  doc.text('Datos del Cliente', 15, 45)
  doc.setFontSize(10)

  // Columna izquierda
  doc.text(`Nombre: ${cliente.value.nombre}`, 15, 55)
  doc.text(`Documento: ${cliente.value.documento}`, 15, 61)
  doc.text(`Dirección: ${cliente.value.direccion ?? '-'}`, 15, 67)

  // Columna derecha
  doc.text(`Teléfono: ${cliente.value.telefono ?? '-'}`, 110, 55)
  doc.text(`Correo: ${cliente.value.correo ?? '-'}`, 110, 61)

  /* ------------------------------------------------------
   *  INFORMACIÓN DEL INMUEBLE
   * ------------------------------------------------------ */
  doc.setFontSize(14)
  doc.text('Información del Inmueble', 15, 80)
  doc.setFontSize(10)

  doc.text(`Número: ${inmueble.value.numero}`, 15, 88)
  doc.text(`Piso: ${inmueble.value.pisoTorre?.nivel ?? '-'}`, 15, 94)
  doc.text(`Torre: ${inmueble.value.torre?.nombre_torre ?? '-'}`, 15, 100)

  if (inmueble.value.tipo === 'apartamento') {
    const t = inmueble.value.tipoApartamento

    doc.text(`Tipo: ${t.nombre}`, 15, 106)
    doc.text(`Alcobas: ${t.cantidad_habitaciones}`, 15, 112)
    doc.text(`Baños: ${t.cantidad_banos}`, 15, 118)
    doc.text(`Área Construida: ${t.area_construida} m²`, 15, 124)
    doc.text(`Área Privada: ${t.area_privada} m²`, 15, 130)
    /* Imagen tipo apartamento */
    if (t.imagen) {
      const img = new Image()
      img.src = `/storage/${t.imagen}`
      doc.addImage(img, 'JPEG', 130, 88, 65, 50)
    }
  }

  doc.text(`Precio Vigente: ${formatMoney(inmueble.value.valor_final)}`, 15, 136)

  /* ------------------------------------------------------
   *  DESGLOSE ECONÓMICO
   * ------------------------------------------------------ */
  const valorTotal = inmueble.value.valor_final
  const cuotaInicial = Math.round(valorTotal * (proyecto.value.porcentaje_cuota_inicial_min / 100))
  const cuotaSeparacion = Number(proyecto.value.valor_min_separacion || 0)
  const saldoCuotaInicial = cuotaInicial - cuotaSeparacion
  const valorMensual = Math.round(saldoCuotaInicial / plazo.value)
  const residuo = saldoCuotaInicial - valorMensual * plazo.value
  const saldoRestante = valorTotal - cuotaInicial

  doc.setFontSize(14)
  doc.text('Desglose Económico', 15, 155)
  doc.setFontSize(10)

  // Columna izquierda
  doc.text(`Valor Cuota Inicial: ${formatMoney(cuotaInicial)}`, 15, 165)
  doc.text(`Cuota de Separación: ${formatMoney(cuotaSeparacion)}`, 15, 171)
  doc.text(`Saldo Cuota Inicial: ${formatMoney(saldoCuotaInicial)}`, 15, 177)

  // Columna derecha
  doc.text(`No. Cuotas: ${plazo.value} Meses`, 110, 165)
  doc.text(`Valor Cuota Mensual: ${formatMoney(valorMensual)}`, 110, 171)
  doc.text(`Saldo Restante: ${formatMoney(saldoRestante)}`, 110, 177)

  /* ------------------------------------------------------
   *  TABLA AMORTIZACIÓN
   * ------------------------------------------------------ */
  doc.setFontSize(14)
  doc.text(`Tabla de Amortización`, 82, 190)
  doc.setFontSize(10)

  let tabla = []
  let saldo = saldoCuotaInicial

  for (let i = 1; i <= plazo.value; i++) {
    let valorCuota = valorMensual

    if (i === plazo.value) {
      valorCuota += residuo
    }

    const saldoFinal = Math.max(saldo - valorCuota, 0)
    tabla.push([i, `Mes ${i}`, formatMoney(valorCuota), formatMoney(saldoFinal)])
    saldo = saldoFinal
  }

  autoTable(doc, {
    startY: 195,
    head: [['#', 'Mes', 'Valor', 'Valor Restante']],
    body: tabla,
    headStyles: { fillColor: [30, 58, 95], textColor: [255, 255, 255] },
    styles: { halign: 'center', fontSize: 10 },
  })

  /* ------------------------------------------------------
   *  PIE DE PÁGINA
   * ------------------------------------------------------ */
  const pageCount = doc.internal.getNumberOfPages()
  for (let i = 1; i <= pageCount; i++) {
    doc.setPage(i)
    doc.setFontSize(8)
    doc.text(`Página ${i} de ${pageCount} - Generado ${fechaGen}`, 105, 290, { align: 'center' })
  }

  /* ------------------------------------------------------
   *  DESCARGAR
   * ------------------------------------------------------ */
  doc.save(`Cotizacion-${proyecto.value.nombre}-${inmueble.value.numero}.pdf`)
  cargando.value = false
}
</script>

<template>
  <VentasLayout>
    <div class="p-6 space-y-6">
      <h1 class="text-3xl font-bold">Cotizador</h1>

      <!-- SELECTS -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
          <label>Proyecto</label>
          <select v-model="proyectoId" class="w-full border rounded p-2">
            <option value="">Seleccione...</option>
            <option v-for="p in proyectos" :key="p.id_proyecto" :value="p.id_proyecto">
              {{ p.nombre }}
            </option>
          </select>
        </div>

        <div>
          <label>Cliente</label>
          <select v-model="clienteId" class="w-full border rounded p-2">
            <option value="">Seleccione...</option>
            <option v-for="c in clientes" :key="c.documento" :value="c.documento">
              {{ c.nombre }}
            </option>
          </select>
        </div>

        <div>
          <label>Inmueble Disponible</label>
          <select v-model="inmuebleId" class="w-full border rounded p-2">
            <option value="">Seleccione...</option>
            <option v-for="i in props.inmuebles" :key="i.id" :value="i.id">
              {{ i.tipo === 'apartamento' ? `Apto ${i.numero} ` : `Local ${i.numero} ` }}
            </option>
          </select>
        </div>

        <div>
          <label>Plazo (meses)</label>
          <select v-model="plazo" class="w-full border rounded p-2">
            <option value="">Seleccione...</option>
            <option v-for="p in plazosDisponibles" :key="p" :value="p">
              {{ p }}
            </option>
          </select>
        </div>
      </div>

      <!-- BOTÓN -->
      <button
        @click="generarPDF"
        class="px-6 py-3 bg-brand-800 text-white rounded-lg hover:bg-brand-700 shadow"
      >
        Generar Cotización
      </button>

      <!-- LOADING -->
      <div v-if="cargando" class="text-center text-lg mt-4 animate-pulse">Generando PDF...</div>
    </div>
  </VentasLayout>
</template>
