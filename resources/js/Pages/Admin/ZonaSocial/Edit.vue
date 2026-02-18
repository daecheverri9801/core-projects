<!-- resources/js/Pages/Admin/ZonaSocial/Edit.vue -->
<template>
  <TopBannerLayout :empleado="empleado" panel-name="Proyectos">
    <div class="space-y-6">
      <PageHeader
        title="Editar zona social"
        kicker="Zonas sociales"
        subtitle="Actualiza la información de la zona social."
      >
        <template #actions>
          <div class="flex items-center gap-2">
            <Link
              :href="`/zonas-sociales/${zona.id_zona_social}`"
              class="rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-800 hover:bg-gray-50 transition"
            >
              Volver al detalle
            </Link>
            <Link
              href="/zonas-sociales"
              class="rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-800 hover:bg-gray-50 transition"
            >
              Listado
            </Link>
          </div>
        </template>
      </PageHeader>

      <AppCard padding="md" class="max-w-3xl">
        <form @submit.prevent="submit" class="space-y-6">
          <!-- Meta -->
          <div class="flex items-center justify-between">
            <div class="text-sm text-gray-600">
              ID:
              <span class="font-semibold text-gray-900">{{ zona.id_zona_social }}</span>
            </div>
            <span class="text-xs text-gray-500">Campos marcados con * son obligatorios</span>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <!-- Proyecto -->
            <div class="md:col-span-2">
              <label class="block text-sm font-semibold text-gray-900 mb-1">
                Proyecto <span class="text-red-500">*</span>
              </label>
              <select v-model="form.id_proyecto" class="field">
                <option value="">Seleccione un proyecto</option>
                <option v-for="p in proyectos" :key="p.id_proyecto" :value="p.id_proyecto">
                  {{ p.nombre }}
                </option>
              </select>
              <p v-if="errors.id_proyecto" class="mt-1 text-sm text-red-600">
                {{ errors.id_proyecto }}
              </p>
            </div>

            <!-- Nombre -->
            <div class="md:col-span-2">
              <label class="block text-sm font-semibold text-gray-900 mb-1">
                Nombre <span class="text-red-500">*</span>
              </label>
              <input
                v-model="form.nombre"
                type="text"
                maxlength="100"
                class="field"
                placeholder="Ej: Gimnasio, Piscina, Salón Social"
              />
              <p v-if="errors.nombre" class="mt-1 text-sm text-red-600">
                {{ errors.nombre }}
              </p>
            </div>

            <!-- Descripción -->
            <div class="md:col-span-2">
              <label class="block text-sm font-semibold text-gray-900 mb-1">
                Descripción <span class="text-gray-400 font-medium">(opcional)</span>
              </label>
              <textarea
                v-model="form.descripcion"
                rows="3"
                maxlength="100"
                class="field"
                placeholder="Breve descripción (máx. 100 caracteres)"
              />
              <div class="mt-1 flex items-center justify-between">
                <p v-if="errors.descripcion" class="text-sm text-red-600">
                  {{ errors.descripcion }}
                </p>
                <p class="text-xs text-gray-500 ml-auto">
                  {{ (form.descripcion || '').length }}/100
                </p>
              </div>
            </div>
          </div>

          <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-end">
            <Link
              href="/zonas-sociales"
              class="rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-800 hover:bg-gray-50 transition text-center"
            >
              Cancelar
            </Link>

            <button
              type="submit"
              class="rounded-xl bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-brand-700 transition disabled:opacity-60 disabled:cursor-not-allowed"
              :disabled="processing"
            >
              <span v-if="processing">Actualizando…</span>
              <span v-else>Actualizar</span>
            </button>
          </div>
        </form>
      </AppCard>

      <FlashMessages />
    </div>
  </TopBannerLayout>
</template>

<script setup>
import { reactive, ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'

import TopBannerLayout from '@/Components/TopBannerLayout.vue'
import FlashMessages from '@/Components/FlashMessages.vue'
import AppCard from '@/Components/AppCard.vue'
import PageHeader from '@/Components/PageHeader.vue'

const props = defineProps({
  zona: { type: Object, required: true },
  proyectos: { type: Array, default: () => [] },
  empleado: { type: Object, default: null },
})

const form = reactive({
  id_proyecto: props.zona.id_proyecto || '',
  nombre: props.zona.nombre || '',
  descripcion: props.zona.descripcion || '',
})

const errors = ref({})
const processing = ref(false)

function submit() {
  errors.value = {}
  processing.value = true

  router.put(
    `/zonas-sociales/${props.zona.id_zona_social}`,
    {
      id_proyecto: form.id_proyecto || '',
      nombre: form.nombre,
      descripcion: form.descripcion || '',
    },
    {
      onError: (e) => {
        errors.value = e || {}
      },
      onFinish: () => {
        processing.value = false
      },
    }
  )
}
</script>

<style scoped>
.field {
  @apply w-full rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900
    focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500;
}
</style>
