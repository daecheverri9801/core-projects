<template>
  <SidebarBannerLayout :empleado="empleado">
    <template #title>Detalle de Parqueadero</template>

    <div class="space-y-6">
      <div class="bg-white rounded-lg border p-4 md:p-6">
        <div class="flex items-center justify-between">
          <h2 class="text-lg font-semibold text-brand-900">Información</h2>
          <div class="flex items-center gap-2">
            <Link :href="`/parqueaderos/${parqueadero.id_parqueadero}/edit`" class="btn-secondary"
              >Editar</Link
            >
            <Link href="/parqueaderos" class="btn-secondary">Volver</Link>
          </div>
        </div>

        <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
          <InfoItem label="ID" :value="parqueadero.id_parqueadero" />
          <InfoItem label="Número" :value="parqueadero.numero" />
          <InfoItem label="Tipo" :value="parqueadero.tipo" />
          <InfoItem label="Estado" :value="resumen.estado" />

          <template v-if="parqueadero.apartamento">
            <InfoItem label="Apartamento" :value="parqueadero.apartamento.numero" />
            <InfoItem
              label="Tipo Apto"
              :value="parqueadero.apartamento.tipo_apartamento?.nombre ?? parqueadero.apartamento.tipoApartamento?.nombre ?? '—'
            "
            />
            <InfoItem label="Torre" :value="parqueadero.apartamento.torre?.nombre_torre || '—'" />
            <InfoItem label="Piso" :value="parqueadero.apartamento.piso_torre?.nivel ?? parqueaderoapartamento.pisoTorre?.nivel ?? '—'" />
            <InfoItem
              label="Proyecto"
              :value="parqueadero.apartamento.torre?.proyecto?.nombre || '—'"
            />
            <InfoItem label="Ubicación" :value="resumen.apartamento?.ubicacion || '—'" />
            <InfoItem
              label="Estado inmueble"
              :value="parqueadero.apartamento.estado_inmueble?.nombre ?? parqueadero.apartamento.estadoInmueble?.nombre ?? '—'"
            />
          </template>
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
  parqueadero: { type: Object, required: true },
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
</script>

<style scoped>
.btn-secondary {
  @apply inline-flex items-center gap-2 px-3 py-2 rounded-md border text-brand-700 hover:bg-brand-50;
}
</style>
