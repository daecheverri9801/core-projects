<template>
  <SidebarBannerLayout :empleado="empleado">
    <template #title>Tipos de Apartamento</template>

    <div class="bg-white rounded-lg border p-4 md:p-6">
      <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-4">
        <h2 class="text-lg font-semibold text-brand-900">Listado</h2>
        <div class="flex items-center gap-3">
          <input
            v-model="search"
            type="text"
            placeholder="Buscar por nombre, áreas, habitaciones, baños, valor m²"
            class="w-full md:w-96 border rounded-md px-3 py-2 text-sm"
          />
          <Link href="/tipos-apartamento/create" class="btn-primary">Nuevo</Link>
        </div>
      </div>

      <div class="overflow-x-auto">
        <table class="min-w-full table-auto border-collapse">
          <thead>
            <tr class="bg-brand-50 text-left text-sm text-brand-800">
              <th class="px-3 py-2 border">ID</th>
              <th class="px-3 py-2 border">Nombre</th>
              <th class="px-3 py-2 border text-right">Área construida</th>
              <th class="px-3 py-2 border text-right">Área privada</th>
              <th class="px-3 py-2 border text-center">Hab.</th>
              <th class="px-3 py-2 border text-center">Baños</th>
              <th class="px-3 py-2 border text-right">Valor m²</th>
              <th class="px-3 py-2 border text-right">Valor estimado</th>
              <th class="px-3 py-2 border text-center">Aptos</th>
              <th class="px-3 py-2 border w-40">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="t in filtered"
              :key="t.id_tipo_apartamento"
              class="border-b hover:bg-gray-50"
            >
              <td class="px-3 py-2 border">{{ t.id_tipo_apartamento }}</td>
              <td class="px-3 py-2 border">{{ t.nombre }}</td>
              <td class="px-3 py-2 border text-right">{{ formatArea(t.area_construida) }}</td>
              <td class="px-3 py-2 border text-right">{{ formatArea(t.area_privada) }}</td>
              <td class="px-3 py-2 border text-center">{{ t.cantidad_habitaciones ?? '—' }}</td>
              <td class="px-3 py-2 border text-center">{{ t.cantidad_banos ?? '—' }}</td>
              <td class="px-3 py-2 border text-right">{{ formatCurrency(t.valor_m2) }}</td>
              <td class="px-3 py-2 border text-right">{{ formatCurrency(t.valor_estimado) }}</td>
              <td class="px-3 py-2 border text-center">
                <span
                  class="inline-flex items-center justify-center min-w-8 px-2 py-1 text-xs font-semibold rounded-full bg-brand-100 text-brand-800 border"
                >
                  {{ t.apartamentos_count ?? 0 }}
                </span>
              </td>
              <td class="px-3 py-2 border">
                <div class="flex items-center gap-2">
                  <Link
                    :href="`/tipos-apartamento/${t.id_tipo_apartamento}`"
                    class="icon-btn info"
                    title="Ver"
                  >
                    <EyeIcon class="w-5 h-5" />
                  </Link>
                  <Link
                    :href="`/tipos-apartamento/${t.id_tipo_apartamento}/edit`"
                    class="icon-btn warn"
                    title="Editar"
                  >
                    <PencilSquareIcon class="w-5 h-5" />
                  </Link>
                  <button
                    class="icon-btn danger"
                    @click="confirmDelete(t.id_tipo_apartamento)"
                    title="Eliminar"
                  >
                    <TrashIcon class="w-5 h-5" />
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="filtered.length === 0">
              <td colspan="10" class="px-3 py-6 text-center text-sm text-gray-500">
                No hay registros
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <FlashMessages />
  </SidebarBannerLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link } from '@inertiajs/inertia-vue3'
import { Inertia } from '@inertiajs/inertia'
import SidebarBannerLayout from '@/Components/SidebarBannerLayout.vue'
import FlashMessages from '@/Components/FlashMessages.vue'
import { EyeIcon, PencilSquareIcon, TrashIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  tipos: { type: Array, default: () => [] },
  empleado: { type: Object, default: null },
})

const search = ref('')

const filtered = computed(() => {
  const q = search.value.toLowerCase().trim()
  if (!q) return props.tipos
  return props.tipos.filter(
    (t) =>
      String(t.id_tipo_apartamento).includes(q) ||
      (t.nombre || '').toLowerCase().includes(q) ||
      String(t.area_construida ?? '').includes(q) ||
      String(t.area_privada ?? '').includes(q) ||
      String(t.cantidad_habitaciones ?? '').includes(q) ||
      String(t.cantidad_banos ?? '').includes(q) ||
      String(t.valor_m2 ?? '').includes(q)
  )
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

function confirmDelete(id) {
  if (confirm('¿Eliminar este tipo de apartamento? Esta acción no se puede deshacer.')) {
    Inertia.delete(`/tipos-apartamento/${id}`)
  }
}
</script>

<style scoped>
.btn-primary {
  @apply inline-flex items-center gap-2 px-3 py-2 rounded-md bg-brand-600 text-white hover:bg-brand-700;
}
.icon-btn {
  @apply inline-flex items-center justify-center w-9 h-9 rounded-md border transition;
}
.icon-btn.info {
  @apply border-brand-300 text-brand-700 hover:bg-brand-50;
}
.icon-btn.warn {
  @apply border-amber-300 text-amber-700 hover:bg-amber-50;
}
.icon-btn.danger {
  @apply border-red-300 text-red-600 hover:bg-red-50;
}
</style>
