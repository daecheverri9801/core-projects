<template>
  <SidebarBannerLayout :empleado="empleado">
    <template #title>Editar Apartamento</template>

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
            <input v-model="form.numero" type="text" maxlength="20" class="form-input" />
            <p v-if="errors.numero" class="form-error">{{ errors.numero }}</p>
          </div>

          <div>
            <label class="form-label">Tipo de Apartamento</label>
            <select v-model="form.id_tipo_apartamento" class="form-input">
              <option value="">Seleccione</option>
              <option
                v-for="t in tiposFiltrados"
                :key="t.id_tipo_apartamento"
                :value="t.id_tipo_apartamento"
              >
                {{ t.nombre }} — {{ formatCurrency(t.valor_estimado) }}
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

          <!-- Prima Altura (solo lectura, calculada) -->
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
          <button type="submit" class="btn-primary">Actualizar</button>
          <Link href="/apartamentos" class="btn-secondary">Cancelar</Link>
        </div>
      </form>
    </div>

    <FlashMessages />
  </SidebarBannerLayout>
</template>

<script setup>
import { reactive, ref, onMounted, computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import SidebarBannerLayout from '@/Components/SidebarBannerLayout.vue'
import FlashMessages from '@/Components/FlashMessages.vue'

const props = defineProps({
  apartamento: { type: Object, required: true },
  proyectos: { type: Array, default: () => [] },
  tipos: { type: Array, default: () => [] },
  estados: { type: Array, default: () => [] },
  torres: { type: Array, default: () => [] }, // torres correctas del proyecto
  pisos: { type: Array, default: () => [] }, // pisos correctos de la torre
  empleado: { type: Object, default: null },
})

const form = reactive({
  id_proyecto: props.apartamento.id_proyecto || '',
  id_torre: props.apartamento.id_torre || '',
  id_piso_torre: props.apartamento.id_piso_torre || '',
  numero: props.apartamento.numero || '',
  id_tipo_apartamento: props.apartamento.id_tipo_apartamento || '',
  id_estado_inmueble: props.apartamento.id_estado_inmueble || '',
})

const errors = ref({})
const torres = ref([])
const pisos = ref([])

// =========================
// CARGA INICIAL CORRECTA
// =========================
onMounted(() => {
  torres.value = [...props.torres]
  pisos.value = [...props.pisos]
})

setTimeout(() => {
  form.id_torre = form.id_torre
  form.id_piso_torre = form.id_piso_torre
}, 50)

// =========================
// CAMBIO DE PROYECTO
// =========================
async function onProyectoChange() {
  form.id_torre = ''
  form.id_piso_torre = ''
  form.id_tipo_apartamento = ''

  torres.value = []
  pisos.value = []

  if (!form.id_proyecto) return

  const res = await fetch(`/api/torres-por-proyecto/${form.id_proyecto}`)
  torres.value = await res.json()
}

// =========================
// CAMBIO DE TORRE
// =========================
async function onTorreChange() {
  form.id_piso_torre = ''
  pisos.value = []

  if (!form.id_torre) return

  const res = await fetch(`/api/pisos-por-torre/${form.id_torre}`)
  pisos.value = await res.json()
}

// =========================
// PRIMA ALTURA (CORREGIDA)
// =========================
const primaAlturaCalculada = computed(() => {
  const piso = pisos.value.find((p) => Number(p.id_piso_torre) === Number(form.id_piso_torre))
  if (!piso || piso.nivel == null) return 0

  const nivel = Number(piso.nivel)
  if (nivel < 2) return 0

  // Igual que CREATE
  const torre = torres.value.find((t) => Number(t.id_torre) === Number(form.id_torre))
  if (!torre || !torre.proyecto) return 0

  const proyecto = torre.proyecto
  if (!proyecto.prima_altura_activa) return 0

  const base = Number(proyecto.prima_altura_base || 0)
  const incremento = Number(proyecto.prima_altura_incremento || 0)

  return base + (nivel - 2) * incremento
})

// =========================
// VALOR TOTAL
// =========================
const valorEstimadoTipo = computed(() => {
  const t = props.tipos.find((x) => x.id_tipo_apartamento === form.id_tipo_apartamento)
  return t ? Number(t.valor_estimado || 0) : 0
})

const valorTotalCalculado = computed(() => {
  return valorEstimadoTipo.value + primaAlturaCalculada.value
})

// =========================
// SUBMIT
// =========================
function submit() {
  errors.value = {}

  router.put(
    `/apartamentos/${props.apartamento.id_apartamento}`,
    {
      numero: form.numero,
      id_tipo_apartamento: form.id_tipo_apartamento,
      id_torre: form.id_torre,
      id_piso_torre: form.id_piso_torre,
      id_estado_inmueble: form.id_estado_inmueble,
    },
    {
      onError: (e) => (errors.value = e),
    }
  )
}

function formatCurrency(v) {
  return Number(v || 0).toLocaleString('es-CO', {
    style: 'currency',
    currency: 'COP',
    maximumFractionDigits: 0,
  })
}

const tiposFiltrados = computed(() => {
  return props.tipos.filter((t) => Number(t.id_proyecto) === Number(form.id_proyecto))
})
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
