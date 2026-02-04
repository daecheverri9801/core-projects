<template>
  <SidebarBannerLayout :empleado="empleado">
    <div class="space-y-6">
      <PageHeader
        title="Pisos de Torre"
        kicker="Edición"
        subtitle="Actualiza la información del piso seleccionado."
      >
        <template #actions>
          <Link
            href="/pisos-torre"
            class="rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-800 hover:bg-gray-50 transition"
          >
            Volver
          </Link>
        </template>
      </PageHeader>

      <AppCard padding="md" class="max-w-3xl">
        <div class="flex flex-col gap-1">
          <p class="text-xs text-gray-600">Piso</p>
          <div class="flex flex-wrap items-center gap-2">
            <h3 class="text-xl font-semibold text-gray-900">
              Editar piso
            </h3>
            <span
              class="inline-flex items-center rounded-full border border-gray-200 bg-gray-50 px-2.5 py-1 text-xs font-semibold text-gray-700"
            >
              ID: {{ piso.id_piso_torre }}
            </span>
          </div>
          <p class="text-sm text-gray-600">
            Cambios guardarán en la torre seleccionada.
          </p>
        </div>

        <form @submit.prevent="submit" class="mt-6 space-y-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Proyecto -->
            <div>
              <label class="form-label">Proyecto</label>
              <select v-model="form.id_proyecto" @change="loadTorres" class="form-input">
                <option value="">Seleccione un proyecto</option>
                <option v-for="p in proyectos" :key="p.id_proyecto" :value="p.id_proyecto">
                  {{ p.nombre }}
                </option>
              </select>
              <p v-if="errors.id_proyecto" class="form-error">{{ errors.id_proyecto }}</p>
            </div>

            <!-- Torre -->
            <div>
              <label class="form-label">Torre</label>
              <select v-model="form.id_torre" :disabled="torres.length === 0" class="form-input">
                <option value="">Seleccione una torre</option>
                <option v-for="t in torres" :key="t.id_torre" :value="t.id_torre">
                  {{ t.nombre_torre }}
                </option>
              </select>
              <p v-if="errors.id_torre" class="form-error">{{ errors.id_torre }}</p>
            </div>

            <!-- Nivel -->
            <div>
              <label class="form-label">Nivel</label>
              <input v-model.number="form.nivel" type="number" class="form-input" />
              <p v-if="errors.nivel" class="form-error">{{ errors.nivel }}</p>
            </div>

            <!-- Uso -->
            <div>
              <label class="form-label">Uso</label>
              <input v-model="form.uso" type="text" maxlength="40" class="form-input" />
              <p v-if="errors.uso" class="form-error">{{ errors.uso }}</p>
            </div>
          </div>

          <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-between pt-2">
            <Link href="/pisos-torre" class="btn-secondary">
              Cancelar
            </Link>

            <button type="submit" class="btn-primary">
              Actualizar
            </button>
          </div>
        </form>
      </AppCard>

      <FlashMessages />
    </div>
  </SidebarBannerLayout>
</template>

<script setup>
import { reactive, ref, onMounted } from 'vue'
import { Link, router } from '@inertiajs/vue3'

import SidebarBannerLayout from '@/Components/SidebarBannerLayout.vue'
import FlashMessages from '@/Components/FlashMessages.vue'
import AppCard from '@/Components/AppCard.vue'
import PageHeader from '@/Components/PageHeader.vue'

const props = defineProps({
  piso: { type: Object, required: true },
  proyectos: { type: Array, default: () => [] },
  torresInicial: { type: Array, default: () => [] },
  empleado: { type: Object, default: null },
})

const form = reactive({
  id_proyecto: props.piso.id_proyecto || '',
  id_torre: props.piso.id_torre || '',
  nivel: props.piso.nivel ?? '',
  uso: props.piso.uso ?? '',
})

const errors = ref({})
const torres = ref([])

onMounted(() => {
  torres.value = props.torresInicial || []
})

async function loadTorres() {
  form.id_torre = ''
  errors.value.id_torre = undefined
  torres.value = []
  if (!form.id_proyecto) return
  try {
    const res = await fetch(`/api/torres-por-proyecto/${form.id_proyecto}`)
    if (!res.ok) throw new Error('Error cargando torres')
    torres.value = await res.json()
  } catch (e) {
    console.error(e)
  }
}

function submit() {
  errors.value = {}
  router.put(
    `/pisos-torre/${props.piso.id_piso_torre}`,
    {
      id_torre: form.id_torre,
      nivel: form.nivel,
      uso: form.uso || null,
    },
    {
      onError: (e) => {
        errors.value = e || {}
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
  @apply w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900
  focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500
  disabled:bg-gray-50 disabled:text-gray-500;
}
.form-error {
  @apply text-sm text-red-600 mt-1;
}
.btn-primary {
  @apply inline-flex items-center justify-center gap-2 rounded-xl bg-brand-600 px-4 py-2 text-sm font-semibold text-white
  hover:bg-brand-700 transition disabled:opacity-50;
}
.btn-secondary {
  @apply inline-flex items-center justify-center gap-2 rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-800
  hover:bg-gray-50 transition;
}
</style>
