<!-- resources/js/Pages/Ventas/Index.vue (o la ruta donde esté tu vista) -->
<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { computed, ref } from 'vue'

import TopBannerLayout from '@/Components/TopBannerLayout.vue'
import PageHeader from '@/Components/PageHeader.vue'
import AppCard from '@/Components/AppCard.vue'

import {
  EyeIcon,
  PencilIcon,
  TrashIcon,
  PlusIcon,
  MagnifyingGlassIcon,
  FunnelIcon,
  CalendarDaysIcon,
} from '@heroicons/vue/24/outline'

const props = defineProps({
  empleado: Object,
  ventas: { type: Array, default: () => [] },

  // DEBUG (se mantiene)
  debug_proyecto: Object,
  debug_priceengine: Object,
  debug_venta: Object,
})

const debugEnabled = false
const debug = {
  proyecto: props.debug_proyecto,
  pe: props.debug_priceengine,
  venta: props.debug_venta,
}

const q = ref('')
const tipo = ref('') // apartamento | local | ''
const estado = ref('') // nombre estado inmueble (string)
const desde = ref('')
const hasta = ref('')

const estadosDisponibles = computed(() => {
  const set = new Set()
  props.ventas.forEach((v) => {
    const name = v?.apartamento?.estado_inmueble?.nombre || v?.local?.estado_inmueble?.nombre
    if (name) set.add(name)
  })
  return Array.from(set).sort((a, b) => a.localeCompare(b, 'es'))
})

function formatCurrency(v) {
  return Number(v || 0).toLocaleString('es-CO', {
    style: 'currency',
    currency: 'COP',
    maximumFractionDigits: 0,
  })
}

function formatDate(date) {
  if (!date) return '—'
  return new Date(date).toLocaleDateString('es-CO', {
    year: 'numeric',
    month: 'numeric',
    day: 'numeric',
  })
}

function tipoInmueble(v) {
  if (v?.apartamento) return 'Apartamento'
  if (v?.local) return 'Local'
  return '—'
}

function inmuebleLabel(v) {
  if (v?.apartamento) return `Apto ${v.apartamento?.numero ?? '—'}`
  if (v?.local) return `Local ${v.local?.numero ?? '—'}`
  return '—'
}

function estadoNombre(v) {
  return v?.apartamento?.estado_inmueble?.nombre || v?.local?.estado_inmueble?.nombre || '—'
}

function matchText(haystack, needle) {
  return String(haystack ?? '')
    .toLowerCase()
    .includes(
      String(needle ?? '')
        .toLowerCase()
        .trim()
    )
}

function toISODate(d) {
  if (!d) return null
  const dt = new Date(d)
  if (Number.isNaN(dt.getTime())) return null
  dt.setHours(0, 0, 0, 0)
  return dt.getTime()
}

const ventasFiltradas = computed(() => {
  const qv = q.value.trim()
  const t = tipo.value
  const e = estado.value.trim()
  const from = toISODate(desde.value)
  const to = toISODate(hasta.value)

  return props.ventas.filter((v) => {
    // texto
    if (qv) {
      const ok =
        matchText(v?.id_venta, qv) ||
        matchText(v?.documento_cliente, qv) ||
        matchText(v?.proyecto?.nombre, qv) ||
        matchText(inmuebleLabel(v), qv)
      if (!ok) return false
    }

    // tipo
    if (t) {
      if (t === 'apartamento' && !v?.apartamento) return false
      if (t === 'local' && !v?.local) return false
    }

    // estado inmueble
    if (e) {
      if (estadoNombre(v) !== e) return false
    }

    // fecha
    if (from || to) {
      const fv = toISODate(v?.fecha_venta)
      if (!fv) return false
      if (from && fv < from) return false
      if (to && fv > to) return false
    }

    return true
  })
})

const totales = computed(() => {
  const totalVentas = ventasFiltradas.value.length
  const sumaValor = ventasFiltradas.value.reduce((acc, v) => acc + Number(v?.valor_total || 0), 0)
  const aptos = ventasFiltradas.value.filter((v) => !!v?.apartamento).length
  const locales = ventasFiltradas.value.filter((v) => !!v?.local).length
  return { totalVentas, sumaValor, aptos, locales }
})

function resetFiltros() {
  q.value = ''
  tipo.value = ''
  estado.value = ''
  desde.value = ''
  hasta.value = ''
}

function eliminar(id) {
  if (confirm('¿Desea eliminar esta venta?')) {
    router.delete(`/admin/ventas/${id}`, { preserveScroll: true })
  }
}
</script>

<template>
  <TopBannerLayout :empleado="empleado" panel-name="Panel administrador">
    <Head title="Ventas" />

    <div class="space-y-6">
      <PageHeader title="Ventas" subtitle="Consulta, filtra y administra las ventas registradas.">
        <template #actions>
          <Link
            href="/ventas/create"
            class="inline-flex items-center gap-2 rounded-xl bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700 transition"
          >
            <PlusIcon class="h-5 w-5" />
            Nueva venta
          </Link>
        </template>
      </PageHeader>

      <!-- KPIs -->
      <!-- <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <AppCard padding="md">
          <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">
            Ventas (filtradas)
          </p>
          <p class="mt-2 text-2xl font-bold text-gray-900">{{ totales.totalVentas }}</p>
        </AppCard>

        <AppCard padding="md">
          <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Valor total</p>
          <p class="mt-2 text-2xl font-bold text-gray-900">
            {{ formatCurrency(totales.sumaValor) }}
          </p>
        </AppCard>

        <AppCard padding="md">
          <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Apartamentos</p>
          <p class="mt-2 text-2xl font-bold text-gray-900">{{ totales.aptos }}</p>
        </AppCard>

        <AppCard padding="md">
          <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Locales</p>
          <p class="mt-2 text-2xl font-bold text-gray-900">{{ totales.locales }}</p>
        </AppCard>
      </div> -->

      <!-- Filtros -->
      <AppCard padding="md">
        <div class="flex items-start justify-between gap-4">
          <div class="min-w-0">
            <p class="text-sm font-semibold text-gray-900 inline-flex items-center gap-2">
              <FunnelIcon class="h-5 w-5 text-gray-500" />
              Filtros
            </p>
            <p class="text-sm text-gray-600 mt-1">Filtra por texto, tipo, estado y fechas.</p>
          </div>

          <button
            type="button"
            @click="resetFiltros"
            class="shrink-0 rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm font-semibold text-gray-800 hover:bg-gray-50 transition"
          >
            Limpiar
          </button>
        </div>

        <div class="mt-4 grid grid-cols-1 md:grid-cols-12 gap-4">
          <!-- Search -->
          <div class="md:col-span-5">
            <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1"
              >Buscar</label
            >
            <div class="relative">
              <MagnifyingGlassIcon
                class="h-5 w-5 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2"
              />
              <input
                v-model="q"
                type="text"
                class="w-full rounded-xl border border-gray-300 bg-white pl-10 pr-3 py-2.5 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
                placeholder="ID venta, documento, proyecto, apto/local…"
              />
            </div>
          </div>

          <!-- Tipo -->
          <div class="md:col-span-2">
            <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1"
              >Tipo</label
            >
            <select
              v-model="tipo"
              class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
            >
              <option value="">Todos</option>
              <option value="apartamento">Apartamento</option>
              <option value="local">Local</option>
            </select>
          </div>

          <!-- Estado -->
          <div class="md:col-span-3">
            <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1"
              >Estado</label
            >
            <select
              v-model="estado"
              class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
            >
              <option value="">Todos</option>
              <option v-for="e in estadosDisponibles" :key="e" :value="e">{{ e }}</option>
            </select>
          </div>

          <!-- Fechas -->
          <!-- <div class="md:col-span-2">
            <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">
              <span class="inline-flex items-center gap-2">
                <CalendarDaysIcon class="h-5 w-5 text-gray-500" /> Desde
              </span>
            </label>
            <input
              v-model="desde"
              type="date"
              class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
            />
          </div>

          <div class="md:col-span-2">
            <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1"
              >Hasta</label
            >
            <input
              v-model="hasta"
              type="date"
              class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
            />
          </div> -->
        </div>
      </AppCard>

      <!-- Tabla -->
      <AppCard padding="none" class="overflow-hidden">
        <div class="px-4 md:px-6 py-4 border-b bg-gray-50 flex items-center justify-between">
          <div class="min-w-0">
            <p class="text-sm font-semibold text-gray-900">Listado</p>
            <p class="text-xs text-gray-500 mt-0.5">
              Mostrando {{ ventasFiltradas.length }} de {{ ventas.length }} ventas
            </p>
          </div>
        </div>

        <div class="overflow-x-auto">
          <table class="min-w-[1050px] w-full divide-y divide-gray-200">
            <thead class="bg-white">
              <tr class="text-left">
                <th
                  class="px-4 md:px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider"
                >
                  ID
                </th>
                <th
                  class="px-4 md:px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider"
                >
                  Documento
                </th>
                <th
                  class="px-4 md:px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider"
                >
                  Inmueble
                </th>
                <th
                  class="px-4 md:px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider"
                >
                  Proyecto
                </th>
                <th
                  class="px-4 md:px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider"
                >
                  Fecha
                </th>
                <th
                  class="px-4 md:px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider"
                >
                  Valor
                </th>
                <th
                  class="px-4 md:px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider"
                >
                  Plazo (meses)
                </th>
                <th
                  class="px-4 md:px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider"
                >
                  Estado
                </th>
                <th
                  class="px-4 md:px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-right"
                >
                  Acciones
                </th>
              </tr>
            </thead>

            <tbody class="divide-y divide-gray-100 bg-white">
              <tr
                v-for="venta in ventasFiltradas"
                :key="venta.id_venta"
                class="hover:bg-gray-50/70 transition"
              >
                <td class="px-4 md:px-6 py-3 text-sm text-gray-600">{{ venta.id_venta }}</td>

                <td class="px-4 md:px-6 py-3 text-sm text-gray-900">
                  {{ venta.documento_cliente ?? '—' }}
                </td>

                <td class="px-4 md:px-6 py-3">
                  <div class="flex items-center gap-2">
                    <span
                      class="inline-flex rounded-full border px-2.5 py-1 text-xs font-semibold"
                      :class="
                        venta.apartamento
                          ? 'border-sky-200 bg-sky-50 text-sky-700'
                          : venta.local
                            ? 'border-emerald-200 bg-emerald-50 text-emerald-700'
                            : 'border-gray-200 bg-gray-50 text-gray-700'
                      "
                    >
                      {{ tipoInmueble(venta) }}
                    </span>
                    <span class="text-sm text-gray-900 font-semibold">
                      {{ inmuebleLabel(venta) }}
                    </span>
                  </div>
                </td>

                <td class="px-4 md:px-6 py-3 text-sm text-gray-700">
                  {{ venta.proyecto?.nombre ?? '—' }}
                </td>

                <td class="px-4 md:px-6 py-3 text-sm text-gray-700">
                  {{ formatDate(venta.fecha_venta) }}
                </td>

                <td class="px-4 md:px-6 py-3 text-sm font-semibold text-gray-900">
                  {{ formatCurrency(venta.valor_total ?? 0) }}
                </td>

                <td class="px-4 md:px-6 py-3 text-sm text-gray-700">
                  {{ venta.plazo_cuota_inicial_meses ?? '—' }}
                </td>

                <td class="px-4 md:px-6 py-3">
                  <span
                    class="inline-flex rounded-full border border-gray-200 bg-gray-50 px-2.5 py-1 text-xs font-semibold text-gray-700"
                  >
                    {{ estadoNombre(venta) }}
                  </span>
                </td>

                <td class="px-4 md:px-6 py-3">
                  <div class="flex items-center justify-end gap-2">
                    <Link
                      :href="`/admin/ventas/${venta.id_venta}/edit`"
                      class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white p-2 hover:bg-gray-50 transition"
                      title="Editar"
                    >
                      <PencilIcon class="h-5 w-5 text-gray-700" />
                    </Link>

                    <button
                      type="button"
                      @click="eliminar(venta.id_venta)"
                      class="inline-flex items-center justify-center rounded-xl border border-red-200 bg-red-50 p-2 hover:bg-red-100 transition"
                      title="Eliminar"
                    >
                      <TrashIcon class="h-5 w-5 text-red-700" />
                    </button>
                  </div>
                </td>
              </tr>

              <tr v-if="ventasFiltradas.length === 0">
                <td colspan="9" class="px-6 py-10 text-center">
                  <p class="text-sm font-semibold text-gray-900">Sin resultados</p>
                  <p class="text-sm text-gray-600 mt-1">Ajusta los filtros o limpia la búsqueda.</p>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </AppCard>

      <!-- DEBUG (opcional, limpio) -->
      <AppCard v-if="debugEnabled" padding="md">
        <pre class="text-xs overflow-auto">{{ debug }}</pre>
      </AppCard>
    </div>
  </TopBannerLayout>
</template>
