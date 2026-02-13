<!-- resources/js/Pages/Admin/Empleados/DependenciasCargos.vue -->
<template>
  <TopBannerLayout :empleado="empleado" panel-name="Panel administrador">
    <Head title="Dependencias y Cargos" />

    <div class="space-y-6">
      <PageHeader
        title="Dependencias y cargos"
        subtitle="Crea, edita y elimina dependencias y cargos. Visualiza cuántos empleados están asociados."
      />

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- ===================== -->
        <!-- DEPENDENCIAS -->
        <!-- ===================== -->
        <AppCard padding="md">
          <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
              <p class="text-sm font-semibold text-gray-900">Dependencias</p>
              <p class="text-sm text-gray-600 mt-1">
                Administra dependencias y valida empleados asociados.
              </p>
            </div>

            <span
              class="inline-flex items-center rounded-full border border-gray-200 bg-gray-50 px-3 py-1 text-xs font-semibold text-gray-700"
            >
              {{ dependencias?.length || 0 }} en total
            </span>
          </div>

          <!-- Form -->
          <form @submit.prevent="submitDependencia" class="mt-6 space-y-4">
            <FormField label="Nombre" required :error="formDependencia.errors.nombre">
              <TextInput
                v-model="formDependencia.nombre"
                maxlength="80"
                placeholder="Ej: Comercial"
              />
            </FormField>

            <FormField label="Descripción" :error="formDependencia.errors.descripcion">
              <TextArea
                v-model="formDependencia.descripcion"
                maxlength="200"
                rows="3"
                placeholder="Opcional…"
              />
            </FormField>

            <div class="flex flex-col sm:flex-row sm:items-center gap-2">
              <button
                type="submit"
                :disabled="formDependencia.processing"
                class="rounded-xl bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-brand-700 transition disabled:opacity-60"
              >
                {{ editDependenciaId ? 'Actualizar dependencia' : 'Crear dependencia' }}
              </button>

              <button
                v-if="editDependenciaId"
                type="button"
                @click="cancelEditDependencia"
                class="rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-800 hover:bg-gray-50 transition"
              >
                Cancelar
              </button>

              <span v-if="editDependenciaId" class="text-xs text-amber-700 sm:ml-auto">
                Editando ID: <span class="font-semibold">{{ editDependenciaId }}</span>
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
                    Asociados
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
                  v-for="dep in dependencias"
                  :key="dep.id_dependencia"
                  class="hover:bg-gray-50 transition"
                >
                  <td class="px-4 py-3 text-sm font-semibold text-gray-900">
                    {{ dep.nombre }}
                  </td>

                  <td class="px-4 py-3 text-sm text-gray-600">
                    {{ dep.descripcion || '—' }}
                  </td>

                  <td class="px-4 py-3 text-center">
                    <span
                      class="inline-flex items-center rounded-full border border-gray-200 bg-gray-50 px-2.5 py-1 text-xs font-semibold text-gray-700"
                    >
                      {{ dep.empleados_count }}
                    </span>
                  </td>

                  <td class="px-4 py-3">
                    <div class="flex justify-end items-center gap-2">
                      <button
                        type="button"
                        @click="editDependencia(dep)"
                        class="rounded-xl border border-amber-200 bg-amber-50 px-3 py-1.5 text-xs font-semibold text-amber-800 hover:bg-amber-100 transition"
                      >
                        Editar
                      </button>

                      <button
                        type="button"
                        @click="confirmDeleteDependencia(dep.id_dependencia)"
                        class="rounded-xl border border-red-200 bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-700 hover:bg-red-100 transition"
                      >
                        Eliminar
                      </button>
                    </div>
                  </td>
                </tr>

                <tr v-if="!dependencias?.length">
                  <td colspan="4" class="px-4 py-6 text-center text-sm text-gray-500">
                    No hay dependencias registradas.
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </AppCard>

        <!-- ===================== -->
        <!-- CARGOS -->
        <!-- ===================== -->
        <AppCard padding="md">
          <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
              <p class="text-sm font-semibold text-gray-900">Cargos</p>
              <p class="text-sm text-gray-600 mt-1">
                Administra cargos y valida empleados asociados.
              </p>
            </div>

            <span
              class="inline-flex items-center rounded-full border border-gray-200 bg-gray-50 px-3 py-1 text-xs font-semibold text-gray-700"
            >
              {{ cargos?.length || 0 }} en total
            </span>
          </div>

          <!-- Form -->
          <form @submit.prevent="submitCargo" class="mt-6 space-y-4">
            <FormField label="Nombre" required :error="formCargo.errors.nombre">
              <TextInput v-model="formCargo.nombre" maxlength="80" placeholder="Ej: Auxiliar" />
            </FormField>

            <FormField label="Descripción" :error="formCargo.errors.descripcion">
              <TextArea
                v-model="formCargo.descripcion"
                maxlength="200"
                rows="3"
                placeholder="Opcional…"
              />
            </FormField>

            <div class="flex flex-col sm:flex-row sm:items-center gap-2">
              <button
                type="submit"
                :disabled="formCargo.processing"
                class="rounded-xl bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-brand-700 transition disabled:opacity-60"
              >
                {{ editCargoId ? 'Actualizar cargo' : 'Crear cargo' }}
              </button>

              <button
                v-if="editCargoId"
                type="button"
                @click="cancelEditCargo"
                class="rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-800 hover:bg-gray-50 transition"
              >
                Cancelar
              </button>

              <span v-if="editCargoId" class="text-xs text-amber-700 sm:ml-auto">
                Editando ID: <span class="font-semibold">{{ editCargoId }}</span>
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
                    Asociados
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
                  v-for="cargo in cargos"
                  :key="cargo.id_cargo"
                  class="hover:bg-gray-50 transition"
                >
                  <td class="px-4 py-3 text-sm font-semibold text-gray-900">
                    {{ cargo.nombre }}
                  </td>

                  <td class="px-4 py-3 text-sm text-gray-600">
                    {{ cargo.descripcion || '—' }}
                  </td>

                  <td class="px-4 py-3 text-center">
                    <span
                      class="inline-flex items-center rounded-full border border-gray-200 bg-gray-50 px-2.5 py-1 text-xs font-semibold text-gray-700"
                    >
                      {{ cargo.empleados_count }}
                    </span>
                  </td>

                  <td class="px-4 py-3">
                    <div class="flex justify-end items-center gap-2">
                      <button
                        type="button"
                        @click="editCargo(cargo)"
                        class="rounded-xl border border-amber-200 bg-amber-50 px-3 py-1.5 text-xs font-semibold text-amber-800 hover:bg-amber-100 transition"
                      >
                        Editar
                      </button>

                      <button
                        type="button"
                        @click="confirmDeleteCargo(cargo.id_cargo)"
                        class="rounded-xl border border-red-200 bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-700 hover:bg-red-100 transition"
                      >
                        Eliminar
                      </button>
                    </div>
                  </td>
                </tr>

                <tr v-if="!cargos?.length">
                  <td colspan="4" class="px-4 py-6 text-center text-sm text-gray-500">
                    No hay cargos registrados.
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
  dependencias: Array,
  cargos: Array,
})

const dependencias = ref(props.dependencias || [])
const cargos = ref(props.cargos || [])

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
const deleteType = ref(null) // 'dependencia' | 'cargo'
const deleteMessage = ref('')

// Dependencias
function submitDependencia() {
  if (editDependenciaId.value) {
    formDependencia.put(`/dependencias/${editDependenciaId.value}`, {
      preserveScroll: true,
      onSuccess: () => {
        resetDependenciaForm()
        reloadPage()
      },
    })
  } else {
    formDependencia.post('/dependencias', {
      preserveScroll: true,
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
      preserveScroll: true,
      onSuccess: () => {
        resetCargoForm()
        reloadPage()
      },
    })
  } else {
    formCargo.post('/cargos', {
      preserveScroll: true,
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
      preserveScroll: true,
      onSuccess: () => {
        reloadPage()
        showConfirmDelete.value = false
      },
    })
  } else if (deleteType.value === 'cargo') {
    router.delete(`/cargos/${deleteId.value}`, {
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
  router.reload({ only: ['dependencias', 'cargos'] })
}
</script>
