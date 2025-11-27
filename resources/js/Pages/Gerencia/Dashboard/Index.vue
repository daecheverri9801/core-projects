<script setup>
import { ref, computed } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import GerenciaLayout from '@/Components/GerenciaLayout.vue'

import {
  Chart as ChartJS,
  ArcElement,
  BarElement,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Tooltip,
  Legend,
} from 'chart.js'
import { Bar, Doughnut, Line } from 'vue-chartjs'

ChartJS.register(
  ArcElement,
  BarElement,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Tooltip,
  Legend
)

const props = defineProps({
  resumenGlobal: Object,
  ventasPorProyecto: Array,
  proyeccionVsReal: Array,
  inventarioProyectos: Array,
  ventasAsesoresProyecto: Array,
  estadoInventario: Array,
  rankingAsesores: Array,
  absorcionMensual: Array,
  proyectos: Array,
  empleados: Array,
  estadosInmueble: Array,
  filtros: Object,
  planPagosCI: Object,
})

const filtrosValue = props.filtros || {}

const activeTab = ref('resumen')

function qs(obj) {
  return new URLSearchParams(obj).toString()
}

/* ==============================
   FILTROS SUPERIORES
============================== */
const filtros = ref({
  desde: props.filtros?.desde || '',
  hasta: props.filtros?.hasta || '',
  proyecto_id: props.filtros?.proyecto_id || '',
  asesor_id: props.filtros?.asesor_id || '',
  estado_inmueble: props.filtros?.estado_inmueble || '',
})

function aplicarFiltros() {
  router.get('/gerencia/dashboard', { ...filtros.value }, { preserveState: true, replace: true })
}

/* ==============================
   HELPERS
============================== */
function formatMoney(v) {
  return Number(v || 0).toLocaleString('es-CO', {
    style: 'currency',
    currency: 'COP',
    maximumFractionDigits: 0,
  })
}

function formatPercent(num, den) {
  if (!den || den === 0) return '0 %'
  return ((num / den) * 100).toFixed(1) + ' %'
}

/* ==============================
   GRÁFICA 1: Ventas por proyecto (Bar)
============================== */
const ventasProyectoData = computed(() => {
  const labels = (props.ventasPorProyecto || []).map((p) => p.nombre)
  const data = (props.ventasPorProyecto || []).map((p) => p.total_valor)

  return {
    labels,
    datasets: [
      {
        label: 'Ventas por proyecto',
        data,
        backgroundColor: 'rgba(56, 189, 248, 0.7)',
        borderColor: 'rgba(56, 189, 248, 1)',
        borderWidth: 1.5,
      },
    ],
  }
})

const barChartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      labels: {
        color: '#e5e7eb',
        font: { size: 11 },
      },
    },
    tooltip: {
      callbacks: {
        label(ctx) {
          const v = ctx.parsed.y ?? ctx.parsed.x
          return formatMoney(v)
        },
      },
    },
  },
  scales: {
    x: {
      ticks: { color: '#9ca3af' },
      grid: { color: 'rgba(55, 65, 81, 0.4)' },
    },
    y: {
      ticks: { color: '#9ca3af' },
      grid: { color: 'rgba(55, 65, 81, 0.4)' },
    },
  },
}

/* ==============================
   GRÁFICA 2: Estado del inventario (Doughnut)
============================== */
const proyectoInventarioSeleccionado = ref('')

const inventarioOptions = computed(() => {
  const base = [{ value: '', label: 'Todos los proyectos' }]
  ;(props.estadoInventario || []).forEach((p) => {
    base.push({ value: p.proyecto, label: p.proyecto })
  })
  return base
})

const inventarioDoughnutData = computed(() => {
  const labels = ['Disponible', 'Vendido', 'Separado', 'No Disponible', 'Congelado']
  const counts = {
    Disponible: 0,
    Vendido: 0,
    Separado: 0,
    'No Disponible': 0,
    Congelado: 0,
  }

  const dataSrc = props.estadoInventario || []

  if (!dataSrc.length) {
    return {
      labels,
      datasets: [
        {
          data: labels.map(() => 0),
          backgroundColor: [],
        },
      ],
    }
  }

  if (!proyectoInventarioSeleccionado.value) {
    // Consolidado global
    dataSrc.forEach((proj) => {
      labels.forEach((estado) => {
        const v = proj.estados?.[estado] ?? 0
        counts[estado] += Number(v)
      })
    })
  } else {
    // Solo un proyecto
    const proj = dataSrc.find((p) => p.proyecto === proyectoInventarioSeleccionado.value)
    if (proj) {
      labels.forEach((estado) => {
        counts[estado] = Number(proj.estados?.[estado] ?? 0)
      })
    }
  }

  const palette = [
    'rgba(16, 185, 129, 0.8)', // disponible
    'rgba(56, 189, 248, 0.8)', // vendido
    'rgba(245, 158, 11, 0.8)', // separado
    'rgba(148, 163, 184, 0.8)', // no disponible
    'rgba(244, 63, 94, 0.8)', // congelado
  ]

  const borderPalette = [
    'rgba(16, 185, 129, 1)',
    'rgba(56, 189, 248, 1)',
    'rgba(245, 158, 11, 1)',
    'rgba(148, 163, 184, 1)',
    'rgba(244, 63, 94, 1)',
  ]

  return {
    labels,
    datasets: [
      {
        data: labels.map((estado) => counts[estado]),
        backgroundColor: palette,
        borderColor: borderPalette,
        borderWidth: 1.5,
      },
    ],
  }
})

const doughnutOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      position: 'bottom',
      labels: {
        color: '#e5e7eb',
        font: { size: 10 },
      },
    },
  },
}

/* ==============================
   GRÁFICA 3: Ranking de asesores (Bar horizontal)
============================== */
const rankingAsesoresData = computed(() => {
  const rows = props.rankingAsesores || []
  const labels = rows.map((r) => r.asesor)
  const data = rows.map((r) => Number(r.total_ventas || 0))

  return {
    labels,
    datasets: [
      {
        label: 'Total ventas',
        data,
        backgroundColor: 'rgba(244, 114, 182, 0.8)',
        borderColor: 'rgba(236, 72, 153, 1)',
        borderWidth: 1.5,
      },
    ],
  }
})

const rankingOptions = {
  responsive: true,
  maintainAspectRatio: false,
  indexAxis: 'y',
  plugins: {
    legend: {
      labels: {
        color: '#e5e7eb',
        font: { size: 11 },
      },
    },
    tooltip: {
      callbacks: {
        label(ctx) {
          const v = ctx.parsed.x
          return formatMoney(v)
        },
      },
    },
  },
  scales: {
    x: {
      ticks: { color: '#9ca3af' },
      grid: { color: 'rgba(55, 65, 81, 0.4)' },
    },
    y: {
      ticks: { color: '#9ca3af' },
      grid: { color: 'rgba(31, 41, 55, 0.8)' },
    },
  },
}

/* ==============================
   GRÁFICA 4: Absorción mensual (Line)
============================== */
const absorcionLineData = computed(() => {
  const rows = props.absorcionMensual || []

  const mesesSet = new Set()
  const proyectosSet = new Set()
  rows.forEach((r) => {
    mesesSet.add(r.mes)
    proyectosSet.add(r.proyecto)
  })

  const labels = Array.from(mesesSet).sort()
  const proyectos = Array.from(proyectosSet)

  const palette = [
    [56, 189, 248],
    [34, 197, 94],
    [251, 146, 60],
    [244, 114, 182],
    [129, 140, 248],
    [248, 250, 252],
  ]

  const datasets = proyectos.map((nombre, idx) => {
    const rgb = palette[idx % palette.length]
    const border = `rgb(${rgb[0]}, ${rgb[1]}, ${rgb[2]})`
    const bg = `rgba(${rgb[0]}, ${rgb[1]}, ${rgb[2]}, 0.25)`

    const data = labels.map((m) => {
      const row = rows.find((r) => r.proyecto === nombre && r.mes === m)
      return row ? Number(row.unidades) : 0
    })

    return {
      label: nombre,
      data,
      borderColor: border,
      backgroundColor: bg,
      fill: false,
      tension: 0.25,
      pointRadius: 3,
      pointHoverRadius: 4,
    }
  })

  return { labels, datasets }
})

const lineOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      labels: {
        color: '#e5e7eb',
        font: { size: 10 },
      },
    },
  },
  scales: {
    x: {
      ticks: { color: '#9ca3af' },
      grid: { color: 'rgba(55, 65, 81, 0.4)' },
    },
    y: {
      ticks: { color: '#9ca3af' },
      grid: { color: 'rgba(55, 65, 81, 0.4)' },
    },
  },
}

/* ==============================
   PROYECTOS / INVENTARIO TAB
============================== */
function formatDate(dateStr) {
  if (!dateStr) return '—'
  return new Date(dateStr).toISOString().split('T')[0]
}
</script>

<template>
  <GerenciaLayout>
    <Head title="Panel de Gerencia" />

    <!-- Encabezado -->
    <!-- <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="text-3xl font-semibold text-slate-50">Tablero de Gerencia</h1>
        <p class="text-slate-400 text-sm">
          Visión consolidada de proyectos, ventas, inventario y desempeño comercial.
        </p>
      </div>
    </div> -->

    <!-- KPIs -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
      <div
        class="rounded-2xl bg-gradient-to-br from-emerald-500/25 to-emerald-500/5 border border-emerald-500/40 p-4"
      >
        <div class="text-xs uppercase tracking-wide text-emerald-300">Ventas Totales</div>
        <div class="mt-2 text-2xl font-semibold text-emerald-50">
          {{ formatMoney(resumenGlobal.ventas_totales) }}
        </div>
        <div class="text-xs text-emerald-200/80 mt-1">Valor acumulado de ventas (todas)</div>
      </div>

      <div
        class="rounded-2xl bg-gradient-to-br from-sky-500/25 to-sky-500/5 border border-sky-500/40 p-4"
      >
        <div class="text-xs uppercase tracking-wide text-sky-300">Unidades Vendidas</div>
        <div class="mt-2 text-2xl font-semibold text-sky-50">
          {{ resumenGlobal.unidades_vendidas }}
        </div>
        <div class="text-xs text-sky-200/80 mt-1">Total de operaciones tipo venta</div>
      </div>

      <div
        class="rounded-2xl bg-gradient-to-br from-amber-500/25 to-amber-500/5 border border-amber-500/40 p-4"
      >
        <div class="text-xs uppercase tracking-wide text-amber-300">Inventario Disponible</div>
        <div class="mt-2 text-2xl font-semibold text-amber-50">
          {{ resumenGlobal.inventario_disponible }}
        </div>
        <div class="text-xs text-amber-200/80 mt-1">Unidades disponibles (apto + locales)</div>
      </div>
    </div>

    <!-- Filtros -->
    <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-4 mb-5">
      <div class="flex items-center justify-between mb-3">
        <h2 class="text-sm font-semibold text-slate-200 uppercase tracking-wide">
          Filtros de análisis
        </h2>
        <button
          @click="aplicarFiltros"
          class="px-3 py-1.5 text-xs rounded-lg bg-sky-600 text-white hover:bg-sky-500"
        >
          Aplicar filtros
        </button>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-5 gap-4 text-sm">
        <div>
          <label class="text-slate-400 text-xs mb-1 block">Desde</label>
          <input
            type="date"
            v-model="filtros.desde"
            class="w-full bg-slate-800 text-slate-100 rounded p-2 border border-slate-700"
          />
        </div>

        <div>
          <label class="text-slate-400 text-xs mb-1 block">Hasta</label>
          <input
            type="date"
            v-model="filtros.hasta"
            class="w-full bg-slate-800 text-slate-100 rounded p-2 border border-slate-700"
          />
        </div>

        <div>
          <label class="text-slate-400 text-xs mb-1 block">Proyecto</label>
          <select
            v-model="filtros.proyecto_id"
            class="w-full bg-slate-800 text-slate-100 rounded p-2 border border-slate-700"
          >
            <option value="">Todos</option>
            <option v-for="p in proyectos" :key="p.id_proyecto" :value="p.id_proyecto">
              {{ p.nombre }}
            </option>
          </select>
        </div>

        <div>
          <label class="text-slate-400 text-xs mb-1 block">Asesor</label>
          <select
            v-model="filtros.asesor_id"
            class="w-full bg-slate-800 text-slate-100 rounded p-2 border border-slate-700"
          >
            <option value="">Todos</option>
            <option v-for="e in empleados" :key="e.id_empleado" :value="e.id_empleado">
              {{ e.nombre }} {{ e.apellido }}
            </option>
          </select>
        </div>

        <div>
          <label class="text-slate-400 text-xs mb-1 block">Estado inmueble</label>
          <select
            v-model="filtros.estado_inmueble"
            class="w-full bg-slate-800 text-slate-100 rounded p-2 border border-slate-700"
          >
            <option value="">Todos</option>
            <option
              v-for="e in estadosInmueble"
              :key="e.id_estado_inmueble"
              :value="e.id_estado_inmueble"
            >
              {{ e.nombre }}
            </option>
          </select>
        </div>
      </div>
    </div>

    <!-- Tabs -->
    <div class="mb-4 border-b border-slate-800">
      <nav class="-mb-px flex space-x-4 text-sm">
        <button
          type="button"
          :class="[
            'px-4 py-2 border-b-2',
            activeTab === 'resumen'
              ? 'border-sky-400 text-sky-200'
              : 'border-transparent text-slate-400 hover:text-slate-200',
          ]"
          @click="activeTab = 'resumen'"
        >
          Resumen / Gráficas
        </button>
        <button
          type="button"
          :class="[
            'px-4 py-2 border-b-2',
            activeTab === 'plan_ci'
              ? 'border-indigo-400 text-indigo-200'
              : 'border-transparent text-slate-400 hover:text-slate-200',
          ]"
          @click="activeTab = 'plan_ci'"
        >
          Plan Pagos Cuota Inicial
        </button>

        <button
          type="button"
          :class="[
            'px-4 py-2 border-b-2',
            activeTab === 'proyectos'
              ? 'border-emerald-400 text-emerald-200'
              : 'border-transparent text-slate-400 hover:text-slate-200',
          ]"
          @click="activeTab = 'proyectos'"
        >
          Proyectos / Inventario
        </button>

        <button
          type="button"
          :class="[
            'px-4 py-2 border-b-2',
            activeTab === 'proyeccion'
              ? 'border-amber-400 text-amber-200'
              : 'border-transparent text-slate-400 hover:text-slate-200',
          ]"
          @click="activeTab = 'proyeccion'"
        >
          Proyección de Ingresos
        </button>

        <button
          type="button"
          :class="[
            'px-4 py-2 border-b-2',
            activeTab === 'asesores'
              ? 'border-fuchsia-400 text-fuchsia-200'
              : 'border-transparent text-slate-400 hover:text-slate-200',
          ]"
          @click="activeTab = 'asesores'"
        >
          Ventas / Separaciones por Asesor
        </button>
      </nav>
    </div>

    <!-- TAB: RESUMEN / GRÁFICAS -->
    <div v-if="activeTab === 'resumen'" class="space-y-6">
      <!-- Fila de 4 tarjetas con hover -->
      <div class="grid grid-cols-1 xl:grid-cols-4 gap-4 mb-4">
        <!-- Ventas por proyecto -->
        <div
          class="group bg-slate-900/80 border border-slate-800 rounded-2xl p-4 transition transform hover:-translate-y-1 hover:border-sky-500/60 hover:shadow-[0_0_25px_rgba(56,189,248,0.25)]"
          style="height: 260px"
        >
          <div class="flex justify-between items-center mb-3">
            <h2 class="text-xs font-semibold text-slate-100 uppercase tracking-wide">
              Ventas por proyecto
            </h2>
            <span class="text-[10px] text-slate-500">Valor total</span>
          </div>
          <Bar :data="ventasProyectoData" :options="barChartOptions" />
        </div>

        <!-- Estado inventario (doughnut) -->
        <div
          class="group bg-slate-900/80 border border-slate-800 rounded-2xl p-4 transition transform hover:-translate-y-1 hover:border-emerald-500/60 hover:shadow-[0_0_25px_rgba(16,185,129,0.25)]"
          style="height: 260px"
        >
          <div class="flex justify-between items-center mb-2">
            <h2 class="text-xs font-semibold text-slate-100 uppercase tracking-wide">
              Estado del inventario
            </h2>
          </div>

          <div class="mb-2">
            <select
              v-model="proyectoInventarioSeleccionado"
              class="w-full bg-slate-900 text-slate-100 border border-slate-700 rounded-lg px-2 py-1 text-xs"
            >
              <option v-for="opt in inventarioOptions" :key="opt.value || 'all'" :value="opt.value">
                {{ opt.label }}
              </option>
            </select>
          </div>

          <Doughnut :data="inventarioDoughnutData" :options="doughnutOptions" />
        </div>

        <!-- Ranking de asesores -->
        <div
          class="group bg-slate-900/80 border border-slate-800 rounded-2xl p-4 transition transform hover:-translate-y-1 hover:border-pink-500/60 hover:shadow-[0_0_25px_rgba(236,72,153,0.25)]"
          style="height: 260px"
        >
          <div class="flex justify-between items-center mb-3">
            <h2 class="text-xs font-semibold text-slate-100 uppercase tracking-wide">
              Ranking asesores
            </h2>
            <span class="text-[10px] text-slate-500">Top por valor vendido</span>
          </div>
          <Bar :data="rankingAsesoresData" :options="rankingOptions" />
        </div>

        <!-- Absorción mensual -->
        <div
          class="group bg-slate-900/80 border border-slate-800 rounded-2xl p-4 transition transform hover:-translate-y-1 hover:border-indigo-500/60 hover:shadow-[0_0_25px_rgba(129,140,248,0.25)]"
          style="height: 260px"
        >
          <div class="flex justify-between items-center mb-3">
            <h2 class="text-xs font-semibold text-slate-100 uppercase tracking-wide">
              Absorción mensual
            </h2>
            <span class="text-[10px] text-slate-500">Unidades vendidas / mes</span>
          </div>
          <Line :data="absorcionLineData" :options="lineOptions" />
        </div>
      </div>
    </div>

    <!-- TAB: PLAN DE PAGOS DE CUOTA INICIAL -->
    <div v-else-if="activeTab === 'plan_ci'" class="space-y-4">
      <!-- <h2 class="text-sm font-semibold text-slate-100">
        Plan de pagos de cuota inicial por ventas
      </h2> -->

      <div class="flex justify-end mb-3">
        <a
          class="px-3 py-2 rounded bg-emerald-600 text-white hover:bg-emerald-500 text-sm"
          :href="`/gerencia/plan-pagos-ci/export?${qs(filtros)}`"
        >
          Exportar Excel
        </a>
      </div>

      <div class="overflow-x-auto bg-slate-900/80 border border-slate-800 rounded-2xl p-4">
        <table
          class="min-w-full text-sm text-slate-200"
          v-if="planPagosCI && planPagosCI.filas && planPagosCI.filas.length"
        >
          <thead>
            <tr class="bg-slate-800">
              <th class="p-2 border border-slate-700">Proyecto</th>
              <th class="p-2 border border-slate-700">Inmueble</th>
              <th class="p-2 border border-slate-700">Cliente</th>

              <!-- Meses dinámicos -->
              <th
                v-for="m in planPagosCI.encabezados"
                :key="m"
                class="p-2 border border-slate-700 text-center"
              >
                {{ m }}
              </th>
            </tr>
          </thead>

          <tbody>
            <tr v-for="(f, i) in planPagosCI.filas" :key="i" class="odd:bg-slate-900/50">
              <td class="p-2 border border-slate-800">{{ f.proyecto }}</td>
              <td class="p-2 border border-slate-800">{{ f.inmueble }}</td>
              <td class="p-2 border border-slate-800">{{ f.cliente }}</td>

              <td
                v-for="m in planPagosCI.encabezados"
                :key="m"
                class="p-2 border border-slate-800 text-right"
              >
                {{ formatMoney(f.meses[m] || 0) }}
              </td>
            </tr>

            <tr class="bg-slate-800 font-semibold">
              <td class="p-2 border border-slate-700" colspan="3">TOTAL</td>
              <td
                v-for="m in planPagosCI.encabezados"
                :key="'tot-' + m"
                class="p-2 border border-slate-700 text-right"
              >
                {{ formatMoney(planPagosCI.totales[m]) }}
              </td>
            </tr>
          </tbody>
        </table>

        <div v-else class="text-center text-slate-400 text-sm py-6">
          No hay ventas con cuota inicial en el rango seleccionado.
        </div>
      </div>
    </div>

    <!-- TAB: PROYECTOS / INVENTARIO -->
    <div v-else-if="activeTab === 'proyectos'" class="space-y-4">
      <h2 class="text-sm font-semibold text-slate-100">
        Inventario detallado por proyecto (precio base / vigente / estado)
      </h2>

      <div
        v-for="proyecto in inventarioProyectos"
        :key="proyecto.id_proyecto"
        class="bg-slate-900/80 border border-slate-800 rounded-2xl p-4"
      >
        <div class="flex items-center justify-between mb-3">
          <div class="text-slate-100 font-semibold">
            {{ proyecto.nombre }}
          </div>
          <div class="text-xs text-slate-400">{{ proyecto.inmuebles.length }} unidades</div>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead>
              <tr class="text-slate-400 border-b border-slate-800 text-left">
                <th class="py-2 pr-2">Tipo</th>
                <th class="py-2 pr-2">Inmueble</th>
                <th class="py-2 pr-2 text-right">Precio base</th>
                <th class="py-2 pr-2 text-right">Precio vigente</th>
                <th class="py-2 pr-2">Estado</th>
                <th class="py-2 pr-2">Asesor</th>
                <th class="py-2 pr-2">Fecha venta/sep.</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="(i, idx) in proyecto.inmuebles"
                :key="idx"
                class="border-b border-slate-800/60"
              >
                <td class="py-2 pr-2 text-slate-200">{{ i.tipo }}</td>
                <td class="py-2 pr-2 text-slate-100">{{ i.etiqueta }}</td>
                <td class="py-2 pr-2 text-right text-slate-200">
                  {{ formatMoney(i.precio_base) }}
                </td>
                <td class="py-2 pr-2 text-right text-slate-100">
                  {{ formatMoney(i.precio_vigente) }}
                </td>
                <td class="py-2 pr-2">
                  <span
                    :class="[
                      'px-2 py-0.5 rounded-full text-xs',
                      i.estado === 'Disponible'
                        ? 'bg-emerald-500/15 text-emerald-300 border border-emerald-500/40'
                        : i.estado === 'Vendido'
                          ? 'bg-sky-500/15 text-sky-300 border border-sky-500/40'
                          : i.estado === 'Separado'
                            ? 'bg-amber-500/15 text-amber-300 border border-amber-500/40'
                            : 'bg-slate-800 text-slate-200 border border-slate-700',
                    ]"
                  >
                    {{ i.estado }}
                  </span>
                </td>
                <td class="py-2 pr-2 text-slate-200">
                  {{ i.asesor || '—' }}
                </td>
                <td class="py-2 pr-2 text-slate-300">
                  {{ formatDate(i.fecha_operacion) }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- TAB: PROYECCIÓN DE INGRESOS -->
    <div v-else-if="activeTab === 'proyeccion'" class="space-y-4">
      <h2 class="text-sm font-semibold text-slate-100">
        Proyección de ingresos vs ventas reales (mes actual)
      </h2>

      <div
        class="bg-slate-900/80 border border-slate-800 rounded-2xl p-4"
        style="max-height: 360px; overflow: auto"
      >
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead>
              <tr class="text-slate-400 border-b border-slate-800 text-left">
                <th class="py-2 pr-2">Proyecto</th>
                <th class="py-2 pr-2 text-right">Meta unidades</th>
                <th class="py-2 pr-2 text-right">Unidades reales</th>
                <th class="py-2 pr-2 text-right">Meta valor</th>
                <th class="py-2 pr-2 text-right">Valor real</th>
                <th class="py-2 pr-2 text-right">% Cumpl. unidades</th>
                <th class="py-2 pr-2 text-right">% Cumpl. valor</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="r in proyeccionVsReal"
                :key="r.id_proyecto"
                class="border-b border-slate-800/60"
              >
                <td class="py-2 pr-2 text-slate-200">{{ r.nombre }}</td>
                <td class="py-2 pr-2 text-right text-slate-100">{{ r.meta_unidades }}</td>
                <td class="py-2 pr-2 text-right text-slate-100">{{ r.real_unidades }}</td>
                <td class="py-2 pr-2 text-right text-slate-100">
                  {{ formatMoney(r.meta_valor) }}
                </td>
                <td class="py-2 pr-2 text-right text-slate-100">
                  {{ formatMoney(r.real_valor) }}
                </td>
                <td class="py-2 pr-2 text-right text-slate-100">
                  {{ formatPercent(r.real_unidades, r.meta_unidades || 0) }}
                </td>
                <td class="py-2 pr-2 text-right text-slate-100">
                  {{ formatPercent(r.real_valor, r.meta_valor || 0) }}
                </td>
              </tr>
              <tr v-if="!proyeccionVsReal.length">
                <td colspan="7" class="py-4 text-center text-slate-400">
                  No hay metas comerciales definidas para el mes actual.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- TAB: VENTAS / SEPARACIONES POR ASESOR Y PROYECTO -->
    <div v-else-if="activeTab === 'asesores'" class="space-y-4">
      <h2 class="text-sm font-semibold text-slate-100">
        Ventas y separaciones por asesor y proyecto
      </h2>

      <div
        class="bg-slate-900/80 border border-slate-800 rounded-2xl p-4"
        style="max-height: 360px; overflow: auto"
      >
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead>
              <tr class="text-slate-400 border-b border-slate-800 text-left">
                <th class="py-2 pr-2">Proyecto</th>
                <th class="py-2 pr-2">Asesor</th>
                <th class="py-2 pr-2 text-right">Ventas</th>
                <th class="py-2 pr-2 text-right">Separaciones</th>
                <th class="py-2 pr-2 text-right">Sep. ejecutadas</th>
                <th class="py-2 pr-2 text-right">Sep. caducadas</th>
                <th class="py-2 pr-2 text-right">% Efectividad sep.</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="row in ventasAsesoresProyecto"
                :key="`${row.id_proyecto}-${row.id_empleado}`"
                class="border-b border-slate-800/60"
              >
                <td class="py-2 pr-2 text-slate-200">
                  {{ row.proyecto }}
                </td>
                <td class="py-2 pr-2 text-slate-100">
                  {{ row.empleado }}
                </td>
                <td class="py-2 pr-2 text-right text-sky-300">
                  {{ row.ventas }}
                </td>
                <td class="py-2 pr-2 text-right text-slate-100">
                  {{ row.separaciones }}
                </td>
                <td class="py-2 pr-2 text-right text-emerald-300">
                  {{ row.separaciones_ejecutadas }}
                </td>
                <td class="py-2 pr-2 text-right text-rose-300">
                  {{ row.separaciones_caducadas }}
                </td>
                <td class="py-2 pr-2 text-right text-slate-100">
                  {{
                    formatPercent(
                      row.separaciones_ejecutadas,
                      row.separaciones_ejecutadas + row.separaciones_caducadas
                    )
                  }}
                </td>
              </tr>
              <tr v-if="!ventasAsesoresProyecto.length">
                <td colspan="7" class="py-4 text-center text-slate-400">
                  Aún no hay datos de ventas / separaciones agrupados por asesor.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </GerenciaLayout>
</template>
