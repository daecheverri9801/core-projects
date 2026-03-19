<template>
  <TopBannerLayout :empleado="empleado">
    <div class="space-y-6">
      <PageHeader
        title="Detalle de política de comisión"
        kicker="Políticas de comisión"
        subtitle="Consulta el proyecto, emnpleado, porcentaje y vigencia de la política."
      >
        <template #actions>
          <div class="flex items-center gap-2">
            <Link
              href="/politicas-comision"
              class="rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-800 hover:bg-gray-50 transition"
            >
              Volver
            </Link>

            <Link
              :href="`/politicas-comision/${politica.id_politica_comision}/edit`"
              class="rounded-xl bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700 transition inline-flex items-center gap-2"
            >
              <PencilSquareIcon class="w-5 h-5" />
              Editar
            </Link>
          </div>
        </template>
      </PageHeader>

      <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <div class="lg:col-span-8 space-y-6">
          <AppCard padding="md">
            <SectionHeader
              title="Información"
              subtitle="Datos principales de la política de comisión."
              icon="TagIcon"
            />

            <div class="mt-5 grid grid-cols-1 md:grid-cols-2 gap-4">
              <SummaryTile label="ID">
                <p class="text-sm font-semibold text-gray-900">
                  {{ politica?.id_politica_comision ?? '—' }}
                </p>
              </SummaryTile>

              <SummaryTile label="Proyecto">
                <p class="text-sm font-semibold text-gray-900 truncate">
                  {{ politica?.proyecto?.nombre || '—' }}
                </p>
              </SummaryTile>

              <SummaryTile label="Empleado">
                <p class="text-sm font-semibold text-gray-900">
                  {{ politica?.empleado?.nombre || '—' }}
                </p>
                <p class="text-xs text-gray-600 mt-1">
                  {{ politica?.empleado?.cargo || '—' }}
                </p>
              </SummaryTile>

              <SummaryTile label="Tipo comisión">
                <p class="text-sm font-semibold text-gray-900">
                  {{ tipoFmt(politica?.tipo_comision) }}
                </p>
              </SummaryTile>

              <SummaryTile label="Porcentaje">
                <p class="text-sm font-semibold text-gray-900">
                  {{ porcentajeFmt(politica?.porcentaje) }}
                </p>
              </SummaryTile>

              <SummaryTile label="Vigente desde">
                <p class="text-sm font-semibold text-gray-900">
                  {{ fechaFmt(politica?.vigente_desde) }}
                </p>
              </SummaryTile>

              <SummaryTile label="Vigente hasta">
                <p class="text-sm font-semibold text-gray-900">
                  {{ fechaFmt(politica?.vigente_hasta) }}
                </p>
              </SummaryTile>
            </div>
          </AppCard>

          <div class="lg:hidden">
            <Link
              :href="`/politicas-comision/${politica.id_politica_comision}/edit`"
              class="block w-full text-center rounded-xl bg-brand-600 px-4 py-3 text-sm font-semibold text-white hover:bg-brand-700 transition"
            >
              Editar
            </Link>
            <Link
              href="/politicas-comision"
              class="mt-3 block w-full text-center rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm font-semibold text-gray-800 hover:bg-gray-50 transition"
            >
              Volver al listado
            </Link>
          </div>
        </div>

        <div class="lg:col-span-4 space-y-6">
          <AppCard padding="md">
            <div class="flex items-start gap-3">
              <span class="mt-0.5 rounded-2xl border border-brand-300/60 bg-brand-200 p-2">
                <InformationCircleIcon class="w-5 h-5 text-brand-900" />
              </span>
              <div class="min-w-0">
                <p class="font-semibold text-gray-900">Notas</p>
                <ul class="mt-2 space-y-2 text-sm text-gray-700 list-disc pl-5">
                  <li>La vigencia debe alinearse con las fechas del proyecto.</li>
                  <li>El tipo de comisión define si aplica sobre venta propia o del equipo.</li>
                  <li>Valida el empleado antes de guardar cambios.</li>
                </ul>
              </div>
            </div>
          </AppCard>

          <AppCard padding="md">
            <p class="text-sm font-semibold text-gray-900">Validación rápida</p>
            <div class="mt-3 space-y-2 text-sm">
              <InlineStatus :ok="!!politica?.proyecto?.nombre" label="Proyecto asociado" />
              <InlineStatus :ok="!!politica?.empleado?.nombre" label="Empleado asociado" />
              <InlineStatus :ok="!!politica?.tipo_comision" label="Tipo definido" />
              <InlineStatus
                :ok="politica?.porcentaje !== null && politica?.porcentaje !== undefined"
                label="Porcentaje definido"
              />
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

function tipoFmt(tipo) {
  if (!tipo) return '—'
  if (tipo === 'venta_propia') return 'Venta propia'
  if (tipo === 'venta_equipo') return 'Venta del equipo'
  return tipo
}

function fechaFmt(val) {
  if (!val) return '—'
  return String(val).split('T')[0].split(' ')[0]
}
</script>
