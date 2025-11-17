<template>
  <SidebarBannerLayout :empleado="empleado">
    <template #title>Torres</template>

    <div class="bg-white rounded-lg shadow p-6">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div class="flex items-center gap-2 w-full sm:w-auto">
          <input
            v-model="localFilters.search"
            @keyup.enter="doSearch"
            type="text"
            placeholder="Buscar por nombre..."
            class="flex-1 sm:flex-none rounded-md border border-gray-300 px-4 py-2 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 w-full sm:w-72"
          />
          <button
            @click="doSearch"
            class="rounded bg-brand-500 px-4 py-2 text-white font-semibold hover:bg-brand-600"
          >
            Buscar
          </button>
        </div>

        <Link
          :href="route('admin.torres.create')"
          class="rounded bg-brand-600 px-4 py-2 text-white font-semibold hover:bg-brand-700"
        >
          Nueva Torre
        </Link>
      </div>

      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nombre</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Proyecto</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pisos</th>
              <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Acciones</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100 bg-white">
            <tr v-for="torre in torres.data" :key="torre.id_torre">
              <td class="px-4 py-3 text-sm text-gray-700">{{ torre.id_torre }}</td>
              <td class="px-4 py-3 text-sm text-gray-900 font-medium">{{ torre.nombre_torre }}</td>
              <td class="px-4 py-3 text-sm text-gray-700">{{ torre.proyecto?.nombre || '-' }}</td>
              <td class="px-4 py-3 text-sm">
                <span class="px-2 py-1 rounded text-xs font-semibold bg-brand-100 text-brand-800">
                  {{ torre.estado?.nombre || '-' }}
                </span>
              </td>
              <td class="px-4 py-3 text-sm text-gray-700">{{ torre.numero_pisos ?? '-' }}</td>
              <td class="px-4 py-3">
                <div class="flex justify-end gap-2">
                  <Link
                    :href="route('admin.torres.show', torre.id_torre)"
                    class="px-3 py-1 rounded border text-gray-700 hover:bg-gray-50"
                    title="Ver"
                  >Ver</Link>
                  <Link
                    :href="route('admin.torres.edit', torre.id_torre)"
                    class="px-3 py-1 rounded border text-gray-700 hover:bg-gray-50"
                    title="Editar"
                  >Editar</Link>
                  <button
                    @click="confirmDelete(torre)"
                    class="px-3 py-1 rounded border border-red-300 text-red-600 hover:bg-red-50"
                    title="Eliminar"
                  >Eliminar</button>
                </div>
              </td>
            </tr>
            <tr v-if="torres.data.length === 0">
              <td colspan="6" class="px-4 py-6 text-center text-gray-500">No hay registros</td>
            </tr>
          </tbody>
        </table>
      </div>

      <div v-if="torres.links && torres.links.length > 0" class="mt-6 flex justify-end">
        <nav class="flex items-center gap-1">
          <template v-for="(link, idx) in torres.links" :key="idx">
            <span
              v-if="!link.url"
              class="px-3 py-1 text-sm text-gray-400"
              v-html="link.label"
            ></span>
            <Link
              v-else
              :href="link.url"
              class="px-3 py-1 text-sm rounded border"
              :class="link.active ? 'bg-brand-600 text-white border-brand-600' : 'hover:bg-gray-50'"
              v-html="link.label"
            />
          </template>
        </nav>
      </div>
    </div>

    <!-- Modal eliminación -->
    <transition name="fade">
      <div v-if="deleteModal.open" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow max-w-md w-full p-6">
          <h3 class="text-lg font-semibold mb-2">Eliminar torre</h3>
          <p class="text-gray-600 mb-6">
            ¿Seguro que deseas eliminar la torre
            <strong>{{ deleteModal.torre?.nombre_torre }}</strong>?
          </p>
          <div class="flex justify-end gap-3">
            <button @click="deleteModal.open=false" class="px-4 py-2 rounded border hover:bg-gray-50">Cancelar</button>
            <button @click="doDelete" class="px-4 py-2 rounded bg-red-600 text-white hover:bg-red-700">Eliminar</button>
          </div>
        </div>
      </div>
    </transition>
  </SidebarBannerLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import SidebarBannerLayout from '@/Components/SidebarBannerLayout.vue'

const props = defineProps({
  torres: Object,
  filters: Object,
  empleado: Object,
})

const localFilters = ref({
  search: props.filters?.search || ''
})

function doSearch() {
  Inertia.get(route('admin.torres.index'), { search: localFilters.value.search }, { preserveState: true, replace: true })
}

const deleteModal = ref({ open: false, torre: null })
function confirmDelete(torre) {
  deleteModal.value = { open: true, torre }
}
function doDelete() {
  if (!deleteModal.value.torre) return
  router.delete(route('admin.torres.destroy', deleteModal.value.torre.id_torre), {
    onFinish: () => { deleteModal.value = { open: false, torre: null } }
  })
}
</script>