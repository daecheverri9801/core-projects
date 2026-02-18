<!-- resources/js/Pages/Ventas/Venta/Convertir.vue -->
<script setup>
import { Head, Link, useForm, usePage, router } from '@inertiajs/vue3'
import { computed, ref, watch, onMounted, reactive, nextTick } from 'vue'
import {
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
  venta: Object, // separación a convertir
  clientes: Array,
  proyectos: Array,
  formasPago: Array,
  estadosInmueble: Array,
  plazos_disponibles: Array,
  tiposCliente: Array,
  tiposDocumento: Array,
})

/** ========= Helpers ========= */
function todayISO() {
  return new Date().toISOString().slice(0, 10)
}

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

/** ========= Contexto separación ========= */
const proyectoSeparacion = computed(() => {
  const id = Number(props.venta?.id_proyecto || 0)
  return props.proyectos?.find((p) => Number(p.id_proyecto) === id) || props.venta?.proyecto || null
})

const inmuebleSeparacion = computed(() => props.venta?.apartamento || props.venta?.local || null)
const inmuebleTipo = computed(() => (props.venta?.apartamento ? 'apartamento' : 'local'))
const inmuebleId = computed(() =>
  props.venta?.apartamento ? props.venta.apartamento.id_apartamento : props.venta?.local?.id_local
)

/** ========= Estados por nombre ========= */
const estadoVendidoId = computed(
  () =>
    props.estadosInmueble?.find((e) => e.nombre?.toLowerCase() === 'vendido')?.id_estado_inmueble ||
    null
)

/** ========= Form =========
 * Reglas solicitadas:
 * - fecha_venta: NO seleccionable; se fija al día del cambio (hoy)
 * - cuota_inicial: se calcula con porcentaje del proyecto (porcentaje_cuota_inicial_min) sobre valor_total
 * - cuota_separacion: es el valor mínimo del proyecto (valor_min_separacion) (para desglose)
 */
const form = useForm({
  // operación fija
  tipo_operacion: 'venta',

  // datos base (se precargan)
  id_empleado: empleado.value?.id_empleado || props.venta?.id_empleado || null,
  documento_cliente: props.venta?.documento_cliente || '',

  // fecha fija hoy (no editable)
  fecha_venta: todayISO(),

  // proyecto + inmueble (bloqueados)
  id_proyecto: String(props.venta?.id_proyecto || ''),
  inmueble_tipo: inmuebleTipo.value,
  inmueble_id: inmuebleId.value ? String(inmuebleId.value) : '',

  // forma pago (editable)
  id_forma_pago: props.venta?.id_forma_pago ? String(props.venta.id_forma_pago) : '',

  // estado (Vendido)
  id_estado_inmueble: estadoVendidoId.value,

  // valores
  valor_total: 0,
  cuota_inicial: 0,
  cuota_inicial_raw: 0,
  valor_restante: 0,

  // cuota separación NO se envía al backend para venta (solo se muestra en UI)
  // valor_separacion: ...
  // fecha_limite_separacion: ...

  // plan cuota inicial
  plazo_cuota_inicial_meses: '',
  frecuencia_cuota_inicial_meses: 1,

  descripcion: props.venta?.descripcion || '',
})

/** ========= Reglas de cálculo ========= */
const porcentajeCuotaInicial = computed(() =>
  Number(proyectoSeparacion.value?.porcentaje_cuota_inicial_min || 0)
)

const cuotaSeparacionProyecto = computed(() =>
  Number(proyectoSeparacion.value?.valor_min_separacion || 0)
)

const valorTotalInmueble = computed(() => {
  const inm = inmuebleSeparacion.value
  if (!inm) return 0

  // Preferir valor_final si existe (apartamento recalculado), si no valor_total
  const v = Number(inm.valor_final ?? inm.valor_total ?? 0)
  return Number.isFinite(v) ? v : 0
})

function recalcularCuotaInicialYRestante() {
  const total = Number(form.valor_total || 0)

  // cuota inicial = total * %min
  const raw = total * (porcentajeCuotaInicial.value / 100)

  form.cuota_inicial_raw = raw
  form.cuota_inicial = Math.round(raw)
  form.valor_restante = total - Math.round(raw)
}

/** ========= Validación UI ========= */
const erroresForm = reactive({
  cuota_inicial: '',
})

watch(
  () => form.cuota_inicial_raw,
  (valor) => {
    const total = Number(form.valor_total || 0)
    const min = total * (porcentajeCuotaInicial.value / 100)
    erroresForm.cuota_inicial =
      valor < min ? `La cuota inicial mínima es ${formatearMoneda(min)}` : ''
  }
)

/** ========= Plazos disponibles ========= */
const plazosDisponibles = ref([])

watch(
  () => form.id_proyecto,
  () => {
    // usa los plazos que llegan del backend (más confiable)
    plazosDisponibles.value = Array.isArray(props.plazos_disponibles)
      ? props.plazos_disponibles
      : []
  },
  { immediate: true }
)

/** ========= Progreso / Resumen ========= */
const proyectoSeleccionado = computed(() => proyectoSeparacion.value)

const estadoNombre = computed(() => {
  const e = props.estadosInmueble?.find((x) => x.id_estado_inmueble === form.id_estado_inmueble)
  return e?.nombre || '—'
})

const resumenInmueble = computed(() => {
  const inm = inmuebleSeparacion.value
  if (!inm) return null
  const label = props.venta?.apartamento ? `Apto ${inm.numero}` : `Local ${inm.numero}`
  return { label, valor: form.valor_total }
})

const camposCompletos = computed(() =>
  Boolean(
    form.documento_cliente &&
      form.id_proyecto &&
      form.inmueble_id &&
      form.id_forma_pago &&
      form.id_estado_inmueble &&
      form.plazo_cuota_inicial_meses
  )
)

/** ========= Inicialización ========= */
onMounted(() => {
  // Fecha de venta fija hoy
  form.fecha_venta = todayISO()

  // Estado vendido
  form.id_estado_inmueble = estadoVendidoId.value

  // Valor total desde inmueble
  form.valor_total = valorTotalInmueble.value

  // Calcular cuota inicial con % del proyecto
  recalcularCuotaInicialYRestante()
})

/** ========= Cuota inicial editable (si quieres permitir modificar, pero respetando mínimo) ========= */
function onCuotaInicialInput(value) {
  const clean = value.replace(/[^\d]/g, '')
  const raw = clean ? Number(clean) : 0

  form.cuota_inicial_raw = raw
  form.cuota_inicial = raw

  const total = Number(form.valor_total) || 0
  form.valor_restante = total - raw
}

/** ========= Submit =========
 * IMPORTANTE:
 * - No enviamos campos de separación (valor_separacion / fecha_limite_separacion)
 * - Enviamos fecha_venta = hoy
 */
function submit() {
  form.cuota_inicial = Math.round(Number(form.cuota_inicial_raw || 0))

  router.put(route('ventas.convertir', props.venta.id_venta), {
    tipo_operacion: 'venta',
    id_empleado: form.id_empleado,
    documento_cliente: form.documento_cliente,
    fecha_venta: form.fecha_venta, // hoy (no editable)
    id_proyecto: form.id_proyecto,
    inmueble_tipo: form.inmueble_tipo,
    inmueble_id: form.inmueble_id,
    id_forma_pago: form.id_forma_pago,
    id_estado_inmueble: form.id_estado_inmueble,
    cuota_inicial: form.cuota_inicial,
    valor_total: form.valor_total,
    valor_restante: form.valor_restante,
    plazo_cuota_inicial_meses: form.plazo_cuota_inicial_meses,
    frecuencia_cuota_inicial_meses: form.frecuencia_cuota_inicial_meses,
    descripcion: form.descripcion,
  })
}

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
</script>

<template>
  <VentasLayout :empleado="empleado">
    <Head title="Convertir Separación a Venta" />

    <!-- Header -->
    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm overflow-hidden mb-6">
      <div class="bg-gradient-to-r from-[#FFEA00] to-[#FFF15C] px-6 py-6">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
          <div class="min-w-0">
            <div class="flex items-center gap-2 text-[#474100]">
              <ClipboardDocumentCheckIcon class="w-6 h-6" />
              <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
                Convertir separación a venta
              </h1>
            </div>
            <p class="mt-1 text-sm text-[#474100]/80">
              La fecha de venta se tomará automáticamente como el día de hoy.
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
              <p class="text-xs font-semibold text-[#474100]/70">Operación</p>
              <p class="text-sm font-extrabold text-[#474100]">Separación #{{ venta.id_venta }}</p>
            </div>
          </div>
        </div>
      </div>

      <div class="px-6 py-6 bg-white">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <!-- FORM -->
          <form @submit.prevent="submit" class="lg:col-span-2 space-y-6">
            <!-- Datos principales -->
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
                <!-- Fecha (NO editable) -->
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

                <!-- Empleado -->
                <div>
                  <label :class="labelClass()">Empleado</label>
                  <input
                    type="text"
                    :value="empleado?.nombre + ' ' + empleado?.apellido"
                    readonly
                    :class="inputClass(false, true)"
                  />
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

                <!-- Proyecto (bloqueado) -->
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

                <!-- Inmueble (bloqueado) -->
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

                <!-- Forma pago -->
                <div>
                  <label :class="labelClass()">Forma de pago *</label>
                  <select v-model="form.id_forma_pago" :class="inputClass(false, false)">
                    <option value="">Seleccione...</option>
                    <option
                      v-for="fp in formasPago"
                      :key="fp.id_forma_pago"
                      :value="String(fp.id_forma_pago)"
                    >
                      {{ fp.forma_pago }}
                    </option>
                  </select>
                </div>

                <!-- Estado auto -->
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

                <!-- Descripción -->
                <div class="md:col-span-2">
                  <label :class="labelClass()">Descripción</label>
                  <textarea v-model="form.descripcion" rows="3" :class="inputClass(false, false)" />
                </div>
              </div>
            </div>

            <!-- Condiciones económicas -->
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
                  <label :class="labelClass()">
                    Cuota inicial ({{ porcentajeCuotaInicial }}%)
                  </label>
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
                  <p :class="hintClass()">
                    Mínimo por proyecto: {{ porcentajeCuotaInicial }}% del valor total.
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
                      {{ p }} mes{{ Number(p) === 1 ? '' : 'es' }}
                    </option>
                  </select>
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

                <!-- Desglose separación (solo informativo) -->
                <div class="md:col-span-2">
                  <div
                    class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-xs text-gray-700"
                  >
                    <div class="flex items-center justify-between">
                      <span>Cuota de separación (proyecto):</span>
                      <span class="font-semibold">{{
                        formatearMoneda(cuotaSeparacionProyecto)
                      }}</span>
                    </div>
                    <div class="flex items-center justify-between mt-2">
                      <span>Separación pagada en operación:</span>
                      <span class="font-semibold">{{
                        formatearMoneda(venta.valor_separacion || 0)
                      }}</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- CTA -->
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

          <!-- RESUMEN -->
          <div class="space-y-4">
            <div class="rounded-2xl border border-gray-200 bg-white shadow-sm p-5 sticky top-6">
              <div class="flex items-center gap-2 mb-3">
                <BuildingOffice2Icon class="w-5 h-5 text-[#1e3a5f]" />
                <h3 class="text-sm font-extrabold text-gray-900">Resumen</h3>
              </div>

              <div class="space-y-3 text-sm">
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
                    <span class="text-gray-600">Valor total</span>
                    <span class="font-extrabold text-[#1e3a5f]">
                      {{ formatearMoneda(form.valor_total) }}
                    </span>
                  </div>

                  <div class="mt-2">
                    <div class="flex items-center justify-between text-xs text-gray-600">
                      <span>Cuota inicial</span>
                      <span class="font-semibold text-gray-900">
                        {{ formatearMoneda(form.cuota_inicial_raw) }}
                      </span>
                    </div>
                    <div class="flex items-center justify-between text-xs text-gray-600 mt-1">
                      <span>Restante</span>
                      <span class="font-semibold text-gray-900">
                        {{ formatearMoneda(form.valor_restante) }}
                      </span>
                    </div>
                  </div>
                </div>
              </div>

              <div
                class="mt-4 rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-xs text-gray-700"
              >
                La separación se convierte a venta. La fecha se toma del día actual y la cuota
                inicial se calcula con el % mínimo del proyecto.
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- MODAL CLIENTE -->
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
