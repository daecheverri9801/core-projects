<template>
  <div class="flex min-h-screen bg-brand-50">
    <!-- Sidebar vertical -->
    <nav
      :class="[
        'bg-white border-r border-brand-200/30 flex flex-col transition-width duration-300',
        sidebarOpen ? 'w-64' : 'w-16',
      ]"
    >
      <div class="flex items-center gap-4 px-4 py-5 border-b border-brand-200/30 justify-between">
        <div class="flex items-center gap-4">
          <Logo class="w-10 h-10" />
          <h1 v-if="sidebarOpen" class="text-xl font-semibold text-brand-900 whitespace-nowrap">
            Panel
          </h1>
        </div>
        <button
          @click="toggleSidebar"
          class="text-gray-500 hover:text-gray-700 focus:outline-none"
          :aria-label="sidebarOpen ? 'Cerrar menú' : 'Abrir menú'"
        >
          <ChevronLeftIcon v-if="sidebarOpen" class="w-6 h-6 transition-transform" />
          <ChevronRightIcon v-else class="w-6 h-6 transition-transform" />
        </button>
      </div>
      <ul class="flex flex-col mt-6 space-y-1 px-2">
        <li>
          <Link
            href="/dashboard"
            class="flex items-center gap-3 px-4 py-3 rounded-md font-semibold text-brand-700 hover:bg-brand-100"
            :class="{ 'bg-brand-100 text-brand-900': currentRoute === 'dashboard' }"
          >
            <HomeIcon class="w-6 h-6" />
            <span v-if="sidebarOpen">Dashboard</span>
          </Link>
        </li>
        <li>
          <Link
            href="/proyectos"
            class="flex items-center gap-3 px-4 py-3 rounded-md font-semibold text-brand-700 hover:bg-brand-100"
            :class="{ 'bg-brand-100 text-brand-900': currentRoute.startsWith('proyectos') }"
          >
            <FolderIcon class="w-6 h-6" />
            <span v-if="sidebarOpen">Proyectos</span>
          </Link>
        </li>
        <li>
          <Link
            href="/empleados"
            class="flex items-center gap-3 px-4 py-3 rounded-md font-semibold text-brand-700 hover:bg-brand-100"
            :class="{ 'bg-brand-100 text-brand-900': currentRoute.startsWith('empleados') }"
          >
            <UsersIcon class="w-6 h-6" />
            <span v-if="sidebarOpen">Empleados</span>
          </Link>
        </li>
        <li>
          <Link
            href="/estados"
            class="flex items-center gap-3 px-4 py-3 rounded-md font-semibold text-brand-700 hover:bg-brand-100"
            :class="{ 'bg-brand-100 text-brand-900': currentRoute.startsWith('estados') }"
          >
            <CheckCircleIcon class="w-6 h-6" />
            <span v-if="sidebarOpen">Estados</span>
          </Link>
        </li>
        <li>
          <Link
            href="/dependencias-cargos"
            class="flex items-center gap-3 px-4 py-3 rounded-md font-semibold text-brand-700 hover:bg-brand-100"
            :class="{
              'bg-brand-100 text-brand-900': currentRoute.startsWith('dependencias-cargos'),
            }"
          >
            <BuildingOfficeIcon class="w-6 h-6" />
            <span v-if="sidebarOpen">Dependencias y Cargos</span>
          </Link>
        </li>
      </ul>
    </nav>

    <!-- Contenido principal -->
    <div class="flex-1 flex flex-col">
      <!-- Banner superior -->
      <header
        class="bg-brand-500/5 border-b border-brand-200/30 px-6 py-4 flex items-center justify-between"
      >
        <div class="flex items-center gap-4">
          <Logo class="w-10 h-10" />
          <h1 class="text-xl font-semibold text-brand-900">Constructora A&C</h1>
        </div>

        <!-- Usuario y menú -->
        <div class="relative" id="user-menu">
          <button @click="toggleMenu" class="flex items-center gap-2 focus:outline-none">
            <div class="text-right max-w-[180px] sm:max-w-xs truncate">
              <div class="font-semibold text-gray-800" :title="empleadoCompleto">
                {{ empleadoCompleto }}
              </div>
              <div
                class="text-sm text-gray-500 flex items-center gap-1 truncate"
                :title="empleado?.cargo?.nombre || 'Cargo'"
              >
                <UserIcon class="w-4 h-4 text-gray-400" />
                {{ empleado?.cargo?.nombre || 'Cargo' }}
              </div>
            </div>
            <ChevronDownIcon class="w-5 h-5 text-gray-600" />
          </button>

          <transition name="fade">
            <ul
              v-if="menuOpen"
              class="absolute right-0 mt-2 w-40 bg-white border rounded shadow-md z-10"
            >
              <li>
                <Link href="/perfil" class="block px-4 py-2 hover:bg-gray-100">Perfil</Link>
              </li>
              <li>
                <button @click="logout" class="w-full text-left px-4 py-2 hover:bg-gray-100">
                  Logout
                </button>
              </li>
            </ul>
          </transition>
        </div>
      </header>

      <!-- Contenido tabla -->
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

            <!-- Paginación -->
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
  <!-- Footer -->
  <footer class="bg-white border-t py-4 text-center text-sm text-gray-500">
    © {{ new Date().getFullYear() }} Constructora A&C. Todos los derechos reservados.
  </footer>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, router, usePage } from '@inertiajs/inertia-vue3'
import { Inertia } from '@inertiajs/inertia'
import {
  UserIcon,
  ChevronDownIcon,
  ChevronLeftIcon,
  ChevronRightIcon,
  HomeIcon,
  FolderIcon,
  UsersIcon,
  CheckCircleIcon,
  BuildingOfficeIcon,
  PlusIcon,
  EyeIcon,
  PencilIcon,
  TrashIcon,
} from '@heroicons/vue/24/outline'
import Logo from '@/Components/Logo.vue'

const props = defineProps({
  proyectos: Object,
  empleado: Object,
})

const empleadoCompleto = computed(() => {
  if (!props.empleado) return 'Usuario'
  return [props.empleado.nombre, props.empleado.apellido].filter(Boolean).join(' ')
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
  Inertia.post('/logout')
}

const proyectoAEliminar = ref(null)
const showConfirmDelete = ref(false)

function confirmarEliminar() {
  Inertia.delete(`/proyectos/${proyectoAEliminar.value}`)
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
