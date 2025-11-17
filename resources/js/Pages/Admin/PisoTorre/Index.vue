<template>
  <SidebarBannerLayout :empleado="empleado">
    <template #title>Pisos de Torre</template>

    <div class="bg-white rounded-lg border p-4 md:p-6">
      <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-4">
        <h2 class="text-lg font-semibold text-brand-900">Listado</h2>
        <div class="flex items-center gap-3">
          <input
            v-model="search"
            type="text"
            placeholder="Buscar por proyecto, torre, uso o nivel"
            class="w-full md:w-64 border rounded-md px-3 py-2 text-sm"
          />
          <Link
            href="/pisos-torre/create"
            class="inline-flex items-center gap-2 px-3 py-2 rounded-md bg-brand-600 text-white hover:bg-brand-700"
          >
            <PlusIcon class="w-5 h-5" /> Nuevo piso
          </Link>
        </div>
      </div>

      <div class="overflow-x-auto">
        <table class="min-w-full table-auto border-collapse">
          <thead>
            <tr class="bg-brand-50 text-left text-sm text-brand-800">
              <th class="px-3 py-2 border">ID</th>
              <th class="px-3 py-2 border">Proyecto</th>
              <th class="px-3 py-2 border">Torre</th>
              <th class="px-3 py-2 border">Nivel</th>
              <th class="px-3 py-2 border">Uso</th>
              <th class="px-3 py-2 border text-center">Aptos</th>
              <th class="px-3 py-2 border w-40">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="p in filtered" :key="p.id_piso_torre" class="border-b hover:bg-gray-50">
              <td class="px-3 py-2 border">{{ p.id_piso_torre }}</td>
              <td class="px-3 py-2 border">{{ p.proyecto || '—' }}</td>
              <td class="px-3 py-2 border">{{ p.torre || '—' }}</td>
              <td class="px-3 py-2 border">{{ p.nivel }}</td>
              <td class="px-3 py-2 border">{{ p.uso || '—' }}</td>
              <td class="px-3 py-2 border text-center">
                <span
                  class="inline-flex items-center justify-center min-w-8 px-2 py-1 text-xs font-semibold rounded-full bg-brand-100 text-brand-800 border border-brand-200"
                >
                  {{ p.apartamentos_count ?? 0 }}
                </span>
              </td>
              <td class="px-3 py-2 border">
                <div class="flex items-center gap-2">
                  <Link
                    :href="`/pisos-torre/${p.id_piso_torre}`"
                    class="icon-btn info"
                    :title="'Ver'"
                  >
                    <EyeIcon class="w-5 h-5" />
                  </Link>
                  <Link
                    :href="`/pisos-torre/${p.id_piso_torre}/edit`"
                    class="icon-btn warn"
                    :title="'Editar'"
                  >
                    <PencilSquareIcon class="w-5 h-5" />
                  </Link>
                  <button
                    class="icon-btn danger"
                    @click="confirmDelete(p.id_piso_torre)"
                    :title="'Eliminar'"
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
    </div>

    <FlashMessages />
  </SidebarBannerLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import SidebarBannerLayout from '@/Components/SidebarBannerLayout.vue'
import FlashMessages from '@/Components/FlashMessages.vue'

import { PlusIcon, EyeIcon, PencilSquareIcon, TrashIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  pisos: { type: Array, default: () => [] },
  empleado: { type: Object, default: null },
})

const search = ref('')

const filtered = computed(() => {
  const q = search.value.toLowerCase().trim()
  if (!q) return props.pisos
  return props.pisos.filter(
    (p) =>
      String(p.id_piso_torre).includes(q) ||
      (p.proyecto || '').toLowerCase().includes(q) ||
      (p.torre || '').toLowerCase().includes(q) ||
      String(p.nivel).includes(q) ||
      (p.uso || '').toLowerCase().includes(q) ||
      String(p.apartamentos_count ?? 0).includes(q) // incluye conteo
  )
})

function confirmDelete(id) {
  // Modal/confirmación simple; si tienes un modal global reemplázalo
  if (confirm('¿Eliminar este piso? Esta acción no se puede deshacer.')) {
    router.delete(`/pisos-torre/${id}`)
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
