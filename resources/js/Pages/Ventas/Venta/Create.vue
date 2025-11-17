<script setup>
import { Head, Link, useForm, usePage, router } from '@inertiajs/vue3'
import { computed, ref, watch, onMounted } from 'vue'
import { CheckCircleIcon, ExclamationTriangleIcon } from '@heroicons/vue/24/solid'
import VentasLayout from '@/Components/VentasLayout.vue'

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
})

const inmueblesDisponibles = ref([])

const form = useForm({
  tipo_operacion: 'venta', // 'venta' o 'separacion'
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
  valor_restante: 0,
  descripcion: '',
  valor_separacion: 0,
  fecha_limite_separacion: '',
})

const proyectoSeleccionado = computed(() =>
  props.proyectos.find((p) => p.id_proyecto === parseInt(form.id_proyecto))
)

// Cuando cambia proyecto, cargar inmuebles disponibles
watch(
  () => form.id_proyecto,
  (nuevoProyecto) => {
    form.inmueble_id = ''
    form.valor_total = 0
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

// Pre-carga desde catálogo
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

// Cuando cambia inmueble, setear tipo y valor_total
watch(
  () => form.inmueble_id,
  (nuevoInmueble) => {
    if (!nuevoInmueble) return

    const inmueble = inmueblesDisponibles.value.find((i) => i.id === parseInt(nuevoInmueble))
    if (!inmueble || !proyectoSeleccionado.value) return

    form.inmueble_tipo = inmueble.tipo
    form.valor_total = parseFloat(inmueble.valor) || 0

    if (form.tipo_operacion === 'venta') {
      const porcentaje = parseFloat(proyectoSeleccionado.value.porcentaje_cuota_inicial_min || 0)
      form.cuota_inicial = porcentaje ? inmueble.valor * (porcentaje / 100) : 0
      form.valor_restante = form.valor_total - form.cuota_inicial
    } else {
      form.cuota_inicial = 0
      form.valor_restante = 0
    }
  }
)

// Si cambia tipo_operacion, recalcular campos
watch(
  () => form.tipo_operacion,
  (tipo) => {
    if (tipo === 'venta') {
      if (proyectoSeleccionado.value) {
        const porcentaje = parseFloat(proyectoSeleccionado.value.porcentaje_cuota_inicial_min || 0)
        form.cuota_inicial = porcentaje ? form.valor_total * (porcentaje / 100) : 0
        form.valor_restante = form.valor_total - form.cuota_inicial
      }
    } else {
      // separación
      form.cuota_inicial = 0
      form.valor_restante = 0
    }
  }
)

// Valor restante para venta
watch(
  () => form.cuota_inicial,
  () => {
    if (form.tipo_operacion === 'venta') {
      const total = parseFloat(form.valor_total) || 0
      const inicial = parseFloat(form.cuota_inicial) || 0
      form.valor_restante = total - inicial
    }
  }
)

// Campos obligatorios genéricos
const camposCompletos = computed(() =>
  Boolean(
    form.documento_cliente &&
      form.id_proyecto &&
      form.inmueble_id &&
      form.id_forma_pago &&
      form.id_estado_inmueble
  )
)

function submit() {
  form.post(route('ventas.store'), {
    onError: (errors) => {
      console.error(errors)
    },
  })
}
</script>

<template>
  <VentasLayout :empleado="empleado">
    <Head title="Registrar Venta / Separación" />

    <div class="flex justify-between items-center mb-6">
      <h1 class="text-3xl font-bold text-gray-900">Registrar Operación</h1>
      <Link href="/ventas" class="text-sm text-[#1e3a5f] hover:underline">← Volver</Link>
    </div>

    <form
      @submit.prevent="submit"
      class="bg-white rounded-xl shadow p-6 space-y-6 border border-gray-200"
    >
      <!-- Tipo de operación -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de Operación</label>
          <select v-model="form.tipo_operacion" class="w-full border-gray-300 rounded-lg shadow-sm">
            <option value="venta">Venta</option>
            <option value="separacion">Separación</option>
          </select>
        </div>

        <!-- Cliente -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Cliente</label>
          <select
            v-model="form.documento_cliente"
            class="w-full border-gray-300 rounded-lg shadow-sm"
          >
            <option value="">Seleccione...</option>
            <option v-for="c in clientes" :key="c.documento" :value="c.documento">
              {{ c.nombre }}
            </option>
          </select>
        </div>

        <!-- Empleado logueado -->
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
            <option value="">Seleccione...</option>
            <option v-for="p in proyectos" :key="p.id_proyecto" :value="p.id_proyecto">
              {{ p.nombre }}
            </option>
          </select>
        </div>

        <!-- Inmueble -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Inmueble Disponible</label>
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
            No hay inmuebles disponibles en este proyecto.
          </div>
        </div>

        <!-- Forma de Pago -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Forma de Pago</label>
          <select v-model="form.id_forma_pago" class="w-full border-gray-300 rounded-lg shadow-sm">
            <option value="">Seleccione...</option>
            <option v-for="fp in formasPago" :key="fp.id_forma_pago" :value="fp.id_forma_pago">
              {{ fp.forma_pago }}
            </option>
          </select>
        </div>

        <!-- Estado del Inmueble -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Estado del Inmueble *</label>
          <select
            v-model="form.id_estado_inmueble"
            required
            class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-[#1e3a5f] focus:border-transparent"
          >
            <option value="" disabled>Seleccione un estado...</option>
            <option
              v-for="estado in estadosInmueble"
              :key="estado.id_estado_inmueble"
              :value="estado.id_estado_inmueble"
            >
              {{ estado.nombre }}
            </option>
          </select>
        </div>

        <!-- Resumen económico (solo venta) -->
        <template v-if="form.tipo_operacion === 'venta'">
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
              type="number"
              v-model.number="form.cuota_inicial"
              class="w-full border-gray-300 rounded-lg shadow-sm"
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
        </template>

        <!-- Datos de separación -->
        <template v-else>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Valor de Separación</label>
            <input
              type="number"
              v-model.number="form.valor_separacion"
              class="w-full border-gray-300 rounded-lg shadow-sm"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1"
              >Fecha Límite Separación</label
            >
            <input
              type="date"
              v-model="form.fecha_limite_separacion"
              class="w-full border-gray-300 rounded-lg shadow-sm"
            />
          </div>
        </template>
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
          {{ form.processing ? 'Guardando...' : 'Guardar Operación' }}
        </button>
      </div>
    </form>
  </VentasLayout>
</template>
