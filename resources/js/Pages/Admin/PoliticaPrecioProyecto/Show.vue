<!-- resources/js/Pages/PoliticasPrecioProyecto/Show.vue -->
<template>
  <TopBannerLayout :empleado="empleado">
    <div class="space-y-6">
      <PageHeader
        title="Detalle de política de precio"
        kicker="Políticas de precio"
        subtitle="Consulta los parámetros y el estado actual de la política."
      >
        <template #actions>
          <div class="flex items-center gap-2">
            <Link
              href="/politicas-precio-proyecto"
              class="rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-800 hover:bg-gray-50 transition"
            >
              Volver
            </Link>

            <Link
              :href="`/politicas-precio-proyecto/${politica.id_politica_precio}/editar`"
              class="rounded-xl bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700 transition inline-flex items-center gap-2"
            >
              <PencilSquareIcon class="w-5 h-5" />
              Editar
            </Link>
          </div>
        </template>
      </PageHeader>

      <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Main -->
        <div class="lg:col-span-8 space-y-6">
          <AppCard padding="md">
            <SectionHeader
              title="Información"
              subtitle="Datos principales de la política."
              icon="TagIcon"
            />

            <div class="mt-5 grid grid-cols-1 md:grid-cols-2 gap-4">
              <SummaryTile label="ID">
                <p class="text-sm font-semibold text-gray-900">{{ politica?.id_politica_precio ?? '—' }}</p>
              </SummaryTile>

              <SummaryTile label="Proyecto">
                <p class="text-sm font-semibold text-gray-900 truncate">
                  {{ politica?.proyecto?.nombre || '—' }}
                </p>
              </SummaryTile>

              <SummaryTile label="Ventas por escalón">
                <p class="text-sm font-semibold text-gray-900">
                  {{ politica?.ventas_por_escalon ?? '—' }}
                </p>
              </SummaryTile>

              <SummaryTile label="% Aumento">
                <p class="text-sm font-semibold text-gray-900">
                  {{ porcentajeFmt(politica?.porcentaje_aumento) }}
                </p>
              </SummaryTile>

              <SummaryTile label="Aplica desde">
                <p class="text-sm font-semibold text-gray-900">
                  {{ politica?.aplica_desde?.split('T')[0] || 'No definida' }}
                </p>
              </SummaryTile>

              <SummaryTile label="Estado">
                <span
                  class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-semibold"
                  :class="politica?.estado ? 'border-green-200 bg-green-50 text-green-800' : 'border-amber-200 bg-amber-50 text-amber-800'"
                >
                  {{ politica?.estado ? 'Activa' : 'Inactiva' }}
                </span>
              </SummaryTile>
            </div>
          </AppCard>

          <!-- Mobile actions -->
          <div class="lg:hidden">
            <Link
              :href="`/politicas-precio-proyecto/${politica.id_politica_precio}/editar`"
              class="block w-full text-center rounded-xl bg-brand-600 px-4 py-3 text-sm font-semibold text-white hover:bg-brand-700 transition"
            >
              Editar
            </Link>
            <Link
              href="/politicas-precio-proyecto"
              class="mt-3 block w-full text-center rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm font-semibold text-gray-800 hover:bg-gray-50 transition"
            >
              Volver al listado
            </Link>
          </div>
        </div>

        <!-- Aside -->
        <div class="lg:col-span-4 space-y-6">
          <AppCard padding="md">
            <div class="flex items-start gap-3">
              <span class="mt-0.5 rounded-2xl border border-brand-300/60 bg-brand-200 p-2">
                <InformationCircleIcon class="w-5 h-5 text-brand-900" />
              </span>
              <div class="min-w-0">
                <p class="font-semibold text-gray-900">Notas</p>
                <ul class="mt-2 space-y-2 text-sm text-gray-700 list-disc pl-5">
                  <li>“Aplica desde” define la vigencia para el escalón.</li>
                  <li>Si está inactiva, no debería aplicarse en cálculos.</li>
                  <li>Verifica el proyecto antes de activar cambios.</li>
                </ul>
              </div>
            </div>
          </AppCard>

          <AppCard padding="md">
            <p class="text-sm font-semibold text-gray-900">Validación rápida</p>
            <div class="mt-3 space-y-2 text-sm">
              <InlineStatus :ok="!!politica?.proyecto?.nombre" label="Proyecto asociado" />
              <InlineStatus :ok="politica?.estado === true || politica?.estado === false" label="Estado definido" />
              <InlineStatus :ok="true" label="Detalle disponible" />
            </div>
          </AppCard>
        </div>
      </div>
    </div>
  </TopBannerLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'

import TopBannerLayout from '@/Components/TopBannerLayout.vue'
import AppCard from '@/Components/AppCard.vue'
import PageHeader from '@/Components/PageHeader.vue'
import SectionHeader from '@/Components/SectionHeader.vue'
import InlineStatus from '@/Components/InlineStatus.vue'
import SummaryTile from '@/Components/SummaryTile.vue'

import { TagIcon, InformationCircleIcon, PencilSquareIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  politica: { type: Object, default: () => ({}) },
  empleado: { type: Object, default: null },
})

const politica = computed(() => props.politica)

function porcentajeFmt(val) {
  if (val === null || val === undefined || val === '') return '—'
  const n = Number(val)
  if (Number.isNaN(n)) return '—'
  const s = String(val)
  return s.includes('.') ? `${parseFloat(s)}%` : `${n}%`
}
</script>
