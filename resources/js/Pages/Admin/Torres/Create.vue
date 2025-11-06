<template>
  <SidebarBannerLayout>
    <template #title>Crear Torre</template>

    <div class="max-w-3xl mx-auto bg-white rounded-lg shadow p-8">
      <form @submit.prevent="submit" class="space-y-6">
        <div>
          <label for="nombre_torre" class="block text-sm font-medium text-gray-700 mb-1">
            Nombre de la Torre <span class="text-red-500">*</span>
          </label>
          <input id="nombre_torre" v-model="form.nombre_torre" type="text"
            class="block w-full rounded-md border border-gray-300 px-4 py-2 focus:border-brand-500 focus:ring-1 focus:ring-brand-500" />
          <p v-if="form.errors.nombre_torre" class="mt-1 text-sm text-red-600">{{ form.errors.nombre_torre }}</p>
        </div>

        <div>
          <label for="numero_pisos" class="block text-sm font-medium text-gray-700 mb-1">
            Número de Pisos
          </label>
          <input id="numero_pisos" v-model="form.numero_pisos" type="number" min="1" max="32767"
            class="block w-full rounded-md border border-gray-300 px-4 py-2 focus:border-brand-500 focus:ring-1 focus:ring-brand-500" />
          <p v-if="form.errors.numero_pisos" class="mt-1 text-sm text-red-600">{{ form.errors.numero_pisos }}</p>
        </div>

        <div>
          <label for="id_proyecto" class="block text-sm font-medium text-gray-700 mb-1">
            Proyecto <span class="text-red-500">*</span>
          </label>
          <select id="id_proyecto" v-model="form.id_proyecto"
            class="block w-full rounded-md border border-gray-300 px-4 py-2 focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
            <option value="" disabled>Seleccione un proyecto</option>
            <option v-for="p in proyectos" :key="p.id_proyecto" :value="p.id_proyecto">{{ p.nombre }}</option>
          </select>
          <p v-if="form.errors.id_proyecto" class="mt-1 text-sm text-red-600">{{ form.errors.id_proyecto }}</p>
        </div>

        <div>
          <label for="id_estado" class="block text-sm font-medium text-gray-700 mb-1">
            Estado <span class="text-red-500">*</span>
          </label>
          <select id="id_estado" v-model="form.id_estado"
            class="block w-full rounded-md border border-gray-300 px-4 py-2 focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
            <option value="" disabled>Seleccione un estado</option>
            <option v-for="e in estados" :key="e.id_estado" :value="e.id_estado">{{ e.nombre }}</option>
          </select>
          <p v-if="form.errors.id_estado" class="mt-1 text-sm text-red-600">{{ form.errors.id_estado }}</p>
        </div>

        <button type="submit" :disabled="form.processing"
          class="mt-4 w-full rounded bg-brand-500 px-6 py-3 text-white font-semibold shadow hover:bg-brand-600 disabled:opacity-50">
          Guardar
        </button>
      </form>
    </div>
  </SidebarBannerLayout>
</template>

<script setup>
import { useForm } from '@inertiajs/inertia-vue3'
import SidebarBannerLayout from '@/Components/SidebarBannerLayout.vue'

const props = defineProps({
  proyectos: Array,
  estados: Array,
  empleado: Object,
})

const form = useForm({
  nombre_torre: '',
  numero_pisos: '',
  id_proyecto: '',
  id_estado: '',
})

function submit() {
  form.post(route('admin.torres.store'))
}
</script>