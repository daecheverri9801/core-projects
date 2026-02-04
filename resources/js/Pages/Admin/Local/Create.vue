<!-- resources/js/Pages/Admin/Local/Create.vue -->
<template>
  <SidebarBannerLayout :empleado="empleado">
    <div class="space-y-6">
      <PageHeader
        title="Crear local"
        kicker="Locales"
        subtitle="Asocia el local a un proyecto/torre/piso y define sus datos comerciales."
      >
        <template #actions>
          <Link
            href="/locales"
            class="rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-800 hover:bg-gray-50 transition"
          >
            Volver
          </Link>
        </template>
      </PageHeader>

      <AppCard padding="md">
        <form @submit.prevent="submit" class="space-y-6">
          <!-- Selección jerárquica -->
          <div>
            <h3 class="text-sm font-semibold text-gray-900">Ubicación</h3>
            <p class="mt-1 text-sm text-gray-600">Selecciona primero el proyecto, luego la torre y el piso.</p>

            <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
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
                  placeholder="Ej: L-05"
                />
                <p v-if="errors.numero" class="form-error">{{ errors.numero }}</p>
              </div>
            </div>
          </div>

          <div class="border-t border-gray-200 pt-6">
            <h3 class="text-sm font-semibold text-gray-900">Datos comerciales</h3>
            <p class="mt-1 text-sm text-gray-600">Define el estado y valores del local. El total se calcula automáticamente.</p>

            <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
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
                <p v-if="errors.id_estado_inmueble" class="form-error">{{ errors.id_estado_inmueble }}</p>
              </div>

              <div class="md:col-span-1">
                <label class="form-label">Área total (m²)</label>
                <input
                  v-model.number="form.area_total_local"
                  type="number"
                  step="0.01"
                  min="0"
                  class="form-input"
                  placeholder="Ej: 42.50"
                />
                <p v-if="errors.area_total_local" class="form-error">{{ errors.area_total_local }}</p>
              </div>

              <div>
                <label class="form-label">Valor m² (COP)</label>
                <input
                  v-model.number="form.valor_m2"
                  type="number"
                  step="0.01"
                  min="0"
                  class="form-input"
                  placeholder="Ej: 5500000"
                />
                <p v-if="errors.valor_m2" class="form-error">{{ errors.valor_m2 }}</p>
              </div>

              <!-- Total (read-only) -->
              <div class="md:col-span-2">
                <div class="rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3">
                  <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-sm font-medium text-gray-700">Valor total (calculado)</p>
                    <p class="text-lg font-semibold text-gray-900">{{ displayValorTotal }}</p>
                  </div>
                  <p class="mt-1 text-xs text-gray-500">Cálculo: Área total × Valor m².</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Acciones -->
          <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-end pt-2">
            <Link href="/locales" class="btn-secondary">Cancelar</Link>
            <button type="submit" class="btn-primary">Guardar</button>
          </div>
        </form>
      </AppCard>

      <FlashMessages />
    </div>
  </SidebarBannerLayout>
</template>

<script setup>
import { reactive, ref, computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import SidebarBannerLayout from '@/Components/SidebarBannerLayout.vue'
import FlashMessages from '@/Components/FlashMessages.vue'

import PageHeader from '@/Components/PageHeader.vue'
import AppCard from '@/Components/AppCard.vue'

const props = defineProps({
  proyectos: { type: Array, default: () => [] },
  estados: { type: Array, default: () => [] },
  empleado: { type: Object, default: null },
})

const form = reactive({
  id_proyecto: '',
  id_torre: '',
  id_piso_torre: '',
  numero: '',
  id_estado_inmueble: '',
  area_total_local: '',
  valor_m2: '',
})

const errors = ref({})
const torres = ref([])
const pisos = ref([])

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

const displayValorTotal = computed(() => {
  const area = Number(form.area_total_local)
  const v2 = Number(form.valor_m2)
  if (isNaN(area) || isNaN(v2) || form.area_total_local === '' || form.valor_m2 === '') return '—'
  const total = area * v2
  return new Intl.NumberFormat('es-CO', {
    style: 'currency',
    currency: 'COP',
    maximumFractionDigits: 0,
  }).format(total)
})

function submit() {
  errors.value = {}
  router.post(
    '/locales',
    {
      numero: form.numero,
      id_estado_inmueble: form.id_estado_inmueble,
      area_total_local: form.area_total_local || null,
      id_torre: form.id_torre,
      id_piso_torre: form.id_piso_torre,
      valor_m2: form.valor_m2 || null,
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
  @apply w-full rounded-xl border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500;
}
.form-error {
  @apply text-sm text-red-600 mt-1;
}
.btn-primary {
  @apply inline-flex items-center justify-center rounded-xl bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700 transition;
}
.btn-secondary {
  @apply inline-flex items-center justify-center rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-800 hover:bg-gray-50 transition;
}
</style>
