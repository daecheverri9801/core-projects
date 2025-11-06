<template>
  <SidebarBannerLayout :empleado="empleado">
    <template #title>Crear Apartamento</template>

    <div class="bg-white rounded-lg border p-4 md:p-6 max-w-3xl">
      <form @submit.prevent="submit">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="form-label">Proyecto</label>
            <select v-model="form.id_proyecto" @change="onProyectoChange" class="form-input">
              <option value="">Seleccione un proyecto</option>
              <option v-for="p in proyectos" :key="p.id_proyecto" :value="p.id_proyecto">
                {{ p.nombre }}
              </option>
            </select>
            <p v-if="errors.id_proyecto" class="form-error">{{ errors.id_proyecto }}</p>
          </div>

          <div>
            <label class="form-label">Torre</label>
            <select
              v-model="form.id_torre"
              :disabled="torres.length === 0"
              @change="onTorreChange"
              class="form-input"
            >
              <option value="">Seleccione una torre</option>
              <option v-for="t in torres" :key="t.id_torre" :value="t.id_torre">
                {{ t.nombre_torre }}
              </option>
            </select>
            <p v-if="errors.id_torre" class="form-error">{{ errors.id_torre }}</p>
          </div>

          <div>
            <label class="form-label">Piso</label>
            <select v-model="form.id_piso_torre" :disabled="pisos.length === 0" class="form-input">
              <option value="">Seleccione un piso</option>
              <option v-for="p in pisos" :key="p.id_piso_torre" :value="p.id_piso_torre">
                Nivel {{ p.nivel }}
              </option>
            </select>
            <p v-if="errors.id_piso_torre" class="form-error">{{ errors.id_piso_torre }}</p>
          </div>

          <div>
            <label class="form-label">Número</label>
            <input
              v-model="form.numero"
              type="text"
              maxlength="20"
              class="form-input"
              placeholder="Ej: 302"
            />
            <p v-if="errors.numero" class="form-error">{{ errors.numero }}</p>
          </div>

          <div>
            <label class="form-label">Tipo de Apartamento</label>
            <select v-model="form.id_tipo_apartamento" class="form-input">
              <option value="">Seleccione un tipo</option>
              <option
                v-for="t in tipos"
                :key="t.id_tipo_apartamento"
                :value="t.id_tipo_apartamento"
              >
                {{ t.nombre }}
              </option>
            </select>
            <p v-if="errors.id_tipo_apartamento" class="form-error">
              {{ errors.id_tipo_apartamento }}
            </p>
          </div>

          <div>
            <label class="form-label">Estado del Inmueble</label>
            <select v-model="form.id_estado_inmueble" class="form-input">
              <option value="">Seleccione un estado</option>
              <option
                v-for="e in estados"
                :key="e.id_estado_inmueble"
                :value="e.id_estado_inmueble"
              >
                {{ e.nombre }}
              </option>
            </select>
            <p v-if="errors.id_estado_inmueble" class="form-error">
              {{ errors.id_estado_inmueble }}
            </p>
          </div>

          <div>
            <label class="form-label">Prima Altura</label>
            <input
              type="text"
              class="form-input bg-gray-50"
              :value="formatCurrency(primaAlturaCalculada)"
              disabled
            />
            <p class="text-xs text-gray-500">
              Se calcula según el piso y configuración del proyecto.
            </p>
          </div>

          <!-- Valor Total (solo lectura) -->
          <div>
            <label class="form-label">Valor Total</label>
            <input
              type="text"
              class="form-input bg-gray-50"
              :value="formatCurrency(valorTotalCalculado)"
              disabled
            />
            <p class="text-xs text-gray-500">Valor Estimado + Prima Altura.</p>
          </div>

        </div>

        <div class="mt-6 flex items-center gap-3">
          <button type="submit" class="btn-primary">Guardar</button>
          <Link href="/apartamentos" class="btn-secondary">Cancelar</Link>
        </div>
      </form>
    </div>

    <FlashMessages />
  </SidebarBannerLayout>
</template>

<script setup>
import { reactive, ref, computed } from 'vue'
import { Link } from '@inertiajs/inertia-vue3'
import { Inertia } from '@inertiajs/inertia'
import SidebarBannerLayout from '@/Components/SidebarBannerLayout.vue'
import FlashMessages from '@/Components/FlashMessages.vue'

const props = defineProps({
  proyectos: { type: Array, default: () => [] },
  tipos: { type: Array, default: () => [] },
  estados: { type: Array, default: () => [] },
  torres: { type: Array, default: () => [] },
  pisos: { type: Array, default: () => [] },
  empleado: { type: Object, default: null },
})

const form = reactive({
  id_proyecto: '',
  id_torre: '',
  id_piso_torre: '',
  numero: '',
  id_tipo_apartamento: '',
  id_estado_inmueble: '',
})

const errors = ref({})
const torres = ref([])
const pisos = ref([])
const processing = ref(false)

function formatCurrency(v) {
  const n = Number(v || 0)
  return n.toLocaleString('es-CO', { style: 'currency', currency: 'COP', maximumFractionDigits: 0 })
}

async function onProyectoChange() {
  form.id_torre = ''
  form.id_piso_torre = ''
  torres.value = []
  pisos.value = []
  if (!form.id_proyecto) return
  try {
    const res = await fetch(`/api/torres-por-proyecto/${form.id_proyecto}`)
    if (!res.ok) throw new Error('Error cargando torres')
    torres.value = await res.json()
  } catch (e) {
    console.error(e)
  }
}

async function onTorreChange() {
  form.id_piso_torre = ''
  pisos.value = []
  if (!form.id_torre) return
  try {
    const res = await fetch(`/api/pisos-por-torre/${form.id_torre}`)
    if (!res.ok) throw new Error('Error cargando pisos')
    pisos.value = await res.json()
  } catch (e) {
    console.error(e)
  }
}

const primaAlturaCalculada = computed(() => {
  // Buscar el piso seleccionado
  const piso = props.pisos.find(p => p.id_piso_torre === form.id_piso_torre)
  if (!piso || !piso.nivel) return 0

  const nivel = parseInt(piso.nivel)
  if (nivel < 2) return 0

  // Buscar la torre seleccionada y su proyecto
  const torre = props.torres.find(t => t.id_torre === form.id_torre)
  if (!torre || !torre.proyecto) return 0

  const proyecto = torre.proyecto
  if (!proyecto.prima_altura_activa) return 0

  const base = parseFloat(proyecto.prima_altura_base || 0)
  const incremento = parseFloat(proyecto.prima_altura_incremento || 0)

  return base + ((nivel - 2) * incremento)
})

const valorTotalCalculado = computed(() => {
  return valorEstimadoTipo.value + primaAlturaCalculada.value
})

const valorEstimadoTipo = computed(() => {
  const t = props.tipos.find(x => x.id_tipo_apartamento === form.id_tipo_apartamento)
  return t ? parseFloat(t.valor_estimado || 0) : 0
})

function submit() {
  errors.value = {}
  Inertia.post(
    '/apartamentos',
    {
      numero: form.numero,
      id_tipo_apartamento: form.id_tipo_apartamento,
      id_torre: form.id_torre,
      id_piso_torre: form.id_piso_torre,
      id_estado_inmueble: form.id_estado_inmueble,
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
