<!-- resources/js/Pages/Empleados/Edit.vue -->
<template>
  <TopBannerLayout :empleado="userEmpleado" panel-name="Panel administrador">
    <Head title="Editar empleado" />

    <div class="space-y-6">
      <PageHeader
        title="Editar empleado"
        kicker="Empleados"
        :subtitle="`Actualiza la información de ${props.empleado?.nombre ?? ''} ${props.empleado?.apellido ?? ''}`"
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
              title="Información básica"
              subtitle="Edita los datos principales del empleado."
              icon="PencilSquareIcon"
            />

            <div class="mt-5 grid grid-cols-1 md:grid-cols-2 gap-4">
              <FormField label="Nombre" required :error="form.errors.nombre">
                <TextInput id="nombre" v-model="form.nombre" type="text" />
              </FormField>

              <FormField label="Apellido" required :error="form.errors.apellido">
                <TextInput id="apellido" v-model="form.apellido" type="text" />
              </FormField>

              <div class="md:col-span-2">
                <FormField label="Email" required :error="form.errors.email">
                  <TextInput id="email" v-model="form.email" type="email" autocomplete="email" />
                </FormField>
              </div>

              <div class="md:col-span-2">
                <FormField
                  label="Contraseña"
                  :error="form.errors.password"
                  hint="Deja vacío para no cambiarla."
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
                  <TextInput id="telefono" v-model="form.telefono" type="text" />
                </FormField>
              </div>
            </div>
          </AppCard>

          <AppCard padding="md">
            <SectionHeader
              title="Asignación"
              subtitle="Actualiza cargo y dependencia."
              icon="BriefcaseIcon"
            />

            <div class="mt-5 grid grid-cols-1 md:grid-cols-2 gap-4">
              <FormField label="Dependencia" required :error="form.errors.id_dependencia">
                <SelectInput id="id_dependencia" v-model="form.id_dependencia">
                  <option value="" disabled>Seleccione una dependencia</option>
                  <option v-for="d in dependencias" :key="d.id_dependencia" :value="d.id_dependencia">
                    {{ d.nombre }}
                  </option>
                </SelectInput>
              </FormField>

              <FormField label="Cargo" required :error="form.errors.id_cargo">
                <SelectInput id="id_cargo" v-model="form.id_cargo">
                  <option value="" disabled>Seleccione un cargo</option>
                  <option v-for="c in cargos" :key="c.id_cargo" :value="c.id_cargo">
                    {{ c.nombre }}
                  </option>
                </SelectInput>
              </FormField>

              <div class="md:col-span-2">
                <Toggle
                  v-model="form.estado"
                  label="Empleado activo"
                  description="Si se desactiva, no podrá ingresar al sistema."
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
              {{ form.processing ? 'Guardando…' : 'Guardar cambios' }}
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
                <p class="font-semibold text-gray-900">Notas</p>
                <ul class="mt-2 space-y-2 text-sm text-gray-700 list-disc pl-5">
                  <li>Si no cambias la contraseña, déjala vacía.</li>
                  <li>Revisa cargo/dependencia para permisos y reportes.</li>
                  <li>Desactivar bloquea el acceso al sistema.</li>
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
import { Head, Link, useForm, usePage } from '@inertiajs/vue3'

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
  PencilSquareIcon,
  BriefcaseIcon,
} from '@heroicons/vue/24/outline'

const props = defineProps({
  // empleado a editar
  empleado: { type: Object, required: true },
  cargos: { type: Array, default: () => [] },
  dependencias: { type: Array, default: () => [] },
})

/**
 * Empleado autenticado (para TopBannerLayout).
 * Ajusta según cómo estés enviando el usuario desde backend:
 * - page.props.empleado
 * - o page.props.auth.user, etc.
 */
const page = usePage()
const userEmpleado = page.props?.empleado ?? null

const showPassword = ref(false)

const form = useForm({
  nombre: props.empleado?.nombre ?? '',
  apellido: props.empleado?.apellido ?? '',
  email: props.empleado?.email ?? '',
  password: '', // vacío => no cambia
  telefono: props.empleado?.telefono ?? '',
  id_cargo: props.empleado?.id_cargo ?? '',
  id_dependencia: props.empleado?.id_dependencia ?? '',
  estado: props.empleado?.estado ?? true,
})

function submit() {
  form.put(`/empleados/${props.empleado.id_empleado}`, {
    preserveScroll: true,
  })
}
</script>
