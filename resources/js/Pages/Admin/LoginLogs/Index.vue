<script setup>
import { Head, router } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'

import TopBannerLayout from '@/Components/TopBannerLayout.vue'
import PageHeader from '@/Components/PageHeader.vue'
import AppCard from '@/Components/AppCard.vue'
import QuickSearch from '@/Components/QuickSearch.vue'

const props = defineProps({
  empleado: { type: Object, default: null },
  logs: { type: Object, default: () => ({ data: [] }) },
  filters: { type: Object, default: () => ({}) },
})

const q = ref(props.filters.q || '')
const desde = ref(props.filters.desde || '')
const hasta = ref(props.filters.hasta || '')

const rows = computed(() => props.logs?.data || [])

function aplicar() {
  router.get(
    '/admin/login-logs',
    { q: q.value, desde: desde.value, hasta: hasta.value },
    { preserveState: true, replace: true }
  )
}

watch([q], () => {
  // búsqueda rápida con debounce simple
  clearTimeout(window.__ll_t)
  window.__ll_t = setTimeout(() => aplicar(), 350)
})

function cambiarPagina(url) {
  if (!url) return
  router.visit(url, { preserveState: true, replace: true })
}

function formatDT(d) {
  if (!d) return '—'
  return new Date(d).toLocaleString('es-CO', {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
    hour: '2-digit',
    minute: '2-digit',
  })
}

function fullName(emp) {
  if (!emp) return '—'
  return [emp.nombre, emp.apellido].filter(Boolean).join(' ') || '—'
}
</script>

<template>
  <TopBannerLayout :empleado="empleado">
    <Head title="Login Logs" />

    <div class="space-y-6">
      <PageHeader
        title="Historial de inicios de sesión"
        subtitle="Registro de accesos al sistema (Admin)."
      />

      <!-- Filtros -->
      <AppCard padding="md">
        <div class="grid grid-cols-1 md:grid-cols-12 gap-3">
          <div class="md:col-span-6">
            <QuickSearch v-model="q" placeholder="Buscar por empleado (nombre/email) o IP…" />
          </div>

          <div class="md:col-span-3">
            <label class="block text-xs font-semibold text-gray-600 mb-1">Desde</label>
            <input
              v-model="desde"
              type="date"
              class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2.5 text-sm"
            />
          </div>

          <div class="md:col-span-3">
            <label class="block text-xs font-semibold text-gray-600 mb-1">Hasta</label>
            <input
              v-model="hasta"
              type="date"
              class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2.5 text-sm"
            />
          </div>

          <div class="md:col-span-12 flex justify-end">
            <button
              @click="aplicar"
              class="rounded-xl bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-brand-700 transition"
            >
              Aplicar
            </button>
          </div>
        </div>
      </AppCard>

      <!-- Tabla -->
      <AppCard padding="md">
        <div class="flex items-center justify-between mb-3">
          <p class="text-sm text-gray-600">
            Mostrando <span class="font-semibold text-gray-900">{{ rows.length }}</span> de
            <span class="font-semibold text-gray-900">{{ logs.total || 0 }}</span>
          </p>

          <p class="text-xs text-gray-500">
            Página {{ logs.current_page || 1 }} de {{ logs.last_page || 1 }}
          </p>
        </div>

        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">
                  Fecha/Hora
                </th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">
                  Empleado
                </th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">
                  Email
                </th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">
                  IP
                </th>
              </tr>
            </thead>

            <tbody class="bg-white divide-y divide-gray-100">
              <tr v-for="r in rows" :key="r.id" class="hover:bg-gray-50">
                <td class="px-4 py-3 text-sm text-gray-900 whitespace-nowrap">
                  {{ formatDT(r.logged_in_at) }}
                </td>
                <td class="px-4 py-3 text-sm text-gray-900">
                  {{ fullName(r.empleado) }}
                </td>
                <td class="px-4 py-3 text-sm text-gray-700">
                  {{ r.empleado?.email || '—' }}
                </td>
                <td class="px-4 py-3 text-sm text-gray-700 whitespace-nowrap">
                  {{ r.ip || '—' }}
                </td>
              </tr>

              <tr v-if="rows.length === 0">
                <td colspan="5" class="px-4 py-8 text-center text-sm text-gray-500">
                  Sin registros para los filtros actuales.
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Paginación -->
        <div class="flex items-center justify-between mt-4">
          <button
            :disabled="!logs.prev_page_url"
            @click="cambiarPagina(logs.prev_page_url)"
            class="rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-800 hover:bg-gray-50 transition disabled:opacity-50"
          >
            Anterior
          </button>
          <button
            :disabled="!logs.next_page_url"
            @click="cambiarPagina(logs.next_page_url)"
            class="rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-800 hover:bg-gray-50 transition disabled:opacity-50"
          >
            Siguiente
          </button>
        </div>
      </AppCard>
    </div>
  </TopBannerLayout>
</template>
