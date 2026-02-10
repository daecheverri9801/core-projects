<template>
  <div class="min-h-screen bg-brand-50">
    <!-- Header (banner superior) -->
    <header class="sticky top-0 z-20 bg-white/80 backdrop-blur border-b border-brand-200/50">
      <div class="px-6 py-4 flex items-center justify-between">
        <div class="flex items-center gap-3 min-w-0">
          <Logo />
          <div class="min-w-0">
            <h1 class="text-lg font-semibold text-gray-900 truncate">
              Constructora A&amp;C
            </h1>
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
import { Link, router } from '@inertiajs/vue3'
import Logo from '@/Components/Logo.vue'

import {
  UserIcon,
  ChevronDownIcon,
  IdentificationIcon,
  ArrowLeftOnRectangleIcon,
} from '@heroicons/vue/24/outline'

const props = defineProps({
  empleado: Object,
  panelName: {
    type: String,
    default: 'Panel administrativo',
  },
})

const empleadoCompleto = computed(() => {
  if (!props.empleado) return 'Usuario'
  return [props.empleado.nombre, props.empleado.apellido].filter(Boolean).join(' ')
})

const cargoNombre = computed(() => props.empleado?.cargo?.nombre || 'Cargo')

const menuOpen = ref(false)
const userMenuRef = ref(null)

function toggleMenu() {
  menuOpen.value = !menuOpen.value
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
