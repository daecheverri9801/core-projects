<!-- resources/js/Pages/Admin/Apartamento/Index.vue -->
<template>
  <TopBannerLayout :empleado="empleado" panel-name="Proyectos">
    <div class="space-y-6">
      <!-- Header -->
      <PageHeader
        title="Apartamentos"
        kicker="Inventario"
        subtitle="Consulta y administra los apartamentos por proyecto, torre y piso."
      >
        <template #actions>
          <ButtonPrimary href="/apartamentos/create">
            <PlusIcon class="w-5 h-5" />
            Nuevo apartamento
          </ButtonPrimary>
        </template>
      </PageHeader>

      <!-- Controles -->
      <AppCard padding="md">
        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
          <div class="min-w-0">
            <p class="text-sm text-gray-600">
              Total en esta página:
              <span class="font-semibold text-gray-900">{{ (groupedProyectos?.length ?? 0) }}</span>
              <span class="text-gray-500">
                ({{ apartamentos.length }} apartamentos)
              </span>
            </p>
          </div>

          <div class="flex w-full flex-col gap-2 md:w-[640px] md:flex-row md:items-center">
            <div class="flex-1">
              <QuickSearch
                v-model="search"
                placeholder="Buscar por número, proyecto, torre, piso, tipo, estado…"
                @keyup.enter="noop"
              />
            </div>

            <button
              type="button"
              class="rounded-xl bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700 transition"
              @click="noop"
              title="La búsqueda es instantánea"
            >
              Buscar
            </button>
          </div>
        </div>
      </AppCard>

      <!-- Listado por Proyecto -->
      <div class="space-y-4">
        <AppCard
          v-for="(proy, idx) in groupedProyectos"
          :key="proy.key || idx"
          padding="none"
        >
          <!-- Header Proyecto -->
          <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between px-6 py-4 border-b border-gray-200">
            <div class="min-w-0">
              <p class="text-xs text-gray-500">Proyecto</p>
              <p class="text-base font-semibold text-gray-900 truncate">
                {{ proy.nombre }}
              </p>
            </div>

            <div class="flex items-center gap-2">
              <span class="inline-flex items-center rounded-full border border-brand-200 bg-brand-50 px-2.5 py-1 text-xs font-semibold text-brand-800">
                {{ proy.items.length }} apto(s)
              </span>
              <span class="inline-flex items-center rounded-full border border-gray-200 bg-gray-50 px-2.5 py-1 text-xs font-semibold text-gray-700">
                {{ proy.torresCount }} torre(s)
              </span>
            </div>
          </div>

          <!-- Tabla -->
          <div class="overflow-x-auto">
            <table class="min-w-[1100px] w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                    Apartamento
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                    Torre
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                    Piso
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                    Tipo
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                    Estado
                  </th>
                  <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">
                    Valor total
                  </th>
                  <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">
                    Acciones
                  </th>
                </tr>
              </thead>

              <tbody class="divide-y divide-gray-200 bg-white">
                <tr
                  v-for="a in proy.items"
                  :key="a.id_apartamento"
                  class="hover:bg-brand-50/40 transition"
                >
                  <td class="px-6 py-4">
                    <div class="flex items-start gap-3">
                      <span class="mt-0.5 rounded-2xl border border-brand-300/60 bg-brand-200 p-2">
                        <HomeModernIcon class="w-5 h-5 text-brand-900" />
                      </span>

                      <div class="min-w-0">
                        <p class="font-semibold text-gray-900 truncate">
                          {{ a.numero || '—' }}
                        </p>
                        <p class="text-xs text-gray-600">
                          ID: {{ a.id_apartamento }}
                        </p>
                      </div>
                    </div>
                  </td>

                  <td class="px-6 py-4 text-sm text-gray-900">
                    <p class="truncate max-w-[320px]">{{ a.torre || '—' }}</p>
                  </td>

                  <td class="px-6 py-4 text-sm text-gray-900">
                    {{ a.piso ?? '—' }}
                  </td>

                  <td class="px-6 py-4 text-sm text-gray-900">
                    <p class="truncate max-w-[320px]">{{ a.tipo || '—' }}</p>
                  </td>

                  <td class="px-6 py-4 text-sm">
                    <span
                      class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-semibold"
                      :class="estadoBadgeClass(a.estado)"
                    >
                      {{ a.estado || '—' }}
                    </span>
                  </td>

                  <td class="px-6 py-4 text-sm text-gray-900 text-right">
                    {{ formatCurrency(a.valor_total) }}
                  </td>

                  <td class="px-6 py-4">
                    <div class="flex items-center justify-end gap-2">
                      <IconButton
                        :href="`/apartamentos/${a.id_apartamento}`"
                        icon="EyeIcon"
                        title="Ver"
                        variant="info"
                      />
                      <IconButton
                        :href="`/apartamentos/${a.id_apartamento}/edit`"
                        icon="PencilIcon"
                        title="Editar"
                        variant="warn"
                      />
                      <IconButton
                        icon="TrashIcon"
                        title="Eliminar"
                        variant="danger"
                        @click="confirmDelete(a.id_apartamento, a.numero)"
                      />
                    </div>
                  </td>
                </tr>

                <tr v-if="proy.items.length === 0">
                  <td colspan="7" class="px-6 py-10 text-center">
                    <p class="text-sm font-semibold text-gray-900">Sin resultados</p>
                    <p class="text-sm text-gray-600 mt-1">
                      No hay apartamentos que coincidan con tu búsqueda en este proyecto.
                    </p>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </AppCard>

        <!-- Empty global -->
        <AppCard v-if="groupedProyectos.length === 0" padding="md">
          <div class="text-center py-10">
            <MagnifyingGlassIcon class="w-8 h-8 mx-auto text-brand-700" />
            <p class="mt-3 text-sm font-semibold text-gray-900">Sin resultados</p>
            <p class="mt-1 text-sm text-gray-600">No hay apartamentos que coincidan con tu búsqueda.</p>
            <button
              v-if="search"
              @click="search=''"
              class="mt-4 rounded-xl bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700 transition"
            >
              Limpiar búsqueda
            </button>
          </div>
        </AppCard>
      </div>

      <!-- Confirm delete -->
      <ConfirmDialog
        :open="deleteModal.open"
        title="Eliminar apartamento"
        :message="deleteMessage"
        cancel-text="Cancelar"
        confirm-text="Eliminar"
        @cancel="deleteModal.open = false"
        @confirm="doDelete"
      />
    </div>

    <FlashMessages />
  </TopBannerLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'

import TopBannerLayout from '@/Components/TopBannerLayout.vue'
import FlashMessages from '@/Components/FlashMessages.vue'

import AppCard from '@/Components/AppCard.vue'
import PageHeader from '@/Components/PageHeader.vue'
import ButtonPrimary from '@/Components/ButtonPrimary.vue'
import QuickSearch from '@/Components/QuickSearch.vue'
import IconButton from '@/Components/IconButton.vue'
import ConfirmDialog from '@/Components/ConfirmDialog.vue'

import {
  PlusIcon,
  HomeModernIcon,
  MagnifyingGlassIcon,
} from '@heroicons/vue/24/outline'

const props = defineProps({
  apartamentos: { type: Array, default: () => [] },
  empleado: { type: Object, default: null },
})

const search = ref('')

const filtered = computed(() => {
  const q = search.value.toLowerCase().trim()
  if (!q) return props.apartamentos

  return props.apartamentos.filter((a) => {
    return (
      String(a.id_apartamento).includes(q) ||
      String(a.numero || '').toLowerCase().includes(q) ||
      String(a.proyecto || '').toLowerCase().includes(q) ||
      String(a.torre || '').toLowerCase().includes(q) ||
      String(a.piso ?? '').includes(q) ||
      String(a.tipo || '').toLowerCase().includes(q) ||
      String(a.estado || '').toLowerCase().includes(q) ||
      String(a.valor_total ?? '').toLowerCase().includes(q)
    )
  })
})

/**
 * Agrupar por proyecto (patrón usado en Torres / PisoTorre)
 * - Mantiene la lógica de datos actual (apartamentos ya mapeados en controller).
 * - Solo agrupa y presenta.
 */
const groupedProyectos = computed(() => {
  const map = new Map()

  for (const a of filtered.value) {
    const nombreProyecto = a.proyecto || '—'
    const key = nombreProyecto

    if (!map.has(key)) {
      map.set(key, {
        key,
        nombre: nombreProyecto,
        items: [],
        torresSet: new Set(),
      })
    }

    const bucket = map.get(key)
    bucket.items.push(a)
    if (a.torre) bucket.torresSet.add(a.torre)
  }

  const arr = Array.from(map.values()).map((p) => ({
    key: p.key,
    nombre: p.nombre,
    items: p.items,
    torresCount: p.torresSet.size,
  }))

  // Ordenar: proyectos con nombre y luego "—" al final
  return arr.sort((x, y) => {
    if (x.nombre === '—') return 1
    if (y.nombre === '—') return -1
    return x.nombre.localeCompare(y.nombre, 'es')
  })
})

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

function estadoBadgeClass(nombre) {
  const n = (nombre || '').toLowerCase()
  if (!n) return 'border-gray-200 bg-gray-50 text-gray-700'
  if (n.includes('dispon') || n.includes('libre')) return 'border-green-200 bg-green-50 text-green-800'
  if (n.includes('vend') || n.includes('ocup')) return 'border-blue-200 bg-blue-50 text-blue-800'
  if (n.includes('separ') || n.includes('proce') || n.includes('reser')) return 'border-amber-200 bg-amber-50 text-amber-800'
  if (n.includes('bloq') || n.includes('inact')) return 'border-red-200 bg-red-50 text-red-800'
  return 'border-gray-200 bg-gray-50 text-gray-700'
}

/** Delete flow */
const deleteModal = ref({ open: false, id: null, numero: null })

const deleteMessage = computed(() => {
  const n = deleteModal.value.numero
  return `¿Seguro que deseas eliminar el apartamento “${n || '—'}”? Esta acción no se puede deshacer.`
})

function confirmDelete(id, numero) {
  deleteModal.value = { open: true, id, numero }
}

function doDelete() {
  if (!deleteModal.value.id) return
  router.delete(`/apartamentos/${deleteModal.value.id}`, {
    onFinish: () => {
      deleteModal.value = { open: false, id: null, numero: null }
    },
  })
}

// Botón "Buscar" solo por UX (la búsqueda es instantánea)
function noop() {}
</script>
