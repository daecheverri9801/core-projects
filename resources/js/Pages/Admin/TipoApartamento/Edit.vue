<template>
  <SidebarBannerLayout :empleado="empleado">
    <template #title>Editar Tipo de Apartamento</template>

    <div class="space-y-6">
      <!-- Header -->
      <div class="bg-white rounded-2xl border p-4 md:p-6 max-w-4xl mx-auto">
        <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
          <div>
            <p class="text-xs text-gray-600">Tipos de Apartamento</p>
            <h2 class="text-xl font-semibold text-gray-900">Editar</h2>
            <p class="mt-1 text-sm text-gray-600">
              Actualiza la información del tipo y, si lo necesitas, reemplaza la imagen.
            </p>
          </div>

          <div class="flex items-center gap-2">
            <Link href="/tipos-apartamento" class="btn-secondary">Volver</Link>
          </div>
        </div>
      </div>

      <!-- Form -->
      <div class="bg-white rounded-2xl border p-4 md:p-6 max-w-4xl mx-auto">
        <form @submit.prevent="submit" class="space-y-6">
          <!-- Grid -->
          <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Left: campos -->
            <div class="lg:col-span-7 space-y-6">
              <div class="rounded-2xl border border-gray-200 p-4">
                <h3 class="text-sm font-semibold text-gray-900">Información base</h3>
                <p class="text-xs text-gray-600 mt-1">
                  Datos principales del tipo de apartamento.
                </p>

                <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div class="md:col-span-2">
                    <label class="form-label">Proyecto</label>
                    <select v-model="form.id_proyecto" class="form-input">
                      <option v-for="p in proyectos" :key="p.id_proyecto" :value="p.id_proyecto">
                        {{ p.nombre }}
                      </option>
                    </select>
                  </div>

                  <div class="md:col-span-2">
                    <label class="form-label">Nombre</label>
                    <input
                      v-model="form.nombre"
                      type="text"
                      maxlength="100"
                      class="form-input"
                      placeholder="Ej: Tipo A - 3H"
                    />
                    <p v-if="errors.nombre" class="form-error">{{ errors.nombre }}</p>
                  </div>
                </div>
              </div>

              <div class="rounded-2xl border border-gray-200 p-4">
                <h3 class="text-sm font-semibold text-gray-900">Características</h3>
                <p class="text-xs text-gray-600 mt-1">
                  Áreas, habitaciones, baños y valor por m².
                </p>

                <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                    <label class="form-label">Área construida (m²)</label>
                    <input
                      v-model.number="form.area_construida"
                      type="number"
                      step="0.01"
                      min="0"
                      class="form-input"
                      placeholder="0.00"
                    />
                    <p v-if="errors.area_construida" class="form-error">{{ errors.area_construida }}</p>
                  </div>

                  <div>
                    <label class="form-label">Área privada (m²)</label>
                    <input
                      v-model.number="form.area_privada"
                      type="number"
                      step="0.01"
                      min="0"
                      class="form-input"
                      placeholder="0.00"
                    />
                    <p v-if="errors.area_privada" class="form-error">{{ errors.area_privada }}</p>
                  </div>

                  <div>
                    <label class="form-label">Habitaciones</label>
                    <input
                      v-model.number="form.cantidad_habitaciones"
                      type="number"
                      step="1"
                      min="0"
                      class="form-input"
                      placeholder="0"
                    />
                    <p v-if="errors.cantidad_habitaciones" class="form-error">
                      {{ errors.cantidad_habitaciones }}
                    </p>
                  </div>

                  <div>
                    <label class="form-label">Baños</label>
                    <input
                      v-model.number="form.cantidad_banos"
                      type="number"
                      step="1"
                      min="0"
                      class="form-input"
                      placeholder="0"
                    />
                    <p v-if="errors.cantidad_banos" class="form-error">{{ errors.cantidad_banos }}</p>
                  </div>

                  <div class="md:col-span-2">
                    <label class="form-label">Valor m² (COP)</label>
                    <input
                      v-model.number="form.valor_m2"
                      type="number"
                      step="0.01"
                      min="0"
                      class="form-input"
                      placeholder="0"
                    />
                    <p v-if="errors.valor_m2" class="form-error">{{ errors.valor_m2 }}</p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Right: imagen -->
            <div class="lg:col-span-5 space-y-6">
              <div class="rounded-2xl border border-gray-200 p-4">
                <h3 class="text-sm font-semibold text-gray-900">Imagen</h3>
                <p class="text-xs text-gray-600 mt-1">
                  Formatos: JPG, PNG, WEBP. Máximo 2MB.
                </p>

                <div class="mt-4">
                  <label class="form-label">Seleccionar nueva imagen</label>
                  <input
                    type="file"
                    @change="handleImageChange"
                    class="form-input"
                    accept="image/jpeg,image/png,image/webp"
                  />
                  <p v-if="errors.imagen" class="form-error">{{ errors.imagen }}</p>
                </div>

                <div class="mt-5">
                  <label class="form-label">Imagen actual</label>

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
                    <div class="p-3">
                      <p class="text-xs text-gray-600">
                        Esta es la imagen actual. Selecciona una nueva solo si deseas cambiarla.
                      </p>
                    </div>
                  </div>

                  <div v-else class="mt-2 rounded-2xl border border-dashed p-6 text-center">
                    <p class="text-sm font-medium text-gray-900">Sin imagen</p>
                    <p class="text-xs text-gray-600 mt-1">Puedes cargar una imagen opcional.</p>
                  </div>
                </div>
              </div>

              <div class="rounded-2xl border border-brand-100 bg-brand-50 p-4">
                <p class="text-xs text-gray-600">ID del tipo</p>
                <p class="text-lg font-semibold text-gray-900">
                  {{ tipo.id_tipo_apartamento }}
                </p>
              </div>
            </div>
          </div>

          <!-- Actions -->
          <div class="flex flex-col-reverse gap-3 md:flex-row md:items-center md:justify-between pt-2">
            <Link href="/tipos-apartamento" class="btn-secondary">Cancelar</Link>

            <button type="submit" class="btn-primary" :disabled="form.processing">
              <span v-if="form.processing">Actualizando...</span>
              <span v-else>Guardar cambios</span>
            </button>
          </div>
        </form>
      </div>

      <div class="max-w-4xl mx-auto">
        <FlashMessages />
      </div>
    </div>
  </SidebarBannerLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import SidebarBannerLayout from '@/Components/SidebarBannerLayout.vue'
import FlashMessages from '@/Components/FlashMessages.vue'

const props = defineProps({
  proyectos: { type: Array, required: true },
  tipo: { type: Object, required: true },
  empleado: { type: Object, default: null },
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
})

function handleImageChange(event) {
  form.imagen = event.target.files[0] || null
}

const errors = ref({})

function submit() {
  form
    .transform((data) => ({
      ...data,
      _method: 'PUT',
    }))
    .post(`/tipos-apartamento/${props.tipo.id_tipo_apartamento}`, {
      onError: (e) => {
        errors.value = e || {}
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
  @apply inline-flex items-center justify-center gap-2 px-4 py-2 rounded-xl bg-brand-600 text-white hover:bg-brand-700 disabled:opacity-50 transition;
}
.btn-secondary {
  @apply inline-flex items-center justify-center gap-2 px-4 py-2 rounded-xl border border-gray-300 bg-white text-gray-800 hover:bg-gray-50 transition;
}
</style>
