<script setup>
import { Head, router } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import ContabilidadLayout from '@/Components/ContabilidadLayout.vue'

const props = defineProps({
  consolidadoComisiones: { type: Array, default: () => [] },
  proyectos: { type: Array, default: () => [] },
  filtros: { type: Object, default: () => ({}) },
  empleado: Object,
})

const q = ref(props.filtros?.q || '')
const selectedProyecto = ref(props.filtros?.proyecto_id || '')
const desde = ref(props.filtros?.desde || '')
const hasta = ref(props.filtros?.hasta || '')

function formatMoney(v) {
  return Number(v || 0).toLocaleString('es-CO', {
    style: 'currency',
    currency: 'COP',
    maximumFractionDigits: 0,
  })
}

function normalizeText(value) {
  return String(value || '')
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .toLowerCase()
    .trim()
}

function aplicarFiltros() {
  router.get(
    '/contabilidad/comisiones',
    {
      proyecto_id: selectedProyecto.value || '',
      desde: desde.value || '',
      hasta: hasta.value || '',
      q: q.value || '',
    },
    {
      preserveState: true,
      preserveScroll: true,
      replace: true,
    }
  )
}

function limpiarFiltros() {
  q.value = ''
  selectedProyecto.value = ''
  desde.value = ''
  hasta.value = ''
  aplicarFiltros()
}

const proyectosList = computed(() =>
  [...(props.proyectos || [])].sort((a, b) => String(a.nombre).localeCompare(String(b.nombre)))
)

const consolidadoFiltrado = computed(() => {
  const term = normalizeText(q.value)

  return (props.consolidadoComisiones || [])
    .map((proyecto) => {
      const empleados = [...(proyecto.empleados || [])].filter((item) => {
        const searchable = [
          proyecto.nombre,
          item.cargo,
          item.nombre_completo,
        ]
          .map(normalizeText)
          .join(' ')

        const matchQ = !term || searchable.includes(term)
        return matchQ
      })

      return {
        ...proyecto,
        empleados,
      }
    })
    .filter((proyecto) => {
      const matchProyecto =
        !selectedProyecto.value || Number(proyecto.id_proyecto) === Number(selectedProyecto.value)

      return matchProyecto && proyecto.empleados.length > 0
    })
})

const totalEmpleados = computed(() =>
  consolidadoFiltrado.value.reduce((acc, proyecto) => acc + Number(proyecto.empleados.length || 0), 0)
)

const totalComisionVenta = computed(() =>
  consolidadoFiltrado.value.reduce(
    (acc, proyecto) =>
      acc +
      proyecto.empleados.reduce(
        (sum, item) => sum + Number(item.total_comisiones_venta || 0),
        0
      ),
    0
  )
)

const totalComisionEquipo = computed(() =>
  consolidadoFiltrado.value.reduce(
    (acc, proyecto) =>
      acc +
      proyecto.empleados.reduce(
        (sum, item) => sum + Number(item.total_comisiones_equipo || 0),
        0
      ),
    0
  )
)

const totalComisionPagar = computed(() =>
  consolidadoFiltrado.value.reduce(
    (acc, proyecto) =>
      acc +
      proyecto.empleados.reduce(
        (sum, item) => sum + Number(item.total_comisiones_pagar || 0),
        0
      ),
    0
  )
)

const resumenCargos = computed(() => {
  const base = {}

  consolidadoFiltrado.value.forEach((proyecto) => {
    proyecto.empleados.forEach((item) => {
      const key = item.cargo || 'Sin cargo'
      base[key] = (base[key] || 0) + 1
    })
  })

  return Object.entries(base)
    .map(([nombre, total]) => ({ nombre, total }))
    .sort((a, b) => b.total - a.total)
})
</script>

<template>
  <ContabilidadLayout>
    <Head title="Contabilidad · Consolidado Comisiones" />

    <div class="space-y-6">
      <!-- Encabezado -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
        <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4">
          <div>
            <h1 class="text-2xl font-bold text-gray-900">Consolidado de Comisiones</h1>
            <p class="text-sm text-gray-600 mt-1">
              Consulta por proyecto el total de comisiones por venta, por equipo y total a pagar.
            </p>
          </div>
        </div>

        <!-- Filtros -->
        <div class="mt-5 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-3">
          <input
            v-model="q"
            type="text"
            placeholder="Buscar proyecto, cargo o empleado…"
            class="xl:col-span-2 w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-[#FFEA00] focus:border-transparent"
          />

          <select
            v-model="selectedProyecto"
            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#FFEA00] focus:border-transparent"
          >
            <option value="">Todos los proyectos</option>
            <option v-for="p in proyectosList" :key="p.id_proyecto" :value="p.id_proyecto">
              {{ p.nombre }}
            </option>
          </select>

          <input
            v-model="desde"
            type="date"
            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#FFEA00] focus:border-transparent"
          />

          <input
            v-model="hasta"
            type="date"
            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#FFEA00] focus:border-transparent"
          />
        </div>

        <div class="mt-4 flex justify-end gap-2">
          <button
            @click="aplicarFiltros"
            class="px-4 py-2 rounded-lg bg-[#FFEA00] hover:bg-[#f2dc00] text-[#474100] text-sm font-semibold transition"
          >
            Aplicar filtros
          </button>

          <button
            @click="limpiarFiltros"
            class="px-4 py-2 rounded-lg border border-gray-300 bg-white hover:bg-gray-50 text-sm font-semibold text-gray-700 transition"
          >
            Limpiar filtros
          </button>
        </div>
      </div>
      <!-- Contenedores por proyecto -->
      <div
        v-for="proyecto in consolidadoFiltrado"
        :key="proyecto.id_proyecto"
        class="bg-white rounded-xl shadow-sm border border-gray-200 p-5"
      >
        <div class="flex items-center justify-between mb-4">
          <div>
            <h2 class="text-lg font-bold text-gray-900">
              {{ proyecto.nombre }}
            </h2>
            <p class="text-sm text-gray-500">
              Consolidado de comisiones del proyecto
            </p>
          </div>

          <div class="bg-gray-50 rounded-lg border border-gray-200 px-4 py-2">
            <p class="text-xs text-gray-500">Empleados</p>
            <p class="text-lg font-bold text-gray-900">{{ proyecto.empleados.length }}</p>
          </div>
        </div>

        <div class="overflow-x-auto">
          <table class="min-w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
              <tr class="text-gray-600 text-xs uppercase tracking-wide">
                <th class="px-4 py-3 text-center">Cargo</th>
                <th class="px-4 py-3 text-center">Nombre Completo Empleado</th>
                <th class="px-4 py-3 text-center">Número total de ventas</th>
                <th class="px-4 py-3 text-center">Total Comisiones por Venta</th>
                <th class="px-4 py-3 text-center">Total Comisiones por Equipo</th>
                <th class="px-4 py-3 text-center">Total Comisiones a Pagar</th>
              </tr>
            </thead>

            <tbody class="divide-y divide-gray-200">
              <tr
                v-for="(item, index) in proyecto.empleados"
                :key="`${proyecto.id_proyecto}-${item.id_empleado || index}`"
                class="hover:bg-gray-50"
              >
                <td class="px-4 py-3 text-gray-700 text-center">
                  {{ item.cargo || '—' }}
                </td>

                <td class="px-4 py-3 text-gray-900 font-semibold text-center">
                  {{ item.nombre_completo || '—' }}
                </td>

                <td class="px-4 py-3 text-center text-gray-700">
                  {{ item.numero_total_ventas || 0 }}
                </td>

                <td class="px-4 py-3 text-center text-gray-700">
                  {{ formatMoney(item.total_comisiones_venta || 0) }}
                </td>

                <td class="px-4 py-3 text-center text-gray-700">
                  {{ formatMoney(item.total_comisiones_equipo || 0) }}
                </td>

                <td class="px-4 py-3 text-center font-semibold text-[#D1C000]">
                  {{ formatMoney(item.total_comisiones_pagar || 0) }}
                </td>
              </tr>

              <tr v-if="!proyecto.empleados.length">
                <td colspan="6" class="px-4 py-10 text-center text-gray-500">
                  Este proyecto no tiene empleados con consolidado de comisiones.
                </td>
              </tr>
            </tbody>

            <tfoot v-if="proyecto.empleados.length" class="bg-gray-50 border-t border-gray-200">
              <tr>
                <td colspan="3" class="px-4 py-3 text-right font-semibold text-gray-700">
                  Totales
                </td>
                <td class="px-4 py-3 text-center font-semibold text-gray-900">
                  {{
                    formatMoney(
                      proyecto.empleados.reduce(
                        (acc, item) => acc + Number(item.total_comisiones_venta || 0),
                        0
                      )
                    )
                  }}
                </td>
                <td class="px-4 py-3 text-center font-semibold text-gray-900">
                  {{
                    formatMoney(
                      proyecto.empleados.reduce(
                        (acc, item) => acc + Number(item.total_comisiones_equipo || 0),
                        0
                      )
                    )
                  }}
                </td>
                <td class="px-4 py-3 text-center font-bold text-[#D1C000]">
                  {{
                    formatMoney(
                      proyecto.empleados.reduce(
                        (acc, item) => acc + Number(item.total_comisiones_pagar || 0),
                        0
                      )
                    )
                  }}
                </td>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>

      <div
        v-if="!consolidadoFiltrado.length"
        class="bg-white rounded-xl shadow-sm border border-gray-200 px-6 py-12 text-center text-gray-500"
      >
        No hay resultados para los filtros actuales.
      </div>
    </div>
  </ContabilidadLayout>
</template>