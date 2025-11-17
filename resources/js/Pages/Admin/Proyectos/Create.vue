<template>
  <SidebarBannerLayout :empleado="empleado">
    <div class="flex min-h-screen bg-brand-50">
      <div class="flex-1 flex flex-col">
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
                  <label
                    for="fecha_finalizacion"
                    class="block text-sm font-medium text-gray-700 mb-1"
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
                  <label
                    for="presupuesto_final"
                    class="block text-sm font-medium text-gray-700 mb-1"
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
                  <label
                    for="metros_construidos"
                    class="block text-sm font-medium text-gray-700 mb-1"
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
                  <p
                    v-if="form.errors.cantidad_parqueaderos_moto"
                    class="mt-1 text-sm text-red-600"
                  >
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
              <!-- Financiación -->
              <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
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

                <div>
                  <label
                    for="plazo_max_separacion_dias"
                    class="block text-sm font-medium text-gray-700 mb-1"
                    >Plazo Máximo Separación (días)</label
                  >
                  <input
                    id="plazo_max_separacion_dias"
                    v-model="form.plazo_max_separacion_dias"
                    type="number"
                    min="1"
                    max="365"
                    class="block w-full rounded-md border border-gray-300 px-4 py-2 focus:border-brand-500 focus:ring-1 focus:ring-brand-500"
                  />
                  <p v-if="form.errors.plazo_max_separacion_dias" class="mt-1 text-sm text-red-600">
                    {{ form.errors.plazo_max_separacion_dias }}
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
                  <option
                    v-for="estado in estados"
                    :key="estado.id_estado"
                    :value="estado.id_estado"
                  >
                    {{ estado.nombre }}
                  </option>
                </select>
                <p v-if="form.errors.id_estado" class="mt-1 text-sm text-red-600">
                  {{ form.errors.id_estado }}
                </p>
              </div>
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
                <div class="flex gap-2 items-center">
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
                  <button
                    type="button"
                    @click="openModal = true"
                    class="ml-2 rounded bg-brand-500 px-3 py-1 text-white hover:bg-brand-600"
                  >
                    Crear Ubicación
                  </button>
                </div>
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

      <!-- Modal Crear Ubicación -->
      <transition name="fade">
        <div
          v-if="openModal"
          class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
        >
          <div class="bg-white rounded-lg shadow-lg max-w-lg w-full p-6 relative">
            <h3 class="text-xl font-semibold mb-4">Crear Nueva Ubicación</h3>

            <form @submit.prevent="submitUbicacion" class="space-y-4 max-h-[70vh] overflow-auto">
              <!-- País -->
              <div>
                <label for="pais" class="block text-sm font-medium text-gray-700 mb-1"
                  >País <span class="text-red-500">*</span></label
                >
                <select
                  id="pais"
                  v-model="selectedPais"
                  @change="onPaisChange"
                  class="block w-full rounded-md border border-gray-300 px-4 py-2 focus:border-brand-500 focus:ring-1 focus:ring-brand-500"
                  required
                >
                  <option value="" disabled>Seleccione un país</option>
                  <option v-for="pais in paises" :key="pais.id_pais" :value="pais.id_pais">
                    {{ pais.nombre }}
                  </option>
                </select>
              </div>

              <!-- Departamento -->
              <div>
                <label for="departamento" class="block text-sm font-medium text-gray-700 mb-1"
                  >Departamento <span class="text-red-500">*</span></label
                >
                <select
                  id="departamento"
                  v-model="selectedDepartamento"
                  @change="onDepartamentoChange"
                  class="block w-full rounded-md border border-gray-300 px-4 py-2 focus:border-brand-500 focus:ring-1 focus:ring-brand-500"
                  :disabled="!selectedPais"
                  required
                >
                  <option value="" disabled>Seleccione un departamento</option>
                  <option
                    v-for="dep in departamentosFiltrados"
                    :key="dep.id_departamento"
                    :value="dep.id_departamento"
                  >
                    {{ dep.nombre }}
                  </option>
                </select>
              </div>

              <!-- Ciudad -->
              <div>
                <label for="ciudad" class="block text-sm font-medium text-gray-700 mb-1"
                  >Ciudad <span class="text-red-500">*</span></label
                >
                <select
                  id="ciudad"
                  v-model="selectedCiudad"
                  class="block w-full rounded-md border border-gray-300 px-4 py-2 focus:border-brand-500 focus:ring-1 focus:ring-brand-500"
                  :disabled="!selectedDepartamento"
                  required
                >
                  <option value="" disabled>Seleccione una ciudad</option>
                  <option
                    v-for="ciudad in ciudadesFiltradas"
                    :key="ciudad.id_ciudad"
                    :value="ciudad.id_ciudad"
                  >
                    {{ ciudad.nombre }}
                  </option>
                </select>
              </div>

              <!-- Barrio -->
              <div>
                <label for="barrio" class="block text-sm font-medium text-gray-700 mb-1"
                  >Barrio</label
                >
                <input
                  id="barrio"
                  v-model="formUbicacion.barrio"
                  type="text"
                  maxlength="120"
                  class="block w-full rounded-md border border-gray-300 px-4 py-2 focus:border-brand-500 focus:ring-1 focus:ring-brand-500"
                />
                <p v-if="errorsUbicacion.barrio" class="mt-1 text-sm text-red-600">
                  {{ errorsUbicacion.barrio }}
                </p>
              </div>

              <!-- Dirección -->
              <div>
                <label for="direccion" class="block text-sm font-medium text-gray-700 mb-1"
                  >Dirección <span class="text-red-500">*</span></label
                >
                <input
                  id="direccion"
                  v-model="formUbicacion.direccion"
                  type="text"
                  maxlength="300"
                  required
                  class="block w-full rounded-md border border-gray-300 px-4 py-2 focus:border-brand-500 focus:ring-1 focus:ring-brand-500"
                />
                <p v-if="errorsUbicacion.direccion" class="mt-1 text-sm text-red-600">
                  {{ errorsUbicacion.direccion }}
                </p>
              </div>

              <div class="flex justify-end gap-4 mt-6">
                <button
                  type="button"
                  @click="closeModal"
                  class="rounded border border-gray-300 px-4 py-2 hover:bg-gray-100"
                >
                  Cancelar
                </button>
                <button
                  type="submit"
                  :disabled="loadingUbicacion"
                  class="rounded bg-brand-500 px-6 py-2 text-white hover:bg-brand-600 disabled:opacity-50"
                >
                  Guardar Ubicación
                </button>
              </div>
            </form>
          </div>
        </div>
      </transition>
    </div>
  </SidebarBannerLayout>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import { useForm, Link, usePage, router } from '@inertiajs/vue3'
import SidebarBannerLayout from '@/Components/SidebarBannerLayout.vue'

const props = defineProps({
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

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onBeforeUnmount(() => {
  document.removeEventListener('click', handleClickOutside)
})

function logout() {
  router.post('/logout')
}

// Formulario proyecto
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
  prima_altura_base: null,
  prima_altura_incremento: null,
  prima_altura_activa: false,
  plazo_max_separacion_dias: '',
})

// Variables para modal ubicación
const openModal = ref(false)
const paises = ref([])
const selectedPais = ref('')
const departamentosFiltrados = ref([])
const selectedDepartamento = ref('')
const ciudadesFiltradas = ref([])
const selectedCiudad = ref('')

const formUbicacion = ref({
  id_ciudad: '',
  barrio: '',
  direccion: '',
})

const errorsUbicacion = ref({})
const loadingUbicacion = ref(false)

// Cargar países y jerarquía al montar
async function cargarJerarquia() {
  try {
    const response = await fetch('/ubicacion/jerarquia')
    const data = await response.json()
    if (data.success) {
      paises.value = data.data
    }
  } catch (error) {
    console.error('Error cargando jerarquía:', error)
  }
}

onMounted(() => {
  cargarJerarquia()
})

// Filtrar departamentos según país seleccionado
function onPaisChange() {
  selectedDepartamento.value = ''
  selectedCiudad.value = ''
  departamentosFiltrados.value = []
  ciudadesFiltradas.value = []
  if (!selectedPais.value) return
  const pais = paises.value.find((p) => p.id_pais === selectedPais.value)
  departamentosFiltrados.value = pais ? pais.departamentos : []
}

// Filtrar ciudades según departamento seleccionado
function onDepartamentoChange() {
  selectedCiudad.value = ''
  ciudadesFiltradas.value = []
  if (!selectedDepartamento.value) return
  const departamento = departamentosFiltrados.value.find(
    (d) => d.id_departamento === selectedDepartamento.value
  )
  ciudadesFiltradas.value = departamento ? departamento.ciudades : []
}

// Enviar formulario de ubicación
async function submitUbicacion() {
  errorsUbicacion.value = {}

  if (!selectedCiudad.value) {
    errorsUbicacion.value.id_ciudad = 'La ciudad es obligatoria'
    return
  }
  if (!formUbicacion.value.direccion) {
    errorsUbicacion.value.direccion = 'La dirección es obligatoria'
    return
  }

  loadingUbicacion.value = true

  router.post(
    '/ubicacion',
    {
      id_ciudad: selectedCiudad.value,
      barrio: formUbicacion.value.barrio,
      direccion: formUbicacion.value.direccion,
    },
    {
      onSuccess: (page) => {
        // Agregar nueva ubicación a la lista y seleccionar
        const nuevaUbicacion = page.props.ubicacionNueva // Asegúrate que el backend envíe esta prop
        if (nuevaUbicacion) {
          form.id_ubicacion = nuevaUbicacion.id_ubicacion
          props.ubicaciones.push(nuevaUbicacion)
        }
        closeModal()
        // Reset form
        selectedPais.value = ''
        selectedDepartamento.value = ''
        selectedCiudad.value = ''
        formUbicacion.value = { id_ciudad: '', barrio: '', direccion: '' }
        errorsUbicacion.value = {}
        loadingUbicacion.value = false
      },
      onError: (errors) => {
        errorsUbicacion.value = errors || {}
        loadingUbicacion.value = false
      },
    }
  )
}

function closeModal() {
  openModal.value = false
}

const currentRoute = computed(() => {
  const comp = usePage()?.component
  if (typeof comp === 'string') {
    return comp.toLowerCase()
  }
  return ''
})

function submit() {
  router.post('/proyectos', form)
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
