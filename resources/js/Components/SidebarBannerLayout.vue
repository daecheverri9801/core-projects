<template>
  <div class="flex min-h-screen bg-brand-50">
    <!-- Sidebar vertical colapsable -->
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
            href="/politicas-precio-proyecto"
            class="flex items-center gap-3 px-4 py-3 rounded-md font-semibold text-brand-700 hover:bg-brand-100"
            :class="{
              'bg-brand-100 text-brand-900': currentRoute.startsWith('politicas-precio-proyecto'),
            }"
          >
            <FolderIcon class="w-6 h-6" />
            <span v-if="sidebarOpen">Políticas de Precio</span>
          </Link>
        </li>
        <li>
          <Link
            href="/admin/torres"
            class="flex items-center gap-3 px-4 py-3 rounded-md font-semibold text-brand-700 hover:bg-brand-100"
            :class="{ 'bg-brand-100 text-brand-900': currentRoute.startsWith('admin/torres') }"
          >
            <BuildingOfficeIcon class="w-6 h-6" />
            <span v-if="sidebarOpen">Torres</span>
          </Link>
        </li>
        <li>
          <Link
            href="/pisos-torre"
            class="flex items-center gap-3 px-4 py-3 rounded-md font-semibold text-brand-700 hover:bg-brand-100"
            :class="{
              'bg-brand-100 text-brand-900': currentRoute.startsWith('pisos-torre'),
            }"
          >
            <BuildingOfficeIcon class="w-6 h-6" />
            <span v-if="sidebarOpen">Pisos de Torre</span>
          </Link>
        </li>
        <li>
          <Link
            href="/apartamentos"
            class="flex items-center gap-3 px-4 py-3 rounded-md font-semibold text-brand-700 hover:bg-brand-100"
            :class="{
              'bg-brand-100 text-brand-900': currentRoute.startsWith('apartamentos'),
            }"
          >
            <BuildingOfficeIcon class="w-6 h-6" />
            <span v-if="sidebarOpen">Apartamentos</span>
          </Link>
        </li>
        <li>
          <Link
            href="/tipos-apartamento"
            class="flex items-center gap-3 px-4 py-3 rounded-md font-semibold text-brand-700 hover:bg-brand-100"
            :class="{
              'bg-brand-100 text-brand-900': currentRoute.startsWith('tipos-apartamento'),
            }"
          >
            <BuildingOfficeIcon class="w-6 h-6" />
            <span v-if="sidebarOpen">Tipos Apartamento</span>
          </Link>
        </li>
        <li>
          <Link
            href="/locales"
            class="flex items-center gap-3 px-4 py-3 rounded-md font-semibold text-brand-700 hover:bg-brand-100"
            :class="{
              'bg-brand-100 text-brand-900': currentRoute.startsWith('locales'),
            }"
          >
            <BuildingOfficeIcon class="w-6 h-6" />
            <span v-if="sidebarOpen">Locales</span>
          </Link>
        </li>
        <li>
          <Link
            href="/zonas-sociales"
            class="flex items-center gap-3 px-4 py-3 rounded-md font-semibold text-brand-700 hover:bg-brand-100"
            :class="{
              'bg-brand-100 text-brand-900': currentRoute.startsWith('zonas-sociales'),
            }"
          >
            <BuildingOfficeIcon class="w-6 h-6" />
            <span v-if="sidebarOpen">Zonas Sociales</span>
          </Link>
        </li>
        <li>
          <Link
            href="/parqueaderos"
            class="flex items-center gap-3 px-4 py-3 rounded-md font-semibold text-brand-700 hover:bg-brand-100"
            :class="{
              'bg-brand-100 text-brand-900': currentRoute.startsWith('parqueaderos'),
            }"
          >
            <BuildingOfficeIcon class="w-6 h-6" />
            <span v-if="sidebarOpen">Parqueaderos</span>
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
            <IdentificationIcon class="w-6 h-6" />
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
                :title="cargoNombre"
              >
                <UserIcon class="w-4 h-4 text-gray-400" />
                {{ cargoNombre }}
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

      <main class="p-8 overflow-auto flex-1">
        <h2 class="text-3xl font-bold text-gray-900 mb-6">
          <slot name="title" />
        </h2>
        <slot />
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import { Link, usePage, router } from '@inertiajs/vue3'
import Logo from '@/Components/Logo.vue'

import {
  UserIcon,
  ChevronDownIcon,
  UsersIcon,
  FolderIcon,
  CheckCircleIcon,
  BuildingOfficeIcon,
  HomeIcon,
  ChevronLeftIcon,
  ChevronRightIcon,
  IdentificationIcon,
} from '@heroicons/vue/24/outline'

const page = usePage()
// const empleado = computed(() => page.props.auth?.empleado || null)

const props = defineProps({
  empleado: Object,
})

const empleadoCompleto = computed(() => {
  if (!props.empleado) return 'Usuario'
  return [props.empleado.nombre, props.empleado.apellido].filter(Boolean).join(' ')
})

const cargoNombre = computed(() => props.empleado?.cargo?.nombre || 'Cargo')

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

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onBeforeUnmount(() => {
  document.removeEventListener('click', handleClickOutside)
})

function logout() {
  router.post('/logout')
}

const currentRoute = computed(() => {
  const comp = usePage().component
  return typeof comp === 'string' ? comp.toLowerCase() : ''
})
</script>

<style scoped>
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
