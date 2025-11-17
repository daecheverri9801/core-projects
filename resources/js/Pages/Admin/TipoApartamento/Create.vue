<template>
  <SidebarBannerLayout :empleado="empleado">
    <template #title>Crear Tipo de Apartamento</template>

    <div class="bg-white rounded-lg border p-4 md:p-6 max-w-3xl">
      <form @submit.prevent="submit">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div class="md:col-span-2">
            <label class="form-label">Nombre</label>
            <input v-model="form.nombre" type="text" maxlength="100" class="form-input" placeholder="Ej: Tipo A - 3H" />
            <p v-if="errors.nombre" class="form-error">{{ errors.nombre }}</p>
          </div>

          <div>
            <label class="form-label">Área construida (m²)</label>
            <input v-model.number="form.area_construida" type="number" step="0.01" min="0" class="form-input" />
            <p v-if="errors.area_construida" class="form-error">{{ errors.area_construida }}</p>
          </div>

          <div>
            <label class="form-label">Área privada (m²)</label>
            <input v-model.number="form.area_privada" type="number" step="0.01" min="0" class="form-input" />
            <p v-if="errors.area_privada" class="form-error">{{ errors.area_privada }}</p>
          </div>

          <div>
            <label class="form-label">Habitaciones</label>
            <input v-model.number="form.cantidad_habitaciones" type="number" step="1" min="0" class="form-input" />
            <p v-if="errors.cantidad_habitaciones" class="form-error">{{ errors.cantidad_habitaciones }}</p>
          </div>

          <div>
            <label class="form-label">Baños</label>
            <input v-model.number="form.cantidad_banos" type="number" step="1" min="0" class="form-input" />
            <p v-if="errors.cantidad_banos" class="form-error">{{ errors.cantidad_banos }}</p>
          </div>

          <div class="md:col-span-2">
            <label class="form-label">Valor m² (COP)</label>
            <input v-model.number="form.valor_m2" type="number" step="0.01" min="0" class="form-input" />
            <p v-if="errors.valor_m2" class="form-error">{{ errors.valor_m2 }}</p>
          </div>
        </div>

        <div class="mt-6 flex items-center gap-3">
          <button type="submit" class="btn-primary">Guardar</button>
          <Link href="/tipos-apartamento" class="btn-secondary">Cancelar</Link>
        </div>
      </form>
    </div>

    <FlashMessages />
  </SidebarBannerLayout>
</template>

<script setup>
import { reactive, ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import SidebarBannerLayout from '@/Components/SidebarBannerLayout.vue'
import FlashMessages from '@/Components/FlashMessages.vue'

const props = defineProps({
  empleado: { type: Object, default: null },
})

const form = reactive({
  nombre: '',
  area_construida: '',
  area_privada: '',
  cantidad_habitaciones: '',
  cantidad_banos: '',
  valor_m2: '',
})

const errors = ref({})

function submit() {
  errors.value = {}
  router.post('/tipos-apartamento', { ...form }, {
    onError: e => { errors.value = e || {} },
  })
}
</script>

<style scoped>
.form-label { @apply block text-sm font-medium mb-1; }
.form-input { @apply w-full border rounded-md px-3 py-2 text-sm; }
.form-error { @apply text-sm text-red-600 mt-1; }
.btn-primary { @apply inline-flex items-center gap-2 px-4 py-2 rounded-md bg-brand-600 text-white hover:bg-brand-700; }
.btn-secondary { @apply inline-flex items-center gap-2 px-4 py-2 rounded-md border text-brand-700 hover:bg-brand-50; }
</style>