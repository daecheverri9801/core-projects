<template>
  <TopBannerLayout :empleado="empleado">
    <div class="space-y-6">
      <PageHeader
        title="Detalle del Piso"
        kicker="Pisos de Torre"
        subtitle="Consulta la información del piso y el resumen de apartamentos."
      >
        <template #actions>
          <div class="flex items-center gap-2">
            <Link
              :href="`/pisos-torre/${piso.id_piso_torre}/edit`"
              class="btn-secondary"
            >
              Editar
            </Link>
            <Link href="/pisos-torre" class="btn-secondary">
              Volver
            </Link>
          </div>
        </template>
      </PageHeader>

      <!-- INFORMACIÓN -->
      <AppCard padding="md" class="max-w-6xl">
        <div class="flex items-center justify-between gap-3">
          <div class="min-w-0">
            <p class="text-xs text-gray-600">Información</p>
            <div class="flex flex-wrap items-center gap-2">
              <h2 class="text-xl font-semibold text-gray-900">Detalle</h2>
              <span
                class="inline-flex items-center rounded-full border border-gray-200 bg-gray-50 px-2.5 py-1 text-xs font-semibold text-gray-700"
              >
                ID: {{ piso.id_piso_torre }}
              </span>
            </div>
            <p class="text-sm text-gray-600">
              {{ piso.torre?.proyecto?.nombre || '—' }} · {{ piso.torre?.nombre_torre || '—' }}
            </p>
          </div>
        </div>

        <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
          <InfoItem label="ID" :value="piso.id_piso_torre" />
          <InfoItem label="Proyecto" :value="piso.torre?.proyecto?.nombre || '—'" />
          <InfoItem label="Torre" :value="piso.torre?.nombre_torre || '—'" />
          <InfoItem label="Nivel" :value="piso.nivel" />
          <InfoItem label="Uso" :value="piso.uso || '—'" />
        </div>
      </AppCard>

      <!-- RESUMEN -->
      <AppCard padding="md" class="max-w-6xl">
        <div class="flex items-center justify-between gap-3">
          <div>
            <p class="text-xs text-gray-600">Resumen</p>
            <h2 class="text-xl font-semibold text-gray-900">Apartamentos</h2>
          </div>
        </div>

        <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
          <!-- Total -->
          <div class="rounded-2xl border border-gray-200 bg-white p-5">
            <p class="text-sm text-gray-600">Total apartamentos</p>
            <p class="mt-1 text-3xl font-bold text-gray-900">
              {{ resumen.total_apartamentos }}
            </p>
          </div>

          <!-- Por estado -->
          <div class="rounded-2xl border border-gray-200 bg-white p-5">
            <p class="text-sm font-semibold text-gray-900">Por estado</p>

            <ul class="mt-3 space-y-2 text-sm">
              <li
                v-for="e in resumen.apartamentos_por_estado"
                :key="e.estado"
                class="flex items-center justify-between gap-3"
              >
                <span class="text-gray-700 truncate">{{ e.estado }}</span>
                <span
                  class="inline-flex min-w-8 items-center justify-center rounded-full border border-brand-200 bg-brand-100 px-2 py-0.5 text-xs font-semibold text-brand-800"
                >
                  {{ e.cantidad }}
                </span>
              </li>

              <li v-if="!resumen.apartamentos_por_estado?.length" class="text-gray-500">
                Sin datos
              </li>
            </ul>
          </div>

          <!-- Por tipo -->
          <div class="rounded-2xl border border-gray-200 bg-white p-5">
            <p class="text-sm font-semibold text-gray-900">Por tipo</p>

            <ul class="mt-3 space-y-2 text-sm">
              <li
                v-for="t in resumen.apartamentos_por_tipo"
                :key="t.tipo"
                class="flex items-center justify-between gap-3"
              >
                <span class="text-gray-700 truncate">{{ t.tipo }}</span>
                <span
                  class="inline-flex min-w-8 items-center justify-center rounded-full border border-brand-200 bg-brand-100 px-2 py-0.5 text-xs font-semibold text-brand-800"
                >
                  {{ t.cantidad }}
                </span>
              </li>

              <li v-if="!resumen.apartamentos_por_tipo?.length" class="text-gray-500">
                Sin datos
              </li>
            </ul>
          </div>
        </div>
      </AppCard>

      <FlashMessages />
    </div>
  </TopBannerLayout>
</template>

<script setup>
import TopBannerLayout from '@/Components/TopBannerLayout.vue'
import FlashMessages from '@/Components/FlashMessages.vue'
import InfoItem from '@/Components/InfoItem.vue'
import AppCard from '@/Components/AppCard.vue'
import PageHeader from '@/Components/PageHeader.vue'
import { Link } from '@inertiajs/vue3'

const props = defineProps({
  piso: { type: Object, required: true },
  resumen: { type: Object, required: true },
  empleado: { type: Object, default: null },
})
</script>

<style scoped>
.btn-secondary {
  @apply inline-flex items-center justify-center gap-2 rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-800
  hover:bg-gray-50 transition;
}
</style>
