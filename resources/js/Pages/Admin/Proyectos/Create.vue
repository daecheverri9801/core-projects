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

      <!-- Formulario -->
      <main class="p-8 overflow-auto">
        <div class="max-w-3xl mx-auto bg-white rounded-lg shadow p-8">
          <h2 class="text-3xl font-bold mb-6 text-gray-900">Crear Proyecto</h2>

          <form @submit.prevent="submit" class="space-y-6">
            <!-- Nombre -->
            <div>
              <label for="nombre" class="block text-sm font-medium text-gray-700 mb-1"
                >Nombre <span class="text-red-500">*</span></label
              >
              <input
                id="nombre"
                v-model="form.nombre"
                type="text"
                class="block w-full rounded-md border border-gray-300 px-4 py-2 focus:border-brand-500 focus:ring-1 focus:ring-brand-500"
              />
              <p v-if="form.errors.nombre" class="mt-1 text-sm text-red-600">
                {{ form.errors.nombre }}
              </p>
            </div>

            <!-- Descripción -->
            <div>
              <label for="descripcion" class="block text-sm font-medium text-gray-700 mb-1"
                >Descripción</label
              >
              <textarea
                id="descripcion"
                v-model="form.descripcion"
                rows="3"
                class="block w-full rounded-md border border-gray-300 px-4 py-2 focus:border-brand-500 focus:ring-1 focus:ring-brand-500"
              ></textarea>
              <p v-if="form.errors.descripcion" class="mt-1 text-sm text-red-600">
                {{ form.errors.descripcion }}
              </p>
            </div>

            <!-- Fechas -->
            <div class="grid grid-cols-2 gap-6">
              <div>
                <label for="fecha_inicio" class="block text-sm font-medium text-gray-700 mb-1"
                  >Fecha Inicio</label
                >
                <input
                  id="fecha_inicio"
                  v-model="form.fecha_inicio"
                  type="date"
                  class="block w-full rounded-md border border-gray-300 px-4 py-2 focus:border-brand-500 focus:ring-1 focus:ring-brand-500"
                />
                <p v-if="form.errors.fecha_inicio" class="mt-1 text-sm text-red-600">
                  {{ form.errors.fecha_inicio }}
                </p>
              </div>

              <div>
                <label for="fecha_finalizacion" class="block text-sm font-medium text-gray-700 mb-1"
                  >Fecha Finalización</label
                >
                <input
                  id="fecha_finalizacion"
                  v-model="form.fecha_finalizacion"
                  type="date"
                  class="block w-full rounded-md border border-gray-300 px-4 py-2 focus:border-brand-500 focus:ring-1 focus:ring-brand-500"
                />
                <p v-if="form.errors.fecha_finalizacion" class="mt-1 text-sm text-red-600">
                  {{ form.errors.fecha_finalizacion }}
                </p>
              </div>
            </div>

            <!-- Presupuestos y metros -->
            <div class="grid grid-cols-3 gap-6">
              <div>
                <label
                  for="presupuesto_inicial"
                  class="block text-sm font-medium text-gray-700 mb-1"
                  >Presupuesto Inicial</label
                >
                <input
                  id="presupuesto_inicial"
                  v-model="form.presupuesto_inicial"
                  type="number"
                  step="0.01"
                  min="0"
                  class="block w-full rounded-md border border-gray-300 px-4 py-2 focus:border-brand-500 focus:ring-1 focus:ring-brand-500"
                />
                <p v-if="form.errors.presupuesto_inicial" class="mt-1 text-sm text-red-600">
                  {{ form.errors.presupuesto_inicial }}
                </p>
              </div>

              <div>
                <label for="presupuesto_final" class="block text-sm font-medium text-gray-700 mb-1"
                  >Presupuesto Final</label
                >
                <input
                  id="presupuesto_final"
                  v-model="form.presupuesto_final"
                  type="number"
                  step="0.01"
                  min="0"
                  class="block w-full rounded-md border border-gray-300 px-4 py-2 focus:border-brand-500 focus:ring-1 focus:ring-brand-500"
                />
                <p v-if="form.errors.presupuesto_final" class="mt-1 text-sm text-red-600">
                  {{ form.errors.presupuesto_final }}
                </p>
              </div>

              <div>
                <label for="metros_construidos" class="block text-sm font-medium text-gray-700 mb-1"
                  >Metros Construidos</label
                >
                <input
                  id="metros_construidos"
                  v-model="form.metros_construidos"
                  type="number"
                  step="0.01"
                  min="0"
                  class="block w-full rounded-md border border-gray-300 px-4 py-2 focus:border-brand-500 focus:ring-1 focus:ring-brand-500"
                />
                <p v-if="form.errors.metros_construidos" class="mt-1 text-sm text-red-600">
                  {{ form.errors.metros_construidos }}
                </p>
              </div>
            </div>

            <!-- Cantidades -->
            <div class="grid grid-cols-4 gap-6">
              <div>
                <label for="cantidad_locales" class="block text-sm font-medium text-gray-700 mb-1"
                  >Cantidad Locales</label
                >
                <input
                  id="cantidad_locales"
                  v-model="form.cantidad_locales"
                  type="number"
                  min="0"
                  class="block w-full rounded-md border border-gray-300 px-4 py-2 focus:border-brand-500 focus:ring-1 focus:ring-brand-500"
                />
                <p v-if="form.errors.cantidad_locales" class="mt-1 text-sm text-red-600">
                  {{ form.errors.cantidad_locales }}
                </p>
              </div>

              <div>
                <label
                  for="cantidad_apartamentos"
                  class="block text-sm font-medium text-gray-700 mb-1"
                  >Cantidad Apartamentos</label
                >
                <input
                  id="cantidad_apartamentos"
                  v-model="form.cantidad_apartamentos"
                  type="number"
                  min="0"
                  class="block w-full rounded-md border border-gray-300 px-4 py-2 focus:border-brand-500 focus:ring-1 focus:ring-brand-500"
                />
                <p v-if="form.errors.cantidad_apartamentos" class="mt-1 text-sm text-red-600">
                  {{ form.errors.cantidad_apartamentos }}
                </p>
              </div>

              <div>
                <label
                  for="cantidad_parqueaderos_vehiculo"
                  class="block text-sm font-medium text-gray-700 mb-1"
                  >Parqueaderos Vehículo</label
                >
                <input
                  id="cantidad_parqueaderos_vehiculo"
                  v-model="form.cantidad_parqueaderos_vehiculo"
                  type="number"
                  min="0"
                  class="block w-full rounded-md border border-gray-300 px-4 py-2 focus:border-brand-500 focus:ring-1 focus:ring-brand-500"
                />
                <p
                  v-if="form.errors.cantidad_parqueaderos_vehiculo"
                  class="mt-1 text-sm text-red-600"
                >
                  {{ form.errors.cantidad_parqueaderos_vehiculo }}
                </p>
              </div>

              <div>
                <label
                  for="cantidad_parqueaderos_moto"
                  class="block text-sm font-medium text-gray-700 mb-1"
                  >Parqueaderos Moto</label
                >
                <input
                  id="cantidad_parqueaderos_moto"
                  v-model="form.cantidad_parqueaderos_moto"
                  type="number"
                  min="0"
                  class="block w-full rounded-md border border-gray-300 px-4 py-2 focus:border-brand-500 focus:ring-1 focus:ring-brand-500"
                />
                <p v-if="form.errors.cantidad_parqueaderos_moto" class="mt-1 text-sm text-red-600">
                  {{ form.errors.cantidad_parqueaderos_moto }}
                </p>
              </div>
            </div>

            <!-- Estrato, pisos, torres -->
            <div class="grid grid-cols-3 gap-6">
              <div>
                <label for="estrato" class="block text-sm font-medium text-gray-700 mb-1"
                  >Estrato</label
                >
                <input
                  id="estrato"
                  v-model="form.estrato"
                  type="number"
                  min="1"
                  max="6"
                  class="block w-full rounded-md border border-gray-300 px-4 py-2 focus:border-brand-500 focus:ring-1 focus:ring-brand-500"
                />
                <p v-if="form.errors.estrato" class="mt-1 text-sm text-red-600">
                  {{ form.errors.estrato }}
                </p>
              </div>

              <div>
                <label for="numero_pisos" class="block text-sm font-medium text-gray-700 mb-1"
                  >Número Pisos</label
                >
                <input
                  id="numero_pisos"
                  v-model="form.numero_pisos"
                  type="number"
                  min="1"
                  class="block w-full rounded-md border border-gray-300 px-4 py-2 focus:border-brand-500 focus:ring-1 focus:ring-brand-500"
                />
                <p v-if="form.errors.numero_pisos" class="mt-1 text-sm text-red-600">
                  {{ form.errors.numero_pisos }}
                </p>
              </div>

              <div>
                <label for="numero_torres" class="block text-sm font-medium text-gray-700 mb-1"
                  >Número Torres</label
                >
                <input
                  id="numero_torres"
                  v-model="form.numero_torres"
                  type="number"
                  min="1"
                  class="block w-full rounded-md border border-gray-300 px-4 py-2 focus:border-brand-500 focus:ring-1 focus:ring-brand-500"
                />
                <p v-if="form.errors.numero_torres" class="mt-1 text-sm text-red-600">
                  {{ form.errors.numero_torres }}
                </p>
              </div>
            </div>

            <!-- Financiación -->
            <div class="grid grid-cols-3 gap-6">
              <div>
                <label
                  for="porcentaje_cuota_inicial_min"
                  class="block text-sm font-medium text-gray-700 mb-1"
                  >% Cuota Inicial Mínima</label
                >
                <input
                  id="porcentaje_cuota_inicial_min"
                  v-model="form.porcentaje_cuota_inicial_min"
                  type="number"
                  min="0"
                  max="100"
                  step="0.01"
                  class="block w-full rounded-md border border-gray-300 px-4 py-2 focus:border-brand-500 focus:ring-1 focus:ring-brand-500"
                />
                <p
                  v-if="form.errors.porcentaje_cuota_inicial_min"
                  class="mt-1 text-sm text-red-600"
                >
                  {{ form.errors.porcentaje_cuota_inicial_min }}
                </p>
              </div>

              <div>
                <label
                  for="valor_min_separacion"
                  class="block text-sm font-medium text-gray-700 mb-1"
                  >Valor Mínimo Separación</label
                >
                <input
                  id="valor_min_separacion"
                  v-model="form.valor_min_separacion"
                  type="number"
                  min="0"
                  step="0.01"
                  class="block w-full rounded-md border border-gray-300 px-4 py-2 focus:border-brand-500 focus:ring-1 focus:ring-brand-500"
                />
                <p v-if="form.errors.valor_min_separacion" class="mt-1 text-sm text-red-600">
                  {{ form.errors.valor_min_separacion }}
                </p>
              </div>

              <div>
                <label
                  for="plazo_cuota_inicial_meses"
                  class="block text-sm font-medium text-gray-700 mb-1"
                  >Plazo Cuota Inicial (meses)</label
                >
                <input
                  id="plazo_cuota_inicial_meses"
                  v-model="form.plazo_cuota_inicial_meses"
                  type="number"
                  min="1"
                  class="block w-full rounded-md border border-gray-300 px-4 py-2 focus:border-brand-500 focus:ring-1 focus:ring-brand-500"
                />
                <p v-if="form.errors.plazo_cuota_inicial_meses" class="mt-1 text-sm text-red-600">
                  {{ form.errors.plazo_cuota_inicial_meses }}
                </p>
              </div>
            </div>

            <!-- Estado -->
            <div>
              <label for="id_estado" class="block text-sm font-medium text-gray-700 mb-1"
                >Estado <span class="text-red-500">*</span></label
              >
              <select
                id="id_estado"
                v-model="form.id_estado"
                class="block w-full rounded-md border border-gray-300 px-4 py-2 focus:border-brand-500 focus:ring-1 focus:ring-brand-500"
              >
                <option value="" disabled>Seleccione un estado</option>
                <option v-for="estado in estados" :key="estado.id_estado" :value="estado.id_estado">
                  {{ estado.nombre }}
                </option>
              </select>
              <p v-if="form.errors.id_estado" class="mt-1 text-sm text-red-600">
                {{ form.errors.id_estado }}
              </p>
            </div>

            <!-- Ubicación -->
            <div>
              <label for="id_ubicacion" class="block text-sm font-medium text-gray-700 mb-1"
                >Ubicación <span class="text-red-500">*</span></label
              >
              <select
                id="id_ubicacion"
                v-model="form.id_ubicacion"
                class="block w-full rounded-md border border-gray-300 px-4 py-2 focus:border-brand-500 focus:ring-1 focus:ring-brand-500"
              >
                <option value="" disabled>Seleccione una ubicación</option>
                <option
                  v-for="ubicacion in ubicaciones"
                  :key="ubicacion.id_ubicacion"
                  :value="ubicacion.id_ubicacion"
                >
                  {{ ubicacion.direccion }}, {{ ubicacion.ciudad.nombre }}
                </option>
              </select>
              <p v-if="form.errors.id_ubicacion" class="mt-1 text-sm text-red-600">
                {{ form.errors.id_ubicacion }}
              </p>
            </div>

            <button
              type="submit"
              :disabled="form.processing"
              class="mt-6 w-full rounded bg-brand-500 px-6 py-3 text-white font-semibold shadow hover:bg-brand-600 disabled:opacity-50"
            >
              Guardar Proyecto
            </button>
          </form>
        </div>
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useForm } from '@inertiajs/inertia-vue3'
import { Link, router, usePage } from '@inertiajs/inertia-vue3'
import { Inertia } from '@inertiajs/inertia'
import Logo from '@/Components/Logo.vue'

import {
  UserIcon,
  ChevronDownIcon,
  FolderIcon,
  UsersIcon,
  CheckCircleIcon,
  BuildingOfficeIcon,
  PlusIcon,
  HomeIcon,
  ChevronLeftIcon,
  ChevronRightIcon,
} from '@heroicons/vue/24/outline'

const props = defineProps({
  estados: Array,
  ubicaciones: Array,
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

const form = useForm({
  nombre: '',
  descripcion: '',
  fecha_inicio: '',
  fecha_finalizacion: '',
  presupuesto_inicial: '',
  presupuesto_final: '',
  metros_construidos: '',
  cantidad_locales: '',
  cantidad_apartamentos: '',
  cantidad_parqueaderos_vehiculo: '',
  cantidad_parqueaderos_moto: '',
  estrato: '',
  numero_pisos: '',
  numero_torres: '',
  porcentaje_cuota_inicial_min: '',
  valor_min_separacion: '',
  plazo_cuota_inicial_meses: '',
  id_estado: '',
  id_ubicacion: '',
})

const currentRoute = computed(() => {
  const comp = usePage()?.component
  if (typeof comp === 'string') {
    return comp.toLowerCase()
  }
  return ''
})

function submit() {
  form.post('/proyectos')
}
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
