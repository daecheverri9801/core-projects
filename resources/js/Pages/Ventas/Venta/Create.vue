<script setup>
import { Head, Link, useForm, usePage, router } from '@inertiajs/vue3'
import { computed, ref, watch, onMounted, reactive, nextTick } from 'vue'
import {
  CheckCircleIcon,
  ExclamationTriangleIcon,
  BuildingOffice2Icon,
  UserPlusIcon,
  ArrowLeftIcon,
  ClipboardDocumentCheckIcon,
  BanknotesIcon,
  CalendarDaysIcon,
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
  clientes: Array,
  empleados: Array,
  proyectos: Array,
  apartamentos: Array,
  locales: Array,
  formasPago: Array,
  estadosInmueble: Array,
  parqueaderos: { type: Array, default: () => [] },
  empleadoProp: Object,
  inmueblePrecargado: Object,
  plazos_disponibles: Array,
  tiposCliente: Array,
  tiposDocumento: Array,
})

const plazosDisponibles = ref([])
const inmueblesDisponibles = ref([])
const parqueaderosDisponibles = ref([])

const form = useForm({
  tipo_operacion: '',
  id_empleado: empleado.value?.id_empleado || null,
  documento_cliente: '',
  fecha_venta: new Date().toISOString().slice(0, 10),
  fecha_vencimiento: null,
  id_proyecto: '',
  inmueble_tipo: '',
  inmueble_id: '',
  inmueble_uid: '',
  id_forma_pago: '',
  id_estado_inmueble: '',
  valor_base: 0,
  iva: 0,
  valor_total: 0,
  id_parqueadero: '',
  cuota_inicial: 0,
  cuota_inicial_raw: 0,
  valor_restante: 0,
  descripcion: '',
  valor_separacion: 0,
  fecha_limite_separacion: '',
  plazo_cuota_inicial_meses: '',
  frecuencia_cuota_inicial_meses: '',
})

const proyectoSeleccionado = computed(() =>
  props.proyectos.find((p) => p.id_proyecto === parseInt(form.id_proyecto))
)

const estadoNombre = computed(() => {
  const e = props.estadosInmueble.find((x) => x.id_estado_inmueble === form.id_estado_inmueble)
  return e?.nombre || '—'
})

const erroresForm = reactive({
  cuota_inicial: '',
  valor_separacion: '',
  fecha_limite_separacion: '',
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
  const plazo = Number(form.plazo_cuota_inicial_meses)
  if (!plazo) return []
  return frecuenciasDisponibles.filter((f) => plazo % f.valor === 0)
})

watch(
  () => form.plazo_cuota_inicial_meses,
  (nuevoPlazo) => {
    if (nuevoPlazo) {
      const plazoNum = Number(nuevoPlazo)
      const valoresValidos = frecuenciasDisponibles
        .map((f) => f.valor)
        .filter((v) => plazoNum % v === 0)

      if (!valoresValidos.includes(Number(form.frecuencia_cuota_inicial_meses))) {
        form.frecuencia_cuota_inicial_meses = valoresValidos.length > 0 ? valoresValidos[0] : ''
      }
    } else {
      form.frecuencia_cuota_inicial_meses = ''
    }
  },
  { immediate: true }
)

function formatearMoneda(valor) {
  if (valor === null || valor === undefined || valor === '') return ''
  return Number(valor).toLocaleString('es-CO', {
    style: 'currency',
    currency: 'COP',
    maximumFractionDigits: 0,
  })
}

function parseMoneda(valorStr) {
  if (!valorStr) return 0
  return Number(valorStr.replace(/\./g, '').replace(/[^0-9]/g, ''))
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

const steps = computed(() => {
  const s = [
    Boolean(form.tipo_operacion),
    Boolean(form.documento_cliente),
    Boolean(form.id_proyecto),
    Boolean(form.inmueble_id),
    Boolean(form.id_forma_pago),
  ]
  const done = s.filter(Boolean).length
  return { done, total: s.length, pct: Math.round((done / s.length) * 100) }
})

const resumenInmueble = computed(() => {
  const inm = inmueblesDisponibles.value.find((i) => i.uid === form.inmueble_uid)
  if (!inm) return null
  return inm
})

const resumenParqueadero = computed(() => {
  const p = parqueaderosDisponibles.value.find(
    (x) => x.id_parqueadero === Number(form.id_parqueadero)
  )
  return p || null
})

const precioParqueaderoSeleccionado = computed(() => {
  if (!form.id_parqueadero) return 0
  const p = parqueaderosDisponibles.value.find(
    (x) => x.id_parqueadero === Number(form.id_parqueadero)
  )
  return p ? Number(p.precio || 0) : 0
})

const clienteBusqueda = reactive({
  documento: '',
  error: '',
})

const clienteSeleccionado = ref(null)

function normalizarDocumento(value) {
  return String(value || '').replace(/\D/g, '')
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

  const cliente = (props.clientes || []).find((c) => normalizarDocumento(c.documento) === documento)

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
    if (limpio !== value) clienteBusqueda.documento = limpio

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

/** ===== Modal crear cliente ===== */
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

function setClienteFieldError(field, message) {
  clienteInlineForm.errors = {
    ...clienteInlineForm.errors,
    [field]: message,
  }
}

function clearClienteFieldError(field) {
  if (!clienteInlineForm.errors[field]) return

  const newErrors = { ...clienteInlineForm.errors }
  delete newErrors[field]
  clienteInlineForm.errors = newErrors
}

function validateClienteNombre() {
  const value = clienteInlineForm.nombre?.trim() || ''

  if (!value) {
    setClienteFieldError('nombre', 'El nombre es obligatorio.')
    return false
  }

  if (value.length < 3) {
    setClienteFieldError('nombre', 'El nombre debe tener al menos 3 caracteres.')
    return false
  }

  if (!/^[A-Za-zÁÉÍÓÚáéíóúÑñÜü\s]+$/.test(value)) {
    setClienteFieldError('nombre', 'El nombre solo puede contener letras y espacios.')
    return false
  }

  clearClienteFieldError('nombre')
  return true
}

function validateClienteTipoCliente() {
  if (!clienteInlineForm.id_tipo_cliente) {
    setClienteFieldError('id_tipo_cliente', 'Debes seleccionar un tipo de cliente.')
    return false
  }

  clearClienteFieldError('id_tipo_cliente')
  return true
}

function validateClienteTipoDocumento() {
  if (!clienteInlineForm.id_tipo_documento) {
    setClienteFieldError('id_tipo_documento', 'Debes seleccionar un tipo de documento.')
    return false
  }

  clearClienteFieldError('id_tipo_documento')
  return true
}

function validateClienteDocumento() {
  const value = (clienteInlineForm.documento || '').trim()

  if (!value) {
    setClienteFieldError('documento', 'El número de documento es obligatorio.')
    return false
  }

  if (!/^\d+$/.test(value)) {
    setClienteFieldError('documento', 'El documento solo puede contener números.')
    return false
  }

  if (value.length < 5) {
    setClienteFieldError('documento', 'El documento debe tener al menos 5 dígitos.')
    return false
  }

  if (value.length > 20) {
    setClienteFieldError('documento', 'El documento no puede superar los 20 dígitos.')
    return false
  }

  clearClienteFieldError('documento')
  return true
}

function validateClienteDireccion() {
  const value = (clienteInlineForm.direccion || '').trim()

  if (!value) {
    clearClienteFieldError('direccion')
    return true
  }

  if (value.length < 5) {
    setClienteFieldError('direccion', 'La dirección debe tener al menos 5 caracteres.')
    return false
  }

  if (value.length > 255) {
    setClienteFieldError('direccion', 'La dirección no puede superar los 255 caracteres.')
    return false
  }

  clearClienteFieldError('direccion')
  return true
}

function validateClienteTelefono() {
  const value = (clienteInlineForm.telefono || '').trim()

  if (!value) {
    clearClienteFieldError('telefono')
    return true
  }

  if (!/^\d+$/.test(value)) {
    setClienteFieldError('telefono', 'El teléfono solo puede contener números.')
    return false
  }

  if (value.length < 7) {
    setClienteFieldError('telefono', 'El teléfono debe tener al menos 7 dígitos.')
    return false
  }

  if (value.length > 15) {
    setClienteFieldError('telefono', 'El teléfono no puede superar los 15 dígitos.')
    return false
  }

  clearClienteFieldError('telefono')
  return true
}

function validateClienteCorreo() {
  const value = (clienteInlineForm.correo || '').trim()

  if (!value) {
    clearClienteFieldError('correo')
    return true
  }

  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/

  if (!emailRegex.test(value)) {
    setClienteFieldError('correo', 'Debes ingresar un correo electrónico válido.')
    return false
  }

  if (value.length > 255) {
    setClienteFieldError('correo', 'El correo no puede superar los 255 caracteres.')
    return false
  }

  clearClienteFieldError('correo')
  return true
}

function validateClienteAsesor() {
  if (!clienteInlineForm.id_empleado_asesor) {
    setClienteFieldError('id_empleado_asesor', 'No se encontró el asesor responsable.')
    return false
  }

  clearClienteFieldError('id_empleado_asesor')
  return true
}

function validateClienteInlineForm() {
  const results = [
    validateClienteNombre(),
    validateClienteTipoCliente(),
    validateClienteTipoDocumento(),
    validateClienteDocumento(),
    validateClienteDireccion(),
    validateClienteTelefono(),
    validateClienteCorreo(),
    validateClienteAsesor(),
  ]

  return results.every(Boolean)
}

watch(
  () => clienteInlineForm.nombre,
  (value) => {
    const limpio = (value || '').replace(/[^A-Za-zÁÉÍÓÚáéíóúÑñÜü\s]/g, '')
    if (limpio !== value) clienteInlineForm.nombre = limpio
    validateClienteNombre()
  }
)

watch(
  () => clienteInlineForm.id_tipo_cliente,
  () => validateClienteTipoCliente()
)

watch(
  () => clienteInlineForm.id_tipo_documento,
  () => validateClienteTipoDocumento()
)

watch(
  () => clienteInlineForm.documento,
  (value) => {
    const limpio = (value || '').replace(/\D/g, '')
    if (limpio !== value) clienteInlineForm.documento = limpio
    validateClienteDocumento()
  }
)

watch(
  () => clienteInlineForm.direccion,
  () => validateClienteDireccion()
)

watch(
  () => clienteInlineForm.telefono,
  (value) => {
    const limpio = (value || '').replace(/\D/g, '')
    if (limpio !== value) clienteInlineForm.telefono = limpio
    validateClienteTelefono()
  }
)

watch(
  () => clienteInlineForm.correo,
  (value) => {
    const limpio = (value || '').trimStart()
    if (limpio !== value) clienteInlineForm.correo = limpio
    validateClienteCorreo()
  }
)

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
  clienteInlineForm.errors = {}

  if (!validateClienteInlineForm()) return

  clienteInlineForm.processing = true

  router.post(
    '/clientes',
    {
      nombre: clienteInlineForm.nombre.trim(),
      id_tipo_cliente: clienteInlineForm.id_tipo_cliente,
      id_tipo_documento: clienteInlineForm.id_tipo_documento,
      documento: clienteInlineForm.documento.trim(),
      direccion: clienteInlineForm.direccion?.trim() || '',
      telefono: clienteInlineForm.telefono?.trim() || '',
      correo: clienteInlineForm.correo?.trim() || '',
      id_empleado_asesor: clienteInlineForm.id_empleado_asesor,
      redirect_to: window.location.pathname + window.location.search,
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

        clienteInlineForm.processing = false
      },
      onFinish: () => {
        clienteInlineForm.processing = false
      },
    }
  )
}

/** ===== Helpers de recálculo ===== */
function recalcularEconomiaVenta() {
  const total = Number(form.valor_base || 0) + Number(precioParqueaderoSeleccionado.value || 0)
  form.valor_total = total

  if (form.tipo_operacion === 'venta' && proyectoSeleccionado.value) {
    const porcentaje = Number(proyectoSeleccionado.value.porcentaje_cuota_inicial_min || 0)
    const raw = total * (porcentaje / 100)
    form.cuota_inicial_raw = raw
    form.cuota_inicial = Math.round(raw)
    form.valor_restante = total - Math.round(raw)
  } else {
    form.cuota_inicial_raw = 0
    form.cuota_inicial = 0
    form.valor_restante = 0
  }
}

/** ===== Validaciones dinámicas ===== */
watch(
  () => form.cuota_inicial_raw,
  (valor) => {
    if (form.tipo_operacion !== 'venta') {
      erroresForm.cuota_inicial = ''
      return
    }
    if (!proyectoSeleccionado.value?.porcentaje_cuota_inicial_min) return

    const min = form.valor_total * (proyectoSeleccionado.value.porcentaje_cuota_inicial_min / 100)
    erroresForm.cuota_inicial =
      valor < min ? `La cuota inicial mínima es ${formatearMoneda(min)}` : ''
  }
)

watch(
  () => form.valor_separacion,
  (valor) => {
    if (form.tipo_operacion !== 'separacion') {
      erroresForm.valor_separacion = ''
      return
    }
    const min = proyectoSeleccionado.value?.valor_min_separacion ?? 0
    erroresForm.valor_separacion =
      valor < min ? `El valor mínimo de separación es ${formatearMoneda(min)}` : ''
  }
)

watch(
  () => form.fecha_limite_separacion,
  (fecha) => {
    if (form.tipo_operacion !== 'separacion') {
      erroresForm.fecha_limite_separacion = ''
      return
    }

    const maxDias = proyectoSeleccionado.value?.plazo_max_separacion_dias ?? 0
    const base = form.fecha_venta ? new Date(form.fecha_venta + 'T00:00:00') : new Date()
    const fechaLimite = new Date(base)
    fechaLimite.setDate(base.getDate() + Number(maxDias))

    if (!fecha) {
      erroresForm.fecha_limite_separacion = ''
      return
    }

    const f = new Date(fecha + 'T00:00:00')

    erroresForm.fecha_limite_separacion =
      f > fechaLimite
        ? `La fecha máxima permitida es ${fechaLimite.toISOString().slice(0, 10)}`
        : ''
  }
)

const fechaMinimaSeparacion = computed(
  () => form.fecha_venta || new Date().toISOString().split('T')[0]
)

const fechaMaximaSeparacion = computed(() => {
  if (!proyectoSeleccionado.value || !form.fecha_venta) return null

  const dias = Number(proyectoSeleccionado.value.plazo_max_separacion_dias || 0)
  const fecha = new Date(form.fecha_venta + 'T00:00:00')
  fecha.setDate(fecha.getDate() + dias)

  return fecha.toISOString().split('T')[0]
})

const estadoVendidoId = props.estadosInmueble.find(
  (e) => e.nombre.toLowerCase() === 'vendido'
)?.id_estado_inmueble
const estadoSeparadoId = props.estadosInmueble.find(
  (e) => e.nombre.toLowerCase() === 'separado'
)?.id_estado_inmueble

watch(
  () => form.tipo_operacion,
  (tipo) => {
    if (tipo === 'venta') form.id_estado_inmueble = estadoVendidoId
    else if (tipo === 'separacion') form.id_estado_inmueble = estadoSeparadoId
    else form.id_estado_inmueble = null

    recalcularEconomiaVenta()
  }
)

onMounted(() => {
  if (form.tipo_operacion === 'venta') form.id_estado_inmueble = estadoVendidoId
  else if (form.tipo_operacion === 'separacion') form.id_estado_inmueble = estadoSeparadoId
})

watch(
  () => form.id_proyecto,
  (nuevoProyecto) => {
    form.inmueble_id = ''
    form.inmueble_uid = ''
    form.inmueble_tipo = ''
    form.valor_base = 0
    form.valor_total = 0
    form.id_parqueadero = ''
    form.cuota_inicial_raw = 0
    form.cuota_inicial = 0
    form.valor_restante = 0

    if (!nuevoProyecto) {
      inmueblesDisponibles.value = []
      parqueaderosDisponibles.value = []
      return
    }

    const proyectoId = parseInt(nuevoProyecto)

    const aps = props.apartamentos.filter(
      (a) => a.torre?.id_proyecto === proyectoId && a.id_estado_inmueble === 1
    )
    const locs = props.locales.filter(
      (l) => l.torre?.id_proyecto === proyectoId && l.id_estado_inmueble === 1
    )

    inmueblesDisponibles.value = [
      ...aps.map((a) => ({
        tipo: 'apartamento',
        id: a.id_apartamento,
        uid: `apartamento-${a.id_apartamento}`,
        label: `Apto ${a.numero}`,
        valor: parseFloat(a.valor_final || a.valor_total || 0),
      })),
      ...locs.map((l) => ({
        tipo: 'local',
        id: l.id_local,
        uid: `local-${l.id_local}`,
        label: `Local ${l.numero}`,
        valor: parseFloat(l.valor_total || 0),
      })),
    ]

    parqueaderosDisponibles.value = props.parqueaderos
      .filter((p) => Number(p.id_proyecto) === proyectoId)
      .map((p) => ({
        ...p,
        precio: Number(p.precio || 0),
      }))
  }
)

onMounted(() => {
  if (props.inmueblePrecargado) {
    const inmueble = props.inmueblePrecargado
    const esApartamento = Object.prototype.hasOwnProperty.call(inmueble, 'id_apartamento')
    const proyectoId = inmueble.torre?.id_proyecto

    if (proyectoId) {
      form.id_proyecto = proyectoId
      form.inmueble_tipo = esApartamento ? 'apartamento' : 'local'
      form.inmueble_id = esApartamento ? inmueble.id_apartamento : inmueble.id_local
      form.inmueble_uid = `${form.inmueble_tipo}-${form.inmueble_id}`
      form.valor_base = parseFloat(inmueble.valor_final || inmueble.valor_total || 0)
      recalcularEconomiaVenta()
    }
  }
})

watch(
  () => form.inmueble_uid,
  (newUid) => {
    if (!newUid) {
      form.inmueble_id = ''
      form.inmueble_tipo = ''
      form.valor_base = 0
      form.valor_total = 0
      return
    }

    const inm = inmueblesDisponibles.value.find((i) => i.uid === newUid)
    if (!inm || !proyectoSeleccionado.value) return

    form.inmueble_tipo = inm.tipo
    form.inmueble_id = inm.id
    form.valor_base = Number(inm.valor)

    if (form.inmueble_tipo !== 'apartamento') {
      form.id_parqueadero = ''
    }

    recalcularEconomiaVenta()
  }
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

function onCuotaInicialInput(value) {
  const clean = value.replace(/[^\d]/g, '')
  const raw = clean ? Number(clean) : 0

  form.cuota_inicial_raw = raw
  form.cuota_inicial = raw

  if (form.tipo_operacion === 'venta') {
    const total = Number(form.valor_total) || 0
    form.valor_restante = total - raw
  }
}

watch(
  () => form.cuota_inicial_raw,
  () => {
    if (form.tipo_operacion === 'venta') {
      const total = Number(form.valor_total) || 0
      const inicial = Number(form.cuota_inicial_raw) || 0
      form.valor_restante = total - inicial
    }
  }
)

const camposCompletos = computed(() =>
  Boolean(
    form.tipo_operacion &&
      form.documento_cliente &&
      form.fecha_venta &&
      form.id_proyecto &&
      form.inmueble_id &&
      form.id_forma_pago &&
      form.id_estado_inmueble &&
      (form.tipo_operacion === 'venta' ? form.plazo_cuota_inicial_meses : true) &&
      (form.tipo_operacion === 'separacion' ? form.fecha_limite_separacion : true)
  )
)

function calcularMesesEntreFechas(inicioStr, fechaRefStr) {
  if (!inicioStr || !fechaRefStr) return 0

  const inicio = new Date(inicioStr + 'T00:00:00')
  const ref = new Date(fechaRefStr + 'T00:00:00')

  let meses = (ref.getFullYear() - inicio.getFullYear()) * 12 + (ref.getMonth() - inicio.getMonth())

  if (ref.getDate() < inicio.getDate()) {
    meses--
  }

  return Math.max(meses, 0)
}

function actualizarPlazosDisponibles() {
  const p = proyectoSeleccionado.value

  if (!p || !p.fecha_inicio || !p.plazo_cuota_inicial_meses || !form.fecha_venta) {
    plazosDisponibles.value = []
    form.plazo_cuota_inicial_meses = ''
    return
  }

  const plazoTotal = Number(p.plazo_cuota_inicial_meses || 0)
  const mesesTranscurridos = calcularMesesEntreFechas(p.fecha_inicio, form.fecha_venta)
  const plazosRestantes = Math.max(plazoTotal - mesesTranscurridos, 0)

  plazosDisponibles.value =
    plazosRestantes > 0 ? Array.from({ length: plazosRestantes }, (_, i) => i + 1) : []

  if (
    form.plazo_cuota_inicial_meses &&
    !plazosDisponibles.value.includes(Number(form.plazo_cuota_inicial_meses))
  ) {
    form.plazo_cuota_inicial_meses = ''
    form.frecuencia_cuota_inicial_meses = ''
  }
}

watch(
  [() => form.id_proyecto, () => form.fecha_venta],
  () => {
    actualizarPlazosDisponibles()
  },
  { immediate: true }
)

function precargarValorSeparacion() {
  if (form.tipo_operacion === 'separacion' && proyectoSeleccionado.value) {
    const valorMinimo = proyectoSeleccionado.value.valor_min_separacion || 0
    if (!form.valor_separacion || form.valor_separacion === 0) form.valor_separacion = valorMinimo
  }
}

watch(
  () => form.tipo_operacion,
  (nuevoTipo) => {
    if (nuevoTipo === 'separacion') {
      if (proyectoSeleccionado.value) precargarValorSeparacion()
      form.id_estado_inmueble = estadoSeparadoId
    } else if (nuevoTipo === 'venta') {
      form.valor_separacion = 0
      form.id_estado_inmueble = estadoVendidoId
    }
  },
  { immediate: true }
)

watch(proyectoSeleccionado, (nuevoProyecto) => {
  if (nuevoProyecto && form.tipo_operacion === 'separacion') {
    nextTick(() => precargarValorSeparacion())
  }
})

function usarValorMinimo() {
  if (proyectoSeleccionado.value) {
    form.valor_separacion = proyectoSeleccionado.value.valor_min_separacion || 0
  }
}

function submit() {
  form.cuota_inicial = form.cuota_inicial_raw

  if (form.inmueble_tipo !== 'apartamento') {
    form.id_parqueadero = ''
  }

  form.post('/ventas', {
    preserveScroll: true,
    onError: (errors) => {
      console.log('Errores de validación:', errors)
    },
    onSuccess: () => {
      console.log('Venta guardada correctamente')
    },
  })
}
</script>

<template>
  <VentasLayout :empleado="empleado">
    <Head title="Registrar Operación" />

    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm overflow-hidden mb-6">
      <div class="bg-gradient-to-r from-[#FFEA00] to-[#FFF15C] px-6 py-6">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
          <div class="min-w-0">
            <div class="flex items-center gap-2 text-[#474100]">
              <ClipboardDocumentCheckIcon class="w-6 h-6" />
              <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
                Registrar operación
              </h1>
            </div>
            <p class="mt-1 text-sm text-[#474100]/80">
              Completa los datos y confirma para generar una venta o separación.
            </p>
          </div>

          <div class="flex items-center gap-2">
            <Link
              href="/ventas"
              class="inline-flex items-center gap-2 rounded-xl border border-[#474100]/20 bg-white/70 px-4 py-2.5 text-sm font-semibold text-[#474100] hover:bg-white transition"
            >
              <ArrowLeftIcon class="w-5 h-5" />
              Volver
            </Link>

            <div class="rounded-xl bg-white/70 border border-[#474100]/20 px-4 py-2.5">
              <p class="text-xs font-semibold text-[#474100]/70">Progreso</p>
              <div class="flex items-center gap-2">
                <div class="w-40 h-2 rounded-full bg-[#474100]/10 overflow-hidden">
                  <div
                    class="h-full bg-[#1e3a5f] rounded-full transition-all"
                    :style="{ width: `${steps.pct}%` }"
                  />
                </div>
                <p class="text-sm font-extrabold text-[#474100]">
                  {{ steps.done }}/{{ steps.total }}
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div
        v-if="Object.keys(form.errors).length"
        class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"
      >
        <p class="font-semibold mb-1">No se pudo guardar la operación.</p>
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
                  class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-semibold"
                  :class="
                    form.tipo_operacion === 'venta'
                      ? 'bg-emerald-50 text-emerald-800 border-emerald-200'
                      : form.tipo_operacion === 'separacion'
                        ? 'bg-blue-50 text-blue-800 border-blue-200'
                        : 'bg-gray-50 text-gray-700 border-gray-200'
                  "
                >
                  {{
                    form.tipo_operacion
                      ? form.tipo_operacion === 'venta'
                        ? 'Venta'
                        : 'Separación'
                      : 'Sin tipo'
                  }}
                </span>
              </div>

              <div class="p-5 grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                  <label :class="labelClass()">Tipo de operación *</label>
                  <select v-model="form.tipo_operacion" :class="inputClass(false, false)">
                    <option value="">Seleccione...</option>
                    <option value="venta">Venta</option>
                    <option value="separacion">Separación</option>
                  </select>
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

                <div>
                  <label :class="labelClass()">Fecha de operación *</label>
                  <input
                    type="date"
                    v-model="form.fecha_venta"
                    :max="new Date().toISOString().slice(0, 10)"
                    :class="inputClass(false, false)"
                  />
                  <p v-if="form.errors.fecha_venta" :class="errorClass()">
                    {{ form.errors.fecha_venta }}
                  </p>
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
                  <label :class="labelClass()">Proyecto *</label>
                  <select v-model="form.id_proyecto" :class="inputClass(false, false)">
                    <option value="">Seleccione...</option>
                    <option v-for="p in proyectos" :key="p.id_proyecto" :value="p.id_proyecto">
                      {{ p.nombre }}
                    </option>
                  </select>
                </div>

                <div>
                  <label :class="labelClass()">Inmueble disponible *</label>
                  <select
                    v-model="form.inmueble_uid"
                    :disabled="!inmueblesDisponibles.length"
                    :class="inputClass(false, !inmueblesDisponibles.length)"
                  >
                    <option value="">Seleccione...</option>
                    <option v-for="i in inmueblesDisponibles" :key="i.uid" :value="i.uid">
                      {{ i.label }}
                    </option>
                  </select>

                  <div
                    v-if="form.id_proyecto && !inmueblesDisponibles.length"
                    class="mt-2 inline-flex items-center gap-2 rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-xs font-semibold text-amber-700"
                  >
                    <ExclamationTriangleIcon class="w-4 h-4" />
                    No hay inmuebles disponibles en este proyecto.
                  </div>
                </div>

                <div>
                  <label :class="labelClass()">Parqueadero adicional (opcional)</label>
                  <select
                    v-model="form.id_parqueadero"
                    :disabled="!form.id_proyecto || form.inmueble_tipo !== 'apartamento'"
                    :class="
                      inputClass(false, !form.id_proyecto || form.inmueble_tipo !== 'apartamento')
                    "
                  >
                    <option value="">Sin parqueadero adicional</option>
                    <option
                      v-for="p in parqueaderosDisponibles"
                      :key="p.id_parqueadero"
                      :value="p.id_parqueadero"
                    >
                      {{ p.numero }} · {{ p.tipo }} · {{ formatearMoneda(p.precio) }}
                    </option>
                  </select>
                </div>

                <div>
                  <label :class="labelClass()">Forma de pago *</label>
                  <select v-model="form.id_forma_pago" :class="inputClass(false, false)">
                    <option value="">Seleccione...</option>
                    <option
                      v-for="fp in formasPago"
                      :key="fp.id_forma_pago"
                      :value="fp.id_forma_pago"
                    >
                      {{ fp.forma_pago }}
                    </option>
                  </select>
                </div>

                <div>
                  <label :class="labelClass()">Estado del inmueble *</label>
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
                <template v-if="form.tipo_operacion === 'venta'">
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
                      :class="inputClass(Boolean(erroresForm.cuota_inicial), false)"
                    />
                    <p v-if="erroresForm.cuota_inicial" :class="errorClass()">
                      {{ erroresForm.cuota_inicial }}
                    </p>
                  </div>

                  <div>
                    <label :class="labelClass()">Plazo cuota inicial (meses) *</label>
                    <select
                      v-model="form.plazo_cuota_inicial_meses"
                      :class="inputClass(false, false)"
                    >
                      <option value="">Seleccione...</option>
                      <option v-for="p in plazosDisponibles" :key="p" :value="p">
                        {{ p }} mes{{ p === 1 ? '' : 'es' }}
                      </option>
                    </select>
                    <p v-if="form.errors.plazo_cuota_inicial_meses" :class="errorClass()">
                      {{ form.errors.plazo_cuota_inicial_meses }}
                    </p>
                  </div>

                  <div>
                    <label :class="labelClass()">Frecuencia de pago cuota inicial</label>
                    <select
                      v-model="form.frecuencia_cuota_inicial_meses"
                      :disabled="!form.plazo_cuota_inicial_meses || opcionesFrecuencia.length === 0"
                      :class="
                        inputClass(
                          false,
                          !form.plazo_cuota_inicial_meses || opcionesFrecuencia.length === 0
                        )
                      "
                    >
                      <option value="">Seleccione...</option>
                      <option v-for="f in opcionesFrecuencia" :key="f.valor" :value="f.valor">
                        {{ f.etiqueta }}
                      </option>
                    </select>
                    <p :class="hintClass()">
                      {{
                        opcionesFrecuencia.length === 0 && form.plazo_cuota_inicial_meses
                          ? 'No hay frecuencias que dividan exactamente este plazo.'
                          : 'Solo se muestran frecuencias que dividen exactamente el plazo.'
                      }}
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
                </template>

                <template v-else-if="form.tipo_operacion === 'separacion'">
                  <div>
                    <div class="flex items-center justify-between mb-1">
                      <label :class="labelClass()">Valor de separación</label>
                      <button
                        type="button"
                        @click="usarValorMinimo"
                        class="text-xs font-semibold text-[#1e3a5f] hover:underline"
                      >
                        Usar mínimo
                      </button>
                    </div>

                    <input
                      type="text"
                      :value="formatearMoneda(form.valor_separacion)"
                      @input="(e) => (form.valor_separacion = parseMoneda(e.target.value))"
                      :class="inputClass(Boolean(erroresForm.valor_separacion), false)"
                      placeholder="Ingresa el valor de separación"
                    />

                    <p v-if="erroresForm.valor_separacion" :class="errorClass()">
                      {{ erroresForm.valor_separacion }}
                    </p>

                    <p v-if="proyectoSeleccionado?.valor_min_separacion" :class="hintClass()">
                      Mínimo: {{ formatearMoneda(proyectoSeleccionado.valor_min_separacion) }}
                    </p>
                  </div>

                  <div>
                    <label :class="labelClass()">Fecha límite separación *</label>
                    <input
                      type="date"
                      v-model="form.fecha_limite_separacion"
                      :min="fechaMinimaSeparacion"
                      :max="fechaMaximaSeparacion"
                      :class="inputClass(Boolean(erroresForm.fecha_limite_separacion), false)"
                    />

                    <p v-if="erroresForm.fecha_limite_separacion" :class="errorClass()">
                      {{ erroresForm.fecha_limite_separacion }}
                    </p>

                    <div class="mt-2 inline-flex items-center gap-2 text-xs text-gray-600">
                      <CalendarDaysIcon class="w-4 h-4 text-gray-400" />
                      <span>Rango: {{ fechaMinimaSeparacion }} → {{ fechaMaximaSeparacion }}</span>
                    </div>
                  </div>
                </template>

                <template v-else>
                  <div class="md:col-span-2">
                    <div
                      class="rounded-2xl border border-dashed border-gray-300 bg-gray-50 p-4 text-sm text-gray-700"
                    >
                      Selecciona primero el
                      <span class="font-semibold">tipo de operación</span> para ver las condiciones
                      económicas.
                    </div>
                  </div>
                </template>
              </div>
            </div>

            <div class="flex items-center justify-end gap-3">
              <button
                type="submit"
                :disabled="
                  !camposCompletos ||
                  form.processing ||
                  Boolean(erroresForm.cuota_inicial) ||
                  Boolean(erroresForm.valor_separacion) ||
                  Boolean(erroresForm.fecha_limite_separacion)
                "
                class="inline-flex items-center justify-center gap-2 rounded-xl px-5 py-3 text-sm font-extrabold transition focus:outline-none focus:ring-2 focus:ring-[#1e3a5f] focus:ring-offset-2"
                :class="[
                  camposCompletos &&
                  !form.processing &&
                  !erroresForm.cuota_inicial &&
                  !erroresForm.valor_separacion &&
                  !erroresForm.fecha_limite_separacion
                    ? 'bg-[#1e3a5f] text-white hover:bg-[#2c5282]'
                    : 'bg-gray-200 text-gray-500 cursor-not-allowed',
                ]"
              >
                <span
                  v-if="form.processing"
                  class="animate-spin w-4 h-4 border-2 border-white border-t-transparent rounded-full"
                />
                <CheckSolid v-else class="w-5 h-5" />
                {{ form.processing ? 'Guardando...' : 'Guardar operación' }}
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
                  <span class="font-semibold text-gray-900">
                    {{
                      form.tipo_operacion
                        ? form.tipo_operacion === 'venta'
                          ? 'Venta'
                          : 'Separación'
                        : '—'
                    }}
                  </span>
                </div>

                <div class="flex items-center justify-between">
                  <span class="text-gray-600">Proyecto</span>
                  <span class="font-semibold text-gray-900 text-right">
                    {{ proyectoSeleccionado?.nombre || '—' }}
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

                <div class="pt-3 border-t border-gray-200">
                  <div class="flex items-center justify-between">
                    <span class="text-gray-600">Valor Apartamento</span>
                    <span class="font-extrabold text-[#1e3a5f]">
                      {{
                        form.tipo_operacion === 'venta'
                          ? formatearMoneda(form.valor_base)
                          : formatearMoneda(form.valor_separacion)
                      }}
                    </span>
                  </div>
                  <div class="flex items-center justify-between">
                    <span class="text-gray-600">Parqueadero</span>
                    <span class="font-extrabold text-[#1e3a5f]">
                      {{
                        resumenParqueadero ? `${formatearMoneda(resumenParqueadero.precio)}` : '—'
                      }}
                    </span>
                  </div>
                  <div class="flex items-center justify-between">
                    <span class="text-gray-600">Valor Total</span>
                    <span class="font-extrabold text-[#1e3a5f]">
                      {{
                        form.tipo_operacion === 'venta'
                          ? formatearMoneda(form.valor_total)
                          : formatearMoneda(form.valor_separacion)
                      }}
                    </span>
                  </div>

                  <div v-if="form.tipo_operacion === 'venta'" class="mt-2">
                    <div class="flex items-center justify-between text-xs text-gray-600">
                      <span>Cuota inicial</span>
                      <span class="font-semibold text-gray-900">{{
                        formatearMoneda(form.cuota_inicial_raw)
                      }}</span>
                    </div>
                    <div class="flex items-center justify-between text-xs text-gray-600 mt-1">
                      <span>Restante</span>
                      <span class="font-semibold text-gray-900">{{
                        formatearMoneda(form.valor_restante)
                      }}</span>
                    </div>
                  </div>
                </div>

                <div
                  v-if="form.tipo_operacion === 'separacion'"
                  class="pt-3 border-t border-gray-200"
                >
                  <div class="flex items-center justify-between text-xs text-gray-600">
                    <span>Fecha límite</span>
                    <span class="font-semibold text-gray-900">{{
                      form.fecha_limite_separacion || '—'
                    }}</span>
                  </div>
                </div>
              </div>

              <div
                class="mt-4 rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-xs text-gray-700"
              >
                Verifica el resumen antes de guardar. Los campos obligatorios están marcados con
                <span class="font-semibold">*</span>.
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
