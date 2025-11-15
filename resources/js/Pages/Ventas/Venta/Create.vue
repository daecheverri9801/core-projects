<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/inertia-vue3'
import { computed, ref, watch, onMounted } from 'vue'
import { CheckCircleIcon, ExclamationTriangleIcon } from '@heroicons/vue/24/solid'
import VentasLayout from '@/Components/VentasLayout.vue'

const page = usePage()
const empleado = computed(() => page.props.value.auth?.empleado || null)

const props = defineProps({
  clientes: Array,
  empleados: Array,
  proyectos: Array,
  apartamentos: Array,
  locales: Array,
  formasPago: Array,
  estadosInmueble: Array,
  empleado: Object,
  inmueblePrecargado: Object,
})

const inmueblesDisponibles = ref([])

const form = useForm({
  id_empleado: 1,
  documento_cliente: '',
  fecha_venta: new Date().toISOString().slice(0, 10),
  fecha_vencimiento: null, // ← Añade esto
  id_proyecto: '',
  inmueble_tipo: '',
  inmueble_id: '',
  id_forma_pago: '',
  id_estado_inmueble: '',
  valor_base: 0, // ← Añade esto
  iva: 0,
  valor_total: '',
  cuota_inicial: '',
  valor_restante: 0,
  descripcion: '',
})

console.log('👤 Empleado recibido:', props.empleado)
console.log('🆔 ID Empleado en form:', form.id_empleado)

const proyectoSeleccionado = computed(() =>
  props.proyectos.find((p) => p.id_proyecto === parseInt(form.id_proyecto))
)

watch(
  () => form.id_proyecto,
  (nuevoProyecto) => {
    form.inmueble_id = ''
    form.valor_total = ''
    form.cuota_inicial = ''

    if (!nuevoProyecto) {
      inmueblesDisponibles.value = []
      return
    }

    const proyectoId = parseInt(nuevoProyecto)

    // ✅ CORREGIDO: Filtrar por proyecto usando la relación torre
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
        valor: parseFloat(a.valor_final || a.valor_base || 0),
      })),
      ...locs.map((l) => ({
        tipo: 'local',
        id: l.id_local,
        label: `${l.numero}`,
        valor: parseFloat(l.valor_total || l.valor_base || 0),
      })),
    ]

    console.log('Inmuebles disponibles:', inmueblesDisponibles.value) // Para debug
  }
)

onMounted(() => {
  if (props.inmueblePrecargado) {
    const inmueble = props.inmueblePrecargado

    // Determinar el tipo de inmueble y su proyecto
    const esApartamento = inmueble.hasOwnProperty('id_apartamento')
    const proyectoId = esApartamento ? inmueble.torre?.id_proyecto : inmueble.torre?.id_proyecto

    if (proyectoId) {
      form.id_proyecto = proyectoId
      form.inmueble_tipo = esApartamento ? 'apartamento' : 'local'
      form.inmueble_id = esApartamento ? inmueble.id_apartamento : inmueble.id_local
      form.valor_total = inmueble.valor || inmueble.valor_base || 0

      // Calcular cuota inicial si hay proyecto
      const proyecto = props.proyectos.find((p) => p.id_proyecto === proyectoId)
      if (proyecto && proyecto.porcentaje_cuota_inicial_min) {
        form.cuota_inicial = form.valor_total * (proyecto.porcentaje_cuota_inicial_min / 100)
      }
    }
  }
})

watch(
  () => form.inmueble_id,
  (nuevoInmueble) => {
    if (!nuevoInmueble) return

    const inmueble = inmueblesDisponibles.value.find((i) => i.id === parseInt(nuevoInmueble))
    if (!inmueble || !proyectoSeleccionado.value) return

    form.inmueble_tipo = inmueble.tipo
    form.valor_total = parseFloat(inmueble.valor) || 0

    const porcentaje = parseFloat(proyectoSeleccionado.value.porcentaje_cuota_inicial_min) || 0
    form.cuota_inicial = parseFloat((inmueble.valor * (porcentaje / 100)).toFixed(2))

    console.log('✅ Inmueble seleccionado:', inmueble)
    console.log('💰 Valor total asignado:', form.valor_total)
    console.log('💵 Cuota inicial calculada:', form.cuota_inicial)
  }
)

// ✅ CORREGIDO: Validación de campos completos
const camposCompletos = computed(() =>
  Boolean(
    form.documento_cliente &&
      form.id_proyecto &&
      form.inmueble_id &&
      form.id_forma_pago &&
      form.id_estado_inmueble
  )
)

// ✅ Calcular valor restante automáticamente
watch([() => form.valor_total, () => form.cuota_inicial], () => {
  const total = parseFloat(form.valor_total) || 0
  const inicial = parseFloat(form.cuota_inicial) || 0
  form.valor_restante = total - inicial
})

function submit() {
  console.log('📤 Datos a enviar:', {
    id_empleado: form.id_empleado,
    documento_cliente: form.documento_cliente,
    inmueble_tipo: form.inmueble_tipo,
    inmueble_id: form.inmueble_id,
    id_estado_inmueble: form.id_estado_inmueble,
  })

  if (!camposCompletos.value) {
    alert('⚠️ Complete todos los campos obligatorios')
    return
  }

  form.post(route('ventas.store'), {
    onSuccess: () => {
      console.log('✅ Venta creada')
    },
    onError: (errors) => {
      console.error('❌ Errores:', errors)
      alert('Error: ' + JSON.stringify(errors))
    },
  })
}
</script>

<template>
  <VentasLayout :empleado="empleado">
    <Head title="Registrar Venta" />

    <div class="flex justify-between items-center mb-6">
      <h1 class="text-3xl font-bold text-gray-900">Registrar Nueva Venta</h1>
      <Link href="/ventas" class="text-sm text-[#1e3a5f] hover:underline">← Volver</Link>
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

          <!-- alerta de no disponibles -->
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
            <option v-for="fp in formasPago" :key="fp.id_forma_pago" :value="fp.id_forma_pago">
              {{ fp.forma_pago }}
            </option>
          </select>
        </div>

        <!-- Estado del Inmueble -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Estado del Inmueble *
          </label>
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
          {{ form.processing ? 'Guardando...' : 'Guardar Venta' }}
        </button>
      </div>
    </form>
  </VentasLayout>
</template>
