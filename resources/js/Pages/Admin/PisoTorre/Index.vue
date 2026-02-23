<!-- resources/js/Pages/Admin/PisoTorre/Index.vue -->
<template>
  <TopBannerLayout :empleado="empleado">
    <div class="space-y-6">
      <PageHeader
        title="Pisos de Torre"
        kicker="Inventario del proyecto"
        subtitle="Visualiza los pisos agrupados por proyecto (torre, nivel, uso y aptos)."
      >
        <template #actions>
          <ButtonPrimary :href="route('pisostorre.create')">
            <PlusIcon class="w-5 h-5" />
            Nuevo piso
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
              Pisos en esta página:
              <span class="font-semibold text-gray-900">{{ totalPisosEnPagina }}</span>
              <span class="mx-2 text-gray-300">•</span>
              Aptos (conteo) en esta página:
              <span class="font-semibold text-gray-900">{{ totalAptosEnPagina }}</span>
            </p>
          </div>

          <div class="flex w-full flex-col gap-2 md:w-[620px] md:flex-row md:items-center">
            <div class="flex-1">
              <QuickSearch
                v-model="localFilters.search"
                placeholder="Buscar por torre, uso o nivel…"
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
                      Pisos: <span class="font-semibold text-gray-900">{{ proyecto.pisos?.length ?? 0 }}</span>
                      <span class="mx-2 text-gray-300">•</span>
                      Aptos: <span class="font-semibold text-gray-900">{{ sumAptos(proyecto.pisos) }}</span>
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

          <!-- Pisos del proyecto -->
          <div class="overflow-x-auto">
            <table class="min-w-[1040px] w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                    Torre
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                    Nivel
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                    Uso
                  </th>
                  <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">
                    Aptos
                  </th>
                  <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">
                    Acciones
                  </th>
                </tr>
              </thead>

              <tbody class="divide-y divide-gray-200 bg-white">
                <tr
                  v-for="piso in (proyecto.pisos || [])"
                  :key="piso.id_piso_torre"
                  class="hover:bg-brand-50/40 transition"
                >
                  <td class="px-6 py-4">
                    <div class="flex items-start gap-3">
                      <span class="mt-0.5 rounded-2xl border border-brand-300/60 bg-brand-200 p-2">
                        <BuildingOffice2Icon class="w-5 h-5 text-brand-900" />
                      </span>

                      <div class="min-w-0">
                        <p class="font-semibold text-gray-900 truncate">
                          {{ piso.torre || '—' }}
                        </p>
                        <p class="text-xs text-gray-600">
                          Piso ID: {{ piso.id_piso_torre }}
                        </p>
                      </div>
                    </div>
                  </td>

                  <td class="px-6 py-4 text-sm text-gray-900">
                    <span class="font-semibold">{{ piso.nivel ?? '—' }}</span>
                  </td>

                  <td class="px-6 py-4 text-sm text-gray-900">
                    {{ piso.uso || '—' }}
                  </td>

                  <td class="px-6 py-4 text-center">
                    <span
                      class="inline-flex items-center justify-center min-w-8 px-2 py-1 text-xs font-semibold rounded-full bg-brand-100 text-brand-800 border border-brand-200"
                    >
                      {{ piso.apartamentos_count ?? 0 }}
                    </span>
                  </td>

                  <td class="px-6 py-4">
                    <div class="flex items-center justify-end gap-2">
                      <IconButton
                        :href="route('pisostorre.show', piso.id_piso_torre)"
                        icon="EyeIcon"
                        title="Ver"
                        variant="info"
                      />
                      <IconButton
                        :href="route('pisostorre.edit', piso.id_piso_torre)"
                        icon="PencilIcon"
                        title="Editar"
                        variant="warn"
                      />
                      <IconButton
                        icon="TrashIcon"
                        title="Eliminar"
                        variant="danger"
                        @click="confirmDelete(piso)"
                      />
                    </div>
                  </td>
                </tr>

                <!-- Empty per project -->
                <tr v-if="(proyecto.pisos?.length ?? 0) === 0">
                  <td colspan="5" class="px-6 py-10 text-center">
                    <div class="mx-auto max-w-md">
                      <MagnifyingGlassIcon class="w-8 h-8 mx-auto text-brand-700" />
                      <p class="mt-3 text-sm font-semibold text-gray-900">Sin pisos</p>
                      <p class="mt-1 text-sm text-gray-600">
                        Este proyecto no tiene pisos registrados.
                      </p>
                      <Link
                        :href="route('pisostorre.create')"
                        class="mt-4 inline-flex items-center gap-2 rounded-xl bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700 transition"
                      >
                        <PlusIcon class="w-5 h-5" />
                        Crear piso
                      </Link>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </AppCard>

        <!-- Empty global -->
        <AppCard v-if="(proyectos.data?.length ?? 0) === 0" padding="none">
          <div class="px-6 py-12 text-center">
            <div class="mx-auto max-w-md">
              <MagnifyingGlassIcon class="w-8 h-8 mx-auto text-brand-700" />
              <p class="mt-3 text-sm font-semibold text-gray-900">Sin resultados</p>
              <p class="mt-1 text-sm text-gray-600">
                No hay proyectos con pisos que coincidan con tu búsqueda.
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
        title="Eliminar piso"
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
import { Link, router } from '@inertiajs/vue3'

import TopBannerLayout from '@/Components/TopBannerLayout.vue'
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

const totalPisosEnPagina = computed(() => {
  const data = props.proyectos?.data ?? []
  return data.reduce((acc, p) => acc + (p.pisos?.length ?? 0), 0)
})

const totalAptosEnPagina = computed(() => {
  const data = props.proyectos?.data ?? []
  return data.reduce((acc, p) => acc + sumAptos(p.pisos), 0)
})

function sumAptos(pisos = []) {
  return (pisos || []).reduce((acc, x) => acc + Number(x.apartamentos_count ?? 0), 0)
}

function doSearch() {
  router.get(
    route('pisostorre.index'),
    { search: localFilters.value.search },
    { preserveState: true, replace: true }
  )
}

function cambiarPagina(pagina) {
  router.get(
    route('pisostorre.index'),
    { search: localFilters.value.search, page: pagina },
    { preserveState: true, replace: true }
  )
}

/** Delete flow */
const deleteModal = ref({ open: false, piso: null })

const deleteMessage = computed(() => {
  const torre = deleteModal.value.piso?.torre || '—'
  const nivel = deleteModal.value.piso?.nivel ?? '—'
  return `¿Seguro que deseas eliminar el piso (Torre: ${torre}, Nivel: ${nivel})? Esta acción no se puede deshacer.`
})

function confirmDelete(piso) {
  deleteModal.value = { open: true, piso }
}

function doDelete() {
  if (!deleteModal.value.piso) return
  router.delete(route('pisostorre.destroy', deleteModal.value.piso.id_piso_torre), {
    onFinish: () => {
      deleteModal.value = { open: false, piso: null }
    },
  })
}
</script>
