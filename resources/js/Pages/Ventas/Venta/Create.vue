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
  empleadoProp: Object,
  inmueblePrecargado: Object,
  plazos_disponibles: Array,
  tiposCliente: Array,
  tiposDocumento: Array,
})

const plazosDisponibles = ref([])
const inmueblesDisponibles = ref([])

const form = useForm({
  tipo_operacion: '', // 'venta' o 'separacion'
  id_empleado: empleado.value?.id_empleado || null,
  documento_cliente: '',
  fecha_venta: new Date().toISOString().slice(0, 10),
  fecha_vencimiento: null,
  id_proyecto: '',
  inmueble_tipo: '',
  inmueble_id: '',
  id_forma_pago: '',
  id_estado_inmueble: '',
  valor_base: 0,
  iva: 0,
  valor_total: 0,
  cuota_inicial: 0,
  cuota_inicial_raw: 0,
  valor_restante: 0,
  descripcion: '',
  valor_separacion: 0,
  fecha_limite_separacion: '',
  plazo_cuota_inicial_meses: '',
  frecuencia_cuota_inicial_meses: 1,
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

/** ====== UI Helpers ====== */
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
  const inm = inmueblesDisponibles.value.find((i) => i.id === Number(form.inmueble_id))
  if (!inm) return null
  return inm
})

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
      redirect_to: window.location.pathname + window.location.search,
    },
    {
      preserveScroll: true,
      onError: (errors) => {
        clienteInlineForm.errors = errors
        clienteInlineForm.processing = false
      },
      onSuccess: () => {
        closeClienteModal()
        resetClienteInlineForm()
        const newDoc = page.props.flash?.new_cliente_documento
        if (newDoc) form.documento_cliente = newDoc
        clienteInlineForm.processing = false
      },
      onFinish: () => {
        clienteInlineForm.processing = false
      },
    }
  )
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
    const hoy = new Date()
    const fechaLimite = new Date(hoy)
    fechaLimite.setDate(hoy.getDate() + Number(maxDias))

    if (!fecha) {
      erroresForm.fecha_limite_separacion = ''
      return
    }

    const f = new Date(fecha)
    erroresForm.fecha_limite_separacion =
      f > fechaLimite
        ? `La fecha máxima permitida es ${fechaLimite.toISOString().slice(0, 10)}`
        : ''
  }
)

/** ===== Fechas separacion min/max ===== */
const fechaMinimaSeparacion = computed(() => new Date().toISOString().split('T')[0])

const fechaMaximaSeparacion = computed(() => {
  if (!proyectoSeleccionado.value) return null
  const dias = Number(proyectoSeleccionado.value.plazo_max_separacion_dias || 0)
  const fecha = new Date()
  fecha.setDate(fecha.getDate() + dias)
  return fecha.toISOString().split('T')[0]
})

/** ===== Estados por nombre ===== */
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
  }
)

onMounted(() => {
  if (form.tipo_operacion === 'venta') form.id_estado_inmueble = estadoVendidoId
  else if (form.tipo_operacion === 'separacion') form.id_estado_inmueble = estadoSeparadoId
})

/** ===== Proyecto -> inmuebles disponibles ===== */
watch(
  () => form.id_proyecto,
  (nuevoProyecto) => {
    form.inmueble_id = ''
    form.valor_total = 0
    form.cuota_inicial_raw = 0
    form.cuota_inicial = 0
    form.valor_restante = 0

    if (!nuevoProyecto) {
      inmueblesDisponibles.value = []
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
        label: `Apto ${a.numero}`,
        valor: parseFloat(a.valor_final || a.valor_total || 0),
      })),
      ...locs.map((l) => ({
        tipo: 'local',
        id: l.id_local,
        label: `Local ${l.numero}`,
        valor: parseFloat(l.valor_total || 0),
      })),
    ]
  }
)

/** ===== Pre-carga desde catálogo ===== */
onMounted(() => {
  if (props.inmueblePrecargado) {
    const inmueble = props.inmueblePrecargado
    const esApartamento = Object.prototype.hasOwnProperty.call(inmueble, 'id_apartamento')
    const proyectoId = inmueble.torre?.id_proyecto

    if (proyectoId) {
      form.id_proyecto = proyectoId
      form.inmueble_tipo = esApartamento ? 'apartamento' : 'local'
      form.inmueble_id = esApartamento ? inmueble.id_apartamento : inmueble.id_local
      form.valor_total = parseFloat(inmueble.valor_final || inmueble.valor_total || 0)

      if (proyectoSeleccionado.value?.porcentaje_cuota_inicial_min) {
        const porcentaje = proyectoSeleccionado.value.porcentaje_cuota_inicial_min
        form.cuota_inicial = form.valor_total * (porcentaje / 100)
        form.valor_restante = form.valor_total - form.cuota_inicial
      }
    }
  }
})

/** ===== Inmueble -> setear tipo, valor_total, cuota/ restante ===== */
watch(
  () => form.inmueble_id,
  (newId) => {
    if (!newId) return
    const inm = inmueblesDisponibles.value.find((i) => i.id === Number(newId))
    if (!inm || !proyectoSeleccionado.value) return

    form.inmueble_tipo = inm.tipo
    form.valor_total = Number(inm.valor)

    if (form.tipo_operacion === 'venta') {
      const porcentaje = Number(proyectoSeleccionado.value.porcentaje_cuota_inicial_min || 0)
      const raw = form.valor_total * (porcentaje / 100)
      form.cuota_inicial_raw = raw
      form.cuota_inicial = Math.round(raw)
      form.valor_restante = form.valor_total - Math.round(raw)
    } else {
      form.cuota_inicial_raw = 0
      form.cuota_inicial = 0
      form.valor_restante = 0
    }
  }
)

/** ===== Cuota inicial input ===== */
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

/** ===== Tipo operacion -> recalcular ===== */
watch(
  () => form.tipo_operacion,
  (tipo) => {
    if (tipo === 'venta') {
      if (proyectoSeleccionado.value) {
        const porcentaje = Number(proyectoSeleccionado.value.porcentaje_cuota_inicial_min || 0)
        const raw = form.valor_total * (porcentaje / 100)
        form.cuota_inicial_raw = raw
        form.cuota_inicial = raw
        form.valor_restante = form.valor_total - raw
      }
    } else {
      form.cuota_inicial_raw = 0
      form.cuota_inicial = 0
      form.valor_restante = 0
    }
  }
)

/** ===== Restante ===== */
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

/** ===== Campos completos ===== */
const camposCompletos = computed(() =>
  Boolean(
    form.tipo_operacion &&
      form.documento_cliente &&
      form.id_proyecto &&
      form.inmueble_id &&
      form.id_forma_pago &&
      form.id_estado_inmueble &&
      (form.tipo_operacion === 'venta' ? form.plazo_cuota_inicial_meses : true) &&
      (form.tipo_operacion === 'separacion' ? form.fecha_limite_separacion : true)
  )
)

/** ===== Plazos disponibles ===== */
watch(
  () => form.id_proyecto,
  () => {
    const p = proyectoSeleccionado.value
    if (p) {
      const inicio = p.fecha_inicio
      const plazoTotal = p.plazo_cuota_inicial_meses
      if (inicio && plazoTotal > 0) {
        const start = new Date(inicio)
        const now = new Date()
        const diffMonths =
          (now.getFullYear() - start.getFullYear()) * 12 + (now.getMonth() - start.getMonth())
        const plazosRestantes = Math.max(plazoTotal - diffMonths, 0)
        plazosDisponibles.value = Array.from({ length: plazosRestantes }, (_, i) => i + 1)
      } else {
        plazosDisponibles.value = []
      }
    } else {
      plazosDisponibles.value = []
    }
  },
  { immediate: true }
)

/** ===== Separacion: precargar valor minimo ===== */
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
  if (proyectoSeleccionado.value)
    form.valor_separacion = proyectoSeleccionado.value.valor_min_separacion || 0
}

/** ===== Submit ===== */
function submit() {
  form.cuota_inicial = form.cuota_inicial_raw
  form.post('/ventas', {
    preserveScroll: true,
  })
}
</script>

<template>
  <VentasLayout :empleado="empleado">
    <Head title="Registrar Operación" />

    <!-- Header tipo hero -->
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

      <!-- Contenido: grid form + resumen -->
      <div class="px-6 py-6 bg-white">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <!-- FORM -->
          <form @submit.prevent="submit" class="lg:col-span-2 space-y-6">
            <!-- Sección 1 -->
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
                <!-- Tipo -->
                <div>
                  <label :class="labelClass()">Tipo de operación *</label>
                  <select v-model="form.tipo_operacion" :class="inputClass(false, false)">
                    <option value="">Seleccione...</option>
                    <option value="venta">Venta</option>
                    <option value="separacion">Separación</option>
                  </select>
                </div>

                <!-- Empleado -->
                <div>
                  <label :class="labelClass()">Empleado</label>
                  <input
                    type="text"
                    :value="empleado?.nombre + ' ' + empleado?.apellido"
                    readonly
                    :class="inputClass(false, true)"
                  />
                  <p :class="hintClass()">Se toma el asesor logueado.</p>
                </div>

                <!-- Cliente -->
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

                  <select v-model="form.documento_cliente" :class="inputClass(false, false)">
                    <option value="">Seleccione...</option>
                    <option v-for="c in clientes" :key="c.documento" :value="c.documento">
                      {{ c.nombre }}
                    </option>
                  </select>
                </div>

                <!-- Proyecto -->
                <div>
                  <label :class="labelClass()">Proyecto *</label>
                  <select v-model="form.id_proyecto" :class="inputClass(false, false)">
                    <option value="">Seleccione...</option>
                    <option v-for="p in proyectos" :key="p.id_proyecto" :value="p.id_proyecto">
                      {{ p.nombre }}
                    </option>
                  </select>
                </div>

                <!-- Inmueble -->
                <div>
                  <label :class="labelClass()">Inmueble disponible *</label>
                  <select
                    v-model="form.inmueble_id"
                    :disabled="!inmueblesDisponibles.length"
                    :class="inputClass(false, !inmueblesDisponibles.length)"
                  >
                    <option value="">Seleccione...</option>
                    <option v-for="i in inmueblesDisponibles" :key="i.id" :value="i.id">
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

                <!-- Forma pago -->
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

                <!-- Estado auto -->
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

                <!-- Descripción -->
                <div class="md:col-span-2">
                  <label :class="labelClass()">Descripción</label>
                  <textarea v-model="form.descripcion" rows="3" :class="inputClass(false, false)" />
                </div>
              </div>
            </div>

            <!-- Sección 2: Venta/Separación -->
            <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
              <div class="px-5 py-4 border-b border-gray-200 flex items-center gap-2">
                <BanknotesIcon class="w-5 h-5 text-[#1e3a5f]" />
                <h2 class="text-sm font-extrabold text-gray-900">Condiciones económicas</h2>
              </div>

              <div class="p-5 grid grid-cols-1 md:grid-cols-2 gap-5">
                <!-- VENTA -->
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
                      @input="onCuotaInicialInput($event.target.value)"
                      :class="inputClass(Boolean(erroresForm.cuota_inicial), false)"
                      placeholder="Ingresa el valor de la cuota inicial"
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
                    <p :class="hintClass()">
                      Se calcula con base en la fecha de inicio del proyecto.
                    </p>
                  </div>

                  <div>
                    <label :class="labelClass()">Frecuencia de pago cuota inicial</label>
                    <select
                      v-model="form.frecuencia_cuota_inicial_meses"
                      :disabled="!form.plazo_cuota_inicial_meses"
                      :class="inputClass(false, !form.plazo_cuota_inicial_meses)"
                    >
                      <option :value="1">Mensual (cada 1 mes)</option>
                      <option :value="2" v-if="Number(form.plazo_cuota_inicial_meses) >= 2">
                        Bimestral
                      </option>
                      <option :value="3" v-if="Number(form.plazo_cuota_inicial_meses) >= 3">
                        Trimestral
                      </option>
                      <option :value="4" v-if="Number(form.plazo_cuota_inicial_meses) >= 4">
                        Cada 4 meses
                      </option>
                      <option :value="6" v-if="Number(form.plazo_cuota_inicial_meses) >= 6">
                        Semestral
                      </option>
                      <option :value="12" v-if="Number(form.plazo_cuota_inicial_meses) >= 12">
                        Anual
                      </option>
                    </select>
                    <p :class="hintClass()">Ej: plazo 12 meses, trimestral => 4 pagos.</p>
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

                <!-- SEPARACIÓN -->
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

                <!-- Sin tipo -->
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

            <!-- CTA -->
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

          <!-- RESUMEN LATERAL -->
          <div class="space-y-4">
            <div class="rounded-2xl border border-gray-200 bg-white shadow-sm p-5 sticky top-6">
              <div class="flex items-center gap-2 mb-3">
                <BuildingOffice2Icon class="w-5 h-5 text-[#1e3a5f]" />
                <h3 class="text-sm font-extrabold text-gray-900">Resumen</h3>
              </div>

              <div class="space-y-3 text-sm">
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
                    <span class="text-gray-600">Valor</span>
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

    <!-- MODAL CLIENTE (scroll + tamaño correcto) -->
    <teleport to="body">
      <div v-if="showClienteModal" class="fixed inset-0 z-50">
        <div class="absolute inset-0 bg-black/40" @click="closeClienteModal" />

        <div class="absolute inset-0 flex items-center justify-center p-4">
          <div
            class="relative w-full max-w-3xl rounded-2xl bg-white shadow-xl border border-gray-200 overflow-hidden"
            role="dialog"
            aria-modal="true"
          >
            <!-- header -->
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

            <!-- body scroll -->
            <div class="max-h-[calc(100vh-11rem)] overflow-y-auto px-5 py-5">
              <ClienteForm
                :form="clienteInlineForm"
                :tipos-cliente="tiposCliente"
                :tipos-documento="tiposDocumento"
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
