<template>
  <TopBannerLayout :empleado="empleado">
    <div class="space-y-6">
      <PageHeader
        title="Editar apartamento"
        kicker="Inventario del proyecto"
        subtitle="Actualiza la información. La prima de altura se recalcula automáticamente según el tipo y el nivel."
      >
        <template #actions>
          <ButtonSecondary href="/apartamentos"> Volver </ButtonSecondary>
        </template>
      </PageHeader>

      <form @submit.prevent="submit" class="space-y-6">
        <!-- Form -->
        <AppCard padding="md">
          <div class="flex items-start justify-between gap-4">
            <div class="min-w-0">
              <p class="text-sm text-gray-600">
                ID:

                <span class="font-semibold text-gray-900">
                  {{ apartamento.id_apartamento }}
                </span>
              </p>

              <p class="text-xs text-gray-500 mt-1">Los precios se recalculan al guardar.</p>
            </div>
          </div>

          <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Proyecto -->
            <div>
              <label class="form-label"> Proyecto </label>

              <select v-model="form.id_proyecto" @change="onProyectoChange" class="form-input">
                <option value="">Seleccione un proyecto</option>

                <option v-for="p in proyectos" :key="p.id_proyecto" :value="p.id_proyecto">
                  {{ p.nombre }}
                </option>
              </select>

              <p v-if="errors.id_proyecto" class="form-error">
                {{ errors.id_proyecto }}
              </p>
            </div>

            <!-- Torre -->
            <div>
              <label class="form-label"> Torre </label>

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

              <p v-if="errors.id_torre" class="form-error">
                {{ errors.id_torre }}
              </p>
            </div>

            <!-- Piso -->
            <div>
              <label class="form-label"> Piso </label>

              <select
                v-model="form.id_piso_torre"
                :disabled="pisos.length === 0"
                class="form-input"
              >
                <option value="">Seleccione un piso</option>

                <option v-for="p in pisos" :key="p.id_piso_torre" :value="p.id_piso_torre">
                  Nivel {{ p.nivel }}
                </option>
              </select>

              <p v-if="errors.id_piso_torre" class="form-error">
                {{ errors.id_piso_torre }}
              </p>
            </div>

            <!-- Número -->
            <div>
              <label class="form-label"> Número </label>

              <input
                v-model="form.numero"
                type="text"
                maxlength="20"
                class="form-input"
                placeholder="Ej: 302"
              />

              <p v-if="errors.numero" class="form-error">
                {{ errors.numero }}
              </p>
            </div>

            <!-- Tipo -->
            <div>
              <label class="form-label"> Tipo de apartamento </label>

              <select v-model="form.id_tipo_apartamento" class="form-input">
                <option value="">Seleccione</option>

                <option
                  v-for="t in tiposFiltrados"
                  :key="t.id_tipo_apartamento"
                  :value="t.id_tipo_apartamento"
                >
                  {{ t.nombre }}
                  —
                  {{ formatCurrency(t.valor_estimado) }}
                </option>
              </select>

              <p v-if="errors.id_tipo_apartamento" class="form-error">
                {{ errors.id_tipo_apartamento }}
              </p>
            </div>

            <!-- Estado -->
            <div>
              <label class="form-label"> Estado del inmueble </label>

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
          </div>
        </AppCard>

        <!-- Cálculos -->
        <AppCard padding="md">
          <div>
            <h3 class="text-sm font-semibold text-gray-900">Cálculos</h3>

            <p class="text-xs text-gray-500 mt-1">
              Preview basado en la configuración seleccionada. El backend realiza el cálculo
              definitivo.
            </p>
          </div>

          <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Base -->
            <div class="rounded-2xl border border-gray-200 p-4">
              <p class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Valor base</p>

              <p class="mt-2 text-xl font-semibold text-gray-900">
                {{ formatCurrency(valorEstimadoTipo) }}
              </p>

              <p class="mt-1 text-xs text-gray-500">Valor estimado del tipo.</p>
            </div>

            <!-- Prima -->
            <div class="rounded-2xl border border-brand-200 bg-brand-50/50 p-4">
              <p class="text-xs font-semibold text-gray-600 uppercase tracking-wide">
                Prima altura
              </p>

              <p class="mt-2 text-xl font-semibold text-gray-900">
                {{ formatCurrency(primaAlturaCalculada) }}
              </p>

              <p class="mt-1 text-xs text-gray-500">Según tipo y nivel seleccionado.</p>
            </div>

            <!-- Base + prima -->
            <div class="rounded-2xl border border-gray-200 p-4">
              <p class="text-xs font-semibold text-gray-600 uppercase tracking-wide">
                Base + prima
              </p>

              <p class="mt-2 text-xl font-semibold text-gray-900">
                {{ formatCurrency(valorTotalCalculado) }}
              </p>

              <p class="mt-1 text-xs text-gray-500">
                La política de precio se aplica posteriormente en backend.
              </p>
            </div>
          </div>

          <div
            v-if="tipoSeleccionado"
            class="mt-4 rounded-xl border border-gray-200 bg-gray-50 p-4"
          >
            <p class="text-xs font-semibold text-gray-700 uppercase tracking-wide">
              Configuración de prima
            </p>

            <template v-if="tipoEsLegacy">
              <p class="mt-2 text-sm text-amber-700">
                Este tipo todavía utiliza la configuración heredada del Proyecto y Torre.
              </p>
            </template>

            <template v-else-if="tipoSeleccionado.prima_altura_activa">
              <div class="mt-2 grid grid-cols-1 sm:grid-cols-3 gap-3 text-sm">
                <div>
                  <span class="text-gray-500"> Inicio: </span>

                  <span class="font-semibold text-gray-900">
                    Nivel {{ tipoSeleccionado.nivel_inicio_prima }}
                  </span>
                </div>

                <div>
                  <span class="text-gray-500"> Base: </span>

                  <span class="font-semibold text-gray-900">
                    {{ formatCurrency(tipoSeleccionado.prima_altura_base) }}
                  </span>
                </div>

                <div>
                  <span class="text-gray-500"> Incremento: </span>

                  <span class="font-semibold text-gray-900">
                    {{ formatCurrency(tipoSeleccionado.prima_altura_incremento) }}
                  </span>
                </div>
              </div>
            </template>

            <template v-else>
              <p class="mt-2 text-sm text-gray-600">Este tipo no aplica prima de altura.</p>
            </template>
          </div>
        </AppCard>

        <!-- Actions -->
        <div class="flex items-center justify-end gap-2">
          <ButtonSecondary href="/apartamentos"> Cancelar </ButtonSecondary>

          <ButtonPrimary type="submit"> Actualizar </ButtonPrimary>
        </div>
      </form>

      <FlashMessages />
    </div>
  </TopBannerLayout>
</template>

<script setup>
import { computed, reactive, ref } from 'vue'

import { router } from '@inertiajs/vue3'

import TopBannerLayout from '@/Components/TopBannerLayout.vue'
import FlashMessages from '@/Components/FlashMessages.vue'
import AppCard from '@/Components/AppCard.vue'
import PageHeader from '@/Components/PageHeader.vue'
import ButtonPrimary from '@/Components/ButtonPrimary.vue'
import ButtonSecondary from '@/Components/ButtonSecondary.vue'

const props = defineProps({
  apartamento: {
    type: Object,
    required: true,
  },

  proyectos: {
    type: Array,
    default: () => [],
  },

  tipos: {
    type: Array,
    default: () => [],
  },

  estados: {
    type: Array,
    default: () => [],
  },

  torres: {
    type: Array,
    default: () => [],
  },

  pisos: {
    type: Array,
    default: () => [],
  },

  empleado: {
    type: Object,
    default: null,
  },
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

const torres = ref([...props.torres])

const pisos = ref([...props.pisos])

/* ============================================================
 * Tipos
 * ============================================================ */

const tiposFiltrados = computed(() => {
  return props.tipos.filter((tipo) => Number(tipo.id_proyecto) === Number(form.id_proyecto))
})

const tipoSeleccionado = computed(() => {
  return (
    props.tipos.find(
      (tipo) => Number(tipo.id_tipo_apartamento) === Number(form.id_tipo_apartamento)
    ) || null
  )
})

const tipoEsLegacy = computed(() => {
  if (!tipoSeleccionado.value) {
    return false
  }

  return (
    tipoSeleccionado.value.prima_altura_activa === null ||
    typeof tipoSeleccionado.value.prima_altura_activa === 'undefined'
  )
})

/* ============================================================
 * Piso / Torre
 * ============================================================ */

const pisoSeleccionado = computed(() => {
  return (
    pisos.value.find((piso) => Number(piso.id_piso_torre) === Number(form.id_piso_torre)) || null
  )
})

const torreSeleccionada = computed(() => {
  return torres.value.find((torre) => Number(torre.id_torre) === Number(form.id_torre)) || null
})

/* ============================================================
 * Proyecto
 * ============================================================ */

async function onProyectoChange() {
  form.id_torre = ''
  form.id_piso_torre = ''
  form.id_tipo_apartamento = ''

  torres.value = []
  pisos.value = []

  if (!form.id_proyecto) {
    return
  }

  try {
    const response = await fetch(`/api/torres-por-proyecto/${form.id_proyecto}`, {
      headers: {
        Accept: 'application/json',
      },
    })

    if (!response.ok) {
      throw new Error('Error cargando torres')
    }

    torres.value = await response.json()
  } catch (error) {
    console.error(error)

    errors.value.id_proyecto = 'No se pudieron cargar las torres del proyecto.'
  }
}

/* ============================================================
 * Torre
 * ============================================================ */

async function onTorreChange() {
  form.id_piso_torre = ''

  pisos.value = []

  if (!form.id_torre) {
    return
  }

  try {
    const response = await fetch(`/api/pisos-por-torre/${form.id_torre}`, {
      headers: {
        Accept: 'application/json',
      },
    })

    if (!response.ok) {
      throw new Error('Error cargando pisos')
    }

    pisos.value = await response.json()
  } catch (error) {
    console.error(error)

    errors.value.id_torre = 'No se pudieron cargar los pisos de la torre.'
  }
}

/* ============================================================
 * Base
 * ============================================================ */

const valorEstimadoTipo = computed(() => {
  return tipoSeleccionado.value ? Number(tipoSeleccionado.value.valor_estimado || 0) : 0
})

/* ============================================================
 * Prima
 * ============================================================ */

const primaAlturaCalculada = computed(() => {
  const tipo = tipoSeleccionado.value

  const piso = pisoSeleccionado.value

  if (!tipo || !piso) {
    return 0
  }

  const nivelActual = Number(piso.nivel || 0)

  /*
   * Nueva configuración.
   */
  if (!tipoEsLegacy.value) {
    const activa =
      tipo.prima_altura_activa === true ||
      tipo.prima_altura_activa === 1 ||
      tipo.prima_altura_activa === '1'

    if (!activa) {
      return 0
    }

    const nivelInicio = Number(tipo.nivel_inicio_prima || 0)

    if (nivelInicio <= 0 || nivelActual < nivelInicio) {
      return 0
    }

    const base = Number(tipo.prima_altura_base || 0)

    const incremento = Number(tipo.prima_altura_incremento || 0)

    return base + (nivelActual - nivelInicio) * incremento
  }

  /*
   * Compatibilidad legacy.
   */
  const torre = torreSeleccionada.value

  if (!torre || !torre.proyecto || !torre.proyecto.prima_altura_activa) {
    return 0
  }

  const nivelInicio = Number(torre.nivel_inicio_prima ?? 2)

  if (nivelActual < nivelInicio) {
    return 0
  }

  const base = Number(torre.proyecto.prima_altura_base || 0)

  const incremento = Number(torre.proyecto.prima_altura_incremento || 0)

  return base + (nivelActual - nivelInicio) * incremento
})

const valorTotalCalculado = computed(() => {
  return valorEstimadoTipo.value + primaAlturaCalculada.value
})

/* ============================================================
 * Guardar
 * ============================================================ */

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
      onError: (responseErrors) => {
        errors.value = responseErrors || {}
      },
    }
  )
}

/* ============================================================
 * Formato
 * ============================================================ */

function formatCurrency(value) {
  return Number(value || 0).toLocaleString('es-CO', {
    style: 'currency',
    currency: 'COP',
    maximumFractionDigits: 0,
  })
}
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
