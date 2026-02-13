<!-- resources/js/Pages/Admin/Configuracion/Estados.vue -->
<template>
  <TopBannerLayout :empleado="empleado" panel-name="Panel administrador">
    <Head title="Gestión de Estados" />

    <div class="space-y-6">
      <PageHeader
        title="Gestión de estados"
        subtitle="Administra estados de proyectos/torres y estados de inmuebles. Visualiza asociaciones y gestiona cambios."
      />

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- ===================== -->
        <!-- ESTADOS PROYECTO / TORRES -->
        <!-- ===================== -->
        <AppCard padding="md">
          <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
              <p class="text-sm font-semibold text-gray-900">Estados proyectos · torres</p>
              <p class="text-sm text-gray-600 mt-1">
                Usados para el estado general del proyecto y la torre.
              </p>
            </div>

            <span
              class="inline-flex items-center rounded-full border border-gray-200 bg-gray-50 px-3 py-1 text-xs font-semibold text-gray-700"
            >
              {{ estados?.length || 0 }} en total
            </span>
          </div>

          <!-- Form -->
          <form @submit.prevent="submitEstado" class="mt-6 space-y-4">
            <FormField label="Nombre" required :error="formEstado.errors.nombre">
              <TextInput v-model="formEstado.nombre" maxlength="50" placeholder="Ej: En obra" />
            </FormField>

            <FormField label="Descripción" :error="formEstado.errors.descripcion">
              <TextArea
                v-model="formEstado.descripcion"
                maxlength="200"
                rows="3"
                placeholder="Opcional…"
              />
            </FormField>

            <div class="flex flex-col sm:flex-row sm:items-center gap-2">
              <button
                type="submit"
                :disabled="formEstado.processing"
                class="rounded-xl bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-brand-700 transition disabled:opacity-60"
              >
                {{ editEstadoId ? 'Actualizar estado' : 'Crear estado' }}
              </button>

              <button
                v-if="editEstadoId"
                type="button"
                @click="cancelEditEstado"
                class="rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-800 hover:bg-gray-50 transition"
              >
                Cancelar
              </button>

              <span v-if="editEstadoId" class="text-xs text-amber-700 sm:ml-auto">
                Editando ID: <span class="font-semibold">{{ editEstadoId }}</span>
              </span>
            </div>
          </form>

          <!-- Table -->
          <div class="mt-6 overflow-x-auto rounded-2xl border border-gray-200">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th
                    class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide"
                  >
                    Nombre
                  </th>
                  <th
                    class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide"
                  >
                    Descripción
                  </th>
                  <th
                    class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wide"
                  >
                    Proyectos
                  </th>
                  <th
                    class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wide"
                  >
                    Torres
                  </th>
                  <th
                    class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wide"
                  >
                    Acciones
                  </th>
                </tr>
              </thead>

              <tbody class="divide-y divide-gray-100 bg-white">
                <tr
                  v-for="estadoItem in estados"
                  :key="estadoItem.id_estado"
                  class="hover:bg-gray-50 transition"
                >
                  <td class="px-4 py-3 text-sm font-semibold text-gray-900">
                    {{ estadoItem.nombre }}
                  </td>

                  <td class="px-4 py-3 text-sm text-gray-600">
                    {{ estadoItem.descripcion || '—' }}
                  </td>

                  <td class="px-4 py-3 text-center">
                    <span
                      class="inline-flex items-center rounded-full border border-gray-200 bg-gray-50 px-2.5 py-1 text-xs font-semibold text-gray-700"
                    >
                      {{ (estadoItem.proyectos || []).length }}
                    </span>
                  </td>

                  <td class="px-4 py-3 text-center">
                    <span
                      class="inline-flex items-center rounded-full border border-gray-200 bg-gray-50 px-2.5 py-1 text-xs font-semibold text-gray-700"
                    >
                      {{ (estadoItem.torres || []).length }}
                    </span>
                  </td>

                  <td class="px-4 py-3">
                    <div class="flex justify-end items-center gap-2">
                      <button
                        type="button"
                        @click="editEstado(estadoItem)"
                        class="rounded-xl border border-amber-200 bg-amber-50 px-3 py-1.5 text-xs font-semibold text-amber-800 hover:bg-amber-100 transition"
                      >
                        Editar
                      </button>

                      <button
                        type="button"
                        @click="confirmDeleteEstado(estadoItem.id_estado)"
                        class="rounded-xl border border-red-200 bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-700 hover:bg-red-100 transition"
                      >
                        Eliminar
                      </button>
                    </div>
                  </td>
                </tr>

                <tr v-if="!estados?.length">
                  <td colspan="5" class="px-4 py-6 text-center text-sm text-gray-500">
                    No hay estados registrados.
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </AppCard>

        <!-- ===================== -->
        <!-- ESTADOS INMUEBLE -->
        <!-- ===================== -->
        <AppCard padding="md">
          <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
              <p class="text-sm font-semibold text-gray-900">Estados de inmuebles</p>
              <p class="text-sm text-gray-600 mt-1">Aplican para apartamentos y locales.</p>
            </div>

            <span
              class="inline-flex items-center rounded-full border border-gray-200 bg-gray-50 px-3 py-1 text-xs font-semibold text-gray-700"
            >
              {{ estadosInmueble?.length || 0 }} en total
            </span>
          </div>

          <!-- Form -->
          <form @submit.prevent="submitEstadoInmueble" class="mt-6 space-y-4">
            <FormField label="Nombre" required :error="formEstadoInmueble.errors.nombre">
              <TextInput
                v-model="formEstadoInmueble.nombre"
                maxlength="50"
                placeholder="Ej: Disponible"
              />
            </FormField>

            <FormField label="Descripción" :error="formEstadoInmueble.errors.descripcion">
              <TextArea
                v-model="formEstadoInmueble.descripcion"
                maxlength="200"
                rows="3"
                placeholder="Opcional…"
              />
            </FormField>

            <div class="flex flex-col sm:flex-row sm:items-center gap-2">
              <button
                type="submit"
                :disabled="formEstadoInmueble.processing"
                class="rounded-xl bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-brand-700 transition disabled:opacity-60"
              >
                {{ editEstadoInmuebleId ? 'Actualizar estado inmueble' : 'Crear estado inmueble' }}
              </button>

              <button
                v-if="editEstadoInmuebleId"
                type="button"
                @click="cancelEditEstadoInmueble"
                class="rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-800 hover:bg-gray-50 transition"
              >
                Cancelar
              </button>

              <span v-if="editEstadoInmuebleId" class="text-xs text-amber-700 sm:ml-auto">
                Editando ID: <span class="font-semibold">{{ editEstadoInmuebleId }}</span>
              </span>
            </div>
          </form>

          <!-- Table -->
          <div class="mt-6 overflow-x-auto rounded-2xl border border-gray-200">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th
                    class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide"
                  >
                    Nombre
                  </th>
                  <th
                    class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide"
                  >
                    Descripción
                  </th>
                  <th
                    class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wide"
                  >
                    Apartamentos
                  </th>
                  <th
                    class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wide"
                  >
                    Locales
                  </th>
                  <th
                    class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wide"
                  >
                    Acciones
                  </th>
                </tr>
              </thead>

              <tbody class="divide-y divide-gray-100 bg-white">
                <tr
                  v-for="estadoItem in estadosInmueble"
                  :key="estadoItem.id_estado_inmueble"
                  class="hover:bg-gray-50 transition"
                >
                  <td class="px-4 py-3 text-sm font-semibold text-gray-900">
                    {{ estadoItem.nombre }}
                  </td>

                  <td class="px-4 py-3 text-sm text-gray-600">
                    {{ estadoItem.descripcion || '—' }}
                  </td>

                  <td class="px-4 py-3 text-center">
                    <span
                      class="inline-flex items-center rounded-full border border-gray-200 bg-gray-50 px-2.5 py-1 text-xs font-semibold text-gray-700"
                    >
                      {{ (estadoItem.apartamentos || []).length }}
                    </span>
                  </td>

                  <td class="px-4 py-3 text-center">
                    <span
                      class="inline-flex items-center rounded-full border border-gray-200 bg-gray-50 px-2.5 py-1 text-xs font-semibold text-gray-700"
                    >
                      {{ (estadoItem.locales || []).length }}
                    </span>
                  </td>

                  <td class="px-4 py-3">
                    <div class="flex justify-end items-center gap-2">
                      <button
                        type="button"
                        @click="editEstadoInmueble(estadoItem)"
                        class="rounded-xl border border-amber-200 bg-amber-50 px-3 py-1.5 text-xs font-semibold text-amber-800 hover:bg-amber-100 transition"
                      >
                        Editar
                      </button>

                      <button
                        type="button"
                        @click="confirmDeleteEstadoInmueble(estadoItem.id_estado_inmueble)"
                        class="rounded-xl border border-red-200 bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-700 hover:bg-red-100 transition"
                      >
                        Eliminar
                      </button>
                    </div>
                  </td>
                </tr>

                <tr v-if="!estadosInmueble?.length">
                  <td colspan="5" class="px-4 py-6 text-center text-sm text-gray-500">
                    No hay estados de inmuebles registrados.
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </AppCard>
      </div>

      <!-- Modal confirmación eliminación -->
      <ConfirmDeleteModal
        v-if="showConfirmDelete"
        :message="deleteMessage"
        @confirm="deleteItem"
        @cancel="cancelDelete"
      />
    </div>
  </TopBannerLayout>
</template>

<script setup>
import { Head, useForm, router, usePage } from '@inertiajs/vue3'
import { computed, ref } from 'vue'

import TopBannerLayout from '@/Components/TopBannerLayout.vue'
import PageHeader from '@/Components/PageHeader.vue'
import AppCard from '@/Components/AppCard.vue'
import FormField from '@/Components/FormField.vue'
import TextInput from '@/Components/TextInput.vue'
import TextArea from '@/Components/TextArea.vue'
import ConfirmDeleteModal from '@/Components/ConfirmDeleteModal.vue'

const page = usePage()
const empleado = computed(() => page.props.auth?.empleado || null)

const props = defineProps({
  estados: Array,
  estadosInmueble: Array,
})

const estados = ref(props.estados || [])
const estadosInmueble = ref(props.estadosInmueble || [])

// Estados form
const formEstado = useForm({
  nombre: '',
  descripcion: '',
})
const editEstadoId = ref(null)

// Estados Inmueble form
const formEstadoInmueble = useForm({
  nombre: '',
  descripcion: '',
})
const editEstadoInmuebleId = ref(null)

// Modal control
const showConfirmDelete = ref(false)
const deleteId = ref(null)
const deleteType = ref(null) // 'estado' | 'estadoInmueble'
const deleteMessage = ref('')

// Funciones para Estados
function submitEstado() {
  if (editEstadoId.value) {
    formEstado.put(`/estados/${editEstadoId.value}`, {
      preserveScroll: true,
      onSuccess: () => {
        resetEstadoForm()
        reloadPage()
      },
    })
  } else {
    formEstado.post('/estados', {
      preserveScroll: true,
      onSuccess: () => {
        resetEstadoForm()
        reloadPage()
      },
    })
  }
}

function editEstado(estadoItem) {
  editEstadoId.value = estadoItem.id_estado
  formEstado.nombre = estadoItem.nombre
  formEstado.descripcion = estadoItem.descripcion || ''
}

function cancelEditEstado() {
  resetEstadoForm()
}

function resetEstadoForm() {
  editEstadoId.value = null
  formEstado.reset()
}

// Funciones para Estados Inmueble
function submitEstadoInmueble() {
  if (editEstadoInmuebleId.value) {
    formEstadoInmueble.put(`/estados-inmueble/${editEstadoInmuebleId.value}`, {
      preserveScroll: true,
      onSuccess: () => {
        resetEstadoInmuebleForm()
        reloadPage()
      },
    })
  } else {
    formEstadoInmueble.post('/estados-inmueble', {
      preserveScroll: true,
      onSuccess: () => {
        resetEstadoInmuebleForm()
        reloadPage()
      },
    })
  }
}

function editEstadoInmueble(estadoItem) {
  editEstadoInmuebleId.value = estadoItem.id_estado_inmueble
  formEstadoInmueble.nombre = estadoItem.nombre
  formEstadoInmueble.descripcion = estadoItem.descripcion || ''
}

function cancelEditEstadoInmueble() {
  resetEstadoInmuebleForm()
}

function resetEstadoInmuebleForm() {
  editEstadoInmuebleId.value = null
  formEstadoInmueble.reset()
}

// Funciones para eliminar
function confirmDeleteEstado(id) {
  deleteId.value = id
  deleteType.value = 'estado'
  deleteMessage.value = '¿Está seguro que desea eliminar este estado?'
  showConfirmDelete.value = true
}

function confirmDeleteEstadoInmueble(id) {
  deleteId.value = id
  deleteType.value = 'estadoInmueble'
  deleteMessage.value = '¿Está seguro que desea eliminar este estado de inmueble?'
  showConfirmDelete.value = true
}

function deleteItem() {
  if (deleteType.value === 'estado') {
    router.delete(`/estados/${deleteId.value}`, {
      preserveScroll: true,
      onSuccess: () => {
        reloadPage()
        showConfirmDelete.value = false
      },
    })
  } else if (deleteType.value === 'estadoInmueble') {
    router.delete(`/estados-inmueble/${deleteId.value}`, {
      preserveScroll: true,
      onSuccess: () => {
        reloadPage()
        showConfirmDelete.value = false
      },
    })
  }
}

function cancelDelete() {
  showConfirmDelete.value = false
  deleteId.value = null
  deleteType.value = null
  deleteMessage.value = ''
}

function reloadPage() {
  router.reload({ only: ['estados', 'estadosInmueble'] })
}
</script>
