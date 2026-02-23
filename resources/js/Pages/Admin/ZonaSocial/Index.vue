<!-- resources/js/Pages/Admin/ZonaSocial/Index.vue -->
<template>
  <TopBannerLayout :empleado="empleado">
    <div class="space-y-6">
      <PageHeader
        title="Zonas sociales"
        kicker="Inventario del proyecto"
        subtitle="Crea, consulta y administra las zonas sociales asociadas a los proyectos."
      >
        <template #actions>
          <ButtonPrimary :href="route('zonas-sociales.create')">
            <PlusIcon class="w-5 h-5" />
            Nueva zona
          </ButtonPrimary>
        </template>
      </PageHeader>

      <!-- Controles -->
      <AppCard padding="md">
        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
          <div class="min-w-0">
            <p class="text-sm text-gray-600">
              Proyectos en esta página:
              <span class="font-semibold text-gray-900">{{ proyectosAgrupados.length }}</span>
            </p>
          </div>

          <div class="flex w-full flex-col gap-2 md:w-[520px] md:flex-row md:items-center">
            <div class="flex-1">
              <QuickSearch
                v-model="search"
                placeholder="Buscar por proyecto, zona o descripción…"
                @keyup.enter="noop"
              />
            </div>

            <button
              type="button"
              @click="noop"
              class="rounded-xl bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700 transition"
            >
              Buscar
            </button>
          </div>
        </div>
      </AppCard>

      <!-- Listado por proyecto -->
      <div class="space-y-4">
        <AppCard
          v-for="p in proyectosAgrupados"
          :key="p.key"
          padding="none"
        >
          <div class="px-6 py-5 border-b border-gray-200 flex items-start justify-between gap-4">
            <div class="min-w-0">
              <p class="text-xs text-gray-600">Proyecto</p>
              <p class="text-lg font-semibold text-gray-900 truncate">
                {{ p.nombre || '—' }}
              </p>
              <p class="text-sm text-gray-600 mt-0.5">
                Ciudad: <span class="font-medium text-gray-900">{{ p.ciudad || '—' }}</span>
              </p>
            </div>

            <div class="shrink-0">
              <span class="inline-flex items-center rounded-full border border-brand-200 bg-brand-50 px-3 py-1 text-xs font-semibold text-brand-800">
                {{ p.zonas.length }} zona(s)
              </span>
            </div>
          </div>

          <div class="overflow-x-auto">
            <table class="min-w-[980px] w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                    Zona social
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                    Descripción
                  </th>
                  <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">
                    Acciones
                  </th>
                </tr>
              </thead>

              <tbody class="divide-y divide-gray-200 bg-white">
                <tr
                  v-for="z in p.zonas"
                  :key="z.id_zona_social"
                  class="hover:bg-brand-50/40 transition"
                >
                  <td class="px-6 py-4">
                    <div class="flex items-start gap-3">
                      <span class="mt-0.5 rounded-2xl border border-brand-300/60 bg-brand-200 p-2">
                        <MapPinIcon class="w-5 h-5 text-brand-900" />
                      </span>

                      <div class="min-w-0">
                        <p class="font-semibold text-gray-900 truncate">
                          {{ z.nombre || '—' }}
                        </p>
                        <p class="text-xs text-gray-600">
                          ID: {{ z.id_zona_social }}
                        </p>
                      </div>
                    </div>
                  </td>

                  <td class="px-6 py-4 text-sm text-gray-900">
                    <p class="line-clamp-2 max-w-[680px]">
                      {{ z.descripcion || '—' }}
                    </p>
                  </td>

                  <td class="px-6 py-4">
                    <div class="flex items-center justify-end gap-2">
                      <IconButton
                        :href="route('zonas-sociales.show', z.id_zona_social)"
                        icon="EyeIcon"
                        title="Ver"
                        variant="info"
                      />
                      <IconButton
                        :href="route('zonas-sociales.edit', z.id_zona_social)"
                        icon="PencilIcon"
                        title="Editar"
                        variant="warn"
                      />
                      <IconButton
                        icon="TrashIcon"
                        title="Eliminar"
                        variant="danger"
                        @click="confirmDelete(z)"
                      />
                    </div>
                  </td>
                </tr>

                <tr v-if="p.zonas.length === 0">
                  <td colspan="3" class="px-6 py-10 text-center text-sm text-gray-500">
                    Sin zonas para este proyecto
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </AppCard>

        <!-- Empty global -->
        <AppCard v-if="proyectosAgrupados.length === 0" padding="md">
          <div class="py-10 text-center">
            <MagnifyingGlassIcon class="w-8 h-8 mx-auto text-brand-700" />
            <p class="mt-3 text-sm font-semibold text-gray-900">Sin resultados</p>
            <p class="mt-1 text-sm text-gray-600">No hay zonas sociales que coincidan con tu búsqueda.</p>

            <button
              v-if="search"
              type="button"
              @click="search=''"
              class="mt-4 rounded-xl bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700 transition"
            >
              Limpiar búsqueda
            </button>
          </div>
        </AppCard>
      </div>

      <ConfirmDialog
        :open="deleteModal.open"
        title="Eliminar zona social"
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
  MapPinIcon,
  MagnifyingGlassIcon,
} from '@heroicons/vue/24/outline'

const props = defineProps({
  zonas: { type: Array, default: () => [] },
  empleado: { type: Object, default: null },
})

const search = ref('')

/**
 * Agrupa zonas por proyecto y filtra por búsqueda (proyecto/zona/descripcion)
 * Sin cambiar la lógica de datos: usa props.zonas tal como viene del controller.
 */
const proyectosAgrupados = computed(() => {
  const q = search.value.toLowerCase().trim()

  const visibles = !q
    ? props.zonas
    : props.zonas.filter((z) => {
        const nombre = (z.nombre || '').toLowerCase()
        const desc = (z.descripcion || '').toLowerCase()
        const proyecto = (z.proyecto || '').toLowerCase()
        const ciudad = (z.ubicacion || '').toLowerCase()
        return (
          nombre.includes(q) ||
          desc.includes(q) ||
          proyecto.includes(q) ||
          ciudad.includes(q)
        )
      })

  const map = new Map()

  for (const z of visibles) {
    const key = `${z.proyecto || '—'}__${z.ubicacion || '—'}`
    if (!map.has(key)) {
      map.set(key, {
        key,
        nombre: z.proyecto || '—',
        ciudad: z.ubicacion || '—',
        zonas: [],
      })
    }
    map.get(key).zonas.push(z)
  }

  // Ordenar por nombre de proyecto y dentro por nombre de zona
  const arr = Array.from(map.values()).sort((a, b) => a.nombre.localeCompare(b.nombre, 'es'))
  arr.forEach((p) => p.zonas.sort((a, b) => (a.nombre || '').localeCompare(b.nombre || '', 'es')))
  return arr
})

function noop() {
  // búsqueda es reactiva, no hace falta navegar
}

/** Delete flow */
const deleteModal = ref({ open: false, zona: null })

const deleteMessage = computed(() => {
  const nombre = deleteModal.value.zona?.nombre
  return `¿Seguro que deseas eliminar la zona social “${nombre || '—'}”? Esta acción no se puede deshacer.`
})

function confirmDelete(zona) {
  deleteModal.value = { open: true, zona }
}

function doDelete() {
  const zona = deleteModal.value.zona
  if (!zona) return
  router.delete(route('zonas-sociales.destroy', zona.id_zona_social), {
    onFinish: () => {
      deleteModal.value = { open: false, zona: null }
    },
  })
}
</script>
