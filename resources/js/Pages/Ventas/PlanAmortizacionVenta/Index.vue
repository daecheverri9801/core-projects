<template>
  <VentasLayout :empleado="empleado">
    <Head title="Planes de Amortización" />

    <VentasPageHeader
      title="Planes de Amortización"
      subtitle="Gestiona los planes asociados a ventas"
      :icon="DocumentTextIcon"
    />

    <VentasCard>
      <template #header>
        <div class="flex justify-between items-center">
          <h2 class="text-lg font-semibold text-gray-900">Listado de Planes</h2>
          <Link
            href="/planes-amortizacion-venta/create"
            class="inline-flex items-center gap-2 px-4 py-2 bg-[#f4c430] text-gray-900 rounded-lg font-semibold hover:bg-[#e5b520]"
          >
            <PlusIcon class="w-5 h-5" /> Nuevo Plan
          </Link>
        </div>
      </template>

      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-4 py-3 text-left text-xs font-medium">Plan</th>
              <th class="px-4 py-3 text-left text-xs font-medium">Venta</th>
              <th class="px-4 py-3 text-left text-xs font-medium">Cliente</th>
              <th class="px-4 py-3 text-left text-xs font-medium">Fecha Inicio</th>
              <th class="px-4 py-3 text-center text-xs font-medium">Acciones</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200">
            <tr v-for="plan in planes" :key="plan.id_plan" class="hover:bg-gray-50">
              <td class="px-4 py-4 font-semibold">#{{ plan.id_plan }}</td>
              <td class="px-4 py-4">Venta #{{ plan.id_venta }}</td>
              <td class="px-4 py-4">{{ plan.venta?.cliente?.nombre ?? '—' }}</td>
              <td class="px-4 py-4">{{ formatDate(plan.fecha_inicio) }}</td>

              <td class="px-4 py-4 text-center flex justify-center gap-3">
                <Link :href="`/planes-amortizacion-venta/${plan.id_plan}`" class="text-blue-600">
                  <EyeIcon class="w-5 h-5" />
                </Link>
                <Link :href="`/planes-amortizacion-venta/${plan.id_plan}/edit`" class="text-yellow-600">
                  <PencilIcon class="w-5 h-5" />
                </Link>
                <button @click="eliminar(plan.id_plan)" class="text-red-600">
                  <TrashIcon class="w-5 h-5" />
                </button>
              </td>
            </tr>

            <tr v-if="planes.length === 0">
              <td colspan="5" class="px-4 py-6 text-center text-gray-500">No hay planes registrados</td>
            </tr>
          </tbody>
        </table>
      </div>
    </VentasCard>

    <FlashMessages />
  </VentasLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/inertia-vue3'
import { Inertia } from '@inertiajs/inertia'
import VentasLayout from '@/Components/VentasLayout.vue'
import FlashMessages from '@/Components/FlashMessages.vue'
import VentasPageHeader from '@/Pages/Ventas/Components/VentasPageHeader.vue'
import VentasCard from '@/Pages/Ventas/Components/VentasCard.vue'

import {
  DocumentTextIcon,
  EyeIcon,
  PencilIcon,
  TrashIcon,
  PlusIcon,
} from '@heroicons/vue/24/outline'

const props = defineProps({
  planes: Array,
  empleado: Object,
})

function formatDate(date) {
  if (!date) return '—'
  return new Date(date).toLocaleDateString('es-CO')
}

function eliminar(id) {
  if (confirm('¿Desea eliminar este plan?')) {
    Inertia.delete(`/planes-amortizacion-venta/${id}`)
  }
}
</script>
