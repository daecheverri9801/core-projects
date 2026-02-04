<template>
  <SidebarBannerLayout :empleado="empleado">
    <div class="space-y-6">
      <!-- Header -->
      <PageHeader
        title="Detalle del apartamento"
        kicker="Inventario del proyecto"
        :subtitle="`Número ${apartamento.numero || '—'} · ID ${apartamento.id_apartamento}`"
      >
        <template #actions>
          <ButtonSecondary href="/apartamentos">Volver</ButtonSecondary>
          <ButtonPrimary :href="`/apartamentos/${apartamento.id_apartamento}/edit`">
            Editar
          </ButtonPrimary>
        </template>
      </PageHeader>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Info -->
        <div class="lg:col-span-2 space-y-6">
          <AppCard padding="md">
            <div class="flex items-start justify-between gap-4">
              <div class="min-w-0">
                <h3 class="text-sm font-semibold text-gray-900">Información</h3>
                <p class="text-xs text-gray-500 mt-1">
                  Datos generales del apartamento y su ubicación dentro del proyecto.
                </p>
              </div>

              <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-semibold"
                :class="estadoBadgeClass(apartamento.estado_inmueble?.nombre ?? apartamento.estadoInmueble?.nombre)"
              >
                {{ apartamento.estado_inmueble?.nombre ?? apartamento.estadoInmueble?.nombre ?? '—' }}
              </span>
            </div>

            <div class="mt-5 grid grid-cols-1 md:grid-cols-2 gap-4">
              <InfoItem label="ID" :value="apartamento.id_apartamento" />
              <InfoItem label="Número" :value="apartamento.numero" />
              <InfoItem label="Proyecto" :value="apartamento.torre?.proyecto?.nombre || '—'" />
              <InfoItem label="Torre" :value="apartamento.torre?.nombre_torre || '—'" />
              <InfoItem
                label="Piso"
                :value="apartamento.piso_torre?.nivel ?? apartamento.pisoTorre?.nivel ?? '—'"
              />
              <InfoItem
                label="Tipo"
                :value="apartamento.tipo_apartamento?.nombre ?? apartamento.tipoApartamento?.nombre ?? '—'"
              />
              <InfoItem label="Ubicación" :value="resumen.ubicacion || '—'" />
            </div>
          </AppCard>

          <AppCard padding="md">
            <div class="flex items-start justify-between gap-4">
              <div class="min-w-0">
                <h3 class="text-sm font-semibold text-gray-900">Valores</h3>
                <p class="text-xs text-gray-500 mt-1">
                  Cálculo de precio: tipo + prima altura + política.
                </p>
              </div>
            </div>

            <div class="mt-5 grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="rounded-2xl border border-gray-200 p-4">
                <p class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Valor base</p>
                <p class="mt-2 text-2xl font-semibold text-gray-900">
                  {{ formatCurrency(apartamento.valor_total || 0) }}
                </p>
                <p class="mt-1 text-xs text-gray-500">Valor estimado del tipo (sin prima ni política).</p>
              </div>

              <div class="rounded-2xl border border-gray-200 p-4">
                <p class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Prima altura</p>
                <p class="mt-2 text-2xl font-semibold text-gray-900">
                  {{ formatCurrency(apartamento.prima_altura || 0) }}
                </p>
                <p class="mt-1 text-xs text-gray-500">Depende del piso y del nivel base.</p>
              </div>

              <div class="rounded-2xl border border-gray-200 p-4">
                <p class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Ajuste por política</p>
                <p class="mt-2 text-2xl font-semibold text-gray-900">
                  {{ formatCurrency(apartamento.valor_politica || 0) }}
                </p>
                <p class="mt-1 text-xs text-gray-500">Ajuste aplicado según la política del proyecto.</p>
              </div>

              <div class="rounded-2xl border border-gray-200 p-4">
                <p class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Valor final</p>
                <p class="mt-2 text-2xl font-semibold text-gray-900">
                  {{ formatCurrency(apartamento.valor_final || 0) }}
                </p>
                <p class="mt-1 text-xs text-gray-500">Suma total final del apartamento.</p>
              </div>
            </div>
          </AppCard>
        </div>

        <!-- Summary / Stats -->
        <div class="space-y-6">
          <AppCard padding="md">
            <div class="flex items-start justify-between gap-4">
              <div class="min-w-0">
                <h3 class="text-sm font-semibold text-gray-900">Parqueaderos</h3>
                <p class="text-xs text-gray-500 mt-1">Resumen por tipo.</p>
              </div>
            </div>

            <div class="mt-4 grid grid-cols-1 gap-3">
              <StatCard label="Total" :value="resumen.parqueaderos.total" icon="Squares2X2Icon" />
              <StatCard label="Vehículos" :value="resumen.parqueaderos.vehiculos" icon="TruckIcon" />
              <StatCard label="Motos" :value="resumen.parqueaderos.motos" icon="BoltIcon" />
            </div>
          </AppCard>
        </div>
      </div>

      <FlashMessages />
    </div>
  </SidebarBannerLayout>
</template>

<script setup>
import SidebarBannerLayout from '@/Components/SidebarBannerLayout.vue'
import FlashMessages from '@/Components/FlashMessages.vue'
import InfoItem from '@/Components/InfoItem.vue'

import AppCard from '@/Components/AppCard.vue'
import PageHeader from '@/Components/PageHeader.vue'
import ButtonPrimary from '@/Components/ButtonPrimary.vue'
import ButtonSecondary from '@/Components/ButtonSecondary.vue'

import { Squares2X2Icon, TruckIcon, BoltIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  apartamento: { type: Object, required: true },
  resumen: { type: Object, required: true },
  empleado: { type: Object, default: null },
})

const StatCard = {
  props: { label: String, value: [String, Number], icon: String },
  components: { Squares2X2Icon, TruckIcon, BoltIcon },
  methods: {
    IconCmp() {
      return this.icon === 'TruckIcon'
        ? TruckIcon
        : this.icon === 'BoltIcon'
          ? BoltIcon
          : Squares2X2Icon
    },
  },
  template: `
    <div class="rounded-2xl border border-gray-200 p-4">
      <div class="flex items-center gap-3">
        <span class="rounded-2xl border border-brand-300/60 bg-brand-200 p-2">
          <component :is="IconCmp()" class="w-5 h-5 text-brand-900" />
        </span>
        <div class="min-w-0">
          <p class="text-xs font-semibold text-gray-600 uppercase tracking-wide">{{ label }}</p>
          <p class="mt-1 text-2xl font-semibold text-gray-900">{{ value ?? 0 }}</p>
        </div>
      </div>
    </div>
  `,
}

function estadoBadgeClass(nombre) {
  const n = (nombre || '').toLowerCase()
  if (n.includes('dispon') || n.includes('libre')) return 'border-green-200 bg-green-50 text-green-800'
  if (n.includes('vend') || n.includes('separ')) return 'border-blue-200 bg-blue-50 text-blue-800'
  if (n.includes('bloq') || n.includes('paus') || n.includes('susp')) return 'border-amber-200 bg-amber-50 text-amber-800'
  return 'border-gray-200 bg-gray-50 text-gray-700'
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
