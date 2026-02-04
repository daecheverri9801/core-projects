<!-- resources/js/Pages/PoliticasPrecioProyecto/Create.vue -->
<template>
  <SidebarBannerLayout :empleado="empleado">
    <div class="space-y-6">
      <PageHeader
        title="Nueva política de precio"
        kicker="Políticas de precio"
        subtitle="Define escalones de aumento por ventas y su vigencia."
      >
        <template #actions>
          <div class="flex items-center gap-2">
            <Link
              href="/politicas-precio-proyecto"
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
              Guardar
            </button>
          </div>
        </template>
      </PageHeader>

      <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Form -->
        <div class="lg:col-span-8 space-y-6">
          <AppCard padding="md">
            <SectionHeader
              title="Configuración"
              subtitle="Completa los campos para crear la política."
              icon="TagIcon"
            />

            <div class="mt-5 grid grid-cols-1 md:grid-cols-2 gap-4">
              <!-- Proyecto -->
              <div class="md:col-span-2">
                <FormField label="Proyecto" required :error="form.errors.id_proyecto">
                  <SelectInput v-model="form.id_proyecto">
                    <option value="">Seleccione un proyecto</option>
                    <option v-for="p in proyectos" :key="p.id_proyecto" :value="p.id_proyecto">
                      {{ p.nombre }}
                    </option>
                  </SelectInput>
                </FormField>
              </div>

              <!-- Ventas por escalón -->
              <FormField label="Ventas por escalón" :error="form.errors.ventas_por_escalon" hint="Ej: 10">
                <TextInput
                  v-model.number="form.ventas_por_escalon"
                  type="number"
                  min="1"
                  placeholder="10"
                />
              </FormField>

              <!-- % Aumento -->
              <FormField label="% Aumento" :error="form.errors.porcentaje_aumento" hint="Ej: 5.5">
                <TextInput
                  v-model.number="form.porcentaje_aumento"
                  type="number"
                  step="0.001"
                  min="0"
                  max="999.999"
                  placeholder="5.5"
                />
              </FormField>

              <!-- Aplica desde -->
              <FormField label="Aplica desde" :error="form.errors.aplica_desde">
                <TextInput v-model="form.aplica_desde" type="date" />
              </FormField>

              <!-- Estado -->
              <div class="md:col-span-2">
                <Toggle
                  v-model="form.estado"
                  label="Política activa"
                  description="Si está activa, podrá aplicarse según las reglas del sistema."
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
              {{ form.processing ? 'Guardando…' : 'Guardar' }}
            </button>

            <Link
              href="/politicas-precio-proyecto"
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
                  <li>Define primero el proyecto al que aplica.</li>
                  <li>Usa “Aplica desde” para controlar la vigencia.</li>
                  <li>Puedes dejar ventas/escalón y % aumento vacíos si tu backend lo permite.</li>
                </ul>
              </div>
            </div>
          </AppCard>

          <AppCard padding="md">
            <p class="text-sm font-semibold text-gray-900">Validación rápida</p>
            <div class="mt-3 space-y-2 text-sm">
              <InlineStatus :ok="!!form.id_proyecto" label="Proyecto" />
              <InlineStatus :ok="form.estado === true || form.estado === false" label="Estado definido" />
              <InlineStatus :ok="!form.processing" label="Listo para guardar" />
            </div>
          </AppCard>
        </div>
      </div>
    </div>
  </SidebarBannerLayout>
</template>

<script setup>
import { useForm, Link } from '@inertiajs/vue3'

import SidebarBannerLayout from '@/Components/SidebarBannerLayout.vue'
import AppCard from '@/Components/AppCard.vue'
import PageHeader from '@/Components/PageHeader.vue'
import SectionHeader from '@/Components/SectionHeader.vue'

import FormField from '@/Components/FormField.vue'
import TextInput from '@/Components/TextInput.vue'
import SelectInput from '@/Components/SelectInput.vue'
import Toggle from '@/Components/Toggle.vue'
import InlineStatus from '@/Components/InlineStatus.vue'

import { TagIcon, InformationCircleIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  proyectos: { type: Array, default: () => [] },
  proyectoSeleccionado: { type: [String, Number], default: null },
  empleado: { type: Object, default: null },
})

const form = useForm({
  id_proyecto: props.proyectoSeleccionado || '',
  ventas_por_escalon: null,
  porcentaje_aumento: null,
  aplica_desde: '',
  estado: true,
})

function submit() {
  form.post('/politicas-precio-proyecto')
}
</script>
