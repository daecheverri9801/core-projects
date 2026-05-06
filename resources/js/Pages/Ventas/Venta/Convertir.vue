<!-- resources/js/Pages/Ventas/Venta/Convertir.vue -->
<script setup>
import { Head, Link, usePage, router } from '@inertiajs/vue3'
import { computed, ref, watch, onMounted, reactive, nextTick } from 'vue'
import {
  BuildingOffice2Icon,
  UserPlusIcon,
  ArrowLeftIcon,
  ClipboardDocumentCheckIcon,
  BanknotesIcon,
  HomeModernIcon,
  XMarkIcon,
  MagnifyingGlassIcon,
  IdentificationIcon,
} from '@heroicons/vue/24/outline'
import { CheckCircleIcon as CheckSolid } from '@heroicons/vue/24/solid'

import VentasLayout from '@/Components/VentasLayout.vue'
import ClienteForm from '@/Components/ClienteForm.vue'

const page = usePage()
const empleado = computed(() => page.props.auth?.empleado || null)

const props = defineProps({
  venta: Object,
  clientes: { type: Array, default: () => [] },
  proyectos: { type: Array, default: () => [] },
  formasPago: { type: Array, default: () => [] },
  estadosInmueble: { type: Array, default: () => [] },
  plazos_disponibles: { type: Array, default: () => [] },
  tiposCliente: { type: Array, default: () => [] },
  tiposDocumento: { type: Array, default: () => [] },
  parqueaderos: { type: Array, default: () => [] },
})

const PLAN_PROYECTO_DEFAULT_ID = '__condiciones_proyecto__'

function todayISO() {
  return new Date().toISOString().slice(0, 10)
}

function normalizarDocumento(value) {
  return String(value || '').replace(/\D/g, '')
}

function formatearMoneda(valor) {
  if (valor === null || valor === undefined || valor === '') return ''

  return Number(valor).toLocaleString('es-CO', {
    style: 'currency',
    currency: 'COP',
    maximumFractionDigits: 0,
  })
}

function inputClass(error = false, disabled = false) {
  return [
    'w-full rounded-xl border bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm transition',
    'focus:outline-none focus:ring-2 focus:ring-[#1e3a5f] focus:border-transparent',
    disabled ? 'bg-gray-100 text-gray-700 cursor-not-allowed' : '',
    error ? 'border-red-400 focus:ring-red-300' : 'border-gray-300',
  ].join(' ')
}

function labelClass() {
  return 'block text-xs font-semibold text-gray-600 mb-1'
}

function hintClass() {
  return 'text-xs text-gray-500 mt-1'
}

function errorClass() {
  return 'text-xs text-red-600 mt-1'
}

function calcularMesesEntreFechas(inicioStr, fechaRefStr) {
  if (!inicioStr || !fechaRefStr) return 0

  const inicio = new Date(`${inicioStr}T00:00:00`)
  const ref = new Date(`${fechaRefStr}T00:00:00`)

  let meses = (ref.getFullYear() - inicio.getFullYear()) * 12 + (ref.getMonth() - inicio.getMonth())

  if (ref.getDate() < inicio.getDate()) {
    meses--
  }

  return Math.max(meses, 0)
}

function esPlanCuotaInicialMensual(plan) {
  return ['cuota_inicial_mensual', 'condiciones_proyecto'].includes(plan?.tipo_plan)
}

function esPlanEspecialManual(plan) {
  return plan?.tipo_plan === 'especial_manual'
}

function requierePlazoYFrecuencia(plan) {
  return ['cuota_inicial_mensual', 'condiciones_proyecto'].includes(plan?.tipo_plan)
}

function requiereSoloPlazo(plan) {
  return esPlanEspecialManual(plan)
}

function addMonthsToDate(dateStr, monthsToAdd) {
  const base = dateStr ? new Date(`${dateStr}T00:00:00`) : new Date()
  const year = base.getFullYear()
  const month = base.getMonth()

  return new Date(year, month + monthsToAdd, 1)
}

function formatMesAnio(date) {
  return date.toLocaleDateString('es-CO', {
    year: 'numeric',
    month: 'long',
  })
}

function fechaCuotaISO(date) {
  return date.toISOString().slice(0, 10)
}

function tipoPlanLabel(tipo) {
  const labels = {
    cuota_inicial_mensual: 'Cuota inicial mensual',
    cuota_inicial_contado: 'Cuota inicial de contado',
    pago_total_diferido: 'Pago total diferido',
    especial_manual: 'Plan especial manual',
    condiciones_proyecto: 'Condiciones económicas del proyecto',
  }

  return labels[tipo] || tipo || '—'
}

function calcularDescuento(valorTotal, cuotaInicialBruta, plan) {
  if (!plan || plan.tipo_descuento === 'ninguno') return 0

  const valorDescuento = Number(plan.valor_descuento || 0)

  if (plan.tipo_descuento === 'valor_fijo') {
    return Math.round(valorDescuento)
  }

  if (plan.tipo_descuento === 'porcentaje') {
    if (plan.base_descuento === 'cuota_inicial') {
      return Math.round(cuotaInicialBruta * (valorDescuento / 100))
    }

    if (plan.base_descuento === 'precio_total') {
      return Math.round(valorTotal * (valorDescuento / 100))
    }
  }

  return 0
}

const proyectoSeparacion = computed(() => {
  const id = Number(props.venta?.id_proyecto || 0)

  return props.proyectos.find((p) => Number(p.id_proyecto) === id) || props.venta?.proyecto || null
})

const inmuebleSeparacion = computed(() => props.venta?.apartamento || props.venta?.local || null)
const inmuebleTipo = computed(() => (props.venta?.apartamento ? 'apartamento' : 'local'))
const inmuebleId = computed(() =>
  props.venta?.apartamento ? props.venta.apartamento.id_apartamento : props.venta?.local?.id_local
)

const proyectoTienePlanesPago = computed(() => {
  return (
    Array.isArray(proyectoSeparacion.value?.planes_pago) &&
    proyectoSeparacion.value.planes_pago.length > 0
  )
})

const planCondicionesProyecto = computed(() => {
  if (!proyectoSeparacion.value) return null

  const porcentajeCuotaInicial = Number(proyectoSeparacion.value.porcentaje_cuota_inicial_min || 0)

  return {
    id_plan_pago_proyecto: PLAN_PROYECTO_DEFAULT_ID,
    codigo: 'COND-PROYECTO',
    nombre: 'Condiciones económicas del proyecto',
    tipo_plan: 'condiciones_proyecto',
    valor_separacion: Number(proyectoSeparacion.value.valor_min_separacion || 0),
    porcentaje_cuota_inicial: porcentajeCuotaInicial,
    plazo_cuota_inicial_meses: Number(proyectoSeparacion.value.plazo_cuota_inicial_meses || 0),
    frecuencia_cuota_inicial_meses: 1,
    plazo_pago_total_dias: null,
    porcentaje_escritura: Math.max(100 - porcentajeCuotaInicial, 0),
    tipo_descuento: 'ninguno',
    valor_descuento: null,
    base_descuento: 'ninguna',
    beneficio_comercial: null,
    permite_plazo_manual: false,
    permite_cuotas_manuales: false,
    activo: true,
    es_plan_default_proyecto: true,
  }
})

const planesPagoProyecto = computed(() => {
  if (!proyectoSeleccionado.value) return []

  if (proyectoTienePlanesPago.value) {
    return (proyectoSeleccionado.value.planes_pago || []).filter(planDisponiblePorPerfil)
  }

  return planCondicionesProyecto.value ? [planCondicionesProyecto.value] : []
})

function resolverPlanInicial() {
  if (props.venta?.id_plan_pago_proyecto) {
    return String(props.venta.id_plan_pago_proyecto)
  }

  if (!proyectoTienePlanesPago.value && planCondicionesProyecto.value) {
    return PLAN_PROYECTO_DEFAULT_ID
  }

  return ''
}

const estadoVendidoId = computed(() => {
  return (
    props.estadosInmueble.find((e) => e.nombre?.toLowerCase() === 'vendido')?.id_estado_inmueble ||
    props.venta?.id_estado_inmueble ||
    null
  )
})

const valorTotalInmueble = computed(() => {
  const inm = inmuebleSeparacion.value
  if (!inm) return 0

  const valor = Number(inm.valor_final ?? inm.valor_total ?? props.venta?.valor_base ?? 0)

  return Number.isFinite(valor) ? valor : 0
})

const plazosDisponibles = ref([])
const parqueaderosDisponibles = ref([])

const form = reactive({
  processing: false,
  errors: {},
  tipo_operacion: 'venta',
  id_empleado: empleado.value?.id_empleado || props.venta?.id_empleado || null,
  documento_cliente: props.venta?.documento_cliente || '',
  fecha_venta: todayISO(),
  id_proyecto: String(props.venta?.id_proyecto || ''),
  inmueble_tipo: inmuebleTipo.value,
  inmueble_id: inmuebleId.value ? String(inmuebleId.value) : '',
  id_forma_pago: props.venta?.id_forma_pago ? String(props.venta.id_forma_pago) : '',
  id_estado_inmueble: estadoVendidoId.value,
  valor_base: valorTotalInmueble.value,
  valor_total: Number(props.venta?.valor_total || 0),
  cuota_inicial: 0,
  cuota_inicial_raw: 0,
  valor_restante: 0,
  valor_separacion: Number(props.venta?.valor_separacion || 0),
  plazo_cuota_inicial_meses: '',
  frecuencia_cuota_inicial_meses: '',
  descripcion: props.venta?.descripcion || '',
  id_parqueadero: props.venta?.id_parqueadero ? String(props.venta.id_parqueadero) : '',
  id_plan_pago_proyecto: resolverPlanInicial(),
  cuotas_manual_ci: Array.isArray(props.venta?.cuotas_manual_ci)
    ? props.venta.cuotas_manual_ci
    : [],
})

const proyectoSeleccionado = computed(() => proyectoSeparacion.value)

const planPagoSeleccionado = computed(() => {
  if (!form.id_plan_pago_proyecto) return null

  return (
    planesPagoProyecto.value.find(
      (p) => String(p.id_plan_pago_proyecto) === String(form.id_plan_pago_proyecto)
    ) || null
  )
})

const precioParqueaderoSeleccionado = computed(() => {
  if (!form.id_parqueadero) return 0

  const parqueadero = parqueaderosDisponibles.value.find(
    (p) => Number(p.id_parqueadero) === Number(form.id_parqueadero)
  )

  return parqueadero ? Number(parqueadero.precio || 0) : 0
})

const resumenPlanVenta = computed(() => {
  if (!planPagoSeleccionado.value) return null

  const plan = planPagoSeleccionado.value

  const valorBruto = Number(form.valor_base || 0) + Number(precioParqueaderoSeleccionado.value || 0)
  const porcentajeCuotaInicial = Number(plan.porcentaje_cuota_inicial || 0)
  const porcentajeEscritura = Number(plan.porcentaje_escritura || 0)
  const cuotaSeparacion = Number(plan.valor_separacion || 0)

  const cuotaInicialBruta = Math.round(valorBruto * (porcentajeCuotaInicial / 100))
  const valorEscrituraBruto = Math.round(valorBruto * (porcentajeEscritura / 100))

  let descuento = 0
  let totalCotizado = valorBruto
  let cuotaInicial = cuotaInicialBruta
  let saldoCuotaInicial = Math.max(cuotaInicial - cuotaSeparacion, 0)
  let valorRestante = Math.max(valorBruto - cuotaInicialBruta, 0)
  let saldoPagoDiferido = 0

  if (plan.tipo_descuento !== 'ninguno' && plan.base_descuento === 'precio_total') {
    descuento = calcularDescuento(valorBruto, cuotaInicialBruta, plan)
    totalCotizado = Math.max(valorBruto - descuento, 0)

    if (esPlanCuotaInicialMensual(plan)) {
      cuotaInicial = Math.round(totalCotizado * (porcentajeCuotaInicial / 100))
      saldoCuotaInicial = Math.max(cuotaInicial - cuotaSeparacion, 0)
      valorRestante = Math.max(totalCotizado - cuotaInicial, 0)
    }

    if (plan.tipo_plan === 'pago_total_diferido') {
      cuotaInicial = cuotaSeparacion
      saldoCuotaInicial = 0
      saldoPagoDiferido = Math.max(totalCotizado - cuotaSeparacion, 0)
      valorRestante = saldoPagoDiferido
    }
  }

  if (plan.tipo_descuento !== 'ninguno' && plan.base_descuento === 'cuota_inicial') {
    descuento = calcularDescuento(valorBruto, cuotaInicialBruta, plan)

    cuotaInicial = Math.max(cuotaInicialBruta - descuento, 0)
    saldoCuotaInicial = Math.max(cuotaInicial - cuotaSeparacion, 0)
    valorRestante =
      valorEscrituraBruto > 0 ? valorEscrituraBruto : Math.max(valorBruto - cuotaInicialBruta, 0)
    totalCotizado = cuotaInicial + valorRestante
  }

  if (plan.tipo_descuento === 'ninguno' && plan.tipo_plan === 'pago_total_diferido') {
    totalCotizado = valorBruto
    cuotaInicial = cuotaSeparacion
    saldoCuotaInicial = 0
    saldoPagoDiferido = Math.max(totalCotizado - cuotaSeparacion, 0)
    valorRestante = saldoPagoDiferido
  }

  return {
    valor_total_sin_descuento: Math.round(valorBruto),
    valor_descuento: Math.round(descuento),
    valor_total: Math.round(totalCotizado),
    cuota_inicial: Math.round(cuotaInicial),
    cuota_separacion: Math.round(cuotaSeparacion),
    saldo_cuota_inicial: Math.round(saldoCuotaInicial),
    valor_restante: Math.round(valorRestante),
    saldo_pago_diferido: Math.round(saldoPagoDiferido),
  }
})

const totalCuotasManualCI = computed(() => {
  return (form.cuotas_manual_ci || []).reduce((total, cuota) => {
    return total + Number(cuota.valor_cuota || 0)
  }, 0)
})

const diferenciaCuotasManualCI = computed(() => {
  const saldoCI = Number(resumenPlanVenta.value?.saldo_cuota_inicial || 0)
  return totalCuotasManualCI.value - saldoCI
})

const estadoNombre = computed(() => {
  const estado = props.estadosInmueble.find(
    (e) => Number(e.id_estado_inmueble) === Number(form.id_estado_inmueble)
  )

  return estado?.nombre || '—'
})

const resumenInmueble = computed(() => {
  const inmueble = inmuebleSeparacion.value
  if (!inmueble) return null

  return {
    label: props.venta?.apartamento ? `Apto ${inmueble.numero}` : `Local ${inmueble.numero}`,
    valor: form.valor_base,
  }
})

const resumenParqueadero = computed(() => {
  if (!form.id_parqueadero) return null

  return (
    parqueaderosDisponibles.value.find(
      (p) => Number(p.id_parqueadero) === Number(form.id_parqueadero)
    ) || null
  )
})

const erroresForm = reactive({
  cuota_inicial: '',
})

const frecuenciasDisponibles = [
  { valor: 1, etiqueta: 'Mensual (cada 1 mes)' },
  { valor: 2, etiqueta: 'Bimestral' },
  { valor: 3, etiqueta: 'Trimestral' },
  { valor: 4, etiqueta: 'Cada 4 meses' },
  { valor: 6, etiqueta: 'Semestral' },
  { valor: 12, etiqueta: 'Anual' },
]

const opcionesFrecuencia = computed(() => {
  if (esPlanEspecialManual(planPagoSeleccionado.value)) return []

  const plazo = Number(form.plazo_cuota_inicial_meses)
  if (!plazo) return []

  return frecuenciasDisponibles.filter((frecuencia) => plazo % frecuencia.valor === 0)
})

const camposCompletos = computed(() =>
  Boolean(
    form.tipo_operacion &&
      form.documento_cliente &&
      form.fecha_venta &&
      form.id_proyecto &&
      form.inmueble_id &&
      form.id_forma_pago &&
      form.id_estado_inmueble &&
      form.id_plan_pago_proyecto &&
      (esPlanEspecialManual(planPagoSeleccionado.value)
        ? form.plazo_cuota_inicial_meses &&
          form.cuotas_manual_ci.length === Number(form.plazo_cuota_inicial_meses || 0) &&
          Math.round(totalCuotasManualCI.value) ===
            Math.round(Number(resumenPlanVenta.value?.saldo_cuota_inicial || 0))
        : requierePlazoYFrecuencia(planPagoSeleccionado.value)
          ? form.plazo_cuota_inicial_meses && form.frecuencia_cuota_inicial_meses
          : true)
  )
)

const clienteBusqueda = reactive({
  documento: props.venta?.documento_cliente ? String(props.venta.documento_cliente) : '',
  error: '',
})

const clienteSeleccionado = ref(
  props.clientes.find(
    (cliente) =>
      normalizarDocumento(cliente.documento) === normalizarDocumento(props.venta?.documento_cliente)
  ) || null
)

function cargarParqueaderosDelProyecto() {
  const proyectoId = Number(form.id_proyecto || 0)

  parqueaderosDisponibles.value = props.parqueaderos
    .filter((parqueadero) => Number(parqueadero.id_proyecto) === proyectoId)
    .map((parqueadero) => ({
      ...parqueadero,
      precio: Number(parqueadero.precio || 0),
    }))
}

function recalcularEconomiaVenta() {
  const totalBruto = Number(form.valor_base || 0) + Number(precioParqueaderoSeleccionado.value || 0)

  if (!planPagoSeleccionado.value) {
    form.valor_total = totalBruto
    form.valor_separacion = 0
    form.cuota_inicial_raw = 0
    form.cuota_inicial = 0
    form.valor_restante = 0
    return
  }

  const resumen = resumenPlanVenta.value

  if (!resumen) return

  form.valor_total = resumen.valor_total
  form.valor_separacion = resumen.cuota_separacion
  form.cuota_inicial_raw = resumen.cuota_inicial
  form.cuota_inicial = resumen.cuota_inicial
  form.valor_restante = resumen.valor_restante
}

function generarCuotasManualCI() {
  const plan = planPagoSeleccionado.value

  if (!esPlanEspecialManual(plan)) {
    form.cuotas_manual_ci = []
    return
  }

  const plazo = Number(form.plazo_cuota_inicial_meses || 0)
  const saldoCI = Number(resumenPlanVenta.value?.saldo_cuota_inicial || 0)

  if (!plazo || plazo < 1 || !form.fecha_venta) {
    form.cuotas_manual_ci = []
    return
  }

  const cuotaBase = Math.floor(saldoCI / plazo)
  const residuo = saldoCI - cuotaBase * plazo

  form.cuotas_manual_ci = Array.from({ length: plazo }, (_, index) => {
    const numeroCuota = index + 1
    const fecha = addMonthsToDate(form.fecha_venta, numeroCuota)
    const valorPorDefecto = numeroCuota === plazo ? cuotaBase + residuo : cuotaBase

    return {
      numero_cuota: numeroCuota,
      mes_label: formatMesAnio(fecha),
      fecha_vencimiento: fechaCuotaISO(fecha),
      valor_cuota: valorPorDefecto,
    }
  })
}

function actualizarPlazosDisponibles() {
  const proyecto = proyectoSeleccionado.value
  const plan = planPagoSeleccionado.value

  if (!proyecto || !plan || !form.fecha_venta) {
    plazosDisponibles.value = []
    form.plazo_cuota_inicial_meses = ''
    form.frecuencia_cuota_inicial_meses = ''
    return
  }

  if (esPlanEspecialManual(plan)) {
    const maxPlazoProyecto = Number(proyecto.plazo_cuota_inicial_meses || 0)

    form.frecuencia_cuota_inicial_meses = ''

    if (!maxPlazoProyecto || maxPlazoProyecto < 1) {
      plazosDisponibles.value = []
      form.plazo_cuota_inicial_meses = ''
      form.cuotas_manual_ci = []
      return
    }

    plazosDisponibles.value = Array.from({ length: maxPlazoProyecto }, (_, index) => index + 1)

    if (
      form.plazo_cuota_inicial_meses &&
      !plazosDisponibles.value.includes(Number(form.plazo_cuota_inicial_meses))
    ) {
      form.plazo_cuota_inicial_meses = ''
      form.cuotas_manual_ci = []
    }

    return
  }

  if (!requierePlazoYFrecuencia(plan)) {
    plazosDisponibles.value = []
    form.plazo_cuota_inicial_meses = ''
    form.frecuencia_cuota_inicial_meses = ''
    return
  }

  const plazoTotal = Number(plan.plazo_cuota_inicial_meses || 0)

  if (!plazoTotal) {
    plazosDisponibles.value = []
    form.plazo_cuota_inicial_meses = ''
    form.frecuencia_cuota_inicial_meses = ''
    return
  }

  if (!proyecto.fecha_inicio) {
    plazosDisponibles.value = Array.from({ length: plazoTotal }, (_, index) => index + 1)
    return
  }

  const mesesTranscurridos = calcularMesesEntreFechas(proyecto.fecha_inicio, form.fecha_venta)
  const plazosRestantes = Math.max(plazoTotal - mesesTranscurridos, 0)

  plazosDisponibles.value =
    plazosRestantes > 0 ? Array.from({ length: plazosRestantes }, (_, index) => index + 1) : []

  if (
    form.plazo_cuota_inicial_meses &&
    !plazosDisponibles.value.includes(Number(form.plazo_cuota_inicial_meses))
  ) {
    form.plazo_cuota_inicial_meses = ''
    form.frecuencia_cuota_inicial_meses = ''
  }
}

function limpiarClienteSeleccionado() {
  clienteSeleccionado.value = null
  form.documento_cliente = ''
}

function buscarCliente() {
  clienteBusqueda.error = ''

  const documento = normalizarDocumento(clienteBusqueda.documento)

  if (!documento) {
    limpiarClienteSeleccionado()
    clienteBusqueda.error = 'Ingresa el número de documento del cliente.'
    return
  }

  const cliente = props.clientes.find((item) => normalizarDocumento(item.documento) === documento)

  if (!cliente) {
    limpiarClienteSeleccionado()
    clienteBusqueda.error = 'No se encontró un cliente con ese documento.'
    return
  }

  clienteSeleccionado.value = cliente
  form.documento_cliente = cliente.documento
}

function limpiarBusquedaCliente() {
  clienteBusqueda.documento = ''
  clienteBusqueda.error = ''
  limpiarClienteSeleccionado()
}

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

watch(
  () => form.id_plan_pago_proyecto,
  () => {
    const planActual = planPagoSeleccionado.value

    if (planActual?.tipo_plan === 'especial_manual' && !puedeUsarPlanEspecialManual.value) {
      form.id_plan_pago_proyecto = ''
      form.plazo_cuota_inicial_meses = ''
      form.frecuencia_cuota_inicial_meses = ''
      form.cuotas_manual_ci = []
      return
    }
    form.plazo_cuota_inicial_meses = ''
    form.frecuencia_cuota_inicial_meses = ''
    form.cuotas_manual_ci = []

    if (requierePlazoYFrecuencia(planPagoSeleccionado.value)) {
      form.frecuencia_cuota_inicial_meses = Number(
        planPagoSeleccionado.value?.frecuencia_cuota_inicial_meses || 1
      )
    }

    if (esPlanEspecialManual(planPagoSeleccionado.value)) {
      form.frecuencia_cuota_inicial_meses = ''
    }

    recalcularEconomiaVenta()
    actualizarPlazosDisponibles()
  }
)

function normalizarTextoPermiso(value) {
  return String(value || '')
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .toLowerCase()
    .trim()
}

const puedeUsarPlanEspecialManual = computed(() => {
  const cargo = normalizarTextoPermiso(empleado.value?.cargo?.nombre)

  return cargo === 'directora comercial'
})

function planDisponiblePorPerfil(plan) {
  if (plan?.tipo_plan !== 'especial_manual') {
    return true
  }

  return puedeUsarPlanEspecialManual.value
}

const proyectoTienePlanEspecialManual = computed(() => {
  return (proyectoSeleccionado.value?.planes_pago || []).some(
    (plan) => plan.tipo_plan === 'especial_manual'
  )
})

watch(
  () => form.plazo_cuota_inicial_meses,
  (nuevoPlazo) => {
    if (esPlanEspecialManual(planPagoSeleccionado.value)) {
      form.frecuencia_cuota_inicial_meses = ''
      return
    }

    if (nuevoPlazo) {
      const plazoNum = Number(nuevoPlazo)
      const valoresValidos = frecuenciasDisponibles
        .map((frecuencia) => frecuencia.valor)
        .filter((valor) => plazoNum % valor === 0)

      if (!valoresValidos.includes(Number(form.frecuencia_cuota_inicial_meses))) {
        form.frecuencia_cuota_inicial_meses = valoresValidos.length > 0 ? valoresValidos[0] : ''
      }
    } else {
      form.frecuencia_cuota_inicial_meses = ''
    }
  },
  { immediate: true }
)

watch(
  [
    () => form.plazo_cuota_inicial_meses,
    () => form.fecha_venta,
    () => form.id_plan_pago_proyecto,
    () => resumenPlanVenta.value?.saldo_cuota_inicial,
  ],
  () => {
    if (esPlanEspecialManual(planPagoSeleccionado.value)) {
      generarCuotasManualCI()
    }
  },
  { deep: true }
)

watch(
  [() => form.id_proyecto, () => form.fecha_venta, () => form.id_plan_pago_proyecto],
  () => {
    actualizarPlazosDisponibles()
  },
  { immediate: true }
)

watch(
  () => form.id_parqueadero,
  () => {
    if (form.inmueble_tipo !== 'apartamento') {
      form.id_parqueadero = ''
      return
    }

    recalcularEconomiaVenta()
  }
)

watch(
  resumenPlanVenta,
  () => {
    recalcularEconomiaVenta()
  },
  { deep: true }
)

watch(
  [() => form.cuota_inicial_raw, () => form.id_plan_pago_proyecto, () => resumenPlanVenta.value],
  ([valor]) => {
    const plan = planPagoSeleccionado.value

    if (!plan || !resumenPlanVenta.value) {
      erroresForm.cuota_inicial = ''
      return
    }

    const cuotaInicialCalculada = Math.round(Number(resumenPlanVenta.value.cuota_inicial || 0))
    const valorIngresado = Math.round(Number(valor || 0))

    if (valorIngresado < cuotaInicialCalculada) {
      erroresForm.cuota_inicial = `La cuota inicial del plan es ${formatearMoneda(cuotaInicialCalculada)}`
      return
    }

    erroresForm.cuota_inicial = ''
  },
  { deep: true }
)

onMounted(() => {
  form.fecha_venta = todayISO()
  form.id_estado_inmueble = estadoVendidoId.value
  form.valor_base = valorTotalInmueble.value

  cargarParqueaderosDelProyecto()
  recalcularEconomiaVenta()
  actualizarPlazosDisponibles()

  if (
    !form.id_plan_pago_proyecto &&
    !proyectoTienePlanesPago.value &&
    planCondicionesProyecto.value
  ) {
    form.id_plan_pago_proyecto = PLAN_PROYECTO_DEFAULT_ID
  }
})

function submit() {
  const resumen = resumenPlanVenta.value

  form.errors = {}

  if (resumen) {
    form.valor_total = resumen.valor_total
    form.cuota_inicial = resumen.cuota_inicial
    form.cuota_inicial_raw = resumen.cuota_inicial
    form.valor_restante = resumen.valor_restante
    form.valor_separacion = resumen.cuota_separacion
  }

  if (esPlanEspecialManual(planPagoSeleccionado.value)) {
    if (!form.plazo_cuota_inicial_meses) {
      alert('Debes seleccionar el plazo de cuota inicial para el Plan 05.')
      return
    }

    if (form.cuotas_manual_ci.length !== Number(form.plazo_cuota_inicial_meses || 0)) {
      generarCuotasManualCI()
    }

    const saldoCI = Math.round(Number(resumenPlanVenta.value?.saldo_cuota_inicial || 0))
    const totalManual = Math.round(Number(totalCuotasManualCI.value || 0))

    if (totalManual !== saldoCI) {
      alert('La suma de las cuotas manuales debe ser igual al saldo de cuota inicial.')
      return
    }

    form.frecuencia_cuota_inicial_meses = ''
  }

  form.processing = true

  router.put(
    route('ventas.convertir', props.venta.id_venta),
    {
      tipo_operacion: 'venta',
      id_empleado: form.id_empleado,
      documento_cliente: form.documento_cliente,
      fecha_venta: form.fecha_venta,
      id_proyecto: form.id_proyecto,
      inmueble_tipo: form.inmueble_tipo,
      inmueble_id: form.inmueble_id,
      id_forma_pago: form.id_forma_pago,
      id_estado_inmueble: form.id_estado_inmueble,
      id_plan_pago_proyecto: form.id_plan_pago_proyecto,
      cuota_inicial: form.cuota_inicial,
      valor_total: form.valor_total,
      valor_restante: form.valor_restante,
      plazo_cuota_inicial_meses: form.plazo_cuota_inicial_meses || null,
      frecuencia_cuota_inicial_meses: form.frecuencia_cuota_inicial_meses || null,
      cuotas_manual_ci: form.cuotas_manual_ci,
      descripcion: form.descripcion,
      id_parqueadero: form.id_parqueadero || null,
    },
    {
      preserveScroll: true,
      onError: (errors) => {
        form.errors = errors || {}
        console.log('Errores de validación:', errors)
      },
      onFinish: () => {
        form.processing = false
      },
    }
  )
}

const showClienteModal = ref(false)

const clienteInlineForm = reactive({
  nombre: '',
  id_tipo_cliente: '',
  id_tipo_documento: '',
  documento: '',
  direccion: '',
  telefono: '',
  correo: '',
  id_empleado_asesor: empleado.value?.id_empleado || '',
  processing: false,
  errors: {},
})

function openClienteModal() {
  clienteInlineForm.errors = {}
  showClienteModal.value = true
  document.body.classList.add('overflow-hidden')
}

function closeClienteModal() {
  showClienteModal.value = false
  document.body.classList.remove('overflow-hidden')
}

function resetClienteInlineForm() {
  clienteInlineForm.nombre = ''
  clienteInlineForm.id_tipo_cliente = ''
  clienteInlineForm.id_tipo_documento = ''
  clienteInlineForm.documento = ''
  clienteInlineForm.direccion = ''
  clienteInlineForm.telefono = ''
  clienteInlineForm.correo = ''
  clienteInlineForm.id_empleado_asesor = empleado.value?.id_empleado || ''
  clienteInlineForm.processing = false
  clienteInlineForm.errors = {}
}

function submitClienteInline() {
  clienteInlineForm.processing = true
  clienteInlineForm.errors = {}

  router.post(
    '/clientes',
    {
      nombre: clienteInlineForm.nombre,
      id_tipo_cliente: clienteInlineForm.id_tipo_cliente,
      id_tipo_documento: clienteInlineForm.id_tipo_documento,
      documento: clienteInlineForm.documento,
      direccion: clienteInlineForm.direccion,
      telefono: clienteInlineForm.telefono,
      correo: clienteInlineForm.correo,
      id_empleado_asesor: clienteInlineForm.id_empleado_asesor,
      redirect_to: `${window.location.pathname}${window.location.search}`,
    },
    {
      preserveScroll: true,
      onError: (errors) => {
        clienteInlineForm.errors = errors || {}
        clienteInlineForm.processing = false
      },
      onSuccess: () => {
        closeClienteModal()
        const nuevoClienteDocumento = clienteInlineForm.documento
        resetClienteInlineForm()

        if (nuevoClienteDocumento) {
          clienteBusqueda.documento = nuevoClienteDocumento
          nextTick(() => buscarCliente())
        }
      },
      onFinish: () => {
        clienteInlineForm.processing = false
      },
    }
  )
}
</script>

<template>
  <VentasLayout :empleado="empleado">
    <Head title="Convertir Separación a Venta" />

    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm overflow-hidden mb-6">
      <div class="bg-gradient-to-r from-[#FFFFFF] to-[#F0F4F8] px-6 py-6">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
          <div class="min-w-0">
            <div class="flex items-center gap-2 text-[#000000]">
              <ClipboardDocumentCheckIcon class="w-6 h-6" />
              <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
                Convertir separación a venta
              </h1>
            </div>
            <p class="mt-1 text-sm text-[#000000]/80">
              La fecha de venta se tomará automáticamente como el día de hoy.
            </p>
          </div>

          <div class="flex items-center gap-2">
            <Link
              href="/ventas"
              class="inline-flex items-center gap-2 rounded-xl border border-[#000000]/20 bg-white/70 px-4 py-2.5 text-sm font-semibold text-[#474100] hover:bg-white transition"
            >
              <ArrowLeftIcon class="w-5 h-5" />
              Volver
            </Link>

            <div class="rounded-xl bg-white/70 border border-[#474100]/20 px-4 py-2.5">
              <p class="text-xs font-semibold text-[#474100]/70">Operación</p>
              <p class="text-sm font-extrabold text-[#474100]">Separación #{{ venta.id_venta }}</p>
            </div>
          </div>
        </div>
      </div>

      <div
        v-if="Object.keys(form.errors).length"
        class="mx-6 mt-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"
      >
        <p class="font-semibold mb-1">No se pudo convertir la separación.</p>
        <ul class="list-disc pl-5 space-y-1">
          <li v-for="(msg, key) in form.errors" :key="key">
            {{ msg }}
          </li>
        </ul>
      </div>

      <div class="px-6 py-6 bg-white">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <form @submit.prevent="submit" class="lg:col-span-2 space-y-6">
            <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
              <div class="px-5 py-4 border-b border-gray-200 flex items-center justify-between">
                <div class="flex items-center gap-2">
                  <HomeModernIcon class="w-5 h-5 text-[#1e3a5f]" />
                  <h2 class="text-sm font-extrabold text-gray-900">Datos principales</h2>
                </div>
                <span
                  class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-semibold bg-emerald-50 text-emerald-800 border-emerald-200"
                >
                  Venta
                </span>
              </div>

              <div class="p-5 grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                  <label :class="labelClass()">Fecha de venta</label>
                  <input
                    type="date"
                    v-model="form.fecha_venta"
                    disabled
                    :class="inputClass(false, true)"
                  />
                  <p :class="hintClass()">Se fija automáticamente al día de hoy.</p>
                </div>

                <div>
                  <label :class="labelClass()">Empleado</label>
                  <input
                    type="text"
                    :value="empleado?.nombre + ' ' + empleado?.apellido"
                    readonly
                    :class="inputClass(false, true)"
                  />
                </div>

                <div class="md:col-span-2">
                  <div class="flex items-center justify-between mb-1">
                    <label :class="labelClass()">Cliente *</label>
                    <button
                      type="button"
                      @click="openClienteModal"
                      class="inline-flex items-center gap-2 rounded-xl px-3 py-2 text-xs font-semibold text-[#474100] bg-gradient-to-r from-[#FFEA00] to-[#D1C000] hover:shadow-md transition focus:outline-none focus:ring-2 focus:ring-[#FFEA00] focus:ring-offset-2"
                    >
                      <UserPlusIcon class="w-4 h-4" />
                      Nuevo cliente
                    </button>
                  </div>

                  <div class="flex flex-col sm:flex-row gap-3">
                    <div class="flex-1">
                      <input
                        v-model="clienteBusqueda.documento"
                        type="text"
                        inputmode="numeric"
                        maxlength="20"
                        autocomplete="off"
                        :class="inputClass(Boolean(clienteBusqueda.error), false)"
                        placeholder="Digita el número de documento"
                        @keyup.enter="buscarCliente"
                      />
                    </div>

                    <div class="flex gap-2">
                      <button
                        type="button"
                        @click="buscarCliente"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#1e3a5f] px-4 py-2.5 text-sm font-bold text-white hover:bg-[#2c5282] transition"
                      >
                        <MagnifyingGlassIcon class="w-4 h-4" />
                        Buscar
                      </button>

                      <button
                        type="button"
                        @click="limpiarBusquedaCliente"
                        class="inline-flex items-center justify-center rounded-xl border border-gray-300 px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition"
                      >
                        Limpiar
                      </button>
                    </div>
                  </div>

                  <p v-if="clienteBusqueda.error" :class="errorClass()">
                    {{ clienteBusqueda.error }}
                  </p>

                  <div
                    v-if="clienteSeleccionado"
                    class="mt-4 rounded-2xl border border-sky-200 bg-sky-50 p-4"
                  >
                    <div class="flex items-center gap-2 mb-3">
                      <IdentificationIcon class="w-5 h-5 text-sky-700" />
                      <h3 class="text-sm font-extrabold text-sky-900">Cliente encontrado</h3>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                      <div>
                        <p class="text-xs font-semibold text-sky-700 uppercase tracking-wide">
                          Nombre completo
                        </p>
                        <p class="mt-1 font-semibold text-gray-900">
                          {{ clienteSeleccionado.nombre || '—' }}
                        </p>
                      </div>

                      <div>
                        <p class="text-xs font-semibold text-sky-700 uppercase tracking-wide">
                          Documento
                        </p>
                        <p class="mt-1 font-semibold text-gray-900">
                          {{ clienteSeleccionado.documento || '—' }}
                        </p>
                      </div>

                      <div>
                        <p class="text-xs font-semibold text-sky-700 uppercase tracking-wide">
                          Dirección
                        </p>
                        <p class="mt-1 font-semibold text-gray-900">
                          {{ clienteSeleccionado.direccion || '—' }}
                        </p>
                      </div>

                      <div>
                        <p class="text-xs font-semibold text-sky-700 uppercase tracking-wide">
                          Teléfono
                        </p>
                        <p class="mt-1 font-semibold text-gray-900">
                          {{ clienteSeleccionado.telefono || '—' }}
                        </p>
                      </div>

                      <div class="md:col-span-2">
                        <p class="text-xs font-semibold text-sky-700 uppercase tracking-wide">
                          Correo
                        </p>
                        <p class="mt-1 font-semibold text-gray-900">
                          {{ clienteSeleccionado.correo || '—' }}
                        </p>
                      </div>
                    </div>
                  </div>

                  <p v-else class="text-xs text-gray-500 mt-2">
                    Busca el cliente por número de documento y luego continúa con la operación.
                  </p>
                </div>

                <div>
                  <label :class="labelClass()">Proyecto</label>
                  <input
                    type="text"
                    :value="proyectoSeleccionado?.nombre || '—'"
                    readonly
                    :class="inputClass(false, true)"
                  />
                  <input type="hidden" v-model="form.id_proyecto" />
                </div>

                <div>
                  <label :class="labelClass()">Plan de venta *</label>
                  <select
                    v-model="form.id_plan_pago_proyecto"
                    :disabled="planesPagoProyecto.length === 0"
                    :class="
                      inputClass(
                        Boolean(form.errors.id_plan_pago_proyecto),
                        planesPagoProyecto.length === 0
                      )
                    "
                  >
                    <option value="">Seleccione...</option>
                    <option
                      v-for="plan in planesPagoProyecto"
                      :key="plan.id_plan_pago_proyecto"
                      :value="plan.id_plan_pago_proyecto"
                    >
                      {{ plan.nombre }} - {{ tipoPlanLabel(plan.tipo_plan) }}
                    </option>
                  </select>

                  <p
                    v-if="proyectoSeleccionado && !proyectoTienePlanesPago"
                    class="mt-2 rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-xs font-medium text-amber-700"
                  >
                    Este proyecto no tiene planes configurados. Se usarán las condiciones económicas
                    generales del proyecto.
                  </p>

                  <p v-if="form.errors.id_plan_pago_proyecto" :class="errorClass()">
                    {{ form.errors.id_plan_pago_proyecto }}
                  </p>
                </div>

                <div
                  v-if="planPagoSeleccionado"
                  class="md:col-span-2 rounded-2xl border border-[#FFEA00]/50 bg-[#FFFDE6] p-4"
                >
                  <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                    <div>
                      <p class="text-sm font-extrabold text-[#474100]">
                        {{ planPagoSeleccionado.nombre }}
                      </p>
                      <p class="mt-1 text-xs font-semibold text-[#756C00]">
                        {{ tipoPlanLabel(planPagoSeleccionado.tipo_plan) }}
                      </p>
                    </div>

                    <span
                      class="rounded-full border border-[#D1C000]/50 bg-white px-3 py-1 text-xs font-bold text-[#756C00]"
                    >
                      {{ planPagoSeleccionado.codigo }}
                    </span>
                  </div>

                  <div class="mt-4 grid grid-cols-1 gap-3 md:grid-cols-4 text-sm">
                    <div class="rounded-xl bg-white/80 px-4 py-3">
                      <p class="text-[11px] font-semibold uppercase tracking-wide text-[#756C00]">
                        Separación
                      </p>
                      <p class="mt-1 font-bold text-gray-900">
                        {{ formatearMoneda(planPagoSeleccionado.valor_separacion) }}
                      </p>
                    </div>

                    <div class="rounded-xl bg-white/80 px-4 py-3">
                      <p class="text-[11px] font-semibold uppercase tracking-wide text-[#756C00]">
                        Cuota inicial
                      </p>
                      <p class="mt-1 font-bold text-gray-900">
                        {{ Number(planPagoSeleccionado.porcentaje_cuota_inicial || 0) }}%
                      </p>
                    </div>

                    <div class="rounded-xl bg-white/80 px-4 py-3">
                      <p class="text-[11px] font-semibold uppercase tracking-wide text-[#756C00]">
                        Descuento
                      </p>
                      <p class="mt-1 font-bold text-gray-900">
                        {{
                          planPagoSeleccionado.tipo_descuento === 'ninguno'
                            ? 'Sin descuento'
                            : planPagoSeleccionado.tipo_descuento === 'valor_fijo'
                              ? formatearMoneda(planPagoSeleccionado.valor_descuento)
                              : `${planPagoSeleccionado.valor_descuento}%`
                        }}
                      </p>
                    </div>

                    <div class="rounded-xl bg-white/80 px-4 py-3">
                      <p class="text-[11px] font-semibold uppercase tracking-wide text-[#756C00]">
                        Valor restante
                      </p>
                      <p class="mt-1 font-bold text-gray-900">
                        {{
                          resumenPlanVenta ? formatearMoneda(resumenPlanVenta.valor_restante) : '—'
                        }}
                      </p>
                    </div>
                  </div>

                  <div
                    v-if="planPagoSeleccionado.beneficio_comercial"
                    class="mt-3 rounded-xl bg-white/80 px-4 py-3"
                  >
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-[#756C00]">
                      Beneficio / compromiso comercial
                    </p>
                    <p class="mt-1 font-bold text-gray-900">
                      {{ planPagoSeleccionado.beneficio_comercial }}
                    </p>
                  </div>
                </div>

                <div>
                  <label :class="labelClass()">Inmueble</label>
                  <input
                    type="text"
                    :value="resumenInmueble?.label || '—'"
                    readonly
                    :class="inputClass(false, true)"
                  />
                  <input type="hidden" v-model="form.inmueble_id" />
                  <input type="hidden" v-model="form.inmueble_tipo" />
                </div>

                <div>
                  <label :class="labelClass()">Parqueadero adicional (opcional)</label>
                  <select
                    v-model="form.id_parqueadero"
                    :disabled="form.inmueble_tipo !== 'apartamento'"
                    :class="inputClass(false, form.inmueble_tipo !== 'apartamento')"
                  >
                    <option value="">Sin parqueadero adicional</option>
                    <option
                      v-for="p in parqueaderosDisponibles"
                      :key="p.id_parqueadero"
                      :value="String(p.id_parqueadero)"
                    >
                      {{ p.numero }} · {{ p.tipo }} · {{ formatearMoneda(p.precio) }}
                    </option>
                  </select>
                  <p :class="hintClass()">Se suma al valor total y recalcula la operación.</p>
                </div>

                <div>
                  <label :class="labelClass()">Forma de pago *</label>
                  <select
                    v-model="form.id_forma_pago"
                    :class="inputClass(Boolean(form.errors.id_forma_pago), false)"
                  >
                    <option value="">Seleccione...</option>
                    <option
                      v-for="fp in formasPago"
                      :key="fp.id_forma_pago"
                      :value="String(fp.id_forma_pago)"
                    >
                      {{ fp.forma_pago }}
                    </option>
                  </select>
                  <p v-if="form.errors.id_forma_pago" :class="errorClass()">
                    {{ form.errors.id_forma_pago }}
                  </p>
                </div>

                <div>
                  <label :class="labelClass()">Estado del inmueble</label>
                  <input
                    type="text"
                    :value="estadoNombre"
                    disabled
                    :class="inputClass(false, true)"
                  />
                  <input type="hidden" v-model="form.id_estado_inmueble" />
                </div>

                <div class="md:col-span-2">
                  <label :class="labelClass()">Descripción</label>
                  <textarea v-model="form.descripcion" rows="3" :class="inputClass(false, false)" />
                </div>
              </div>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
              <div class="px-5 py-4 border-b border-gray-200 flex items-center gap-2">
                <BanknotesIcon class="w-5 h-5 text-[#1e3a5f]" />
                <h2 class="text-sm font-extrabold text-gray-900">Condiciones económicas</h2>
              </div>

              <div class="p-5 grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                  <label :class="labelClass()">Valor total</label>
                  <input
                    type="text"
                    :value="form.valor_total ? formatearMoneda(form.valor_total) : '—'"
                    readonly
                    :class="inputClass(false, true)"
                  />
                </div>

                <div>
                  <label :class="labelClass()">Cuota inicial</label>
                  <input
                    type="text"
                    :value="formatearMoneda(form.cuota_inicial_raw)"
                    readonly
                    :class="inputClass(Boolean(erroresForm.cuota_inicial), true)"
                  />
                  <p v-if="erroresForm.cuota_inicial" :class="errorClass()">
                    {{ erroresForm.cuota_inicial }}
                  </p>
                </div>

                <div
                  v-if="
                    requierePlazoYFrecuencia(planPagoSeleccionado) ||
                    requiereSoloPlazo(planPagoSeleccionado)
                  "
                >
                  <label :class="labelClass()">Plazo cuota inicial (meses) *</label>
                  <select
                    v-model="form.plazo_cuota_inicial_meses"
                    :class="inputClass(Boolean(form.errors.plazo_cuota_inicial_meses), false)"
                  >
                    <option value="">Seleccione...</option>
                    <option v-for="p in plazosDisponibles" :key="p" :value="p">
                      {{ p }} mes{{ Number(p) === 1 ? '' : 'es' }}
                    </option>
                  </select>

                  <p v-if="form.errors.plazo_cuota_inicial_meses" :class="errorClass()">
                    {{ form.errors.plazo_cuota_inicial_meses }}
                  </p>

                  <p v-if="esPlanEspecialManual(planPagoSeleccionado)" :class="hintClass()">
                    Este plazo generará automáticamente las cuotas mensuales de la cuota inicial.
                    Plazo máximo permitido:
                    {{ proyectoSeleccionado?.plazo_cuota_inicial_meses || 0 }} meses.
                  </p>
                </div>

                <div v-if="requierePlazoYFrecuencia(planPagoSeleccionado)">
                  <label :class="labelClass()">Frecuencia Pago Cuota Inicial</label>
                  <select
                    v-model="form.frecuencia_cuota_inicial_meses"
                    :disabled="!form.plazo_cuota_inicial_meses || opcionesFrecuencia.length === 0"
                    :class="
                      inputClass(
                        Boolean(form.errors.frecuencia_cuota_inicial_meses),
                        !form.plazo_cuota_inicial_meses || opcionesFrecuencia.length === 0
                      )
                    "
                  >
                    <option value="">Seleccione...</option>
                    <option v-for="f in opcionesFrecuencia" :key="f.valor" :value="f.valor">
                      {{ f.etiqueta }}
                    </option>
                  </select>

                  <p v-if="form.errors.frecuencia_cuota_inicial_meses" :class="errorClass()">
                    {{ form.errors.frecuencia_cuota_inicial_meses }}
                  </p>
                </div>

                <div
                  v-if="esPlanEspecialManual(planPagoSeleccionado)"
                  class="md:col-span-2 rounded-2xl border border-amber-200 bg-amber-50 p-4"
                >
                  <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                    <div>
                      <p class="text-sm font-extrabold text-amber-900">
                        Cuotas mensuales de cuota inicial
                      </p>
                      <p class="mt-1 text-xs text-amber-700">
                        Se generan automáticamente según el plazo seleccionado. Solo debes editar el
                        valor de cada cuota.
                      </p>
                    </div>

                    <div class="rounded-xl bg-white px-4 py-3 text-right border border-amber-100">
                      <p class="text-[11px] font-semibold uppercase tracking-wide text-amber-700">
                        Saldo cuota inicial
                      </p>
                      <p class="mt-1 text-sm font-extrabold text-amber-900">
                        {{ formatearMoneda(resumenPlanVenta?.saldo_cuota_inicial || 0) }}
                      </p>
                    </div>
                  </div>

                  <div
                    v-if="!form.plazo_cuota_inicial_meses"
                    class="mt-4 rounded-xl bg-white p-4 text-sm text-amber-800"
                  >
                    Selecciona el plazo de cuota inicial para generar el listado de cuotas.
                  </div>

                  <div
                    v-else
                    class="mt-4 overflow-x-auto rounded-2xl border border-amber-100 bg-white"
                  >
                    <table class="min-w-full text-sm">
                      <thead class="bg-amber-100/70 text-amber-900">
                        <tr>
                          <th class="px-4 py-3 text-center font-bold">#</th>
                          <th class="px-4 py-3 text-left font-bold">Mes cuota</th>
                          <th class="px-4 py-3 text-right font-bold">Valor cuota</th>
                        </tr>
                      </thead>

                      <tbody class="divide-y divide-amber-100">
                        <tr v-for="(cuota, index) in form.cuotas_manual_ci" :key="index">
                          <td class="px-4 py-3 text-center font-semibold text-gray-800">
                            {{ index + 1 }}
                          </td>

                          <td class="px-4 py-3">
                            <div class="font-semibold text-gray-900 capitalize">
                              {{ cuota.mes_label }}
                            </div>
                            <div class="text-xs text-gray-500">
                              Fecha interna: {{ cuota.fecha_vencimiento }}
                            </div>
                          </td>

                          <td class="px-4 py-3">
                            <input
                              v-model="cuota.valor_cuota"
                              type="number"
                              min="0"
                              step="1"
                              class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-right text-sm font-semibold text-gray-900 focus:outline-none focus:ring-2 focus:ring-amber-500"
                            />
                          </td>
                        </tr>
                      </tbody>

                      <tfoot class="bg-amber-50">
                        <tr>
                          <td colspan="2" class="px-4 py-3 text-right font-bold text-amber-900">
                            Total cuotas
                          </td>
                          <td class="px-4 py-3 text-right font-extrabold text-amber-900">
                            {{ formatearMoneda(totalCuotasManualCI) }}
                          </td>
                        </tr>

                        <tr>
                          <td colspan="2" class="px-4 py-3 text-right font-bold text-amber-900">
                            Diferencia
                          </td>
                          <td
                            class="px-4 py-3 text-right font-extrabold"
                            :class="
                              Math.round(diferenciaCuotasManualCI) === 0
                                ? 'text-emerald-700'
                                : 'text-red-700'
                            "
                          >
                            {{ formatearMoneda(diferenciaCuotasManualCI) }}
                          </td>
                        </tr>
                      </tfoot>
                    </table>
                  </div>

                  <p
                    v-if="
                      form.plazo_cuota_inicial_meses && Math.round(diferenciaCuotasManualCI) !== 0
                    "
                    class="mt-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-xs font-semibold text-red-700"
                  >
                    La suma de las cuotas debe ser igual al saldo de cuota inicial. Diferencia
                    actual: {{ formatearMoneda(diferenciaCuotasManualCI) }}.
                  </p>

                  <p v-if="form.errors.cuotas_manual_ci" :class="errorClass()">
                    {{ form.errors.cuotas_manual_ci }}
                  </p>
                </div>

                <div>
                  <label :class="labelClass()">Valor restante</label>
                  <input
                    type="text"
                    :value="form.valor_total ? formatearMoneda(form.valor_restante) : '—'"
                    readonly
                    :class="inputClass(false, true)"
                  />
                </div>

                <div class="md:col-span-2">
                  <div
                    class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-xs text-gray-700"
                  >
                    <div class="flex items-center justify-between">
                      <span>Separación pagada en operación:</span>
                      <span class="font-semibold">{{
                        formatearMoneda(venta.valor_separacion || 0)
                      }}</span>
                    </div>
                    <div class="flex items-center justify-between mt-2">
                      <span>Separación definida por el plan:</span>
                      <span class="font-semibold">{{
                        formatearMoneda(resumenPlanVenta?.cuota_separacion || 0)
                      }}</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="flex items-center justify-end gap-3">
              <button
                type="submit"
                :disabled="
                  !camposCompletos || form.processing || Boolean(erroresForm.cuota_inicial)
                "
                class="inline-flex items-center justify-center gap-2 rounded-xl px-5 py-3 text-sm font-extrabold transition focus:outline-none focus:ring-2 focus:ring-[#1e3a5f] focus:ring-offset-2"
                :class="[
                  camposCompletos && !form.processing && !erroresForm.cuota_inicial
                    ? 'bg-[#1e3a5f] text-white hover:bg-[#2c5282]'
                    : 'bg-gray-200 text-gray-500 cursor-not-allowed',
                ]"
              >
                <span
                  v-if="form.processing"
                  class="animate-spin w-4 h-4 border-2 border-white border-t-transparent rounded-full"
                />
                <CheckSolid v-else class="w-5 h-5" />
                {{ form.processing ? 'Guardando...' : 'Convertir a venta' }}
              </button>
            </div>
          </form>

          <div class="space-y-4">
            <div class="rounded-2xl border border-gray-200 bg-white shadow-sm p-5 sticky top-6">
              <div class="flex items-center gap-2 mb-3">
                <BuildingOffice2Icon class="w-5 h-5 text-[#1e3a5f]" />
                <h3 class="text-sm font-extrabold text-gray-900">Resumen</h3>
              </div>

              <div class="space-y-3 text-sm">
                <div class="flex items-center justify-between">
                  <span class="text-gray-600">Cliente</span>
                  <span class="font-semibold text-gray-900 text-right">
                    {{ clienteSeleccionado?.nombre || '—' }}
                  </span>
                </div>

                <div class="flex items-center justify-between">
                  <span class="text-gray-600">Tipo</span>
                  <span class="font-semibold text-gray-900">Venta</span>
                </div>

                <div class="flex items-center justify-between">
                  <span class="text-gray-600">Fecha</span>
                  <span class="font-semibold text-gray-900">{{ form.fecha_venta }}</span>
                </div>

                <div class="flex items-center justify-between">
                  <span class="text-gray-600">Proyecto</span>
                  <span class="font-semibold text-gray-900 text-right">
                    {{ proyectoSeleccionado?.nombre || '—' }}
                  </span>
                </div>

                <div class="flex items-center justify-between">
                  <span class="text-gray-600">Plan</span>
                  <span class="font-semibold text-gray-900 text-right">
                    {{ planPagoSeleccionado?.nombre || '—' }}
                  </span>
                </div>

                <div class="flex items-center justify-between">
                  <span class="text-gray-600">Inmueble</span>
                  <span class="font-semibold text-gray-900">
                    {{ resumenInmueble?.label || '—' }}
                  </span>
                </div>

                <div class="flex items-center justify-between">
                  <span class="text-gray-600">Estado</span>
                  <span class="font-semibold text-gray-900">{{ estadoNombre }}</span>
                </div>

                <div class="pt-3 border-t border-gray-200 space-y-2">
                  <div class="flex items-center justify-between">
                    <span class="text-gray-600">Valor Apartamento</span>
                    <span class="font-extrabold text-[#1e3a5f]">
                      {{ formatearMoneda(form.valor_base) }}
                    </span>
                  </div>

                  <div class="flex items-center justify-between">
                    <span class="text-gray-600">Parqueadero</span>
                    <span class="font-extrabold text-[#1e3a5f]">
                      {{ resumenParqueadero ? formatearMoneda(resumenParqueadero.precio) : '—' }}
                    </span>
                  </div>

                  <div class="flex items-center justify-between">
                    <span class="text-gray-600">Valor total</span>
                    <span class="font-extrabold text-[#1e3a5f]">
                      {{ formatearMoneda(form.valor_total) }}
                    </span>
                  </div>

                  <div class="flex items-center justify-between text-xs text-gray-600">
                    <span>Cuota inicial</span>
                    <span class="font-semibold text-gray-900">
                      {{ formatearMoneda(form.cuota_inicial_raw) }}
                    </span>
                  </div>

                  <div class="flex items-center justify-between text-xs text-gray-600">
                    <span>Restante</span>
                    <span class="font-semibold text-gray-900">
                      {{ formatearMoneda(form.valor_restante) }}
                    </span>
                  </div>
                </div>
              </div>

              <div
                class="mt-4 rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-xs text-gray-700"
              >
                La separación se convierte a venta. La fecha se toma del día actual y las
                condiciones económicas se calculan según el plan seleccionado.
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <teleport to="body">
      <div v-if="showClienteModal" class="fixed inset-0 z-50">
        <div class="absolute inset-0 bg-black/40" @click="closeClienteModal" />

        <div class="absolute inset-0 flex items-center justify-center p-4">
          <div
            class="relative w-full max-w-3xl rounded-2xl bg-white shadow-xl border border-gray-200 overflow-hidden"
            role="dialog"
            aria-modal="true"
          >
            <div
              class="px-5 py-4 border-b border-gray-200 flex items-center justify-between bg-white"
            >
              <div class="flex items-center gap-2">
                <UserPlusIcon class="w-5 h-5 text-[#1e3a5f]" />
                <h3 class="text-base font-extrabold text-gray-900">Crear cliente</h3>
              </div>
              <button
                type="button"
                class="inline-flex items-center justify-center rounded-xl p-2 hover:bg-gray-50 text-gray-600 hover:text-gray-900 transition"
                @click="closeClienteModal"
              >
                <XMarkIcon class="w-5 h-5" />
              </button>
            </div>

            <div class="max-h-[calc(100vh-11rem)] overflow-y-auto px-5 py-5">
              <ClienteForm
                :form="clienteInlineForm"
                :tipos-cliente="tiposCliente"
                :tipos-documento="tiposDocumento"
                :empleado="empleado"
                submit-text="Crear Cliente"
                :processing="clienteInlineForm.processing"
                cancel-url="#"
                @submit="submitClienteInline"
              />
            </div>
          </div>
        </div>
      </div>
    </teleport>
  </VentasLayout>
</template>
