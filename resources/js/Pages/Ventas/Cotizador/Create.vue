<template>
  <VentasLayout :empleado="empleado">
    <Head title="Nueva Cotización" />

    <div class="flex justify-between items-center mb-6">
      <h1 class="text-3xl font-bold text-gray-900">Cotizador</h1>
      <Link href="/cotizador" class="text-sm text-[#1e3a5f] hover:underline">← Volver</Link>
    </div>

    <div class="bg-white rounded-xl shadow p-6 space-y-6 border border-gray-200">

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        <!-- Cliente -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Cliente</label>
          <select v-model="form.documento_cliente" class="w-full border-gray-300 rounded-lg shadow-sm">
            <option value="">Seleccione...</option>
            <option v-for="c in clientes" :key="c.documento" :value="c.documento">
              {{ c.nombre }}
            </option>
          </select>
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
          <label class="block text-sm font-medium text-gray-700 mb-1">Inmueble</label>
          <select v-model="form.inmueble_id" :disabled="!inmueblesFiltrados.length"
                  class="w-full border-gray-300 rounded-lg shadow-sm">
            <option value="">Seleccione...</option>
            <option v-for="i in inmueblesFiltrados" :key="i.id" :value="i.id">
              {{ i.label }}
            </option>
          </select>
        </div>

        <!-- Forma de Pago -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Forma de Pago</label>
          <select v-model="form.id_forma_pago" class="w-full border-gray-300 rounded-lg shadow-sm">
            <option value="">Seleccione...</option>
            <option v-for="f in formasPago" :key="f.id_forma_pago" :value="f.id_forma_pago">
              {{ f.forma_pago }}
            </option>
          </select>
        </div>

      </div>

      <!-- DESGLOSE -->
      <div v-if="inmuebleSeleccionado" class="bg-gray-50 p-4 rounded-lg border border-gray-200">

        <h3 class="text-lg font-semibold text-gray-900 mb-3">Desglose de Precio</h3>

        <div class="space-y-2">
          <p class="flex justify-between text-gray-700">
            <span>Valor Base:</span>
            <span>{{ formatCurrency(inmuebleSeleccionado.valor_base) }}</span>
          </p>
          <p class="flex justify-between text-gray-700" v-if="inmuebleSeleccionado.prima_altura > 0">
            <span>Prima por Altura:</span>
            <span>{{ formatCurrency(inmuebleSeleccionado.prima_altura) }}</span>
          </p>
          <p class="flex justify-between text-gray-700" v-if="inmuebleSeleccionado.valor_politica > 0">
            <span>Ajuste Política:</span>
            <span>{{ formatCurrency(inmuebleSeleccionado.valor_politica) }}</span>
          </p>
          <p class="flex justify-between font-bold text-gray-900 border-t pt-2">
            <span>Precio Final:</span>
            <span>{{ formatCurrency(inmuebleSeleccionado.valor_comercial) }}</span>
          </p>
        </div>

        <!-- Cuota Inicial -->
        <div class="mt-4">
          <label class="block text-sm font-medium text-gray-700 mb-1">Cuota Inicial</label>
          <input
            v-model.number="form.cuota_inicial"
            type="number"
            class="w-full border-gray-300 rounded-lg shadow-sm"
          />
        </div>

      </div>

      <!-- BOTÓN -->
      <div class="flex justify-end">
        <button
          @click="generarCotizacion"
          :disabled="!camposValidos"
          class="px-6 py-2 bg-[#1e3a5f] text-white rounded-lg font-semibold hover:bg-[#2c5282] disabled:bg-gray-300 disabled:text-gray-600 transition"
        >
          Generar Cotización
        </button>
      </div>

    </div>
  </VentasLayout>
</template>

<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import VentasLayout from '@/Components/VentasLayout.vue'

const props = defineProps({
  clientes: Array,
  proyectos: Array,
  apartamentos: Array,
  locales: Array,
  formasPago: Array,
  empleado: Object,
})

const form = ref({
  documento_cliente: '',
  id_proyecto: '',
  inmueble_id: '',
  inmueble_tipo: '',
  id_forma_pago: '',
  cuota_inicial: 0,
})

// Filtrar inmuebles según proyecto
const inmueblesFiltrados = computed(() => {
  if (!form.value.id_proyecto) return []

  const apts = props.apartamentos
    .filter(a => a.torre?.id_proyecto === parseInt(form.value.id_proyecto))
    .map(a => ({
      id: a.id_apartamento,
      tipo: 'apartamento',
      label: `Apto ${a.numero}`,
      valor_base: a.valor_final,
      prima_altura: a.prima_altura,
      valor_politica: a.valor_politica,
      valor_comercial: a.valor_final,
    }))

  const locs = props.locales
    .filter(l => l.torre?.id_proyecto === parseInt(form.value.id_proyecto))
    .map(l => ({
      id: l.id_local,
      tipo: 'local',
      label: `Local ${l.numero}`,
      valor_base: l.valor_base ?? l.valor_total,
      prima_altura: 0,
      valor_politica: 0,
      valor_comercial: l.valor_comercial,
    }))

  return [...apts, ...locs]
})

const inmuebleSeleccionado = computed(() =>
  inmueblesFiltrados.value.find(i => i.id === parseInt(form.value.inmueble_id))
)

const camposValidos = computed(() =>
    form.value.documento_cliente &&
    form.value.id_proyecto &&
    form.value.inmueble_id &&
    form.value.id_forma_pago
)

function generarCotizacion() {
  alert('Cotización generada (aún no guardamos en BD)')
}

function formatCurrency(val) {
  return new Intl.NumberFormat('es-CO', {
    style: 'currency',
    currency: 'COP',
    minimumFractionDigits: 0,
  }).format(val || 0)
}
</script>
