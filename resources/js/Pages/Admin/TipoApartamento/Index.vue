<template>
  <SidebarBannerLayout :empleado="empleado">
    <div class="space-y-6">
      <PageHeader
        title="Tipos de Apartamento"
        kicker="Configuración comercial"
        subtitle="Administra los tipos de apartamento definidos por proyecto."
      >
        <template #actions>
          <ButtonPrimary href="/tipos-apartamento/create">
            Nuevo tipo
          </ButtonPrimary>
        </template>
      </PageHeader>

      <!-- Buscador -->
      <AppCard padding="md">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
          <p class="text-sm text-gray-600">
            Total registros:
            <span class="font-semibold text-gray-900">{{ tiposFiltrados.length }}</span>
          </p>

          <input
            v-model="search"
            type="text"
            placeholder="Buscar por nombre, áreas, habitaciones, baños o valor m²"
            class="w-full md:w-[420px] rounded-md border px-3 py-2 text-sm"
          />
        </div>
      </AppCard>

      <!-- Listado por proyecto -->
      <div
        v-for="(tiposProyecto, proyecto) in agrupadosPorProyecto"
        :key="proyecto"
        class="space-y-3"
      >
        <!-- Header proyecto -->
        <div class="flex items-center gap-2">
          <span class="rounded-xl bg-brand-100 px-3 py-1 text-sm font-semibold text-brand-900">
            {{ proyecto }}
          </span>
          <span class="text-sm text-gray-500">
            {{ tiposProyecto.length }} tipo(s)
          </span>
        </div>

        <!-- Tabla -->
        <AppCard padding="none">
          <div class="overflow-x-auto">
            <table class="min-w-[1100px] w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="th">Nombre</th>
                  <th class="th text-right">Área const.</th>
                  <th class="th text-right">Área priv.</th>
                  <th class="th text-center">Hab.</th>
                  <th class="th text-center">Baños</th>
                  <th class="th text-right">Valor m²</th>
                  <th class="th text-right">Valor estimado</th>
                  <th class="th text-center">Aptos</th>
                  <th class="th text-right">Acciones</th>
                </tr>
              </thead>

              <tbody class="divide-y divide-gray-100 bg-white">
                <tr
                  v-for="t in tiposProyecto"
                  :key="t.id_tipo_apartamento"
                  class="hover:bg-brand-50/40 transition"
                >
                  <td class="td font-medium">{{ t.nombre }}</td>
                  <td class="td text-right">{{ formatArea(t.area_construida) }}</td>
                  <td class="td text-right">{{ formatArea(t.area_privada) }}</td>
                  <td class="td text-center">{{ t.cantidad_habitaciones ?? '—' }}</td>
                  <td class="td text-center">{{ t.cantidad_banos ?? '—' }}</td>
                  <td class="td text-right">{{ formatCurrency(t.valor_m2) }}</td>
                  <td class="td text-right">{{ formatCurrency(t.valor_estimado) }}</td>
                  <td class="td text-center">
                    <span class="badge">
                      {{ t.apartamentos_count ?? 0 }}
                    </span>
                  </td>
                  <td class="td">
                    <div class="flex justify-end gap-2">
                      <IconButton
                        :href="`/tipos-apartamento/${t.id_tipo_apartamento}`"
                        icon="EyeIcon"
                        title="Ver"
                        variant="info"
                      />
                      <IconButton
                        :href="`/tipos-apartamento/${t.id_tipo_apartamento}/edit`"
                        icon="PencilIcon"
                        title="Editar"
                        variant="warn"
                      />
                      <IconButton
                        icon="TrashIcon"
                        title="Eliminar"
                        variant="danger"
                        @click="confirmDelete(t.id_tipo_apartamento)"
                      />
                    </div>
                  </td>
                </tr>

                <tr v-if="tiposProyecto.length === 0">
                  <td colspan="9" class="py-6 text-center text-sm text-gray-500">
                    Sin tipos de apartamento
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </AppCard>
      </div>

      <FlashMessages />
    </div>
  </SidebarBannerLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'

import SidebarBannerLayout from '@/Components/SidebarBannerLayout.vue'
import PageHeader from '@/Components/PageHeader.vue'
import AppCard from '@/Components/AppCard.vue'
import ButtonPrimary from '@/Components/ButtonPrimary.vue'
import IconButton from '@/Components/IconButton.vue'
import FlashMessages from '@/Components/FlashMessages.vue'

const props = defineProps({
  tipos: { type: Array, default: () => [] },
  empleado: { type: Object, default: null },
})

const search = ref('')

const tiposFiltrados = computed(() => {
  const q = search.value.toLowerCase().trim()
  if (!q) return props.tipos

  return props.tipos.filter(t =>
    (t.proyecto || '').toLowerCase().includes(q) ||
    (t.nombre || '').toLowerCase().includes(q) ||
    String(t.area_construida ?? '').includes(q) ||
    String(t.area_privada ?? '').includes(q) ||
    String(t.valor_m2 ?? '').includes(q)
  )
})

const agrupadosPorProyecto = computed(() => {
  return tiposFiltrados.value.reduce((acc, t) => {
    const key = t.proyecto || 'Sin proyecto'
    if (!acc[key]) acc[key] = []
    acc[key].push(t)
    return acc
  }, {})
})

function formatArea(val) {
  if (!val) return '—'
  return `${Number(val).toLocaleString('es-CO')} m²`
}

function formatCurrency(val) {
  if (!val) return '—'
  return new Intl.NumberFormat('es-CO', {
    style: 'currency',
    currency: 'COP',
    maximumFractionDigits: 0,
  }).format(val)
}

function confirmDelete(id) {
  if (confirm('¿Eliminar este tipo de apartamento?')) {
    router.delete(`/tipos-apartamento/${id}`)
  }
}
</script>

<style scoped>
.th {
  @apply px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase;
}
.td {
  @apply px-6 py-4 text-sm text-gray-900;
}
.badge {
  @apply inline-flex min-w-8 items-center justify-center rounded-full border
  border-brand-200 bg-brand-100 px-2 py-0.5 text-xs font-semibold text-brand-800;
}
</style>
