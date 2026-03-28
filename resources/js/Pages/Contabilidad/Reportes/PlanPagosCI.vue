<script setup>
import { Head, router } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'
import ContabilidadLayout from '@/Components/ContabilidadLayout.vue'

const props = defineProps({
  empleado: Object,
  planPagosCI: Object,
  proyectos: Array,
  filtros: Object,
})

const filtros = ref({
  ano: props.filtros?.ano || '',
  mes: props.filtros?.mes || '',
  desde: props.filtros?.desde || '',
  hasta: props.filtros?.hasta || '',
  id_proyecto: props.filtros?.id_proyecto || '',
  asesor_id: props.filtros?.asesor_id || '',
  buscar_cliente: props.filtros?.buscar_cliente || '',
})

const meses = [
  { n: 1, name: 'Enero' },
  { n: 2, name: 'Febrero' },
  { n: 3, name: 'Marzo' },
  { n: 4, name: 'Abril' },
  { n: 5, name: 'Mayo' },
  { n: 6, name: 'Junio' },
  { n: 7, name: 'Julio' },
  { n: 8, name: 'Agosto' },
  { n: 9, name: 'Septiembre' },
  { n: 10, name: 'Octubre' },
  { n: 11, name: 'Noviembre' },
  { n: 12, name: 'Diciembre' },
]

let debounceTimer = null

function aplicarConDebounce() {
  clearTimeout(debounceTimer)

  debounceTimer = setTimeout(() => {
    aplicar()
  }, 500)
}

watch(
  () => filtros.value.buscar_cliente,
  () => {
    aplicarConDebounce()
  }
)

watch(
  () => [
    filtros.value.ano,
    filtros.value.mes,
    filtros.value.desde,
    filtros.value.hasta,
    filtros.value.id_proyecto,
    filtros.value.asesor_id,
  ],
  () => {
    aplicar()
  }
)

watch(
  () => filtros.value.mes,
  (value) => {
    if (value) {
      filtros.value.desde = ''
      filtros.value.hasta = ''
    }
  }
)

watch(
  () => [filtros.value.desde, filtros.value.hasta],
  ([desde, hasta]) => {
    if (desde || hasta) {
      filtros.value.mes = ''
    }
  }
)

function limpiarFiltros() {
  filtros.value = {
    ano: '',
    mes: '',
    desde: '',
    hasta: '',
    id_proyecto: '',
    asesor_id: '',
    buscar_cliente: '',
  }

  aplicar()
}

function exportar() {
  const qs = new URLSearchParams({ ...filtros.value }).toString()
  window.location.href = `/contabilidad/reportes/plan-pagos-ci/export?${qs}`
}

function formatMoney(v) {
  return Number(v || 0).toLocaleString('es-CO', {
    style: 'currency',
    currency: 'COP',
    maximumFractionDigits: 0,
  })
}

function aplicar() {
  router.get(
    '/contabilidad/reportes/plan-pagos-ci',
    { ...filtros.value },
    {
      preserveState: true,
      preserveScroll: true,
      replace: true,
    }
  )
}

const tieneFilas = computed(() => props.planPagosCI?.filas?.length)
</script>

<template>
  <ContabilidadLayout>
    <Head title="Contabilidad · Plan Pagos CI" />

    <div class="space-y-6">
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4">
          <div>
            <h1 class="text-2xl font-bold text-gray-900">Plan de Pagos · Cuota Inicial</h1>
            <p class="text-sm text-gray-600 mt-1">
              Consolidado contable por meses con exportación a Excel.
            </p>
          </div>

          <div class="flex items-center gap-3">
            <button
              @click="limpiarFiltros"
              class="px-4 py-2 rounded-lg border border-gray-300 bg-white hover:bg-gray-50 text-sm font-semibold text-gray-700 transition"
            >
              Limpiar filtros
            </button>
            <button
              @click="exportar"
              class="px-5 py-2.5 rounded-lg border border-gray-300 bg-white hover:bg-gray-50 text-gray-700 font-semibold text-sm transition"
            >
              Exportar Excel
            </button>
          </div>
        </div>

        <div class="mt-6 grid grid-cols-1 md:grid-cols-6 gap-4">
          <div class="md:col-span-1">
            <label class="block text-xs font-medium text-gray-600 mb-1">Buscar cliente</label>
            <input
              v-model="filtros.buscar_cliente"
              type="text"
              placeholder="Nombre o número de documento"
              class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-[#FFEA00] focus:border-transparent"
            />
          </div>
          <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Año</label>
            <input
              v-model="filtros.ano"
              type="number"
              placeholder="Año"
              class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-[#FFEA00] focus:border-transparent"
            />
          </div>

          <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Mes</label>
            <select
              v-model="filtros.mes"
              class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#FFEA00] focus:border-transparent"
            >
              <option value="">Todos / rango manual</option>
              <option v-for="m in meses" :key="m.n" :value="m.n">{{ m.name }}</option>
            </select>
          </div>

          <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Desde</label>
            <input
              v-model="filtros.desde"
              type="date"
              class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#FFEA00] focus:border-transparent"
            />
          </div>

          <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Hasta</label>
            <input
              v-model="filtros.hasta"
              type="date"
              class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#FFEA00] focus:border-transparent"
            />
          </div>

          <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Proyecto</label>
            <select
              v-model="filtros.id_proyecto"
              class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#FFEA00] focus:border-transparent"
            >
              <option value="">Todos</option>
              <option v-for="p in proyectos" :key="p.id_proyecto" :value="p.id_proyecto">
                {{ p.nombre }}
              </option>
            </select>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 overflow-x-auto">
        <table v-if="tieneFilas" class="min-w-full text-sm">
          <thead>
            <tr class="bg-gray-50 text-gray-600">
              <th class="p-3 border border-gray-200 text-left">Proyecto</th>
              <th class="p-3 border border-gray-200 text-left">Inmueble</th>
              <th class="p-3 border border-gray-200 text-left">Cliente</th>
              <th class="p-3 border border-gray-200 text-left">Documento</th>
              <th
                v-for="m in planPagosCI.encabezados"
                :key="m"
                class="p-3 border border-gray-200 text-center"
              >
                {{ m }}
              </th>
            </tr>
          </thead>

          <tbody>
            <tr v-for="(f, i) in planPagosCI.filas" :key="i" class="even:bg-gray-50">
              <td class="p-3 border border-gray-200">{{ f.proyecto }}</td>
              <td class="p-3 border border-gray-200">{{ f.inmueble }}</td>
              <td class="p-3 border border-gray-200">{{ f.cliente }}</td>
              <td class="p-3 border border-gray-200">{{ f.documento_cliente }}</td>
              <td
                v-for="m in planPagosCI.encabezados"
                :key="m"
                class="p-3 border border-gray-200 text-right"
              >
                {{ formatMoney(f.meses[m] || 0) }}
              </td>
            </tr>

            <tr class="bg-gray-100 font-semibold">
              <td class="p-3 border border-gray-200" colspan="4">TOTAL</td>
              <td
                v-for="m in planPagosCI.encabezados"
                :key="'tot-' + m"
                class="p-3 border border-gray-200 text-right"
              >
                {{ formatMoney(planPagosCI.totales[m]) }}
              </td>
            </tr>
          </tbody>
        </table>

        <div v-else class="text-center text-gray-500 py-12">
          No hay ventas con cuota inicial en el rango seleccionado.
        </div>
      </div>
    </div>
  </ContabilidadLayout>
</template>
