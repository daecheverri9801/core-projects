<template>
  <SidebarBannerLayout :empleado="empleado">
    <template #title>Detalle Tipo de Apartamento</template>

    <div class="space-y-6">
      <div class="bg-white rounded-lg border p-4 md:p-6">
        <div class="flex items-center justify-between">
          <h2 class="text-lg font-semibold text-brand-900">Información</h2>
          <div class="flex items-center gap-2">
            <Link
              :href="`/tipos-apartamento/${tipo.id_tipo_apartamento}/edit`"
              class="btn-secondary"
              >Editar</Link
            >
            <Link href="/tipos-apartamento" class="btn-secondary">Volver</Link>
          </div>
        </div>

        <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
          <InfoItem label="Proyecto" :value="tipo.id_proyecto" />
          <InfoItem label="Nombre" :value="tipo.nombre" />
          <InfoItem label="Área construida" :value="formatArea(tipo.area_construida)" />
          <InfoItem label="Área privada" :value="formatArea(tipo.area_privada)" />
          <InfoItem label="Habitaciones" :value="tipo.cantidad_habitaciones ?? '—'" />
          <InfoItem label="Baños" :value="tipo.cantidad_banos ?? '—'" />
          <InfoItem label="Valor m²" :value="formatCurrency(tipo.valor_m2)" />
          <InfoItem label="Valor estimado" :value="formatCurrency(tipo.valor_estimado)" />
        </div>
      </div>

      <div class="bg-white rounded-lg border p-4 md:p-6">
        <h2 class="text-lg font-semibold text-brand-900 mb-3">Apartamentos con este tipo</h2>
        <div class="overflow-x-auto">
          <table class="min-w-full table-auto border-collapse">
            <thead>
              <tr class="bg-brand-50 text-left text-sm text-brand-800">
                <th class="px-3 py-2 border">ID</th>
                <th class="px-3 py-2 border">Número</th>
                <th class="px-3 py-2 border">Torre</th>
                <th class="px-3 py-2 border">Estado</th>
                <th class="px-3 py-2 border w-28">Acciones</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="a in apartamentos" :key="a.id_apartamento" class="border-b">
                <td class="px-3 py-2 border">{{ a.id_apartamento }}</td>
                <td class="px-3 py-2 border">{{ a.numero }}</td>
                <td class="px-3 py-2 border">{{ a.torre || '—' }}</td>
                <td class="px-3 py-2 border">{{ a.estado || '—' }}</td>
                <td class="px-3 py-2 border">
                  <Link
                    :href="`/apartamentos/${a.id_apartamento}`"
                    class="text-brand-700 hover:underline"
                    >Ver</Link
                  >
                </td>
              </tr>
              <tr v-if="apartamentos.length === 0">
                <td colspan="5" class="px-3 py-6 text-center text-sm text-gray-500">
                  Sin apartamentos asociados
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <FlashMessages />
  </SidebarBannerLayout>
</template>

<script setup>
import SidebarBannerLayout from '@/Components/SidebarBannerLayout.vue'
import FlashMessages from '@/Components/FlashMessages.vue'
import { Link } from '@inertiajs/vue3'
import InfoItem from '@/Components/InfoItem.vue'

const props = defineProps({
  proyectos: { type: Array, required: true },
  tipo: { type: Object, required: true },
  apartamentos: { type: Array, default: () => [] },
  empleado: { type: Object, default: null },
})

function formatArea(val) {
  if (val === null || val === undefined) return '—'
  const num = Number(val)
  if (isNaN(num)) return '—'
  return `${num.toLocaleString('es-CO', { maximumFractionDigits: 2 })} m²`
}
function formatCurrency(val) {
  if (val === null || val === undefined) return '—'
  const num = Number(val)
  if (isNaN(num)) return '—'
  return new Intl.NumberFormat('es-CO', {
    style: 'currency',
    currency: 'COP',
    maximumFractionDigits: 0,
  }).format(num)
}
</script>

<style scoped>
.btn-secondary {
  @apply inline-flex items-center gap-2 px-3 py-2 rounded-md border text-brand-700 hover:bg-brand-50;
}
</style>
