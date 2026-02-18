<template>
  <TopBannerLayout :empleado="empleado" panel-name="Proyectos">
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex items-start justify-between">
        <div>
          <p class="text-xs text-gray-500">Zonas Sociales</p>
          <h1 class="text-2xl font-semibold text-gray-900">
            {{ zona.nombre }}
          </h1>
          <p class="text-sm text-gray-600 mt-1">
            Detalle de la zona social asociada al proyecto.
          </p>
        </div>

        <div class="flex items-center gap-2">
          <Link
            :href="`/zonas-sociales/${zona.id_zona_social}/edit`"
            class="btn-secondary"
          >
            Editar
          </Link>
          <Link href="/zonas-sociales" class="btn-secondary">
            Volver
          </Link>
        </div>
      </div>

      <!-- Información -->
      <div class="bg-white rounded-lg border p-4 md:p-6">
        <h2 class="text-lg font-semibold text-brand-900 mb-4">
          Información general
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <InfoItem label="ID" :value="zona.id_zona_social" />
          <InfoItem label="Nombre" :value="zona.nombre" />
          <InfoItem label="Descripción" :value="zona.descripcion || '—'" />
          <InfoItem label="Proyecto" :value="zona.proyecto?.nombre || '—'" />
          <InfoItem
            label="Ubicación"
            :value="
              zona.proyecto?.ubicacion
                ? zona.proyecto.ubicacion.direccion +
                  ', ' +
                  zona.proyecto.ubicacion.ciudad?.nombre
                : '—'
            "
          />
          <InfoItem
            label="Estado del proyecto"
            :value="zona.proyecto?.estado_proyecto?.nombre || '—'"
          />
        </div>
      </div>

      <FlashMessages />
    </div>
  </TopBannerLayout>
</template>

<script setup>
import TopBannerLayout from '@/Components/TopBannerLayout.vue'
import FlashMessages from '@/Components/FlashMessages.vue'
import { Link } from '@inertiajs/vue3'
import InfoItem from '@/Components/InfoItem.vue'

const props = defineProps({
  zona: { type: Object, required: true },
  empleado: { type: Object, default: null },
})
</script>

<style scoped>
.btn-secondary {
  @apply inline-flex items-center gap-2 px-3 py-2 rounded-md border
    text-brand-700 hover:bg-brand-50;
}
</style>
