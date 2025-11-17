<template>
  <VentasLayout :empleado="empleado">
    <Head :title="`Plan #${plan.id_plan}`" />

    <VentasPageHeader
      :title="`Plan de Amortización #${plan.id_plan}`"
      :subtitle="`Venta #${plan.id_venta}`"
      :icon="DocumentTextIcon"
    />

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

      <!-- Columna Principal -->
      <VentasCard class="lg:col-span-2">
        <template #header>
          <h3 class="text-lg font-semibold text-gray-900">Información del Plan</h3>
        </template>

        <div class="space-y-4">
          <InfoRow label="Venta" :value="`#${plan.id_venta}`" />
          <InfoRow label="Cliente" :value="plan.venta?.cliente?.nombre ?? '—'" />
          <InfoRow label="Tipo de Plan" :value="plan.tipo_plan ?? '—'" />
          <InfoRow label="Interés Anual" :value="`${plan.valor_interes_anual ?? 0}%`" />
          <InfoRow label="Plazo" :value="`${plan.plazo_meses} meses`" />
          <InfoRow label="Fecha Inicio" :value="formatDate(plan.fecha_inicio)" />

          <div v-if="plan.observacion" class="pt-4 border-t border-gray-200">
            <h3 class="font-semibold mb-1">Observación</h3>
            <p>{{ plan.observacion }}</p>
          </div>
        </div>
      </VentasCard>

      <!-- Columna Lateral -->
      <VentasCard>
        <template #header>
          <h3 class="text-lg font-semibold text-gray-900">Acciones</h3>
        </template>

        <div class="space-y-3">
          <Link
            :href="`/planes-amortizacion-venta/${plan.id_plan}/edit`"
            class="w-full inline-flex justify-center px-4 py-2 bg-yellow-500 text-white rounded-lg font-semibold"
          >
            Editar Plan
          </Link>

          <button
            @click="eliminar"
            class="w-full px-4 py-2 bg-red-600 text-white rounded-lg font-semibold"
          >
            Eliminar
          </button>
        </div>
      </VentasCard>

    </div>

    <FlashMessages />
  </VentasLayout>
</template>

<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import VentasLayout from '@/Components/VentasLayout.vue'
import VentasCard from '@/Pages/Ventas/Components/VentasCard.vue'
import VentasPageHeader from '@/Pages/Ventas/Components/VentasPageHeader.vue'
import FlashMessages from '@/Components/FlashMessages.vue'

import { DocumentTextIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  plan: Object,
  empleado: Object,
})

function eliminar() {
  if (confirm('¿Desea eliminar este plan?')) {
    router.delete(`/planes-amortizacion-venta/${props.plan.id_plan}`)
  }
}

function formatDate(d) {
  if (!d) return '—'
  return new Date(d).toLocaleDateString('es-CO')
}

// Componente interno simple
const InfoRow = {
  props: ['label', 'value'],
  template: `
    <div class="flex justify-between text-gray-700">
      <span class="font-medium">{{ label }}</span>
      <span>{{ value }}</span>
    </div>
  `,
}
</script>
