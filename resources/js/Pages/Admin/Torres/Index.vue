<!-- resources/js/Pages/Admin/Torres/Index.vue -->
<template>
  <SidebarBannerLayout :empleado="empleado">
    <div class="space-y-6">
      <PageHeader
        title="Torres"
        kicker="Inventario del proyecto"
        subtitle="Visualiza las torres agrupadas por proyecto."
      >
        <template #actions>
          <ButtonPrimary :href="route('admin.torres.create')">
            <PlusIcon class="w-5 h-5" />
            Nueva torre
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
              <span class="mx-2 text-gray-300">•</span>
              Torres en esta página:
              <span class="font-semibold text-gray-900">{{ totalTorresEnPagina }}</span>
            </p>
          </div>

          <div class="flex w-full flex-col gap-2 md:w-[560px] md:flex-row md:items-center">
            <div class="flex-1">
              <QuickSearch
                v-model="localFilters.search"
                placeholder="Buscar por nombre de torre…"
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

      <!-- Listado por Proyecto -->
      <div class="space-y-4">
        <AppCard
          v-for="proyecto in proyectos.data"
          :key="proyecto.id_proyecto"
          padding="none"
        >
          <!-- Header proyecto -->
          <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-start justify-between gap-4">
              <div class="min-w-0">
                <div class="flex items-start gap-3">
                  <span class="mt-0.5 rounded-2xl border border-brand-300/60 bg-brand-200 p-2">
                    <FolderIcon class="w-5 h-5 text-brand-900" />
                  </span>
                  <div class="min-w-0">
                    <p class="font-semibold text-gray-900 truncate">
                      {{ proyecto.nombre || '—' }}
                    </p>
                    <p class="text-xs text-gray-600">
                      ID: {{ proyecto.id_proyecto }}
                      <span class="mx-2 text-gray-300">•</span>
                      Torres: <span class="font-semibold text-gray-900">{{ proyecto.torres?.length ?? 0 }}</span>
                    </p>
                  </div>
                </div>
              </div>

              <div class="shrink-0">
                <Link
                  v-if="proyecto.id_proyecto"
                  :href="`/proyectos/${proyecto.id_proyecto}`"
                  class="rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm font-semibold text-gray-800 hover:bg-gray-50 transition inline-flex items-center gap-2"
                  title="Ver proyecto"
                >
                  <EyeIcon class="w-5 h-5" />
                  Ver proyecto
                </Link>
              </div>
            </div>
          </div>

          <!-- Torres del proyecto -->
          <div class="overflow-x-auto">
            <table class="min-w-[980px] w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                    Torre
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                    Estado
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                    Pisos
                  </th>
                  <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">
                    Acciones
                  </th>
                </tr>
              </thead>

              <tbody class="divide-y divide-gray-200 bg-white">
                <tr
                  v-for="torre in (proyecto.torres || [])"
                  :key="torre.id_torre"
                  class="hover:bg-brand-50/40 transition"
                >
                  <td class="px-6 py-4">
                    <div class="flex items-start gap-3">
                      <span class="mt-0.5 rounded-2xl border border-brand-300/60 bg-brand-200 p-2">
                        <BuildingOffice2Icon class="w-5 h-5 text-brand-900" />
                      </span>

                      <div class="min-w-0">
                        <p class="font-semibold text-gray-900 truncate">
                          {{ torre.nombre_torre || '—' }}
                        </p>
                        <p class="text-xs text-gray-600">
                          ID: {{ torre.id_torre }}
                        </p>
                      </div>
                    </div>
                  </td>

                  <td class="px-6 py-4 text-sm">
                    <span
                      class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-semibold"
                      :class="estadoBadgeClass(torre.estado?.nombre)"
                    >
                      {{ torre.estado?.nombre || '—' }}
                    </span>
                  </td>

                  <td class="px-6 py-4 text-sm text-gray-900">
                    {{ torre.numero_pisos ?? '—' }}
                  </td>

                  <td class="px-6 py-4">
                    <div class="flex items-center justify-end gap-2">
                      <IconButton
                        :href="route('admin.torres.show', torre.id_torre)"
                        icon="EyeIcon"
                        title="Ver"
                        variant="info"
                      />
                      <IconButton
                        :href="route('admin.torres.edit', torre.id_torre)"
                        icon="PencilIcon"
                        title="Editar"
                        variant="warn"
                      />
                      <IconButton
                        icon="TrashIcon"
                        title="Eliminar"
                        variant="danger"
                        @click="confirmDelete(torre)"
                      />
                    </div>
                  </td>
                </tr>

                <!-- Empty per project -->
                <tr v-if="(proyecto.torres?.length ?? 0) === 0">
                  <td colspan="4" class="px-6 py-10 text-center">
                    <div class="mx-auto max-w-md">
                      <MagnifyingGlassIcon class="w-8 h-8 mx-auto text-brand-700" />
                      <p class="mt-3 text-sm font-semibold text-gray-900">Sin torres</p>
                      <p class="mt-1 text-sm text-gray-600">
                        Este proyecto no tiene torres registradas.
                      </p>
                      <Link
                        :href="route('admin.torres.create')"
                        class="mt-4 inline-flex items-center gap-2 rounded-xl bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700 transition"
                      >
                        <PlusIcon class="w-5 h-5" />
                        Crear torre
                      </Link>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Footer por proyecto: nada (se mantiene limpio) -->
        </AppCard>

        <!-- Empty global -->
        <AppCard v-if="(proyectos.data?.length ?? 0) === 0" padding="none">
          <div class="px-6 py-12 text-center">
            <div class="mx-auto max-w-md">
              <MagnifyingGlassIcon class="w-8 h-8 mx-auto text-brand-700" />
              <p class="mt-3 text-sm font-semibold text-gray-900">Sin resultados</p>
              <p class="mt-1 text-sm text-gray-600">
                No hay proyectos con torres que coincidan con tu búsqueda.
              </p>
              <button
                v-if="localFilters.search"
                @click="localFilters.search=''; doSearch()"
                class="mt-4 rounded-xl bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700 transition"
              >
                Limpiar búsqueda
              </button>
            </div>
          </div>
        </AppCard>
      </div>

      <!-- Paginación (por proyectos) -->
      <AppCard padding="none">
        <div class="flex items-center justify-between gap-3 px-6 py-4 border border-gray-200 rounded-2xl bg-white">
          <p class="text-sm text-gray-600">
            Página <span class="font-semibold text-gray-900">{{ proyectos.current_page }}</span>
            de <span class="font-semibold text-gray-900">{{ proyectos.last_page }}</span>
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
        title="Eliminar torre"
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
import { Link, router } from '@inertiajs/vue3'

import SidebarBannerLayout from '@/Components/SidebarBannerLayout.vue'
import AppCard from '@/Components/AppCard.vue'
import PageHeader from '@/Components/PageHeader.vue'
import ButtonPrimary from '@/Components/ButtonPrimary.vue'
import QuickSearch from '@/Components/QuickSearch.vue'
import IconButton from '@/Components/IconButton.vue'
import ConfirmDialog from '@/Components/ConfirmDialog.vue'

import {
  PlusIcon,
  FolderIcon,
  BuildingOffice2Icon,
  MagnifyingGlassIcon,
  EyeIcon,
} from '@heroicons/vue/24/outline'

const props = defineProps({
  proyectos: { type: Object, default: () => ({ data: [] }) }, // paginación por proyecto
  filters: { type: Object, default: () => ({}) },
  empleado: { type: Object, default: null },
})

const localFilters = ref({
  search: props.filters?.search || '',
})

const totalTorresEnPagina = computed(() => {
  const data = props.proyectos?.data ?? []
  return data.reduce((acc, p) => acc + (p.torres?.length ?? 0), 0)
})

function doSearch() {
  router.get(
    route('admin.torres.index'),
    { search: localFilters.value.search },
    { preserveState: true, replace: true }
  )
}

function cambiarPagina(pagina) {
  router.get(
    route('admin.torres.index'),
    { search: localFilters.value.search, page: pagina },
    { preserveState: true, replace: true }
  )
}

function estadoBadgeClass(nombre) {
  const n = (nombre || '').toLowerCase()
  if (n.includes('activo') || n.includes('ejec')) return 'border-green-200 bg-green-50 text-green-800'
  if (n.includes('final') || n.includes('termin')) return 'border-blue-200 bg-blue-50 text-blue-800'
  if (n.includes('paus') || n.includes('susp')) return 'border-amber-200 bg-amber-50 text-amber-800'
  return 'border-gray-200 bg-gray-50 text-gray-700'
}

/** Delete flow */
const deleteModal = ref({ open: false, torre: null })

const deleteMessage = computed(() => {
  const nombre = deleteModal.value.torre?.nombre_torre
  return `¿Seguro que deseas eliminar la torre “${nombre || '—'}”? Esta acción no se puede deshacer.`
})

function confirmDelete(torre) {
  deleteModal.value = { open: true, torre }
}

function doDelete() {
  if (!deleteModal.value.torre) return
  router.delete(route('admin.torres.destroy', deleteModal.value.torre.id_torre), {
    onFinish: () => {
      deleteModal.value = { open: false, torre: null }
    },
  })
}
</script>
