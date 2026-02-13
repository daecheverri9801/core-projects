<!-- resources/js/Pages/Empleados/Show.vue -->
<template>
  <TopBannerLayout :empleado="userEmpleado" panel-name="Panel administrador">
    <Head title="Detalle empleado" />

    <div class="space-y-6">
      <PageHeader
        title="Detalle empleado"
        kicker="Empleados"
        subtitle="Consulta la información del empleado registrado."
      >
        <template #actions>
          <div class="flex items-center gap-2">
            <Link
              href="/empleados"
              class="rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-800 hover:bg-gray-50 transition"
            >
              Volver
            </Link>

            <Link
              :href="`/empleados/${empleado.id_empleado}/edit`"
              class="rounded-xl bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700 transition inline-flex items-center gap-2"
            >
              <PencilSquareIcon class="h-5 w-5" />
              Editar
            </Link>
          </div>
        </template>
      </PageHeader>

      <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Main -->
        <div class="lg:col-span-8 space-y-6">
          <AppCard padding="md">
            <SectionHeader
              title="Información"
              subtitle="Datos principales del empleado."
              icon="UserIcon"
            />

            <dl class="mt-5 grid grid-cols-1 md:grid-cols-2 gap-4">
              <InfoItem label="Nombre" :value="empleado.nombre" />
              <InfoItem label="Apellido" :value="empleado.apellido" />
              <InfoItem label="Email" :value="empleado.email" class="md:col-span-2" />
              <InfoItem label="Teléfono" :value="empleado.telefono || '—'" />
              <InfoItem label="Cargo" :value="empleado.cargo?.nombre || '—'" />
              <InfoItem label="Dependencia" :value="empleado.dependencia?.nombre || '—'" />
              <div class="md:col-span-2">
                <dt class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Estado</dt>
                <dd class="mt-1">
                  <span
                    class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-sm font-semibold border"
                    :class="
                      empleado.estado
                        ? 'bg-emerald-50 border-emerald-200 text-emerald-800'
                        : 'bg-gray-50 border-gray-200 text-gray-700'
                    "
                  >
                    <span
                      class="h-2 w-2 rounded-full"
                      :class="empleado.estado ? 'bg-emerald-500' : 'bg-gray-400'"
                    />
                    {{ empleado.estado ? 'Activo' : 'Inactivo' }}
                  </span>
                </dd>
              </div>
            </dl>
          </AppCard>
        </div>

        <!-- Aside -->
        <div class="lg:col-span-4 space-y-6">
          <AppCard padding="md">
            <div class="flex items-start gap-3">
              <span class="mt-0.5 rounded-2xl border border-brand-300/60 bg-brand-200 p-2">
                <InformationCircleIcon class="w-5 h-5 text-brand-900" />
              </span>
              <div class="min-w-0">
                <p class="font-semibold text-gray-900">Acciones rápidas</p>
                <p class="mt-1 text-sm text-gray-700">
                  Edita el empleado para actualizar datos, cargo, dependencia o estado.
                </p>
              </div>
            </div>
          </AppCard>

          <AppCard padding="md">
            <p class="text-sm font-semibold text-gray-900">Resumen</p>
            <div class="mt-3 space-y-2 text-sm">
              <InlineStatus :ok="!!empleado.nombre" label="Nombre" />
              <InlineStatus :ok="!!empleado.apellido" label="Apellido" />
              <InlineStatus :ok="!!empleado.email" label="Email" />
              <InlineStatus :ok="!!empleado.cargo?.nombre" label="Cargo asignado" />
              <InlineStatus :ok="!!empleado.dependencia?.nombre" label="Dependencia asignada" />
            </div>
          </AppCard>
        </div>
      </div>
    </div>
  </TopBannerLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Head, Link, usePage } from '@inertiajs/vue3'

import TopBannerLayout from '@/Components/TopBannerLayout.vue'
import PageHeader from '@/Components/PageHeader.vue'
import AppCard from '@/Components/AppCard.vue'
import SectionHeader from '@/Components/SectionHeader.vue'
import InlineStatus from '@/Components/InlineStatus.vue'

import { PencilSquareIcon, InformationCircleIcon, UserIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  empleado: { type: Object, required: true },
})

/**
 * Empleado autenticado (para TopBannerLayout).
 * Ajusta según tu app si lo mandas con otro nombre.
 */
const page = usePage()
const userEmpleado = computed(() => page.props?.empleado ?? null)

/**
 * Pequeño "InfoItem" local (sin depender de otro componente).
 * Si ya tienes un componente InfoItem global, puedes reemplazarlo.
 */
const InfoItem = {
  props: {
    label: { type: String, required: true },
    value: { type: [String, Number], default: '—' },
  },
  template: `
    <div>
      <dt class="text-xs font-semibold text-gray-600 uppercase tracking-wide">{{ label }}</dt>
      <dd class="mt-1 text-sm text-gray-900 break-words">{{ value }}</dd>
    </div>
  `,
}
</script>
