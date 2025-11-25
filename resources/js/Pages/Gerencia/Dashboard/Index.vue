<script setup>
import { ref, computed } from 'vue'
import GerenciaLayout from '@/Components/GerenciaLayout.vue'

import { Chart as ChartJS, BarElement, CategoryScale, LinearScale, Tooltip, Legend } from 'chart.js'
import { Bar } from 'vue-chartjs'

ChartJS.register(BarElement, CategoryScale, LinearScale, Tooltip, Legend)

const props = defineProps({
  resumenGlobal: Object,
  ventasPorProyecto: Array,
  proyeccionVsReal: Array,
  velocidadVentas: Array,
  separacionesEfectiv: Array,
  inventarioProyectos: Array,
  ventasAsesoresProyecto: {
    type: Array,
    default: () => [],
  },
})

const activeTab = ref('resumen')

/* ==============================
   FORMATOS
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
   GRÁFICA: Ventas por proyecto
============================== */
const ventasProyectoData = computed(() => {
  const labels = props.ventasPorProyecto.map((p) => p.nombre)
  const data = props.ventasPorProyecto.map((p) => p.total_valor)

  return {
    labels,
    datasets: [
      {
        label: 'Ventas por proyecto',
        data,
        backgroundColor: '#38bdf8', // cyan
        borderColor: '#0ea5e9',
        borderWidth: 1.5,
      },
    ],
  }
})

/* ==============================
   GRÁFICA: Proyección vs Real
============================== */
const proyeccionData = computed(() => {
  const labels = props.proyeccionVsReal.map((p) => p.nombre)

  return {
    labels,
    datasets: [
      {
        label: 'Meta (valor)',
        data: props.proyeccionVsReal.map((p) => p.meta_valor),
        backgroundColor: '#f97316', // naranja
        borderColor: '#ea580c',
        borderWidth: 1.5,
      },
      {
        label: 'Real (valor)',
        data: props.proyeccionVsReal.map((p) => p.real_valor),
        backgroundColor: '#22c55e', // verde
        borderColor: '#16a34a',
        borderWidth: 1.5,
      },
    ],
  }
})

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      labels: {
        color: '#e5e7eb', // text-gray-200
        font: {
          size: 11,
        },
      },
    },
  },
  scales: {
    x: {
      ticks: {
        color: '#9ca3af', // gray-400
      },
      grid: {
        color: 'rgba(55, 65, 81, 0.4)', // gray-700
      },
    },
    y: {
      ticks: {
        color: '#9ca3af',
      },
      grid: {
        color: 'rgba(55, 65, 81, 0.4)',
      },
    },
  },
}
</script>

<template>
  <GerenciaLayout>
    <!-- Encabezado -->
    <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="text-3xl font-semibold text-slate-50">Tablero de Gerencia</h1>
        <p class="text-slate-400 text-sm">
          Visión consolidada de proyectos, ventas, inventario y desempeño comercial.
        </p>
      </div>
    </div>

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

    <!-- Pestañas -->
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

    <!-- TAB: RESUMEN -->
    <div v-if="activeTab === 'resumen'" class="space-y-6">
      <!-- Gráficas -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Ventas por proyecto -->
        <div
          class="bg-slate-900/80 border border-slate-800 rounded-2xl p-4"
          style="height: 320px; overflow: hidden"
        >
          <div class="flex justify-between items-center mb-3">
            <h2 class="text-sm font-semibold text-slate-100">Ventas por proyecto</h2>
            <span class="text-xs text-slate-500">Consolidado actual</span>
          </div>
          <Bar :data="ventasProyectoData" :options="chartOptions" />
        </div>

        <!-- Proyección vs real -->
        <div
          class="bg-slate-900/80 border border-slate-800 rounded-2xl p-4"
          style="height: 320px; overflow: hidden"
        >
          <div class="flex justify-between items-center mb-3">
            <h2 class="text-sm font-semibold text-slate-100">Proyección vs ventas reales</h2>
            <span class="text-xs text-slate-500">Mes actual</span>
          </div>
          <Bar :data="proyeccionData" :options="chartOptions" />
        </div>
      </div>

      <!-- Velocidad de ventas + separaciones -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Velocidad de ventas -->
        <div
          class="bg-slate-900/80 border border-slate-800 rounded-2xl p-4"
          style="height: 320px; overflow: hidden"
        >
          <h2 class="text-sm font-semibold text-slate-100 mb-3">
            Velocidad de ventas por proyecto
          </h2>
          <table class="w-full text-sm">
            <thead>
              <tr class="text-slate-400 border-b border-slate-800">
                <th class="py-2 text-left">Proyecto</th>
                <th class="py-2 text-right">Días promedio</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="item in velocidadVentas"
                :key="item.proyecto"
                class="border-b border-slate-800/60"
              >
                <td class="py-2 text-slate-200">{{ item.proyecto }}</td>
                <td class="py-2 text-right text-slate-100">{{ item.dias_promedio_venta }}</td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Separaciones / efectividad -->
        <div
          class="bg-slate-900/80 border border-slate-800 rounded-2xl p-4"
          style="height: 320px; overflow: hidden"
        >

          <h2 class="text-sm font-semibold text-slate-100 mb-3">
            Separaciones, caducidades y efectividad
          </h2>
          <table class="w-full text-sm">
            <thead>
              <tr class="text-slate-400 border-b border-slate-800">
                <th class="py-2 text-left">Asesor</th>
                <th class="py-2 text-right">Separaciones</th>
                <th class="py-2 text-right">Ejecutadas</th>
                <th class="py-2 text-right">Caducadas</th>
                <th class="py-2 text-right">% Efectividad</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="s in separacionesEfectiv"
                :key="s.id_empleado"
                class="border-b border-slate-800/60"
              >
                <td class="py-2 text-slate-200">
                  {{ s.empleado }}
                </td>
                <td class="py-2 text-right text-slate-100">{{ s.total_separaciones }}</td>
                <td class="py-2 text-right text-emerald-300">
                  {{ s.separaciones_ejecutadas }}
                </td>
                <td class="py-2 text-right text-rose-300">
                  {{ s.separaciones_caducadas }}
                </td>
                <td class="py-2 text-right text-slate-100">
                  {{
                    formatPercent(
                      s.separaciones_ejecutadas,
                      s.separaciones_ejecutadas + s.separaciones_caducadas
                    )
                  }}
                </td>
              </tr>
            </tbody>
          </table>
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
                <td class="py-2 pr-2 text-right">
                  <span
                    :class="[
                      'px-2 py-1 rounded-full text-xs',
                      i.disponible
                        ? 'bg-emerald-500/10 text-emerald-300 border border-emerald-500/40'
                        : 'bg-slate-800 text-slate-200 border border-slate-700',
                    ]"
                  >
                    {{ formatMoney(i.precio_vigente) }}
                  </span>
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
        style="height: 320px; overflow: hidden"
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
        style="height: 320px; overflow: hidden"
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
                v-for="row in props.ventasAsesoresProyecto"
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
              <tr v-if="!props.ventasAsesoresProyecto.length">
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
