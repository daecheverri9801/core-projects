<template>
  <SidebarBannerLayout :empleado="empleado">
    <div class="space-y-6">
      <PageHeader
        title="Proyectos"
        kicker="Panel administrador"
        subtitle="Crea, consulta y administra la configuración general de cada proyecto."
      >
        <template #actions>
          <ButtonPrimary href="/proyectos/create">
            <PlusIcon class="w-5 h-5" />
            Crear proyecto
          </ButtonPrimary>
        </template>
      </PageHeader>

      <!-- Controles -->
      <AppCard padding="md">
        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
          <div class="min-w-0">
            <p class="text-sm text-gray-600">
              Total en esta página:
              <span class="font-semibold text-gray-900">{{ proyectos.data?.length ?? 0 }}</span>
            </p>
          </div>

          <div class="w-full md:w-[420px]">
            <QuickSearch v-model="q" placeholder="Buscar por nombre, estado, ciudad o dirección…" />
          </div>
        </div>
      </AppCard>

      <!-- Tabla -->
      <AppCard padding="none">
        <div class="overflow-x-auto">
          <table class="min-w-[980px] w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                  Proyecto
                </th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                  Estado
                </th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                  Ubicación
                </th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                  Inicio
                </th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                  Finalización
                </th>
                <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">
                  Acciones
                </th>
              </tr>
            </thead>

            <tbody class="divide-y divide-gray-200 bg-white">
              <tr
                v-for="proyecto in filtered"
                :key="proyecto.id_proyecto"
                class="hover:bg-brand-50/40 transition"
              >
                <td class="px-6 py-4">
                  <div class="flex items-start gap-3">
                    <span class="mt-0.5 rounded-2xl border border-brand-300/60 bg-brand-200 p-2">
                      <FolderIcon class="w-5 h-5 text-brand-900" />
                    </span>

                    <div class="min-w-0">
                      <p class="font-semibold text-gray-900 truncate">
                        {{ proyecto.nombre }}
                      </p>
                      <p class="text-xs text-gray-600">
                        ID: {{ proyecto.id_proyecto }}
                      </p>
                    </div>
                  </div>
                </td>

                <td class="px-6 py-4 text-sm text-gray-900">
                  <span
                    class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-semibold"
                    :class="estadoBadgeClass(proyecto.estado_proyecto?.nombre)"
                  >
                    {{ proyecto.estado_proyecto?.nombre || '—' }}
                  </span>
                </td>

                <td class="px-6 py-4 text-sm text-gray-900">
                  <p class="truncate max-w-[360px]">
                    {{ proyecto.ubicacion?.direccion || '—' }}
                  </p>
                  <p class="text-xs text-gray-600 truncate max-w-[360px]">
                    {{ proyecto.ubicacion?.ciudad?.nombre || '—' }}
                  </p>
                </td>

                <td class="px-6 py-4 text-sm text-gray-900">
                  {{ proyecto.fecha_inicio || '—' }}
                </td>

                <td class="px-6 py-4 text-sm text-gray-900">
                  {{ proyecto.fecha_finalizacion || '—' }}
                </td>

                <td class="px-6 py-4">
                  <div class="flex items-center justify-end gap-2">
                    <IconButton
                      :href="`/proyectos/${proyecto.id_proyecto}`"
                      icon="EyeIcon"
                      title="Ver"
                      variant="info"
                    />
                    <IconButton
                      :href="`/proyectos/${proyecto.id_proyecto}/edit`"
                      icon="PencilIcon"
                      title="Editar"
                      variant="warn"
                    />
                    <IconButton
                      icon="TrashIcon"
                      title="Eliminar"
                      variant="danger"
                      @click="askDelete(proyecto.id_proyecto)"
                    />
                  </div>
                </td>
              </tr>

              <!-- Empty -->
              <tr v-if="filtered.length === 0">
                <td colspan="6" class="px-6 py-12 text-center">
                  <div class="mx-auto max-w-md">
                    <MagnifyingGlassIcon class="w-8 h-8 mx-auto text-brand-700" />
                    <p class="mt-3 text-sm font-semibold text-gray-900">Sin resultados</p>
                    <p class="mt-1 text-sm text-gray-600">
                      No hay proyectos que coincidan con tu búsqueda.
                    </p>
                    <button
                      v-if="q"
                      @click="q = ''"
                      class="mt-4 rounded-xl bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700 transition"
                    >
                      Limpiar búsqueda
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Paginación -->
        <div class="flex items-center justify-between gap-3 px-6 py-4 border-t border-gray-200">
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
        :open="showConfirmDelete"
        title="Confirmar eliminación"
        message="¿Estás seguro de eliminar este proyecto? Esta acción no se puede deshacer."
        cancel-text="Cancelar"
        confirm-text="Eliminar"
        @cancel="cancelarEliminar"
        @confirm="confirmarEliminar"
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

import { PlusIcon, FolderIcon, MagnifyingGlassIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  proyectos: Object,
  empleado: Object,
})

const q = ref('')

const filtered = computed(() => {
  const data = props.proyectos?.data ?? []
  const query = q.value.trim().toLowerCase()
  if (!query) return data

  return data.filter((p) => {
    const nombre = (p.nombre || '').toLowerCase()
    const estado = (p.estado_proyecto?.nombre || '').toLowerCase()
    const dir = (p.ubicacion?.direccion || '').toLowerCase()
    const ciudad = (p.ubicacion?.ciudad?.nombre || '').toLowerCase()
    return (
      nombre.includes(query) ||
      estado.includes(query) ||
      dir.includes(query) ||
      ciudad.includes(query)
    )
  })
})

function estadoBadgeClass(nombre) {
  const n = (nombre || '').toLowerCase()
  // Ajusta estas reglas a tus estados reales si quieres
  if (n.includes('activo') || n.includes('ejec')) {
    return 'border-green-200 bg-green-50 text-green-800'
  }
  if (n.includes('final') || n.includes('termin')) {
    return 'border-blue-200 bg-blue-50 text-blue-800'
  }
  if (n.includes('paus') || n.includes('susp')) {
    return 'border-amber-200 bg-amber-50 text-amber-800'
  }
  return 'border-gray-200 bg-gray-50 text-gray-700'
}

function cambiarPagina(pagina) {
  router.visit(`/proyectos?page=${pagina}`)
}

/** Delete flow */
const proyectoAEliminar = ref(null)
const showConfirmDelete = ref(false)

function askDelete(id) {
  proyectoAEliminar.value = id
  showConfirmDelete.value = true
}

function cancelarEliminar() {
  showConfirmDelete.value = false
  proyectoAEliminar.value = null
}

function confirmarEliminar() {
  if (!proyectoAEliminar.value) return
  router.delete(`/proyectos/${proyectoAEliminar.value}`, {
    onFinish: () => {
      showConfirmDelete.value = false
      proyectoAEliminar.value = null
    },
  })
}
</script>
