<template>
  <TopBannerLayout :empleado="empleado" panel-name="Proyectos">
    <div class="space-y-6">
      <PageHeader
        :title="`Torre: ${torre.nombre_torre}`"
        kicker="Torres"
        :subtitle="`${torre.proyecto?.nombre || '—'} · ${torre.estado?.nombre || '—'}`"
      >
        <template #actions>
          <div class="flex items-center gap-2">
            <Link
              :href="route('admin.torres.edit', torre.id_torre)"
              class="btn-secondary"
            >
              Editar
            </Link>

            <Link
              :href="route('admin.torres.index')"
              class="btn-primary"
            >
              Volver
            </Link>
          </div>
        </template>
      </PageHeader>

      <!-- INFO + UBICACIÓN -->
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <AppCard padding="md" class="lg:col-span-8">
          <div class="flex items-start justify-between gap-4">
            <div class="min-w-0">
              <p class="text-xs text-gray-600">Información general</p>
              <div class="mt-1 flex flex-wrap items-center gap-2">
                <h2 class="text-xl font-semibold text-gray-900 truncate">
                  {{ torre.nombre_torre }}
                </h2>
                <span
                  class="inline-flex items-center rounded-full border border-gray-200 bg-gray-50 px-2.5 py-1 text-xs font-semibold text-gray-700"
                >
                  ID: {{ torre.id_torre }}
                </span>
              </div>
              <p class="mt-1 text-sm text-gray-600">
                Proyecto: <span class="font-semibold text-gray-900">{{ torre.proyecto?.nombre || '—' }}</span>
              </p>
            </div>

            <span
              class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-semibold"
              :class="estadoBadgeClass(torre.estado?.nombre)"
            >
              {{ torre.estado?.nombre || '—' }}
            </span>
          </div>

          <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
            <InfoItem label="Nombre" :value="torre.nombre_torre" />
            <InfoItem label="Número de pisos" :value="torre.numero_pisos ?? '—'" />
          </div>
        </AppCard>

        <AppCard padding="md" class="lg:col-span-4">
          <p class="text-xs text-gray-600">Ubicación del proyecto</p>
          <p class="mt-2 text-sm font-semibold text-gray-900">
            {{ torre.proyecto?.ubicacion?.direccion || '—' }}
          </p>
          <p class="mt-1 text-sm text-gray-600">
            {{ torre.proyecto?.ubicacion?.ciudad?.nombre || '—' }}
          </p>
        </AppCard>
      </div>

      <!-- RESUMEN -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <AppCard padding="md">
          <p class="text-sm text-gray-600">Pisos registrados</p>
          <p class="mt-1 text-3xl font-bold text-gray-900">{{ torre.pisos?.length || 0 }}</p>
        </AppCard>

        <AppCard padding="md">
          <p class="text-sm text-gray-600">Apartamentos</p>
          <p class="mt-1 text-3xl font-bold text-gray-900">{{ torre.apartamentos?.length || 0 }}</p>
        </AppCard>

        <AppCard padding="md">
          <p class="text-sm text-gray-600">Estado</p>
          <p class="mt-1 text-lg font-semibold text-gray-900">{{ torre.estado?.nombre || '—' }}</p>
        </AppCard>
      </div>

      <!-- PISOS -->
      <AppCard padding="none">
        <div class="px-6 py-4 border-b border-gray-200">
          <div class="flex items-center justify-between gap-3">
            <div>
              <p class="text-xs text-gray-600">Listado</p>
              <h3 class="text-lg font-semibold text-gray-900">Pisos</h3>
            </div>

            <span
              class="inline-flex items-center rounded-full border border-brand-200 bg-brand-100 px-2.5 py-1 text-xs font-semibold text-brand-800"
            >
              {{ torre.pisos?.length || 0 }}
            </span>
          </div>
        </div>

        <div class="overflow-x-auto">
          <table class="min-w-[720px] w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                  #
                </th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                  Nombre
                </th>
              </tr>
            </thead>

            <tbody class="divide-y divide-gray-200 bg-white">
              <tr
                v-for="(p, idx) in torre.pisos || []"
                :key="idx"
                class="hover:bg-brand-50/40 transition"
              >
                <td class="px-6 py-4 text-sm text-gray-700">
                  {{ idx + 1 }}
                </td>
                <td class="px-6 py-4 text-sm font-semibold text-gray-900">
                  {{ p.nombre || p.id_piso || '-' }}
                </td>
              </tr>

              <tr v-if="!torre.pisos || torre.pisos.length === 0">
                <td colspan="2" class="px-6 py-12 text-center">
                  <p class="text-sm font-semibold text-gray-900">Sin pisos</p>
                  <p class="mt-1 text-sm text-gray-600">Esta torre aún no tiene pisos registrados.</p>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </AppCard>

      <!-- APARTAMENTOS -->
      <AppCard padding="none">
        <div class="px-6 py-4 border-b border-gray-200">
          <div class="flex items-center justify-between gap-3">
            <div>
              <p class="text-xs text-gray-600">Listado</p>
              <h3 class="text-lg font-semibold text-gray-900">Apartamentos</h3>
            </div>

            <span
              class="inline-flex items-center rounded-full border border-brand-200 bg-brand-100 px-2.5 py-1 text-xs font-semibold text-brand-800"
            >
              {{ torre.apartamentos?.length || 0 }}
            </span>
          </div>
        </div>

        <div class="overflow-x-auto">
          <table class="min-w-[980px] w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                  #
                </th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                  Tipo
                </th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                  Estado
                </th>
              </tr>
            </thead>

            <tbody class="divide-y divide-gray-200 bg-white">
              <tr
                v-for="(a, idx) in torre.apartamentos || []"
                :key="idx"
                class="hover:bg-brand-50/40 transition"
              >
                <td class="px-6 py-4 text-sm text-gray-700">
                  {{ idx + 1 }}
                </td>

                <td class="px-6 py-4 text-sm font-semibold text-gray-900">
                  {{ a.tipo_apartamento?.nombre || a.tipoApartamento?.nombre || '-' }}
                </td>

                <td class="px-6 py-4 text-sm">
                  <span
                    class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-semibold"
                    :class="estadoInmuebleBadgeClass(a.estado_inmueble?.nombre || a.estadoInmueble?.nombre)"
                  >
                    {{ a.estado_inmueble?.nombre || a.estadoInmueble?.nombre || '-' }}
                  </span>
                </td>
              </tr>

              <tr v-if="!torre.apartamentos || torre.apartamentos.length === 0">
                <td colspan="3" class="px-6 py-12 text-center">
                  <p class="text-sm font-semibold text-gray-900">Sin apartamentos</p>
                  <p class="mt-1 text-sm text-gray-600">Esta torre aún no tiene apartamentos asociados.</p>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </AppCard>
    </div>
  </TopBannerLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import TopBannerLayout from '@/Components/TopBannerLayout.vue'

import AppCard from '@/Components/AppCard.vue'
import PageHeader from '@/Components/PageHeader.vue'
import InfoItem from '@/Components/InfoItem.vue'

const props = defineProps({
  torre: Object,
  empleado: Object,
})

function estadoBadgeClass(nombre) {
  const n = (nombre || '').toLowerCase()
  if (n.includes('activo') || n.includes('ejec')) return 'border-green-200 bg-green-50 text-green-800'
  if (n.includes('final') || n.includes('termin')) return 'border-blue-200 bg-blue-50 text-blue-800'
  if (n.includes('paus') || n.includes('susp')) return 'border-amber-200 bg-amber-50 text-amber-800'
  return 'border-gray-200 bg-gray-50 text-gray-700'
}

function estadoInmuebleBadgeClass(nombre) {
  const n = (nombre || '').toLowerCase()
  if (n.includes('dispo') || n.includes('libre')) return 'border-green-200 bg-green-50 text-green-800'
  if (n.includes('separ') || n.includes('reser')) return 'border-amber-200 bg-amber-50 text-amber-800'
  if (n.includes('vend') || n.includes('escr')) return 'border-blue-200 bg-blue-50 text-blue-800'
  if (n.includes('bloq') || n.includes('inhab')) return 'border-red-200 bg-red-50 text-red-800'
  return 'border-gray-200 bg-gray-50 text-gray-700'
}
</script>

<style scoped>
.btn-primary {
  @apply inline-flex items-center justify-center gap-2 rounded-xl bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700 transition;
}
.btn-secondary {
  @apply inline-flex items-center justify-center gap-2 rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-800 hover:bg-gray-50 transition;
}
</style>
