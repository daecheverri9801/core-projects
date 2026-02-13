<template>
  <TopBannerLayout :empleado="empleado" panel-name="Panel administrador">
    <div class="space-y-6">
      <PageHeader
        title="Proyectos"
        subtitle="Crea, consulta y administra la configuración general de cada proyecto."
      >
        <template #actions>
          <Link :href="route('proyectos.create')" class="btn-primary"> Crear proyecto </Link>
        </template>
      </PageHeader>

      <!-- Controles -->
      <AppCard padding="md">
        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
          <div class="min-w-0">
            <p class="text-sm text-gray-600">
              Mostrando:
              <span class="font-semibold text-gray-900">{{ filtered.length }}</span>
              de
              <span class="font-semibold text-gray-900">{{
                proyectos.total ?? proyectos.data?.length ?? 0
              }}</span>
              proyectos
            </p>
            <p class="text-xs text-gray-500 mt-1">
              Página {{ proyectos.current_page }} de {{ proyectos.last_page }}
            </p>
          </div>

          <div class="w-full md:w-[460px]">
            <QuickSearch v-model="q" placeholder="Buscar por nombre, estado, ciudad o dirección…" />
          </div>
        </div>
      </AppCard>

      <!-- LISTADO (Cards) -->
      <section class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <AppCard
          v-for="proyecto in filtered"
          :key="proyecto.id_proyecto"
          padding="md"
          class="group relative overflow-hidden"
        >
          <!-- Glow -->
          <div
            class="absolute -top-24 -right-24 h-56 w-56 rounded-full bg-brand-600/10 blur-2xl opacity-0 group-hover:opacity-100 transition"
          ></div>

          <div class="relative flex flex-col gap-4">
            <!-- Header card -->
            <div class="flex items-start justify-between gap-4">
              <div class="min-w-0 flex items-start gap-3">
                <span class="mt-0.5 rounded-2xl border border-brand-300/60 bg-brand-200 p-2">
                  <FolderIcon class="w-5 h-5 text-brand-900" />
                </span>

                <div class="min-w-0">
                  <div class="flex items-center gap-2 flex-wrap">
                    <h3 class="text-base font-semibold text-gray-900 truncate max-w-[420px]">
                      {{ proyecto.nombre }}
                    </h3>

                    <span
                      class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-semibold"
                      :class="estadoBadgeClass(proyecto.estado_proyecto?.nombre)"
                    >
                      {{ proyecto.estado_proyecto?.nombre || '—' }}
                    </span>
                  </div>

                  <p class="text-xs text-gray-500 mt-1">
                    ID: <span class="font-semibold text-gray-700">{{ proyecto.id_proyecto }}</span>
                  </p>
                </div>
              </div>

              <!-- Acciones -->
              <div class="flex items-center gap-2 shrink-0">
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
            </div>

            <!-- Body card: datos principales -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <div class="rounded-2xl border border-brand-200/60 bg-white p-4">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Ubicación</p>
                <p class="mt-1 text-sm font-semibold text-gray-900 truncate">
                  {{ proyecto.ubicacion?.ciudad?.nombre || '—' }}
                </p>
                <p class="text-xs text-gray-600 truncate">
                  {{ proyecto.ubicacion?.direccion || '—' }}
                </p>
              </div>

              <div class="rounded-2xl border border-brand-200/60 bg-white p-4">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Fechas</p>
                <div class="mt-1 flex flex-col gap-1">
                  <p class="text-sm text-gray-900">
                    <span class="text-gray-500">Inicio:</span>
                    <span class="font-semibold">{{ proyecto.fecha_inicio || '—' }}</span>
                  </p>
                  <p class="text-sm text-gray-900">
                    <span class="text-gray-500">Finalización:</span>
                    <span class="font-semibold">{{ proyecto.fecha_finalizacion || '—' }}</span>
                  </p>
                </div>
              </div>
            </div>

            <!-- Footer: CTA -->
            <div class="flex items-center justify-between pt-1">
              <p class="text-xs text-gray-500">
                Gestiona torres, políticas y configuración desde el detalle.
              </p>

              <Link
                :href="`/proyectos/${proyecto.id_proyecto}`"
                class="inline-flex items-center gap-2 text-sm font-semibold text-brand-900 hover:underline"
              >
                Entrar
                <ArrowRightIcon class="w-4 h-4 group-hover:translate-x-0.5 transition" />
              </Link>
            </div>
          </div>

          <div class="mt-4 h-1 w-full bg-brand-200 group-hover:bg-brand-600/60 transition"></div>
        </AppCard>

        <!-- Empty -->
        <AppCard v-if="filtered.length === 0" padding="md" class="lg:col-span-2">
          <div class="py-10 text-center">
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
        </AppCard>
      </section>

      <!-- Paginación -->
      <AppCard padding="md">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
          <p class="text-sm text-gray-600">
            Página <span class="font-semibold text-gray-900">{{ proyectos.current_page }}</span> de
            <span class="font-semibold text-gray-900">{{ proyectos.last_page }}</span>
          </p>

          <div class="flex items-center justify-end gap-2">
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
  MagnifyingGlassIcon,
  ArrowRightIcon,
} from '@heroicons/vue/24/outline'

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
  if (n.includes('activo') || n.includes('ejec'))
    return 'border-green-200 bg-green-50 text-green-800'
  if (n.includes('final') || n.includes('termin')) return 'border-blue-200 bg-blue-50 text-blue-800'
  if (n.includes('paus') || n.includes('susp')) return 'border-amber-200 bg-amber-50 text-amber-800'
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
