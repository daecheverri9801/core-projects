<template>
  <SidebarBannerLayout :empleado="empleado">
    <template #title>Detalle de Política de Precio</template>

    <div class="bg-white rounded-lg border p-4 md:p-6 max-w-3xl">
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-semibold text-brand-900">Información de la Política</h2>
        <Link
          :href="`/politicas-precio-proyecto/${politica.id_politica_precio}/editar`"
          class="btn-primary"
        >
          <PencilSquareIcon class="w-5 h-5" />
          Editar
        </Link>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <InfoItem label="ID" :value="politica.id_politica_precio" />
        <InfoItem label="Proyecto" :value="politica.proyecto?.nombre || '—'" />
        <InfoItem label="Ventas por Escalón" :value="politica.ventas_por_escalon ?? '—'" />
        <InfoItem
          label="% Aumento"
          :value="politica.porcentaje_aumento ? `${parseFloat(politica.porcentaje_aumento)}%` : '—'"
        />
        <InfoItem label="Aplica Desde" :value="politica.aplica_desde?.split('T')[0] || 'No definida'" />
        <InfoItem label="Estado">
          <span :class="politica.estado ? 'badge-success' : 'badge-warn'">
            {{ politica.estado ? 'Activa' : 'Inactiva' }}
          </span>
        </InfoItem>
      </div>

      <div class="flex items-center gap-3 mt-6 pt-4 border-t">
        <Link href="/politicas-precio-proyecto" class="btn-secondary">Volver al listado</Link>
      </div>

      <FlashMessages />
    </div>
  </SidebarBannerLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import SidebarBannerLayout from '@/Components/SidebarBannerLayout.vue'
import FlashMessages from '@/Components/FlashMessages.vue'
import InfoItem from '@/Components/InfoItem.vue'
import { PencilSquareIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  politica: { type: Object, default: () => ({}) },
  empleado: { type: Object, default: null },
})
</script>

<style scoped>
.btn-primary {
  @apply inline-flex items-center gap-2 px-4 py-2 rounded-md bg-brand-600 text-white hover:bg-brand-700;
}
.btn-secondary {
  @apply inline-flex items-center gap-2 px-4 py-2 rounded-md border border-gray-300 text-gray-700 hover:bg-gray-50;
}
.badge-success {
  @apply inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800;
}
.badge-warn {
  @apply inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-amber-100 text-amber-800;
}
</style>
