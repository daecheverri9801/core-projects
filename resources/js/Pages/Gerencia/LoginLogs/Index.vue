<script setup>
import { Head, router } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'

import GerenciaLayout from '@/Components/GerenciaLayout.vue'
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
    '/gerencia/login-logs',
    { q: q.value, desde: desde.value, hasta: hasta.value },
    { preserveState: true, replace: true }
  )
}

function limpiar() {
  q.value = ''
  desde.value = ''
  hasta.value = ''
  aplicar()
}

watch([q], () => {
  clearTimeout(window.__gll_t)
  window.__gll_t = setTimeout(() => aplicar(), 350)
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
  <GerenciaLayout>
    <Head title="Login Logs (Gerencia)" />

    <div class="space-y-6">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-slate-100">Historial de inicios de sesión</h1>
          <p class="text-sm text-slate-400 mt-1">Registro de accesos al sistema (Gerencia).</p>
        </div>
        <div>
          <button
            @click="aplicar"
            class="px-4 py-2 rounded-lg bg-brand-600 text-white font-semibold hover:bg-brand-500 transition"
          >
            Aplicar filtros
          </button>
          <button
            @click="limpiar()"
            class="px-4 py-2 rounded-lg bg-brand-600 text-white font-semibold hover:bg-brand-500 transition"
          >
            Limpiar filtros
          </button>
        </div>
      </div>

      <!-- Filtros -->
      <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-4">
        <div class="grid grid-cols-1 md:grid-cols-12 gap-3">
          <div class="md:col-span-6">
            <label class="block text-xs font-semibold text-slate-300 mb-1">Buscar por:</label>
            <QuickSearch v-model="q" placeholder="Empleado (nombre/email) o IP…" />
          </div>

          <div class="md:col-span-3">
            <label class="block text-xs font-semibold text-slate-300 mb-1">Desde</label>
            <input
              v-model="desde"
              type="date"
              class="w-full bg-slate-800 text-slate-100 rounded p-2 border border-slate-700"
            />
          </div>

          <div class="md:col-span-3">
            <label class="block text-xs font-semibold text-slate-300 mb-1">Hasta</label>
            <input
              v-model="hasta"
              type="date"
              class="w-full bg-slate-800 text-slate-100 rounded p-2 border border-slate-700"
            />
          </div>
        </div>
      </div>

      <!-- Tabla -->
      <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-4">
        <div class="flex items-center justify-between mb-3">
          <p class="text-sm text-slate-300">
            Mostrando <span class="font-semibold text-slate-100">{{ rows.length }}</span> de
            <span class="font-semibold text-slate-100">{{ logs.total || 0 }}</span>
          </p>
          <p class="text-xs text-slate-400">
            Página {{ logs.current_page || 1 }} de {{ logs.last_page || 1 }}
          </p>
        </div>

        <div class="overflow-x-auto">
          <table class="min-w-full text-xs text-slate-200 border border-slate-800 rounded">
            <thead class="bg-slate-800">
              <tr>
                <th class="p-2 border border-slate-700 text-left">Fecha/Hora</th>
                <th class="p-2 border border-slate-700 text-left">Empleado</th>
                <th class="p-2 border border-slate-700 text-left">Email</th>
                <th class="p-2 border border-slate-700 text-left">IP</th>
              </tr>
            </thead>

            <tbody>
              <tr v-for="r in rows" :key="r.id" class="odd:bg-slate-900/60">
                <td class="p-2 border border-slate-800 whitespace-nowrap">
                  {{ formatDT(r.logged_in_at) }}
                </td>
                <td class="p-2 border border-slate-800">{{ fullName(r.empleado) }}</td>
                <td class="p-2 border border-slate-800">{{ r.empleado?.email || '—' }}</td>
                <td class="p-2 border border-slate-800 whitespace-nowrap">{{ r.ip || '—' }}</td>
              </tr>

              <tr v-if="rows.length === 0">
                <td colspan="5" class="p-6 text-center text-slate-400">
                  Sin registros para los filtros actuales.
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="flex items-center justify-between mt-4">
          <button
            :disabled="!logs.prev_page_url"
            @click="cambiarPagina(logs.prev_page_url)"
            class="rounded-xl border border-slate-700 bg-slate-900 px-4 py-2 text-sm font-semibold text-slate-200 hover:bg-slate-800 transition disabled:opacity-50"
          >
            Anterior
          </button>
          <button
            :disabled="!logs.next_page_url"
            @click="cambiarPagina(logs.next_page_url)"
            class="rounded-xl border border-slate-700 bg-slate-900 px-4 py-2 text-sm font-semibold text-slate-200 hover:bg-slate-800 transition disabled:opacity-50"
          >
            Siguiente
          </button>
        </div>
      </div>
    </div>
  </GerenciaLayout>
</template>
