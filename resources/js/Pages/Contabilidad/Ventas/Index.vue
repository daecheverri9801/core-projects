<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import ContabilidadLayout from '@/Components/ContabilidadLayout.vue'

const props = defineProps({
  ventas: { type: Array, default: () => [] },
  proyectos: { type: Array, default: () => [] },
  filtros: { type: Object, default: () => ({}) },
  empleado: Object,
})

const q = ref('')
const proyecto = ref(props.filtros?.proyecto_id || '')
const tipo = ref('')
const desde = ref('')
const hasta = ref('')
const selectedProyecto = ref(props.filtros?.proyecto_id || '')

function formatMoney(v) {
  return Number(v || 0).toLocaleString('es-CO', {
    style: 'currency',
    currency: 'COP',
    maximumFractionDigits: 0,
  })
}

function formatDate(d) {
  if (!d) return '—'
  return new Date(d).toLocaleDateString('es-CO', {
    year: 'numeric',
    month: 'short',
    day: '2-digit',
  })
}

function inmuebleLabel(v) {
  if (v?.apartamento) return `Apto ${v.apartamento.numero}`
  if (v?.local) return `Local ${v.local.numero}`
  return '—'
}

const proyectosList = computed(() =>
  (props.proyectos || [])
    .map((p) => ({ id: p.id_proyecto, nombre: p.nombre }))
    .sort((a, b) => a.nombre.localeCompare(b.nombre))
)

const filtered = computed(() => {
  const term = q.value.trim().toLowerCase()
  const fFrom = desde.value ? new Date(desde.value) : null
  const fTo = hasta.value ? new Date(hasta.value) : null

  return (props.ventas || []).filter((v) => {
    const matchQ =
      !term ||
      String(v?.documento_cliente || '')
        .toLowerCase()
        .includes(term) ||
      String(v?.cliente?.nombre || '')
        .toLowerCase()
        .includes(term) ||
      String(v?.proyecto?.nombre || '')
        .toLowerCase()
        .includes(term) ||
      String(inmuebleLabel(v)).toLowerCase().includes(term)

    const matchProyecto =
      !proyecto.value || Number(v?.proyecto?.id_proyecto) === Number(proyecto.value)
    const matchTipo = !tipo.value || v?.tipo_operacion === tipo.value

    const fv = v?.fecha_venta ? new Date(v.fecha_venta) : null
    const matchFrom = !fFrom || (fv && fv >= fFrom)
    const matchTo = !fTo || (fv && fv <= fTo)

    return matchQ && matchProyecto && matchTipo && matchFrom && matchTo
  })
})

function aplicarFiltroProyecto() {
  router.get(
    '/contabilidad/ventas',
    { proyecto_id: selectedProyecto.value || '' },
    { preserveState: true, preserveScroll: true, replace: true }
  )
}

function limpiarFiltroProyecto() {
  ;((selectedProyecto.value = ''),
    (tipo.value = ''),
    (desde.value = ''),
    (hasta.value = ''),
    aplicarFiltroProyecto())
}

const total = computed(() => filtered.value.length)
const totalValor = computed(() =>
  filtered.value.reduce((a, v) => a + Number(v?.valor_total || 0), 0)
)
</script>

<template>
  <ContabilidadLayout>
    <Head title="Contabilidad · Ventas" />

    <div class="space-y-6">
      <!-- Panel de filtros y resumen -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
        <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4">
          <div>
            <h1 class="text-2xl font-bold text-gray-900">Operaciones</h1>
            <p class="text-sm text-gray-600 mt-1">Listado de ventas y separaciones</p>
          </div>

          <div class="flex items-center gap-3">
            <div class="bg-gray-50 rounded-lg border border-gray-200 px-4 py-3">
              <p class="text-xs text-gray-500">Total</p>
              <p class="text-xl font-bold text-gray-900">{{ total }}</p>
            </div>
            <div class="bg-gray-50 rounded-lg border border-gray-200 px-4 py-3">
              <p class="text-xs text-gray-500">Valor total</p>
              <p class="text-xl font-bold text-[#D1C000]">{{ formatMoney(totalValor) }}</p>
            </div>
          </div>
        </div>

        <div class="mt-5 grid grid-cols-1 md:grid-cols-5 gap-3">
          <input
            v-model="q"
            type="text"
            placeholder="Buscar cliente / documento / proyecto / inmueble…"
            class="md:col-span-2 w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-[#FFEA00] focus:border-transparent"
          />

          <select
            v-model="selectedProyecto"
            @change="aplicarFiltroProyecto"
            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#FFEA00] focus:border-transparent"
          >
            <option value="">Todos</option>
            <option v-for="p in proyectos" :key="p.id_proyecto" :value="p.id_proyecto">
              {{ p.nombre }}
            </option>
          </select>

          <select
            v-model="tipo"
            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#FFEA00] focus:border-transparent"
          >
            <option value="">Todos los tipos</option>
            <option value="venta">Venta</option>
            <option value="separacion">Separación</option>
          </select>

          <div class="grid grid-cols-2 gap-2">
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
        </div>

        <div class="mt-4 flex justify-end">
          <button
            @click="limpiarFiltroProyecto"
            class="px-4 py-2 rounded-lg border border-gray-300 bg-white hover:bg-gray-50 text-sm font-semibold text-gray-700 transition"
          >
            Limpiar filtros
          </button>
        </div>
      </div>

      <!-- Tabla de resultados -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
              <tr class="text-gray-600 text-xs uppercase tracking-wide">
                <th class="px-4 py-3 text-center">Proyecto</th>
                <th class="px-4 py-3 text-center">Cliente</th>
                <th class="px-4 py-3 text-center">Operación</th>
                <th class="px-4 py-3 text-center">Inmueble</th>
                <th class="px-4 py-3 text-center">Fecha</th>
                <th class="px-4 py-3 text-center">Valor</th>
                <th class="px-4 py-3 text-center">Acción</th>
              </tr>
            </thead>

            <tbody class="divide-y divide-gray-200">
              <tr v-for="v in filtered" :key="v.id_venta" class="hover:bg-gray-50">
                <td class="px-4 py-3 text-gray-700 text-center">{{ v.proyecto?.nombre || '—' }}</td>
                <td class="px-4 py-3">
                  <div class="text-gray-900 font-semibold text-center">{{ v.cliente?.nombre || '—' }}</div>
                  <div class="text-xs text-gray-500 text-center">{{ v.documento_cliente || '—' }}</div>
                </td>
                <td class="px-4 py-3 text-center">
                  <span
                    class="ml-2 text-xs px-2 py-0.5 rounded-full border"
                    :class="
                      v.tipo_operacion === 'venta'
                        ? 'border-green-300 text-green-700 bg-green-50'
                        : 'border-blue-300 text-blue-700 bg-blue-50'
                    "
                  >
                    {{ v.tipo_operacion === 'venta' ? 'Venta' : 'Separación' }}
                  </span>
                </td>

                <td class="px-4 py-3 text-gray-700 text-center">{{ inmuebleLabel(v) }}</td>
                <td class="px-4 py-3 text-gray-600 text-center  ">{{ formatDate(v.fecha_venta) }}</td>
                <td class="px-4 py-3 text-center font-semibold text-[#D1C000]">
                  {{ formatMoney(v.valor_total) }}
                </td>

                <td class="px-4 py-3 text-center">
                  <Link
                    :href="route('contabilidad.ventas.show', v.id_venta)"
                    class="inline-flex items-center justify-center px-3 py-2 rounded-lg border border-gray-300 bg-white hover:bg-gray-50 text-xs font-semibold text-gray-700 transition"
                  >
                    Ver detalle
                  </Link>
                </td>
              </tr>

              <tr v-if="!filtered.length">
                <td colspan="7" class="px-4 py-10 text-center text-gray-500">
                  No hay resultados para los filtros actuales.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </ContabilidadLayout>
</template>
