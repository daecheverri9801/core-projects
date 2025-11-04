<template>
  <SidebarBannerLayout>
    <template #title>Gestión de Estados</template>

    <div class="max-w-6xl mx-auto space-y-12">
      <!-- Estados -->
      <section>
        <h2 class="text-2xl font-bold mb-4">Estados Proyectos - Torres</h2>

        <form @submit.prevent="submitEstado" class="mb-6 space-y-4 max-w-md">
          <InputText
            label="Nombre"
            v-model="formEstado.nombre"
            :error="formEstado.errors.nombre"
            required
            maxlength="50"
          />
          <InputTextarea
            label="Descripción"
            v-model="formEstado.descripcion"
            :error="formEstado.errors.descripcion"
            maxlength="200"
          />
          <button type="submit" :disabled="formEstado.processing" class="btn-primary">
            {{ editEstadoId ? 'Actualizar Estado' : 'Crear Estado' }}
          </button>
          <button v-if="editEstadoId" type="button" @click="cancelEditEstado" class="btn-secondary">
            Cancelar
          </button>
        </form>

        <table class="w-full table-auto border border-gray-300 rounded shadow">
          <thead class="bg-gray-100">
            <tr>
              <th class="p-2 border-b">Nombre</th>
              <th class="p-2 border-b text-center">Descripción</th>
              <th class="p-2 border-b">Proyectos Asociados</th>
              <th class="p-2 border-b text-center">Torres Asociadas</th>
              <th class="p-2 border-b">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="estado in estados || []" :key="estado.id_estado" class="hover:bg-gray-50">
              <td class="p-2 border-b">{{ estado.nombre }}</td>
              <td class="p-2 border-b">{{ estado.descripcion || '-' }}</td>
              <td class="p-2 border-b text-center">{{ (estado.proyectos || []).length }}</td>
              <td class="p-2 border-b text-center">{{ (estado.torres || []).length }}</td>
              <td class="p-2 border-b text-center space-x-2">
                <button @click="editEstado(estado)" class="btn-edit">Editar</button>
                <button @click="confirmDeleteEstado(estado.id_estado)" class="btn-delete">
                  Eliminar
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </section>

        <!-- Estados Inmueble -->
        <section>
          <h2 class="text-2xl font-bold mb-4">Estados de Inmuebles</h2>

          <form @submit.prevent="submitEstadoInmueble" class="mb-6 space-y-4 max-w-md">
            <InputText
              label="Nombre"
              v-model="formEstadoInmueble.nombre"
              :error="formEstadoInmueble.errors.nombre"
              required
              maxlength="50"
            />
            <InputTextarea
              label="Descripción"
              v-model="formEstadoInmueble.descripcion"
              :error="formEstadoInmueble.errors.descripcion"
              maxlength="200"
            />
            <button type="submit" :disabled="formEstadoInmueble.processing" class="btn-primary">
              {{
                editEstadoInmuebleId ? 'Actualizar Estado de Inmueble' : 'Crear Estado de Inmueble'
              }}
            </button>
            <button
              v-if="editEstadoInmuebleId"
              type="button"
              @click="cancelEditEstadoInmueble"
              class="btn-secondary"
            >
              Cancelar
            </button>
          </form>

          <table class="w-full table-auto border border-gray-300 rounded shadow">
            <thead class="bg-gray-100">
              <tr>
                <th class="p-2 border-b">Nombre</th>
                <th class="p-2 border-b">Descripción</th>
                <th class="p-2 border-b">Apartamentos Asociados</th>
                <th class="p-2 border-b">Locales Asociados</th>
                <th class="p-2 border-b">Acciones</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="estado in estadosInmueble || []"
                :key="estado.id_estado_inmueble"
                class="hover:bg-gray-50"
              >
                <td class="p-2 border-b">{{ estado.nombre }}</td>
                <td class="p-2 border-b">{{ estado.descripcion || '-' }}</td>
                <td class="p-2 border-b text-center">{{ (estado.apartamentos || []).length }}</td>
                <td class="p-2 border-b text-center">{{ (estado.locales || []).length }}</td>
                <td class="p-2 border-b text-center space-x-2">
                  <button @click="editEstadoInmueble(estado)" class="btn-edit">Editar</button>
                  <button
                    @click="confirmDeleteEstadoInmueble(estado.id_estado_inmueble)"
                    class="btn-delete"
                  >
                    Eliminar
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </section>
      </div>

      <!-- Modal confirmación eliminación -->
      <ConfirmDeleteModal
        v-if="showConfirmDelete"
        :message="deleteMessage"
        @confirm="deleteItem"
        @cancel="cancelDelete"
      />
  </SidebarBannerLayout>
</template>

<script setup>
import { ref } from 'vue'
import { useForm } from '@inertiajs/inertia-vue3'
import SidebarBannerLayout from '@/Components/SidebarBannerLayout.vue'
import InputText from '@/Components/InputText.vue'
import InputTextarea from '@/Components/InputTextarea.vue'
import ConfirmDeleteModal from '@/Components/ConfirmDeleteModal.vue'
import { Inertia } from '@inertiajs/inertia'

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
const deleteType = ref(null) // 'estado' or 'estadoInmueble'
const deleteMessage = ref('')

// Funciones para Estados
function submitEstado() {
  if (editEstadoId.value) {
    formEstado.put(`/estados/${editEstadoId.value}`, {
      onSuccess: () => {
        resetEstadoForm()
        reloadPage()
      },
    })
  } else {
    formEstado.post('/estados', {
      onSuccess: () => {
        resetEstadoForm()
        reloadPage()
      },
    })
  }
}

function editEstado(estado) {
  editEstadoId.value = estado.id_estado
  formEstado.nombre = estado.nombre
  formEstado.descripcion = estado.descripcion || ''
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
      onSuccess: () => {
        resetEstadoInmuebleForm()
        reloadPage()
      },
    })
  } else {
    formEstadoInmueble.post('/estados-inmueble', {
      onSuccess: () => {
        resetEstadoInmuebleForm()
        reloadPage()
      },
    })
  }
}

function editEstadoInmueble(estado) {
  editEstadoInmuebleId.value = estado.id_estado_inmueble
  formEstadoInmueble.nombre = estado.nombre
  formEstadoInmueble.descripcion = estado.descripcion || ''
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
    Inertia.delete(`/estados/${deleteId.value}`, {
      onSuccess: () => {
        reloadPage()
        showConfirmDelete.value = false
      },
    })
  } else if (deleteType.value === 'estadoInmueble') {
    Inertia.delete(`/estados-inmueble/${deleteId.value}`, {
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
  Inertia.reload({ only: ['estados', 'estadosInmueble'] })
}
</script>

<style scoped>
.btn-primary {
  background-color: #3b82f6;
  color: white;
  padding: 0.5rem 1rem;
  font-weight: 600;
  border-radius: 0.375rem;
  transition: background-color 0.2s;
}
.btn-primary:hover:not(:disabled) {
  background-color: #2563eb;
}
.btn-primary:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}
.btn-secondary {
  background-color: #e5e7eb;
  color: #374151;
  padding: 0.5rem 1rem;
  font-weight: 600;
  border-radius: 0.375rem;
  transition: background-color 0.2s;
}
.btn-secondary:hover {
  background-color: #d1d5db;
}
.btn-edit {
  background-color: #fbbf24;
  color: #92400e;
  padding: 0.25rem 0.5rem;
  border-radius: 0.375rem;
  font-weight: 600;
  cursor: pointer;
}
.btn-edit:hover {
  background-color: #f59e0b;
}
.btn-delete {
  background-color: #ef4444;
  color: white;
  padding: 0.25rem 0.5rem;
  border-radius: 0.375rem;
  font-weight: 600;
  cursor: pointer;
}
.btn-delete:hover {
  background-color: #dc2626;
}
table {
  border-collapse: collapse;
}
th,
td {
  text-align: left;
}
</style>
