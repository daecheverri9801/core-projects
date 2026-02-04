<template>
  <SidebarBannerLayout :empleado="empleado">
    <div class="space-y-6">
      <!-- Header -->
      <PageHeader
        title="Nuevo Parqueadero"
        kicker="Inventario del proyecto"
        subtitle="Registra un nuevo parqueadero y opcionalmente asígnalo a un apartamento."
      />

      <!-- Form -->
      <AppCard padding="md" class="max-w-2xl">
        <form @submit.prevent="submit" class="space-y-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Número -->
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

            <!-- Tipo -->
            <div>
              <label class="form-label">Tipo</label>
              <select v-model="form.tipo" class="form-input">
                <option value="">Seleccione</option>
                <option v-for="t in tipos" :key="t" :value="t">
                  {{ t }}
                </option>
              </select>
              <p v-if="errors.tipo" class="form-error">{{ errors.tipo }}</p>
            </div>

            <!-- Apartamento -->
            <div>
              <label class="form-label">Apartamento (opcional)</label>
              <select v-model="form.id_apartamento" class="form-input">
                <option :value="''">Sin asignar</option>
                <option
                  v-for="a in apartamentos"
                  :key="a.id_apartamento"
                  :value="a.id_apartamento"
                >
                  {{ a.numero }} — {{ a.torre }} ({{ a.proyecto }})
                </option>
              </select>
              <p class="text-xs text-gray-500 mt-1">
                Si no se asigna, el parqueadero quedará disponible.
              </p>
              <p v-if="errors.id_apartamento" class="form-error">
                {{ errors.id_apartamento }}
              </p>
            </div>
          </div>

          <!-- Actions -->
          <div class="flex items-center gap-3 pt-2">
            <button
              type="submit"
              class="btn-primary"
              :disabled="processing"
            >
              <span v-if="processing">Guardando…</span>
              <span v-else>Guardar</span>
            </button>

            <Link href="/parqueaderos" class="btn-secondary">
              Cancelar
            </Link>
          </div>
        </form>
      </AppCard>

      <FlashMessages />
    </div>
  </SidebarBannerLayout>
</template>

<script setup>
import { reactive, ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'

import SidebarBannerLayout from '@/Components/SidebarBannerLayout.vue'
import PageHeader from '@/Components/PageHeader.vue'
import AppCard from '@/Components/AppCard.vue'
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

  router.post(
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
  @apply block text-sm font-medium text-gray-700 mb-1;
}
.form-input {
  @apply w-full rounded-xl border border-gray-300 px-3 py-2 text-sm
         focus:outline-none focus:ring-2 focus:ring-brand-500;
}
.form-error {
  @apply text-sm text-red-600 mt-1;
}
.btn-primary {
  @apply inline-flex items-center gap-2 px-4 py-2 rounded-xl
         bg-brand-600 text-white font-semibold
         hover:bg-brand-700 transition disabled:opacity-60;
}
.btn-secondary {
  @apply inline-flex items-center gap-2 px-4 py-2 rounded-xl
         border border-gray-300 text-brand-700
         hover:bg-brand-50 transition;
}
</style>
