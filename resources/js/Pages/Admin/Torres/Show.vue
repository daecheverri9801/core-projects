<template>
  <SidebarBannerLayout>
    <template #title>Torre: {{ torre.nombre_torre }}</template>

    <div class="max-w-5xl mx-auto bg-white rounded-lg shadow p-8">
      <div class="flex items-center justify-between mb-6">
        <div class="text-gray-500">ID: <span class="font-medium text-gray-900">{{ torre.id_torre }}</span></div>
        <div class="flex gap-2">
          <Link :href="route('admin.torres.edit', torre.id_torre)"
            class="px-4 py-2 rounded border hover:bg-gray-50">Editar</Link>
          <Link :href="route('admin.torres.index')"
            class="px-4 py-2 rounded bg-brand-600 text-white hover:bg-brand-700">Volver</Link>
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="space-y-3">
          <div><span class="text-gray-500">Nombre:</span> <span class="font-medium">{{ torre.nombre_torre }}</span></div>
          <div><span class="text-gray-500">Proyecto:</span> <span class="font-medium">{{ torre.proyecto?.nombre || '-' }}</span></div>
          <div><span class="text-gray-500">Estado:</span>
            <span class="px-2 py-1 rounded text-xs font-semibold bg-brand-100 text-brand-800">
              {{ torre.estado?.nombre || '-' }}
            </span>
          </div>
          <div><span class="text-gray-500">Número de pisos:</span> <span class="font-medium">{{ torre.numero_pisos ?? '-' }}</span></div>
        </div>

        <div class="space-y-3">
          <div class="text-gray-500">Ubicación del proyecto:</div>
          <div class="font-medium">
            {{ torre.proyecto?.ubicacion?.direccion || '-' }},
            {{ torre.proyecto?.ubicacion?.ciudad?.nombre || '' }}
          </div>
        </div>
      </div>

      <div class="mt-8">
        <h3 class="text-xl font-semibold mb-3">Resumen</h3>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div class="p-4 border rounded">
            <div class="text-gray-500 text-sm">Pisos registrados</div>
            <div class="text-2xl font-bold">{{ torre.pisos?.length || 0 }}</div>
          </div>
          <div class="p-4 border rounded">
            <div class="text-gray-500 text-sm">Apartamentos</div>
            <div class="text-2xl font-bold">{{ torre.apartamentos?.length || 0 }}</div>
          </div>
          <div class="p-4 border rounded">
            <div class="text-gray-500 text-sm">Estado</div>
            <div class="text-lg font-semibold">{{ torre.estado?.nombre || '-' }}</div>
          </div>
        </div>
      </div>

      <div class="mt-8">
        <h3 class="text-xl font-semibold mb-3">Pisos</h3>
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Nombre</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white">
              <tr v-for="(p, idx) in torre.pisos || []" :key="idx">
                <td class="px-4 py-2 text-sm text-gray-700">{{ idx + 1 }}</td>
                <td class="px-4 py-2 text-sm text-gray-900">{{ p.nombre || p.id_piso || '-' }}</td>
              </tr>
              <tr v-if="!torre.pisos || torre.pisos.length === 0">
                <td colspan="2" class="px-4 py-4 text-center text-gray-500">Sin pisos</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <div class="mt-8">
        <h3 class="text-xl font-semibold mb-3">Apartamentos</h3>
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white">
              <tr v-for="(a, idx) in torre.apartamentos || []" :key="idx">
                <td class="px-4 py-2 text-sm text-gray-700">{{ idx + 1 }}</td>
                <td class="px-4 py-2 text-sm text-gray-900">{{ a.tipo_apartamento?.nombre || a.tipoApartamento?.nombre || '-' }}</td>
                <td class="px-4 py-2 text-sm">
                  <span class="px-2 py-1 rounded text-xs font-semibold bg-brand-100 text-brand-800">
                    {{ a.estado_inmueble?.nombre || a.estadoInmueble?.nombre || '-' }}
                  </span>
                </td>
              </tr>
              <tr v-if="!torre.apartamentos || torre.apartamentos.length === 0">
                <td colspan="3" class="px-4 py-4 text-center text-gray-500">Sin apartamentos</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </SidebarBannerLayout>
</template>

<script setup>
import { Link } from '@inertiajs/inertia-vue3'
import SidebarBannerLayout from '@/Components/SidebarBannerLayout.vue'

const props = defineProps({
  torre: Object,
  empleado: Object,
})
</script>