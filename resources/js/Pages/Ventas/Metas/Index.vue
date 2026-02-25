<script setup>
import { ref, computed } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import VentasLayout from '@/Components/VentasLayout.vue'
import VentasPageHeader from '@/Pages/Ventas/Components/VentasPageHeader.vue'
import VentasCard from '@/Pages/Ventas/Components/VentasCard.vue'
import StatCard from '@/Pages/Ventas/Components/StatCard.vue'
import FlashMessages from '@/Components/FlashMessages.vue'
import {
  ChartBarIcon,
  ExclamationTriangleIcon,
  CalendarDaysIcon,
  BuildingOffice2Icon,
} from '@heroicons/vue/24/outline'

const props = defineProps({
  metas: { type: Array, default: () => [] },
  alertas: { type: Array, default: () => [] },
  resumenProyecto: { type: Array, default: () => [] },
  resumenAsesor: { type: Array, default: () => [] },
  proyectos: { type: Array, default: () => [] },
  filtros: { type: Object, default: () => ({}) },
  empleado: { type: Object, default: null },
})

const filtros = ref({
  ano: props.filtros.ano || new Date().getFullYear(),
  mes_desde: props.filtros.mes_desde || 1,
  mes_hasta: props.filtros.mes_hasta || 12,
  id_proyecto: props.filtros.id_proyecto || '',
})

const MESES = [
  { value: 1, label: 'Enero' },
  { value: 2, label: 'Febrero' },
  { value: 3, label: 'Marzo' },
  { value: 4, label: 'Abril' },
  { value: 5, label: 'Mayo' },
  { value: 6, label: 'Junio' },
  { value: 7, label: 'Julio' },
  { value: 8, label: 'Agosto' },
  { value: 9, label: 'Septiembre' },
  { value: 10, label: 'Octubre' },
  { value: 11, label: 'Noviembre' },
  { value: 12, label: 'Diciembre' },
]

function mesLabel(m) {
  const n = Number(m)
  return MESES.find((x) => x.value === n)?.label || String(m ?? '—')
}

function aplicarFiltros() {
  router.get('/metas', { ...filtros.value }, { preserveState: true, replace: true })
}

function formatMoney(v) {
  return Number(v || 0).toLocaleString('es-CO', {
    style: 'currency',
    currency: 'COP',
    maximumFractionDigits: 0,
  })
}

function pct(valor) {
  if (valor === null || valor === undefined) return '—'
  return `${Math.round(valor * 100)}%`
}

function widthFromCumpl(c) {
  if (c === null || c === undefined) return '0%'
  return `${Math.min(120, Math.round(c * 100))}%`
}

const metasOrdenadas = computed(() =>
  [...props.metas].sort((a, b) => {
    if (a.ano !== b.ano) return a.ano - b.ano
    if (a.mes !== b.mes) return a.mes - b.mes
    return (a.proyecto || '').localeCompare(b.proyecto || '')
  })
)

// Stats rápidos
const stats = computed(() => {
  const totalMetas = props.metas.length
  const totalAlertas = props.alertas.length
  const promedioCumplValor = average(
    props.metas.map((m) => m.cumplimiento_valor ?? null).filter((x) => x !== null)
  )
  const promedioCumplUnid = average(
    props.metas.map((m) => m.cumplimiento_unidades ?? null).filter((x) => x !== null)
  )

  return {
    totalMetas,
    totalAlertas,
    promedioCumplValor,
    promedioCumplUnid,
  }
})

function average(arr) {
  if (!arr || !arr.length) return null
  const s = arr.reduce((acc, n) => acc + Number(n || 0), 0)
  return s / arr.length
}
</script>

<template>
  <VentasLayout :empleado="empleado">
    <Head title="Metas" />

    <VentasPageHeader
      title="Metas"
      subtitle="Seguimiento de metas comerciales vs resultados (metas generales y asignadas a ti)"
      :icon="ChartBarIcon"
    />

    <!-- Estadísticas -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
      <StatCard
        label="Metas en periodo"
        :value="stats.totalMetas"
        :icon="ChartBarIcon"
        variant="yellow"
      />
      <StatCard
        label="Alertas"
        :value="stats.totalAlertas"
        :icon="ExclamationTriangleIcon"
        variant="purple"
      />
      <StatCard
        label="Prom. cumplimiento $"
        :value="
          stats.promedioCumplValor === null ? '—' : `${Math.round(stats.promedioCumplValor * 100)}%`
        "
        :icon="CalendarDaysIcon"
        variant="blue"
      />
      <StatCard
        label="Prom. cumplimiento U"
        :value="
          stats.promedioCumplUnid === null ? '—' : `${Math.round(stats.promedioCumplUnid * 100)}%`
        "
        :icon="BuildingOffice2Icon"
        variant="green"
      />
    </div>

    <!-- Filtros -->
    <VentasCard class="mb-6">
      <template #header>
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
          <div>
            <h2 class="text-lg font-semibold text-gray-900">Filtros</h2>
            <p class="text-xs text-gray-500 mt-1">
              Ajusta el periodo y proyecto para analizar tus metas.
            </p>
          </div>

          <button
            @click="aplicarFiltros"
            class="inline-flex items-center justify-center px-4 py-2 rounded-lg bg-gradient-to-r from-[#FFEA00] to-[#D1C000] text-[#474100] font-semibold hover:shadow-lg transition-all duration-200"
          >
            Aplicar filtros
          </button>
        </div>
      </template>

      <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-sm">
        <div>
          <label class="text-xs text-gray-600 block mb-1">Año</label>
          <input
            type="number"
            v-model="filtros.ano"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#FFEA00] focus:border-transparent transition"
          />
        </div>

        <div>
          <label class="text-xs text-gray-600 block mb-1">Mes desde</label>
          <select
            v-model.number="filtros.mes_desde"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#FFEA00] focus:border-transparent transition"
          >
            <option v-for="m in MESES" :key="m.value" :value="m.value">{{ m.label }}</option>
          </select>
        </div>

        <div>
          <label class="text-xs text-gray-600 block mb-1">Mes hasta</label>
          <select
            v-model.number="filtros.mes_hasta"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#FFEA00] focus:border-transparent transition"
          >
            <option v-for="m in MESES" :key="m.value" :value="m.value">{{ m.label }}</option>
          </select>
        </div>

        <div>
          <label class="text-xs text-gray-600 block mb-1">Proyecto</label>
          <select
            v-model="filtros.id_proyecto"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#FFEA00] focus:border-transparent transition"
          >
            <option value="">Todos</option>
            <option v-for="p in proyectos" :key="p.id_proyecto" :value="p.id_proyecto">
              {{ p.nombre }}
            </option>
          </select>
        </div>
      </div>
    </VentasCard>

    <!-- Resumen -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
      <VentasCard>
        <template #header>
          <div>
            <h2 class="text-lg font-semibold text-gray-900">Meta vs Real · Proyectos</h2>
            <p class="text-xs text-gray-500 mt-1">
              Consolidado por proyecto para el periodo filtrado.
            </p>
          </div>
        </template>

        <div v-if="!resumenProyecto.length" class="text-sm text-gray-500 py-4">
          No hay metas registradas para los filtros actuales.
        </div>

        <div v-for="p in resumenProyecto" :key="p.id_proyecto" class="mb-4 last:mb-0">
          <div class="flex items-center justify-between text-xs mb-1">
            <div class="font-semibold text-gray-900">{{ p.proyecto || 'General' }}</div>
            <div class="text-gray-500">
              {{ pct(p.cumplimiento_valor) }} · {{ pct(p.cumplimiento_unidades) }}
            </div>
          </div>

          <div class="space-y-1">
            <div class="flex items-center justify-between text-[11px] text-gray-600">
              <span>Meta Ventas: {{ formatMoney(p.meta_valor) }}</span>
              <span>Real Ventas: {{ formatMoney(p.real_valor) }}</span>
            </div>
            <div class="h-2 rounded-full bg-gray-200 overflow-hidden">
              <div
                class="h-full rounded-full bg-gradient-to-r from-emerald-500 to-lime-400"
                :style="{ width: widthFromCumpl(p.cumplimiento_valor) }"
              ></div>
            </div>

            <div class="flex items-center justify-between text-[11px] text-gray-600">
              <span>Meta Unds: {{ p.meta_unidades }}</span>
              <span>Real Unds: {{ p.real_unidades }}</span>
            </div>
            <div class="h-2 rounded-full bg-gray-200 overflow-hidden">
              <div
                class="h-full rounded-full bg-gradient-to-r from-sky-500 to-cyan-400"
                :style="{ width: widthFromCumpl(p.cumplimiento_unidades) }"
              ></div>
            </div>
          </div>
        </div>
      </VentasCard>

      <VentasCard>
        <template #header>
          <div>
            <h2 class="text-lg font-semibold text-gray-900">Meta vs Real · Asesor</h2>
            <p class="text-xs text-gray-500 mt-1">Incluye metas generales y metas asignadas.</p>
          </div>
        </template>

        <div v-if="!resumenAsesor.length" class="text-sm text-gray-500 py-4">
          No hay metas registradas para los filtros actuales.
        </div>

        <div v-for="a in resumenAsesor" :key="a.id_empleado" class="mb-4 last:mb-0">
          <div class="flex items-center justify-between text-xs mb-1">
            <div class="font-semibold text-gray-900">{{ a.empleado || 'General' }}</div>
            <div class="text-gray-500">
              {{ pct(a.cumplimiento_valor) }} · {{ pct(a.cumplimiento_unidades) }}
            </div>
          </div>

          <div class="space-y-1">
            <div class="flex items-center justify-between text-[11px] text-gray-600">
              <span>Meta Ventas: {{ formatMoney(a.meta_valor) }}</span>
              <span>Real Ventas: {{ formatMoney(a.real_valor) }}</span>
            </div>
            <div class="h-2 rounded-full bg-gray-200 overflow-hidden">
              <div
                class="h-full rounded-full bg-gradient-to-r from-fuchsia-500 to-pink-400"
                :style="{ width: widthFromCumpl(a.cumplimiento_valor) }"
              ></div>
            </div>

            <div class="flex items-center justify-between text-[11px] text-gray-600">
              <span>Meta Unds: {{ a.meta_unidades }}</span>
              <span>Real Unds: {{ a.real_unidades }}</span>
            </div>
            <div class="h-2 rounded-full bg-gray-200 overflow-hidden">
              <div
                class="h-full rounded-full bg-gradient-to-r from-amber-500 to-orange-400"
                :style="{ width: widthFromCumpl(a.cumplimiento_unidades) }"
              ></div>
            </div>
          </div>
        </div>
      </VentasCard>
    </div>

    <!-- Alertas -->
    <VentasCard class="mb-6">
      <template #header>
        <div class="flex items-center justify-between w-full">
          <div>
            <h2 class="text-lg font-semibold text-gray-900">Alertas estratégicas</h2>
            <p class="text-xs text-gray-500 mt-1">
              Alertas cuando el cumplimiento está por debajo del 80%.
            </p>
          </div>
          <span class="text-xs text-gray-600 font-semibold">{{ alertas.length }} alerta(s)</span>
        </div>
      </template>

      <div v-if="!alertas.length" class="text-sm text-gray-500 py-4">
        No hay alertas para las metas en el rango seleccionado.
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
        <div
          v-for="al in alertas"
          :key="al.id_meta"
          class="border border-red-200 bg-red-50 rounded-xl p-3 text-xs text-red-900"
        >
          <div class="flex items-center justify-between mb-1">
            <div class="font-semibold">
              {{ al.proyecto || 'General' }} · {{ al.empleado || 'Equipo' }}
            </div>
            <div class="text-[11px] text-red-600">{{ mesLabel(al.mes) }} / {{ al.ano }}</div>
          </div>

          <div class="space-y-1">
            <div>
              Meta Ventas: {{ formatMoney(al.meta_valor) }} · Real Ventas: {{ formatMoney(al.real_valor) }} ·
              {{ pct(al.cumplimiento_valor) }}
            </div>
            <div>
              Meta Unds: {{ al.meta_unidades }} · Real Unds: {{ al.real_unidades }} ·
              {{ pct(al.cumplimiento_unidades) }}
            </div>
          </div>
        </div>
      </div>
    </VentasCard>

    <!-- Detalle -->
    <VentasCard>
      <template #header>
        <div>
          <h2 class="text-lg font-semibold text-gray-900">Detalle metas vs resultados</h2>
          <p class="text-xs text-gray-500 mt-1">
            Listado completo de metas visibles para ti en el periodo.
          </p>
        </div>
      </template>

      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th
                class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
              >
                Año
              </th>
              <th
                class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
              >
                Mes
              </th>
              <th
                class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
              >
                Proyecto
              </th>
              <th
                class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
              >
                Asesor
              </th>
              <th
                class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider"
              >
                Meta Ventas
              </th>
              <th
                class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider"
              >
                Real Ventas
              </th>
              <th
                class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider"
              >
                % Ventas
              </th>
              <th
                class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider"
              >
                Meta Unds
              </th>
              <th
                class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider"
              >
                Real Unds
              </th>
              <th
                class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider"
              >
                % Unds
              </th>
            </tr>
          </thead>

          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="m in metasOrdenadas" :key="m.id_meta" class="hover:bg-gray-50">
              <td class="px-4 py-3 text-sm text-gray-900">{{ m.ano }}</td>
              <td class="px-4 py-3 text-sm text-gray-900">{{ mesLabel(m.mes) }}</td>
              <td class="px-4 py-3 text-sm text-gray-900">{{ m.proyecto || 'Todos' }}</td>
              <td class="px-4 py-3 text-sm text-gray-900">{{ m.empleado || 'Todos' }}</td>
              <td class="px-4 py-3 text-sm text-gray-900 text-right">
                {{ formatMoney(m.meta_valor) }}
              </td>
              <td class="px-4 py-3 text-sm text-gray-900 text-right">
                {{ formatMoney(m.real_valor) }}
              </td>
              <td class="px-4 py-3 text-sm text-gray-900 text-center">
                {{ pct(m.cumplimiento_valor) }}
              </td>
              <td class="px-4 py-3 text-sm text-gray-900 text-center">{{ m.meta_unidades }}</td>
              <td class="px-4 py-3 text-sm text-gray-900 text-center">{{ m.real_unidades }}</td>
              <td class="px-4 py-3 text-sm text-gray-900 text-center">
                {{ pct(m.cumplimiento_unidades) }}
              </td>
            </tr>

            <tr v-if="metasOrdenadas.length === 0">
              <td colspan="10" class="px-4 py-8 text-center text-sm text-gray-500">
                No se encontraron metas para el periodo seleccionado.
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </VentasCard>

    <FlashMessages />
  </VentasLayout>
</template>
