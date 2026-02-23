<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <header class="sticky top-0 z-40 bg-white border-b border-gray-200 shadow-sm">
      <div class="mx-auto max-w-7xl px-4 sm:px-6 py-3">
        <div class="flex items-center justify-between">
          <!-- Logo y Nombre (izquierda) -->
          <div class="flex items-center gap-3">
            <div
              class="w-10 h-10 rounded-lg bg-gradient-to-br from-[#FFEA00] to-[#D1C000] flex items-center justify-center shadow-md"
            >
              <Logo class="w-7 h-7" />
            </div>
            <div>
              <h1 class="text-xl font-bold text-gray-900">Constructora A&C</h1>
              <!-- <p class="text-xs text-gray-500">Módulo de Contabilidad</p> -->
            </div>
          </div>

          <nav class="flex flex-wrap gap-2 mt-3">
            <Link
              href="/contabilidad/ventas"
              class="px-4 py-2 rounded-lg text-sm font-semibold transition"
              :class="
                isActive('/contabilidad/ventas')
                  ? 'bg-[#FFEA00] text-[#474100] shadow-sm'
                  : 'text-gray-700 hover:bg-gray-100'
              "
            >
              Ventas
            </Link>

            <Link
              href="/contabilidad/reportes/plan-pagos-ci"
              class="px-4 py-2 rounded-lg text-sm font-semibold transition"
              :class="
                isActive('/contabilidad/reportes/plan-pagos-ci')
                  ? 'bg-[#FFEA00] text-[#474100] shadow-sm'
                  : 'text-gray-700 hover:bg-gray-100'
              "
            >
              Plan Pagos CI
            </Link>
          </nav>

          <!-- Usuario y Logout (derecha) -->
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
                class="absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-lg border border-gray-200 py-2 z-50"
              >
                <div class="px-4 py-3 border-b border-gray-100">
                  <p class="text-sm font-semibold text-gray-900">{{ empleadoCompleto }}</p>
                  <p class="text-xs text-gray-500 mt-1">{{ empleado?.correo || cargoNombre }}</p>
                </div>

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

        <!-- Navegación (pestañas) -->
      </div>
    </header>

    <!-- Contenido principal -->
    <main class="mx-auto max-w-7xl px-4 sm:px-6 py-6">
      <slot />
    </main>
  </div>
  <ConfirmDialog
    :open="showLogoutModal"
    title="Cerrar sesión"
    message="¿Está seguro que desea cerrar sesión?"
    confirm-text="Sí, cerrar sesión"
    cancel-text="Cancelar"
    @cancel="showLogoutModal = false"
    @confirm="handleLogoutConfirm"
  />
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue'
import { Link, usePage, router } from '@inertiajs/vue3'
import Logo from '@/Components/Logo.vue'
import ConfirmDialog from '@/Components/ConfirmDialog.vue'
import { ChevronDownIcon, ArrowRightOnRectangleIcon } from '@heroicons/vue/24/outline'

const page = usePage()
const empleado = computed(() => page.props?.auth?.empleado ?? page.props?.empleado ?? null)
const showLogoutModal = ref(false)

// Nombre completo del empleado
const empleadoCompleto = computed(() => {
  const emp = empleado.value
  if (!emp) return 'Usuario'
  return [emp.nombre, emp.apellido].filter(Boolean).join(' ') || 'Usuario'
})

// Cargo del empleado (si existe)
const cargoNombre = computed(() => empleado.value?.cargo?.nombre || 'Contador')

// Iniciales para el avatar
function getInitials(name) {
  if (!name) return 'U'
  return name
    .split(' ')
    .map((n) => n[0])
    .join('')
    .toUpperCase()
    .slice(0, 2)
}

// Menú de usuario
const userMenuOpen = ref(false)
const userMenuRef = ref(null)

function toggleUserMenu() {
  userMenuOpen.value = !userMenuOpen.value
}

function logout() {
  showLogoutModal.value = true
}

function handleLogoutConfirm() {
  router.post('/logout')
  showLogoutModal.value = false // opcional, el post redirigirá
}

// Cerrar menú al hacer clic fuera
function handleClickOutside(event) {
  if (userMenuRef.value && !userMenuRef.value.contains(event.target)) {
    userMenuOpen.value = false
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})

// Detectar ruta activa
function isActive(path) {
  const url = page.url || ''
  return url.startsWith(path)
}
</script>

<style scoped>
.dropdown-enter-active,
.dropdown-leave-active {
  transition:
    opacity 0.2s ease,
    transform 0.2s ease;
}
.dropdown-enter-from,
.dropdown-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}
</style>
