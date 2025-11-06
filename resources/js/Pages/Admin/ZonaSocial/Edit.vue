<template>
  <SidebarBannerLayout :empleado="empleado">
    <template #title>Editar Zona Social</template>

    <div class="bg-white rounded-lg border p-4 md:p-6 max-w-2xl">
      <form @submit.prevent="submit">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div class="md:col-span-2">
            <label class="form-label">Proyecto</label>
            <select v-model="form.id_proyecto" class="form-input">
              <option value="">Seleccione un proyecto</option>
              <option v-for="p in proyectos" :key="p.id_proyecto" :value="p.id_proyecto">
                {{ p.nombre }}
              </option>
            </select>
            <p v-if="errors.id_proyecto" class="form-error">{{ errors.id_proyecto }}</p>
          </div>

          <div class="md:col-span-2">
            <label class="form-label">Nombre</label>
            <input
              v-model="form.nombre"
              type="text"
              maxlength="100"
              class="form-input"
              placeholder="Ej: Gimnasio, Piscina, Salón Social"
            />
            <p v-if="errors.nombre" class="form-error">{{ errors.nombre }}</p>
          </div>

          <div class="md:col-span-2">
            <label class="form-label">Descripción (opcional)</label>
            <input
              v-model="form.descripcion"
              type="text"
              maxlength="100"
              class="form-input"
              placeholder="Breve descripción"
            />
            <p v-if="errors.descripcion" class="form-error">{{ errors.descripcion }}</p>
          </div>
        </div>

        <div class="mt-6 flex items-center gap-3">
          <button type="submit" class="btn-primary" :disabled="processing">
            {{ processing ? 'Actualizando...' : 'Actualizar' }}
          </button>
          <Link href="/zonas-sociales" class="btn-secondary">Cancelar</Link>
        </div>
      </form>
    </div>

    <FlashMessages />
  </SidebarBannerLayout>
</template>

<script setup>
import { reactive, ref } from 'vue'
import { Link } from '@inertiajs/inertia-vue3'
import { Inertia } from '@inertiajs/inertia'
import SidebarBannerLayout from '@/Components/SidebarBannerLayout.vue'
import FlashMessages from '@/Components/FlashMessages.vue'

const props = defineProps({
  zona: { type: Object, required: true },
  proyectos: { type: Array, default: () => [] },
  empleado: { type: Object, default: null },
})

const form = reactive({
  id_proyecto: props.zona.id_proyecto || '',
  nombre: props.zona.nombre || '',
  descripcion: props.zona.descripcion || '',
})

const errors = ref({})
const processing = ref(false)

function submit() {
  errors.value = {}
  processing.value = true
  Inertia.put(
    `/zonas-sociales/${props.zona.id_zona_social}`,
    {
      id_proyecto: form.id_proyecto || '',
      nombre: form.nombre,
      descripcion: form.descripcion || '',
    },
    {
      onError: (e) => {
        errors.value = e || {}
      },
      onFinish: () => {
        processing.value = false
      },
    }
  )
}
</script>

<style scoped>
.form-label {
  @apply block text-sm font-medium mb-1;
}
.form-input {
  @apply w-full border rounded-md px-3 py-2 text-sm;
}
.form-error {
  @apply text-sm text-red-600 mt-1;
}
.btn-primary {
  @apply inline-flex items-center gap-2 px-4 py-2 rounded-md bg-brand-600 text-white hover:bg-brand-700 disabled:opacity-60;
}
.btn-secondary {
  @apply inline-flex items-center gap-2 px-4 py-2 rounded-md border text-brand-700 hover:bg-brand-50;
}
</style>
