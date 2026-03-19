<template>
  <TopBannerLayout :empleado="empleado">
    <div class="space-y-6">
      <PageHeader
        title="Políticas de comisión"
        kicker="Módulo comercial"
        subtitle="Crea, consulta y administra porcentajes de comisión por proyecto y cargo."
      >
        <template #actions>
          <ButtonPrimary href="/politicas-precio-proyecto/crear?proyecto=${pid}">
            <PlusIcon class="w-5 h-5" />
            Nueva política
          </ButtonPrimary>
        </template>
      </PageHeader>

      <AppCard padding="md">
        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
          <div class="min-w-0">
            <p class="text-sm text-gray-600">
              Total:
              <span class="font-semibold text-gray-900">{{ filtered.length }}</span>
            </p>
          </div>

          <div class="w-full md:w-[520px]">
            <QuickSearch
              v-model="search"
              placeholder="Buscar por proyecto, cargo, tipo o porcentaje…"
            />
          </div>
        </div>
      </AppCard>

      <AppCard padding="none">
        <div class="overflow-x-auto">
          <table class="min-w-[1100px] w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th
                  class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider"
                >
                  Proyecto
                </th>
                <th
                  class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider"
                >
                  Empleado
                </th>
                <th
                  class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider"
                >
                  Tipo comisión
                </th>
                <th
                  class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider"
                >
                  Porcentaje
                </th>
                <th
                  class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider"
                >
                  Vigencia
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
                v-for="p in filtered"
                :key="p.id_politica_comision"
                class="hover:bg-brand-50/40 transition"
              >
                <td class="px-6 py-4">
                  <div class="flex items-start gap-3">
                    <span class="mt-0.5 rounded-2xl border border-brand-300/60 bg-brand-200 p-2">
                      <TagIcon class="w-5 h-5 text-brand-900" />
                    </span>

                    <div class="min-w-0">
                      <p class="font-semibold text-gray-900 truncate">
                        {{ p.proyecto?.nombre || '—' }}
                      </p>
                      <p class="text-xs text-gray-600">ID: {{ p.id_politica_comision }}</p>
                    </div>
                  </div>
                </td>

                <td class="px-6 py-4">
                  <div class="text-sm font-semibold text-gray-900">
                    {{ p.empleado?.nombre || '—' }}
                  </div>
                  <div class="text-xs text-gray-600">
                    {{ p.empleado?.cargo || '—' }}
                  </div>
                </td>

                <td class="px-6 py-4 text-sm text-gray-900">
                  {{ tipoFmt(p.tipo_comision) }}
                </td>

                <td class="px-6 py-4 text-sm text-gray-900">
                  {{ porcentajeFmt(p.porcentaje) }}
                </td>

                <td class="px-6 py-4 text-sm text-gray-900">
                  {{ fechaFmt(p.vigente_desde) }} - {{ fechaFmt(p.vigente_hasta) }}
                </td>

                <td class="px-6 py-4">
                  <div class="flex items-center justify-end gap-2">
                    <IconButton
                      :href="`/politicas-comision/${p.id_politica_comision}`"
                      icon="EyeIcon"
                      title="Ver"
                      variant="info"
                    />
                    <IconButton
                      :href="`/politicas-comision/${p.id_politica_comision}/edit`"
                      icon="PencilIcon"
                      title="Editar"
                      variant="warn"
                    />
                    <IconButton
                      icon="TrashIcon"
                      title="Eliminar"
                      variant="danger"
                      @click="askDelete(p.id_politica_comision)"
                    />
                  </div>
                </td>
              </tr>

              <tr v-if="filtered.length === 0">
                <td colspan="6" class="px-6 py-12 text-center">
                  <div class="mx-auto max-w-md">
                    <MagnifyingGlassIcon class="w-8 h-8 mx-auto text-brand-700" />
                    <p class="mt-3 text-sm font-semibold text-gray-900">Sin resultados</p>
                    <p class="mt-1 text-sm text-gray-600">
                      No hay políticas de comisión que coincidan con tu búsqueda.
                    </p>
                    <button
                      v-if="search"
                      @click="search = ''"
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

        <div class="flex items-center justify-between gap-3 px-6 py-4 border-t border-gray-200">
          <p class="text-sm text-gray-600">
            Mostrando
            <span class="font-semibold text-gray-900">{{ filtered.length }}</span> registros
          </p>
        </div>
      </AppCard>

      <ConfirmDialog
        :open="showConfirmDelete"
        title="Confirmar eliminación"
        message="¿Estás seguro de eliminar esta política de comisión? Esta acción no se puede deshacer."
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
import { router } from '@inertiajs/vue3'

import TopBannerLayout from '@/Components/TopBannerLayout.vue'
import AppCard from '@/Components/AppCard.vue'
import PageHeader from '@/Components/PageHeader.vue'
import ButtonPrimary from '@/Components/ButtonPrimary.vue'
import QuickSearch from '@/Components/QuickSearch.vue'
import IconButton from '@/Components/IconButton.vue'
import ConfirmDialog from '@/Components/ConfirmDialog.vue'

import { PlusIcon, MagnifyingGlassIcon, TagIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  politicas: { type: Array, default: () => [] },
  empleado: { type: Object, default: null },
})

const search = ref('')

const filtered = computed(() => {
  const q = search.value.toLowerCase().trim()
  if (!q) return props.politicas

  return props.politicas.filter((p) => {
    const id = String(p.id_politica_comision || '')
    const proyecto = (p.proyecto?.nombre || '').toLowerCase()
    const empleado = (p.empleado?.nombre || '').toLowerCase()
    const cargo = (p.empleado?.cargo || '').toLowerCase()
    const tipo = tipoFmt(p.tipo_comision).toLowerCase()
    const porcentaje = String(p.porcentaje ?? '')
    return (
      id.includes(q) ||
      proyecto.includes(q) ||
      empleado.includes(q) ||
      cargo.includes(q) ||
      tipo.includes(q) ||
      porcentaje.includes(q)
    )
  })
})

function porcentajeFmt(val) {
  if (val === null || val === undefined || val === '') return '—'
  const n = Number(val)
  if (Number.isNaN(n)) return '—'
  const s = String(val)
  return s.includes('.') ? `${parseFloat(s)}%` : `${n}%`
}

function tipoFmt(tipo) {
  if (!tipo) return '—'
  if (tipo === 'venta_propia') return 'Venta propia'
  if (tipo === 'venta_equipo') return 'Venta del equipo'
  return tipo
}

function fechaFmt(val) {
  if (!val) return '—'
  return String(val).split('T')[0].split(' ')[0]
}

const politicaAEliminar = ref(null)
const showConfirmDelete = ref(false)

function askDelete(id) {
  politicaAEliminar.value = id
  showConfirmDelete.value = true
}

function cancelarEliminar() {
  showConfirmDelete.value = false
  politicaAEliminar.value = null
}

function confirmarEliminar() {
  if (!politicaAEliminar.value) return
  router.delete(`/politicas-comision/${politicaAEliminar.value}`, {
    onFinish: () => {
      showConfirmDelete.value = false
      politicaAEliminar.value = null
    },
  })
}
</script>
