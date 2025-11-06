<template>
  <SidebarBannerLayout :empleado="empleado">
    <template #title>Detalle del Local</template>

    <div class="space-y-6">
      <div class="bg-white rounded-lg border p-4 md:p-6">
        <div class="flex items-center justify-between">
          <h2 class="text-lg font-semibold text-brand-900">Información</h2>
          <div class="flex items-center gap-2">
            <Link :href="`/locales/${local.id_local}/edit`" class="btn-secondary">Editar</Link>
            <Link href="/locales" class="btn-secondary">Volver</Link>
          </div>
        </div>

        <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
          <InfoItem label="ID" :value="local.id_local" />
          <InfoItem label="Número" :value="local.numero" />
          <InfoItem label="Proyecto" :value="local.torre?.proyecto?.nombre || '—'" />
          <InfoItem label="Torre" :value="local.torre?.nombre_torre || '—'" />
          <InfoItem
            label="Piso"
            :value="local?.piso_torre?.nivel ?? local?.pisoTorre?.nivel ?? '—'"
          />
          <InfoItem
            label="Estado"
            :value="local?.estado_inmueble?.nombre ?? local?.estadoInmueble?.nombre ?? '—'"
          />
          <InfoItem label="Área total" :value="formatArea(local.area_total_local)" />
          <InfoItem label="Valor m²" :value="formatCurrency(local.valor_m2)" />
          <InfoItem label="Valor total" :value="formatCurrency(local.valor_total)" />
          <InfoItem label="Ubicación" :value="resumen.ubicacion || '—'" />
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
  local: { type: Object, required: true },
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
