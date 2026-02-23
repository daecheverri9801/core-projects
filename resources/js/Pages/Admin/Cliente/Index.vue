<template>
  <TopBannerLayout :empleado="empleado">
    <Head title="Clientes" />

    <div class="space-y-6">
      <!-- Estadísticas -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <StatCard
          label="Total clientes"
          :value="clientes.length"
          :icon="UserGroupIcon"
          variant="yellow"
        />
        <StatCard
          label="Personas naturales"
          :value="stats.personasNaturales"
          :icon="UserIcon"
          variant="blue"
        />
        <StatCard
          label="Personas jurídicas"
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
            <div class="min-w-0">
              <h2 class="text-lg font-semibold text-gray-900">Listado de clientes</h2>
              <p class="text-sm text-gray-600 mt-1">
                Busca por documento, nombre, tipo de cliente, teléfono o correo.
              </p>
            </div>

            <div class="flex flex-col sm:flex-row sm:items-center gap-3">
              <!-- Search -->
              <div class="relative w-full sm:w-80">
                <MagnifyingGlassIcon
                  class="w-5 h-5 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none"
                />
                <input
                  v-model="search"
                  type="text"
                  placeholder="Buscar cliente…"
                  class="w-full rounded-xl border border-gray-300 bg-white pl-10 pr-3 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition"
                />
              </div>

              <Link
                href="/admin/clientes/create"
                class="inline-flex items-center justify-center gap-2 rounded-xl bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-brand-700 transition whitespace-nowrap"
              >
                <PlusIcon class="w-5 h-5" />
                Nuevo cliente
              </Link>
            </div>
          </div>
        </template>

        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th
                  class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide"
                >
                  Documento
                </th>
                <th
                  class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide"
                >
                  Nombre
                </th>
                <th
                  class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide"
                >
                  Tipo cliente
                </th>
                <th
                  class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide"
                >
                  Contacto
                </th>
                <th
                  class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide"
                >
                  Ventas
                </th>
                <th
                  class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wide"
                >
                  Acciones
                </th>
              </tr>
            </thead>

            <tbody class="bg-white divide-y divide-gray-100">
              <tr
                v-for="cliente in filtered"
                :key="cliente.documento"
                class="hover:bg-gray-50 transition"
              >
                <td class="px-4 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div
                      class="w-10 h-10 rounded-full bg-brand-100 flex items-center justify-center text-brand-700 font-bold"
                    >
                      {{ getInitials(cliente.nombre) }}
                    </div>

                    <div class="ml-3">
                      <div class="text-sm font-semibold text-gray-900">
                        {{ cliente.documento }}
                      </div>
                      <div class="text-xs text-gray-500">
                        {{ cliente.tipo_documento?.tipo_documento || '—' }}
                      </div>
                    </div>
                  </div>
                </td>

                <td class="px-4 py-4">
                  <div class="text-sm font-semibold text-gray-900">
                    {{ cliente.nombre }}
                  </div>
                </td>

                <td class="px-4 py-4 whitespace-nowrap">
                  <span
                    class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full border"
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

                <td class="px-4 py-4 whitespace-nowrap text-right">
                  <div class="flex items-center justify-end gap-2">
                    <Link
                      :href="`/admin/clientes/${cliente.documento}`"
                      class="p-2 text-blue-600 hover:bg-blue-50 rounded-xl transition"
                      title="Ver"
                    >
                      <EyeIcon class="w-5 h-5" />
                    </Link>

                    <Link
                      :href="`/admin/clientes/${cliente.documento}/edit`"
                      class="p-2 text-amber-700 hover:bg-amber-50 rounded-xl transition"
                      title="Editar"
                    >
                      <PencilSquareIcon class="w-5 h-5" />
                    </Link>

                    <button
                      type="button"
                      @click="confirmDelete(cliente.documento, cliente.nombre)"
                      class="p-2 text-red-600 hover:bg-red-50 rounded-xl transition"
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
        :open="showConfirmDialog"
        :title="dialogOptions.title"
        :message="dialogOptions.message"
        :confirm-text="dialogOptions.confirmText"
        :cancel-text="dialogOptions.cancelText"
        :variant="dialogOptions.variant"
        @confirm="handleConfirm"
        @cancel="handleCancel"
      />
    </div>
  </TopBannerLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'

import TopBannerLayout from '@/Components/TopBannerLayout.vue'
import FlashMessages from '@/Components/FlashMessages.vue'
import VentasCard from '@/Components/VentasCard.vue'
import StatCard from '@/Components/StatCard.vue'
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

// Modal confirmación
const showConfirmDialog = ref(false)
const clienteToDelete = ref(null)
const dialogOptions = ref({
  title: '¿Eliminar cliente?',
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
    router.delete(`/admin/clientes/${clienteToDelete.value}`)
  }
  showConfirmDialog.value = false
  clienteToDelete.value = null
}

function handleCancel() {
  showConfirmDialog.value = false
  clienteToDelete.value = null
}

const stats = computed(() => ({
  personasNaturales: props.clientes.filter(
    (c) => c.tipo_cliente?.tipo_cliente === 'Persona Natural'
  ).length,
  personasJuridicas: props.clientes.filter(
    (c) => c.tipo_cliente?.tipo_cliente === 'Persona Jurídica'
  ).length,
  inversionistas: props.clientes.filter((c) => c.tipo_cliente?.tipo_cliente === 'Inversionista')
    .length,
}))

function getInitials(name) {
  if (!name) return '?'
  const parts = String(name).trim().split(/\s+/)
  return parts.length > 1
    ? `${parts[0][0]}${parts[1][0]}`.toUpperCase()
    : String(name).substring(0, 2).toUpperCase()
}

function getTipoClienteBadge(tipo) {
  const badges = {
    'Persona Natural': 'bg-blue-50 text-blue-800 border-blue-100',
    'Persona Jurídica': 'bg-green-50 text-green-800 border-green-100',
    Inversionista: 'bg-purple-50 text-purple-800 border-purple-100',
    Corporativo: 'bg-orange-50 text-orange-800 border-orange-100',
  }
  return badges[tipo] || 'bg-gray-50 text-gray-800 border-gray-200'
}
</script>
