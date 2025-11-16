<template>
  <VentasLayout :empleado="empleado">
    <Head title="Editar Plan de Amortización" />

    <VentasPageHeader
      title="Editar Plan de Amortización"
      :subtitle="`Plan #${plan.id_plan}`"
      :icon="PencilIcon"
    />

    <VentasCard>
      <form @submit.prevent="submit" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

          <!-- Venta -->
          <div>
            <label class="text-sm font-medium">Venta *</label>
            <select v-model="form.id_venta" class="w-full mt-1 border-gray-300 rounded-lg">
              <option v-for="v in ventas" :value="v.id_venta" :key="v.id_venta">
                Venta #{{ v.id_venta }} — {{ v.cliente.nombre }}
              </option>
            </select>
          </div>

          <!-- Tipo -->
          <div>
            <label class="text-sm font-medium">Tipo de Plan</label>
            <input v-model="form.tipo_plan" class="w-full mt-1 border-gray-300 rounded-lg" />
          </div>

          <div>
            <label class="text-sm font-medium">Interés (%)</label>
            <input v-model="form.valor_interes_anual" class="w-full mt-1 border-gray-300 rounded-lg" />
          </div>

          <div>
            <label class="text-sm font-medium">Plazo (meses)</label>
            <input v-model="form.plazo_meses" class="w-full mt-1 border-gray-300 rounded-lg" />
          </div>

          <div>
            <label class="text-sm font-medium">Fecha Inicio</label>
            <input type="date" v-model="form.fecha_inicio" class="w-full mt-1 border-gray-300 rounded-lg" />
          </div>
        </div>

        <div>
          <label class="text-sm font-medium">Observación</label>
          <textarea v-model="form.observacion" class="w-full border-gray-300 rounded-lg" rows="3"></textarea>
        </div>

        <div class="flex justify-end">
          <button
            type="submit"
            class="px-6 py-2 bg-[#1e3a5f] text-white rounded-lg font-semibold"
          >
            Actualizar
          </button>
        </div>

      </form>
    </VentasCard>

    <FlashMessages />
  </VentasLayout>
</template>

<script setup>
import { Head, useForm } from '@inertiajs/inertia-vue3'
import VentasLayout from '@/Components/VentasLayout.vue'
import VentasCard from '@/Pages/Ventas/Components/VentasCard.vue'
import FlashMessages from '@/Components/FlashMessages.vue'
import VentasPageHeader from '@/Pages/Ventas/Components/VentasPageHeader.vue'
import { PencilIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  plan: Object,
  ventas: Array,
  empleado: Object,
})

const form = useForm({
  id_venta: props.plan.id_venta,
  tipo_plan: props.plan.tipo_plan,
  valor_interes_anual: props.plan.valor_interes_anual,
  plazo_meses: props.plan.plazo_meses,
  fecha_inicio: props.plan.fecha_inicio,
  observacion: props.plan.observacion,
})

function submit() {
  form.put(`/planes-amortizacion-venta/${props.plan.id_plan}`)
}
</script>
