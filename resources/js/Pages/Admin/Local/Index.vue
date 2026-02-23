<!-- resources/js/Pages/Admin/Locales/Index.vue -->
<template>
  <TopBannerLayout :empleado="empleado">
    <div class="space-y-6">
      <PageHeader
        title="Locales"
        kicker="Inventario del proyecto"
        subtitle="Consulta y administra locales agrupados por proyecto."
      >
        <template #actions>
          <ButtonPrimary href="/locales/create">
            <PlusIcon class="w-5 h-5" />
            Nuevo local
          </ButtonPrimary>
        </template>
      </PageHeader>

      <!-- Controles -->
      <AppCard padding="md">
        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
          <div class="min-w-0">
            <p class="text-sm text-gray-600">
              Proyectos en esta página:
              <span class="font-semibold text-gray-900">{{ proyectos.data?.length ?? 0 }}</span>
            </p>
          </div>

          <div class="flex w-full flex-col gap-2 md:w-[620px] md:flex-row md:items-center">
            <div class="flex-1">
              <QuickSearch
                v-model="localFilters.search"
                placeholder="Buscar por número, torre, piso, estado…"
                @keyup.enter="doSearch"
              />
            </div>

            <button
              type="button"
              @click="doSearch"
              class="rounded-xl bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700 transition"
            >
              Buscar
            </button>
          </div>
        </div>
      </AppCard>

      <!-- Listado por proyecto -->
      <div class="space-y-4">
        <AppCard v-for="proy in proyectos.data" :key="proy.id_proyecto" padding="none">
          <!-- Encabezado proyecto -->
          <div class="px-6 py-4 border-b border-gray-200 flex items-start justify-between gap-4">
            <div class="min-w-0">
              <div class="flex items-center gap-3">
                <span class="rounded-2xl border border-brand-300/60 bg-brand-200 p-2">
                  <BuildingStorefrontIcon class="w-5 h-5 text-brand-900" />
                </span>

                <div class="min-w-0">
                  <p class="font-semibold text-gray-900 truncate">
                    {{ proy.nombre || 'Proyecto' }}
                  </p>
                  <p class="text-xs text-gray-600">
                    ID: {{ proy.id_proyecto }}
                    <span class="mx-2 text-gray-300">•</span>
                    Locales:
                    <span class="font-semibold text-gray-900">{{
                      proy.locales_count ?? proy.locales?.length ?? 0
                    }}</span>
                  </p>
                </div>
              </div>
            </div>
          </div>

          <!-- Tabla locales del proyecto -->
          <div class="overflow-x-auto">
            <table class="min-w-[1100px] w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th
                    class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider"
                  >
                    Número
                  </th>
                  <th
                    class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider"
                  >
                    Torre
                  </th>
                  <th
                    class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider"
                  >
                    Piso
                  </th>
                  <th
                    class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider"
                  >
                    Área (m²)
                  </th>
                  <th
                    class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider"
                  >
                    Valor m²
                  </th>
                  <th
                    class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider"
                  >
                    Valor total
                  </th>
                  <th
                    class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider"
                  >
                    Estado
                  </th>
                  <th
                    class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider"
                  >
                    Acciones
                  </th>
                </tr>
              </thead>

              <tbody class="divide-y divide-gray-200 bg-white">
                <tr
                  v-for="l in proy.locales || []"
                  :key="l.id_local"
                  class="hover:bg-brand-50/40 transition"
                >
                  <td class="px-6 py-4">
                    <div class="min-w-0">
                      <p class="font-semibold text-gray-900 truncate">
                        {{ l.numero || '—' }}
                      </p>
                      <p class="text-xs text-gray-600">ID: {{ l.id_local }}</p>
                    </div>
                  </td>

                  <td class="px-6 py-4 text-sm text-gray-900">
                    {{ l.torre || '—' }}
                  </td>

                  <td class="px-6 py-4 text-sm text-gray-900">
                    {{ l.piso ?? '—' }}
                  </td>

                  <td class="px-6 py-4 text-sm text-gray-900 text-right">
                    {{ formatArea(l.area_total_local) }}
                  </td>

                  <td class="px-6 py-4 text-sm text-gray-900 text-right">
                    {{ formatCurrency(l.valor_m2) }}
                  </td>

                  <td class="px-6 py-4 text-sm text-gray-900 text-right">
                    {{ formatCurrency(l.valor_total) }}
                  </td>

                  <td class="px-6 py-4 text-sm">
                    <span
                      class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-semibold"
                      :class="estadoBadgeClass(l.estado)"
                    >
                      {{ l.estado || '—' }}
                    </span>
                  </td>

                  <td class="px-6 py-4">
                    <div class="flex items-center justify-end gap-2">
                      <IconButton
                        :href="`/locales/${l.id_local}`"
                        icon="EyeIcon"
                        title="Ver"
                        variant="info"
                      />
                      <IconButton
                        :href="`/locales/${l.id_local}/edit`"
                        icon="PencilIcon"
                        title="Editar"
                        variant="warn"
                      />
                      <IconButton
                        icon="TrashIcon"
                        title="Eliminar"
                        variant="danger"
                        @click="confirmDelete(l)"
                      />
                    </div>
                  </td>
                </tr>

                <tr v-if="(proy.locales?.length ?? 0) === 0">
                  <td colspan="8" class="px-6 py-10 text-center text-sm text-gray-500">
                    Este proyecto no tiene locales.
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </AppCard>

        <!-- Empty total -->
        <AppCard v-if="(proyectos.data?.length ?? 0) === 0" padding="md">
          <div class="py-6 text-center">
            <MagnifyingGlassIcon class="w-8 h-8 mx-auto text-brand-700" />
            <p class="mt-3 text-sm font-semibold text-gray-900">Sin resultados</p>
            <p class="mt-1 text-sm text-gray-600">
              No hay proyectos con locales que coincidan con tu búsqueda.
            </p>
            <button
              v-if="localFilters.search"
              @click="clearSearch"
              class="mt-4 rounded-xl bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700 transition"
            >
              Limpiar búsqueda
            </button>
          </div>
        </AppCard>
      </div>

      <!-- Paginación -->
      <AppCard padding="md">
        <div class="flex items-center justify-between gap-3">
          <p class="text-sm text-gray-600">
            Página <span class="font-semibold text-gray-900">{{ proyectos.current_page }}</span> de
            <span class="font-semibold text-gray-900">{{ proyectos.last_page }}</span>
          </p>

          <div class="flex items-center gap-2">
            <button
              :disabled="!proyectos.prev_page_url"
              @click="cambiarPagina(proyectos.current_page - 1)"
              class="rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-800 hover:bg-gray-50 transition disabled:opacity-50"
            >
              Anterior
            </button>
            <button
              :disabled="!proyectos.next_page_url"
              @click="cambiarPagina(proyectos.current_page + 1)"
              class="rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-800 hover:bg-gray-50 transition disabled:opacity-50"
            >
              Siguiente
            </button>
          </div>
        </div>
      </AppCard>

      <!-- Confirm delete -->
      <ConfirmDialog
        :open="deleteModal.open"
        title="Eliminar local"
        :message="deleteMessage"
        cancel-text="Cancelar"
        confirm-text="Eliminar"
        @cancel="deleteModal.open = false"
        @confirm="doDelete"
      />
    </div>
  </TopBannerLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'

import TopBannerLayout from '@/Components/TopBannerLayout.vue'
import AppCard from '@/Components/AppCard.vue'
import PageHeader from '@/Components/PageHeader.vue'
import ButtonPrimary from '@/Components/ButtonPrimary.vue'
import ButtonSecondary from '@/Components/ButtonSecondary.vue'
import QuickSearch from '@/Components/QuickSearch.vue'
import IconButton from '@/Components/IconButton.vue'
import ConfirmDialog from '@/Components/ConfirmDialog.vue'

import { PlusIcon, MagnifyingGlassIcon, BuildingStorefrontIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  proyectos: { type: Object, default: () => ({ data: [] }) }, // paginado
  filters: { type: Object, default: () => ({}) },
  empleado: { type: Object, default: null },
})

const localFilters = ref({
  search: props.filters?.search || '',
})

function clearSearch() {
  localFilters.search = ''
  doSearch()
}

function doSearch() {
  router.get(
    route('admin.locales.index'),
    { search: localFilters.value.search },
    { preserveState: true, replace: true }
  )
}

function cambiarPagina(pagina) {
  router.get(
    route('admin.locales.index'),
    { search: localFilters.value.search, page: pagina },
    { preserveState: true, replace: true }
  )
}

/** Expand/collapse por proyecto */
// const openProjects = ref(new Set())

// function isOpen(id) {
//   return openProjects.value.has(Number(id))
// }
// function toggleProyecto(id) {
//   const key = Number(id)
//   const s = new Set(openProjects.value)
//   if (s.has(key)) s.delete(key)
//   else s.add(key)
//   openProjects.value = s
// }

function estadoBadgeClass(nombre) {
  const n = (nombre || '').toLowerCase()
  if (n.includes('dispon') || n.includes('libre'))
    return 'border-green-200 bg-green-50 text-green-800'
  if (n.includes('vend') || n.includes('separ')) return 'border-blue-200 bg-blue-50 text-blue-800'
  if (n.includes('bloq') || n.includes('paus') || n.includes('susp'))
    return 'border-amber-200 bg-amber-50 text-amber-800'
  return 'border-gray-200 bg-gray-50 text-gray-700'
}

/** Delete flow */
const deleteModal = ref({ open: false, local: null })

const deleteMessage = computed(() => {
  const n = deleteModal.value.local?.numero
  return `¿Seguro que deseas eliminar el local “${n || '—'}”? Esta acción no se puede deshacer.`
})

function confirmDelete(local) {
  deleteModal.value = { open: true, local }
}

function doDelete() {
  if (!deleteModal.value.local) return
  router.delete(`/locales/${deleteModal.value.local.id_local}`, {
    onFinish: () => (deleteModal.value = { open: false, local: null }),
  })
}

function formatArea(val) {
  if (val === null || val === undefined) return '—'
  const num = Number(val)
  if (isNaN(num)) return '—'
  return `${num.toLocaleString('es-CO', { maximumFractionDigits: 2 })}`
}
function formatCurrency(val) {
  if (val === null || val === undefined) return '—'
  const num = Number(val)
  if (isNaN(num)) return '—'
  return new Intl.NumberFormat('es-CO', {
    style: 'currency',
    currency: 'COP',
    maximumFractionDigits: 0,
  }).format(num)
}
</script>
