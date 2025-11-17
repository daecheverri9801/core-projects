<template>
  <SidebarBannerLayout :empleado="empleado">
    <div class="flex min-h-screen bg-brand-50">
      <div class="flex-1 flex flex-col">
        <main class="p-8">
          <div class="overflow-x-auto w-full">
            <div class="max-w-full min-w-[900px]">
              <div class="flex justify-between items-center mb-6">
                <h2 class="text-3xl font-bold text-gray-900">Proyectos</h2>
                <Link
                  href="/proyectos/create"
                  class="inline-flex items-center gap-2 rounded bg-brand-500 px-5 py-3 text-white font-semibold shadow hover:bg-brand-600 transition"
                >
                  <PlusIcon class="w-5 h-5" />
                  Crear Proyecto
                </Link>
              </div>

              <table
                class="w-full divide-y divide-gray-200 rounded-lg border border-gray-200 shadow-sm table-fixed"
              >
                <thead class="bg-gray-50">
                  <tr>
                    <th
                      class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider break-words"
                      style="width: 5%"
                    >
                      ID
                    </th>
                    <th
                      class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider break-words"
                      style="width: 25%"
                    >
                      Nombre
                    </th>
                    <th
                      class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider break-words"
                      style="width: 15%"
                    >
                      Estado
                    </th>
                    <th
                      class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider break-words"
                      style="width: 25%"
                    >
                      Ubicación
                    </th>
                    <th
                      class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider break-words"
                      style="width: 10%"
                    >
                      Fecha Inicio
                    </th>
                    <th
                      class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider break-words"
                      style="width: 10%"
                    >
                      Fecha Finalización
                    </th>
                    <th
                      class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider"
                      style="width: 10%"
                    >
                      Acciones
                    </th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr
                    v-for="proyecto in proyectos.data"
                    :key="proyecto.id_proyecto"
                    class="hover:bg-gray-50"
                  >
                    <td class="px-6 py-4 text-sm text-gray-900 break-words">
                      {{ proyecto.id_proyecto }}
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-900 break-words">
                      {{ proyecto.nombre }}
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-900 break-words">
                      {{ proyecto.estado_proyecto?.nombre || '-' }}
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-900 break-words">
                      {{ proyecto.ubicacion?.direccion }},
                      {{ proyecto.ubicacion?.ciudad?.nombre || '-' }}
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-900 break-words">
                      {{ proyecto.fecha_inicio || '-' }}
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-900 break-words">
                      {{ proyecto.fecha_finalizacion || '-' }}
                    </td>
                    <td
                      class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium"
                      style="width: 60px"
                    >
                      <div class="flex flex-col items-center space-y-2">
                        <Link
                          :href="`/proyectos/${proyecto.id_proyecto}`"
                          class="flex items-center gap-1 text-blue-600 hover:text-blue-800 transition"
                          title="Ver proyecto"
                        >
                          <EyeIcon class="w-5 h-5" />
                          <span class="sr-only">Ver</span>
                        </Link>

                        <Link
                          :href="`/proyectos/${proyecto.id_proyecto}/edit`"
                          class="flex items-center gap-1 text-green-600 hover:text-green-800 transition"
                          title="Editar proyecto"
                        >
                          <PencilIcon class="w-5 h-5" />
                          <span class="sr-only">Editar</span>
                        </Link>

                        <button
                          @click="eliminar(proyecto.id_proyecto)"
                          class="flex items-center gap-1 text-red-600 hover:text-red-800 transition"
                          title="Eliminar proyecto"
                        >
                          <TrashIcon class="w-5 h-5" />
                          <span class="sr-only">Eliminar</span>
                        </button>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>

              <div class="mt-6 flex justify-center gap-3">
                <button
                  :disabled="!proyectos.prev_page_url"
                  @click="cambiarPagina(proyectos.current_page - 1)"
                  class="rounded border px-4 py-2 disabled:opacity-50"
                >
                  Anterior
                </button>
                <button
                  :disabled="!proyectos.next_page_url"
                  @click="cambiarPagina(proyectos.current_page + 1)"
                  class="rounded border px-4 py-2 disabled:opacity-50"
                >
                  Siguiente
                </button>
              </div>
            </div>
          </div>
        </main>
      </div>
      <div
        v-if="showConfirmDelete"
        class="fixed inset-0 bg-black bg-opacity-30 flex items-center justify-center z-50"
      >
        <div class="bg-white rounded-lg p-6 max-w-sm w-full shadow-lg">
          <h3 class="text-lg font-semibold mb-4">Confirmar eliminación</h3>
          <p class="mb-6">¿Estás seguro de eliminar este proyecto?</p>
          <div class="flex justify-end gap-4">
            <button
              @click="cancelarEliminar"
              class="px-4 py-2 rounded border border-gray-300 hover:bg-gray-100"
            >
              Cancelar
            </button>
            <button
              @click="confirmarEliminar"
              class="px-4 py-2 rounded bg-red-600 text-white hover:bg-red-700"
            >
              Eliminar
            </button>
          </div>
        </div>
      </div>
    </div>

    <footer class="bg-white border-t py-4 text-center text-sm text-gray-500">
      © {{ new Date().getFullYear() }} Constructora A&C. Todos los derechos reservados.
    </footer>
  </SidebarBannerLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import {
  PlusIcon,
  EyeIcon,
  PencilIcon,
  TrashIcon,
} from '@heroicons/vue/24/outline'
import SidebarBannerLayout from '@/Components/SidebarBannerLayout.vue'

const props = defineProps({
  proyectos: Object,
  empleado: Object,
})

const page = usePage()
const empleado = computed(() => page.props.auth?.empleado)

const empleadoCompleto = computed(() => {
  if (!empleado.value) return 'Usuario'
  return [empleado.value.nombre, empleado.value.apellido].filter(Boolean).join(' ')
})

const menuOpen = ref(false)

function toggleMenu() {
  menuOpen.value = !menuOpen.value
}

function closeMenu() {
  menuOpen.value = false
}

function handleClickOutside(event) {
  const menu = document.getElementById('user-menu')
  if (menu && !menu.contains(event.target)) {
    closeMenu()
  }
}

import { onMounted, onBeforeUnmount } from 'vue'

const sidebarOpen = ref(true)

function toggleSidebar() {
  sidebarOpen.value = !sidebarOpen.value
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onBeforeUnmount(() => {
  document.removeEventListener('click', handleClickOutside)
})

function logout() {
  router.post('/logout')
}

const proyectoAEliminar = ref(null)
const showConfirmDelete = ref(false)

function confirmarEliminar() {
  router.delete(`/proyectos/${proyectoAEliminar.value}`)
  showConfirmDelete.value = false
  proyectoAEliminar.value = null
}

function cancelarEliminar() {
  showConfirmDelete.value = false
  proyectoAEliminar.value = null
}

function eliminar(id) {
  proyectoAEliminar.value = id
  showConfirmDelete.value = true
}

function cambiarPagina(pagina) {
  router.visit(`/proyectos?page=${pagina}`)
}

// Obtener ruta actual para resaltar menú
const currentRoute = computed(() => {
  const comp = usePage().component
  return typeof comp === 'string' ? comp.toLowerCase() : ''
})
</script>

<style scoped>
/* Transición para menú */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
nav {
  transition-property: width;
  transition-duration: 300ms;
  transition-timing-function: ease;
}
</style>
