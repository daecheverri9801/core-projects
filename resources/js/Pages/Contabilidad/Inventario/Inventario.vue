<script setup>
import { Head, router } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import ContabilidadLayout from '@/Components/ContabilidadLayout.vue'

const props = defineProps({
  inventarioProyectos: { type: Array, default: () => [] },
  proyectos: { type: Array, default: () => [] },
  estadosInmueble: { type: Array, default: () => [] },
  filtros: { type: Object, default: () => ({}) },
  empleado: Object,
})

const q = ref(props.filtros?.q || '')
const selectedProyecto = ref(props.filtros?.proyecto_id || '')
const selectedEstado = ref(props.filtros?.estado_inmueble || '')
const selectedTipo = ref(props.filtros?.tipo || '')
const desde = ref(props.filtros?.desde || '')
const hasta = ref(props.filtros?.hasta || '')

function formatMoney(v) {
  return Number(v || 0).toLocaleString('es-CO', {
    style: 'currency',
    currency: 'COP',
    maximumFractionDigits: 0,
  })
}

function formatDate(dateStr) {
  if (!dateStr) return '—'
  return new Date(dateStr).toISOString().split('T')[0]
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
    '/contabilidad/inventario',
    {
      proyecto_id: selectedProyecto.value || '',
      estado_inmueble: selectedEstado.value || '',
      tipo: selectedTipo.value || '',
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
  selectedEstado.value = ''
  selectedTipo.value = ''
  desde.value = ''
  hasta.value = ''
  aplicarFiltros()
}

const proyectosList = computed(() =>
  [...(props.proyectos || [])].sort((a, b) => String(a.nombre).localeCompare(String(b.nombre)))
)

const inventarioProyectosFiltrado = computed(() => {
  const term = normalizeText(q.value)

  return (props.inventarioProyectos || [])
    .map((proyecto) => {
      const inmueblesOrdenados = [...(proyecto.inmuebles || [])].sort((a, b) => {
        const aIsApto = normalizeText(a?.tipo) === 'apartamento'
        const bIsApto = normalizeText(b?.tipo) === 'apartamento'

        if (aIsApto && !bIsApto) return -1
        if (!aIsApto && bIsApto) return 1

        const getNum = (x) => {
          const raw = String(x?.etiqueta ?? '').trim()
          const m = raw.match(/\d+/)
          return m ? Number(m[0]) : Number.POSITIVE_INFINITY
        }

        const na = getNum(a)
        const nb = getNum(b)

        if (na !== nb) return na - nb

        return String(a?.etiqueta || '').localeCompare(String(b?.etiqueta || ''))
      })

      const inmuebles = inmueblesOrdenados.filter((item) => {
        const matchTipo =
          !selectedTipo.value || normalizeText(item.tipo) === normalizeText(selectedTipo.value)

        const matchEstado =
          !selectedEstado.value ||
          String(item.id_estado_inmueble || '') === String(selectedEstado.value) ||
          normalizeText(item.estado) ===
            normalizeText(
              props.estadosInmueble.find(
                (estado) => String(estado.id_estado_inmueble) === String(selectedEstado.value)
              )?.nombre || ''
            )

        const matchDesde =
          !desde.value ||
          (item.fecha_operacion && new Date(item.fecha_operacion) >= new Date(desde.value))

        const matchHasta =
          !hasta.value ||
          (item.fecha_operacion && new Date(item.fecha_operacion) <= new Date(hasta.value))

        const searchable = [proyecto.nombre, item.tipo, item.etiqueta, item.estado, item.asesor]
          .map(normalizeText)
          .join(' ')

        const matchQ = !term || searchable.includes(term)

        return matchTipo && matchEstado && matchDesde && matchHasta && matchQ
      })

      return {
        ...proyecto,
        inmuebles,
      }
    })
    .filter((proyecto) => {
      const matchProyecto =
        !selectedProyecto.value || Number(proyecto.id_proyecto) === Number(selectedProyecto.value)

      return matchProyecto && proyecto.inmuebles.length > 0
    })
})

const totalUnidades = computed(() =>
  inventarioProyectosFiltrado.value.reduce(
    (acc, proyecto) => acc + Number(proyecto.inmuebles.length || 0),
    0
  )
)

const totalPrecioBase = computed(() =>
  inventarioProyectosFiltrado.value.reduce(
    (acc, proyecto) =>
      acc + proyecto.inmuebles.reduce((sum, item) => sum + Number(item.precio_base || 0), 0),
    0
  )
)

const totalPrecioVigente = computed(() =>
  inventarioProyectosFiltrado.value.reduce(
    (acc, proyecto) =>
      acc + proyecto.inmuebles.reduce((sum, item) => sum + Number(item.precio_vigente || 0), 0),
    0
  )
)

const resumenEstados = computed(() => {
  const base = {}

  inventarioProyectosFiltrado.value.forEach((proyecto) => {
    proyecto.inmuebles.forEach((item) => {
      const key = item.estado || 'Sin estado'
      base[key] = (base[key] || 0) + 1
    })
  })

  return Object.entries(base)
    .map(([nombre, total]) => ({ nombre, total }))
    .sort((a, b) => b.total - a.total)
})

function estadoBadgeClass(estadoNombre) {
  switch (estadoNombre) {
    case 'Disponible':
      return 'border-green-300 text-green-700 bg-green-50'
    case 'Vendido':
      return 'border-sky-300 text-sky-700 bg-sky-50'
    case 'Separado':
      return 'border-amber-300 text-amber-700 bg-amber-50'
    case 'Bloqueado':
      return 'border-rose-300 text-rose-700 bg-rose-50'
    case 'Congelado':
      return 'border-slate-300 text-slate-700 bg-slate-100'
    default:
      return 'border-gray-300 text-gray-700 bg-gray-100'
  }
}
</script>

<template>
  <ContabilidadLayout>
    <Head title="Contabilidad · Inventario" />

    <div class="space-y-6">
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
        <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4">
          <div>
            <h1 class="text-2xl font-bold text-gray-900">Proyectos / Inventario</h1>
            <p class="text-sm text-gray-600 mt-1">
              Inventario detallado por proyecto con precios, estado, asesor y fecha de operación.
            </p>
          </div>
        </div>

        <div class="mt-5 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-6 gap-3">
          <input
            v-model="q"
            type="text"
            placeholder="Buscar inmueble, tipo, asesor o estado…"
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

          <select
            v-model="selectedTipo"
            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#FFEA00] focus:border-transparent"
          >
            <option value="">Todos los tipos</option>
            <option value="Apartamento">Apartamento</option>
            <option value="Local">Local</option>
          </select>

          <select
            v-model="selectedEstado"
            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#FFEA00] focus:border-transparent"
          >
            <option value="">Todos los estados</option>
            <option
              v-for="estado in estadosInmueble"
              :key="estado.id_estado_inmueble"
              :value="estado.id_estado_inmueble"
            >
              {{ estado.nombre }}
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

      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div
          v-for="item in resumenEstados"
          :key="item.nombre"
          class="bg-white rounded-xl shadow-sm border border-gray-200 p-4"
        >
          <div class="text-xs text-gray-500 uppercase tracking-wide">{{ item.nombre }}</div>
          <div class="mt-2 text-2xl font-bold text-gray-900">{{ item.total }}</div>
        </div>
      </div>

      <div
        v-for="proyecto in inventarioProyectosFiltrado"
        :key="proyecto.id_proyecto"
        class="bg-white rounded-xl shadow-sm border border-gray-200 p-5"
      >
        <div class="flex items-center justify-between mb-4">
          <div>
            <h2 class="text-lg font-bold text-gray-900">
              {{ proyecto.nombre }}
            </h2>
            <p class="text-sm text-gray-500">Inventario detallado del proyecto</p>
          </div>

          <div class="bg-gray-50 rounded-lg border border-gray-200 px-4 py-2">
            <p class="text-xs text-gray-500">Unidades</p>
            <p class="text-lg font-bold text-gray-900">{{ proyecto.inmuebles.length }}</p>
          </div>
        </div>

        <div class="overflow-x-auto">
          <table class="min-w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
              <tr class="text-gray-600 text-xs uppercase tracking-wide">
                <th class="px-4 py-3 text-center">Tipo</th>
                <th class="px-4 py-3 text-center">Inmueble</th>
                <th class="px-4 py-3 text-center">Precio base</th>
                <th class="px-4 py-3 text-center">Precio vigente</th>
                <th class="px-4 py-3 text-center">Estado</th>
                <th class="px-4 py-3 text-center">Asesor</th>
                <th class="px-4 py-3 text-center">Fecha venta/sep.</th>
                <th class="px-4 py-3 text-right">Valor Comisión Asesora</th>
                <th class="px-4 py-3 text-right">Valor Comisión Directora</th>
              </tr>
            </thead>

            <tbody class="divide-y divide-gray-200">
              <tr
                v-for="(item, index) in proyecto.inmuebles"
                :key="`${proyecto.id_proyecto}-${item.etiqueta}-${index}`"
                class="hover:bg-gray-50"
              >
                <td class="px-4 py-3 text-gray-700 text-center">{{ item.tipo || '—' }}</td>
                <td class="px-4 py-3 text-gray-900 font-semibold text-center">
                  {{ item.etiqueta || '—' }}
                </td>
                <td class="px-4 py-3 text-center text-gray-700">
                  {{ formatMoney(item.precio_base) }}
                </td>
                <td class="px-4 py-3 text-center font-semibold text-[#D1C000]">
                  {{ formatMoney(item.precio_vigente) }}
                </td>
                <td class="px-4 py-3 text-center">
                  <span
                    class="inline-flex items-center justify-center text-xs px-2.5 py-1 rounded-full border"
                    :class="estadoBadgeClass(item.estado)"
                  >
                    {{ item.estado || '—' }}
                  </span>
                </td>
                <td class="px-4 py-3 text-center text-gray-700">
                  {{ item.asesor || '—' }}
                </td>
                <td class="px-4 py-3 text-center text-gray-600">
                  {{ formatDate(item.fecha_operacion) }}
                </td>
                <td class="px-4 py-3 text-center text-gray-600">
                  {{ formatMoney(item.valor_comision_asesora || 0) }}
                </td>
                <td class="px-4 py-3 text-center text-gray-600">
                  {{ formatMoney(item.valor_comision_directora || 0) }}
                </td>
              </tr>

              <tr v-if="!proyecto.inmuebles.length">
                <td colspan="7" class="px-4 py-10 text-center text-gray-500">
                  No hay inmuebles para este proyecto.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <div
        v-if="!inventarioProyectosFiltrado.length"
        class="bg-white rounded-xl shadow-sm border border-gray-200 px-6 py-12 text-center text-gray-500"
      >
        No hay resultados para los filtros actuales.
      </div>
    </div>
  </ContabilidadLayout>
</template>
