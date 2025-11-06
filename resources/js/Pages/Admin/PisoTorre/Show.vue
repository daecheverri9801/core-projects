<template>
  <SidebarBannerLayout :empleado="empleado">
    <template #title>Detalle del Piso</template>

    <div class="space-y-6">
      <div class="bg-white rounded-lg border p-4 md:p-6">
        <div class="flex items-center justify-between">
          <h2 class="text-lg font-semibold text-brand-900">Información</h2>
          <div class="flex items-center gap-2">
            <Link :href="`/pisos-torre/${piso.id_piso_torre}/edit`" class="btn-secondary">
              Editar
            </Link>
            <Link href="/pisos-torre" class="btn-secondary">Volver</Link>
          </div>
        </div>

        <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
          <InfoItem label="ID" :value="piso.id_piso_torre" />
          <InfoItem label="Proyecto" :value="piso.torre?.proyecto?.nombre || '—'" />
          <InfoItem label="Torre" :value="piso.torre?.nombre_torre || '—'" />
          <InfoItem label="Nivel" :value="piso.nivel" />
          <InfoItem label="Uso" :value="piso.uso || '—'" />
        </div>
      </div>

      <div class="bg-white rounded-lg border p-4 md:p-6">
        <h2 class="text-lg font-semibold text-brand-900 mb-3">Resumen de Apartamentos</h2>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div class="p-4 border rounded-md">
            <div class="text-sm text-gray-500">Total apartamentos</div>
            <div class="text-2xl font-bold">{{ resumen.total_apartamentos }}</div>
          </div>

          <div class="p-4 border rounded-md md:col-span-1 col-span-1">
            <div class="text-sm font-medium mb-2">Por estado</div>
            <ul class="text-sm space-y-1">
              <li
                v-for="e in resumen.apartamentos_por_estado"
                :key="e.estado"
                class="flex justify-between"
              >
                <span>{{ e.estado }}</span
                ><span class="font-semibold">{{ e.cantidad }}</span>
              </li>
              <li
                v-if="
                  !resumen.apartamentos_por_estado || resumen.apartamentos_por_estado.length === 0
                "
                class="text-gray-500"
              >
                Sin datos
              </li>
            </ul>
          </div>

          <div class="p-4 border rounded-md md:col-span-1 col-span-1">
            <div class="text-sm font-medium mb-2">Por tipo</div>
            <ul class="text-sm space-y-1">
              <li
                v-for="t in resumen.apartamentos_por_tipo"
                :key="t.tipo"
                class="flex justify-between"
              >
                <span>{{ t.tipo }}</span
                ><span class="font-semibold">{{ t.cantidad }}</span>
              </li>
              <li
                v-if="!resumen.apartamentos_por_tipo || resumen.apartamentos_por_tipo.length === 0"
                class="text-gray-500"
              >
                Sin datos
              </li>
            </ul>
          </div>
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
  piso: { type: Object, required: true },
  resumen: { type: Object, required: true },
  empleado: { type: Object, default: null },
})

// Item simple para mostrar pares etiqueta-valor
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
