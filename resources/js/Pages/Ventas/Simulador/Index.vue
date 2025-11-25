<template>
  <VentasLayout>
    <Head title="Simulador de Cuotas" />

    <div class="max-w-3xl mx-auto bg-white rounded-xl shadow p-6 border border-gray-200">
      <Link href="/catalogo" class="text-sm text-[#1e3a5f] hover:underline">← Volver</Link>

      <h1 class="text-3xl font-bold text-gray-900 mb-6">Simulador de Cuotas</h1>

      <div class="space-y-4">
        <p class="text-gray-700">
          <strong>Inmueble:</strong>
          {{ tipo === 'apartamento' ? 'Apartamento ' : 'Local ' }}
          {{ inmueble.numero }}
        </p>

        <p class="text-gray-700">
          <strong>Proyecto:</strong>
          {{ inmueble.torre?.proyecto?.nombre }}
        </p>

        <p class="text-gray-700"><strong>Valor Final:</strong> {{ formatCurrency(valor_final) }}</p>

        <p class="text-gray-700">
          <strong>Cuota Inicial ({{ porcentaje }}%):</strong>
          {{ formatCurrency(cuota_inicial) }}
        </p>
      </div>

      <div class="mt-6">
        <label class="block text-sm font-medium text-gray-700 mb-1">Meses</label>
        <select v-model.number="meses" class="w-full border-gray-300 rounded-lg shadow-sm">
          <option :value="1">1 meses</option>
          <option :value="2">2 meses</option>
          <option :value="3">3 meses</option>
          <option :value="4">4 meses</option>
          <option :value="5">5 meses</option>
          <option :value="6">6 meses</option>
          <option :value="7">7 meses</option>
          <option :value="8">8 meses</option>
          <option :value="9">9 meses</option>
          <option :value="10">10 meses</option>
          <option :value="11">11 meses</option>
          <option :value="12">12 meses</option>
          <option :value="13">13 meses</option>
          <option :value="14">14 meses</option>
          <option :value="15">15 meses</option>
          <option :value="16">16 meses</option>
          <option :value="17">17 meses</option>
          <option :value="18">18 meses</option>
          <option :value="19">19 meses</option>
          <option :value="20">20 meses</option>
        </select>
      </div>

      <div class="mt-6 bg-gray-50 rounded-lg p-4 border border-gray-200">
        <h2 class="text-xl font-semibold text-gray-900 mb-3">Resultado</h2>

        <p class="text-gray-700">
          <strong>Cuota Inicial:</strong> {{ formatCurrency(cuota_inicial) }}
        </p>
        <p class="text-gray-700"><strong>Meses:</strong> {{ meses }}</p>

        <p class="text-gray-900 text-2xl font-bold mt-4">
          Cuota Mensual: {{ formatCurrency(cuotaMensual) }}
        </p>
      </div>
    </div>
  </VentasLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import VentasLayout from '@/Components/VentasLayout.vue'

const props = defineProps({
  tipo: String,
  inmueble: Object,
  valor_final: Number,
  porcentaje: Number,
  cuota_inicial: Number,
})

const meses = ref(12)

const cuotaMensual = computed(() =>
  props.cuota_inicial > 0 ? props.cuota_inicial / meses.value : 0
)

function formatCurrency(v) {
  return new Intl.NumberFormat('es-CO', {
    style: 'currency',
    currency: 'COP',
    minimumFractionDigits: 0,
  }).format(v || 0)
}
</script>
