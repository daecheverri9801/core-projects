<template>
  <SidebarBannerLayout>
    <div class="flex justify-between items-center mb-6 max-w-4xl mx-auto">
      <h2 class="text-3xl font-bold text-gray-900">Detalle Empleado</h2>
      <Link
        :href="`/empleados/${empleado.id_empleado}/edit`"
        class="inline-flex items-center gap-2 rounded bg-brand-500 px-5 py-3 text-white font-semibold shadow hover:bg-brand-600 transition"
      >
        <PencilIcon class="w-5 h-5" />
        Editar
      </Link>
    </div>

    <div class="max-w-4xl mx-auto bg-white rounded-lg shadow p-8 space-y-6">
      <div>
        <h3 class="text-lg font-semibold text-gray-700 mb-2">Nombre</h3>
        <p class="text-gray-900">{{ empleado.nombre }}</p>
      </div>

      <div>
        <h3 class="text-lg font-semibold text-gray-700 mb-2">Apellido</h3>
        <p class="text-gray-900">{{ empleado.apellido }}</p>
      </div>

      <div>
        <h3 class="text-lg font-semibold text-gray-700 mb-2">Email</h3>
        <p class="text-gray-900">{{ empleado.email }}</p>
      </div>

      <div>
        <h3 class="text-lg font-semibold text-gray-700 mb-2">Teléfono</h3>
        <p class="text-gray-900">{{ empleado.telefono || '-' }}</p>
      </div>

      <div>
        <h3 class="text-lg font-semibold text-gray-700 mb-2">Cargo</h3>
        <p class="text-gray-900">{{ empleado.cargo?.nombre || '-' }}</p>
      </div>

      <div>
        <h3 class="text-lg font-semibold text-gray-700 mb-2">Dependencia</h3>
        <p class="text-gray-900">{{ empleado.dependencia?.nombre || '-' }}</p>
      </div>

      <div>
        <h3 class="text-lg font-semibold text-gray-700 mb-2">Estado</h3>
        <p class="text-gray-900">{{ empleado.estado ? 'Activo' : 'Inactivo' }}</p>
      </div>
    </div>
  </SidebarBannerLayout>
</template>

<script setup>
import SidebarBannerLayout from '@/Components/SidebarBannerLayout.vue'
import { Link } from '@inertiajs/vue3'
import { PencilIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  empleado: Object,
})
</script>
