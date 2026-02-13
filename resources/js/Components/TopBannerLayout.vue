<template>
  <div class="min-h-screen bg-brand-50">
    <!-- Header (banner superior) -->
    <header class="sticky top-0 z-30 bg-white/80 backdrop-blur border-b border-brand-200/50">
      <div class="px-6 py-4 flex items-center justify-between">
        <div class="flex items-center gap-3 min-w-0">
          <Logo />
          <div class="min-w-0">
            <h1 class="text-lg font-semibold text-gray-900 truncate">Constructora A&amp;C</h1>
            <p class="text-xs text-gray-500 truncate">
              {{ panelName }}
            </p>
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
                <p class="text-sm font-semibold text-gray-900 truncate">
                  {{ empleadoCompleto }}
                </p>
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

      <!-- Tabs -->
      <div class="px-6 pb-3">
        <div class="rounded-2xl border border-brand-200/60 bg-white/70 backdrop-blur px-2 py-2">
          <!-- Mobile: scroll -->
          <div class="flex items-center gap-1 overflow-x-auto no-scrollbar md:hidden">
            <Link
              v-for="t in tabs"
              :key="t.href"
              :href="t.href"
              class="group inline-flex items-center gap-2 whitespace-nowrap rounded-2xl px-3 py-2 text-sm font-semibold transition"
              :class="
                isActive(t.active)
                  ? 'bg-brand-200 border border-brand-300 text-brand-950 shadow-sm'
                  : 'text-brand-800 hover:bg-brand-50'
              "
              :title="t.label"
            >
              <span
                class="rounded-2xl border p-1.5 transition"
                :class="
                  isActive(t.active)
                    ? 'border-brand-300 bg-white'
                    : 'border-brand-200/60 bg-white group-hover:bg-brand-50'
                "
              >
                <component
                  :is="t.icon"
                  class="w-4 h-4 transition"
                  :class="
                    isActive(t.active)
                      ? 'text-brand-900'
                      : 'text-brand-700 group-hover:text-brand-900'
                  "
                />
              </span>

              <span>{{ t.label }}</span>
              <span v-if="isActive(t.active)" class="ml-1 h-2 w-2 rounded-full bg-brand-500"></span>
            </Link>
          </div>

          <!-- Desktop: una sola fila horizontal, distribuida 1/7 -->
          <div class="hidden md:flex md:items-center md:gap-1">
            <Link
              v-for="t in tabs"
              :key="t.href"
              :href="t.href"
              class="group flex-1 min-w-0 inline-flex items-center justify-center gap-2 rounded-2xl px-3 py-2 text-sm font-semibold transition"
              :class="
                isActive(t.active)
                  ? 'bg-brand-200 border border-brand-300 text-brand-950 shadow-sm'
                  : 'text-brand-800 hover:bg-brand-50 border border-transparent'
              "
              :title="t.label"
            >
              <span
                class="rounded-2xl border p-1.5 transition shrink-0"
                :class="
                  isActive(t.active)
                    ? 'border-brand-300 bg-white'
                    : 'border-brand-200/60 bg-white group-hover:bg-brand-50'
                "
              >
                <component
                  :is="t.icon"
                  class="w-4 h-4 transition"
                  :class="
                    isActive(t.active)
                      ? 'text-brand-900'
                      : 'text-brand-700 group-hover:text-brand-900'
                  "
                />
              </span>

              <span class="truncate">{{ t.label }}</span>
              <span
                v-if="isActive(t.active)"
                class="ml-1 h-2 w-2 rounded-full bg-brand-500 shrink-0"
              ></span>
            </Link>
          </div>
        </div>
      </div>
    </header>

    <!-- Content -->
    <main class="p-6 sm:p-8 overflow-auto">
      <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-6">
        <slot name="title" />
      </h2>

      <slot />
    </main>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import Logo from '@/Components/Logo.vue'

import {
  UserIcon,
  ChevronDownIcon,
  IdentificationIcon,
  ArrowLeftOnRectangleIcon,
  HomeIcon,
  FolderIcon,
  ChartBarIcon,
  UserGroupIcon,
  UsersIcon,
  CheckCircleIcon,
} from '@heroicons/vue/24/outline'

const props = defineProps({
  empleado: Object,
  panelName: {
    type: String,
    default: 'Panel administrativo',
  },
})

/** User */
const empleadoCompleto = computed(() => {
  if (!props.empleado) return 'Usuario'
  return [props.empleado.nombre, props.empleado.apellido].filter(Boolean).join(' ')
})
const cargoNombre = computed(() => props.empleado?.cargo?.nombre || 'Cargo')

/** Dropdown */
const menuOpen = ref(false)
const userMenuRef = ref(null)

function toggleMenu() {
  menuOpen.value = !menuOpen.value
}
function closeMenu() {
  menuOpen.value = false
}
function handleClickOutside(event) {
  if (userMenuRef.value && !userMenuRef.value.contains(event.target)) closeMenu()
}

onMounted(() => document.addEventListener('click', handleClickOutside))
onBeforeUnmount(() => document.removeEventListener('click', handleClickOutside))

function logout() {
  router.post('/logout')
}

/** Active tab detection (by URL) */
const page = usePage()
const currentPath = computed(() => {
  const url = page.url || '/'
  return url.split('?')[0].replace(/\/+$/, '') || '/'
})

function isActive(match) {
  const path = currentPath.value
  if (typeof match === 'string') return path === match || path.startsWith(match + '/')
  if (Array.isArray(match)) return match.some((m) => path === m || path.startsWith(m + '/'))
  if (typeof match === 'function') return match(path)
  return false
}

/** Tabs */
const tabs = [
  { label: 'Dashboard', href: '/dashboard', icon: HomeIcon, active: '/dashboard' },
  { label: 'Proyectos', href: '/proyectos', icon: FolderIcon, active: '/proyectos' },
  { label: 'Ventas', href: '/admin/ventas', icon: ChartBarIcon, active: '/admin/ventas' },
  { label: 'Clientes', href: '/admin/clientes', icon: UserGroupIcon, active: '/admin/clientes' },
  { label: 'Empleados', href: '/empleados', icon: UsersIcon, active: '/empleados' },
  { label: 'Estados', href: '/estados', icon: CheckCircleIcon, active: '/estados' },
  {
    label: 'Dependencias y Cargos',
    href: '/dependencias-cargos',
    icon: IdentificationIcon,
    active: '/dependencias-cargos',
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

.no-scrollbar::-webkit-scrollbar {
  display: none;
}
.no-scrollbar {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
</style>
