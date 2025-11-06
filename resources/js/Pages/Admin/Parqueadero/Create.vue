<template>
  <SidebarBannerLayout :empleado="empleado">
    <template #title>Nuevo Parqueadero</template>

    <div class="bg-white rounded-lg border p-4 md:p-6 max-w-2xl">
      <form @submit.prevent="submit">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div class="md:col-span-2">
            <label class="form-label">Número</label>
            <input
              v-model="form.numero"
              type="text"
              maxlength="20"
              class="form-input"
              placeholder="Ej: P-101"
            />
            <p v-if="errors.numero" class="form-error">{{ errors.numero }}</p>
          </div>

          <div>
            <label class="form-label">Tipo</label>
            <select v-model="form.tipo" class="form-input">
              <option value="">Seleccione</option>
              <option v-for="t in tipos" :key="t" :value="t">{{ t }}</option>
            </select>
            <p v-if="errors.tipo" class="form-error">{{ errors.tipo }}</p>
          </div>

          <div>
            <label class="form-label">Apartamento (opcional)</label>
            <select v-model="form.id_apartamento" class="form-input">
              <option :value="''">Sin asignar</option>
              <option v-for="a in apartamentos" :key="a.id_apartamento" :value="a.id_apartamento">
                {{ a.numero }} — {{ a.torre }} ({{ a.proyecto }})
              </option>
            </select>
            <p v-if="errors.id_apartamento" class="form-error">{{ errors.id_apartamento }}</p>
          </div>
        </div>

        <div class="mt-6 flex items-center gap-3">
          <button type="submit" class="btn-primary" :disabled="processing">
            {{ processing ? 'Guardando...' : 'Guardar' }}
          </button>
          <Link href="/parqueaderos" class="btn-secondary">Cancelar</Link>
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
  apartamentos: { type: Array, default: () => [] },
  tipos: { type: Array, default: () => ['Vehiculo', 'Moto'] },
  empleado: { type: Object, default: null },
})

const form = reactive({
  numero: '',
  tipo: '',
  id_apartamento: '',
})

const errors = ref({})
const processing = ref(false)

function submit() {
  errors.value = {}
  processing.value = true
  Inertia.post(
    '/parqueaderos',
    {
      numero: form.numero,
      tipo: form.tipo,
      id_apartamento: form.id_apartamento || null,
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
