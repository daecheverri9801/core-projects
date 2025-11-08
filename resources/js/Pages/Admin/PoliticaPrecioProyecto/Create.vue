<template>
  <SidebarBannerLayout :empleado="empleado">
    <template #title>Nueva Política de Precio</template>

    <div class="bg-white rounded-lg border p-4 md:p-6 max-w-3xl">
      <h2 class="text-lg font-semibold text-brand-900 mb-4">Crear Política</h2>

      <form @submit.prevent="submit" class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <!-- Proyecto -->
          <div class="md:col-span-2">
            <label class="form-label">Proyecto *</label>
            <select v-model="form.id_proyecto" class="form-input">
              <option value="">Seleccione un proyecto</option>
              <option v-for="p in proyectos" :key="p.id_proyecto" :value="p.id_proyecto">
                {{ p.nombre }}
              </option>
            </select>
            <p v-if="form.errors.id_proyecto" class="form-error">
              {{ form.errors.id_proyecto }}
            </p>
          </div>

          <!-- Ventas por Escalón -->
          <div>
            <label class="form-label">Ventas por Escalón</label>
            <input
              v-model.number="form.ventas_por_escalon"
              type="number"
              min="1"
              class="form-input"
              placeholder="Ej: 10"
            />
            <p v-if="form.errors.ventas_por_escalon" class="form-error">
              {{ form.errors.ventas_por_escalon }}
            </p>
          </div>

          <!-- Porcentaje Aumento -->
          <div>
            <label class="form-label">% Aumento</label>
            <input
              v-model.number="form.porcentaje_aumento"
              type="number"
              step="0.001"
              min="0"
              max="999.999"
              class="form-input"
              placeholder="Ej: 5.5"
            />
            <p v-if="form.errors.porcentaje_aumento" class="form-error">
              {{ form.errors.porcentaje_aumento }}
            </p>
          </div>

          <!-- Aplica Desde -->
          <div>
            <label class="form-label">Aplica Desde</label>
            <input v-model="form.aplica_desde" type="date" class="form-input" />
            <p v-if="form.errors.aplica_desde" class="form-error">
              {{ form.errors.aplica_desde }}
            </p>
          </div>

          <!-- Estado -->
          <div class="flex items-center gap-2">
            <label class="flex items-center gap-2 cursor-pointer">
              <input
                v-model="form.estado"
                type="checkbox"
                class="rounded border-gray-300 text-brand-600 focus:ring-brand-500"
              />
              <span class="text-sm font-medium text-gray-700">Activa</span>
            </label>
          </div>
        </div>

        <!-- Botones -->
        <div class="flex items-center gap-3 pt-4">
          <button type="submit" class="btn-primary" :disabled="form.processing">
            {{ form.processing ? 'Guardando...' : 'Guardar' }}
          </button>
          <Link href="/politicas-precio-proyecto" class="btn-secondary">Cancelar</Link>
        </div>
      </form>

      <FlashMessages />
    </div>
  </SidebarBannerLayout>
</template>

<script setup>
import { useForm, Link } from '@inertiajs/inertia-vue3'
import SidebarBannerLayout from '@/Components/SidebarBannerLayout.vue'
import FlashMessages from '@/Components/FlashMessages.vue'

const props = defineProps({
  proyectos: { type: Array, default: () => [] },
  proyectoSeleccionado: { type: [String, Number], default: null },
  empleado: { type: Object, default: null },
})

const form = useForm({
  id_proyecto: props.proyectoSeleccionado || '',
  ventas_por_escalon: null,
  porcentaje_aumento: null,
  aplica_desde: '',
  estado: true,
})

const submit = () => form.post('/politicas-precio-proyecto')
</script>

<style scoped>
.form-label {
  @apply block text-sm font-medium text-gray-700 mb-1;
}
.form-input {
  @apply w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-brand-500;
}
.form-error {
  @apply text-red-600 text-sm mt-1;
}
.btn-primary {
  @apply inline-flex items-center gap-2 px-4 py-2 rounded-md bg-brand-600 text-white hover:bg-brand-700 disabled:opacity-50;
}
.btn-secondary {
  @apply inline-flex items-center gap-2 px-4 py-2 rounded-md border border-gray-300 text-gray-700 hover:bg-gray-50;
}
</style>
