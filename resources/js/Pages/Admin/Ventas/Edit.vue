<!-- resources/js/Pages/Ventas/Edit.vue -->
<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3'
import { ref, computed, watch, onMounted } from 'vue'
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
  venta: Object,
  clientes: Array,
  proyectos: Array,
  apartamentos: Array,
  locales: Array,
  formasPago: Array,
  estadosInmueble: Array,
  empleados: Array,
  plazos_disponibles: Array,
})

const plazosDisponibles = ref([])
const inmueblesDisponibles = ref([])

const inicializarFecha = (fechaDesdeBD) => {
  if (!fechaDesdeBD) return null
  return new Date(fechaDesdeBD).toISOString().split('T')[0]
}

const form = useForm({
  tipo_operacion: props.venta?.tipo_operacion || 'venta',
  id_empleado: props.venta?.id_empleado,
  documento_cliente: props.venta?.documento_cliente,
  fecha_venta: props.venta?.fecha_venta,
  fecha_vencimiento: props.venta?.fecha_vencimiento,
  id_proyecto: props.venta?.id_proyecto,
  inmueble_tipo: props.venta?.id_apartamento ? 'apartamento' : 'local',
  inmueble_id: props.venta?.id_apartamento || props.venta?.id_local,
  id_forma_pago: props.venta?.id_forma_pago,
  id_estado_inmueble:
    props.venta?.apartamento?.id_estado_inmueble || props.venta?.local?.id_estado_inmueble,
  valor_base: props.venta?.valor_base,
  iva: props.venta?.iva,
  valor_total: props.venta?.valor_total,
  cuota_inicial: props.venta?.cuota_inicial,
  cuota_inicial_raw: Number(props.venta?.cuota_inicial || 0),
  valor_restante: props.venta?.valor_restante,
  descripcion: props.venta?.descripcion,
  valor_separacion: props.venta?.valor_separacion,
  fecha_limite_separacion: inicializarFecha(props.venta?.fecha_limite_separacion),
  plazo_cuota_inicial_meses: props.venta?.plazo_cuota_inicial_meses || '',
  frecuencia_cuota_inicial_meses: props.venta?.frecuencia_cuota_inicial_meses ?? 1,
})

onMounted(() => {
  form.valor_total = Number(form.valor_total || 0)
  form.cuota_inicial = Number(form.cuota_inicial || 0)
  form.valor_restante = Number(form.valor_restante || 0)

  if (form.id_proyecto) cargarInmueblesDelProyecto(form.id_proyecto)
})

const proyectoSeleccionado = computed(() =>
  props.proyectos?.find((p) => p.id_proyecto === parseInt(form.id_proyecto))
)

function cargarInmueblesDelProyecto(proyectoId) {
  const pid = parseInt(proyectoId)

  const aps = (props.apartamentos || []).filter((a) => a.torre?.id_proyecto === pid)
  const locs = (props.locales || []).filter((l) => l.torre?.id_proyecto === pid)

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

const erroresLocales = ref({
  cuota_inicial: '',
  valor_separacion: '',
  fecha_limite: '',
})

watch(
  () => form.cuota_inicial,
  (nuevo) => {
    if (form.tipo_operacion === 'venta') {
      const total = Number(form.valor_total) || 0
      const inicial = Number(nuevo) || 0
      form.valor_restante = total - inicial
    }
  }
)

watch(
  () => form.valor_separacion,
  (nuevo) => {
    erroresLocales.value.valor_separacion = ''
    if (form.tipo_operacion !== 'separacion') return
    if (!proyectoSeleccionado.value) return

    const minimo = Number(proyectoSeleccionado.value.valor_min_separacion || 0)
    if (Number(nuevo) < minimo) {
      erroresLocales.value.valor_separacion = `El valor mínimo de separación es ${formatearMoneda(
        minimo
      )}`
    }
  }
)

const fechaMinimaSeparacion = computed(() => new Date().toISOString().split('T')[0])

const fechaMaximaSeparacion = computed(() => {
  if (!proyectoSeleccionado.value) return null
  const dias = Number(proyectoSeleccionado.value.plazo_max_separacion_dias || 0)
  if (!dias) return null
  const fecha = new Date()
  fecha.setDate(fecha.getDate() + dias)
  return fecha.toISOString().split('T')[0]
})

const estadoActualNombre = computed(() => {
  const estado = (props.estadosInmueble || []).find(
    (e) => e.id_estado_inmueble === form.id_estado_inmueble
  )
  return estado ? estado.nombre : '—'
})

watch(
  () => form.fecha_limite_separacion,
  (nuevaFecha) => {
    erroresLocales.value.fecha_limite = ''
    if (form.tipo_operacion !== 'separacion') return
    if (!proyectoSeleccionado.value) return
    if (!nuevaFecha) return

    const diasMax = Number(proyectoSeleccionado.value.plazo_max_separacion_dias || 0)
    if (!diasMax) return

    const fechaVenta = new Date(form.fecha_venta)
    const fechaLimite = new Date(nuevaFecha)
    const diffMs = fechaLimite.getTime() - fechaVenta.getTime()
    const diffDias = Math.ceil(diffMs / (1000 * 60 * 60 * 24))

    if (diffDias > diasMax) {
      erroresLocales.value.fecha_limite = `La fecha máxima permitida es ${diasMax} días desde la fecha de venta`
    }
  }
)

watch(
  () => form.id_proyecto,
  (nuevoProyecto) => {
    if (nuevoProyecto) cargarInmueblesDelProyecto(nuevoProyecto)
    else inmueblesDisponibles.value = []
  }
)

watch(
  () => form.inmueble_id,
  (nuevoInmueble) => {
    const inmueble = inmueblesDisponibles.value.find((i) => i.id === parseInt(nuevoInmueble))
    if (!inmueble || !proyectoSeleccionado.value) return

    form.inmueble_tipo = inmueble.tipo
    form.valor_total = Number(inmueble.valor || 0)

    if (form.tipo_operacion === 'venta') {
      const porcentaje = parseFloat(proyectoSeleccionado.value.porcentaje_cuota_inicial_min || 0)
      const raw = form.valor_total * (porcentaje / 100)
      form.cuota_inicial_raw = raw
      form.cuota_inicial = raw
      form.valor_restante = form.valor_total - raw
    } else {
      form.cuota_inicial_raw = 0
      form.cuota_inicial = 0
      form.valor_restante = 0
    }
  }
)

watch(
  () => form.tipo_operacion,
  (tipo) => {
    if (!proyectoSeleccionado.value) return

    if (tipo === 'venta') {
      const estado = (props.estadosInmueble || []).find((e) => e.nombre === 'Vendido')
      if (estado) form.id_estado_inmueble = estado.id_estado_inmueble

      const porcentaje = Number(proyectoSeleccionado.value.porcentaje_cuota_inicial_min || 0)
      const raw = porcentaje ? form.valor_total * (porcentaje / 100) : 0
      form.cuota_inicial_raw = raw
      form.cuota_inicial = raw
      form.valor_restante = Number(form.valor_total || 0) - raw

      form.valor_separacion = 0
      form.fecha_limite_separacion = null
    } else {
      const estado = (props.estadosInmueble || []).find((e) => e.nombre === 'Separado')
      if (estado) form.id_estado_inmueble = estado.id_estado_inmueble

      form.cuota_inicial_raw = 0
      form.cuota_inicial = 0
      form.valor_restante = 0

      const minimo = Number(proyectoSeleccionado.value.valor_min_separacion || 0)
      form.valor_separacion = minimo
    }
  }
)

watch(
  () => form.cuota_inicial_raw,
  () => {
    if (form.tipo_operacion !== 'venta') return
    const total = Number(form.valor_total) || 0
    const inicial = Number(form.cuota_inicial_raw) || 0
    form.valor_restante = total - inicial
  }
)

watch(
  () => form.id_proyecto,
  () => {
    const p = proyectoSeleccionado.value
    if (!p) {
      plazosDisponibles.value = []
      return
    }
    const plazos = calcularPlazosProyecto(p)
    plazosDisponibles.value = plazos

    if (form.plazo_cuota_inicial_meses && !plazos.includes(form.plazo_cuota_inicial_meses)) {
      plazosDisponibles.value.unshift(form.plazo_cuota_inicial_meses)
    }
  },
  { immediate: true }
)

const camposCompletos = computed(
  () =>
    form.documento_cliente &&
    form.id_proyecto &&
    form.inmueble_id &&
    form.id_forma_pago &&
    form.id_estado_inmueble
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

function calcularPlazosProyecto(proyecto) {
  if (!proyecto) return []
  const inicio = proyecto.fecha_inicio
  const plazoTotal = proyecto.plazo_cuota_inicial_meses
  if (!inicio || !plazoTotal) return []

  const start = new Date(inicio)
  const now = new Date()
  const diffMonths =
    (now.getFullYear() - start.getFullYear()) * 12 + (now.getMonth() - start.getMonth())
  const plazosRestantes = Math.max(Number(plazoTotal) - diffMonths, 0)
  return Array.from({ length: plazosRestantes }, (_, i) => i + 1)
}

function onCuotaInicialInput(value) {
  const clean = String(value || '').replace(/[^\d]/g, '')
  const raw = clean ? Number(clean) : 0

  form.cuota_inicial_raw = raw
  form.cuota_inicial = raw

  if (form.tipo_operacion === 'venta') {
    form.valor_restante = Number(form.valor_total) - raw
  }
}

function submit() {
  form.cuota_inicial = form.cuota_inicial_raw

  form.put(`/admin/ventas/${props.venta.id_venta}`, {
    preserveScroll: true,
    onError: (errors) => {
      if (errors?.plazo_cuota_inicial_meses) {
        alert('Error en plazo: ' + errors.plazo_cuota_inicial_meses)
      }
    },
  })
}
</script>

<template>
  <TopBannerLayout :empleado="empleadoAuth" panel-name="Panel administrador">
    <Head title="Editar venta / separación" />

    <div class="space-y-6">
      <PageHeader
        :title="`Editar ${form.tipo_operacion === 'venta' ? 'Venta' : 'Separación'} #${props.venta.id_venta}`"
        kicker="Ventas"
        subtitle="Actualiza los datos y valida las reglas del proyecto."
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

      <!-- FORM -->
      <AppCard padding="md">
        <form @submit.prevent="submit" class="space-y-6">
          <!-- Grid principal -->
          <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
            <!-- Tipo operación -->
            <div class="md:col-span-3">
              <FormField label="Tipo de operación">
                <SelectInput v-model="form.tipo_operacion">
                  <option value="venta">Venta</option>
                  <option value="separacion">Separación</option>
                </SelectInput>
              </FormField>
            </div>

            <!-- Cliente -->
            <div class="md:col-span-5">
              <FormField label="Cliente">
                <SelectInput v-model="form.documento_cliente">
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
              <FormField label="Inmueble">
                <div class="relative">
                  <HomeModernIcon
                    class="h-5 w-5 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2"
                  />
                  <SelectInput
                    v-model="form.inmueble_id"
                    :disabled="!inmueblesDisponibles.length"
                    class="pl-10"
                  >
                    <option value="">Seleccione…</option>
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

            <!-- Forma Pago -->
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
                  :value="estadoActualNombre"
                  readonly
                  class="w-full rounded-xl border border-gray-300 bg-gray-50 px-3 py-2.5 text-sm text-gray-900 cursor-not-allowed"
                />
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
                    <option value="">Seleccione…</option>
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
                    :disabled="!form.plazo_cuota_inicial_meses"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm disabled:bg-gray-100 disabled:cursor-not-allowed"
                  >
                    <option :value="1">Mensual (cada 1 mes)</option>
                    <option :value="2" v-if="Number(form.plazo_cuota_inicial_meses) >= 2">
                      Bimestral (cada 2 meses)
                    </option>
                    <option :value="3" v-if="Number(form.plazo_cuota_inicial_meses) >= 3">
                      Trimestral (cada 3 meses)
                    </option>
                    <option :value="4" v-if="Number(form.plazo_cuota_inicial_meses) >= 4">
                      Cada 4 meses
                    </option>
                    <option :value="6" v-if="Number(form.plazo_cuota_inicial_meses) >= 6">
                      Semestral (cada 6 meses)
                    </option>
                    <option :value="12" v-if="Number(form.plazo_cuota_inicial_meses) >= 12">
                      Anual (cada 12 meses)
                    </option>
                  </select>
                  <template #hint> Ej: plazo 12 meses, trimestral => 4 pagos. </template>
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
            <template v-else>
              <div class="md:col-span-6">
                <FormField label="Valor de separación" :error="erroresLocales.valor_separacion">
                  <div class="relative">
                    <CurrencyDollarIcon
                      class="h-5 w-5 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2"
                    />
                    <input
                      type="text"
                      :value="formatearMoneda(form.valor_separacion)"
                      @input="(e) => (form.valor_separacion = parseMoneda(e.target.value))"
                      class="w-full rounded-xl border border-gray-300 bg-white pl-10 pr-3 py-2.5 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
                    />
                  </div>
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

              <div class="md:col-span-12 rounded-2xl border border-amber-200 bg-amber-50 p-4">
                <p class="text-sm font-semibold text-amber-900">Reglas de separación</p>
                <p class="text-sm text-amber-900/80 mt-1">
                  Valor mínimo:
                  <span class="font-semibold">{{
                    formatearMoneda(proyectoSeleccionado?.valor_min_separacion || 0)
                  }}</span>
                  · Plazo máximo:
                  <span class="font-semibold">{{
                    proyectoSeleccionado?.plazo_max_separacion_dias || 0
                  }}</span>
                  días.
                </p>
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
              <span class="font-semibold text-gray-900">Estado:</span>
              <span
                class="ml-2 inline-flex items-center rounded-full border px-3 py-1 text-sm font-semibold"
                :class="
                  form.tipo_operacion === 'venta'
                    ? 'bg-emerald-50 border-emerald-200 text-emerald-800'
                    : 'bg-amber-50 border-amber-200 text-amber-800'
                "
              >
                {{ form.tipo_operacion === 'venta' ? 'Venta' : 'Separación' }}
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
              {{ form.processing ? 'Actualizando…' : 'Actualizar operación' }}
            </button>
          </div>
        </form>
      </AppCard>
    </div>
  </TopBannerLayout>
</template>
