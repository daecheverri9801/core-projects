<template>
  <TopBannerLayout :empleado="empleado" panel-name="Proyectos">
    <div class="space-y-6">
      <!-- Header -->
      <PageHeader
        title="Detalle de Parqueadero"
        kicker="Inventario del proyecto"
        subtitle="Consulta la información general y, si aplica, su asignación."
      />

      <!-- Card -->
      <AppCard padding="md">
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-base font-semibold text-gray-900">Información</h2>
          <div class="flex items-center gap-2">
            <Link
              :href="`/parqueaderos/${parqueadero.id_parqueadero}/edit`"
              class="btn-secondary"
            >
              Editar
            </Link>
            <Link href="/parqueaderos" class="btn-secondary">Volver</Link>
          </div>
        </div>

        <!-- Info grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <InfoItem label="ID" :value="parqueadero.id_parqueadero" />
          <InfoItem label="Número" :value="parqueadero.numero" />
          <InfoItem label="Tipo" :value="parqueadero.tipo" />
          <InfoItem label="Estado" :value="resumen.estado" />

          <!-- Asignación -->
          <template v-if="parqueadero.apartamento">
            <Divider label="Asignación" class="md:col-span-2" />

            <InfoItem label="Apartamento" :value="parqueadero.apartamento.numero" />
            <InfoItem
              label="Tipo Apto"
              :value="
                parqueadero.apartamento.tipo_apartamento?.nombre ??
                parqueadero.apartamento.tipoApartamento?.nombre ??
                '—'
              "
            />
            <InfoItem
              label="Torre"
              :value="parqueadero.apartamento.torre?.nombre_torre || '—'"
            />
            <InfoItem
              label="Piso"
              :value="
                parqueadero.apartamento.piso_torre?.nivel ??
                parqueadero.apartamento.pisoTorre?.nivel ??
                '—'
              "
            />
            <InfoItem
              label="Proyecto"
              :value="parqueadero.apartamento.torre?.proyecto?.nombre || '—'"
            />
            <InfoItem label="Ubicación" :value="resumen.apartamento?.ubicacion || '—'" />
            <InfoItem
              label="Estado inmueble"
              :value="
                parqueadero.apartamento.estado_inmueble?.nombre ??
                parqueadero.apartamento.estadoInmueble?.nombre ??
                '—'
              "
            />
          </template>
        </div>
      </AppCard>

      <FlashMessages />
    </div>
  </TopBannerLayout>
</template>

<script setup>
import TopBannerLayout from '@/Components/TopBannerLayout.vue'
import PageHeader from '@/Components/PageHeader.vue'
import AppCard from '@/Components/AppCard.vue'
import Divider from '@/Components/Divider.vue'
import FlashMessages from '@/Components/FlashMessages.vue'
import { Link } from '@inertiajs/vue3'
import InfoItem from '@/Components/InfoItem.vue'

const props = defineProps({
  parqueadero: { type: Object, required: true },
  resumen: { type: Object, required: true },
  empleado: { type: Object, default: null },
})
</script>

<style scoped>
.btn-secondary {
  @apply inline-flex items-center gap-2 px-3 py-2 rounded-xl
         border border-gray-300 text-brand-700
         hover:bg-brand-50 transition;
}
</style>
