<template>
  <SidebarBannerLayout :empleado="empleado">
    <template #title>Locales</template>

    <div class="bg-white rounded-lg border p-4 md:p-6">
      <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-4">
        <h2 class="text-lg font-semibold text-brand-900">Listado</h2>
        <div class="flex items-center gap-3">
          <input
            v-model="search"
            type="text"
            placeholder="Buscar por número, proyecto, torre, piso, estado"
            class="w-full md:w-96 border rounded-md px-3 py-2 text-sm"
          />
          <Link href="/locales/create" class="btn-primary">Nuevo</Link>
        </div>
      </div>

      <div class="overflow-x-auto">
        <table class="min-w-full table-auto border-collapse">
          <thead>
            <tr class="bg-brand-50 text-left text-sm text-brand-800">
              <th class="px-3 py-2 border">ID</th>
              <th class="px-3 py-2 border">Número</th>
              <th class="px-3 py-2 border">Proyecto</th>
              <th class="px-3 py-2 border">Torre</th>
              <th class="px-3 py-2 border">Piso</th>
              <th class="px-3 py-2 border text-right">Área (m²)</th>
              <th class="px-3 py-2 border text-right">Valor m²</th>
              <th class="px-3 py-2 border text-right">Valor total</th>
              <th class="px-3 py-2 border">Estado</th>
              <th class="px-3 py-2 border w-40">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="l in filtered" :key="l.id_local" class="border-b hover:bg-gray-50">
              <td class="px-3 py-2 border">{{ l.id_local }}</td>
              <td class="px-3 py-2 border">{{ l.numero }}</td>
              <td class="px-3 py-2 border">{{ l.proyecto || '—' }}</td>
              <td class="px-3 py-2 border">{{ l.torre || '—' }}</td>
              <td class="px-3 py-2 border">{{ l.piso ?? '—' }}</td>
              <td class="px-3 py-2 border text-right">{{ formatArea(l.area_total_local) }}</td>
              <td class="px-3 py-2 border text-right">{{ formatCurrency(l.valor_m2) }}</td>
              <td class="px-3 py-2 border text-right">{{ formatCurrency(l.valor_total) }}</td>
              <td class="px-3 py-2 border">{{ l.estado || '—' }}</td>
              <td class="px-3 py-2 border">
                <div class="flex items-center gap-2">
                  <Link :href="`/locales/${l.id_local}`" class="icon-btn info" title="Ver">
                    <EyeIcon class="w-5 h-5" />
                  </Link>
                  <Link :href="`/locales/${l.id_local}/edit`" class="icon-btn warn" title="Editar">
                    <PencilSquareIcon class="w-5 h-5" />
                  </Link>
                  <button
                    class="icon-btn danger"
                    @click="confirmDelete(l.id_local)"
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
  locales: { type: Array, default: () => [] },
  empleado: { type: Object, default: null },
})

const search = ref('')

const filtered = computed(() => {
  const q = search.value.toLowerCase().trim()
  if (!q) return props.locales
  return props.locales.filter(
    (l) =>
      String(l.id_local).includes(q) ||
      (l.numero || '').toLowerCase().includes(q) ||
      (l.proyecto || '').toLowerCase().includes(q) ||
      (l.torre || '').toLowerCase().includes(q) ||
      String(l.piso ?? '').includes(q) ||
      (l.estado || '').toLowerCase().includes(q) ||
      String(l.area_total_local ?? '').includes(q) ||
      String(l.valor_total ?? '').includes(q)
  )
})

function formatArea(val) {
  if (val === null || val === undefined) return '—'
  const num = Number(val)
  if (isNaN(num)) return '—'
  return `${num.toLocaleString('es-CO', { maximumFractionDigits: 2 })}`
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
  if (confirm('¿Eliminar este local? Esta acción no se puede deshacer.')) {
    Inertia.delete(`/locales/${id}`)
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
