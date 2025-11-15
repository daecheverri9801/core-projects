<template>
  <VentasLayout :empleado="empleado">
    <VentasPageHeader
      title="Gestión de Clientes"
      subtitle="Administra la información de tus clientes y prospectos"
      :icon="UserGroupIcon"
    />

    <!-- Estadísticas -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
      <StatCard
        label="Total Clientes"
        :value="clientes.length"
        :icon="UserGroupIcon"
        variant="yellow"
      />
      <StatCard
        label="Personas Naturales"
        :value="stats.personasNaturales"
        :icon="UserIcon"
        variant="blue"
      />
      <StatCard
        label="Personas Jurídicas"
        :value="stats.personasJuridicas"
        :icon="BuildingOfficeIcon"
        variant="green"
      />
      <StatCard
        label="Inversionistas"
        :value="stats.inversionistas"
        :icon="CurrencyDollarIcon"
        variant="purple"
      />
    </div>

    <!-- Tabla de Clientes -->
    <VentasCard>
      <template #header>
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
          <h2 class="text-lg font-semibold text-gray-900">Listado de Clientes</h2>
          <div class="flex items-center gap-3">
            <div class="relative">
              <MagnifyingGlassIcon
                class="w-5 h-5 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2"
              />
              <input
                v-model="search"
                type="text"
                placeholder="Buscar cliente..."
                class="w-full md:w-80 pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#FFEA00] focus:border-transparent transition"
              />
            </div>
            <Link
              href="/clientes/create"
              class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-[#FFEA00] to-[#D1C000] text-[#474100] font-semibold rounded-lg hover:shadow-lg transition-all duration-200"
            >
              <PlusIcon class="w-5 h-5" /> Nuevo Cliente
            </Link>
          </div>
        </div>
      </template>

      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th
                class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
              >
                Documento
              </th>
              <th
                class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
              >
                Nombre
              </th>
              <th
                class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
              >
                Tipo Cliente
              </th>
              <th
                class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
              >
                Contacto
              </th>
              <th
                class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
              >
                Ventas
              </th>
              <th
                class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider"
              >
                Acciones
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="cliente in filtered" :key="cliente.documento" class="hover:bg-gray-50">
              <td class="px-4 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <div
                    class="w-10 h-10 rounded-full bg-gradient-to-br from-[#FFEA00] to-[#D1C000] flex items-center justify-center text-[#474100] font-bold"
                  >
                    {{ getInitials(cliente.nombre) }}
                  </div>
                  <div class="ml-3">
                    <div class="text-sm font-medium text-gray-900">{{ cliente.documento }}</div>
                    <div class="text-xs text-gray-500">
                      {{ cliente.tipo_documento?.tipo_documento || '—' }}
                    </div>
                  </div>
                </div>
              </td>
              <td class="px-4 py-4">
                <div class="text-sm font-medium text-gray-900">{{ cliente.nombre }}</div>
              </td>
              <td class="px-4 py-4 whitespace-nowrap">
                <span
                  class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full"
                  :class="getTipoClienteBadge(cliente.tipo_cliente?.tipo_cliente)"
                >
                  {{ cliente.tipo_cliente?.tipo_cliente || '—' }}
                </span>
              </td>
              <td class="px-4 py-4">
                <div class="text-sm text-gray-900">{{ cliente.telefono || '—' }}</div>
                <div class="text-xs text-gray-500">{{ cliente.correo || '—' }}</div>
              </td>
              <td class="px-4 py-4 whitespace-nowrap">
                <div class="text-sm font-semibold text-gray-900">
                  {{ cliente.ventas?.length || 0 }}
                </div>
              </td>
              <td class="px-4 py-4 whitespace-nowrap text-right text-sm font-medium">
                <div class="flex items-center justify-end gap-2">
                  <Link
                    :href="`/clientes/${cliente.documento}`"
                    class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition"
                    title="Ver"
                  >
                    <EyeIcon class="w-5 h-5" />
                  </Link>
                  <Link
                    :href="`/clientes/${cliente.documento}/edit`"
                    class="p-2 text-amber-600 hover:bg-amber-50 rounded-lg transition"
                    title="Editar"
                  >
                    <PencilSquareIcon class="w-5 h-5" />
                  </Link>
                  <button
                    @click="confirmDelete(cliente.documento, cliente.nombre)"
                    class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition"
                    title="Eliminar"
                  >
                    <TrashIcon class="w-5 h-5" />
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="filtered.length === 0">
              <td colspan="6" class="px-4 py-8 text-center text-sm text-gray-500">
                No se encontraron clientes
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </VentasCard>

    <FlashMessages />
    <!-- Modal de Confirmación -->
    <ConfirmDialog
      :is-open="showConfirmDialog"
      :title="dialogOptions.title"
      :message="dialogOptions.message"
      :confirm-text="dialogOptions.confirmText"
      :cancel-text="dialogOptions.cancelText"
      :variant="dialogOptions.variant"
      @confirm="handleConfirm"
      @cancel="handleCancel"
    />
  </VentasLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link } from '@inertiajs/inertia-vue3'
import { Inertia } from '@inertiajs/inertia'
import VentasLayout from '@/Components/VentasLayout.vue'
import FlashMessages from '@/Components/FlashMessages.vue'
import VentasPageHeader from '../Components/VentasPageHeader.vue'
import VentasCard from '../Components/VentasCard.vue'
import StatCard from '../Components/StatCard.vue'
import ConfirmDialog from '@/Components/ConfirmDialog.vue'
import {
  UserGroupIcon,
  UserIcon,
  BuildingOfficeIcon,
  CurrencyDollarIcon,
  PlusIcon,
  EyeIcon,
  PencilSquareIcon,
  TrashIcon,
  MagnifyingGlassIcon,
} from '@heroicons/vue/24/outline'

const props = defineProps({
  clientes: { type: Array, default: () => [] },
  empleado: { type: Object, default: null },
})

const search = ref('')

// Estado del modal de confirmación
const showConfirmDialog = ref(false)
const clienteToDelete = ref(null)
const dialogOptions = ref({
  title: '¿Eliminar Cliente?',
  message: '',
  confirmText: 'Sí, eliminar',
  cancelText: 'Cancelar',
  variant: 'danger',
})

const filtered = computed(() => {
  const q = search.value.toLowerCase().trim()
  if (!q) return props.clientes
  return props.clientes.filter(
    (c) =>
      (c.nombre || '').toLowerCase().includes(q) ||
      (c.documento || '').toLowerCase().includes(q) ||
      (c.tipo_cliente?.tipo_cliente || '').toLowerCase().includes(q) ||
      (c.telefono || '').toLowerCase().includes(q) ||
      (c.correo || '').toLowerCase().includes(q)
  )
})

function confirmDelete(documento, nombre) {
  clienteToDelete.value = documento
  dialogOptions.value.message = `¿Está seguro de eliminar a ${nombre}? Esta acción no se puede deshacer y se eliminarán todos los datos asociados.`
  showConfirmDialog.value = true
}

function handleConfirm() {
  if (clienteToDelete.value) {
    Inertia.delete(`/clientes/${clienteToDelete.value}`)
  }
  showConfirmDialog.value = false
  clienteToDelete.value = null
}

function handleCancel() {
  showConfirmDialog.value = false
  clienteToDelete.value = null
}

const stats = computed(() => {
  return {
    personasNaturales: props.clientes.filter(
      (c) => c.tipo_cliente?.tipo_cliente === 'Persona Natural'
    ).length,
    personasJuridicas: props.clientes.filter(
      (c) => c.tipo_cliente?.tipo_cliente === 'Persona Jurídica'
    ).length,
    inversionistas: props.clientes.filter((c) => c.tipo_cliente?.tipo_cliente === 'Inversionista')
      .length,
  }
})

function getInitials(name) {
  if (!name) return '?'
  const parts = name.split(' ')
  return parts.length > 1
    ? `${parts[0][0]}${parts[1][0]}`.toUpperCase()
    : name.substring(0, 2).toUpperCase()
}

function getTipoClienteBadge(tipo) {
  const badges = {
    'Persona Natural': 'bg-blue-100 text-blue-800',
    'Persona Jurídica': 'bg-green-100 text-green-800',
    Inversionista: 'bg-purple-100 text-purple-800',
    Corporativo: 'bg-orange-100 text-orange-800',
  }
  return badges[tipo] || 'bg-gray-100 text-gray-800'
}
</script>
