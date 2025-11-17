<template>
  <div class="flex flex-col min-h-screen bg-[#FFF9B8]">
    <!-- Header Superior -->
    <header class="bg-white shadow-md sticky top-0 z-50">
      <!-- Barra Superior -->
      <div class="border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div class="flex items-center justify-between h-16">
            <!-- Logo y Nombre -->
            <div class="flex items-center gap-3">
              <div
                class="w-10 h-10 rounded-lg bg-gradient-to-br from-[#FFEA00] to-[#D1C000] flex items-center justify-center shadow-md"
              >
                <Logo class="w-7 h-7" />
              </div>
              <div>
                <h1 class="text-xl font-bold text-gray-900">Constructora A&C</h1>
                <p class="text-xs text-gray-500">Módulo de Ventas</p>
              </div>
            </div>

            <!-- Usuario y Logout -->
            <div class="relative" ref="userMenuRef">
              <button
                @click="toggleUserMenu"
                class="flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-gray-50 transition"
              >
                <div
                  class="w-9 h-9 rounded-full bg-gradient-to-br from-[#FFEA00] to-[#D1C000] flex items-center justify-center text-[#474100] font-bold shadow-sm"
                >
                  {{ getInitials(empleadoCompleto) }}
                </div>
                <div class="text-left hidden sm:block">
                  <div class="font-semibold text-gray-800 text-sm">{{ empleadoCompleto }}</div>
                  <div class="text-xs text-gray-500">{{ cargoNombre }}</div>
                </div>
                <ChevronDownIcon
                  class="w-5 h-5 text-gray-600 transition-transform"
                  :class="{ 'rotate-180': userMenuOpen }"
                />
              </button>

              <!-- Dropdown Menu -->
              <transition name="dropdown">
                <div
                  v-if="userMenuOpen"
                  class="absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-lg border border-gray-200 py-2"
                >
                  <div class="px-4 py-3 border-b border-gray-100">
                    <p class="text-sm font-semibold text-gray-900">{{ empleadoCompleto }}</p>
                    <p class="text-xs text-gray-500 mt-1">{{ empleado?.correo || cargoNombre }}</p>
                  </div>
                  <Link
                    href="/perfil"
                    class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition"
                  >
                    <UserIcon class="w-5 h-5 text-gray-400" />
                    Mi Perfil
                  </Link>
                  <button
                    @click="logout"
                    class="w-full flex items-center gap-3 px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition"
                  >
                    <ArrowRightOnRectangleIcon class="w-5 h-5" />
                    Cerrar Sesión
                  </button>
                </div>
              </transition>
            </div>
          </div>
        </div>
      </div>

      <!-- Navegación de Módulos -->
      <nav class="bg-gradient-to-r from-[#FFEA00] to-[#FFF15C]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div class="flex items-center space-x-1 overflow-x-auto">
            <Link
              v-for="modulo in modulos"
              :key="modulo.name"
              :href="modulo.href"
              class="flex items-center gap-2 px-4 py-3 text-sm font-semibold whitespace-nowrap transition-all duration-200 border-b-3"
              :class="
                isActive(modulo.href)
                  ? 'text-[#1A1700] border-[#1A1700] bg-white/20'
                  : 'text-[#474100] border-transparent hover:bg-white/10 hover:text-[#1A1700]'
              "
            >
              <component :is="modulo.icon" class="w-5 h-5" />
              {{ modulo.name }}
            </Link>
          </div>
        </div>
      </nav>
    </header>

    <!-- Contenido Principal -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <slot />
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 mt-auto">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
        <div class="flex flex-col sm:flex-row items-center justify-between gap-2">
          <p class="text-sm text-gray-600">
            © {{ currentYear }} Constructora A&C - Todos los derechos reservados
          </p>
          <p class="text-xs text-gray-500">Módulo de Ventas v1.0</p>
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import { Link, usePage, router } from '@inertiajs/vue3' // ✅ Mantenido en v0.6.0
import Logo from '@/Components/Logo.vue'
import {
  UserGroupIcon,
  ShoppingCartIcon,
  CreditCardIcon,
  DocumentTextIcon,
  ClipboardDocumentListIcon,
  CalculatorIcon,
  UserIcon,
  ChevronDownIcon,
  ArrowRightOnRectangleIcon,
  Squares2X2Icon,
} from '@heroicons/vue/24/outline'

const props = defineProps({
  empleado: { type: Object, default: null },
  cliente: { type: Object, default: null },
  apartamento: { type: Object, default: null },
  local: { type: Object, default: null },
  proyecto: { type: Object, default: null },
  formaPago: { type: Object, default: null },
  estadoVenta: { type: Object, default: null },
})

const page = usePage()

// ✅ CORREGIDO: Acceso seguro a las props en v0.6.0
const empleado = computed(() => {
  // Primero usar la prop directa
  if (props.empleado) return props.empleado

  // Luego intentar con page.props.value
  if (page.props && page.props.value) {
    return page.props.auth?.empleado || page.props.empleado || null
  }

  return null
})

const empleadoCompleto = computed(() => {
  const emp = empleado.value
  if (!emp) return 'Usuario'
  return [emp.nombre, emp.apellido].filter(Boolean).join(' ') || 'Usuario'
})

const cargoNombre = computed(() => empleado.value?.cargo?.nombre || 'Vendedor')

const currentYear = new Date().getFullYear()

const userMenuOpen = ref(false)
const userMenuRef = ref(null)

const modulos = [
  {
    name: 'Clientes',
    href: '/clientes',
    icon: UserGroupIcon,
  },
  { name: 'Catálogo', href: '/catalogo', icon: Squares2X2Icon  },
  {
    name: 'Ventas',
    href: '/ventas',
    icon: ShoppingCartIcon,
  },
  {
    name: 'Pagos',
    href: '/pagos',
    icon: CreditCardIcon,
  },
  {
    name: 'Plan Amortización Venta',
    href: '/plan-amortizacion-venta',
    icon: DocumentTextIcon,
  },
  {
    name: 'Plan Amortización Cuota',
    href: '/plan-amortizacion-cuota',
    icon: ClipboardDocumentListIcon,
  },
  {
    name: 'Cotizador',
    href: '/cotizador',
    icon: CalculatorIcon,
  },
]

function isActive(href) {
  // ✅ CORREGIDO: Acceso seguro a page.url en v0.6.0
  const currentPath = page.url && page.url.value ? page.url.value : window.location.pathname
  return currentPath.startsWith(href)
}

function toggleUserMenu() {
  userMenuOpen.value = !userMenuOpen.value
}

function closeUserMenu() {
  userMenuOpen.value = false
}

function handleClickOutside(event) {
  if (userMenuRef.value && !userMenuRef.value.contains(event.target)) {
    closeUserMenu()
  }
}

function getInitials(name) {
  if (!name) return 'U'
  const parts = name.split(' ')
  return parts.length > 1
    ? `${parts[0][0]}${parts[1][0]}`.toUpperCase()
    : name.substring(0, 2).toUpperCase()
}

function logout() {
  if (confirm('¿Está seguro que desea cerrar sesión?')) {
    Inertia.post('/logout') // ✅ Usar Inertia directamente
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onBeforeUnmount(() => {
  document.removeEventListener('click', handleClickOutside)
})
</script>

<style scoped>
.border-b-3 {
  border-bottom-width: 3px;
}

.dropdown-enter-active,
.dropdown-leave-active {
  transition: all 0.2s ease;
}

.dropdown-enter-from,
.dropdown-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}

/* Scrollbar personalizado para navegación */
nav::-webkit-scrollbar {
  height: 4px;
}

nav::-webkit-scrollbar-track {
  background: transparent;
}

nav::-webkit-scrollbar-thumb {
  background: rgba(71, 65, 0, 0.3);
  border-radius: 2px;
}

nav::-webkit-scrollbar-thumb:hover {
  background: rgba(71, 65, 0, 0.5);
}
</style>
