<!-- resources/js/Pages/Admin/Local/Show.vue -->
<template>
  <TopBannerLayout :empleado="empleado" panel-name="Proyectos">
    <div class="space-y-6">
      <PageHeader
        title="Detalle del local"
        kicker="Locales"
        :subtitle="`Consulta la información y valores del local ${local.numero || ''}`"
      >
        <template #actions>
          <div class="flex items-center gap-2">
            <Link
              :href="`/locales/${local.id_local}/edit`"
              class="rounded-xl bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700 transition"
            >
              Editar
            </Link>
            <Link
              href="/locales"
              class="rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-800 hover:bg-gray-50 transition"
            >
              Volver
            </Link>
          </div>
        </template>
      </PageHeader>

      <!-- Info -->
      <AppCard padding="md">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
          <div class="min-w-0">
            <div class="flex items-center gap-3">
              <span class="rounded-2xl border border-brand-300/60 bg-brand-200 p-2">
                <BuildingStorefrontIcon class="h-5 w-5 text-brand-900" />
              </span>

              <div class="min-w-0">
                <p class="text-lg font-semibold text-gray-900 truncate">
                  {{ local.numero || 'Local' }}
                </p>
                <p class="text-sm text-gray-600">
                  ID: <span class="font-semibold text-gray-900">{{ local.id_local }}</span>
                </p>
              </div>
            </div>

            <div class="mt-3 flex flex-wrap items-center gap-2">
              <span
                class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-semibold"
                :class="estadoBadgeClass(local?.estado_inmueble?.nombre ?? local?.estadoInmueble?.nombre)"
              >
                {{ local?.estado_inmueble?.nombre ?? local?.estadoInmueble?.nombre ?? '—' }}
              </span>

              <span class="text-xs text-gray-500">
                {{ local.torre?.proyecto?.nombre || '—' }} · {{ local.torre?.nombre_torre || '—' }}
              </span>
            </div>
          </div>

          <div class="mt-2 sm:mt-0 w-full sm:w-auto">
            <div class="rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3">
              <p class="text-xs font-medium text-gray-600">Valor total</p>
              <p class="text-lg font-semibold text-gray-900">
                {{ formatCurrency(local.valor_total) }}
              </p>
            </div>
          </div>
        </div>

        <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
          <InfoItem label="Proyecto" :value="local.torre?.proyecto?.nombre || '—'" />
          <InfoItem label="Torre" :value="local.torre?.nombre_torre || '—'" />
          <InfoItem
            label="Piso"
            :value="local?.piso_torre?.nivel ?? local?.pisoTorre?.nivel ?? '—'"
          />
          <InfoItem label="Ubicación" :value="resumen.ubicacion || '—'" />
        </div>

        <div class="mt-6 border-t border-gray-200 pt-6">
          <h3 class="text-sm font-semibold text-gray-900">Valores y métricas</h3>

          <div class="mt-4 grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="rounded-2xl border border-gray-200 p-4">
              <p class="text-xs font-medium text-gray-600">Área total</p>
              <p class="mt-1 text-xl font-semibold text-gray-900">
                {{ formatArea(local.area_total_local) }}
              </p>
            </div>

            <div class="rounded-2xl border border-gray-200 p-4">
              <p class="text-xs font-medium text-gray-600">Valor m²</p>
              <p class="mt-1 text-xl font-semibold text-gray-900">
                {{ formatCurrency(local.valor_m2) }}
              </p>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-gray-50 p-4">
              <p class="text-xs font-medium text-gray-600">Valor total</p>
              <p class="mt-1 text-xl font-semibold text-gray-900">
                {{ formatCurrency(local.valor_total) }}
              </p>
              <p class="mt-1 text-xs text-gray-500">Área total × Valor m²</p>
            </div>
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
import PageHeader from '@/Components/PageHeader.vue'
import AppCard from '@/Components/AppCard.vue'
import { Link } from '@inertiajs/vue3'
import InfoItem from '@/Components/InfoItem.vue'
import { BuildingStorefrontIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  local: { type: Object, required: true },
  resumen: { type: Object, required: true },
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

function estadoBadgeClass(nombre) {
  const n = (nombre || '').toLowerCase()
  if (n.includes('activo') || n.includes('disp') || n.includes('vend')) return 'border-green-200 bg-green-50 text-green-800'
  if (n.includes('reserv') || n.includes('separ')) return 'border-amber-200 bg-amber-50 text-amber-800'
  if (n.includes('inact') || n.includes('bloq') || n.includes('no')) return 'border-gray-200 bg-gray-50 text-gray-700'
  return 'border-brand-200 bg-brand-50 text-brand-800'
}
</script>
