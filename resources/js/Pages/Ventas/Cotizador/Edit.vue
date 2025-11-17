<script setup>
import { Head, Link, useForm, usePage, router } from '@inertiajs/vue3'
import { ref, computed, watch, onMounted} from 'vue'
import { CheckCircleIcon, ExclamationTriangleIcon } from '@heroicons/vue/24/solid'
import VentasLayout from '@/Components/VentasLayout.vue'

const page = usePage()
const empleado = computed(() => page.props.auth?.empleado || null)

const props = defineProps({
  venta: Object,
  clientes: Array,
  proyectos: Array,
  apartamentos: Array,
  locales: Array,
  formasPago: Array,
  estadosInmueble: Array,
  empleados: Array,
})

const form = useForm({
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
  valor_base: props.venta.valor_base,
  iva: props.venta.iva,
  valor_total: props.venta.valor_total,
  cuota_inicial: props.venta.cuota_inicial,
  valor_restante: props.venta.valor_restante,
  descripcion: props.venta.descripcion,
})

const inmueblesDisponibles = ref([])

const proyectoSeleccionado = computed(() =>
  props.proyectos.find((p) => p.id_proyecto === parseInt(form.id_proyecto))
)

// Cargar inmuebles al montar si ya hay proyecto seleccionado
onMounted(() => {
  if (form.id_proyecto) {
    cargarInmueblesDelProyecto(form.id_proyecto)
  }
})

// Watcher para cambio de proyecto
watch(
  () => form.id_proyecto,
  (nuevoProyecto) => {
    if (nuevoProyecto) {
      cargarInmueblesDelProyecto(nuevoProyecto)
    } else {
      inmueblesDisponibles.value = []
    }
  }
)

// Función para cargar inmuebles
function cargarInmueblesDelProyecto(proyectoId) {
  const pid = parseInt(proyectoId)

  const aps = props.apartamentos.filter((a) => a.torre?.id_proyecto === pid)

  const locs = props.locales.filter((l) => l.torre?.id_proyecto === pid)

  inmueblesDisponibles.value = [
    ...aps.map((a) => ({
      tipo: 'apartamento',
      id: a.id_apartamento,
      label: `Apto ${a.numero}`,
      valor: parseFloat(a.valor_final || a.valor_base || 0),
    })),
    ...locs.map((l) => ({
      tipo: 'local',
      id: l.id_local,
      label: `Local ${l.numero}`,
      valor: parseFloat(l.valor_total || l.valor_base || 0),
    })),
  ]
}

watch(
  () => form.inmueble_id,
  (nuevoInmueble) => {
    const inmueble = inmueblesDisponibles.value.find((i) => i.id === parseInt(nuevoInmueble))
    if (!inmueble || !proyectoSeleccionado.value) return

    form.inmueble_tipo = inmueble.tipo
    form.valor_base = inmueble.valor
    const porcentaje = parseFloat(proyectoSeleccionado.value.porcentaje_cuota_inicial_min) || 0
    form.cuota_inicial = inmueble.valor * (porcentaje / 100)
    form.valor_total = inmueble.valor
  }
)

const camposCompletos = computed(
  () =>
    form.documento_cliente &&
    form.id_proyecto &&
    form.inmueble_id &&
    form.id_forma_pago &&
    form.id_estado_inmueble
)

function submit() {
  form.put(`/ventas/${props.venta.id_venta}`)
}
</script>

<template>
  <VentasLayout :empleado="empleado">
    <Head title="Editar Venta" />

    <div class="flex justify-between items-center mb-6">
      <Link href="/ventas" class="text-sm text-[#1e3a5f] hover:underline">← Volver</Link>
      <h1 class="text-3xl font-bold text-gray-900">Editar Venta #{{ props.venta.id_venta }}</h1>
    </div>

    <form
      @submit.prevent="submit"
      class="bg-white rounded-xl shadow p-6 space-y-6 border border-gray-200"
    >
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Cliente -->
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

        <!-- Empleado (no editable) -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Empleado</label>
          <input
            type="text"
            :value="empleado?.nombre + ' ' + empleado?.apellido"
            readonly
            class="w-full border-gray-300 rounded-lg shadow-sm bg-gray-100 cursor-not-allowed"
          />
        </div>

        <!-- Proyecto -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Proyecto</label>
          <select v-model="form.id_proyecto" class="w-full border-gray-300 rounded-lg shadow-sm">
            <option v-for="p in proyectos" :key="p.id_proyecto" :value="p.id_proyecto">
              {{ p.nombre }}
            </option>
          </select>
        </div>

        <!-- Inmueble -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Inmueble</label>
          <div>
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
          </div>

          <div
            v-if="form.id_proyecto && !inmueblesDisponibles.length"
            class="mt-2 flex items-center gap-2 text-amber-600 text-sm"
          >
            <ExclamationTriangleIcon class="w-5 h-5" />
            No hay inmuebles disponibles en este proyecto.
          </div>
        </div>

        <!-- Forma Pago -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Forma de Pago</label>
          <select v-model="form.id_forma_pago" class="w-full border-gray-300 rounded-lg shadow-sm">
            <option v-for="fp in formasPago" :key="fp.id_forma_pago" :value="fp.id_forma_pago">
              {{ fp.forma_pago }}
            </option>
          </select>
        </div>

        <!-- Estado -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Estado del Inmueble *</label>
          <select
            v-model="form.id_estado_inmueble"
            required
            class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-[#1e3a5f]"
          >
            <option value="">Seleccione...</option>
            <option
              v-for="estado in estadosInmueble"
              :key="estado.id_estado_inmueble"
              :value="estado.id_estado_inmueble"
            >
              {{ estado.nombre }}
            </option>
          </select>
        </div>

        <!-- Campos automáticos -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Valor Total</label>
          <input
            type="text"
            :value="
              form.valor_total
                ? `$${parseFloat(form.valor_total).toLocaleString('es-CO', { minimumFractionDigits: 0, maximumFractionDigits: 0 })}`
                : '-'
            "
            readonly
            class="w-full border-gray-300 rounded-lg bg-gray-100 text-gray-700 font-semibold"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Cuota Inicial</label>
          <input
            type="text"
            :value="
              form.cuota_inicial
                ? `$${parseFloat(form.cuota_inicial).toLocaleString('es-CO', { minimumFractionDigits: 0, maximumFractionDigits: 0 })}`
                : '-'
            "
            readonly
            class="w-full border-gray-300 rounded-lg bg-gray-100 text-gray-700 font-semibold"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Valor Restante</label>
          <input
            type="text"
            :value="
              form.valor_total
                ? `$${parseFloat(form.valor_restante).toLocaleString('es-CO', { minimumFractionDigits: 0, maximumFractionDigits: 0 })}`
                : '-'
            "
            readonly
            class="w-full border-gray-300 rounded-lg bg-gray-100 text-gray-700 font-semibold"
          />
        </div>
      </div>

      <!-- Descripción -->
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
          {{ form.processing ? 'Actualizando...' : 'Actualizar Venta' }}
        </button>
      </div>
    </form>
  </VentasLayout>
</template>
