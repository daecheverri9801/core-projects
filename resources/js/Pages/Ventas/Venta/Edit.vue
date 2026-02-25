<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3'
import { ref, computed, watch, onMounted } from 'vue'
import { CheckCircleIcon, ExclamationTriangleIcon } from '@heroicons/vue/24/solid'
import VentasLayout from '@/Components/VentasLayout.vue'

const page = usePage()
const empleado = computed(() => page.props.auth?.empleado || null)

const props = defineProps({
  venta: Object,
  empleado: Object,
  clientes: Array,
  proyectos: Array,
  apartamentos: Array,
  locales: Array,
  formasPago: Array,
  estadosInmueble: Array,
  empleados: Array,
  plazos_disponibles: Array,
  parqueaderos: { type: Array, default: () => [] }, // ✅ nuevo
})

const plazosDisponibles = ref([])
const inmueblesDisponibles = ref([])
const parqueaderosDisponibles = ref([])

const inicializarFecha = (fechaDesdeBD) => {
  if (fechaDesdeBD) return new Date(fechaDesdeBD).toISOString().split('T')[0]
  return null
}

const form = useForm({
  tipo_operacion: props.venta.tipo_operacion || 'venta',
  id_empleado: props.venta.id_empleado,
  documento_cliente: props.venta.documento_cliente,
  fecha_venta: props.venta.fecha_venta,
  fecha_vencimiento: props.venta.fecha_vencimiento,
  id_proyecto: props.venta.id_proyecto,
  inmueble_tipo: props.venta.id_apartamento ? 'apartamento' : 'local',
  inmueble_id: props.venta.id_apartamento || props.venta.id_local,
  id_forma_pago: props.venta.id_forma_pago,
  id_estado_inmueble:
    props.venta.apartamento?.id_estado_inmueble || props.venta.local?.id_estado_inmueble,
  valor_base: Number(props.venta.valor_base || 0),
  iva: props.venta.iva,
  valor_total: Number(props.venta.valor_total || 0),
  id_parqueadero: props.venta.id_parqueadero ? String(props.venta.id_parqueadero) : '', // ✅ nuevo
  cuota_inicial: Number(props.venta.cuota_inicial || 0),
  cuota_inicial_raw: Number(props.venta.cuota_inicial || 0),
  valor_restante: Number(props.venta.valor_restante || 0),
  descripcion: props.venta.descripcion,
  valor_separacion: props.venta.valor_separacion,
  fecha_limite_separacion: inicializarFecha(props.venta.fecha_limite_separacion),
  plazo_cuota_inicial_meses: props.venta.plazo_cuota_inicial_meses || '',
  frecuencia_cuota_inicial_meses: props.venta.frecuencia_cuota_inicial_meses || 1,
})

const proyectoSeleccionado = computed(() =>
  props.proyectos.find((p) => p.id_proyecto === parseInt(form.id_proyecto))
)

function cargarInmueblesDelProyecto(proyectoId) {
  const pid = parseInt(proyectoId)

  const aps = props.apartamentos.filter((a) => a.torre?.id_proyecto === pid)
  const locs = props.locales.filter((l) => l.torre?.id_proyecto === pid)

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

function cargarParqueaderosDelProyecto(proyectoId) {
  const pid = Number(proyectoId || 0)
  parqueaderosDisponibles.value = (props.parqueaderos || [])
    .filter((p) => Number(p.id_proyecto) === pid)
    .map((p) => ({ ...p, precio: Number(p.precio || 0) }))
}

const precioParqueaderoSeleccionado = computed(() => {
  if (!form.id_parqueadero) return 0
  const p = parqueaderosDisponibles.value.find(
    (x) => x.id_parqueadero === Number(form.id_parqueadero)
  )
  return p ? Number(p.precio || 0) : 0
})

function recalcularEconomia() {
  const total = Number(form.valor_base || 0) + Number(precioParqueaderoSeleccionado.value || 0)
  form.valor_total = total

  if (form.tipo_operacion === 'venta' && proyectoSeleccionado.value) {
    const porcentaje = Number(proyectoSeleccionado.value.porcentaje_cuota_inicial_min || 0)
    const raw = total * (porcentaje / 100)
    form.cuota_inicial_raw = raw
    form.cuota_inicial = raw
    form.valor_restante = total - raw
  } else {
    form.cuota_inicial_raw = 0
    form.cuota_inicial = 0
    form.valor_restante = 0
  }
}

watch(
  () => form.id_proyecto,
  (nuevoProyecto) => {
    if (nuevoProyecto) {
      cargarInmueblesDelProyecto(nuevoProyecto)
      cargarParqueaderosDelProyecto(nuevoProyecto)
    } else {
      inmueblesDisponibles.value = []
      parqueaderosDisponibles.value = []
    }
  },
  { immediate: true }
)

watch(
  () => form.inmueble_id,
  (nuevoInmueble) => {
    const inmueble = inmueblesDisponibles.value.find((i) => i.id === parseInt(nuevoInmueble))
    if (!inmueble) return

    form.inmueble_tipo = inmueble.tipo
    form.valor_base = Number(inmueble.valor || 0)

    if (form.inmueble_tipo !== 'apartamento') {
      form.id_parqueadero = ''
    }

    recalcularEconomia()
  }
)

watch(
  () => form.id_parqueadero,
  () => {
    if (form.inmueble_tipo !== 'apartamento') {
      form.id_parqueadero = ''
      return
    }
    recalcularEconomia()
  }
)

watch(
  () => form.tipo_operacion,
  () => recalcularEconomia()
)

function formatearMoneda(valor) {
  if (valor === null || valor === undefined || valor === '') return ''
  const num = Number(valor)
  const redondeado = Math.ceil(num)
  return redondeado.toLocaleString('es-CO', {
    style: 'currency',
    currency: 'COP',
    maximumFractionDigits: 0,
  })
}

function parseMoneda(valorStr) {
  if (!valorStr) return 0
  return Number(valorStr.replace(/\./g, '').replace(/[^0-9]/g, ''))
}

function onCuotaInicialInput(value) {
  const clean = value.replace(/[^\d]/g, '')
  const raw = clean ? Number(clean) : 0

  form.cuota_inicial_raw = raw
  form.cuota_inicial = raw

  if (form.tipo_operacion === 'venta') {
    form.valor_restante = Number(form.valor_total) - raw
  }
}

const camposCompletos = computed(
  () =>
    form.documento_cliente &&
    form.id_proyecto &&
    form.inmueble_id &&
    form.id_forma_pago &&
    form.id_estado_inmueble
)

function submit() {
  form.cuota_inicial = form.cuota_inicial_raw
  form.put(`/ventas/${props.venta.id_venta}`)
}
</script>

<template>
  <VentasLayout :empleado="empleado">
    <Head title="Editar Venta / Separación" />

    <div class="flex justify-between items-center mb-6">
      <Link href="/ventas" class="text-sm text-[#1e3a5f] hover:underline">← Volver</Link>
      <h1 class="text-3xl font-bold text-gray-900">
        Editar {{ form.tipo_operacion === 'venta' ? 'Venta' : 'Separación' }} #{{
          props.venta.id_venta
        }}
      </h1>
    </div>

    <form
      @submit.prevent="submit"
      class="bg-white rounded-xl shadow p-6 space-y-6 border border-gray-200"
    >
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de Operación</label>
          <select v-model="form.tipo_operacion" class="w-full border-gray-300 rounded-lg shadow-sm">
            <option value="venta">Venta</option>
            <option value="separacion">Separación</option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Cliente</label>
          <select
            v-model="form.documento_cliente"
            class="w-full border-gray-300 rounded-lg shadow-sm"
          >
            <option v-for="c in clientes" :key="c.documento" :value="c.documento">
              {{ c.nombre }}
            </option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Empleado</label>
          <input
            type="text"
            :value="empleado?.nombre + ' ' + empleado?.apellido"
            readonly
            class="w-full border-gray-300 rounded-lg shadow-sm bg-gray-100 cursor-not-allowed"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Proyecto</label>
          <select v-model="form.id_proyecto" class="w-full border-gray-300 rounded-lg shadow-sm">
            <option v-for="p in proyectos" :key="p.id_proyecto" :value="p.id_proyecto">
              {{ p.nombre }}
            </option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Inmueble</label>
          <select
            v-model="form.inmueble_id"
            :disabled="!inmueblesDisponibles.length"
            class="w-full border-gray-300 rounded-lg shadow-sm"
          >
            <option value="">Seleccione...</option>
            <option v-for="i in inmueblesDisponibles" :key="i.id" :value="i.id">
              {{ i.label }}
            </option>
          </select>

          <div
            v-if="form.id_proyecto && !inmueblesDisponibles.length"
            class="mt-2 flex items-center gap-2 text-amber-600 text-sm"
          >
            <ExclamationTriangleIcon class="w-5 h-5" />
            No hay inmuebles para este proyecto.
          </div>
        </div>

        <!-- ✅ Parqueadero adicional -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1"
            >Parqueadero adicional (opcional)</label
          >
          <select
            v-model="form.id_parqueadero"
            :disabled="!form.id_proyecto || form.inmueble_tipo !== 'apartamento'"
            class="w-full border-gray-300 rounded-lg shadow-sm"
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
          <p class="text-xs text-gray-500 mt-1">Solo aplica para apartamentos.</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Forma de Pago</label>
          <select v-model="form.id_forma_pago" class="w-full border-gray-300 rounded-lg shadow-sm">
            <option v-for="fp in formasPago" :key="fp.id_forma_pago" :value="fp.id_forma_pago">
              {{ fp.forma_pago }}
            </option>
          </select>
        </div>

        <template v-if="form.tipo_operacion === 'venta'">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Valor Total</label>
            <input
              type="text"
              :value="form.valor_total ? formatearMoneda(form.valor_total) : '-'"
              readonly
              class="w-full border-gray-300 rounded-lg bg-gray-100 text-gray-700 font-semibold"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Cuota Inicial</label>
            <input
              type="text"
              :value="formatearMoneda(form.cuota_inicial_raw)"
              @input="onCuotaInicialInput($event.target.value)"
              class="w-full border-gray-300 rounded-lg shadow-sm"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1"
              >Plazo Cuota Inicial (meses)</label
            >
            <select
              v-model="form.plazo_cuota_inicial_meses"
              class="w-full border-gray-300 rounded-lg shadow-sm"
            >
              <option value="">Seleccione...</option>
              <option v-for="p in plazosDisponibles" :key="p" :value="p">
                {{ p }} mes{{ p === 1 ? '' : 'es' }}
              </option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Valor Restante</label>
            <input
              type="text"
              :value="form.valor_total ? formatearMoneda(form.valor_restante) : '-'"
              readonly
              class="w-full border-gray-300 rounded-lg bg-gray-100 text-gray-700 font-semibold"
            />
          </div>
        </template>

        <template v-else>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Valor de Separación</label>
            <input
              type="text"
              :value="formatearMoneda(form.valor_separacion)"
              @input="(e) => (form.valor_separacion = parseMoneda(e.target.value))"
              class="w-full border-gray-300 rounded-lg shadow-sm"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1"
              >Fecha Límite de Separación</label
            >
            <input
              type="date"
              v-model="form.fecha_limite_separacion"
              class="w-full border-gray-300 rounded-lg shadow-sm"
            />
          </div>
        </template>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
        <textarea
          v-model="form.descripcion"
          rows="3"
          class="w-full border-gray-300 rounded-lg shadow-sm"
        ></textarea>
      </div>

      <div class="flex justify-end items-center gap-3">
        <button
          type="submit"
          :disabled="!camposCompletos || form.processing"
          :class="[
            'px-6 py-2 rounded-lg font-semibold flex items-center gap-2 transition',
            camposCompletos
              ? 'bg-[#1e3a5f] text-white hover:bg-[#2c5282]'
              : 'bg-gray-300 text-gray-600 cursor-not-allowed',
          ]"
        >
          <span
            v-if="form.processing"
            class="animate-spin w-4 h-4 border-2 border-white border-t-transparent rounded-full"
          ></span>
          <CheckCircleIcon v-else class="w-5 h-5" />
          {{ form.processing ? 'Actualizando...' : 'Actualizar Operación' }}
        </button>
      </div>
    </form>
  </VentasLayout>
</template>
