<template>
  <SidebarBannerLayout :empleado="empleado">
    <template #title>Políticas de Precio</template>

    <div class="bg-white rounded-lg border p-4 md:p-6">
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 mb-4">
        <h2 class="text-lg font-semibold text-brand-900">Listado</h2>
        <div class="flex items-center gap-3">
          <input
            v-model="search"
            placeholder="Buscar por proyecto, escalón, porcentaje, estado"
            class="w-full md:w-96 border px-3 py-2 rounded-md text-sm"
          />
          <Link href="/politicas-precio-proyecto/crear" class="btn-primary">Nueva</Link>
        </div>
      </div>

      <div class="overflow-x-auto">
        <table class="min-w-full table-auto border-collapse">
          <thead>
            <tr class="bg-brand-50 text-left text-sm text-brand-800">
              <th class="px-3 py-2 border">ID</th>
              <th class="px-3 py-2 border">Proyecto</th>
              <th class="px-3 py-2 border">Ventas/Escalón</th>
              <th class="px-3 py-2 border">% Aumento</th>
              <th class="px-3 py-2 border">Aplica Desde</th>
              <th class="px-3 py-2 border">Estado</th>
              <th class="px-3 py-2 border w-40">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="p in filtered" :key="p.id_politica_precio" class="border-b hover:bg-gray-50">
              <td class="px-3 py-2 border">{{ p.id_politica_precio }}</td>
              <td class="px-3 py-2 border">{{ p.proyecto?.nombre || '—' }}</td>
              <td class="px-3 py-2 border">{{ p.ventas_por_escalon ?? '—' }}</td>
              <td class="px-3 py-2 border">{{ parseFloat(p.porcentaje_aumento) }}%</td>
              <td class="px-3 py-2 border">{{ p.aplica_desde?.split('T')[0] || 'No definida' }}</td>
              <td class="px-3 py-2 border">
                <span :class="p.estado ? 'badge-success' : 'badge-warn'">
                  {{ p.estado ? 'Activa' : 'Inactiva' }}
                </span>
              </td>
              <td class="px-3 py-2 border">
                <div class="flex items-center gap-2">
                  <Link
                    :href="`/politicas-precio-proyecto/${p.id_politica_precio}`"
                    class="icon-btn info"
                    title="Ver"
                  >
                    <EyeIcon class="w-5 h-5" />
                  </Link>
                  <Link
                    :href="`/politicas-precio-proyecto/${p.id_politica_precio}/editar`"
                    class="icon-btn warn"
                    title="Editar"
                  >
                    <PencilSquareIcon class="w-5 h-5" />
                  </Link>
                  <button
                    class="icon-btn danger"
                    @click="confirmDelete(p.id_politica_precio)"
                    title="Eliminar"
                  >
                    <TrashIcon class="w-5 h-5" />
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="filtered.length === 0">
              <td colspan="7" class="px-3 py-6 text-center text-sm text-gray-500">
                No hay registros
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <FlashMessages />
    </div>
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
  politicas: { type: Array, default: () => [] },
  empleado: { type: Object, default: null },
})

const search = ref('')

const filtered = computed(() => {
  const q = search.value.toLowerCase().trim()
  if (!q) return props.politicas
  return props.politicas.filter(
    (p) =>
      String(p.id_politica_precio).includes(q) ||
      (p.proyecto?.nombre || '').toLowerCase().includes(q) ||
      String(p.ventas_por_escalon || '').includes(q) ||
      String(p.porcentaje_aumento || '').includes(q) ||
      (p.estado ? 'activa' : 'inactiva').includes(q)
  )
})

function confirmDelete(id) {
  if (confirm('¿Eliminar esta política de precio? Esta acción no se puede deshacer.')) {
    Inertia.delete(`/politicas-precio-proyecto/${id}`)
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
.badge-success {
  @apply inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800;
}
.badge-warn {
  @apply inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-amber-100 text-amber-800;
}
</style>
