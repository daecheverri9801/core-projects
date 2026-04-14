<script setup>
import { ref, computed, watch, reactive } from 'vue'
import VentasLayout from '@/Components/VentasLayout.vue'
import jsPDF from 'jspdf'
import autoTable from 'jspdf-autotable'
import {
  ClipboardDocumentCheckIcon,
  BuildingOffice2Icon,
  UserIcon,
  MagnifyingGlassIcon,
  IdentificationIcon,
  HomeModernIcon,
  CalendarDaysIcon,
  BanknotesIcon,
  SparklesIcon,
} from '@heroicons/vue/24/outline'

const props = defineProps({
  proyectos: Array,
  clientes: Array,
  empleado: Object,
  inmuebles: Array,
})

const proyectoId = ref('')
const clienteBusqueda = reactive({
  documento: '',
  error: '',
})

const clienteSeleccionado = ref(null)
const inmuebleId = ref('')
const plazo = ref('')
const cargando = ref(false)

const proyecto = computed(
  () => props.proyectos.find((p) => String(p.id_proyecto) === String(proyectoId.value)) || null
)

const empleado = computed(() => props.empleado || null)
const cliente = computed(() => clienteSeleccionado.value || null)

function normalizarDocumento(value) {
  return String(value || '').replace(/\D/g, '')
}

function limpiarClienteSeleccionado() {
  clienteSeleccionado.value = null
}

function buscarCliente() {
  clienteBusqueda.error = ''

  const documento = normalizarDocumento(clienteBusqueda.documento)

  if (!documento) {
    limpiarClienteSeleccionado()
    clienteBusqueda.error = 'Ingresa el número de documento del cliente.'
    return
  }

  const encontrado = (props.clientes || []).find(
    (c) => normalizarDocumento(c.documento) === documento
  )

  if (!encontrado) {
    limpiarClienteSeleccionado()
    clienteBusqueda.error = 'No se encontró un cliente con ese documento.'
    return
  }

  clienteSeleccionado.value = encontrado
}

function limpiarBusquedaCliente() {
  clienteBusqueda.documento = ''
  clienteBusqueda.error = ''
  limpiarClienteSeleccionado()
}

const inmueblesFiltrados = computed(() => {
  if (!proyectoId.value) return []

  return props.inmuebles.filter((i) => {
    return String(i.id_proyecto) === String(proyectoId.value)
  })
})

const inmueble = computed(() => {
  if (!inmuebleId.value) return null
  return inmueblesFiltrados.value.find((i) => String(i.id) === String(inmuebleId.value)) || null
})

watch(proyectoId, () => {
  inmuebleId.value = ''
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

function calcularMesesEntreFechas(inicioStr, fechaRefStr) {
  if (!inicioStr || !fechaRefStr) return 0
  const inicio = new Date(inicioStr + 'T00:00:00')
  const ref = new Date(fechaRefStr + 'T00:00:00')
  let meses = (ref.getFullYear() - inicio.getFullYear()) * 12 + (ref.getMonth() - inicio.getMonth())
  if (ref.getDate() < inicio.getDate()) meses--
  return Math.max(meses, 0)
}

const plazosDisponibles = computed(() => {
  if (!proyecto.value) return []
  const plazoTotal = Number(proyecto.value.plazo_cuota_inicial_meses || 0)
  if (!plazoTotal || !proyecto.value.fecha_inicio) return []

  const hoy = new Date()
  const hoyStr = hoy.toISOString().slice(0, 10)
  const mesesTranscurridos = calcularMesesEntreFechas(proyecto.value.fecha_inicio, hoyStr)
  const plazosRestantes = Math.max(plazoTotal - mesesTranscurridos, 0)

  return plazosRestantes > 0 ? Array.from({ length: plazosRestantes }, (_, i) => i + 1) : []
})

function formatMoney(v) {
  return Number(v || 0).toLocaleString('es-CO', {
    style: 'currency',
    currency: 'COP',
    maximumFractionDigits: 0,
  })
}

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

  const doc = new jsPDF()
  const MARGIN_BOTTOM = 15

  const ensureSpace = (doc, currentY, neededHeight = 20) => {
    const pageHeight = doc.internal.pageSize.getHeight()
    if (currentY + neededHeight > pageHeight - MARGIN_BOTTOM) {
      doc.addPage()
      return 20
    }
    return currentY
  }

  const writeWrappedTextWithPageBreak = (
    doc,
    text,
    x,
    y,
    maxWidth,
    lineHeight = 4.5,
    extraGap = 3
  ) => {
    const lines = doc.splitTextToSize(text, maxWidth)
    const pageHeight = doc.internal.pageSize.getHeight()

    for (const line of lines) {
      if (y + lineHeight > pageHeight - MARGIN_BOTTOM) {
        doc.addPage()
        y = 20
      }
      doc.text(line, x, y)
      y += lineHeight
    }

    y += extraGap
    return y
  }

  const logo = new Image()
  logo.src = '/images/logo-ayc.png'
  doc.addImage(logo, 'PNG', 15, 10, 25, 25)

  doc.setFont('Helvetica', 'bold')
  doc.setFontSize(18)
  doc.text(`Cotización - ${proyecto.value.nombre}`, 105, 20, { align: 'center' })

  const now = new Date()
  const fechaGen = now.toISOString().slice(0, 16).replace('T', ' ')
  doc.setFontSize(10)
  doc.text(`Fecha Generación: ${fechaGen}`, 105, 28, { align: 'center' })

  doc.setFontSize(14)
  doc.text('Datos del Cliente', 15, 45)
  doc.setFontSize(10)

  doc.text(`Nombre: ${cliente.value.nombre}`, 15, 55)
  doc.text(`Documento: ${cliente.value.documento}`, 15, 61)
  doc.text(`Dirección: ${cliente.value.direccion ?? '-'}`, 15, 67)

  doc.text(`Teléfono: ${cliente.value.telefono ?? '-'}`, 110, 55)
  doc.text(`Correo: ${cliente.value.correo ?? '-'}`, 110, 61)

  doc.setFontSize(14)
  doc.text('Información del Inmueble', 15, 80)
  doc.setFontSize(10)

  doc.text(`Número: ${inmueble.value.numero}`, 15, 88)
  doc.text(`Piso: ${inmueble.value.pisoTorre?.nivel ?? '-'}`, 15, 94)
  doc.text(`Torre: ${inmueble.value.torre?.nombre_torre ?? '-'}`, 15, 100)

  if (inmueble.value.tipo === 'apartamento') {
    const t = inmueble.value.tipoApartamento

    doc.text(`Tipo: ${t.nombre}`, 15, 106)
    doc.text(`Habitaciones: ${t.cantidad_habitaciones}`, 15, 112)
    doc.text(`Baños: ${t.cantidad_banos}`, 15, 118)
    doc.text(`Área Construida: ${t.area_construida} m²`, 15, 124)
    doc.text(`Área Privada: ${t.area_privada} m²`, 15, 130)

    if (t.imagen) {
      const img = new Image()
      img.src = `/storage/${t.imagen}`
      doc.addImage(img, 'JPEG', 95, 70, 90, 90)
    }
  }

  doc.text(`Precio Vigente: ${formatMoney(inmueble.value.valor_final)}`, 15, 136)

  const valorTotal = inmueble.value.valor_final
  const cuotaInicial = Math.round(valorTotal * (proyecto.value.porcentaje_cuota_inicial_min / 100))
  const cuotaSeparacion = Number(proyecto.value.valor_min_separacion || 0)
  const saldoCuotaInicial = cuotaInicial - cuotaSeparacion
  const valorMensual = Math.round(saldoCuotaInicial / plazo.value)
  const saldoRestante = valorTotal - cuotaInicial

  doc.setFontSize(14)
  doc.text('Desglose Económico', 15, 155)
  doc.setFontSize(10)

  doc.text(`Valor Cuota Inicial: ${formatMoney(cuotaInicial)}`, 15, 165)
  doc.text(`Cuota de Separación: ${formatMoney(cuotaSeparacion)}`, 15, 171)
  doc.text(`Saldo Cuota Inicial: ${formatMoney(saldoCuotaInicial)}`, 15, 177)

  doc.text(`No. Cuotas: ${plazo.value} Meses`, 110, 165)
  doc.text(`Valor Cuota Mensual: ${formatMoney(valorMensual)}`, 110, 171)
  doc.text(`Saldo Restante: ${formatMoney(saldoRestante)}`, 110, 177)

  doc.setFontSize(14)
  doc.text(`Tabla de Amortización`, 82, 190)
  doc.setFontSize(10)

  const tabla = []
  let saldo = saldoCuotaInicial
  const fechaBase = new Date()
  const yearBase = fechaBase.getFullYear()
  const monthBase = fechaBase.getMonth()

  for (let i = 1; i <= plazo.value; i++) {
    const fechaCuota = new Date(yearBase, monthBase + (i - 1), 1)
    const yyyy = fechaCuota.getFullYear()
    const mm = String(fechaCuota.getMonth() + 1).padStart(2, '0')
    const labelMes = `${yyyy}-${mm}`

    saldo -= valorMensual
    tabla.push([i, labelMes, formatMoney(valorMensual), formatMoney(Math.max(saldo, 0))])
  }

  autoTable(doc, {
    startY: 195,
    head: [['#', 'Mes', 'Valor', 'Valor Restante']],
    body: tabla,
    headStyles: { fillColor: [30, 58, 95], textColor: [255, 255, 255] },
    styles: { halign: 'center', fontSize: 10 },
  })

  let yAsesor = doc.lastAutoTable ? doc.lastAutoTable.finalY + 15 : 260
  yAsesor = ensureSpace(doc, yAsesor, 35)

  doc.setFontSize(14)
  doc.setFont('Helvetica', 'bold')
  doc.text('Datos del Asesor', 15, yAsesor)

  yAsesor += 8
  doc.setFontSize(10)
  doc.setFont('Helvetica', 'normal')

  doc.text(`Nombre: ${empleado.value.nombre} ${empleado.value.apellido}`, 15, yAsesor)
  yAsesor += 6
  doc.text(`Teléfono: ${empleado.value.telefono ?? '-'}`, 15, yAsesor)
  yAsesor += 6
  doc.text(`Correo: ${empleado.value.email ?? '-'}`, 15, yAsesor)
  yAsesor += 12

  yAsesor = ensureSpace(doc, yAsesor, 20)

  doc.setFontSize(12)
  doc.setFont('Helvetica', 'bold')
  doc.setTextColor(30, 30, 30)
  doc.text('Aclaraciones Importantes', 15, yAsesor)

  doc.setDrawColor(150, 150, 150)
  doc.line(15, yAsesor + 2, 195, yAsesor + 2)

  yAsesor += 10

  doc.setFontSize(8)
  doc.setFont('Helvetica', 'normal')
  doc.setTextColor(70, 70, 70)

  const aclaraciones = [
    '1. CLAUSULA PENAL: DIEZ POR CIENTO (10%) SOBRE LO APORTADO AL MOMENTO DEL RETIRO VOLUNTARIO O POR INCUMPLIMIENTO DE LOS PLAZOS DE PAGO.',
    '2. Los valores presentados en esta cotización son referenciales al momento de su generación. Antes de cualquier trámite, confirme el valor actualizado con la asesora comercial.',
    '3. LOS RENDER USADOS EN LA PUBLICIDAD SON UNA APROXIMACIÓN A LA REALIDAD. Las áreas, animaciones y diseños pueden variar en el desarrollo arquitectónico y constructivo. Solo es válido lo acordado en la promesa de compraventa.',
    '4. Todo material publicitario (brochures, web, redes, prensa), renders e imágenes tiene carácter ilustrativo e informativo. No modifica lo pactado contractualmente salvo que se incorpore expresamente.',
    '5. Salvo indicación expresa, no se incluyen muebles, electrodomésticos, decoración ni equipamiento mostrado en piezas publicitarias. La entrega se realiza conforme a la ficha técnica y el inventario de entrega.',
    '6. Las áreas, distribuciones y especificaciones pueden registrar ajustes razonables debido a tolerancias constructivas, instalaciones u obligaciones técnicas. Dichos ajustes no afectarán la funcionalidad esencial del inmueble.',
  ]

  for (const texto of aclaraciones) {
    yAsesor = writeWrappedTextWithPageBreak(doc, texto, 15, yAsesor, 180, 4.5, 3)
  }

  const pageCount = doc.internal.getNumberOfPages()
  for (let i = 1; i <= pageCount; i++) {
    doc.setPage(i)
    doc.setFontSize(8)
    doc.setTextColor(70, 70, 70)
    doc.text(`Página ${i} de ${pageCount} - Generado ${fechaGen}`, 105, 290, { align: 'center' })
  }

  doc.save(`Cotizacion-${proyecto.value.nombre}-${inmueble.value.numero}.pdf`)
  cargando.value = false
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
                  <ClipboardDocumentCheckIcon class="h-6 w-6" />
                </div>
                <div>
                  <h1 class="text-2xl font-extrabold tracking-tight sm:text-3xl">Cotizador</h1>
                  <p class="mt-1 text-sm text-[#474100]/80">
                    Genera cotizaciones claras y rápidas para tus clientes.
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
                  {{ proyecto?.nombre || 'Pendiente' }}
                </p>
              </div>

              <div class="rounded-2xl border border-[#474100]/10 bg-white/70 px-4 py-3 shadow-sm">
                <p class="text-[11px] font-semibold uppercase tracking-wide text-[#474100]/70">
                  Cliente
                </p>
                <p class="mt-1 truncate text-sm font-bold text-[#474100]">
                  {{ cliente?.nombre || 'Pendiente' }}
                </p>
              </div>

              <div class="rounded-2xl border border-[#474100]/10 bg-white/70 px-4 py-3 shadow-sm">
                <p class="text-[11px] font-semibold uppercase tracking-wide text-[#474100]/70">
                  Inmueble
                </p>
                <p class="mt-1 truncate text-sm font-bold text-[#474100]">
                  {{
                    inmueble
                      ? `${inmueble.tipo === 'apartamento' ? 'Apto' : 'Local'} ${inmueble.numero}`
                      : 'Pendiente'
                  }}
                </p>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- Layout -->
      <div class="grid grid-cols-1 gap-6 xl:grid-cols-12">
        <!-- Principal -->
        <section class="xl:col-span-8 space-y-6">
          <!-- Datos principales -->
          <div class="rounded-3xl border border-gray-200 bg-white shadow-sm">
            <div
              class="flex flex-col gap-3 border-b border-gray-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between"
            >
              <div class="flex items-center gap-2">
                <SparklesIcon class="h-5 w-5 text-[#1e3a5f]" />
                <h2 class="text-base font-extrabold text-gray-900">Datos de la cotización</h2>
              </div>

              <div
                class="inline-flex w-fit items-center rounded-full border border-[#FFEA00]/40 bg-[#FFFDE6] px-3 py-1 text-xs font-bold text-[#756C00]"
              >
                Completa proyecto, cliente, inmueble y plazo
              </div>
            </div>

            <div class="p-5 grid grid-cols-1 gap-5 md:grid-cols-2">
              <!-- Proyecto -->
              <div>
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

              <!-- Plazo -->
              <div>
                <label :class="labelClass()">Plazo (meses)</label>
                <div class="relative">
                  <select
                    v-model="plazo"
                    :disabled="!proyectoId || plazosDisponibles.length === 0"
                    :class="fieldClass(!proyectoId || plazosDisponibles.length === 0)"
                    class="pl-11"
                  >
                    <option value="">Seleccione...</option>
                    <option v-for="p in plazosDisponibles" :key="p" :value="p">
                      {{ p }} mes{{ p === 1 ? '' : 'es' }}
                    </option>
                  </select>
                </div>
                <p v-if="proyectoId && plazosDisponibles.length === 0" class="mt-2 text-xs text-red-500">
                  No hay plazos disponibles para este proyecto a partir de la fecha actual.
                </p>
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
                        :class="fieldClass(false)"
                        class="pl-11"
                        placeholder="Digita el número de documento"
                        @keyup.enter="buscarCliente"
                      />
                    </div>

                    <div class="flex gap-2">
                      <button
                        type="button"
                        @click="buscarCliente"
                        class="inline-flex items-center justify-center gap-2 rounded-2xl bg-[#1e3a5f] px-5 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-[#2c5282]"
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
                          Información validada para la cotización
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

                      <div class="rounded-xl bg-white/80 px-4 py-3">
                        <p class="text-[11px] font-semibold uppercase tracking-wide text-sky-700">
                          Dirección
                        </p>
                        <p class="mt-1 font-semibold text-gray-900">
                          {{ clienteSeleccionado.direccion || '—' }}
                        </p>
                      </div>

                      <div class="rounded-xl bg-white/80 px-4 py-3">
                        <p class="text-[11px] font-semibold uppercase tracking-wide text-sky-700">
                          Teléfono
                        </p>
                        <p class="mt-1 font-semibold text-gray-900">
                          {{ clienteSeleccionado.telefono || '—' }}
                        </p>
                      </div>

                      <div class="rounded-xl bg-white/80 px-4 py-3 md:col-span-2">
                        <p class="text-[11px] font-semibold uppercase tracking-wide text-sky-700">
                          Correo
                        </p>
                        <p class="mt-1 font-semibold text-gray-900">
                          {{ clienteSeleccionado.correo || '—' }}
                        </p>
                      </div>
                    </div>
                  </div>

                  <p v-else class="mt-3 text-xs text-gray-500">
                    Busca el cliente por número de documento para continuar con la cotización.
                  </p>
                </div>
              </div>

              <!-- Inmueble -->
              <div class="md:col-span-2">
                <label :class="labelClass()">Inmueble disponible</label>
                <div class="relative">
                  <select v-model="inmuebleId" :class="fieldClass(false)" class="pl-11">
                    <option value="">Seleccione...</option>
                    <option v-for="i in inmueblesFiltrados" :key="i.id" :value="i.id">
                      {{ i.tipo === 'apartamento' ? `Apto ${i.numero}` : `Local ${i.numero}` }} -
                      {{ i.torre?.nombre_torre || 'Sin torre' }} -
                      {{ formatMoney(i.valor_final) }}
                    </option>
                  </select>
                </div>
              </div>
            </div>
          </div>
        </section>

        <!-- Resumen -->
        <aside class="xl:col-span-4 space-y-6">
          <div class="sticky top-6 space-y-6">
            <div class="rounded-3xl border border-gray-200 bg-white shadow-sm overflow-hidden">
              <div class="bg-gradient-to-r from-[#1e3a5f] to-[#2c5282] px-5 py-4">
                <div class="flex items-center gap-2 text-white">
                  <BanknotesIcon class="h-5 w-5" />
                  <h3 class="text-base font-extrabold">Resumen de cotización</h3>
                </div>
              </div>

              <div class="p-5 space-y-4">
                <div class="rounded-2xl bg-gray-50 px-4 py-3">
                  <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Cliente</p>
                  <p class="mt-1 text-sm font-bold text-gray-900">
                    {{ clienteSeleccionado?.nombre || 'Pendiente' }}
                  </p>
                </div>

                <div class="rounded-2xl bg-gray-50 px-4 py-3">
                  <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Proyecto</p>
                  <p class="mt-1 text-sm font-bold text-gray-900">
                    {{ proyecto?.nombre || 'Pendiente' }}
                  </p>
                </div>

                <div class="rounded-2xl bg-gray-50 px-4 py-3">
                  <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Inmueble</p>
                  <p class="mt-1 text-sm font-bold text-gray-900">
                    {{
                      inmueble
                        ? `${inmueble.tipo === 'apartamento' ? 'Apto' : 'Local'} ${inmueble.numero}`
                        : 'Pendiente'
                    }}
                  </p>
                </div>

                <div class="rounded-2xl bg-gray-50 px-4 py-3">
                  <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Plazo</p>
                  <p class="mt-1 text-sm font-bold text-gray-900">
                    {{ plazo ? `${plazo} mes${plazo === 1 ? '' : 'es'}` : 'Pendiente' }}
                  </p>
                </div>

                <div class="rounded-2xl border border-[#FFEA00]/50 bg-[#FFFDE6] px-4 py-4">
                  <p class="text-xs font-semibold uppercase tracking-wide text-[#756C00]">
                    Valor estimado del inmueble
                  </p>
                  <p class="mt-1 text-xl font-extrabold text-[#474100]">
                    {{ inmueble ? formatMoney(inmueble.valor_final) : '—' }}
                  </p>
                </div>

                <button
                  @click="generarPDF"
                  :disabled="cargando"
                  class="inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-[#FFEA00] to-[#D1C000] px-5 py-3.5 text-sm font-extrabold text-[#474100] shadow-sm transition hover:shadow-md disabled:cursor-not-allowed disabled:opacity-60"
                >
                  <ClipboardDocumentCheckIcon class="h-10 w-5" />
                  {{ cargando ? 'Generando PDF...' : 'Generar cotización' }}
                </button>

                <div
                  v-if="cargando"
                  class="rounded-2xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm font-medium text-blue-700"
                >
                  Procesando la cotización. Espera un momento.
                </div>

                <div class="rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3 text-xs text-gray-600">
                  Verifica que el cliente, el proyecto, el inmueble y el plazo estén correctamente
                  seleccionados antes de generar el documento.
                </div>
              </div>
            </div>
          </div>
        </aside>
      </div>
    </div>
  </VentasLayout>
</template>