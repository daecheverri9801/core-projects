<template>
  <TopBannerLayout :empleado="empleado" panel-name="Proyectos">
    <div class="space-y-6">
      <!-- Header -->
      <PageHeader
        title="Editar apartamento"
        kicker="Inventario del proyecto"
        subtitle="Actualiza la información del apartamento. La prima de altura y el valor total se calculan automáticamente."
      >
        <template #actions>
          <ButtonSecondary href="/apartamentos">
            Volver
          </ButtonSecondary>
        </template>
      </PageHeader>

      <form @submit.prevent="submit" class="space-y-6">
        <!-- Form card -->
        <AppCard padding="md">
          <div class="flex items-start justify-between gap-4">
            <div class="min-w-0">
              <p class="text-sm text-gray-600">
                ID: <span class="font-semibold text-gray-900">{{ apartamento.id_apartamento }}</span>
              </p>
              <p class="text-xs text-gray-500 mt-1">
                Cambios se guardan al actualizar.
              </p>
            </div>
          </div>

          <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Proyecto -->
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

            <!-- Torre -->
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

            <!-- Piso -->
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

            <!-- Número -->
            <div>
              <label class="form-label">Número</label>
              <input v-model="form.numero" type="text" maxlength="20" class="form-input" placeholder="Ej: 302" />
              <p v-if="errors.numero" class="form-error">{{ errors.numero }}</p>
            </div>

            <!-- Tipo -->
            <div>
              <label class="form-label">Tipo de apartamento</label>
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
              <p v-if="errors.id_tipo_apartamento" class="form-error">{{ errors.id_tipo_apartamento }}</p>
            </div>

            <!-- Estado -->
            <div>
              <label class="form-label">Estado del inmueble</label>
              <select v-model="form.id_estado_inmueble" class="form-input">
                <option value="">Seleccione un estado</option>
                <option v-for="e in estados" :key="e.id_estado_inmueble" :value="e.id_estado_inmueble">
                  {{ e.nombre }}
                </option>
              </select>
              <p v-if="errors.id_estado_inmueble" class="form-error">{{ errors.id_estado_inmueble }}</p>
            </div>
          </div>
        </AppCard>

        <!-- Calculated card -->
        <AppCard padding="md">
          <div class="flex items-start justify-between gap-4">
            <div class="min-w-0">
              <h3 class="text-sm font-semibold text-gray-900">Cálculos</h3>
              <p class="text-xs text-gray-500 mt-1">
                Valores informativos (solo lectura).
              </p>
            </div>
          </div>

          <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Prima altura -->
            <div class="rounded-2xl border border-gray-200 p-4">
              <p class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Prima altura</p>
              <p class="mt-2 text-2xl font-semibold text-gray-900">
                {{ formatCurrency(primaAlturaCalculada) }}
              </p>
              <p class="mt-1 text-xs text-gray-500">
                Se calcula según el piso y configuración del proyecto.
              </p>
            </div>

            <!-- Valor total -->
            <div class="rounded-2xl border border-gray-200 p-4">
              <p class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Valor total</p>
              <p class="mt-2 text-2xl font-semibold text-gray-900">
                {{ formatCurrency(valorTotalCalculado) }}
              </p>
              <p class="mt-1 text-xs text-gray-500">
                Valor estimado del tipo + prima altura.
              </p>
            </div>
          </div>
        </AppCard>

        <!-- Actions -->
        <div class="flex items-center justify-end gap-2">
          <ButtonSecondary href="/apartamentos">
            Cancelar
          </ButtonSecondary>
          <ButtonPrimary type="submit">
            Actualizar
          </ButtonPrimary>
        </div>
      </form>

      <FlashMessages />
    </div>
  </TopBannerLayout>
</template>

<script setup>
import { reactive, ref, onMounted, computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'

import TopBannerLayout from '@/Components/TopBannerLayout.vue'
import FlashMessages from '@/Components/FlashMessages.vue'
import AppCard from '@/Components/AppCard.vue'
import PageHeader from '@/Components/PageHeader.vue'
import ButtonPrimary from '@/Components/ButtonPrimary.vue'
import ButtonSecondary from '@/Components/ButtonSecondary.vue'

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
  @apply block text-sm font-semibold text-gray-700 mb-1;
}
.form-input {
  @apply w-full rounded-xl border border-gray-300 px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-brand-500;
}
.form-error {
  @apply text-sm text-red-600 mt-1;
}
</style>
