<template>
  <SidebarBannerLayout>
    <template #title>Gestión de Dependencias y Cargos</template>

    <div class="max-w-6xl mx-auto space-y-12">
      <!-- Dependencias -->
      <section>
        <h2 class="text-2xl font-bold mb-4">Dependencias</h2>

        <form @submit.prevent="submitDependencia" class="mb-6 space-y-4 max-w-md">
          <InputText
            label="Nombre"
            v-model="formDependencia.nombre"
            :error="formDependencia.errors.nombre"
            required
            maxlength="80"
          />
          <InputTextarea
            label="Descripción"
            v-model="formDependencia.descripcion"
            :error="formDependencia.errors.descripcion"
            maxlength="200"
          />
          <button type="submit" :disabled="formDependencia.processing" class="btn-primary">
            {{ editDependenciaId ? 'Actualizar Dependencia' : 'Crear Dependencia' }}
          </button>
          <button
            v-if="editDependenciaId"
            type="button"
            @click="cancelEditDependencia"
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
              <th class="p-2 border-b text-center">Empleados Asociados</th>
              <th class="p-2 border-b">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="dep in dependencias" :key="dep.id_dependencia" class="hover:bg-gray-50">
              <td class="p-2 border-b">{{ dep.nombre }}</td>
              <td class="p-2 border-b">{{ dep.descripcion || '-' }}</td>
              <td class="p-2 border-b text-center">{{ dep.empleados_count }}</td>
              <td class="p-2 border-b text-center space-x-2">
                <button @click="editDependencia(dep)" class="btn-edit">Editar</button>
                <button @click="confirmDeleteDependencia(dep.id_dependencia)" class="btn-delete">
                  Eliminar
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </section>

      <!-- Cargos -->
      <section>
        <h2 class="text-2xl font-bold mb-4">Cargos</h2>

        <form @submit.prevent="submitCargo" class="mb-6 space-y-4 max-w-md">
          <InputText
            label="Nombre"
            v-model="formCargo.nombre"
            :error="formCargo.errors.nombre"
            required
            maxlength="80"
          />
          <InputTextarea
            label="Descripción"
            v-model="formCargo.descripcion"
            :error="formCargo.errors.descripcion"
            maxlength="200"
          />
          <button type="submit" :disabled="formCargo.processing" class="btn-primary">
            {{ editCargoId ? 'Actualizar Cargo' : 'Crear Cargo' }}
          </button>
          <button v-if="editCargoId" type="button" @click="cancelEditCargo" class="btn-secondary">
            Cancelar
          </button>
        </form>

        <table class="w-full table-auto border border-gray-300 rounded shadow">
          <thead class="bg-gray-100">
            <tr>
              <th class="p-2 border-b">Nombre</th>
              <th class="p-2 border-b">Descripción</th>
              <th class="p-2 border-b text-center">Empleados Asociados</th>
              <th class="p-2 border-b">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="cargo in cargos" :key="cargo.id_cargo" class="hover:bg-gray-50">
              <td class="p-2 border-b">{{ cargo.nombre }}</td>
              <td class="p-2 border-b">{{ cargo.descripcion || '-' }}</td>
              <td class="p-2 border-b text-center">{{ cargo.empleados_count }}</td>
              <td class="p-2 border-b text-center space-x-2">
                <button @click="editCargo(cargo)" class="btn-edit">Editar</button>
                <button @click="confirmDeleteCargo(cargo.id_cargo)" class="btn-delete">
                  Eliminar
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </section>

      <!-- Modal confirmación eliminación -->
      <ConfirmDeleteModal
        v-if="showConfirmDelete"
        :message="deleteMessage"
        @confirm="deleteItem"
        @cancel="cancelDelete"
      />
    </div>
  </SidebarBannerLayout>
</template>

<script setup>
import { ref } from 'vue'
import { useForm, router } from '@inertiajs/vue3'
import SidebarBannerLayout from '@/Components/SidebarBannerLayout.vue'
import InputText from '@/Components/InputText.vue'
import InputTextarea from '@/Components/InputTextarea.vue'
import ConfirmDeleteModal from '@/Components/ConfirmDeleteModal.vue'

const props = defineProps({
  dependencias: Array,
  cargos: Array,
})

const dependencias = ref(props.dependencias)
const cargos = ref(props.cargos)

// Formularios
const formDependencia = useForm({
  nombre: '',
  descripcion: '',
})
const editDependenciaId = ref(null)

const formCargo = useForm({
  nombre: '',
  descripcion: '',
})
const editCargoId = ref(null)

// Modal eliminar
const showConfirmDelete = ref(false)
const deleteId = ref(null)
const deleteType = ref(null) // 'dependencia' o 'cargo'
const deleteMessage = ref('')

// Dependencias
function submitDependencia() {
  if (editDependenciaId.value) {
    formDependencia.put(`/dependencias/${editDependenciaId.value}`, {
      onSuccess: () => {
        resetDependenciaForm()
        reloadPage()
      },
    })
  } else {
    formDependencia.post('/dependencias', {
      onSuccess: () => {
        resetDependenciaForm()
        reloadPage()
      },
    })
  }
}

function editDependencia(dep) {
  editDependenciaId.value = dep.id_dependencia
  formDependencia.nombre = dep.nombre
  formDependencia.descripcion = dep.descripcion || ''
}

function cancelEditDependencia() {
  resetDependenciaForm()
}

function resetDependenciaForm() {
  editDependenciaId.value = null
  formDependencia.reset()
}

// Cargos
function submitCargo() {
  if (editCargoId.value) {
    formCargo.put(`/cargos/${editCargoId.value}`, {
      onSuccess: () => {
        resetCargoForm()
        reloadPage()
      },
    })
  } else {
    formCargo.post('/cargos', {
      onSuccess: () => {
        resetCargoForm()
        reloadPage()
      },
    })
  }
}

function editCargo(cargo) {
  editCargoId.value = cargo.id_cargo
  formCargo.nombre = cargo.nombre
  formCargo.descripcion = cargo.descripcion || ''
}

function cancelEditCargo() {
  resetCargoForm()
}

function resetCargoForm() {
  editCargoId.value = null
  formCargo.reset()
}

// Eliminar
function confirmDeleteDependencia(id) {
  deleteId.value = id
  deleteType.value = 'dependencia'
  deleteMessage.value = '¿Está seguro que desea eliminar esta dependencia?'
  showConfirmDelete.value = true
}

function confirmDeleteCargo(id) {
  deleteId.value = id
  deleteType.value = 'cargo'
  deleteMessage.value = '¿Está seguro que desea eliminar este cargo?'
  showConfirmDelete.value = true
}

function deleteItem() {
  if (deleteType.value === 'dependencia') {
    router.delete(`/dependencias/${deleteId.value}`, {
      onSuccess: () => {
        reloadPage()
        showConfirmDelete.value = false
      },
    })
  } else if (deleteType.value === 'cargo') {
    router.delete(`/cargos/${deleteId.value}`, {
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
  router.reload({ only: ['dependencias', 'cargos'] })
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
