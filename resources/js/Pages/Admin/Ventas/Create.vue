<!-- resources/js/Pages/Ventas/Create.vue -->
<script setup>
import { Head, Link, useForm, usePage, router } from '@inertiajs/vue3'
import { computed, ref, watch, onMounted, reactive, nextTick } from 'vue'
import { CheckCircleIcon, ExclamationTriangleIcon } from '@heroicons/vue/24/solid'
import {
  ArrowLeftIcon,
  BuildingOffice2Icon,
  HomeModernIcon,
  CreditCardIcon,
  UserIcon,
  CalendarDaysIcon,
  CurrencyDollarIcon,
  DocumentTextIcon,
} from '@heroicons/vue/24/outline'

import TopBannerLayout from '@/Components/TopBannerLayout.vue'
import PageHeader from '@/Components/PageHeader.vue'
import AppCard from '@/Components/AppCard.vue'
import FormField from '@/Components/FormField.vue'
import TextInput from '@/Components/TextInput.vue'
import TextArea from '@/Components/TextArea.vue'
import SelectInput from '@/Components/SelectInput.vue'

const page = usePage()
const empleadoAuth = computed(() => page.props?.auth?.empleado ?? page.props?.empleado ?? null)

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
})

const plazosDisponibles = ref([])
const inmueblesDisponibles = ref([])

const form = useForm({
  tipo_operacion: '',
  id_empleado: empleadoAuth.value?.id_empleado || null,
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
  frecuencia_cuota_inicial_meses: '',
})

const proyectoSeleccionado = computed(() =>
  (props.proyectos || []).find((p) => p.id_proyecto === parseInt(form.id_proyecto))
)

const FRECUENCIAS = [
  { valor: 1, etiqueta: 'Mensual (cada 1 mes)' },
  { valor: 2, etiqueta: 'Bimestral (cada 2 meses)' },
  { valor: 3, etiqueta: 'Trimestral (cada 3 meses)' },
  { valor: 4, etiqueta: 'Cada 4 meses' },
  { valor: 6, etiqueta: 'Semestral (cada 6 meses)' },
  { valor: 12, etiqueta: 'Anual (cada 12 meses)' },
]

const opcionesFrecuencia = computed(() => {
  const plazo = Number(form.plazo_cuota_inicial_meses)
  if (!plazo) return []
  return FRECUENCIAS.filter((f) => plazo % f.valor === 0)
})

watch(
  () => form.plazo_cuota_inicial_meses,
  (nuevoPlazo) => {
    if (nuevoPlazo) {
      const plazoNum = Number(nuevoPlazo)
      const valoresValidos = FRECUENCIAS.map((f) => f.valor).filter((v) => plazoNum % v === 0)
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
  return Number(
    String(valorStr)
      .replace(/\./g, '')
      .replace(/[^0-9]/g, '')
  )
}

const estadoNombre = computed(() => {
  const e = (props.estadosInmueble || []).find(
    (x) => x.id_estado_inmueble === form.id_estado_inmueble
  )
  return e?.nombre || '—'
})

// Identificar estados por nombre (robusto)
const estadoVendidoId = (props.estadosInmueble || []).find(
  (e) => String(e.nombre || '').toLowerCase() === 'vendido'
)?.id_estado_inmueble

const estadoSeparadoId = (props.estadosInmueble || []).find(
  (e) => String(e.nombre || '').toLowerCase() === 'separado'
)?.id_estado_inmueble

// Auto-estado según operación
watch(
  () => form.tipo_operacion,
  (tipo) => {
    if (tipo === 'venta') form.id_estado_inmueble = estadoVendidoId ?? null
    else if (tipo === 'separacion') form.id_estado_inmueble = estadoSeparadoId ?? null
    else form.id_estado_inmueble = null
  },
  { immediate: true }
)

// Fecha mín/max separación
const fechaMinimaSeparacion = computed(() => new Date().toISOString().split('T')[0])
const fechaMaximaSeparacion = computed(() => {
  if (!proyectoSeleccionado.value) return null
  const dias = Number(proyectoSeleccionado.value.plazo_max_separacion_dias || 0)
  if (!dias) return null
  const fecha = new Date()
  fecha.setDate(fecha.getDate() + dias)
  return fecha.toISOString().split('T')[0]
})

// Errores locales (UI)
const erroresLocales = ref({
  cuota_inicial: '',
  valor_separacion: '',
  fecha_limite: '',
})

// Validar cuota inicial mínima (venta)
watch(
  () => form.cuota_inicial_raw,
  (valor) => {
    erroresLocales.value.cuota_inicial = ''
    if (form.tipo_operacion !== 'venta') return
    if (!proyectoSeleccionado.value) return

    const pct = Number(proyectoSeleccionado.value.porcentaje_cuota_inicial_min || 0)
    const min = Number(form.valor_total || 0) * (pct / 100)

    if (Number(valor || 0) < min) {
      erroresLocales.value.cuota_inicial = `La cuota inicial mínima es ${formatearMoneda(min)}`
    }
  }
)

// Validar valor separación mínima
watch(
  () => form.valor_separacion,
  (valor) => {
    erroresLocales.value.valor_separacion = ''
    if (form.tipo_operacion !== 'separacion') return
    if (!proyectoSeleccionado.value) return

    const min = Number(proyectoSeleccionado.value.valor_min_separacion || 0)
    if (Number(valor || 0) < min) {
      erroresLocales.value.valor_separacion = `El valor mínimo de separación es ${formatearMoneda(min)}`
    }
  }
)

// Validar fecha límite separación
watch(
  () => form.fecha_limite_separacion,
  (val) => {
    erroresLocales.value.fecha_limite = ''
    if (form.tipo_operacion !== 'separacion') return
    if (!proyectoSeleccionado.value) return
    if (!val) return

    const min = fechaMinimaSeparacion.value
    const max = fechaMaximaSeparacion.value
    if (!max) return

    if (val < min || val > max) {
      erroresLocales.value.fecha_limite = `Debe estar entre ${min} y ${max}`
    }
  }
)

// Inmuebles disponibles según proyecto (solo disponibles id_estado_inmueble === 1)
watch(
  () => form.id_proyecto,
  (nuevoProyecto) => {
    form.inmueble_id = ''
    form.valor_total = 0
    form.cuota_inicial_raw = 0
    form.cuota_inicial = 0
    form.valor_restante = 0
    plazosDisponibles.value = []

    if (!nuevoProyecto) {
      inmueblesDisponibles.value = []
      return
    }

    const proyectoId = parseInt(nuevoProyecto)
    const aps = (props.apartamentos || []).filter(
      (a) => a.torre?.id_proyecto === proyectoId && Number(a.id_estado_inmueble) === 1
    )
    const locs = (props.locales || []).filter(
      (l) => l.torre?.id_proyecto === proyectoId && Number(l.id_estado_inmueble) === 1
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

    // plazos disponibles (según tu lógica original)
    const p = proyectoSeleccionado.value
    if (p?.fecha_inicio && Number(p.plazo_cuota_inicial_meses || 0) > 0) {
      const start = new Date(p.fecha_inicio)
      const now = new Date()
      const diffMonths =
        (now.getFullYear() - start.getFullYear()) * 12 + (now.getMonth() - start.getMonth())
      const plazosRestantes = Math.max(Number(p.plazo_cuota_inicial_meses) - diffMonths, 0)
      plazosDisponibles.value = Array.from({ length: plazosRestantes }, (_, i) => i + 1)
    }
  },
  { immediate: true }
)

// Cuando cambia inmueble, setear tipo y valor_total + cuota inicial mínima
watch(
  () => form.inmueble_id,
  (newId) => {
    if (!newId) return
    const inm = inmueblesDisponibles.value.find((i) => i.id === Number(newId))
    if (!inm || !proyectoSeleccionado.value) return

    form.inmueble_tipo = inm.tipo
    form.valor_total = Number(inm.valor || 0)

    if (form.tipo_operacion === 'venta') {
      const pct = Number(proyectoSeleccionado.value.porcentaje_cuota_inicial_min || 0)
      const raw = form.valor_total * (pct / 100)

      form.cuota_inicial_raw = Math.round(raw)
      form.cuota_inicial = Math.round(raw)
      form.valor_restante = Number(form.valor_total || 0) - Math.round(raw)
    } else {
      form.cuota_inicial_raw = 0
      form.cuota_inicial = 0
      form.valor_restante = 0
    }
  }
)

// Input cuota inicial
function onCuotaInicialInput(value) {
  const raw = parseMoneda(value)
  form.cuota_inicial_raw = raw
  form.cuota_inicial = raw
  if (form.tipo_operacion === 'venta') {
    form.valor_restante = Number(form.valor_total || 0) - raw
  }
}

// Al cambiar tipo, precargar separación mínima y limpiar venta/separación
function precargarValorSeparacion() {
  if (form.tipo_operacion === 'separacion' && proyectoSeleccionado.value) {
    const min = Number(proyectoSeleccionado.value.valor_min_separacion || 0)
    if (!form.valor_separacion || Number(form.valor_separacion) === 0) form.valor_separacion = min
  }
}

watch(
  () => form.tipo_operacion,
  (nuevoTipo) => {
    if (nuevoTipo === 'separacion') {
      nextTick(() => precargarValorSeparacion())
      form.cuota_inicial_raw = 0
      form.cuota_inicial = 0
      form.valor_restante = 0
    } else if (nuevoTipo === 'venta') {
      form.valor_separacion = 0
      form.fecha_limite_separacion = ''
      // recalcular cuota si ya hay inmueble
      if (proyectoSeleccionado.value && form.valor_total) {
        const pct = Number(proyectoSeleccionado.value.porcentaje_cuota_inicial_min || 0)
        const raw = Number(form.valor_total) * (pct / 100)
        form.cuota_inicial_raw = Math.round(raw)
        form.cuota_inicial = Math.round(raw)
        form.valor_restante = Number(form.valor_total) - Math.round(raw)
      }
    }
  },
  { immediate: true }
)

// ✅ Valor fijo de separación desde el proyecto
const valorSeparacionProyecto = computed(() => {
  const p = proyectoSeleccionado.value
  return p ? Number(p.valor_min_separacion || 0) : 0
})

// Cuando sea separación y haya proyecto, fijar el valor (y mantenerlo fijo)
watch(
  [() => form.tipo_operacion, () => form.id_proyecto],
  ([tipo]) => {
    if (tipo === 'separacion') {
      form.valor_separacion = valorSeparacionProyecto.value
    }
  },
  { immediate: true }
)

// Precarga desde catálogo
onMounted(() => {
  if (!props.inmueblePrecargado) return

  const inmueble = props.inmueblePrecargado
  const esApartamento = Object.prototype.hasOwnProperty.call(inmueble, 'id_apartamento')
  const proyectoId = inmueble.torre?.id_proyecto

  if (!proyectoId) return

  form.id_proyecto = proyectoId
  form.inmueble_tipo = esApartamento ? 'apartamento' : 'local'
  form.inmueble_id = esApartamento ? inmueble.id_apartamento : inmueble.id_local
  form.valor_total = parseFloat(inmueble.valor_final || inmueble.valor_total || 0)

  if (proyectoSeleccionado.value?.porcentaje_cuota_inicial_min) {
    const pct = Number(proyectoSeleccionado.value.porcentaje_cuota_inicial_min || 0)
    const raw = Number(form.valor_total) * (pct / 100)
    form.cuota_inicial_raw = Math.round(raw)
    form.cuota_inicial = Math.round(raw)
    form.valor_restante = Number(form.valor_total) - Math.round(raw)
  }
})

const camposCompletos = computed(() => {
  const baseOk =
    !!form.documento_cliente &&
    !!form.id_proyecto &&
    !!form.inmueble_id &&
    !!form.id_forma_pago &&
    !!form.id_estado_inmueble

  if (!baseOk) return false

  if (form.tipo_operacion === 'venta') {
    return !erroresLocales.value.cuota_inicial && !!form.plazo_cuota_inicial_meses
  }

  if (form.tipo_operacion === 'separacion') {
    return (
      !erroresLocales.value.valor_separacion &&
      !erroresLocales.value.fecha_limite &&
      !!form.fecha_limite_separacion
    )
  }

  return false
})

function submit() {
  form.cuota_inicial = form.cuota_inicial_raw

  form.post('/admin/ventas', {
    preserveScroll: true,
    onError: () => {},
  })
}
</script>

<template>
  <TopBannerLayout :empleado="empleadoAuth">
    <Head title="Registrar Venta / Separación" />

    <div class="space-y-6">
      <PageHeader
        title="Registrar operación"
        kicker="Ventas"
        subtitle="Crea una venta o separación y valida reglas del proyecto."
      >
        <template #actions>
          <Link
            href="/admin/ventas"
            class="rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-800 hover:bg-gray-50 transition inline-flex items-center gap-2"
          >
            <ArrowLeftIcon class="h-5 w-5" />
            Volver
          </Link>
        </template>
      </PageHeader>

      <AppCard padding="md">
        <form @submit.prevent="submit" class="space-y-6">
          <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
            <!-- Tipo operación -->
            <div class="md:col-span-3">
              <FormField label="Tipo de operación">
                <SelectInput v-model="form.tipo_operacion">
                  <option value="" disabled>Seleccione…</option>
                  <option value="venta">Venta</option>
                  <option value="separacion">Separación</option>
                </SelectInput>
              </FormField>
            </div>

            <!-- Cliente -->
            <div class="md:col-span-5">
              <FormField label="Cliente">
                <SelectInput v-model="form.documento_cliente">
                  <option value="" disabled>Seleccione…</option>
                  <option v-for="c in clientes" :key="c.documento" :value="c.documento">
                    {{ c.nombre }}
                  </option>
                </SelectInput>
              </FormField>
            </div>

            <!-- Empleado -->
            <div class="md:col-span-4">
              <FormField label="Empleado">
                <div class="relative">
                  <UserIcon
                    class="h-5 w-5 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2"
                  />
                  <input
                    type="text"
                    :value="(empleadoAuth?.nombre || '') + ' ' + (empleadoAuth?.apellido || '')"
                    readonly
                    class="w-full rounded-xl border border-gray-300 bg-gray-50 pl-10 pr-3 py-2.5 text-sm text-gray-900 cursor-not-allowed"
                  />
                </div>
              </FormField>
            </div>

            <!-- Proyecto -->
            <div class="md:col-span-6">
              <FormField label="Proyecto">
                <div class="relative">
                  <BuildingOffice2Icon
                    class="h-5 w-5 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2"
                  />
                  <SelectInput v-model="form.id_proyecto" class="pl-10">
                    <option value="" disabled>Seleccione…</option>
                    <option v-for="p in proyectos" :key="p.id_proyecto" :value="p.id_proyecto">
                      {{ p.nombre }}
                    </option>
                  </SelectInput>
                </div>
              </FormField>
            </div>

            <!-- Inmueble -->
            <div class="md:col-span-6">
              <FormField label="Inmueble disponible">
                <div class="relative">
                  <HomeModernIcon
                    class="h-5 w-5 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2"
                  />
                  <SelectInput
                    v-model="form.inmueble_id"
                    :disabled="!inmueblesDisponibles.length"
                    class="pl-10"
                  >
                    <option value="" disabled>Seleccione…</option>
                    <option v-for="i in inmueblesDisponibles" :key="i.id" :value="i.id">
                      {{ i.label }}
                    </option>
                  </SelectInput>
                </div>

                <div
                  v-if="form.id_proyecto && !inmueblesDisponibles.length"
                  class="mt-2 flex items-center gap-2 text-amber-700 text-sm"
                >
                  <ExclamationTriangleIcon class="w-5 h-5" />
                  No hay inmuebles disponibles para este proyecto.
                </div>
              </FormField>
            </div>

            <!-- Forma pago -->
            <div class="md:col-span-6">
              <FormField label="Forma de pago">
                <div class="relative">
                  <CreditCardIcon
                    class="h-5 w-5 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2"
                  />
                  <SelectInput v-model="form.id_forma_pago" class="pl-10">
                    <option value="" disabled>Seleccione…</option>
                    <option
                      v-for="fp in formasPago"
                      :key="fp.id_forma_pago"
                      :value="fp.id_forma_pago"
                    >
                      {{ fp.forma_pago }}
                    </option>
                  </SelectInput>
                </div>
              </FormField>
            </div>

            <!-- Estado inmueble -->
            <div class="md:col-span-6">
              <FormField label="Estado del inmueble">
                <input
                  type="text"
                  :value="estadoNombre"
                  readonly
                  class="w-full rounded-xl border border-gray-300 bg-gray-50 px-3 py-2.5 text-sm text-gray-900 cursor-not-allowed"
                />
                <input type="hidden" v-model="form.id_estado_inmueble" />
              </FormField>
            </div>

            <!-- BLOQUE: VENTA -->
            <template v-if="form.tipo_operacion === 'venta'">
              <div class="md:col-span-4">
                <FormField label="Valor total">
                  <div class="relative">
                    <CurrencyDollarIcon
                      class="h-5 w-5 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2"
                    />
                    <input
                      type="text"
                      :value="formatearMoneda(form.valor_total)"
                      readonly
                      class="w-full rounded-xl border border-gray-300 bg-gray-50 pl-10 pr-3 py-2.5 text-sm font-semibold text-gray-900 cursor-not-allowed"
                    />
                  </div>
                </FormField>
              </div>

              <div class="md:col-span-4">
                <FormField label="Cuota inicial" :error="erroresLocales.cuota_inicial">
                  <div class="relative">
                    <CurrencyDollarIcon
                      class="h-5 w-5 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2"
                    />
                    <input
                      type="text"
                      :value="formatearMoneda(form.cuota_inicial_raw)"
                      @input="onCuotaInicialInput($event.target.value)"
                      class="w-full rounded-xl border border-gray-300 bg-white pl-10 pr-3 py-2.5 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
                    />
                  </div>
                </FormField>
              </div>

              <div class="md:col-span-4">
                <FormField label="Plazo cuota inicial (meses)">
                  <SelectInput v-model="form.plazo_cuota_inicial_meses">
                    <option value="" disabled>Seleccione…</option>
                    <option v-for="p in plazosDisponibles" :key="p" :value="p">
                      {{ p }} mes{{ p === 1 ? '' : 'es' }}
                    </option>
                  </SelectInput>
                </FormField>
              </div>

              <div class="md:col-span-4">
                <FormField label="Frecuencia de pago cuota inicial">
                  <select
                    v-model="form.frecuencia_cuota_inicial_meses"
                    :disabled="!form.plazo_cuota_inicial_meses || opcionesFrecuencia.length === 0"
                    class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 disabled:bg-gray-100 disabled:cursor-not-allowed"
                  >
                    <option value="">Seleccione…</option>
                    <option v-for="f in opcionesFrecuencia" :key="f.valor" :value="f.valor">
                      {{ f.etiqueta }}
                    </option>
                  </select>
                  <template #hint>
                    {{
                      opcionesFrecuencia.length === 0 && form.plazo_cuota_inicial_meses
                        ? 'No hay frecuencias que dividan exactamente este plazo.'
                        : 'Solo se muestran frecuencias que dividen exactamente el plazo.'
                    }}
                  </template>
                </FormField>
              </div>

              <div class="md:col-span-4">
                <FormField label="Valor restante">
                  <div class="relative">
                    <CurrencyDollarIcon
                      class="h-5 w-5 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2"
                    />
                    <input
                      type="text"
                      :value="formatearMoneda(form.valor_restante)"
                      readonly
                      class="w-full rounded-xl border border-gray-300 bg-gray-50 pl-10 pr-3 py-2.5 text-sm font-semibold text-gray-900 cursor-not-allowed"
                    />
                  </div>
                </FormField>
              </div>
            </template>

            <!-- BLOQUE: SEPARACIÓN -->
            <template v-else-if="form.tipo_operacion === 'separacion'">
              <div class="md:col-span-6">
                <FormField label="Valor de separación">
                  <div class="relative">
                    <CurrencyDollarIcon
                      class="h-5 w-5 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2"
                    />
                    <input
                      type="text"
                      :value="formatearMoneda(valorSeparacionProyecto)"
                      readonly
                      disabled
                      class="w-full rounded-xl border border-gray-300 bg-gray-50 pl-10 pr-3 py-2.5 text-sm font-semibold text-gray-900 cursor-not-allowed"
                    />
                  </div>
                  <template #hint
                    >Se toma automáticamente del valor definido en el proyecto.</template
                  >
                </FormField>
              </div>

              <div class="md:col-span-6">
                <FormField label="Fecha límite de separación" :error="erroresLocales.fecha_limite">
                  <div class="relative">
                    <CalendarDaysIcon
                      class="h-5 w-5 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2"
                    />
                    <TextInput
                      v-model="form.fecha_limite_separacion"
                      type="date"
                      class="pl-10"
                      :min="fechaMinimaSeparacion"
                      :max="fechaMaximaSeparacion"
                    />
                  </div>
                </FormField>
              </div>
            </template>

            <!-- Descripción -->
            <div class="md:col-span-12">
              <FormField label="Descripción">
                <div class="relative">
                  <DocumentTextIcon class="h-5 w-5 text-gray-400 absolute left-3 top-3" />
                  <TextArea
                    v-model="form.descripcion"
                    rows="3"
                    class="pl-10"
                    placeholder="Notas u observaciones…"
                  />
                </div>
              </FormField>
            </div>
          </div>

          <!-- Footer acciones -->
          <div
            class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-t pt-5"
          >
            <div class="text-sm">
              <span class="font-semibold text-gray-900">Operación:</span>
              <span
                class="ml-2 inline-flex items-center rounded-full border px-3 py-1 text-sm font-semibold"
                :class="
                  form.tipo_operacion === 'venta'
                    ? 'bg-emerald-50 border-emerald-200 text-emerald-800'
                    : form.tipo_operacion === 'separacion'
                      ? 'bg-amber-50 border-amber-200 text-amber-800'
                      : 'bg-gray-50 border-gray-200 text-gray-700'
                "
              >
                {{
                  form.tipo_operacion
                    ? form.tipo_operacion === 'venta'
                      ? 'Venta'
                      : 'Separación'
                    : '—'
                }}
              </span>
            </div>

            <button
              type="submit"
              :disabled="!camposCompletos || form.processing"
              class="rounded-xl px-5 py-2.5 text-sm font-semibold inline-flex items-center gap-2 transition disabled:opacity-60 disabled:cursor-not-allowed"
              :class="
                camposCompletos
                  ? 'bg-brand-600 text-white hover:bg-brand-700'
                  : 'bg-gray-200 text-gray-600'
              "
            >
              <span
                v-if="form.processing"
                class="animate-spin w-4 h-4 border-2 border-white border-t-transparent rounded-full"
              />
              <CheckCircleIcon v-else class="w-5 h-5" />
              {{ form.processing ? 'Guardando…' : 'Guardar operación' }}
            </button>
          </div>
        </form>
      </AppCard>
    </div>
  </TopBannerLayout>
</template>
