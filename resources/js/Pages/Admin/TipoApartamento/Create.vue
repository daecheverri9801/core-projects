<template>
  <SidebarBannerLayout :empleado="empleado">
    <template #title>Nuevos Tipos de Apartamento</template>

    <div class="space-y-6">
      <!-- Header -->
      <div class="bg-white rounded-2xl border p-4 md:p-6">
        <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
          <div>
            <p class="text-xs text-gray-600">Tipos de Apartamento</p>
            <h2 class="text-xl font-semibold text-gray-900">Crear en lote</h2>
            <p class="mt-1 text-sm text-gray-600">
              Selecciona el proyecto y agrega uno o varios tipos en una sola operación.
            </p>
          </div>

          <div class="flex items-center gap-2">
            <button type="button" class="btn-secondary" @click="addRow">
              + Agregar tipo
            </button>
            <button type="button" class="btn-secondary" @click="resetRows" :disabled="form.tipos.length === 1">
              Limpiar
            </button>
          </div>
        </div>

        <!-- Proyecto -->
        <div class="mt-5 grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="form-label">Proyecto *</label>
            <select v-model="form.id_proyecto" class="form-input">
              <option value="">Seleccione proyecto...</option>
              <option v-for="p in proyectos" :key="p.id_proyecto" :value="p.id_proyecto">
                {{ p.nombre }}
              </option>
            </select>
            <p v-if="form.errors.id_proyecto" class="form-error">
              {{ form.errors.id_proyecto }}
            </p>
          </div>

          <div class="rounded-xl bg-brand-50 border border-brand-100 p-4">
            <p class="text-xs text-gray-600">Total a crear</p>
            <p class="text-2xl font-bold text-gray-900">{{ form.tipos.length }}</p>
            <p class="text-xs text-gray-600 mt-1">Puedes agregar imagen por cada tipo.</p>
          </div>
        </div>
      </div>

      <!-- Lote -->
      <div class="bg-white rounded-2xl border overflow-hidden">
        <div class="px-4 md:px-6 py-4 border-b bg-gray-50 flex items-center justify-between">
          <h3 class="text-sm font-semibold text-gray-700">Detalle de tipos</h3>
          <p class="text-xs text-gray-500">Campos opcionales excepto Nombre</p>
        </div>

        <div class="divide-y">
          <div
            v-for="(t, idx) in form.tipos"
            :key="t._key"
            class="p-4 md:p-6"
          >
            <div class="flex items-start justify-between gap-3 mb-4">
              <div class="min-w-0">
                <p class="text-sm font-semibold text-gray-900">
                  Tipo #{{ idx + 1 }}
                </p>
                <p class="text-xs text-gray-500">
                  Completa los datos del tipo. El valor estimado se calcula automáticamente.
                </p>
              </div>

              <button
                type="button"
                class="icon-btn danger"
                title="Quitar"
                @click="removeRow(idx)"
                :disabled="form.tipos.length === 1"
              >
                ✕
              </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <!-- Nombre -->
              <div class="md:col-span-2">
                <label class="form-label">Nombre *</label>
                <input
                  v-model="t.nombre"
                  type="text"
                  maxlength="100"
                  class="form-input"
                  placeholder="Ej: Tipo A - 3H"
                />
                <p v-if="fieldError(idx, 'nombre')" class="form-error">
                  {{ fieldError(idx, 'nombre') }}
                </p>
              </div>

              <!-- Áreas -->
              <div>
                <label class="form-label">Área construida (m²)</label>
                <input
                  v-model.number="t.area_construida"
                  type="number"
                  step="0.01"
                  min="0"
                  class="form-input"
                />
                <p v-if="fieldError(idx, 'area_construida')" class="form-error">
                  {{ fieldError(idx, 'area_construida') }}
                </p>
              </div>

              <div>
                <label class="form-label">Área privada (m²)</label>
                <input
                  v-model.number="t.area_privada"
                  type="number"
                  step="0.01"
                  min="0"
                  class="form-input"
                />
                <p v-if="fieldError(idx, 'area_privada')" class="form-error">
                  {{ fieldError(idx, 'area_privada') }}
                </p>
              </div>

              <!-- Habitaciones / baños -->
              <div>
                <label class="form-label">Habitaciones</label>
                <input
                  v-model.number="t.cantidad_habitaciones"
                  type="number"
                  step="1"
                  min="0"
                  class="form-input"
                />
                <p v-if="fieldError(idx, 'cantidad_habitaciones')" class="form-error">
                  {{ fieldError(idx, 'cantidad_habitaciones') }}
                </p>
              </div>

              <div>
                <label class="form-label">Baños</label>
                <input
                  v-model.number="t.cantidad_banos"
                  type="number"
                  step="1"
                  min="0"
                  class="form-input"
                />
                <p v-if="fieldError(idx, 'cantidad_banos')" class="form-error">
                  {{ fieldError(idx, 'cantidad_banos') }}
                </p>
              </div>

              <!-- Valor m2 -->
              <div>
                <label class="form-label">Valor m² (COP)</label>
                <input
                  v-model.number="t.valor_m2"
                  type="number"
                  step="0.01"
                  min="0"
                  class="form-input"
                />
                <p v-if="fieldError(idx, 'valor_m2')" class="form-error">
                  {{ fieldError(idx, 'valor_m2') }}
                </p>
              </div>

              <!-- Imagen -->
              <div>
                <label class="form-label">Imagen (opcional)</label>
                <input
                  type="file"
                  accept="image/*"
                  class="form-input"
                  @change="onFileChange($event, idx)"
                />
                <p v-if="fieldError(idx, 'imagen')" class="form-error">
                  {{ fieldError(idx, 'imagen') }}
                </p>
                <p v-if="t._fileName" class="text-xs text-gray-500 mt-1">
                  Seleccionada: <span class="font-medium">{{ t._fileName }}</span>
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Footer acciones -->
        <div class="px-4 md:px-6 py-4 border-t bg-gray-50 flex flex-col md:flex-row gap-3 md:items-center md:justify-between">
          <p class="text-xs text-gray-600">
            Se crearán <span class="font-semibold text-gray-900">{{ form.tipos.length }}</span> tipo(s) en el proyecto seleccionado.
          </p>

          <div class="flex items-center gap-3">
            <button type="button" class="btn-secondary" @click="addRow">+ Agregar tipo</button>
            <button type="button" class="btn-secondary" @click="goBack">Cancelar</button>
            <button type="button" class="btn-primary" @click="submit" :disabled="form.processing">
              {{ form.processing ? 'Guardando...' : 'Guardar' }}
            </button>
          </div>
        </div>
      </div>

      <FlashMessages />
    </div>
  </SidebarBannerLayout>
</template>

<script setup>
import { useForm, router } from '@inertiajs/vue3'
import SidebarBannerLayout from '@/Components/SidebarBannerLayout.vue'
import FlashMessages from '@/Components/FlashMessages.vue'

const props = defineProps({
  empleado: { type: Object, default: null },
  proyectos: { type: Array, required: true },
})

function newRow() {
  return {
    _key: crypto.randomUUID ? crypto.randomUUID() : String(Date.now() + Math.random()),
    nombre: '',
    area_construida: null,
    area_privada: null,
    cantidad_habitaciones: null,
    cantidad_banos: null,
    valor_m2: null,
    imagen: null,
    _fileName: '',
  }
}

const form = useForm({
  id_proyecto: '',
  tipos: [newRow()],
})

function addRow() {
  form.tipos.push(newRow())
}

function removeRow(idx) {
  if (form.tipos.length <= 1) return
  form.tipos.splice(idx, 1)
}

function resetRows() {
  form.tipos = [newRow()]
}

function onFileChange(e, idx) {
  const file = e?.target?.files?.[0]
  form.tipos[idx].imagen = file || null
  form.tipos[idx]._fileName = file?.name || ''
}

function fieldError(idx, field) {
  // Errores vienen como: tipos.0.nombre, tipos.1.valor_m2, etc.
  return form.errors?.[`tipos.${idx}.${field}`]
}

function goBack() {
  router.visit('/tipos-apartamento')
}

function submit() {
  form.clearErrors()

  form.post('/tipos-apartamento', {
    forceFormData: true, // necesario por archivos
    onSuccess: () => {
      // opcional: reset
      // form.reset()
      // resetRows()
    },
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
  @apply inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-brand-600 text-white hover:bg-brand-700 disabled:opacity-50 transition;
}
.btn-secondary {
  @apply inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-gray-300 bg-white text-gray-800 hover:bg-gray-50 transition disabled:opacity-50;
}
.icon-btn {
  @apply inline-flex items-center justify-center w-9 h-9 rounded-xl border transition;
}
.icon-btn.danger {
  @apply border-red-200 text-red-600 hover:bg-red-50;
}
</style>
