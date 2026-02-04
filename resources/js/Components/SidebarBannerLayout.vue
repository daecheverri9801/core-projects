<template>
  <div class="flex min-h-screen bg-brand-50">
    <!-- Sidebar -->
    <nav
      :class="[
        'bg-white/90 backdrop-blur border-r border-brand-200/50 flex flex-col transition-[width] duration-300',
        sidebarOpen ? 'w-64' : 'w-[72px]',
      ]"
    >
      <div class="flex items-center gap-3 px-4 py-5 border-b border-brand-200/50 justify-between">
        <div class="flex items-center gap-3 min-w-0">
          <Logo />
          <h1 v-if="sidebarOpen" class="text-lg font-semibold text-gray-900 whitespace-nowrap">
            Panel
          </h1>
        </div>

        <button
          @click="toggleSidebar"
          class="rounded-xl p-2 text-gray-500 hover:bg-brand-50 hover:text-gray-800 transition focus:outline-none focus:ring-2 focus:ring-brand-300"
          :aria-label="sidebarOpen ? 'Cerrar menú' : 'Abrir menú'"
        >
          <ChevronLeftIcon v-if="sidebarOpen" class="w-5 h-5" />
          <ChevronRightIcon v-else class="w-5 h-5" />
        </button>
      </div>

      <div class="px-2 py-3">
        <p
          v-if="sidebarOpen"
          class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider"
        >
          Navegación
        </p>

        <ul class="mt-2 space-y-1">
          <li v-for="item in navItems" :key="item.href">
            <Link
              :href="item.href"
              class="group flex items-center gap-3 px-3 py-2.5 rounded-xl font-semibold transition"
              :class="
                isActive(item.active)
                  ? 'bg-brand-100 text-gray-900'
                  : 'text-brand-800 hover:bg-brand-50'
              "
              :title="!sidebarOpen ? item.label : null"
            >
              <!-- ICONO CON PATRÓN DASHBOARD (contenedor + borde + fondo) -->
              <span
                class="shrink-0 rounded-2xl border p-2 transition"
                :class="
                  isActive(item.active)
                    ? 'border-brand-300 bg-brand-200'
                    : 'border-brand-200/60 bg-white group-hover:bg-brand-50'
                "
              >
                <component
                  :is="item.icon"
                  class="w-5 h-5 transition"
                  :class="
                    isActive(item.active)
                      ? 'text-brand-900'
                      : 'text-brand-700 group-hover:text-brand-900'
                  "
                />
              </span>

              <span v-if="sidebarOpen" class="truncate">{{ item.label }}</span>

              <span
                v-if="sidebarOpen && isActive(item.active)"
                class="ml-auto h-2 w-2 rounded-full bg-brand-500"
              ></span>
            </Link>
          </li>
        </ul>
      </div>

      <div class="mt-auto p-3">
        <div class="rounded-2xl border border-brand-200/60 bg-brand-50 p-3">
          <div class="flex items-center gap-3">
            <!-- ICONO CON EL MISMO PATRÓN -->
            <div class="rounded-2xl border border-brand-200/60 bg-white p-2">
              <BuildingOfficeIcon class="w-5 h-5 text-brand-700" />
            </div>

            <div v-if="sidebarOpen" class="min-w-0">
              <p class="text-xs text-gray-500">Constructora A&amp;C</p>
              <p class="text-sm font-semibold text-gray-900 truncate">Administración</p>
            </div>
          </div>
        </div>
      </div>
    </nav>

    <!-- Main -->
    <div class="flex-1 flex flex-col">
      <!-- Header -->
      <header class="sticky top-0 z-20 bg-white/80 backdrop-blur border-b border-brand-200/50">
        <div class="px-6 py-4 flex items-center justify-between">
          <div class="flex items-center gap-3 min-w-0">
            <Logo />
            <div class="min-w-0">
              <h1 class="text-lg font-semibold text-gray-900 truncate">Constructora A&C</h1>
              <p class="text-xs text-gray-500 truncate">Sistema integrado</p>
            </div>
          </div>

          <!-- User menu -->
          <div class="relative" ref="userMenuRef">
            <button
              @click="toggleMenu"
              class="flex items-center gap-3 rounded-2xl border border-brand-200/60 bg-white px-3 py-2 hover:bg-brand-50 transition focus:outline-none focus:ring-2 focus:ring-brand-300"
            >
              <div class="text-right max-w-[200px] sm:max-w-xs min-w-0">
                <div class="text-sm font-semibold text-gray-900 truncate" :title="empleadoCompleto">
                  {{ empleadoCompleto }}
                </div>
                <div
                  class="text-xs text-gray-500 flex items-center justify-end gap-1 truncate"
                  :title="cargoNombre"
                >
                  <UserIcon class="w-4 h-4 text-gray-400" />
                  {{ cargoNombre }}
                </div>
              </div>

              <ChevronDownIcon
                class="w-5 h-5 text-gray-600 transition"
                :class="menuOpen ? 'rotate-180' : ''"
              />
            </button>

            <transition name="fade">
              <div
                v-if="menuOpen"
                class="absolute right-0 mt-2 w-56 rounded-2xl border border-brand-200/60 bg-white shadow-lg overflow-hidden"
              >
                <div class="px-4 py-3 bg-brand-50 border-b border-brand-200/60">
                  <p class="text-xs text-gray-500">Sesión</p>
                  <p class="text-sm font-semibold text-gray-900 truncate">{{ empleadoCompleto }}</p>
                </div>

                <div class="py-2">
                  <Link
                    href="/perfil"
                    class="flex items-center gap-2 px-4 py-2 text-sm font-semibold text-gray-800 hover:bg-gray-50"
                  >
                    <IdentificationIcon class="w-5 h-5 text-brand-700" />
                    Perfil
                  </Link>

                  <button
                    type="button"
                    @click="logout"
                    class="w-full flex items-center gap-2 px-4 py-2 text-sm font-semibold text-gray-800 hover:bg-gray-50"
                  >
                    <ArrowLeftOnRectangleIcon class="w-5 h-5 text-brand-700" />
                    Logout
                  </button>
                </div>
              </div>
            </transition>
          </div>
        </div>
      </header>

      <!-- Content -->
      <main class="p-6 sm:p-8 overflow-auto flex-1">
        <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-6">
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
  ArrowLeftOnRectangleIcon,
} from '@heroicons/vue/24/outline'

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
const userMenuRef = ref(null)

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
  if (userMenuRef.value && !userMenuRef.value.contains(event.target)) {
    closeMenu()
  }
}

onMounted(() => document.addEventListener('click', handleClickOutside))
onBeforeUnmount(() => document.removeEventListener('click', handleClickOutside))

function logout() {
  router.post('/logout')
}

const currentRoute = computed(() => {
  const comp = usePage().component
  return typeof comp === 'string' ? comp.toLowerCase() : ''
})

function isActive(match) {
  const route = currentRoute.value
  if (typeof match === 'string') return route === match || route.startsWith(match)
  if (Array.isArray(match)) return match.some((m) => route === m || route.startsWith(m))
  if (typeof match === 'function') return match(route)
  return false
}

const navItems = [
  { label: 'Dashboard', href: '/dashboard', icon: HomeIcon, active: 'dashboard' },
  { label: 'Proyectos', href: '/proyectos', icon: FolderIcon, active: 'proyectos' },
  {
    label: 'Políticas de Precio',
    href: '/politicas-precio-proyecto',
    icon: FolderIcon,
    active: 'politicas-precio-proyecto',
  },
  { label: 'Torres', href: '/admin/torres', icon: BuildingOfficeIcon, active: 'admin/torres' },
  {
    label: 'Pisos de Torre',
    href: '/pisos-torre',
    icon: BuildingOfficeIcon,
    active: 'pisos-torre',
  },
  {
    label: 'Apartamentos',
    href: '/apartamentos',
    icon: BuildingOfficeIcon,
    active: 'apartamentos',
  },
  {
    label: 'Tipos Apartamento',
    href: '/tipos-apartamento',
    icon: BuildingOfficeIcon,
    active: 'tipos-apartamento',
  },
  { label: 'Locales', href: '/locales', icon: BuildingOfficeIcon, active: 'locales' },
  {
    label: 'Zonas Sociales',
    href: '/zonas-sociales',
    icon: BuildingOfficeIcon,
    active: 'zonas-sociales',
  },
  {
    label: 'Parqueaderos',
    href: '/parqueaderos',
    icon: BuildingOfficeIcon,
    active: 'parqueaderos',
  },
  { label: 'Empleados', href: '/empleados', icon: UsersIcon, active: 'empleados' },
  { label: 'Estados', href: '/estados', icon: CheckCircleIcon, active: 'estados' },
  {
    label: 'Dependencias y Cargos',
    href: '/dependencias-cargos',
    icon: IdentificationIcon,
    active: 'dependencias-cargos',
  },
]
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.15s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
