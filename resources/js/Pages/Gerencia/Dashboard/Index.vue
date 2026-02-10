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

/* ==============================
   PLUGINS (labels sin hover)
============================== */
const ValueLabelsPlugin = {
  id: 'valueLabelsPlugin',
  afterDatasetsDraw(chart) {
    const { ctx } = chart
    const type = chart.config.type

    // Solo para chartjs v3/4
    if (!ctx) return

    // Helpers de formato
    const formatCompactMoney = (v) => {
      const n = Number(v || 0)
      // compact COP: 1,2M / 850k, etc.
      const abs = Math.abs(n)
      if (abs >= 1_000_000_000) return `${Math.round(n / 1_000_000_000)}B`
      if (abs >= 1_000_000) return `${Math.round(n / 1_000_000)}M`
      if (abs >= 1_000) return `${Math.round(n / 1_000)}k`
      return `${Math.round(n)}`
    }
    const formatInt = (v) => `${Math.round(Number(v || 0))}`

    ctx.save()
    ctx.textAlign = 'center'
    ctx.textBaseline = 'middle'
    ctx.fillStyle = 'rgba(226,232,240,0.92)' // slate-200
    ctx.font = '11px ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto'

    // BAR (vertical u horizontal)
    if (type === 'bar') {
      const meta0 = chart.getDatasetMeta(0)
      if (!meta0 || meta0.hidden) {
        ctx.restore()
        return
      }

      const isHorizontal = chart.options?.indexAxis === 'y'
      const datasets = chart.data.datasets || []
      const maxLabels = 14 // evita saturación

      datasets.forEach((ds, di) => {
        const meta = chart.getDatasetMeta(di)
        if (!meta || meta.hidden) return

        meta.data.forEach((el, i) => {
          if (!el) return
          if (meta.data.length > maxLabels && i % 2 === 1) return // si hay muchos, etiqueta intercalada

          const raw = ds.data?.[i]
          const label =
            // si parece dinero (valores grandes) usa compacto
            Number(raw || 0) >= 100000 ? formatCompactMoney(raw) : formatInt(raw)

          const x = isHorizontal ? el.x + 18 : el.x
          const y = isHorizontal ? el.y : el.y - 12

          // pequeña sombra para legibilidad
          ctx.save()
          ctx.fillStyle = 'rgba(15,23,42,0.75)'
          ctx.fillText(label, x + 1, y + 1)
          ctx.restore()

          ctx.fillText(label, x, y)
        })
      })

      ctx.restore()
      return
    }

    // LINE
    if (type === 'line') {
      const datasets = chart.data.datasets || []
      const labelsCount = chart.data.labels?.length || 0

      // si es muy denso, solo etiqueta último punto por dataset
      const dense = labelsCount > 10

      datasets.forEach((ds, di) => {
        const meta = chart.getDatasetMeta(di)
        if (!meta || meta.hidden) return

        meta.data.forEach((el, i) => {
          if (!el) return
          if (dense && i !== meta.data.length - 1) return

          const raw = ds.data?.[i]
          const label = formatInt(raw)
          const x = el.x
          const y = el.y - 12

          ctx.save()
          ctx.fillStyle = 'rgba(15,23,42,0.75)'
          ctx.fillText(label, x + 1, y + 1)
          ctx.restore()

          ctx.fillText(label, x, y)
        })
      })

      ctx.restore()
      return
    }

    // DOUGHNUT: dibujar total al centro
    if (type === 'doughnut') {
      const ds = chart.data.datasets?.[0]
      if (!ds) {
        ctx.restore()
        return
      }
      const total = (ds.data || []).reduce((a, b) => a + Number(b || 0), 0)
      const { width, height } = chart

      ctx.save()
      ctx.textAlign = 'center'
      ctx.fillStyle = 'rgba(226,232,240,0.95)'
      ctx.font = '600 14px ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto'
      ctx.fillText('Total', width / 2, height / 2 - 10)

      ctx.font = '700 18px ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto'
      ctx.fillText(`${Math.round(total)}`, width / 2, height / 2 + 12)
      ctx.restore()

      ctx.restore()
      return
    }

    ctx.restore()
  },
}

ChartJS.register(
  ArcElement,
  BarElement,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Tooltip,
  Legend,
  ValueLabelsPlugin
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

const activeTab = ref('resumen')

function qs(obj) {
  return new URLSearchParams(obj).toString()
}

/* ==============================
   FILTROS
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

function formatDate(dateStr) {
  if (!dateStr) return '—'
  return new Date(dateStr).toISOString().split('T')[0]
}

/* ==============================
   GRÁFICA 1: Ventas por proyecto
============================== */
const ventasProyectoData = computed(() => {
  const rows = props.ventasPorProyecto || []
  const labels = rows.map((p) => p.nombre)
  const data = rows.map((p) => Number(p.total_valor || 0))

  return {
    labels,
    datasets: [
      {
        label: 'Ventas (COP)',
        data,
        backgroundColor: 'rgba(56, 189, 248, 0.75)',
        borderColor: 'rgba(56, 189, 248, 1)',
        borderWidth: 1.5,
        borderRadius: 10,
        maxBarThickness: 44,
      },
    ],
  }
})

const barChartOptions = computed(() => {
  const labelsCount = (props.ventasPorProyecto || []).length
  const many = labelsCount > 10

  return {
    responsive: true,
    maintainAspectRatio: false,
    layout: { padding: { top: 18, right: 10, left: 8, bottom: 0 } }, // espacio para labels
    interaction: { mode: 'index', intersect: false },
    plugins: {
      legend: {
        display: true,
        labels: { color: '#e5e7eb', font: { size: 11 } },
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
        ticks: {
          color: '#9ca3af',
          maxRotation: many ? 45 : 0,
          minRotation: many ? 45 : 0,
          autoSkip: true,
        },
        grid: { color: 'rgba(55, 65, 81, 0.35)' },
      },
      y: {
        ticks: {
          color: '#9ca3af',
          callback(value) {
            // eje compacto
            const n = Number(value || 0)
            if (Math.abs(n) >= 1_000_000_000) return `${Math.round(n / 1_000_000_000)}B`
            if (Math.abs(n) >= 1_000_000) return `${Math.round(n / 1_000_000)}M`
            if (Math.abs(n) >= 1_000) return `${Math.round(n / 1_000)}k`
            return `${Math.round(n)}`
          },
        },
        grid: { color: 'rgba(55, 65, 81, 0.35)' },
      },
    },
  }
})

const ventasProyectoTabla = computed(() => {
  return (props.ventasPorProyecto || [])
    .map((r) => ({
      nombre: r.nombre,
      total: Number(r.total_valor || 0),
      unidades: Number(r.unidades || 0),
    }))
    .sort((a, b) => b.total - a.total)
})

/* ==============================
   GRÁFICA 2: Estado del inventario (TABLA)
============================== */
const proyectoInventarioSeleccionado = ref('')

const INVENTARIO_ESTADOS = ['Disponible', 'Vendido', 'Separado', 'No Disponible', 'Congelado']

const inventarioOptions = computed(() => {
  const base = [{ value: '', label: 'Todos los proyectos' }]
  ;(props.estadoInventario || []).forEach((p) =>
    base.push({ value: p.proyecto, label: p.proyecto })
  )
  return base
})

/**
 * Proyectos que se muestran como columnas:
 * - Si hay proyecto seleccionado: 1 columna (ese proyecto)
 * - Si no: todas las columnas (todos los proyectos)
 */
const inventarioColumnasProyectos = computed(() => {
  const src = props.estadoInventario || []
  if (!src.length) return []
  if (proyectoInventarioSeleccionado.value) return [proyectoInventarioSeleccionado.value]
  return src.map((p) => p.proyecto)
})

/**
 * Construye una fila por estado:
 * row = { estado, porProyecto: { [proyecto]: count }, total, pct }
 */
const inventarioTablaPorProyecto = computed(() => {
  const src = props.estadoInventario || []
  const cols = inventarioColumnasProyectos.value
  if (!src.length || !cols.length) return []

  // mapa rápido proyecto -> estados
  const mapProj = new Map(src.map((p) => [p.proyecto, p.estados || {}]))

  const rows = INVENTARIO_ESTADOS.map((estado) => {
    const porProyecto = {}
    let total = 0

    cols.forEach((proy) => {
      const v = Number(mapProj.get(proy)?.[estado] ?? 0)
      porProyecto[proy] = v
      total += v
    })

    return { estado, porProyecto, total }
  })

  const totalGlobal = rows.reduce((acc, r) => acc + Number(r.total || 0), 0)

  return rows.map((r) => ({
    ...r,
    pct: totalGlobal > 0 ? ((r.total / totalGlobal) * 100).toFixed(1) : '0.0',
  }))
})

const inventarioTotalGlobal = computed(() => {
  return inventarioTablaPorProyecto.value.reduce((acc, r) => acc + Number(r.total || 0), 0)
})

const inventarioTotalesPorProyecto = computed(() => {
  const rows = inventarioTablaPorProyecto.value
  const cols = inventarioColumnasProyectos.value
  const out = Object.fromEntries(cols.map((c) => [c, 0]))

  rows.forEach((r) => {
    cols.forEach((c) => {
      out[c] += Number(r.porProyecto?.[c] ?? 0)
    })
  })

  return out
})

/**
 * Resumen corto para el footer (sin hover)
 */
const inventarioResumenCompacto = computed(() => {
  return inventarioTablaPorProyecto.value.map((r) => ({
    estado: r.estado,
    total: Number(r.total || 0),
    pct: r.pct,
  }))
})

/* ==============================
   GRÁFICA 3: Ranking asesores
============================== */
const rankingAsesoresData = computed(() => {
  const rows = (props.rankingAsesores || []).slice(0, 12) // top 12 para que sea legible
  const labels = rows.map((r) => r.asesor)
  const data = rows.map((r) => Number(r.total_ventas || 0))

  return {
    labels,
    datasets: [
      {
        label: 'Ventas (COP)',
        data,
        backgroundColor: 'rgba(244, 114, 182, 0.82)',
        borderColor: 'rgba(236, 72, 153, 1)',
        borderWidth: 1.5,
        borderRadius: 10,
        maxBarThickness: 18,
      },
    ],
  }
})

const rankingOptions = {
  responsive: true,
  maintainAspectRatio: false,
  indexAxis: 'y',
  layout: { padding: { top: 10, right: 40, left: 8, bottom: 0 } }, // espacio label final
  plugins: {
    legend: { display: false },
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
      ticks: {
        color: '#9ca3af',
        callback(value) {
          const n = Number(value || 0)
          if (Math.abs(n) >= 1_000_000_000) return `${Math.round(n / 1_000_000_000)}B`
          if (Math.abs(n) >= 1_000_000) return `${Math.round(n / 1_000_000)}M`
          if (Math.abs(n) >= 1_000) return `${Math.round(n / 1_000)}k`
          return `${Math.round(n)}`
        },
      },
      grid: { color: 'rgba(55, 65, 81, 0.35)' },
    },
    y: {
      ticks: {
        color: '#9ca3af',
        autoSkip: false,
        font: { size: 10 },
      },
      grid: { display: false },
    },
  },
}

const rankingTabla = computed(() => {
  return (props.rankingAsesores || [])
    .map((r) => ({ asesor: r.asesor, total: Number(r.total_ventas || 0) }))
    .sort((a, b) => b.total - a.total)
})

/* ==============================
   GRÁFICA 4: Absorción mensual
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
    [244, 63, 94],
    [148, 163, 184],
  ]

  const datasets = proyectos.map((nombre, idx) => {
    const rgb = palette[idx % palette.length]
    const border = `rgb(${rgb[0]}, ${rgb[1]}, ${rgb[2]})`
    const bg = `rgba(${rgb[0]}, ${rgb[1]}, ${rgb[2]}, 0.22)`

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
      borderWidth: 2,
    }
  })

  return { labels, datasets }
})

const lineOptions = computed(() => {
  const labelsCount = absorcionLineData.value.labels?.length || 0
  const many = labelsCount > 8

  return {
    responsive: true,
    maintainAspectRatio: false,
    layout: { padding: { top: 16, right: 10, left: 8, bottom: 0 } },
    plugins: {
      legend: {
        position: 'bottom',
        labels: { color: '#e5e7eb', font: { size: 10 }, boxWidth: 10 },
      },
      tooltip: {
        callbacks: {
          label(ctx) {
            const v = ctx.parsed.y
            return `Unidades: ${Math.round(Number(v || 0))}`
          },
        },
      },
    },
    scales: {
      x: {
        ticks: {
          color: '#9ca3af',
          maxRotation: many ? 45 : 0,
          minRotation: many ? 45 : 0,
          autoSkip: true,
        },
        grid: { color: 'rgba(55, 65, 81, 0.35)' },
      },
      y: {
        ticks: { color: '#9ca3af', precision: 0 },
        grid: { color: 'rgba(55, 65, 81, 0.35)' },
      },
    },
  }
})

const absorcionTabla = computed(() => {
  const rows = props.absorcionMensual || []
  // total por mes (todas las líneas)
  const byMes = new Map()
  rows.forEach((r) => {
    const mes = r.mes
    const u = Number(r.unidades || 0)
    byMes.set(mes, (byMes.get(mes) || 0) + u)
  })
  return Array.from(byMes.entries())
    .map(([mes, total]) => ({ mes, total }))
    .sort((a, b) => (a.mes > b.mes ? 1 : -1))
})
</script>

<template>
  <GerenciaLayout>
    <Head title="Panel de Gerencia" />

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
      <!-- Grid responsivo (mejor altura + scroll para tablas) -->
      <div class="grid grid-cols-1 lg:grid-cols-2 2xl:grid-cols-4 gap-4">
        <!-- Gráfica 1 -->
        <div
          class="group bg-slate-900/80 border border-slate-800 rounded-2xl p-4 transition transform hover:-translate-y-1 hover:border-sky-500/60 hover:shadow-[0_0_25px_rgba(56,189,248,0.25)]"
        >
          <div class="flex justify-between items-center mb-3">
            <h2 class="text-xs font-semibold text-slate-100 uppercase tracking-wide">
              Ventas por proyecto
            </h2>
            <span class="text-[10px] text-slate-500">COP (con etiqueta)</span>
          </div>

          <div class="h-[240px] sm:h-[260px]">
            <Bar :data="ventasProyectoData" :options="barChartOptions" />
          </div>

          <!-- Datos visibles -->
          <div class="mt-3 border-t border-slate-800 pt-3">
            <div class="flex items-center justify-between">
              <p class="text-[11px] text-slate-400">Detalle (sin hover)</p>
              <p class="text-[11px] text-slate-500">{{ ventasProyectoTabla.length }} proyecto(s)</p>
            </div>

            <div class="mt-2 max-h-[120px] overflow-auto pr-1">
              <div
                v-for="(r, i) in ventasProyectoTabla"
                :key="r.nombre + i"
                class="flex items-center justify-between gap-3 py-1 text-xs"
              >
                <span class="text-slate-200 truncate">{{ r.nombre }}</span>
                <span class="text-slate-100 font-semibold whitespace-nowrap">{{
                  formatMoney(r.total)
                }}</span>
              </div>

              <div v-if="!ventasProyectoTabla.length" class="text-xs text-slate-400 py-2">
                No hay datos para el rango seleccionado.
              </div>
            </div>
          </div>
        </div>

        <!-- Gráfica 2 -->
        <div
          class="group bg-slate-900/80 border border-slate-800 rounded-2xl p-4 transition transform hover:-translate-y-1 hover:border-emerald-500/60 hover:shadow-[0_0_25px_rgba(16,185,129,0.25)]"
        >
          <div class="flex justify-between items-center mb-3">
            <h2 class="text-xs font-semibold text-slate-100 uppercase tracking-wide">
              Estado del inventario
            </h2>

            <div class="flex items-center gap-2">
              <span class="text-[10px] text-slate-500">Proyecto</span>
              <select
                v-model="proyectoInventarioSeleccionado"
                class="bg-slate-900 text-slate-100 border border-slate-700 rounded-lg px-2 py-1 text-xs"
              >
                <option
                  v-for="opt in inventarioOptions"
                  :key="opt.value || 'all'"
                  :value="opt.value"
                >
                  {{ opt.label }}
                </option>
              </select>
            </div>
          </div>

          <!-- Altura IGUAL a la gráfica 1 -->
          <div class="h-[240px] sm:h-[260px]">
            <div class="h-full rounded-xl border border-slate-800 bg-slate-950/30 overflow-hidden">
              <div class="h-full overflow-auto">
                <table class="min-w-full text-xs">
                  <thead
                    class="sticky top-0 bg-slate-900/95 backdrop-blur border-b border-slate-800"
                  >
                    <tr class="text-slate-300">
                      <th class="text-left font-semibold px-3 py-2">Estado</th>

                      <!-- Columnas por proyecto -->
                      <th
                        v-for="p in inventarioColumnasProyectos"
                        :key="p"
                        class="text-right font-semibold px-3 py-2 whitespace-nowrap"
                        :title="p"
                      >
                        <span class="inline-block max-w-[140px] truncate">{{ p }}</span>
                      </th>

                      <th class="text-right font-semibold px-3 py-2">Total</th>
                      <th class="text-right font-semibold px-3 py-2">% Global</th>
                    </tr>
                  </thead>

                  <tbody class="divide-y divide-slate-800/60">
                    <tr
                      v-for="row in inventarioTablaPorProyecto"
                      :key="row.estado"
                      class="hover:bg-slate-900/40"
                    >
                      <td class="px-3 py-2 text-slate-200 whitespace-nowrap">
                        {{ row.estado }}
                      </td>

                      <!-- valores por proyecto -->
                      <td
                        v-for="p in inventarioColumnasProyectos"
                        :key="row.estado + '-' + p"
                        class="px-3 py-2 text-right text-slate-100 tabular-nums"
                      >
                        {{ row.porProyecto[p] ?? 0 }}
                      </td>

                      <td class="px-3 py-2 text-right text-slate-100 font-semibold tabular-nums">
                        {{ row.total }}
                      </td>

                      <td class="px-3 py-2 text-right text-slate-300 tabular-nums">
                        {{ row.pct }} %
                      </td>
                    </tr>

                    <tr v-if="!inventarioTablaPorProyecto.length">
                      <td
                        :colspan="inventarioColumnasProyectos.length + 3"
                        class="px-3 py-6 text-center text-slate-400"
                      >
                        No hay datos de inventario.
                      </td>
                    </tr>
                  </tbody>

                  <tfoot
                    v-if="inventarioTablaPorProyecto.length"
                    class="sticky bottom-0 bg-slate-900/95 backdrop-blur border-t border-slate-800"
                  >
                    <tr class="text-slate-200 font-semibold">
                      <td class="px-3 py-2">TOTAL</td>

                      <td
                        v-for="p in inventarioColumnasProyectos"
                        :key="'tot-' + p"
                        class="px-3 py-2 text-right tabular-nums"
                      >
                        {{ inventarioTotalesPorProyecto[p] ?? 0 }}
                      </td>

                      <td class="px-3 py-2 text-right tabular-nums">
                        {{ inventarioTotalGlobal }}
                      </td>

                      <td class="px-3 py-2 text-right tabular-nums">100.0 %</td>
                    </tr>
                  </tfoot>
                </table>
              </div>
            </div>
          </div>

          <!-- Footer (misma idea que la gráfica 1) -->
          <div class="mt-3 border-t border-slate-800 pt-3">
            <div class="flex items-center justify-between">
              <p class="text-[11px] text-slate-400">Detalle (sin hover)</p>
              <p class="text-[11px] text-slate-500">
                {{ inventarioColumnasProyectos.length }} proyecto(s) •
                {{ inventarioTotalGlobal }} unidad(es)
              </p>
            </div>

            <!-- Scroll corto igual que la gráfica 1 -->
            <div class="mt-2 max-h-[120px] overflow-auto pr-1">
              <div
                v-for="s in inventarioResumenCompacto"
                :key="s.estado"
                class="flex items-center justify-between gap-3 py-1 text-xs"
              >
                <span class="text-slate-200 truncate">{{ s.estado }}</span>
                <span class="text-slate-100 font-semibold whitespace-nowrap">
                  {{ s.total }} ({{ s.pct }} %)
                </span>
              </div>

              <div v-if="!inventarioResumenCompacto.length" class="text-xs text-slate-400 py-2">
                No hay datos para el rango seleccionado.
              </div>
            </div>
          </div>
        </div>

        <!-- Gráfica 3 -->
        <div
          class="group bg-slate-900/80 border border-slate-800 rounded-2xl p-4 transition transform hover:-translate-y-1 hover:border-pink-500/60 hover:shadow-[0_0_25px_rgba(236,72,153,0.25)]"
        >
          <div class="flex justify-between items-center mb-3">
            <h2 class="text-xs font-semibold text-slate-100 uppercase tracking-wide">
              Ranking asesores
            </h2>
            <span class="text-[10px] text-slate-500">Top 12</span>
          </div>

          <div class="h-[240px] sm:h-[260px]">
            <Bar :data="rankingAsesoresData" :options="rankingOptions" />
          </div>

          <!-- Datos visibles -->
          <div class="mt-3 border-t border-slate-800 pt-3">
            <div class="flex items-center justify-between">
              <p class="text-[11px] text-slate-400">Detalle (sin hover)</p>
              <p class="text-[11px] text-slate-500">{{ rankingTabla.length }} asesor(es)</p>
            </div>

            <div class="mt-2 max-h-[120px] overflow-auto pr-1">
              <div
                v-for="(r, i) in rankingTabla"
                :key="r.asesor + i"
                class="flex items-center justify-between gap-3 py-1 text-xs"
              >
                <span class="text-slate-200 truncate">{{ i + 1 }}. {{ r.asesor }}</span>
                <span class="text-slate-100 font-semibold whitespace-nowrap">{{
                  formatMoney(r.total)
                }}</span>
              </div>

              <div v-if="!rankingTabla.length" class="text-xs text-slate-400 py-2">
                No hay datos de ventas registrados.
              </div>
            </div>
          </div>
        </div>

        <!-- Gráfica 4 -->
        <div
          class="group bg-slate-900/80 border border-slate-800 rounded-2xl p-4 transition transform hover:-translate-y-1 hover:border-indigo-500/60 hover:shadow-[0_0_25px_rgba(129,140,248,0.25)]"
        >
          <div class="flex justify-between items-center mb-3">
            <h2 class="text-xs font-semibold text-slate-100 uppercase tracking-wide">
              Absorción mensual
            </h2>
            <span class="text-[10px] text-slate-500">Unidades / mes</span>
          </div>

          <div class="h-[240px] sm:h-[260px]">
            <Line :data="absorcionLineData" :options="lineOptions" />
          </div>

          <!-- Datos visibles -->
          <div class="mt-3 border-t border-slate-800 pt-3">
            <div class="flex items-center justify-between">
              <p class="text-[11px] text-slate-400">Total unidades por mes</p>
              <p class="text-[11px] text-slate-500">{{ absorcionTabla.length }} mes(es)</p>
            </div>

            <div class="mt-2 max-h-[120px] overflow-auto pr-1">
              <div
                v-for="r in absorcionTabla"
                :key="r.mes"
                class="flex items-center justify-between gap-3 py-1 text-xs"
              >
                <span class="text-slate-200">{{ r.mes }}</span>
                <span class="text-slate-100 font-semibold">{{ r.total }}</span>
              </div>

              <div v-if="!absorcionTabla.length" class="text-xs text-slate-400 py-2">
                No hay ventas para construir la serie mensual.
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- TAB: PLAN DE PAGOS DE CUOTA INICIAL -->
    <div v-else-if="activeTab === 'plan_ci'" class="space-y-4">
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
                <td class="py-2 pr-2 text-slate-200">{{ row.proyecto }}</td>
                <td class="py-2 pr-2 text-slate-100">{{ row.empleado }}</td>
                <td class="py-2 pr-2 text-right text-sky-300">{{ row.ventas }}</td>
                <td class="py-2 pr-2 text-right text-slate-100">{{ row.separaciones }}</td>
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
