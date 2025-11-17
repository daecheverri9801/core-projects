<template>
  <SidebarBannerLayout>
    <template #title>Editar Empleado</template>

    <form
      @submit.prevent="submit"
      class="space-y-6 bg-white rounded-lg shadow p-8 max-w-4xl mx-auto"
    >
      <div class="grid grid-cols-2 gap-6">
        <div>
          <label for="nombre" class="block text-sm font-medium text-gray-700 mb-1"
            >Nombre <span class="text-red-500">*</span></label
          >
          <input id="nombre" v-model="form.nombre" type="text" class="input" />
          <ErrorMessage :message="form.errors.nombre" />
        </div>

        <div>
          <label for="apellido" class="block text-sm font-medium text-gray-700 mb-1"
            >Apellido <span class="text-red-500">*</span></label
          >
          <input id="apellido" v-model="form.apellido" type="text" class="input" />
          <ErrorMessage :message="form.errors.apellido" />
        </div>
      </div>

      <div>
        <label for="email" class="block text-sm font-medium text-gray-700 mb-1"
          >Email <span class="text-red-500">*</span></label
        >
        <input id="email" v-model="form.email" type="email" class="input" />
        <ErrorMessage :message="form.errors.email" />
      </div>

      <div class="relative">
        <label for="password" class="block text-sm font-medium text-gray-700 mb-1"
          >Contraseña (dejar vacío para no cambiar)</label
        >
        <input
          :type="showPassword ? 'text' : 'password'"
          id="password"
          v-model="form.password"
          class="input pr-10"
        />
        <button
          type="button"
          @click="showPassword = !showPassword"
          class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-500 hover:text-gray-700 focus:outline-none"
          :aria-label="showPassword ? 'Ocultar contraseña' : 'Mostrar contraseña'"
        >
          <svg
            v-if="showPassword"
            xmlns="http://www.w3.org/2000/svg"
            class="h-5 w-5"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
            stroke-width="2"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              d="M13.875 18.825A10.05 10.05 0 0112 19c-5.523 0-10-4.477-10-10a9.96 9.96 0 012.175-6.125M15 12a3 3 0 11-6 0 3 3 0 016 0z"
            />
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18" />
          </svg>
          <svg
            v-else
            xmlns="http://www.w3.org/2000/svg"
            class="h-5 w-5"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
            stroke-width="2"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
            />
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
            />
          </svg>
        </button>
        <ErrorMessage :message="form.errors.password" />
      </div>

      <div>
        <label for="telefono" class="block text-sm font-medium text-gray-700 mb-1">Teléfono</label>
        <input id="telefono" v-model="form.telefono" type="text" class="input" />
        <ErrorMessage :message="form.errors.telefono" />
      </div>

      <div class="grid grid-cols-2 gap-6">
        <div>
          <label for="id_dependencia" class="block text-sm font-medium text-gray-700 mb-1"
            >Dependencia <span class="text-red-500">*</span></label
          >
          <select id="id_dependencia" v-model="form.id_dependencia" class="input">
            <option value="" disabled>Seleccione una dependencia</option>
            <option
              v-for="dep in dependencias"
              :key="dep.id_dependencia"
              :value="dep.id_dependencia"
            >
              {{ dep.nombre }}
            </option>
          </select>
          <ErrorMessage :message="form.errors.id_dependencia" />
        </div>
        
        <div>
          <label for="id_cargo" class="block text-sm font-medium text-gray-700 mb-1"
            >Cargo <span class="text-red-500">*</span></label
          >
          <select id="id_cargo" v-model="form.id_cargo" class="input">
            <option value="" disabled>Seleccione un cargo</option>
            <option v-for="cargo in cargos" :key="cargo.id_cargo" :value="cargo.id_cargo">
              {{ cargo.nombre }}
            </option>
          </select>
          <ErrorMessage :message="form.errors.id_cargo" />
        </div>
      </div>

      <div class="flex items-center gap-4">
        <input id="estado" type="checkbox" v-model="form.estado" class="h-4 w-4 text-brand-600" />
        <label for="estado" class="text-sm text-gray-700">Activo</label>
      </div>

      <button type="submit" :disabled="form.processing" class="btn-primary w-full">
        Guardar Cambios
      </button>
    </form>
  </SidebarBannerLayout>
</template>

<script setup>
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import SidebarBannerLayout from '@/Components/SidebarBannerLayout.vue'
import ErrorMessage from '@/Components/ErrorMessage.vue'

const props = defineProps({
  empleado: Object,
  cargos: Array,
  dependencias: Array,
})

const form = useForm({
  nombre: props.empleado.nombre || '',
  apellido: props.empleado.apellido || '',
  email: props.empleado.email || '',
  password: '',
  telefono: props.empleado.telefono || '',
  id_cargo: props.empleado.id_cargo || '',
  id_dependencia: props.empleado.id_dependencia || '',
  estado: props.empleado.estado ?? true,
})

const showPassword = ref(false)

function submit() {
  form.put(`/empleados/${props.empleado.id_empleado}`)
}
</script>

<style scoped>
.input {
  width: 100%;
  border: 1px solid #d1d5db;
  border-radius: 0.375rem;
  padding: 0.5rem 1rem;
  font-size: 1rem;
  outline: none;
  transition: border-color 0.2s;
}
.input:focus {
  border-color: #3b82f6;
  box-shadow: 0 0 0 1px #3b82f6;
}
.btn-primary {
  background-color: #3b82f6;
  color: white;
  padding: 0.75rem 1rem;
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
</style>
