<template>
  <VentasLayout :empleado="empleado">
    <Head title="Crear Plan de Amortización" />

    <VentasPageHeader
      title="Crear Plan de Amortización"
      subtitle="Define el plan asociado a una venta"
      :icon="CalculatorIcon"
    />

    <VentasCard>
      <form @submit.prevent="submit" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

          <!-- Venta -->
          <div>
            <label class="text-sm font-medium text-gray-700">Venta *</label>
            <select v-model="form.id_venta" class="w-full mt-1 border-gray-300 rounded-lg">
              <option value="">Seleccione...</option>
              <option v-for="v in ventas" :key="v.id_venta" :value="v.id_venta">
                Venta #{{ v.id_venta }} — {{ v.cliente.nombre }}
              </option>
            </select>
            <p class="text-red-600 text-xs">{{ form.errors.id_venta }}</p>
          </div>

          <!-- Tipo Plan -->
          <div>
            <label class="text-sm font-medium text-gray-700">Tipo de Plan</label>
            <input
              type="text"
              v-model="form.tipo_plan"
              class="w-full mt-1 border-gray-300 rounded-lg" />
          </div>

          <!-- Interés -->
          <div>
            <label class="text-sm font-medium text-gray-700">Interés Anual (%)</label>
            <input
              type="number"
              step="0.01"
              v-model="form.valor_interes_anual"
              class="w-full mt-1 border-gray-300 rounded-lg" />
          </div>

          <!-- Plazo -->
          <div>
            <label class="text-sm font-medium text-gray-700">Plazo (meses)</label>
            <input
              type="number"
              v-model="form.plazo_meses"
              class="w-full mt-1 border-gray-300 rounded-lg" />
          </div>

          <!-- Fecha Inicio -->
          <div>
            <label class="text-sm font-medium text-gray-700">Fecha Inicio</label>
            <input
              type="date"
              v-model="form.fecha_inicio"
              class="w-full mt-1 border-gray-300 rounded-lg" />
          </div>
        </div>

        <!-- Observación -->
        <div>
          <label class="text-sm font-medium text-gray-700">Observación</label>
          <textarea
            v-model="form.observacion"
            rows="3"
            class="w-full mt-1 border-gray-300 rounded-lg"
          ></textarea>
        </div>

        <div class="flex justify-end">
          <button
            type="submit"
            class="px-6 py-2 bg-[#1e3a5f] text-white rounded-lg font-semibold hover:bg-[#2c5282]"
          >
            Guardar Plan
          </button>
        </div>
      </form>
    </VentasCard>

    <FlashMessages />
  </VentasLayout>
</template>

<script setup>
import { Head, Link, useForm } from '@inertiajs/inertia-vue3'
import VentasLayout from '@/Components/VentasLayout.vue'
import VentasCard from '@/Pages/Ventas/Components/VentasCard.vue'
import FlashMessages from '@/Components/FlashMessages.vue'
import VentasPageHeader from '@/Pages/Ventas/Components/VentasPageHeader.vue'
import { CalculatorIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  ventas: Array,
  empleado: Object,
})

const form = useForm({
  id_venta: '',
  tipo_plan: '',
  valor_interes_anual: '',
  plazo_meses: '',
  fecha_inicio: '',
  observacion: '',
})

function submit() {
  form.post('/planes-amortizacion-venta')
}
</script>
