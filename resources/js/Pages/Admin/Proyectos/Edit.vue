<template>
  <SidebarBannerLayout :empleado="empleado">
    <div class="flex min-h-screen bg-brand-50">
      <div class="flex-1 flex flex-col">
        <main class="p-8 overflow-auto max-w-4xl mx-auto">
          <h2 class="text-3xl font-bold mb-6 text-gray-900">Editar Proyecto</h2>

          <form @submit.prevent="submit" class="space-y-6 bg-white rounded-lg shadow p-8">
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
                  >Porcentaje Cuota Inicial Mínima</label
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

            <!-- Prima Altura -->
            <div class="md:col-span-2 border-t pt-4 mt-4">
              <h3 class="text-base font-semibold mb-3">Configuración Prima Altura</h3>
            </div>

            <div>
              <label class="form-label">Prima Altura Base (desde piso 2)</label>
              <input
                v-model.number="form.prima_altura_base"
                type="number"
                step="0.01"
                min="0"
                class="form-input"
                placeholder="Ej: 500000"
              />
              <p v-if="form.errors.prima_altura_base" class="form-error">
                {{ form.errors.prima_altura_base }}
              </p>
            </div>

            <div>
              <label class="form-label">Incremento por Piso</label>
              <input
                v-model.number="form.prima_altura_incremento"
                type="number"
                step="0.01"
                min="0"
                class="form-input"
                placeholder="Ej: 100000"
              />
              <p v-if="form.errors.prima_altura_incremento" class="form-error">
                {{ form.errors.prima_altura_incremento }}
              </p>
            </div>

            <div class="md:col-span-2">
              <label class="flex items-center gap-2">
                <input
                  v-model="form.prima_altura_activa"
                  type="checkbox"
                  class="rounded border-gray-300"
                />
                <span class="text-sm font-medium">Activar Prima Altura en este proyecto</span>
              </label>
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
              Guardar Cambios
            </button>
          </form>
        </main>
      </div>
    </div>
  </SidebarBannerLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useForm } from '@inertiajs/inertia-vue3'
import { Link, usePage } from '@inertiajs/inertia-vue3'
import { Inertia } from '@inertiajs/inertia'
import SidebarBannerLayout from '@/Components/SidebarBannerLayout.vue'

const props = defineProps({
  proyecto: { type: Object, default: () => ({}) },
  estados: { type: Array, default: () => [] },
  ubicaciones: { type: Array, default: () => [] },
  empleado: { type: Object, default: null },
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
  nombre: props.proyecto?.nombre || '',
  descripcion: props.proyecto?.descripcion || '',
  fecha_inicio: props.proyecto?.fecha_inicio || '',
  fecha_finalizacion: props.proyecto?.fecha_finalizacion || '',
  presupuesto_inicial: props.proyecto?.presupuesto_inicial || '',
  presupuesto_final: props.proyecto?.presupuesto_final || '',
  metros_construidos: props.proyecto?.metros_construidos || '',
  cantidad_locales: props.proyecto?.cantidad_locales || '',
  cantidad_apartamentos: props.proyecto?.cantidad_apartamentos || '',
  cantidad_parqueaderos_vehiculo: props.proyecto?.cantidad_parqueaderos_vehiculo || '',
  cantidad_parqueaderos_moto: props.proyecto?.cantidad_parqueaderos_moto || '',
  estrato: props.proyecto?.estrato || '',
  numero_pisos: props.proyecto?.numero_pisos || '',
  numero_torres: props.proyecto?.numero_torres || '',
  porcentaje_cuota_inicial_min: props.proyecto?.porcentaje_cuota_inicial_min || '',
  valor_min_separacion: props.proyecto?.valor_min_separacion || '',
  plazo_cuota_inicial_meses: props.proyecto?.plazo_cuota_inicial_meses || '',
  id_estado: props.proyecto?.id_estado || '',
  id_ubicacion: props.proyecto?.id_ubicacion || '',
  prima_altura_base: props.proyecto?.prima_altura_base ?? null,
  prima_altura_incremento: props.proyecto?.prima_altura_incremento ?? null,
  prima_altura_activa: props.proyecto?.prima_altura_activa ?? false,
})

function submit() {
  form.put(`/proyectos/${props.proyecto.id_proyecto}`)
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
