<template>
  <SidebarBannerLayout :empleado="empleado">
    <template #title>Zonas Sociales</template>

    <div class="bg-white border rounded-lg p-4 md:p-6">
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 mb-4">
        <h2 class="text-lg font-semibold text-brand-900">Listado</h2>
        <div class="flex items-center gap-3">
          <input
            v-model="search"
            placeholder="Buscar por nombre o proyecto"
            class="w-full md:w-96 border px-3 py-2 rounded-md text-sm"
          />
          <Link href="/zonas-sociales/create" class="btn-primary">Nueva</Link>
        </div>
      </div>

      <div class="overflow-x-auto">
        <table class="min-w-full table-auto border-collapse">
          <thead>
            <tr class="bg-brand-50 text-sm text-left text-brand-800">
              <th class="border px-3 py-2">ID</th>
              <th class="border px-3 py-2">Nombre</th>
              <th class="border px-3 py-2">Descripción</th>
              <th class="border px-3 py-2">Proyecto</th>
              <th class="border px-3 py-2">Ciudad</th>
              <th class="border px-3 py-2">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="z in filtered" :key="z.id_zona_social" class="border-b hover:bg-gray-50">
              <td class="px-3 py-2">{{ z.id_zona_social }}</td>
              <td class="px-3 py-2">{{ z.nombre }}</td>
              <td class="px-3 py-2">{{ z.descripcion || '—' }}</td>
              <td class="px-3 py-2">{{ z.proyecto }}</td>
              <td class="px-3 py-2">{{ z.ubicacion || '—' }}</td>
              <td class="px-3 py-2">
                <div class="flex gap-2">
                  <Link :href="`/zonas-sociales/${z.id_zona_social}`" class="icon-btn info"
                    ><EyeIcon class="w-5 h-5"
                  /></Link>
                  <Link :href="`/zonas-sociales/${z.id_zona_social}/edit`" class="icon-btn warn"
                    ><PencilSquareIcon class="w-5 h-5"
                  /></Link>
                  <button @click="confirmDelete(z.id_zona_social)" class="icon-btn danger">
                    <TrashIcon class="w-5 h-5" />
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="filtered.length === 0">
              <td colspan="6" class="text-center py-4 text-gray-500">Sin resultados</td>
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
import { Link, router } from '@inertiajs/vue3'
import SidebarBannerLayout from '@/Components/SidebarBannerLayout.vue'
import FlashMessages from '@/Components/FlashMessages.vue'
import { EyeIcon, PencilSquareIcon, TrashIcon } from '@heroicons/vue/24/outline'

const props = defineProps({ zonas: Array, empleado: Object })
const search = ref('')

const filtered = computed(() => {
  const q = search.value.toLowerCase()
  if (!q) return props.zonas
  return props.zonas.filter(
    (z) =>
      (z.nombre || '').toLowerCase().includes(q) ||
      (z.descripcion || '').toLowerCase().includes(q) ||
      (z.proyecto || '').toLowerCase().includes(q)
  )
})

function confirmDelete(id) {
  if (confirm('¿Desea eliminar esta zona social?')) {
    router.delete(`/zonas-sociales/${id}`)
  }
}
</script>

<style scoped>
.btn-primary {
  @apply bg-brand-600 text-white px-4 py-2 rounded-md hover:bg-brand-700;
}
.icon-btn {
  @apply inline-flex items-center justify-center w-8 h-8 border rounded-md;
}
.icon-btn.info {
  @apply border-brand-300 text-brand-700 hover:bg-brand-50;
}
.icon-btn.warn {
  @apply border-amber-300 text-amber-600 hover:bg-amber-50;
}
.icon-btn.danger {
  @apply border-red-300 text-red-600 hover:bg-red-50;
}
</style>
