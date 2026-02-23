<template>
  <TopBannerLayout :empleado="empleado">
    <div class="space-y-6">
      <PageHeader
        title="Editar Parqueadero"
        kicker="Inventario del proyecto"
        subtitle="Actualiza la información del parqueadero y, si aplica, su asignación."
      />

      <AppCard padding="md" class="max-w-3xl">
        <form @submit.prevent="submit" class="space-y-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="form-label">Proyecto</label>
              <select v-model="form.id_proyecto" class="form-input">
                <option value="">Seleccione</option>
                <option v-for="p in proyectos" :key="p.id_proyecto" :value="p.id_proyecto">
                  {{ p.nombre }}
                </option>
              </select>
              <p v-if="errors.id_proyecto" class="form-error">{{ errors.id_proyecto }}</p>
            </div>

            <div>
              <label class="form-label">Torre</label>
              <select v-model="form.id_torre" class="form-input" :disabled="!form.id_proyecto">
                <option value="">Seleccione</option>
                <option v-for="t in torresFiltradas" :key="t.id_torre" :value="t.id_torre">
                  {{ t.nombre_torre }}
                </option>
              </select>
              <p v-if="errors.id_torre" class="form-error">{{ errors.id_torre }}</p>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="md:col-span-1">
              <label class="form-label">Número</label>
              <input v-model="form.numero" type="text" maxlength="20" class="form-input" />
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

            <div class="md:col-span-2">
              <label class="form-label">Precio (opcional)</label>
              <input v-model="form.precio" type="number" min="0" step="1" class="form-input" />
              <p v-if="errors.precio" class="form-error">{{ errors.precio }}</p>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="form-label">Apartamento (opcional)</label>
              <select v-model="form.id_apartamento" class="form-input" :disabled="!form.id_torre">
                <option :value="''">Sin asignar</option>
                <option
                  v-for="a in apartamentosFiltrados"
                  :key="a.id_apartamento"
                  :value="a.id_apartamento"
                >
                  {{ a.numero }}
                </option>
              </select>
              <p class="text-xs text-gray-500 mt-1">
                Si no se asigna, quedará disponible (inventario).
              </p>
              <p v-if="errors.id_apartamento" class="form-error">{{ errors.id_apartamento }}</p>
            </div>
          </div>

          <div class="flex items-center gap-3 pt-2">
            <button type="submit" class="btn-primary" :disabled="processing">
              <span v-if="processing">Actualizando…</span>
              <span v-else>Actualizar</span>
            </button>
            <Link href="/parqueaderos" class="btn-secondary">Cancelar</Link>
          </div>
        </form>
      </AppCard>

      <FlashMessages />
    </div>
  </TopBannerLayout>
</template>

<script setup>
import { reactive, ref, computed, watch } from 'vue'
import { Link, router } from '@inertiajs/vue3'

import TopBannerLayout from '@/Components/TopBannerLayout.vue'
import PageHeader from '@/Components/PageHeader.vue'
import AppCard from '@/Components/AppCard.vue'
import FlashMessages from '@/Components/FlashMessages.vue'

const props = defineProps({
  parqueadero: { type: Object, required: true },
  proyectos: { type: Array, default: () => [] },
  torres: { type: Array, default: () => [] },
  apartamentos: { type: Array, default: () => [] },
  tipos: { type: Array, default: () => ['Vehiculo', 'Moto'] },
  empleado: { type: Object, default: null },
})

const form = reactive({
  id_proyecto: props.parqueadero.id_proyecto ?? '',
  id_torre: props.parqueadero.id_torre ?? '',
  numero: props.parqueadero.numero ?? '',
  tipo: props.parqueadero.tipo ?? '',
  precio: props.parqueadero.precio ?? '',
  id_apartamento: props.parqueadero.id_apartamento ?? '',
})

const errors = ref({})
const processing = ref(false)

const torresFiltradas = computed(() => {
  if (!form.id_proyecto) return []
  return props.torres.filter((t) => String(t.id_proyecto) === String(form.id_proyecto))
})

const apartamentosFiltrados = computed(() => {
  if (!form.id_torre) return []
  return props.apartamentos.filter((a) => String(a.id_torre) === String(form.id_torre))
})

watch(
  () => form.id_proyecto,
  () => {
    form.id_torre = ''
    form.id_apartamento = ''
  }
)

watch(
  () => form.id_torre,
  () => {
    form.id_apartamento = ''
  }
)

function submit() {
  errors.value = {}
  processing.value = true

  router.put(
    `/parqueaderos/${props.parqueadero.id_parqueadero}`,
    {
      id_proyecto: form.id_proyecto,
      id_torre: form.id_torre,
      numero: form.numero,
      tipo: form.tipo,
      precio: String(form.precio).trim() === '' ? null : Number(form.precio),
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
