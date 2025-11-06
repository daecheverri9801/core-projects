<template>
  <SidebarBannerLayout :empleado="empleado">
    <template #title>Editar Piso</template>

    <div class="bg-white rounded-lg border p-4 md:p-6 max-w-3xl">
      <form @submit.prevent="submit">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
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

          <div>
            <label class="form-label">Nivel</label>
            <input v-model.number="form.nivel" type="number" class="form-input" />
            <p v-if="errors.nivel" class="form-error">{{ errors.nivel }}</p>
          </div>

          <div>
            <label class="form-label">Uso</label>
            <input v-model="form.uso" type="text" maxlength="40" class="form-input" />
            <p v-if="errors.uso" class="form-error">{{ errors.uso }}</p>
          </div>
        </div>

        <div class="mt-6 flex items-center gap-3">
          <button type="submit" class="btn-primary">Actualizar</button>
          <Link href="/pisos-torre" class="btn-secondary">Cancelar</Link>
        </div>
      </form>
    </div>

    <FlashMessages />
  </SidebarBannerLayout>
</template>

<script setup>
import { reactive, ref, onMounted } from 'vue'
import { Link } from '@inertiajs/inertia-vue3'
import { Inertia } from '@inertiajs/inertia'
import SidebarBannerLayout from '@/Components/SidebarBannerLayout.vue'
import FlashMessages from '@/Components/FlashMessages.vue'

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
  Inertia.put(
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
  @apply block text-sm font-medium mb-1;
}
.form-input {
  @apply w-full border rounded-md px-3 py-2 text-sm;
}
.form-error {
  @apply text-sm text-red-600 mt-1;
}
.btn-primary {
  @apply inline-flex items-center gap-2 px-4 py-2 rounded-md bg-brand-600 text-white hover:bg-brand-700;
}
.btn-secondary {
  @apply inline-flex items-center gap-2 px-4 py-2 rounded-md border text-brand-700 hover:bg-brand-50;
}
</style>
