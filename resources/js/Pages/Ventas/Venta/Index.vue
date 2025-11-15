<script setup>
import { Head, Link } from '@inertiajs/inertia-vue3'
import { Inertia } from '@inertiajs/inertia'
import { onMounted } from 'vue'
import VentasLayout from '@/Components/VentasLayout.vue'
import { EyeIcon, PencilIcon, TrashIcon, PlusIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  ventas: Array,
})

function eliminar(id) {
  if (confirm('¿Desea eliminar esta venta?')) {
    Inertia.delete(`/ventas/${id}`)
  }
}

function formatDate(date) {
  if (!date) return '—'
  return new Date(date).toLocaleDateString('es-CO', {
    year: 'numeric',
    month: 'numeric',
    day: 'numeric',
  })
}
</script>

<template>
  <VentasLayout>
    <Head title="Ventas" />

    <div class="flex justify-between items-center mb-6">
      <h1 class="text-3xl font-bold text-gray-900">Ventas</h1>
      <Link
        href="/ventas/create"
        class="inline-flex items-center gap-2 bg-[#f4c430] text-gray-900 px-4 py-2 rounded-lg font-semibold hover:bg-[#e5b520] transition"
      >
        <PlusIcon class="w-5 h-5" /> Nueva Venta
      </Link>
    </div>

    <div class="bg-white shadow rounded-lg overflow-hidden border border-gray-200">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50 text-gray-700 text-sm font-semibold uppercase tracking-wide">
          <tr>
            <th class="px-4 py-3 text-left">#</th>
            <th class="px-4 py-3 text-left">Cliente</th>
            <th class="px-4 py-3 text-left">Inmueble</th>
            <th class="px-4 py-3 text-left">Proyecto</th>
            <th class="px-4 py-3 text-left">Fecha</th>
            <th class="px-4 py-3 text-left">Valor Total</th>
            <th class="px-4 py-3 text-center">Estado</th>
            <th class="px-4 py-3 text-center">Acciones</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <tr v-for="venta in ventas" :key="venta.id_venta" class="hover:bg-gray-50 transition">
            <td class="px-4 py-3 text-sm text-gray-600">{{ venta.id_venta }}</td>
            <td class="px-4 py-3">{{ venta.documento_cliente ?? '—' }}</td>
            <td class="px-4 py-3">
              <span v-if="venta.apartamento">Apto {{ venta.apartamento.numero }}</span>
              <span v-else-if="venta.local">Local {{ venta.local.numero }}</span>
              <span v-else>—</span>
            </td>
            <td class="px-4 py-3">{{ venta.proyecto?.nombre ?? '—' }}</td>
            <td class="px-4 py-3">{{ formatDate(venta.fecha_venta) }}</td>
            <td class="px-4 py-3 font-semibold text-[#1e3a5f]">
              {{
                new Intl.NumberFormat('es-CO', {
                  style: 'currency',
                  currency: 'COP',
                  maximumFractionDigits: 0,
                }).format(venta.valor_total ?? 0)
              }}
            </td>
            <td class="px-4 py-3 font-semibold text-[#1e3a5f]">
              <span>{{
                venta.apartamento?.estado_inmueble?.nombre || venta.local?.estado_inmueble?.nombre
              }}</span>
            </td>
            <td class="px-4 py-3 flex justify-center gap-3">
              <Link :href="`/ventas/${venta.id_venta}`" class="text-blue-600 hover:text-blue-800"
                ><EyeIcon class="w-5 h-5"
              /></Link>
              <Link
                :href="`/ventas/${venta.id_venta}/edit`"
                class="text-yellow-600 hover:text-yellow-800"
                ><PencilIcon class="w-5 h-5"
              /></Link>
              <button @click="eliminar(venta.id_venta)" class="text-red-600 hover:text-red-800">
                <TrashIcon class="w-5 h-5" />
              </button>
            </td>
          </tr>

          <tr v-if="ventas.length === 0">
            <td colspan="7" class="px-4 py-6 text-center text-gray-500">
              No hay ventas registradas
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </VentasLayout>
</template>
