<script setup>
import { Head, Link, useForm, usePage, router } from '@inertiajs/vue3'
import { computed, ref, watch, onMounted, reactive, nextTick } from 'vue'
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
  plazos_disponibles: Array,
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

watch(
  () => form.cuota_inicial_raw,
  (valor) => {
    if (form.tipo_operacion !== 'venta') {
      erroresForm.cuota_inicial = ''
      return
    }

    const min = form.valor_total * (proyectoSeleccionado.value.porcentaje_cuota_inicial_min / 100)

    if (valor < min) {
      erroresForm.cuota_inicial = `La cuota inicial mínima es ${formatearMoneda(min)}`
    } else {
      erroresForm.cuota_inicial = ''
    }
  }
)

watch(
  () => form.valor_separacion,
  (valor) => {
    if (form.tipo_operacion !== 'separacion') {
      erroresForm.valor_separacion = ''
      return
    }

    const min = proyectoSeleccionado.value.valor_min_separacion

    if (valor < min) {
      erroresForm.valor_separacion = `El valor mínimo de separación es $${min.toLocaleString('es-CO')}`
    } else {
      erroresForm.valor_separacion = ''
    }
  }
)

watch(
  () => form.fecha_limite_separacion,
  (fecha) => {
    if (form.tipo_operacion !== 'separacion') {
      erroresForm.fecha_limite_separacion = ''
      return
    }

    const maxDias = proyectoSeleccionado.value.plazo_max_separacion_dias
    const hoy = new Date()
    const fechaLimite = new Date(hoy)
    fechaLimite.setDate(hoy.getDate() + maxDias)

    const f = new Date(fecha)

    if (f > fechaLimite) {
      erroresForm.fecha_limite_separacion = `La fecha máxima permitida es ${fechaLimite.toISOString().slice(0, 10)}`
    } else {
      erroresForm.fecha_limite_separacion = ''
    }
  }
)

const errors = ref({})

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

watch(
  () => form.fecha_limite_separacion,
  (val) => {
    if (!val) return

    const fecha = new Date(val)
    const min = new Date(fechaMinimaSeparacion.value)
    const max = new Date(fechaMaximaSeparacion.value)

    if (fecha < min || fecha > max) {
      errors.value.fecha_limite_separacion = `Debe estar entre ${fechaMinimaSeparacion.value} y ${fechaMaximaSeparacion.value}`
    } else {
      errors.value.fecha_limite_separacion = null
    }
  }
)

// Identificar estados por nombre
const estadoVendidoId = props.estadosInmueble.find(
  (e) => e.nombre.toLowerCase() === 'vendido'
)?.id_estado_inmueble

const estadoSeparadoId = props.estadosInmueble.find(
  (e) => e.nombre.toLowerCase() === 'separado'
)?.id_estado_inmueble

// Cuando cambia tipo de operación → cambiar estado automáticamente
watch(
  () => form.tipo_operacion,
  (tipo) => {
    if (tipo === 'venta') {
      form.id_estado_inmueble = estadoVendidoId
    } else if (tipo === 'separacion') {
      form.id_estado_inmueble = estadoSeparadoId
    } else {
      form.id_estado_inmueble = null
    }
  }
)

onMounted(() => {
  if (form.tipo_operacion === 'venta') {
    form.id_estado_inmueble = estadoVendidoId
  } else if (form.tipo_operacion === 'separacion') {
    form.id_estado_inmueble = estadoSeparadoId
  }
})

// Cuando cambia proyecto, cargar inmuebles disponibles
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

const cuotaInicialDisplay = computed(() =>
  form.cuota_inicial
    ? form.cuota_inicial.toLocaleString('es-CO', {
        style: 'currency',
        currency: 'COP',
        maximumFractionDigits: 0,
      })
    : ''
)

function onCuotaInicialInput(value) {
  const clean = value.replace(/[^\d]/g, '')

  const raw = clean ? Number(clean) : 0

  form.cuota_inicial_raw = raw // valor matemático real
  form.cuota_inicial = raw // valor visual formateado

  if (form.tipo_operacion === 'venta') {
    const total = Number(form.valor_total) || 0
    form.valor_restante = total - raw
  }
}

// Si cambia tipo_operacion, recalcular campos
watch(
  () => form.tipo_operacion,
  (tipo) => {
    if (tipo === 'venta') {
      if (proyectoSeleccionado.value) {
        const porcentaje = parseFloat(proyectoSeleccionado.value.porcentaje_cuota_inicial_min || 0)
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

// Valor restante para venta
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

// Método mejorado para precargar valor de separación
function precargarValorSeparacion() {
  if (form.tipo_operacion === 'separacion' && proyectoSeleccionado.value) {
    const valorMinimo = proyectoSeleccionado.value.valor_min_separacion || 0

    // Solo precargar si el campo está vacío o es 0
    // Esto evita sobreescribir si el usuario ya ingresó un valor
    if (!form.valor_separacion || form.valor_separacion === 0) {
      form.valor_separacion = valorMinimo
    }

    console.log('Valor de separación precargado:', valorMinimo)
  }
}

// Watch mejorado para tipo_operacion
watch(
  () => form.tipo_operacion,
  (nuevoTipo, tipoAnterior) => {
    if (nuevoTipo === 'separacion') {
      // Precargar inmediatamente si hay proyecto seleccionado
      if (proyectoSeleccionado.value) {
        precargarValorSeparacion()
      }

      // Configurar estado automáticamente
      form.id_estado_inmueble = estadoSeparadoId
    } else if (nuevoTipo === 'venta') {
      // Limpiar valor de separación al cambiar a venta
      form.valor_separacion = 0
      form.id_estado_inmueble = estadoVendidoId
    }
  },
  { immediate: true } // Ejecutar inmediatamente al cargar el componente
)

// Watch mejorado para proyecto
watch(proyectoSeleccionado, (nuevoProyecto) => {
  if (nuevoProyecto && form.tipo_operacion === 'separacion') {
    // Usar nextTick para asegurar que el DOM esté actualizado
    nextTick(() => {
      precargarValorSeparacion()
    })
  }
})

// También puedes agregar un botón para "Usar valor mínimo"
function usarValorMinimo() {
  if (proyectoSeleccionado.value) {
    const valorMinimo = proyectoSeleccionado.value.valor_min_separacion || 0
    form.valor_separacion = valorMinimo
  }
}

watch(
  () => form.id_proyecto,
  (nuevoProyecto) => {
    console.group('%cCALCULO DE PLAZOS', 'color:#c33; font-weight: bold')
    console.log('ID Proyecto:', nuevoProyecto)

    const p = proyectoSeleccionado.value
    console.log('Proyecto seleccionado:', p)

    if (p) {
      const inicio = p.fecha_inicio
      const plazoTotal = p.plazo_cuota_inicial_meses
      console.log('Fecha inicio:', inicio)
      console.log('Plazo total:', plazoTotal)

      if (inicio && plazoTotal > 0) {
        const start = new Date(inicio)
        const now = new Date()

        const diffMonths =
          (now.getFullYear() - start.getFullYear()) * 12 + (now.getMonth() - start.getMonth())

        console.log('Meses transcurridos:', diffMonths)

        const plazosRestantes = Math.max(plazoTotal - diffMonths, 0)
        console.log('Plazos restantes calculados:', plazosRestantes)

        // ✔ GENERAR OPCIONES PARA EL SELECT
        plazosDisponibles.value = Array.from({ length: plazosRestantes }, (_, i) => i + 1)
      } else {
        plazosDisponibles.value = []
      }
    } else {
      plazosDisponibles.value = []
    }

    console.groupEnd()
  },
  { immediate: true }
)

function submit() {
  // ✅ SOLUCIÓN ALTERNATIVA: Asignar directamente antes de enviar
  form.cuota_inicial = form.cuota_inicial_raw

  form.post('/ventas', {
    onError: (err) => {
      errors.value = err
    },
    onSuccess: () => {
      console.log('Venta creada exitosamente')
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
            <option value="">Seleccione...</option>
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

        <!-- Estado del Inmueble (automático) -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Estado del Inmueble *
          </label>

          <!-- Input visual (no editable) -->
          <input
            type="text"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-100 text-gray-700"
            :value="estadoNombre"
            disabled
          />

          <!-- Valor real (oculto) -->
          <input type="hidden" v-model="form.id_estado_inmueble" />
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
              type="text"
              :value="formatearMoneda(form.cuota_inicial_raw)"
              @input="onCuotaInicialInput($event.target.value)"
              class="w-full border-gray-300 rounded-lg shadow-sm"
            />

            <!-- ERROR DINÁMICO -->
            <p v-if="erroresForm.cuota_inicial" class="text-red-600 text-sm mt-1">
              {{ erroresForm.cuota_inicial }}
            </p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1"
              >Plazo cuota inicial (meses)</label
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
              type="text"
              :value="formatearMoneda(form.valor_separacion)"
              @input="
                (e) => {
                  form.valor_separacion = parseMoneda(e.target.value)
                }
              "
              class="w-full border-gray-300 rounded-lg shadow-sm"
            />
            <p v-if="erroresForm.valor_separacion" class="text-red-600 text-sm mt-1">
              {{ erroresForm.valor_separacion }}
            </p>
          </div>

          <!-- Fecha Límite Separación -->
          <div v-if="form.tipo_operacion === 'separacion'">
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Fecha Límite Separación
            </label>

            <input
              type="date"
              v-model="form.fecha_limite_separacion"
              :min="fechaMinimaSeparacion"
              :max="fechaMaximaSeparacion"
              class="w-full border-gray-300 rounded-lg shadow-sm"
            />

            <p v-if="erroresForm.fecha_limite_separacion" class="text-red-600 text-sm mt-1">
              {{ erroresForm.fecha_limite_separacion }}
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
          {{ form.processing ? 'Guardando...' : 'Guardar Operación' }}
        </button>
      </div>
    </form>
  </VentasLayout>
</template>
