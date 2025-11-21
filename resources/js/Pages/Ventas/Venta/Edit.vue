<script setup>
import { Head, Link, useForm, usePage, router } from '@inertiajs/vue3'
import { ref, computed, watch, onMounted } from 'vue'
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
  plazos_disponibles: Array,
})

const plazosDisponibles = ref([])

const inmueblesDisponibles = ref([])

// ✅ INICIALIZAR CORRECTAMENTE LA FECHA
const inicializarFecha = (fechaDesdeBD) => {
  if (fechaDesdeBD) {
    // Si viene fecha de la BD, formatearla correctamente para el input date
    return new Date(fechaDesdeBD).toISOString().split('T')[0]
  }
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
  valor_base: props.venta.valor_base,
  iva: props.venta.iva,
  valor_total: props.venta.valor_total,
  cuota_inicial: props.venta.cuota_inicial,
  valor_restante: props.venta.valor_restante,
  descripcion: props.venta.descripcion,
  valor_separacion: props.venta.valor_separacion,
  fecha_limite_separacion: inicializarFecha(props.venta.fecha_limite_separacion),
  plazo_cuota_inicial_meses: props.venta.plazo_cuota_inicial_meses || '',
})

onMounted(() => {
  form.valor_total = Number(form.valor_total || 0)
  form.cuota_inicial = Number(form.cuota_inicial || 0)
  form.valor_restante = Number(form.valor_restante || 0)
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

watch(
  () => form.tipo_operacion,
  (tipo) => {
    if (!proyectoSeleccionado.value) return

    if (tipo === 'venta') {
      const estado = props.estadosInmueble.find((e) => e.nombre === 'Vendido')
      if (estado) form.id_estado_inmueble = estado.id_estado_inmueble
    } else {
      const estado = props.estadosInmueble.find((e) => e.nombre === 'Separado')
      if (estado) form.id_estado_inmueble = estado.id_estado_inmueble
    }
  }
)

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

    const minimo = proyectoSeleccionado.value.valor_min_separacion

    if (nuevo < minimo) {
      erroresLocales.value.valor_separacion = `El valor mínimo de separación es ${formatearMoneda(minimo)}`
    }
  }
)

// Fecha mínima = hoy
const fechaMinimaSeparacion = computed(() => {
  return new Date().toISOString().split('T')[0]
})

// Fecha máxima = hoy + plazo permitido por el proyecto
const fechaMaximaSeparacion = computed(() => {
  if (!proyectoSeleccionado.value) return null

  const dias = Number(proyectoSeleccionado.value.plazo_max_separacion_dias || 0)
  const fecha = new Date()
  fecha.setDate(fecha.getDate() + dias)
  return fecha.toISOString().split('T')[0]
})

const estadoActualNombre = computed(() => {
  const estado = props.estadosInmueble.find((e) => e.id_estado_inmueble === form.id_estado_inmueble)
  return estado ? estado.nombre : '—'
})

watch(
  () => form.fecha_limite_separacion,
  (nuevaFecha) => {
    erroresLocales.value.fecha_limite = ''

    if (form.tipo_operacion !== 'separacion') return
    if (!proyectoSeleccionado.value) return
    if (!nuevaFecha) return

    const diasMax = proyectoSeleccionado.value.plazo_max_separacion_dias
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

onMounted(() => {
  if (form.id_proyecto) {
    cargarInmueblesDelProyecto(form.id_proyecto)
  }
})

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

watch(
  () => form.inmueble_id,
  (nuevoInmueble) => {
    const inmueble = inmueblesDisponibles.value.find((i) => i.id === parseInt(nuevoInmueble))
    if (!inmueble || !proyectoSeleccionado.value) return

    form.inmueble_tipo = inmueble.tipo
    form.valor_total = inmueble.valor

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
      form.cuota_inicial = 0
      form.valor_restante = 0
    }
  }
)

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

watch(
  () => form.tipo_operacion,
  (tipo) => {
    if (!proyectoSeleccionado.value) return

    if (tipo === 'separacion') {
      // Precargar el valor mínimo de separación del proyecto
      const minimo = Number(proyectoSeleccionado.value.valor_min_separacion || 0)
      form.valor_separacion = minimo

      // Limpiar campos de venta
      form.cuota_inicial = 0
      form.valor_restante = 0
    } else if (tipo === 'venta') {
      // Recalcular cuota inicial y valor restante
      const porcentaje = Number(proyectoSeleccionado.value.porcentaje_cuota_inicial_min || 0)
      form.cuota_inicial = porcentaje ? form.valor_total * (porcentaje / 100) : 0
      form.valor_restante = form.valor_total - form.cuota_inicial

      // Limpiar separación
      form.valor_separacion = 0
      form.fecha_limite_separacion = null
    }
  }
)

onMounted(() => {
  if (form.tipo_operacion === 'separacion' && proyectoSeleccionado.value) {
    const minimo = Number(proyectoSeleccionado.value.valor_min_separacion || 0)
    form.valor_separacion = minimo
  }
})

watch(
  () => form.id_proyecto,
  (nuevoProyecto) => {
    const p = proyectoSeleccionado.value
    if (!p) {
      plazosDisponibles.value = []
      return
    }

    const plazos = calcularPlazosProyecto(p)
    plazosDisponibles.value = plazos

    // Si el valor guardado ya no existe, lo mantenemos visible
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

  const plazosRestantes = Math.max(plazoTotal - diffMonths, 0)

  return Array.from({ length: plazosRestantes }, (_, i) => i + 1)
}

function submit() {
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
        <!-- Tipo de operación -->
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
          <input
            type="text"
            :value="estadoActualNombre"
            readonly
            class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm bg-gray-100 cursor-not-allowed"
          />
        </div>

        <!-- Bloque económico / separación -->
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
              type="text"
              :value="formatearMoneda(form.cuota_inicial)"
              @input="
                (e) => {
                  form.cuota_inicial = parseMoneda(e.target.value)
                }
              "
              class="w-full border-gray-300 rounded-lg shadow-sm"
            />
            <p v-if="erroresLocales.cuota_inicial" class="text-red-600 text-sm mt-1">
              {{ erroresLocales.cuota_inicial }}
            </p>
          </div>

          <!-- Plazo cuota inicial -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Plazo Cuota Inicial (meses)
            </label>

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

        <template v-else>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Valor de Separación</label>
            <input
              type="text"
              :value="formatearMoneda(form.valor_separacion)"
              @input="
                (e) => {
                  form.valor_separacion = parseMoneda(e.target.value)
                }
              "
              class="w-full border-gray-300 rounded-lg shadow-sm"
            />
            <p v-if="erroresLocales.valor_separacion" class="text-red-600 text-sm mt-1">
              {{ erroresLocales.valor_separacion }}
            </p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1"
              >Fecha Límite de Separación</label
            >
            <input
              type="date"
              v-model="form.fecha_limite_separacion"
              :min="fechaMinimaSeparacion"
              :max="fechaMaximaSeparacion"
              class="w-full border-gray-300 rounded-lg shadow-sm"
            />
            <p v-if="erroresLocales.fecha_limite" class="text-red-600 text-sm mt-1">
              {{ erroresLocales.fecha_limite }}
            </p>
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
          {{ form.processing ? 'Actualizando...' : 'Actualizar Operación' }}
        </button>
      </div>
    </form>
  </VentasLayout>
</template>
