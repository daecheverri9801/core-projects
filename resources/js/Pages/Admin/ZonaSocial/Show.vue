<template>
  <SidebarBannerLayout :empleado="empleado">
    <template #title>Detalle de Zona Social</template>

    <div class="bg-white border rounded-lg p-4 md:p-6">
      <div class="flex justify-between items-center mb-4">
        <h2 class="font-semibold text-lg">Información</h2>
        <div class="flex gap-2">
          <Link :href="`/zonas-sociales/${zona.id_zona_social}/edit`" class="btn-secondary"
            >Editar</Link
          >
          <Link href="/zonas-sociales" class="btn-secondary">Volver</Link>
        </div>
      </div>

      <div class="grid md:grid-cols-2 gap-4">
        <InfoItem label="ID" :value="zona.id_zona_social" />
        <InfoItem label="Nombre" :value="zona.nombre" />
        <InfoItem label="Descripción" :value="zona.descripcion || '—'" />
        <InfoItem label="Proyecto" :value="zona.proyecto?.nombre" />
        <InfoItem
          label="Ubicación"
          :value="
            zona.proyecto?.ubicacion?.direccion + ', ' + zona.proyecto?.ubicacion?.ciudad?.nombre
          "
        />
        <InfoItem label="Estado proyecto" :value="zona.proyecto?.estado_proyecto?.nombre" />
      </div>

      <FlashMessages />
    </div>
  </SidebarBannerLayout>
</template>

<script setup>
import SidebarBannerLayout from '@/Components/SidebarBannerLayout.vue'
import FlashMessages from '@/Components/FlashMessages.vue'
import { Link } from '@inertiajs/vue3'
import InfoItem from '@/Components/InfoItem.vue'

const props = defineProps({ zona: Object, empleado: Object })
</script>

<style scoped>
.btn-secondary {
  @apply inline-flex items-center px-3 py-2 rounded-md border text-brand-700 hover:bg-brand-50;
}
</style>
