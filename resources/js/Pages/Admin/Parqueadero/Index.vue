<template>
  <SidebarBannerLayout :empleado="empleado">
    <template #title>Parqueaderos</template>

    <div class="bg-white rounded-lg border p-4 md:p-6">
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 mb-4">
        <h2 class="text-lg font-semibold text-brand-900">Listado</h2>
        <div class="flex items-center gap-3">
          <input
            v-model="search"
            placeholder="Buscar por número, tipo, proyecto, torre, estado"
            class="w-full md:w-96 border px-3 py-2 rounded-md text-sm"
          />
          <Link href="/parqueaderos/create" class="btn-primary">Nuevo</Link>
        </div>
      </div>

      <div class="overflow-x-auto">
        <table class="min-w-full table-auto border-collapse">
          <thead>
            <tr class="bg-brand-50 text-left text-sm text-brand-800">
              <th class="px-3 py-2 border">ID</th>
              <th class="px-3 py-2 border">Número</th>
              <th class="px-3 py-2 border">Tipo</th>
              <th class="px-3 py-2 border">Estado</th>
              <th class="px-3 py-2 border">Apartamento</th>
              <th class="px-3 py-2 border">Torre</th>
              <th class="px-3 py-2 border">Proyecto</th>
              <th class="px-3 py-2 border w-40">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="p in filtered" :key="p.id_parqueadero" class="border-b hover:bg-gray-50">
              <td class="px-3 py-2 border">{{ p.id_parqueadero }}</td>
              <td class="px-3 py-2 border">{{ p.numero }}</td>
              <td class="px-3 py-2 border">{{ p.tipo }}</td>
              <td class="px-3 py-2 border">
                <span :class="p.estado === 'Asignado' ? 'badge-warn' : 'badge-success'">{{
                  p.estado
                }}</span>
              </td>
              <td class="px-3 py-2 border">{{ p.apartamento || '—' }}</td>
              <td class="px-3 py-2 border">{{ p.torre || '—' }}</td>
              <td class="px-3 py-2 border">{{ p.proyecto || '—' }}</td>
              <td class="px-3 py-2 border">
                <div class="flex items-center gap-2">
                  <Link
                    :href="`/parqueaderos/${p.id_parqueadero}`"
                    class="icon-btn info"
                    title="Ver"
                  >
                    <EyeIcon class="w-5 h-5" />
                  </Link>
                  <Link
                    :href="`/parqueaderos/${p.id_parqueadero}/edit`"
                    class="icon-btn warn"
                    title="Editar"
                  >
                    <PencilSquareIcon class="w-5 h-5" />
                  </Link>
                  <button
                    class="icon-btn danger"
                    @click="confirmDelete(p.id_parqueadero)"
                    title="Eliminar"
                  >
                    <TrashIcon class="w-5 h-5" />
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="filtered.length === 0">
              <td colspan="8" class="px-3 py-6 text-center text-sm text-gray-500">
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
  parqueaderos: { type: Array, default: () => [] },
  empleado: { type: Object, default: null },
})

const search = ref('')

const filtered = computed(() => {
  const q = search.value.toLowerCase().trim()
  if (!q) return props.parqueaderos
  return props.parqueaderos.filter(
    (p) =>
      String(p.id_parqueadero).includes(q) ||
      (p.numero || '').toLowerCase().includes(q) ||
      (p.tipo || '').toLowerCase().includes(q) ||
      (p.estado || '').toLowerCase().includes(q) ||
      (p.apartamento || '').toLowerCase().includes(q) ||
      (p.torre || '').toLowerCase().includes(q) ||
      (p.proyecto || '').toLowerCase().includes(q)
  )
})

function confirmDelete(id) {
  if (confirm('¿Eliminar este parqueadero? Esta acción no se puede deshacer.')) {
    Inertia.delete(`/parqueaderos/${id}`)
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
