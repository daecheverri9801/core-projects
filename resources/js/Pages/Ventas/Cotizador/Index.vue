<template>
  <VentasLayout :empleado="empleado">
    <Head title="Cotizador" />

    <div class="flex justify-between items-center mb-6">
      <h1 class="text-3xl font-bold text-gray-900">Cotizaciones</h1>

      <Link
        href="/cotizador/create"
        class="inline-flex items-center gap-2 bg-[#f4c430] text-gray-900 px-4 py-2 rounded-lg font-semibold hover:bg-[#e5b520] transition"
      >
        Nueva Cotización
      </Link>
    </div>

    <div class="bg-white shadow rounded-lg p-6 border border-gray-200">
      <p class="text-gray-600">Aún no hay cotizaciones guardadas.</p>
    </div>
  </VentasLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3'
import VentasLayout from '@/Components/VentasLayout.vue'

const props = defineProps({
  cotizaciones: Array,
  empleado: Object,
})
</script>
