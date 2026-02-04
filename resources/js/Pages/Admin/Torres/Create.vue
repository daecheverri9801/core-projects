<!-- resources/js/Pages/Admin/Torres/Create.vue -->
<template>
  <SidebarBannerLayout :empleado="empleado">
    <div class="space-y-6">
      <PageHeader
        title="Crear torres"
        kicker="Torres"
        subtitle="Crea una o varias torres en una sola operación."
      >
        <template #actions>
          <div class="flex items-center gap-2">
            <Link
              :href="route('admin.torres.index')"
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
              {{ form.processing ? 'Guardando…' : 'Guardar' }}
            </button>
          </div>
        </template>
      </PageHeader>

      <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Form -->
        <div class="lg:col-span-8 space-y-6">
          <!-- Config general -->
          <AppCard padding="md">
            <SectionHeader
              title="Configuración general"
              subtitle="Estos datos aplican para todas las torres que agregues."
              icon="BuildingOffice2Icon"
            />

            <div class="mt-5 grid grid-cols-1 md:grid-cols-2 gap-4">
              <!-- Proyecto -->
              <FormField
                class="md:col-span-2"
                label="Proyecto"
                required
                :error="form.errors.id_proyecto"
              >
                <SelectInput v-model="form.id_proyecto">
                  <option value="" disabled>Seleccione un proyecto</option>
                  <option v-for="p in proyectos" :key="p.id_proyecto" :value="p.id_proyecto">
                    {{ p.nombre }}
                  </option>
                </SelectInput>
              </FormField>

              <!-- Estado -->
              <FormField label="Estado" required :error="form.errors.id_estado">
                <SelectInput v-model="form.id_estado">
                  <option value="" disabled>Seleccione un estado</option>
                  <option v-for="e in estados" :key="e.id_estado" :value="e.id_estado">
                    {{ e.nombre }}
                  </option>
                </SelectInput>
              </FormField>

              <div class="md:col-span-1">
                <div class="rounded-2xl border border-gray-200 bg-gray-50 p-4">
                  <p class="text-xs font-semibold text-gray-700 uppercase tracking-wide">Consejo</p>
                  <p class="mt-1 text-sm text-gray-700">
                    Agrega todas las torres necesarias y define el nivel de prima altura en cada una.
                  </p>
                </div>
              </div>
            </div>
          </AppCard>

          <!-- Repeater torres -->
          <AppCard padding="md">
            <div class="flex items-start justify-between gap-3">
              <div class="min-w-0">
                <p class="text-sm font-semibold text-gray-900">Torres a crear</p>
                <p class="text-sm text-gray-600 mt-1">
                  Define nombre, número de pisos y nivel inicio prima altura por torre.
                </p>
              </div>

              <button
                type="button"
                @click="addTorre()"
                class="shrink-0 rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm font-semibold text-gray-800 hover:bg-gray-50 transition inline-flex items-center gap-2"
              >
                <PlusIcon class="w-5 h-5" />
                Agregar
              </button>
            </div>

            <div class="mt-5 space-y-4">
              <div
                v-for="(t, idx) in form.torres"
                :key="idx"
                class="rounded-2xl border border-gray-200 bg-white p-4"
              >
                <div class="flex items-center justify-between gap-3">
                  <p class="text-sm font-semibold text-gray-900">
                    Torre {{ idx + 1 }}
                  </p>

                  <button
                    v-if="form.torres.length > 1"
                    type="button"
                    @click="removeTorre(idx)"
                    class="rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-sm font-semibold text-red-700 hover:bg-red-100 transition inline-flex items-center gap-2"
                  >
                    <TrashIcon class="w-5 h-5" />
                    Quitar
                  </button>
                </div>

                <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                  <!-- Nombre -->
                  <FormField label="Nombre" required :error="err(`torres.${idx}.nombre_torre`)">
                    <TextInput v-model="t.nombre_torre" type="text" placeholder="Ej: Torre 1" />
                  </FormField>

                  <!-- Pisos -->
                  <FormField
                    label="Número de pisos"
                    :error="err(`torres.${idx}.numero_pisos`)"
                    hint="Entero (mín. 1)"
                  >
                    <TextInput
                      v-model.number="t.numero_pisos"
                      type="number"
                      min="1"
                      max="32767"
                      placeholder="Ej: 20"
                    />
                  </FormField>

                  <!-- Nivel prima -->
                  <FormField
                    label="Nivel inicio prima altura"
                    required
                    :error="err(`torres.${idx}.nivel_inicio_prima`)"
                    hint="Desde qué piso se aplica la prima"
                  >
                    <TextInput
                      v-model.number="t.nivel_inicio_prima"
                      type="number"
                      min="1"
                      placeholder="2"
                    />
                  </FormField>
                </div>
              </div>

              <p v-if="form.errors.torres" class="text-sm text-red-600">
                {{ form.errors.torres }}
              </p>
            </div>
          </AppCard>

          <!-- Mobile submit -->
          <div class="lg:hidden">
            <button
              type="button"
              @click="submit"
              :disabled="form.processing || !canSubmit"
              class="w-full rounded-xl bg-brand-600 px-4 py-3 text-sm font-semibold text-white hover:bg-brand-700 transition disabled:opacity-60"
            >
              {{ form.processing ? 'Guardando…' : 'Guardar' }}
            </button>

            <Link
              :href="route('admin.torres.index')"
              class="mt-3 block w-full text-center rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm font-semibold text-gray-800 hover:bg-gray-50 transition"
            >
              Cancelar
            </Link>
          </div>
        </div>

        <!-- Aside -->
        <div class="lg:col-span-4 space-y-6">
          <AppCard padding="md">
            <div class="flex items-start gap-3">
              <span class="mt-0.5 rounded-2xl border border-brand-300/60 bg-brand-200 p-2">
                <InformationCircleIcon class="w-5 h-5 text-brand-900" />
              </span>
              <div class="min-w-0">
                <p class="font-semibold text-gray-900">Tips</p>
                <ul class="mt-2 space-y-2 text-sm text-gray-700 list-disc pl-5">
                  <li>Selecciona el proyecto y estado antes de guardar.</li>
                  <li>El nivel inicio prima altura es obligatorio por torre.</li>
                  <li>Puedes crear todas las torres del proyecto en una sola operación.</li>
                </ul>
              </div>
            </div>
          </AppCard>

          <AppCard padding="md">
            <p class="text-sm font-semibold text-gray-900">Resumen</p>
            <div class="mt-3 space-y-2 text-sm">
              <InlineStatus :ok="!!form.id_proyecto" label="Proyecto seleccionado" />
              <InlineStatus :ok="!!form.id_estado" label="Estado seleccionado" />
              <InlineStatus :ok="form.torres.length > 0" :label="`Torres: ${form.torres.length}`" />
              <InlineStatus :ok="canSubmit" label="Formulario listo" />
            </div>
          </AppCard>
        </div>
      </div>
    </div>
  </SidebarBannerLayout>
</template>

<script setup>
import { computed } from 'vue'
import { useForm, Link, router } from '@inertiajs/vue3'

import SidebarBannerLayout from '@/Components/SidebarBannerLayout.vue'
import AppCard from '@/Components/AppCard.vue'
import PageHeader from '@/Components/PageHeader.vue'
import ButtonPrimary from '@/Components/ButtonPrimary.vue'
import SectionHeader from '@/Components/SectionHeader.vue'

import FormField from '@/Components/FormField.vue'
import TextInput from '@/Components/TextInput.vue'
import SelectInput from '@/Components/SelectInput.vue'
import InlineStatus from '@/Components/InlineStatus.vue'

import {
  PlusIcon,
  TrashIcon,
  InformationCircleIcon,
  BuildingOffice2Icon,
} from '@heroicons/vue/24/outline'

const props = defineProps({
  proyectos: { type: Array, default: () => [] },
  estados: { type: Array, default: () => [] },
  empleado: { type: Object, default: null },
})

const form = useForm({
  id_proyecto: '',
  id_estado: '',
  torres: [{ nombre_torre: 'Torre 1', numero_pisos: null, nivel_inicio_prima: 2 }],
})

function err(path) {
  return form.errors?.[path] || null
}

function addTorre() {
  const next = form.torres.length + 1
  form.torres.push({
    nombre_torre: `Torre ${next}`,
    numero_pisos: null,
    nivel_inicio_prima: 2,
  })
}

function removeTorre(idx) {
  form.torres.splice(idx, 1)
  form.torres = form.torres.map((t, i) => ({
    ...t,
    nombre_torre: t.nombre_torre?.trim() ? t.nombre_torre : `Torre ${i + 1}`,
  }))
}

const canSubmit = computed(() => {
  if (!form.id_proyecto || !form.id_estado) return false
  if (!Array.isArray(form.torres) || form.torres.length === 0) return false
  return form.torres.every((t) => {
    const nombreOk = String(t.nombre_torre || '').trim().length > 0
    const nivelOk = Number(t.nivel_inicio_prima) >= 1
    return nombreOk && nivelOk
  })
})

function submit() {
  form.post(route('admin.torres.store'), { preserveScroll: true })
}
</script>
