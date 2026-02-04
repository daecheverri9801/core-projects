<!-- resources/js/Pages/Admin/Torres/Edit.vue -->
<template>
  <SidebarBannerLayout :empleado="empleado">
    <div class="space-y-6">
      <PageHeader
        title="Editar torre"
        kicker="Torres"
        :subtitle="`Actualiza la información de la torre #${torre?.id_torre ?? '—'}.`"
      >
        <template #actions>
          <div class="flex items-center gap-2">
            <Link
              :href="route('admin.torres.show', torre.id_torre)"
              class="rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-800 hover:bg-gray-50 transition"
            >
              Volver
            </Link>

            <button
              type="button"
              @click="submit"
              :disabled="form.processing || !canSubmit"
              class="rounded-xl bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700 transition disabled:opacity-60"
            >
              {{ form.processing ? 'Guardando…' : 'Guardar cambios' }}
            </button>
          </div>
        </template>
      </PageHeader>

      <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Form -->
        <div class="lg:col-span-8 space-y-6">
          <AppCard padding="md">
            <SectionHeader
              title="Datos de la torre"
              subtitle="Edita los campos necesarios. Los cambios se aplican de inmediato al guardar."
              icon="BuildingOffice2Icon"
            />

            <form @submit.prevent="submit" class="mt-5 space-y-6">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <FormField
                  class="md:col-span-2"
                  label="Nombre de la torre"
                  required
                  :error="form.errors.nombre_torre"
                >
                  <TextInput
                    id="nombre_torre"
                    v-model="form.nombre_torre"
                    type="text"
                    placeholder="Ej: Torre 1"
                    autocomplete="off"
                  />
                </FormField>

                <FormField label="Número de pisos" :error="form.errors.numero_pisos" hint="Entero (mín. 1)">
                  <TextInput
                    id="numero_pisos"
                    v-model.number="form.numero_pisos"
                    type="number"
                    min="1"
                    max="32767"
                    placeholder="Ej: 20"
                  />
                </FormField>

                <FormField
                  label="Nivel inicio prima altura"
                  required
                  :error="form.errors.nivel_inicio_prima"
                  hint="Desde qué piso se aplica la prima"
                >
                  <TextInput
                    id="nivel_inicio_prima"
                    v-model.number="form.nivel_inicio_prima"
                    type="number"
                    min="1"
                    placeholder="2"
                  />
                </FormField>

                <FormField label="Proyecto" required :error="form.errors.id_proyecto">
                  <SelectInput id="id_proyecto" v-model="form.id_proyecto">
                    <option value="" disabled>Seleccione un proyecto</option>
                    <option v-for="p in proyectos" :key="p.id_proyecto" :value="p.id_proyecto">
                      {{ p.nombre }}
                    </option>
                  </SelectInput>
                </FormField>

                <FormField label="Estado" required :error="form.errors.id_estado">
                  <SelectInput id="id_estado" v-model="form.id_estado">
                    <option value="" disabled>Seleccione un estado</option>
                    <option v-for="e in estados" :key="e.id_estado" :value="e.id_estado">
                      {{ e.nombre }}
                    </option>
                  </SelectInput>
                </FormField>
              </div>

              <!-- desktop submit (duplicado por UX) -->
              <div class="hidden lg:flex items-center justify-end gap-2 pt-2">
                <Link
                  :href="route('admin.torres.show', torre.id_torre)"
                  class="rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-800 hover:bg-gray-50 transition"
                >
                  Cancelar
                </Link>
                <button
                  type="submit"
                  :disabled="form.processing || !canSubmit"
                  class="rounded-xl bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700 transition disabled:opacity-60"
                >
                  {{ form.processing ? 'Guardando…' : 'Guardar cambios' }}
                </button>
              </div>
            </form>
          </AppCard>
        </div>

        <!-- Aside -->
        <div class="lg:col-span-4 space-y-6">
          <AppCard padding="md">
            <div class="flex items-start gap-3">
              <span class="mt-0.5 rounded-2xl border border-brand-300/60 bg-brand-200 p-2">
                <IdentificationIcon class="w-5 h-5 text-brand-900" />
              </span>
              <div class="min-w-0">
                <p class="text-sm font-semibold text-gray-900">Información</p>
                <div class="mt-3 space-y-2 text-sm">
                  <p class="text-gray-700">
                    <span class="text-gray-500">ID:</span>
                    <span class="font-semibold text-gray-900">{{ torre.id_torre }}</span>
                  </p>
                  <p class="text-gray-700">
                    <span class="text-gray-500">Proyecto actual:</span>
                    <span class="font-semibold text-gray-900">{{ torre.proyecto?.nombre || '—' }}</span>
                  </p>
                  <p class="text-gray-700">
                    <span class="text-gray-500">Estado actual:</span>
                    <span class="font-semibold text-gray-900">{{ torre.estado?.nombre || '—' }}</span>
                  </p>
                </div>
              </div>
            </div>
          </AppCard>

          <AppCard padding="md">
            <p class="text-sm font-semibold text-gray-900">Validación</p>
            <div class="mt-3 space-y-2 text-sm">
              <InlineStatus :ok="!!String(form.nombre_torre || '').trim()" label="Nombre definido" />
              <InlineStatus :ok="Number(form.nivel_inicio_prima) >= 1" label="Nivel prima válido" />
              <InlineStatus :ok="!!form.id_proyecto" label="Proyecto seleccionado" />
              <InlineStatus :ok="!!form.id_estado" label="Estado seleccionado" />
              <InlineStatus :ok="canSubmit" label="Listo para guardar" />
            </div>
          </AppCard>
        </div>

        <!-- Mobile actions -->
        <div class="lg:hidden">
          <div class="flex flex-col gap-3">
            <button
              type="button"
              @click="submit"
              :disabled="form.processing || !canSubmit"
              class="w-full rounded-xl bg-brand-600 px-4 py-3 text-sm font-semibold text-white hover:bg-brand-700 transition disabled:opacity-60"
            >
              {{ form.processing ? 'Guardando…' : 'Guardar cambios' }}
            </button>

            <Link
              :href="route('admin.torres.show', torre.id_torre)"
              class="w-full text-center rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm font-semibold text-gray-800 hover:bg-gray-50 transition"
            >
              Cancelar
            </Link>
          </div>
        </div>
      </div>
    </div>
  </SidebarBannerLayout>
</template>

<script setup>
import { computed } from 'vue'
import { useForm, Link } from '@inertiajs/vue3'

import SidebarBannerLayout from '@/Components/SidebarBannerLayout.vue'
import AppCard from '@/Components/AppCard.vue'
import PageHeader from '@/Components/PageHeader.vue'
import SectionHeader from '@/Components/SectionHeader.vue'

import FormField from '@/Components/FormField.vue'
import TextInput from '@/Components/TextInput.vue'
import SelectInput from '@/Components/SelectInput.vue'
import InlineStatus from '@/Components/InlineStatus.vue'

import { BuildingOffice2Icon, IdentificationIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  torre: { type: Object, required: true },
  proyectos: { type: Array, default: () => [] },
  estados: { type: Array, default: () => [] },
  empleado: { type: Object, default: null },
})

const form = useForm({
  nombre_torre: props.torre?.nombre_torre || '',
  numero_pisos: props.torre?.numero_pisos ?? null,
  nivel_inicio_prima: props.torre?.nivel_inicio_prima ?? 2,
  id_proyecto: props.torre?.id_proyecto || '',
  id_estado: props.torre?.id_estado || '',
})

const canSubmit = computed(() => {
  const nombreOk = String(form.nombre_torre || '').trim().length > 0
  const nivelOk = Number(form.nivel_inicio_prima) >= 1
  const proyectoOk = !!form.id_proyecto
  const estadoOk = !!form.id_estado
  return nombreOk && nivelOk && proyectoOk && estadoOk
})

function submit() {
  form.put(route('admin.torres.update', props.torre.id_torre), { preserveScroll: true })
}
</script>
