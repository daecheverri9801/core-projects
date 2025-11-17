<template>
  <VentasLayout :empleado="empleado">
    <Head title="Resumen de Cotización" />

    <div class="bg-white shadow rounded-xl p-6 border border-gray-200">
      <h1 class="text-3xl font-bold text-gray-900 mb-4">Cotización Generada</h1>

      <p class="text-gray-600 mb-6">Aquí se mostrará el resumen final de la cotización.</p>

      <Link
        href="/cotizador/create"
        class="text-sm text-[#1e3a5f] hover:underline"
      >
        ← Crear otra cotización
      </Link>
    </div>
  </VentasLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3'
import VentasLayout from '@/Components/VentasLayout.vue'

const props = defineProps({
  empleado: Object,
})
</script>
