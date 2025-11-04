<template>
  <div class="flex min-h-screen bg-brand-50">
    <!-- Sidebar vertical colapsable -->
    <nav
      :class="[
        'bg-white border-r border-brand-200/30 flex flex-col transition-width duration-300',
        sidebarOpen ? 'w-64' : 'w-16'
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

      <!-- Contenido detalle proyecto -->
      <main class="p-8 overflow-auto max-w-4xl mx-auto">
        <div class="flex justify-between items-center mb-6">
          <h2 class="text-3xl font-bold text-gray-900">Detalle Proyecto</h2>
          <Link
            :href="`/proyectos/${proyecto.id_proyecto}/edit`"
            class="inline-flex items-center gap-2 rounded bg-brand-500 px-5 py-3 text-white font-semibold shadow hover:bg-brand-600 transition"
          >
            <PencilIcon class="w-5 h-5" />
            Editar
          </Link>
        </div>

        <div class="bg-white rounded-lg shadow p-6 space-y-6">
          <div>
            <h3 class="text-lg font-semibold text-gray-700 mb-2">Nombre</h3>
            <p class="text-gray-900">{{ proyecto.nombre }}</p>
          </div>

          <div>
            <h3 class="text-lg font-semibold text-gray-700 mb-2">Descripción</h3>
            <p class="text-gray-900 whitespace-pre-line">{{ proyecto.descripcion || '-' }}</p>
          </div>

          <div class="grid grid-cols-2 gap-6">
            <div>
              <h3 class="text-lg font-semibold text-gray-700 mb-2">Estado</h3>
              <p class="text-gray-900">{{ proyecto.estado_proyecto?.nombre || '-' }}</p>
            </div>
            <div>
              <h3 class="text-lg font-semibold text-gray-700 mb-2">Ubicación</h3>
              <p class="text-gray-900">
                {{ proyecto.ubicacion?.direccion || '-' }}, {{ proyecto.ubicacion?.ciudad?.nombre || '-' }}
              </p>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-6">
            <div>
              <h3 class="text-lg font-semibold text-gray-700 mb-2">Fecha Inicio</h3>
              <p class="text-gray-900">{{ proyecto.fecha_inicio || '-' }}</p>
            </div>
            <div>
              <h3 class="text-lg font-semibold text-gray-700 mb-2">Fecha Finalización</h3>
              <p class="text-gray-900">{{ proyecto.fecha_finalizacion || '-' }}</p>
            </div>
          </div>

          <div class="grid grid-cols-3 gap-6">
            <div>
              <h3 class="text-lg font-semibold text-gray-700 mb-2">Presupuesto Inicial</h3>
              <p class="text-gray-900">{{ proyecto.presupuesto_inicial || '-' }}</p>
            </div>
            <div>
              <h3 class="text-lg font-semibold text-gray-700 mb-2">Presupuesto Final</h3>
              <p class="text-gray-900">{{ proyecto.presupuesto_final || '-' }}</p>
            </div>
            <div>
              <h3 class="text-lg font-semibold text-gray-700 mb-2">Metros Construidos</h3>
              <p class="text-gray-900">{{ proyecto.metros_construidos || '-' }}</p>
            </div>
          </div>

          <div class="grid grid-cols-4 gap-6">
            <div>
              <h3 class="text-lg font-semibold text-gray-700 mb-2">Cantidad Locales</h3>
              <p class="text-gray-900">{{ proyecto.cantidad_locales || '-' }}</p>
            </div>
            <div>
              <h3 class="text-lg font-semibold text-gray-700 mb-2">Cantidad Apartamentos</h3>
              <p class="text-gray-900">{{ proyecto.cantidad_apartamentos || '-' }}</p>
            </div>
            <div>
              <h3 class="text-lg font-semibold text-gray-700 mb-2">Parqueaderos Vehículo</h3>
              <p class="text-gray-900">{{ proyecto.cantidad_parqueaderos_vehiculo || '-' }}</p>
            </div>
            <div>
              <h3 class="text-lg font-semibold text-gray-700 mb-2">Parqueaderos Moto</h3>
              <p class="text-gray-900">{{ proyecto.cantidad_parqueaderos_moto || '-' }}</p>
            </div>
          </div>

          <div class="grid grid-cols-3 gap-6">
            <div>
              <h3 class="text-lg font-semibold text-gray-700 mb-2">Estrato</h3>
              <p class="text-gray-900">{{ proyecto.estrato || '-' }}</p>
            </div>
            <div>
              <h3 class="text-lg font-semibold text-gray-700 mb-2">Número Pisos</h3>
              <p class="text-gray-900">{{ proyecto.numero_pisos || '-' }}</p>
            </div>
            <div>
              <h3 class="text-lg font-semibold text-gray-700 mb-2">Número Torres</h3>
              <p class="text-gray-900">{{ proyecto.numero_torres || '-' }}</p>
            </div>
          </div>

          <div class="grid grid-cols-3 gap-6">
            <div>
              <h3 class="text-lg font-semibold text-gray-700 mb-2">Porcentaje Cuota Inicial Mínima</h3>
              <p class="text-gray-900">{{ proyecto.porcentaje_cuota_inicial_min || '-' }}</p>
            </div>
            <div>
              <h3 class="text-lg font-semibold text-gray-700 mb-2">Valor Mínimo Separación</h3>
              <p class="text-gray-900">{{ proyecto.valor_min_separacion || '-' }}</p>
            </div>
            <div>
              <h3 class="text-lg font-semibold text-gray-700 mb-2">Plazo Cuota Inicial (meses)</h3>
              <p class="text-gray-900">{{ proyecto.plazo_cuota_inicial_meses || '-' }}</p>
            </div>
          </div>
        </div>
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, usePage } from '@inertiajs/inertia-vue3'
import { Inertia } from '@inertiajs/inertia'
import Logo from '@/Components/Logo.vue'

import {
  UserIcon,
  ChevronDownIcon,
  FolderIcon,
  UsersIcon,
  CheckCircleIcon,
  BuildingOfficeIcon,
  HomeIcon,
  PencilIcon,
  ChevronLeftIcon,
  ChevronRightIcon,
} from '@heroicons/vue/24/outline'

const props = defineProps({
  proyecto: Object,
  empleado: Object,
})

const empleadoCompleto = computed(() => {
  if (!props.empleado) return 'Usuario'
  return [props.empleado.nombre, props.empleado.apellido].filter(Boolean).join(' ')
})

const menuOpen = ref(false)
const sidebarOpen = ref(true)

function toggleMenu() {
  menuOpen.value = !menuOpen.value
}

function toggleSidebar() {
  sidebarOpen.value = !sidebarOpen.value
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

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onBeforeUnmount(() => {
  document.removeEventListener('click', handleClickOutside)
})

function logout() {
  Inertia.post('/logout')
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

/* Transición para sidebar */
nav {
  transition-property: width;
  transition-duration: 300ms;
  transition-timing-function: ease;
}
</style>