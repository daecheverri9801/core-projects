<template>
  <TopBannerLayout :empleado="empleado" panel-name="Panel administrador">
    <Head title="Empleados" />

    <div class="space-y-6">
      <PageHeader title="Empleados" subtitle="Consulta, filtra y administra los empleados registrados.">
        <template #actions>
          <Link
            href="/empleados/create"
            class="inline-flex items-center gap-2 rounded-xl bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700 transition"
          >
            <PlusIcon class="h-5 w-5" />
            Nuevo empleado
          </Link>
        </template>
      </PageHeader>

      <!-- Filtros -->
      <!-- <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-4 md:p-6">
        <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
          <div class="md:col-span-8">
            <label class="block text-[11px] font-semibold text-gray-600 uppercase tracking-wider mb-1">
              Buscar
            </label>

            <div
              class="group relative rounded-2xl border border-gray-200 bg-white shadow-sm transition
                     focus-within:border-brand-400 focus-within:ring-2 focus-within:ring-brand-200/70"
            >
              <MagnifyingGlassIcon
                class="h-5 w-5 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2 transition
                       group-focus-within:text-brand-600"
              />

              <input
                v-model="filters.search"
                @input="search"
                type="text"
                class="w-full rounded-2xl bg-transparent pl-10 pr-10 py-3 text-sm text-gray-900
                       placeholder:text-gray-400 focus:outline-none"
                placeholder="Buscar por nombre, apellido o email"
              />

              <button
                v-show="filters.search"
                type="button"
                @click="clearSearch"
                class="absolute right-2 top-1/2 -translate-y-1/2 rounded-xl p-2
                       text-gray-400 hover:text-gray-700 hover:bg-gray-50 transition"
                title="Limpiar"
                aria-label="Limpiar búsqueda"
              >
                ✕
              </button>
            </div>

            <p class="mt-1 text-xs text-gray-500">
              {{ empleados?.total ?? 0 }} resultado(s)
            </p>
          </div>

          <div class="md:col-span-4 flex md:justify-end">
            <Link
              href="/empleados/create"
              class="w-full md:w-auto inline-flex items-center justify-center gap-2 rounded-xl
                     bg-brand-600 px-4 py-3 text-sm font-semibold text-white hover:bg-brand-700 transition"
            >
              <PlusIcon class="h-5 w-5" />
              Nuevo empleado
            </Link>
          </div>
        </div>
      </div> -->

      <!-- Tabla -->
      <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-[900px] w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                  Nombre
                </th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                  Apellido
                </th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                  Email
                </th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                  Cargo
                </th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                  Dependencia
                </th>
                <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">
                  Acciones
                </th>
              </tr>
            </thead>

            <tbody class="divide-y divide-gray-100 bg-white">
              <tr
                v-for="emp in empleados.data"
                :key="emp.id_empleado"
                class="hover:bg-gray-50/70 transition"
              >
                <td class="px-6 py-4 text-sm font-semibold text-gray-900 whitespace-nowrap">
                  {{ emp.nombre }}
                </td>
                <td class="px-6 py-4 text-sm text-gray-700 whitespace-nowrap">
                  {{ emp.apellido }}
                </td>
                <td class="px-6 py-4 text-sm text-gray-700">
                  <span class="break-all">{{ emp.email }}</span>
                </td>
                <td class="px-6 py-4 text-sm text-gray-700">
                  {{ emp.cargo?.nombre || '—' }}
                </td>
                <td class="px-6 py-4 text-sm text-gray-700">
                  {{ emp.dependencia?.nombre || '—' }}
                </td>
                <td class="px-6 py-4">
                  <div class="flex items-center justify-center gap-2">
                    <Link
                      :href="`/empleados/${emp.id_empleado}`"
                      class="icon-btn text-blue-700 hover:bg-blue-50"
                      title="Ver empleado"
                      aria-label="Ver empleado"
                    >
                      <EyeIcon class="w-5 h-5" />
                    </Link>

                    <Link
                      :href="`/empleados/${emp.id_empleado}/edit`"
                      class="icon-btn text-emerald-700 hover:bg-emerald-50"
                      title="Editar empleado"
                      aria-label="Editar empleado"
                    >
                      <PencilIcon class="w-5 h-5" />
                    </Link>

                    <button
                      type="button"
                      @click="confirmDelete(emp.id_empleado)"
                      class="icon-btn text-red-700 hover:bg-red-50"
                      title="Eliminar empleado"
                      aria-label="Eliminar empleado"
                    >
                      <TrashIcon class="w-5 h-5" />
                    </button>
                  </div>
                </td>
              </tr>

              <tr v-if="empleados.data?.length === 0">
                <td colspan="6" class="px-6 py-10 text-center text-sm text-gray-600">
                  No hay empleados registrados.
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Footer paginación -->
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between px-4 md:px-6 py-4 border-t bg-gray-50">
          <p class="text-xs text-gray-600">
            Página <span class="font-semibold text-gray-900">{{ empleados.current_page }}</span>
            de <span class="font-semibold text-gray-900">{{ empleados.last_page }}</span>
            · Total <span class="font-semibold text-gray-900">{{ empleados.total }}</span>
          </p>

          <div class="flex items-center gap-2 justify-end">
            <button
              :disabled="!empleados.prev_page_url"
              @click="changePage(empleados.current_page - 1)"
              class="btn-pagination"
            >
              Anterior
            </button>

            <button
              :disabled="!empleados.next_page_url"
              @click="changePage(empleados.current_page + 1)"
              class="btn-pagination"
            >
              Siguiente
            </button>
          </div>
        </div>
      </div>

      <!-- Modal confirmación eliminación -->
      <ConfirmDeleteModal v-if="showConfirmDelete" @confirm="deleteEmpleado" @cancel="cancelDelete" />
    </div>
  </TopBannerLayout>
</template>

<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { ref, reactive } from 'vue'
import { EyeIcon, PencilIcon, TrashIcon, PlusIcon, MagnifyingGlassIcon } from '@heroicons/vue/24/outline'

import ConfirmDeleteModal from '@/Components/ConfirmDeleteModal.vue'
import TopBannerLayout from '@/Components/TopBannerLayout.vue'
import PageHeader from '@/Components/PageHeader.vue'

const props = defineProps({
  empleado: Object,
  empleados: Object,
  filters: Object,
})

const filters = reactive({ search: props.filters?.search ?? '' })

const showConfirmDelete = ref(false)
const empleadoToDelete = ref(null)

function search() {
  router.get('/empleados', { search: filters.search }, { preserveState: true, replace: true })
}

function clearSearch() {
  filters.search = ''
  search()
}

function changePage(page) {
  router.get('/empleados', { page, search: filters.search }, { preserveState: true, replace: true })
}

function confirmDelete(id) {
  empleadoToDelete.value = id
  showConfirmDelete.value = true
}

function deleteEmpleado() {
  router.delete(`/empleados/${empleadoToDelete.value}`, {
    preserveScroll: true,
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
  @apply inline-flex items-center justify-center rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-800
    hover:bg-gray-50 transition disabled:opacity-50 disabled:cursor-not-allowed;
}
.icon-btn {
  @apply inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white p-2 transition;
}
</style>
