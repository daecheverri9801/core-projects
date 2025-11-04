<template>
  <SidebarBannerLayout>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4 w-full">
      <!-- Título a la izquierda -->
      <h1 class="text-3xl font-bold text-gray-900 flex-shrink-0">Empleados</h1>

      <!-- Contenedor central para el input de búsqueda -->
      <div class="flex-grow max-w-md mx-auto w-full">
        <input
          v-model="filters.search"
          @input="search"
          type="text"
          placeholder="Buscar por nombre, apellido o email"
          class="block w-full rounded-md border border-gray-300 px-4 py-2 shadow-sm placeholder-gray-400 focus:border-brand-500 focus:ring-1 focus:ring-brand-500"
        />
      </div>

      <!-- Botón a la derecha -->
      <div class="flex-shrink-0">
        <Link
          href="/empleados/create"
          class="inline-flex items-center gap-2 rounded bg-brand-500 px-5 py-3 text-white font-semibold shadow hover:bg-brand-600 transition whitespace-nowrap"
        >
          Nuevo Empleado
        </Link>
      </div>
    </div>

    <table
      class="w-full table-fixed divide-y divide-gray-200 rounded-lg border border-gray-200 shadow-sm"
    >
      <thead class="bg-gray-100">
        <tr>
          <th
            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
          >
            Nombre
          </th>
          <th
            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
          >
            Apellido
          </th>
          <th
            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
          >
            Email
          </th>
          <th
            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
          >
            Cargo
          </th>
          <th
            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
          >
            Dependencia
          </th>
          <th
            style="width: 80px"
            class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider"
          >
            Acciones
          </th>
        </tr>
      </thead>
      <tbody class="bg-white divide-y divide-gray-200">
        <tr
          v-for="empleado in empleados.data"
          :key="empleado.id_empleado"
          class="hover:bg-gray-50 align-top"
        >
          <td class="px-6 py-4 text-sm font-medium text-gray-900 break-words">
            {{ empleado.nombre }}
          </td>
          <td class="px-6 py-4 text-sm text-gray-500 break-words">{{ empleado.apellido }}</td>
          <td class="px-6 py-4 text-sm text-gray-500 break-words">{{ empleado.email }}</td>
          <td class="px-6 py-4 text-sm text-gray-500 break-words">
            {{ empleado.cargo?.nombre || '-' }}
          </td>
          <td class="px-6 py-4 text-sm text-gray-500 break-words">
            {{ empleado.dependencia?.nombre || '-' }}
          </td>
          <td class="px-6 py-4 text-sm text-center align-top">
            <div class="flex flex-col items-center space-y-2">
              <Link
                :href="`/empleados/${empleado.id_empleado}`"
                class="text-blue-600 hover:text-blue-800 transition"
                title="Ver empleado"
              >
                <EyeIcon class="w-5 h-5" />
              </Link>

              <Link
                :href="`/empleados/${empleado.id_empleado}/edit`"
                class="text-green-600 hover:text-green-800 transition"
                title="Editar empleado"
              >
                <PencilIcon class="w-5 h-5" />
              </Link>

              <button
                @click="confirmDelete(empleado.id_empleado)"
                class="text-red-600 hover:text-red-800 transition"
                title="Eliminar empleado"
              >
                <TrashIcon class="w-5 h-5" />
              </button>
            </div>
          </td>
        </tr>
      </tbody>
    </table>

    <!-- Paginación -->
    <div class="mt-4 flex justify-center space-x-2">
      <button
        :disabled="!empleados.prev_page_url"
        @click="changePage(empleados.current_page - 1)"
        class="btn-pagination"
      >
        Anterior
      </button>
      <span class="px-3 py-1 border rounded"
        >{{ empleados.current_page }} / {{ empleados.last_page }}</span
      >
      <button
        :disabled="!empleados.next_page_url"
        @click="changePage(empleados.current_page + 1)"
        class="btn-pagination"
      >
        Siguiente
      </button>
    </div>

    <!-- Modal confirmación eliminación -->
    <ConfirmDeleteModal v-if="showConfirmDelete" @confirm="deleteEmpleado" @cancel="cancelDelete" />
  </SidebarBannerLayout>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { Link, usePage } from '@inertiajs/inertia-vue3'
import { Inertia } from '@inertiajs/inertia'
import { EyeIcon, PencilIcon, TrashIcon } from '@heroicons/vue/24/outline'
import ConfirmDeleteModal from '@/Components/ConfirmDeleteModal.vue'
import SidebarBannerLayout from '@/Components/SidebarBannerLayout.vue'

const props = defineProps({
  empleados: Object,
  filters: Object,
})

const filters = reactive({ ...props.filters })
const showConfirmDelete = ref(false)
const empleadoToDelete = ref(null)

function search() {
  Inertia.get('/empleados', { search: filters.search }, { preserveState: true, replace: true })
}

function changePage(page) {
  Inertia.get(
    '/empleados',
    { page, search: filters.search },
    { preserveState: true, replace: true }
  )
}

function confirmDelete(id) {
  empleadoToDelete.value = id
  showConfirmDelete.value = true
}

function deleteEmpleado() {
  Inertia.delete(`/empleados/${empleadoToDelete.value}`, {
    onSuccess: () => {
      showConfirmDelete.value = false
      empleadoToDelete.value = null
    },
  })
}

function cancelDelete() {
  showConfirmDelete.value = false
  empleadoToDelete.value = null
}
</script>

<style scoped>
.btn-pagination {
  padding: 0.25rem 0.75rem;
  border: 1px solid #d1d5db;
  border-radius: 0.375rem;
  background-color: white;
  cursor: pointer;
}
.btn-pagination:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}
</style>
