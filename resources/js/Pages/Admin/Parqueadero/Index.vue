<!-- resources/js/Pages/Admin/Parqueadero/Index.vue -->
<template>
  <SidebarBannerLayout :empleado="empleado">
    <div class="space-y-6">
      <PageHeader
        title="Parqueaderos"
        kicker="Inventario del proyecto"
        subtitle="Consulta y administra parqueaderos agrupados por proyecto."
      >
        <template #actions>
          <ButtonPrimary :href="route('parqueaderos.create')">
            <PlusIcon class="h-5 w-5" />
            Nuevo
          </ButtonPrimary>
        </template>
      </PageHeader>

      <!-- Controles -->
      <AppCard padding="md">
        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
          <div class="min-w-0">
            <p class="text-sm text-gray-600">
              Total:
              <span class="font-semibold text-gray-900">{{ totalRegistros }}</span>
            </p>
            <p class="text-xs text-gray-500">
              Mostrando agrupado por proyecto.
            </p>
          </div>

          <div class="flex w-full flex-col gap-2 md:w-[560px] md:flex-row md:items-center">
            <div class="flex-1">
              <QuickSearch
                v-model="localFilters.search"
                placeholder="Buscar por número, tipo, proyecto, torre, estado…"
              />
            </div>

            <button
              type="button"
              @click="clearSearch"
              class="rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-800 hover:bg-gray-50 transition disabled:opacity-50"
              :disabled="!localFilters.search"
            >
              Limpiar
            </button>
          </div>
        </div>
      </AppCard>

      <!-- Listado por proyecto -->
      <div v-for="grupo in grouped" :key="String(grupo.key)" class="space-y-3">
        <AppCard padding="md">
          <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
              <p class="text-xs text-gray-500">Proyecto</p>
              <p class="text-lg font-semibold text-gray-900 truncate">
                {{ grupo.nombre }}
              </p>
              <p class="text-sm text-gray-600 mt-1">
                Registros:
                <span class="font-semibold text-gray-900">{{ grupo.items.length }}</span>
              </p>
            </div>

            <span
              class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-semibold"
              :class="grupo.key ? 'border-brand-200 bg-brand-50 text-brand-800' : 'border-gray-200 bg-gray-50 text-gray-700'"
            >
              {{ grupo.key ? 'Con asignación' : 'Sin asignación' }}
            </span>
          </div>
        </AppCard>

        <AppCard padding="none">
          <div class="overflow-x-auto">
            <table class="min-w-[1100px] w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                    Número
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                    Tipo
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                    Estado
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                    Apartamento
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                    Torre
                  </th>
                  <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">
                    Acciones
                  </th>
                </tr>
              </thead>

              <tbody class="divide-y divide-gray-200 bg-white">
                <tr
                  v-for="p in grupo.items"
                  :key="p.id_parqueadero"
                  class="hover:bg-brand-50/40 transition"
                >
                  <td class="px-6 py-4 text-sm text-gray-900">
                    <div class="flex items-start gap-3">
                      <span class="mt-0.5 rounded-2xl border border-brand-300/60 bg-brand-200 p-2">
                        <Squares2X2Icon class="h-5 w-5 text-brand-900" />
                      </span>
                      <div class="min-w-0">
                        <p class="font-semibold text-gray-900 truncate">
                          {{ p.numero || '—' }}
                        </p>
                        <p class="text-xs text-gray-600">ID: {{ p.id_parqueadero }}</p>
                      </div>
                    </div>
                  </td>

                  <td class="px-6 py-4 text-sm text-gray-900">
                    {{ p.tipo || '—' }}
                  </td>

                  <td class="px-6 py-4 text-sm">
                    <span
                      class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-semibold"
                      :class="estadoBadgeClass(p.estado)"
                    >
                      {{ p.estado || '—' }}
                    </span>
                  </td>

                  <td class="px-6 py-4 text-sm text-gray-900">
                    {{ p.apartamento || '—' }}
                  </td>

                  <td class="px-6 py-4 text-sm text-gray-900">
                    {{ p.torre || '—' }}
                  </td>

                  <td class="px-6 py-4">
                    <div class="flex items-center justify-end gap-2">
                      <IconButton
                        :href="route('parqueaderos.show', p.id_parqueadero)"
                        icon="EyeIcon"
                        title="Ver"
                        variant="info"
                      />
                      <IconButton
                        :href="route('parqueaderos.edit', p.id_parqueadero)"
                        icon="PencilIcon"
                        title="Editar"
                        variant="warn"
                      />
                      <IconButton
                        icon="TrashIcon"
                        title="Eliminar"
                        variant="danger"
                        @click="confirmDelete(p)"
                      />
                    </div>
                  </td>
                </tr>

                <tr v-if="grupo.items.length === 0">
                  <td colspan="6" class="px-6 py-10 text-center text-sm text-gray-500">
                    Sin registros
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </AppCard>
      </div>

      <!-- Empty global -->
      <AppCard v-if="grouped.length === 0" padding="md">
        <div class="py-8 text-center">
          <MagnifyingGlassIcon class="h-8 w-8 mx-auto text-brand-700" />
          <p class="mt-3 text-sm font-semibold text-gray-900">Sin resultados</p>
          <p class="mt-1 text-sm text-gray-600">No hay parqueaderos que coincidan con la búsqueda.</p>
          <button
            v-if="localFilters.search"
            @click="clearSearch"
            class="mt-4 rounded-xl bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700 transition"
          >
            Limpiar búsqueda
          </button>
        </div>
      </AppCard>

      <!-- Confirm delete -->
      <ConfirmDialog
        :open="deleteModal.open"
        title="Eliminar parqueadero"
        :message="deleteMessage"
        cancel-text="Cancelar"
        confirm-text="Eliminar"
        @cancel="deleteModal.open = false"
        @confirm="doDelete"
      />
    </div>
  </SidebarBannerLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'

import SidebarBannerLayout from '@/Components/SidebarBannerLayout.vue'
import AppCard from '@/Components/AppCard.vue'
import PageHeader from '@/Components/PageHeader.vue'
import ButtonPrimary from '@/Components/ButtonPrimary.vue'
import QuickSearch from '@/Components/QuickSearch.vue'
import IconButton from '@/Components/IconButton.vue'
import ConfirmDialog from '@/Components/ConfirmDialog.vue'

import {
  PlusIcon,
  Squares2X2Icon,
  MagnifyingGlassIcon,
} from '@heroicons/vue/24/outline'

const props = defineProps({
  parqueaderos: { type: Array, default: () => [] },
  empleado: { type: Object, default: null },
})

const localFilters = ref({
  search: '',
})

function clearSearch() {
  localFilters.value.search = ''
}

const filteredFlat = computed(() => {
  const q = (localFilters.value.search || '').toLowerCase().trim()
  if (!q) return props.parqueaderos || []

  return (props.parqueaderos || []).filter((p) => {
    return (
      String(p.id_parqueadero).includes(q) ||
      (p.numero || '').toLowerCase().includes(q) ||
      (p.tipo || '').toLowerCase().includes(q) ||
      (p.estado || '').toLowerCase().includes(q) ||
      (p.apartamento || '').toLowerCase().includes(q) ||
      (p.torre || '').toLowerCase().includes(q) ||
      (p.proyecto || '').toLowerCase().includes(q)
    )
  })
})

const totalRegistros = computed(() => (props.parqueaderos || []).length)

const grouped = computed(() => {
  const list = filteredFlat.value

  // key: id_proyecto o null (sin asignación)
  const map = new Map()

  for (const p of list) {
    const key = p.id_proyecto ?? null
    const nombre = p.proyecto || 'Sin asignación'
    if (!map.has(key)) map.set(key, { key, nombre, items: [] })
    map.get(key).items.push(p)
  }

  // ordenar: proyectos primero, luego "Sin asignación" al final
  const arr = Array.from(map.values())
  arr.sort((a, b) => {
    if (a.key === null && b.key !== null) return 1
    if (a.key !== null && b.key === null) return -1
    return String(a.nombre).localeCompare(String(b.nombre))
  })

  return arr
})

function estadoBadgeClass(estado) {
  const n = (estado || '').toLowerCase()
  if (n.includes('asign')) return 'border-amber-200 bg-amber-50 text-amber-800'
  if (n.includes('disp')) return 'border-green-200 bg-green-50 text-green-800'
  return 'border-gray-200 bg-gray-50 text-gray-700'
}

/** Delete flow */
const deleteModal = ref({ open: false, item: null })

const deleteMessage = computed(() => {
  const nombre = deleteModal.value.item?.numero
  return `¿Seguro que deseas eliminar el parqueadero “${nombre || '—'}”? Esta acción no se puede deshacer.`
})

function confirmDelete(item) {
  deleteModal.value = { open: true, item }
}

function doDelete() {
  if (!deleteModal.value.item) return
  router.delete(route('parqueaderos.destroy', deleteModal.value.item.id_parqueadero), {
    onFinish: () => {
      deleteModal.value = { open: false, item: null }
    },
  })
}
</script>
