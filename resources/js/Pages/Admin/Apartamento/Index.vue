<template>
  <SidebarBannerLayout :empleado="empleado">
    <template #title>Apartamentos</template>

    <div class="bg-white rounded-lg border p-4 md:p-6">
      <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-4">
        <h2 class="text-lg font-semibold text-brand-900">Listado</h2>
        <div class="flex items-center gap-3">
          <input
            v-model="search"
            type="text"
            placeholder="Buscar por número, proyecto, torre, piso, tipo, estado"
            class="w-full md:w-80 border rounded-md px-3 py-2 text-sm"
          />
          <Link
            href="/apartamentos/create"
            class="inline-flex items-center gap-2 px-3 py-2 rounded-md bg-brand-600 text-white hover:bg-brand-700"
          >
            <PlusIcon class="w-5 h-5" /> Nuevo
          </Link>
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
              <th class="px-3 py-2 border">Tipo</th>
              <th class="px-3 py-2 border">Estado</th>
              <th class="px-3 py-2 border text-right">Valor Total</th>
              <th class="px-3 py-2 border w-40">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="a in filtered" :key="a.id_apartamento" class="border-b hover:bg-gray-50">
              <td class="px-3 py-2 border">{{ a.id_apartamento }}</td>
              <td class="px-3 py-2 border">{{ a.numero }}</td>
              <td class="px-3 py-2 border">{{ a.proyecto || '—' }}</td>
              <td class="px-3 py-2 border">{{ a.torre || '—' }}</td>
              <td class="px-3 py-2 border">{{ a.piso ?? '—' }}</td>
              <td class="px-3 py-2 border">{{ a.tipo || '—' }}</td>
              <td class="px-3 py-2 border">{{ a.estado || '—' }}</td>
              <td class="px-3 py-2 border text-right">{{ formatCurrency(a.valor_total) }}</td>
              <td class="px-3 py-2 border">
                <div class="flex items-center gap-2">
                  <Link
                    :href="`/apartamentos/${a.id_apartamento}`"
                    class="icon-btn info"
                    title="Ver"
                  >
                    <EyeIcon class="w-5 h-5" />
                  </Link>
                  <Link
                    :href="`/apartamentos/${a.id_apartamento}/edit`"
                    class="icon-btn warn"
                    title="Editar"
                  >
                    <PencilSquareIcon class="w-5 h-5" />
                  </Link>
                  <button
                    class="icon-btn danger"
                    @click="confirmDelete(a.id_apartamento)"
                    title="Eliminar"
                  >
                    <TrashIcon class="w-5 h-5" />
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="filtered.length === 0">
              <td colspan="9" class="px-3 py-6 text-center text-sm text-gray-500">
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
import { Link } from '@inertiajs/vue3'
import { router } from '@inertiajs/vue3'
import SidebarBannerLayout from '@/Components/SidebarBannerLayout.vue'
import FlashMessages from '@/Components/FlashMessages.vue'
import { PlusIcon, EyeIcon, PencilSquareIcon, TrashIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  apartamentos: { type: Array, default: () => [] },
  empleado: { type: Object, default: null },
})

const search = ref('')

const filtered = computed(() => {
  const q = search.value.toLowerCase().trim()
  if (!q) return props.apartamentos
  return props.apartamentos.filter(
    (a) =>
      String(a.id_apartamento).includes(q) ||
      (a.numero || '').toLowerCase().includes(q) ||
      (a.proyecto || '').toLowerCase().includes(q) ||
      (a.torre || '').toLowerCase().includes(q) ||
      String(a.piso ?? '').includes(q) ||
      (a.tipo || '').toLowerCase().includes(q) ||
      (a.estado || '').toLowerCase().includes(q) ||
      String(a.valor_total ?? '')
        .toLowerCase()
        .includes(q)
  )
})


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
  if (confirm('¿Eliminar este apartamento? Esta acción no se puede deshacer.')) {
    router.delete(`/apartamentos/${id}`)
  }
}
</script>

<style scoped>
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
