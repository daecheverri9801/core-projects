<template>
  <SidebarBannerLayout :empleado="empleado">
    <template #title>Detalle del Apartamento</template>

    <div class="space-y-6">
      <div class="bg-white rounded-lg border p-4 md:p-6">
        <div class="flex items-center justify-between">
          <h2 class="text-lg font-semibold text-brand-900">Información</h2>
          <div class="flex items-center gap-2">
            <Link :href="`/apartamentos/${apartamento.id_apartamento}/edit`" class="btn-secondary"
              >Editar</Link
            >
            <Link href="/apartamentos" class="btn-secondary">Volver</Link>
          </div>
        </div>

        <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
          <InfoItem label="ID" :value="apartamento.id_apartamento" />
          <InfoItem label="Número" :value="apartamento.numero" />
          <InfoItem label="Proyecto" :value="apartamento.torre?.proyecto?.nombre || '—'" />
          <InfoItem label="Torre" :value="apartamento.torre?.nombre_torre || '—'" />
          <InfoItem
            label="Piso"
            :value="apartamento.piso_torre?.nivel ?? apartamento.pisoTorre?.nivel ?? '—'"
          />
          <InfoItem
            label="Tipo"
            :value="
              apartamento.tipo_apartamento?.nombre ?? apartamento.tipoApartamento?.nombre ?? '—'
            "
          />
          <InfoItem
            label="Estado"
            :value="
              apartamento.estado_inmueble?.nombre ?? apartamento.estadoInmueble?.nombre ?? '—'
            "
          />
          <InfoItem label="Prima Altura" :value="formatCurrency(apartamento.prima_altura || 0)" />
          <InfoItem label="Valor Total" :value="formatCurrency(apartamento.valor_total || 0)" />
          <InfoItem label="Ubicación" :value="resumen.ubicacion || '—'" />
        </div>
      </div>

      <div class="bg-white rounded-lg border p-4 md:p-6">
        <h2 class="text-lg font-semibold text-brand-900 mb-3">Parqueaderos</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <StatBox label="Total" :value="resumen.parqueaderos.total" />
          <StatBox label="Vehículos" :value="resumen.parqueaderos.vehiculos" />
          <StatBox label="Motos" :value="resumen.parqueaderos.motos" />
        </div>
      </div>
    </div>

    <FlashMessages />
  </SidebarBannerLayout>
</template>

<script setup>
import SidebarBannerLayout from '@/Components/SidebarBannerLayout.vue'
import FlashMessages from '@/Components/FlashMessages.vue'
import { Link } from '@inertiajs/inertia-vue3'

const props = defineProps({
  apartamento: { type: Object, required: true },
  resumen: { type: Object, required: true },
  empleado: { type: Object, default: null },
})

const InfoItem = {
  props: { label: String, value: [String, Number] },
  template: `
    <div>
      <div class="text-sm text-gray-500">{{ label }}</div>
      <div class="text-base font-medium text-brand-900">{{ value }}</div>
    </div>
  `,
}

const StatBox = {
  props: { label: String, value: [String, Number] },
  template: `
    <div class="p-4 border rounded-md">
      <div class="text-sm text-gray-500">{{ label }}</div>
      <div class="text-2xl font-bold">{{ value }}</div>
    </div>
  `,
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
