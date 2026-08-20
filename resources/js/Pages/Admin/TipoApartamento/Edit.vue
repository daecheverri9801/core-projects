<template>
  <TopBannerLayout :empleado="empleado">
    <template #title> Editar Tipo de Apartamento </template>

    <div class="space-y-6">
      <!-- Header -->
      <div class="bg-white rounded-2xl border p-4 md:p-6 max-w-4xl mx-auto">
        <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
          <div>
            <p class="text-xs text-gray-600">Tipos de Apartamento</p>

            <h2 class="text-xl font-semibold text-gray-900">Editar</h2>

            <p class="mt-1 text-sm text-gray-600">
              Actualiza la información comercial y la configuración de prima de altura.
            </p>
          </div>

          <Link href="/tipos-apartamento" class="btn-secondary"> Volver </Link>
        </div>
      </div>

      <!-- Form -->
      <div class="bg-white rounded-2xl border p-4 md:p-6 max-w-4xl mx-auto">
        <form @submit.prevent="submit" class="space-y-6">
          <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Left -->
            <div class="lg:col-span-7 space-y-6">
              <!-- Información base -->
              <div class="rounded-2xl border border-gray-200 p-4">
                <h3 class="text-sm font-semibold text-gray-900">Información base</h3>

                <p class="text-xs text-gray-600 mt-1">Datos principales del tipo de apartamento.</p>

                <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div class="md:col-span-2">
                    <label class="form-label"> Proyecto </label>

                    <select v-model="form.id_proyecto" class="form-input">
                      <option v-for="p in proyectos" :key="p.id_proyecto" :value="p.id_proyecto">
                        {{ p.nombre }}
                      </option>
                    </select>

                    <p v-if="errors.id_proyecto" class="form-error">
                      {{ errors.id_proyecto }}
                    </p>
                  </div>

                  <div class="md:col-span-2">
                    <label class="form-label"> Nombre </label>

                    <input
                      v-model="form.nombre"
                      type="text"
                      maxlength="100"
                      class="form-input"
                      placeholder="Ej: Tipo A - 3H"
                    />

                    <p v-if="errors.nombre" class="form-error">
                      {{ errors.nombre }}
                    </p>
                  </div>
                </div>
              </div>

              <!-- Características -->
              <div class="rounded-2xl border border-gray-200 p-4">
                <h3 class="text-sm font-semibold text-gray-900">Características</h3>

                <p class="text-xs text-gray-600 mt-1">Áreas, habitaciones, baños y valor por m².</p>

                <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                    <label class="form-label"> Área construida (m²) </label>

                    <input
                      v-model.number="form.area_construida"
                      type="number"
                      step="0.01"
                      min="0"
                      class="form-input"
                    />

                    <p v-if="errors.area_construida" class="form-error">
                      {{ errors.area_construida }}
                    </p>
                  </div>

                  <div>
                    <label class="form-label"> Área privada (m²) </label>

                    <input
                      v-model.number="form.area_privada"
                      type="number"
                      step="0.01"
                      min="0"
                      class="form-input"
                    />

                    <p v-if="errors.area_privada" class="form-error">
                      {{ errors.area_privada }}
                    </p>
                  </div>

                  <div>
                    <label class="form-label"> Habitaciones </label>

                    <input
                      v-model.number="form.cantidad_habitaciones"
                      type="number"
                      step="1"
                      min="0"
                      class="form-input"
                    />

                    <p v-if="errors.cantidad_habitaciones" class="form-error">
                      {{ errors.cantidad_habitaciones }}
                    </p>
                  </div>

                  <div>
                    <label class="form-label"> Baños </label>

                    <input
                      v-model.number="form.cantidad_banos"
                      type="number"
                      step="1"
                      min="0"
                      class="form-input"
                    />

                    <p v-if="errors.cantidad_banos" class="form-error">
                      {{ errors.cantidad_banos }}
                    </p>
                  </div>

                  <div class="md:col-span-2">
                    <label class="form-label"> Valor m² (COP) </label>

                    <input
                      v-model.number="form.valor_m2"
                      type="number"
                      step="0.01"
                      min="0"
                      class="form-input"
                    />

                    <p v-if="errors.valor_m2" class="form-error">
                      {{ errors.valor_m2 }}
                    </p>
                  </div>
                </div>
              </div>

              <!-- Prima -->
              <div class="rounded-2xl border border-brand-200 bg-brand-50/50 p-4">
                <h3 class="text-sm font-semibold text-gray-900">Prima de altura</h3>

                <p class="mt-1 text-xs text-gray-600">
                  Esta configuración aplica exclusivamente a este tipo de apartamento.
                </p>

                <!-- Legacy -->
                <div
                  v-if="esConfiguracionLegacy"
                  class="mt-4 rounded-xl border border-amber-200 bg-amber-50 p-4"
                >
                  <p class="text-sm font-semibold text-amber-900">Configuración heredada</p>

                  <p class="mt-1 text-xs text-amber-800">
                    Este tipo todavía utiliza temporalmente la prima configurada en Proyecto y
                    Torre.
                  </p>

                  <div class="mt-3 flex flex-wrap gap-2">
                    <button
                      type="button"
                      class="rounded-xl bg-brand-600 px-3 py-2 text-xs font-semibold text-white hover:bg-brand-700"
                      @click="iniciarConfiguracionPrima"
                    >
                      Configurar prima para este tipo
                    </button>

                    <button
                      type="button"
                      class="rounded-xl border border-gray-300 bg-white px-3 py-2 text-xs font-semibold text-gray-800 hover:bg-gray-50"
                      @click="desactivarPrimaTipo"
                    >
                      Este tipo no aplica prima
                    </button>
                  </div>
                </div>

                <!-- Nueva configuración -->
                <template v-else>
                  <label class="mt-4 inline-flex items-center gap-3 cursor-pointer">
                    <input
                      v-model="form.prima_altura_activa"
                      type="checkbox"
                      class="h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500"
                    />

                    <span class="text-sm font-semibold text-gray-800">
                      Aplicar prima de altura
                    </span>
                  </label>

                  <div
                    v-if="form.prima_altura_activa"
                    class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4"
                  >
                    <div>
                      <label class="form-label"> Nivel inicial </label>

                      <input
                        v-model.number="form.nivel_inicio_prima"
                        type="number"
                        min="1"
                        step="1"
                        class="form-input"
                      />

                      <p v-if="errors.nivel_inicio_prima" class="form-error">
                        {{ errors.nivel_inicio_prima }}
                      </p>
                    </div>

                    <div>
                      <label class="form-label"> Prima base </label>

                      <input
                        v-model.number="form.prima_altura_base"
                        type="number"
                        min="0"
                        step="0.01"
                        class="form-input"
                      />

                      <p v-if="errors.prima_altura_base" class="form-error">
                        {{ errors.prima_altura_base }}
                      </p>
                    </div>

                    <div>
                      <label class="form-label"> Incremento por nivel </label>

                      <input
                        v-model.number="form.prima_altura_incremento"
                        type="number"
                        min="0"
                        step="0.01"
                        class="form-input"
                      />

                      <p v-if="errors.prima_altura_incremento" class="form-error">
                        {{ errors.prima_altura_incremento }}
                      </p>
                    </div>
                  </div>

                  <div
                    v-if="form.prima_altura_activa"
                    class="mt-4 rounded-xl border border-brand-200 bg-white p-3"
                  >
                    <p class="text-xs font-semibold text-gray-700 uppercase">Ejemplo</p>

                    <p class="mt-1 text-sm text-gray-700">
                      {{ primaPreview }}
                    </p>
                  </div>

                  <p v-else class="mt-3 text-xs text-gray-600">
                    Los apartamentos asociados a este tipo tendrán prima de altura $0.
                  </p>
                </template>
              </div>
            </div>

            <!-- Right -->
            <div class="lg:col-span-5 space-y-6">
              <!-- Imagen -->
              <div class="rounded-2xl border border-gray-200 p-4">
                <h3 class="text-sm font-semibold text-gray-900">Imagen</h3>

                <p class="text-xs text-gray-600 mt-1">JPG, PNG o WEBP. Máximo 2MB.</p>

                <div class="mt-4">
                  <label class="form-label"> Seleccionar nueva imagen </label>

                  <input
                    type="file"
                    @change="handleImageChange"
                    class="form-input"
                    accept="image/jpeg,image/png,image/webp"
                  />

                  <p v-if="errors.imagen" class="form-error">
                    {{ errors.imagen }}
                  </p>
                </div>

                <div class="mt-5">
                  <label class="form-label"> Imagen actual </label>

                  <div
                    v-if="tipo.imagen"
                    class="mt-2 overflow-hidden rounded-2xl border bg-gray-50"
                  >
                    <div class="aspect-[4/3] w-full bg-gray-100">
                      <img
                        :src="`/storage/${tipo.imagen}`"
                        class="h-full w-full object-cover"
                        alt="Imagen actual del tipo"
                      />
                    </div>
                  </div>

                  <div v-else class="mt-2 rounded-2xl border border-dashed p-6 text-center">
                    <p class="text-sm font-medium text-gray-900">Sin imagen</p>
                  </div>
                </div>
              </div>

              <!-- ID -->
              <div class="rounded-2xl border border-brand-100 bg-brand-50 p-4">
                <p class="text-xs text-gray-600">ID del tipo</p>

                <p class="text-lg font-semibold text-gray-900">
                  {{ tipo.id_tipo_apartamento }}
                </p>
              </div>
            </div>
          </div>

          <!-- Actions -->
          <div
            class="flex flex-col-reverse gap-3 md:flex-row md:items-center md:justify-between pt-2"
          >
            <Link href="/tipos-apartamento" class="btn-secondary"> Cancelar </Link>

            <button type="submit" class="btn-primary" :disabled="form.processing">
              {{ form.processing ? 'Actualizando...' : 'Guardar cambios' }}
            </button>
          </div>
        </form>
      </div>

      <div class="max-w-4xl mx-auto">
        <FlashMessages />
      </div>
    </div>
  </TopBannerLayout>
</template>

<script setup>
import { computed, ref } from 'vue'

import { Link, useForm } from '@inertiajs/vue3'

import TopBannerLayout from '@/Components/TopBannerLayout.vue'
import FlashMessages from '@/Components/FlashMessages.vue'

const props = defineProps({
  proyectos: {
    type: Array,
    required: true,
  },

  tipo: {
    type: Object,
    required: true,
  },

  empleado: {
    type: Object,
    default: null,
  },
})

const form = useForm({
  id_proyecto: props.tipo.id_proyecto || '',

  nombre: props.tipo.nombre || '',

  area_construida: props.tipo.area_construida ?? '',

  area_privada: props.tipo.area_privada ?? '',

  cantidad_habitaciones: props.tipo.cantidad_habitaciones ?? '',

  cantidad_banos: props.tipo.cantidad_banos ?? '',

  valor_m2: props.tipo.valor_m2 ?? '',

  imagen: null,

  /*
   * Importante:
   * NULL se conserva para tipos legacy.
   */
  prima_altura_activa: props.tipo.prima_altura_activa,

  nivel_inicio_prima: props.tipo.nivel_inicio_prima ?? null,

  prima_altura_base: props.tipo.prima_altura_base ?? null,

  prima_altura_incremento: props.tipo.prima_altura_incremento ?? null,
})

const errors = ref({})

const esConfiguracionLegacy = computed(() => {
  return form.prima_altura_activa === null || typeof form.prima_altura_activa === 'undefined'
})

const primaPreview = computed(() => {
  if (!form.prima_altura_activa) {
    return 'Sin prima'
  }

  const nivel = Number(form.nivel_inicio_prima || 0)

  const base = Number(form.prima_altura_base || 0)

  const incremento = Number(form.prima_altura_incremento || 0)

  if (!nivel) {
    return 'Completa la configuración.'
  }

  return (
    `Nivel ${nivel}: ${formatCurrency(base)} · ` +
    `Nivel ${nivel + 1}: ${formatCurrency(base + incremento)}`
  )
})

function iniciarConfiguracionPrima() {
  form.prima_altura_activa = true
}

function desactivarPrimaTipo() {
  form.prima_altura_activa = false
}

function handleImageChange(event) {
  form.imagen = event.target.files?.[0] || null
}

function submit() {
  errors.value = {}

  form
    .transform((data) => ({
      ...data,
      _method: 'PUT',
    }))
    .post(`/tipos-apartamento/${props.tipo.id_tipo_apartamento}`, {
      forceFormData: true,

      onError: (e) => {
        errors.value = e || {}
      },
    })
}

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
  @apply block text-sm font-medium text-gray-700 mb-1;
}

.form-input {
  @apply w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500;
}

.form-error {
  @apply text-sm text-red-600 mt-1;
}

.btn-primary {
  @apply inline-flex items-center justify-center gap-2 px-4 py-2 rounded-xl bg-brand-600 text-white hover:bg-brand-700 disabled:opacity-50 transition;
}

.btn-secondary {
  @apply inline-flex items-center justify-center gap-2 px-4 py-2 rounded-xl border border-gray-300 bg-white text-gray-800 hover:bg-gray-50 transition;
}
</style>
