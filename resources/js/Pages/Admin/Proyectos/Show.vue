<!-- resources/js/Pages/Proyectos/Show.vue -->
<template>
  <SidebarBannerLayout :empleado="empleado">
    <div class="space-y-6">
      <!-- Header -->
      <PageHeader
        :title="`Proyecto: ${proyecto?.nombre ?? '—'}`"
        kicker="Proyectos"
        subtitle="Consulta la configuración general y las políticas de precio asociadas."
      >
        <template #actions>
          <div class="flex items-center gap-2">
            <Link
              href="/proyectos"
              class="rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-800 hover:bg-gray-50 transition"
            >
              Volver
            </Link>

            <Link
              :href="`/proyectos/${proyecto.id_proyecto}/edit`"
              class="rounded-xl bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700 transition inline-flex items-center gap-2"
            >
              <PencilIcon class="w-5 h-5" />
              Editar
            </Link>
          </div>
        </template>
      </PageHeader>

      <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Main -->
        <div class="lg:col-span-8 space-y-6">
          <!-- Resumen -->
          <AppCard padding="md">
            <SectionHeader
              title="Resumen"
              subtitle="Información principal del proyecto."
              icon="FolderIcon"
            />

            <div class="mt-5 grid grid-cols-1 md:grid-cols-2 gap-4">
              <SummaryTile label="Estado">
                <span
                  class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-semibold"
                  :class="estadoBadgeClass(proyecto?.estado_proyecto?.nombre)"
                >
                  {{ proyecto?.estado_proyecto?.nombre || '—' }}
                </span>
              </SummaryTile>

              <SummaryTile label="Ubicación">
                <p class="text-sm font-semibold text-gray-900 truncate">
                  {{ proyecto?.ubicacion?.direccion || '—' }}
                </p>
                <p class="text-xs text-gray-600 truncate">
                  {{ proyecto?.ubicacion?.ciudad?.nombre || '—' }}
                </p>
              </SummaryTile>

              <SummaryTile label="Fecha inicio">
                <p class="text-sm font-semibold text-gray-900">{{ proyecto?.fecha_inicio || '—' }}</p>
              </SummaryTile>

              <SummaryTile label="Fecha finalización">
                <p class="text-sm font-semibold text-gray-900">{{ proyecto?.fecha_finalizacion || '—' }}</p>
              </SummaryTile>
            </div>

            <div class="mt-5">
              <p class="text-xs font-semibold text-gray-600 uppercase tracking-wider">Descripción</p>
              <p class="mt-2 text-sm text-gray-900 whitespace-pre-line">
                {{ proyecto?.descripcion || '—' }}
              </p>
            </div>
          </AppCard>

          <!-- Presupuesto y métricas -->
          <AppCard padding="md">
            <SectionHeader
              title="Presupuesto y métricas"
              subtitle="Valores base de control financiero y dimensionamiento."
              icon="BanknotesIcon"
            />

            <div class="mt-5 grid grid-cols-1 md:grid-cols-3 gap-4">
              <MetricTile
                label="Presupuesto inicial"
                :value="formatCurrency(proyecto?.presupuesto_inicial)"
              />
              <MetricTile
                label="Presupuesto final"
                :value="formatCurrency(proyecto?.presupuesto_final)"
              />
              <MetricTile
                label="Metros construidos"
                :value="proyecto?.metros_construidos ?? '—'"
                suffix="m²"
              />
            </div>
          </AppCard>

          <!-- Unidades -->
          <AppCard padding="md">
            <SectionHeader
              title="Unidades y parqueaderos"
              subtitle="Parámetros de inventario base."
              icon="BuildingOffice2Icon"
            />

            <div class="mt-5 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
              <MetricTile label="Locales" :value="proyecto?.cantidad_locales ?? '—'" />
              <MetricTile label="Apartamentos" :value="proyecto?.cantidad_apartamentos ?? '—'" />
              <MetricTile label="Parq. vehículo" :value="proyecto?.cantidad_parqueaderos_vehiculo ?? '—'" />
              <MetricTile label="Parq. moto" :value="proyecto?.cantidad_parqueaderos_moto ?? '—'" />
            </div>
          </AppCard>

          <!-- Estructura -->
          <AppCard padding="md">
            <SectionHeader
              title="Estructura"
              subtitle="Estrato, pisos y torres."
              icon="Squares2X2Icon"
            />

            <div class="mt-5 grid grid-cols-1 md:grid-cols-3 gap-4">
              <MetricTile label="Estrato" :value="proyecto?.estrato ?? '—'" />
              <MetricTile label="Número de pisos" :value="proyecto?.numero_pisos ?? '—'" />
              <MetricTile label="Número de torres" :value="proyecto?.numero_torres ?? '—'" />
            </div>
          </AppCard>

          <!-- Financiación -->
          <AppCard padding="md">
            <SectionHeader
              title="Financiación"
              subtitle="Parámetros de separación y cuota inicial."
              icon="CreditCardIcon"
            />

            <div class="mt-5 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
              <MetricTile
                label="% Cuota inicial mínima"
                :value="proyecto?.porcentaje_cuota_inicial_min ?? '—'"
                suffix="%"
              />
              <MetricTile
                label="Valor mínimo separación"
                :value="formatCurrency(proyecto?.valor_min_separacion)"
              />
              <MetricTile
                label="Plazo cuota inicial"
                :value="proyecto?.plazo_cuota_inicial_meses ?? '—'"
                suffix="meses"
              />
              <MetricTile
                label="Plazo máx. separación"
                :value="proyecto?.plazo_max_separacion_dias ?? '—'"
                suffix="días"
              />
            </div>
          </AppCard>

          <!-- Prima altura -->
          <AppCard padding="md">
            <SectionHeader
              title="Prima altura"
              subtitle="Configuración de prima por altura del proyecto."
              icon="ArrowTrendingUpIcon"
            />

            <div class="mt-5 grid grid-cols-1 md:grid-cols-3 gap-4">
              <MetricTile label="Prima base" :value="formatCurrency(proyecto?.prima_altura_base)" />
              <MetricTile label="Incremento por piso" :value="formatCurrency(proyecto?.prima_altura_incremento)" />
              <MetricTile
                label="Estado"
                :value="proyecto?.prima_altura_activa ? 'Activa' : 'Inactiva'"
              />
            </div>
          </AppCard>

          <!-- Políticas de Precio -->
          <AppCard padding="md">
            <div class="flex items-start justify-between gap-4">
              <SectionHeader
                title="Políticas de precio"
                subtitle="Escalones y aumentos configurados para ventas."
                icon="TagIcon"
              />
              <Link
                :href="`/politicas-precio-proyecto/crear?proyecto=${proyecto.id_proyecto}`"
                class="rounded-xl bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700 transition inline-flex items-center gap-2"
              >
                <PlusIcon class="w-5 h-5" />
                Nueva política
              </Link>
            </div>

            <div class="mt-5 space-y-3" v-if="politicas.length">
              <div
                v-for="politica in politicas"
                :key="politica.id_politica_precio"
                class="rounded-2xl border border-gray-200 bg-white p-4 hover:bg-gray-50 transition"
              >
                <div class="flex items-start justify-between gap-4">
                  <div class="min-w-0 flex-1">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                      <KpiLine label="Ventas/Escalón" :value="politica.ventas_por_escalon ?? '—'" />
                      <KpiLine
                        label="% Aumento"
                        :value="politica.porcentaje_aumento != null ? `${parseFloat(politica.porcentaje_aumento)}%` : '—'"
                      />
                      <KpiLine
                        label="Aplica desde"
                        :value="politica.aplica_desde ? politica.aplica_desde.split('T')[0] : 'No definida'"
                      />
                      <div>
                        <p class="text-xs text-gray-500">Estado</p>
                        <span
                          class="mt-1 inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-semibold"
                          :class="politica.estado ? 'border-green-200 bg-green-50 text-green-800' : 'border-amber-200 bg-amber-50 text-amber-800'"
                        >
                          {{ politica.estado ? 'Activa' : 'Inactiva' }}
                        </span>
                      </div>
                    </div>
                  </div>

                  <div class="flex items-center gap-2">
                    <IconButton
                      :href="`/politicas-precio-proyecto/${politica.id_politica_precio}`"
                      icon="EyeIcon"
                      title="Ver detalle"
                      variant="info"
                    />
                    <IconButton
                      :href="`/politicas-precio-proyecto/${politica.id_politica_precio}/editar`"
                      icon="PencilIcon"
                      title="Editar"
                      variant="warn"
                    />
                  </div>
                </div>
              </div>
            </div>

            <EmptyState
              v-else
              title="Sin políticas de precio"
              message="No hay políticas configuradas para este proyecto."
              :action-href="`/politicas-precio-proyecto/crear?proyecto=${proyecto.id_proyecto}`"
              action-text="Crear primera política"
            />
          </AppCard>
        </div>

        <!-- Aside -->
        <div class="lg:col-span-4 space-y-6">
          <AppCard padding="md">
            <div class="flex items-start gap-3">
              <span class="mt-0.5 rounded-2xl border border-brand-300/60 bg-brand-200 p-2">
                <InformationCircleIcon class="w-5 h-5 text-brand-900" />
              </span>
              <div class="min-w-0">
                <p class="font-semibold text-gray-900">Referencia</p>
                <div class="mt-2 text-sm text-gray-700 space-y-1">
                  <p>
                    <span class="text-gray-500">ID:</span>
                    <span class="font-semibold text-gray-900">{{ proyecto?.id_proyecto ?? '—' }}</span>
                  </p>
                  <p class="truncate">
                    <span class="text-gray-500">Nombre:</span>
                    <span class="font-semibold text-gray-900">{{ proyecto?.nombre ?? '—' }}</span>
                  </p>
                  <p class="truncate">
                    <span class="text-gray-500">Ubicación:</span>
                    <span class="font-semibold text-gray-900">{{ ubicacionTexto }}</span>
                  </p>
                </div>
              </div>
            </div>
          </AppCard>

          <AppCard padding="md">
            <p class="text-sm font-semibold text-gray-900">Validación rápida</p>
            <div class="mt-3 space-y-2 text-sm">
              <InlineStatus :ok="!!proyecto?.nombre" label="Nombre" />
              <InlineStatus :ok="!!proyecto?.estado_proyecto?.nombre" label="Estado" />
              <InlineStatus :ok="!!proyecto?.ubicacion?.direccion" label="Ubicación" />
              <InlineStatus :ok="true" label="Detalle disponible" />
            </div>
          </AppCard>
        </div>
      </div>
    </div>
  </SidebarBannerLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'

import SidebarBannerLayout from '@/Components/SidebarBannerLayout.vue'
import AppCard from '@/Components/AppCard.vue'
import PageHeader from '@/Components/PageHeader.vue'
import SectionHeader from '@/Components/SectionHeader.vue'
import IconButton from '@/Components/IconButton.vue'
import InlineStatus from '@/Components/InlineStatus.vue'

import SummaryTile from '@/Components/SummaryTile.vue'
import MetricTile from '@/Components/MetricTile.vue'
import KpiLine from '@/Components/KpiLine.vue'
import EmptyState from '@/Components/EmptyState.vue'

import {
  PencilIcon,
  PlusIcon,
  InformationCircleIcon,
} from '@heroicons/vue/24/outline'

const props = defineProps({
  proyecto: { type: Object, default: () => ({}) },
  empleado: { type: Object, default: null },
})

const proyecto = computed(() => props.proyecto)

const politicas = computed(() => {
  const arr = props.proyecto?.politicas_precio
  return Array.isArray(arr) ? arr : []
})

const ubicacionTexto = computed(() => {
  const dir = props.proyecto?.ubicacion?.direccion
  const ciudad = props.proyecto?.ubicacion?.ciudad?.nombre
  if (!dir && !ciudad) return '—'
  if (dir && ciudad) return `${dir}, ${ciudad}`
  return dir || ciudad || '—'
})

function formatCurrency(val) {
  if (val === null || val === undefined) return '—'
  const num = Number(val)
  if (Number.isNaN(num)) return '—'
  return new Intl.NumberFormat('es-CO', {
    style: 'currency',
    currency: 'COP',
    maximumFractionDigits: 0,
  }).format(num)
}

function estadoBadgeClass(nombre) {
  const n = (nombre || '').toLowerCase()
  if (n.includes('activo') || n.includes('ejec')) return 'border-green-200 bg-green-50 text-green-800'
  if (n.includes('final') || n.includes('termin')) return 'border-blue-200 bg-blue-50 text-blue-800'
  if (n.includes('paus') || n.includes('susp')) return 'border-amber-200 bg-amber-50 text-amber-800'
  return 'border-gray-200 bg-gray-50 text-gray-700'
}
</script>
