<!-- resources/js/Pages/Empleados/Create.vue -->
<template>
  <TopBannerLayout :empleado="empleado" panel-name="Panel administrador">
    <Head title="Crear empleado" />

    <div class="space-y-6">
      <PageHeader
        title="Crear empleado"
        kicker="Empleados"
        subtitle="Registra un nuevo empleado y asígnalo a un cargo y dependencia."
      >
        <template #actions>
          <div class="flex items-center gap-2">
            <Link
              href="/empleados"
              class="rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-800 hover:bg-gray-50 transition"
            >
              Volver
            </Link>

            <button
              type="button"
              @click="submit"
              :disabled="form.processing"
              class="rounded-xl bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700 transition disabled:opacity-60"
            >
              {{ form.processing ? 'Guardando…' : 'Crear empleado' }}
            </button>
          </div>
        </template>
      </PageHeader>

      <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Form -->
        <div class="lg:col-span-8 space-y-6">
          <AppCard padding="md">
            <SectionHeader
              title="Información básica"
              subtitle="Completa los datos principales del empleado."
              icon="UserPlusIcon"
            />

            <div class="mt-5 grid grid-cols-1 md:grid-cols-2 gap-4">
              <FormField label="Nombre" required :error="form.errors.nombre">
                <TextInput id="nombre" v-model="form.nombre" type="text" placeholder="Ej: Daniel" />
              </FormField>

              <FormField label="Apellido" required :error="form.errors.apellido">
                <TextInput
                  id="apellido"
                  v-model="form.apellido"
                  type="text"
                  placeholder="Ej: Arango"
                />
              </FormField>

              <div class="md:col-span-2">
                <FormField label="Email" required :error="form.errors.email">
                  <TextInput
                    id="email"
                    v-model="form.email"
                    type="email"
                    autocomplete="email"
                    placeholder="correo@empresa.com"
                  />
                </FormField>
              </div>

              <div class="md:col-span-2">
                <FormField
                  label="Contraseña"
                  required
                  :error="form.errors.password"
                  hint="Mín. 8 caracteres (según tu validación en backend)"
                >
                  <div class="relative">
                    <TextInput
                      id="password"
                      v-model="form.password"
                      :type="showPassword ? 'text' : 'password'"
                      class="pr-12"
                      autocomplete="new-password"
                      placeholder="••••••••"
                    />
                    <button
                      type="button"
                      @click="showPassword = !showPassword"
                      class="absolute right-2 top-1/2 -translate-y-1/2 rounded-xl p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-50 transition"
                      :aria-label="showPassword ? 'Ocultar contraseña' : 'Mostrar contraseña'"
                    >
                      <EyeSlashIcon v-if="showPassword" class="h-5 w-5" />
                      <EyeIcon v-else class="h-5 w-5" />
                    </button>
                  </div>
                </FormField>
              </div>

              <div class="md:col-span-2">
                <FormField label="Teléfono" :error="form.errors.telefono" hint="Opcional">
                  <TextInput
                    id="telefono"
                    v-model="form.telefono"
                    type="text"
                    placeholder="Ej: 3001234567"
                  />
                </FormField>
              </div>
            </div>
          </AppCard>

          <AppCard padding="md">
            <SectionHeader
              title="Asignación"
              subtitle="Define cargo y dependencia del empleado."
              icon="BriefcaseIcon"
            />

            <div class="mt-5 grid grid-cols-1 md:grid-cols-2 gap-4">
              <FormField label="Cargo" required :error="form.errors.id_cargo">
                <SelectInput id="id_cargo" v-model="form.id_cargo">
                  <option value="" disabled>Seleccione un cargo</option>
                  <option v-for="c in cargos" :key="c.id_cargo" :value="c.id_cargo">
                    {{ c.nombre }}
                  </option>
                </SelectInput>
              </FormField>

              <FormField label="Dependencia" required :error="form.errors.id_dependencia">
                <SelectInput id="id_dependencia" v-model="form.id_dependencia">
                  <option value="" disabled>Seleccione una dependencia</option>
                  <option
                    v-for="d in dependencias"
                    :key="d.id_dependencia"
                    :value="d.id_dependencia"
                  >
                    {{ d.nombre }}
                  </option>
                </SelectInput>
              </FormField>

              <div class="md:col-span-2">
                <Toggle
                  v-model="form.estado"
                  label="Empleado activo"
                  description="Si está activo, podrá ingresar y operar según su rol."
                />
              </div>
            </div>
          </AppCard>

          <!-- Mobile submit -->
          <div class="lg:hidden">
            <button
              type="button"
              @click="submit"
              :disabled="form.processing"
              class="w-full rounded-xl bg-brand-600 px-4 py-3 text-sm font-semibold text-white hover:bg-brand-700 transition disabled:opacity-60"
            >
              {{ form.processing ? 'Guardando…' : 'Crear empleado' }}
            </button>

            <Link
              href="/empleados"
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
                <p class="font-semibold text-gray-900">Recomendaciones</p>
                <ul class="mt-2 space-y-2 text-sm text-gray-700 list-disc pl-5">
                  <li>Verifica el email antes de guardar.</li>
                  <li>Asigna cargo y dependencia para permisos y reportes.</li>
                  <li>Si el empleado no debe ingresar aún, desactívalo.</li>
                </ul>
              </div>
            </div>
          </AppCard>

          <AppCard padding="md">
            <p class="text-sm font-semibold text-gray-900">Validación rápida</p>
            <div class="mt-3 space-y-2 text-sm">
              <InlineStatus :ok="!!form.nombre" label="Nombre" />
              <InlineStatus :ok="!!form.apellido" label="Apellido" />
              <InlineStatus :ok="!!form.email" label="Email" />
              <InlineStatus :ok="!!form.id_cargo" label="Cargo" />
              <InlineStatus :ok="!!form.id_dependencia" label="Dependencia" />
            </div>
          </AppCard>
        </div>
      </div>
    </div>
  </TopBannerLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Head, Link, useForm } from '@inertiajs/vue3'

import TopBannerLayout from '@/Components/TopBannerLayout.vue'
import PageHeader from '@/Components/PageHeader.vue'
import AppCard from '@/Components/AppCard.vue'
import SectionHeader from '@/Components/SectionHeader.vue'
import FormField from '@/Components/FormField.vue'
import TextInput from '@/Components/TextInput.vue'
import SelectInput from '@/Components/SelectInput.vue'
import Toggle from '@/Components/Toggle.vue'
import InlineStatus from '@/Components/InlineStatus.vue'

import {
  EyeIcon,
  EyeSlashIcon,
  InformationCircleIcon,
  UserPlusIcon,
  BriefcaseIcon,
} from '@heroicons/vue/24/outline'

const props = defineProps({
  empleado: { type: Object, default: null },
  cargos: { type: Array, default: () => [] },
  dependencias: { type: Array, default: () => [] },
})

const showPassword = ref(false)

const form = useForm({
  nombre: '',
  apellido: '',
  email: '',
  password: '',
  telefono: '',
  id_cargo: '',
  id_dependencia: '',
  estado: true,
})

function submit() {
  form.post('/empleados', { preserveScroll: true })
}
</script>
