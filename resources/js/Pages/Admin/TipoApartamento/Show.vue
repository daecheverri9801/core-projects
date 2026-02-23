<template>
  <TopBannerLayout :empleado="empleado">
    <template #title>Detalle Tipo de Apartamento</template>

    <div class="space-y-6">
      <!-- Header -->
      <div class="bg-white rounded-2xl border p-4 md:p-6">
        <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
          <div>
            <p class="text-xs text-gray-600">Tipos de Apartamento</p>
            <h2 class="text-xl font-semibold text-gray-900">Detalle</h2>
            <p class="mt-1 text-sm text-gray-600">
              Información del tipo y apartamentos asociados.
            </p>
          </div>

          <div class="flex items-center gap-2">
            <Link
              :href="`/tipos-apartamento/${tipo.id_tipo_apartamento}/edit`"
              class="btn-secondary"
            >
              Editar
            </Link>
            <Link href="/tipos-apartamento" class="btn-secondary">Volver</Link>
          </div>
        </div>
      </div>

      <!-- Info + Imagen -->
      <div class="bg-white rounded-2xl border p-4 md:p-6">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
          <!-- Info -->
          <div class="lg:col-span-7">
            <h3 class="text-sm font-semibold text-gray-900">Información</h3>
            <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
              <InfoItem label="Proyecto" :value="tipo.id_proyecto" />
              <InfoItem label="Nombre" :value="tipo.nombre" />
              <InfoItem label="Área construida" :value="formatArea(tipo.area_construida)" />
              <InfoItem label="Área privada" :value="formatArea(tipo.area_privada)" />
              <InfoItem label="Habitaciones" :value="tipo.cantidad_habitaciones ?? '—'" />
              <InfoItem label="Baños" :value="tipo.cantidad_banos ?? '—'" />
              <InfoItem label="Valor m²" :value="formatCurrency(tipo.valor_m2)" />
              <InfoItem label="Valor estimado" :value="formatCurrency(tipo.valor_estimado)" />
            </div>
          </div>

          <!-- Imagen -->
          <div class="lg:col-span-5">
            <h3 class="text-sm font-semibold text-gray-900">Imagen</h3>

            <div
              v-if="tipo?.imagen"
              class="mt-4 overflow-hidden rounded-2xl border bg-gray-50"
            >
              <div class="aspect-[4/3] w-full bg-gray-100">
                <img
                  :src="`/storage/${tipo.imagen}`"
                  class="h-full w-full object-cover"
                  alt="Imagen del tipo de apartamento"
                />
              </div>
              <div class="p-3">
                <p class="text-xs text-gray-600">
                  Imagen asociada a este tipo de apartamento.
                </p>
              </div>
            </div>

            <div v-else class="mt-4 rounded-2xl border border-dashed p-6 text-center">
              <p class="text-sm font-medium text-gray-900">Sin imagen</p>
              <p class="text-xs text-gray-600 mt-1">Puedes agregar una desde “Editar”.</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Apartamentos -->
      <div class="bg-white rounded-2xl border p-4 md:p-6">
        <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
          <div>
            <h2 class="text-lg font-semibold text-brand-900">Apartamentos con este tipo</h2>
            <p class="text-sm text-gray-600">
              Total: <span class="font-semibold text-gray-900">{{ apartamentos.length }}</span>
            </p>
          </div>
        </div>

        <div class="mt-4 overflow-x-auto">
          <table class="min-w-full table-auto border-collapse">
            <thead>
              <tr class="bg-brand-50 text-left text-sm text-brand-800">
                <th class="px-3 py-2 border">ID</th>
                <th class="px-3 py-2 border">Número</th>
                <th class="px-3 py-2 border">Torre</th>
                <th class="px-3 py-2 border">Estado</th>
                <th class="px-3 py-2 border w-28">Acciones</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="a in apartamentos" :key="a.id_apartamento" class="border-b hover:bg-brand-50/40 transition">
                <td class="px-3 py-2 border">{{ a.id_apartamento }}</td>
                <td class="px-3 py-2 border">{{ a.numero }}</td>
                <td class="px-3 py-2 border">{{ a.torre || '—' }}</td>
                <td class="px-3 py-2 border">
                  <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-semibold"
                        :class="estadoClass(a.estado)">
                    {{ a.estado || '—' }}
                  </span>
                </td>
                <td class="px-3 py-2 border">
                  <Link
                    :href="`/apartamentos/${a.id_apartamento}`"
                    class="text-brand-700 hover:underline font-medium"
                  >
                    Ver
                  </Link>
                </td>
              </tr>

              <tr v-if="apartamentos.length === 0">
                <td colspan="5" class="px-3 py-10 text-center">
                  <p class="text-sm font-semibold text-gray-900">Sin apartamentos asociados</p>
                  <p class="text-sm text-gray-600 mt-1">Aún no hay apartamentos con este tipo.</p>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <FlashMessages />
  </TopBannerLayout>
</template>

<script setup>
import TopBannerLayout from '@/Components/TopBannerLayout.vue'
import FlashMessages from '@/Components/FlashMessages.vue'
import { Link } from '@inertiajs/vue3'
import InfoItem from '@/Components/InfoItem.vue'

const props = defineProps({
  proyectos: { type: Array, required: true },
  tipo: { type: Object, required: true },
  apartamentos: { type: Array, default: () => [] },
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

function estadoClass(estado) {
  const s = String(estado || '').toLowerCase()
  if (!s) return 'border-gray-200 bg-gray-50 text-gray-700'
  if (s.includes('disp') || s.includes('libre')) return 'border-green-200 bg-green-50 text-green-800'
  if (s.includes('vend') || s.includes('ocup')) return 'border-blue-200 bg-blue-50 text-blue-800'
  if (s.includes('separ') || s.includes('proce')) return 'border-amber-200 bg-amber-50 text-amber-800'
  return 'border-gray-200 bg-gray-50 text-gray-700'
}
</script>

<style scoped>
.btn-secondary {
  @apply inline-flex items-center gap-2 px-3 py-2 rounded-xl border border-gray-300 bg-white text-gray-800 hover:bg-gray-50 transition;
}
</style>
