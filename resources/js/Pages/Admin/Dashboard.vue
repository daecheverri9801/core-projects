<template>
  <div class="flex flex-col min-h-screen bg-brand-50">
    <!-- Banner superior -->
    <header
      class="bg-brand-500/5 border-b border-brand-200/30 px-6 py-3 flex items-center justify-between"
    >
      <div class="flex items-center gap-4">
        <Logo class="w-10 h-10" />
        <h1 class="text-xl font-semibold text-brand-900">Constructora A&C</h1>
      </div>

      <!-- Usuario y menú -->
      <div class="relative" id="user-menu">
        <button @click="toggleMenu" class="flex items-center gap-2 focus:outline-none">
          <div class="text-right max-w-[180px] truncate">
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

    <!-- Contenido principal -->
    <main class="flex-grow p-6 max-w-6xl mx-auto">
      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
        <AdminButton icon="FolderIcon" text="Proyectos" href="/proyectos" />
        <AdminButton icon="UsersIcon" text="Empleados" href="/empleados" />
        <AdminButton icon="CheckCircleIcon" text="Estados" href="/estados" />
        <AdminButton icon="BuildingOfficeIcon" text="Dependencias y Cargos" href="/dependencias-cargos" />
        <AdminButton icon="HomeIcon" text="Torres" href="/admin/torres" />
        <AdminButton icon="HomeIcon" text="Pisos Torre" href="/pisos-torre" />
        <AdminButton icon="HomeIcon" text="Apartamentos" href="/apartamentos" />
        <AdminButton icon="HomeIcon" text="Tipos Apartamento" href="/tipos-apartamento" />
        <AdminButton icon="HomeIcon" text="Locales" href="/locales" />
        <AdminButton icon="HomeIcon" text="Zonas Sociales" href="/zonas-sociales" />
        <AdminButton icon="HomeIcon" text="Parqueaderos" href="/parqueaderos" />
        <AdminButton icon="HomeIcon" text="Politicas" href="/politicas-precio-proyecto" />
      </div>
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t py-4 text-center text-sm text-gray-500">
      © {{ new Date().getFullYear() }} Constructora A&C. Todos los derechos reservados.
    </footer>
  </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount, computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import Logo from '@/Components/Logo.vue'
import AdminButton from '@/Components/AdminButton.vue'

import {
  UserIcon,
  ChevronDownIcon,
  HomeIcon,
} from '@heroicons/vue/24/outline'

const props = defineProps({
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

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onBeforeUnmount(() => {
  document.removeEventListener('click', handleClickOutside)
})

function logout() {
  router.post('/logout')
}
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
</style>