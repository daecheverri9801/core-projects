<script setup>
import { computed, ref } from 'vue'
import { Link } from '@inertiajs/vue3'
import ContabilidadLayout from '@/Components/ContabilidadLayout.vue'
import {
  BanknotesIcon,
  MagnifyingGlassIcon,
  PhotoIcon,
  FunnelIcon,
  ArrowTopRightOnSquareIcon,
  EyeIcon,
  ClipboardDocumentCheckIcon,
} from '@heroicons/vue/24/outline'

const props = defineProps({
  pagos: {
    type: Array,
    default: () => [],
  },
  proyectos: {
    type: Array,
    default: () => [],
  },
  empleado: {
    type: Object,
    default: null,
  },
})

const filtros = ref({
  buscar: '',
  id_proyecto: '',
  estado_comprobante: '',
})

const previewComprobante = ref(null)

const pagosFiltrados = computed(() => {
  return (props.pagos || []).filter((pago) => {
    const texto = filtros.value.buscar.trim().toLowerCase()

    const coincideTexto =
      !texto ||
      [
        pago.cliente,
        pago.documento_cliente,
        pago.proyecto,
        pago.inmueble,
        pago.asesor,
        pago.concepto_pago,
        pago.medio_pago,
        pago.referencia_pago,
        pago.numero_cuota ? `cuota ${pago.numero_cuota}` : '',
      ]
        .filter(Boolean)
        .some((valor) => String(valor).toLowerCase().includes(texto))

    const coincideProyecto =
      !filtros.value.id_proyecto ||
      String(props.proyectos.find((p) => p.nombre === pago.proyecto)?.id_proyecto ?? '') ===
        String(filtros.value.id_proyecto)

    const coincideComprobante =
      !filtros.value.estado_comprobante ||
      (filtros.value.estado_comprobante === 'con' && pago.tiene_comprobante) ||
      (filtros.value.estado_comprobante === 'sin' && !pago.tiene_comprobante)

    return coincideTexto && coincideProyecto && coincideComprobante
  })
})

const totalPagos = computed(() => pagosFiltrados.value.length)

const totalValor = computed(() => {
  return pagosFiltrados.value.reduce((acc, pago) => acc + Number(pago.valor || 0), 0)
})

const pagosConComprobante = computed(() => {
  return pagosFiltrados.value.filter((p) => p.tiene_comprobante).length
})

function formatMoney(value) {
  return Number(value || 0).toLocaleString('es-CO', {
    style: 'currency',
    currency: 'COP',
    maximumFractionDigits: 0,
  })
}

function formatDate(value) {
  if (!value) return '—'

  return new Date(value).toLocaleDateString('es-CO', {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
  })
}

function abrirPreview(url) {
  previewComprobante.value = url
}

function cerrarPreview() {
  previewComprobante.value = null
}

function limpiarFiltros() {
  filtros.value = {
    buscar: '',
    id_proyecto: '',
    estado_comprobante: '',
  }
}
</script>

<template>
  <ContabilidadLayout :empleado="empleado">
    <div class="space-y-6">
      <section class="overflow-hidden rounded-3xl border border-gray-200 bg-white shadow-sm">
        <div class="bg-gradient-to-r from-[#FFEA00] via-[#FFF15C] to-[#FFF9B8] px-6 py-6">
          <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
            <div class="min-w-0">
              <div class="flex items-center gap-3 text-[#474100]">
                <div
                  class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white/70 shadow-sm"
                >
                  <BanknotesIcon class="h-6 w-6" />
                </div>
                <div>
                  <h1 class="text-2xl font-extrabold tracking-tight sm:text-3xl">
                    Pagos registrados
                  </h1>
                  <p class="mt-1 text-sm text-[#474100]/80">
                    Consulta pagos cargados por los asesores y revisa sus comprobantes.
                  </p>
                </div>
              </div>
            </div>

            <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
              <div class="rounded-2xl border border-[#474100]/10 bg-white/70 px-4 py-3 shadow-sm">
                <p class="text-[11px] font-semibold uppercase tracking-wide text-[#474100]/70">
                  Pagos visibles
                </p>
                <p class="mt-1 truncate text-lg font-extrabold text-[#474100]">
                  {{ totalPagos }}
                </p>
              </div>

              <div class="rounded-2xl border border-[#474100]/10 bg-white/70 px-4 py-3 shadow-sm">
                <p class="text-[11px] font-semibold uppercase tracking-wide text-[#474100]/70">
                  Valor total
                </p>
                <p class="mt-1 truncate text-lg font-extrabold text-[#474100]">
                  {{ formatMoney(totalValor) }}
                </p>
              </div>

              <div class="rounded-2xl border border-[#474100]/10 bg-white/70 px-4 py-3 shadow-sm">
                <p class="text-[11px] font-semibold uppercase tracking-wide text-[#474100]/70">
                  Con comprobante
                </p>
                <p class="mt-1 truncate text-lg font-extrabold text-[#474100]">
                  {{ pagosConComprobante }}
                </p>
              </div>
            </div>
          </div>
        </div>
      </section>

      <section class="rounded-3xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-200 px-5 py-4">
          <div class="flex items-center gap-2">
            <FunnelIcon class="h-5 w-5 text-[#1e3a5f]" />
            <h2 class="text-base font-extrabold text-gray-900">Filtros</h2>
          </div>
        </div>

        <div class="grid grid-cols-1 gap-4 p-5 md:grid-cols-3">
          <div class="md:col-span-1">
            <label class="mb-2 block text-sm font-semibold text-gray-700"> Buscar </label>
            <div class="relative">
              <MagnifyingGlassIcon
                class="pointer-events-none absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400"
              />
              <input
                v-model="filtros.buscar"
                type="text"
                class="w-full rounded-2xl border border-gray-300 bg-white py-3 pl-11 pr-4 text-sm shadow-sm transition focus:border-[#FFEA00] focus:outline-none focus:ring-2 focus:ring-[#FFEA00]"
                placeholder="Cliente, documento, inmueble, asesor..."
              />
            </div>
          </div>

          <div>
            <label class="mb-2 block text-sm font-semibold text-gray-700"> Proyecto </label>
            <select
              v-model="filtros.id_proyecto"
              class="w-full rounded-2xl border border-gray-300 bg-white px-4 py-3 text-sm shadow-sm transition focus:border-[#FFEA00] focus:outline-none focus:ring-2 focus:ring-[#FFEA00]"
            >
              <option value="">Todos</option>
              <option
                v-for="proyecto in proyectos"
                :key="proyecto.id_proyecto"
                :value="String(proyecto.id_proyecto)"
              >
                {{ proyecto.nombre }}
              </option>
            </select>
          </div>

          <div>
            <label class="mb-2 block text-sm font-semibold text-gray-700"> Comprobante </label>
            <select
              v-model="filtros.estado_comprobante"
              class="w-full rounded-2xl border border-gray-300 bg-white px-4 py-3 text-sm shadow-sm transition focus:border-[#FFEA00] focus:outline-none focus:ring-2 focus:ring-[#FFEA00]"
            >
              <option value="">Todos</option>
              <option value="con">Con comprobante</option>
              <option value="sin">Sin comprobante</option>
            </select>
          </div>

          <div class="md:col-span-3 flex justify-end">
            <button
              type="button"
              @click="limpiarFiltros"
              class="inline-flex items-center justify-center rounded-2xl border border-gray-300 bg-white px-5 py-3 text-sm font-semibold text-gray-700 transition hover:bg-gray-50"
            >
              Limpiar filtros
            </button>
          </div>
        </div>
      </section>

      <section class="overflow-hidden rounded-3xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-200 px-5 py-4">
          <div class="flex items-center gap-2">
            <ClipboardDocumentCheckIcon class="h-5 w-5 text-[#1e3a5f]" />
            <h2 class="text-base font-extrabold text-gray-900">Listado de pagos</h2>
          </div>
        </div>

        <div v-if="pagosFiltrados.length" class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-[#FFFDE6]">
              <tr>
                <th
                  class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-[#756C00]"
                >
                  Fecha
                </th>
                <th
                  class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-[#756C00]"
                >
                  Cliente
                </th>
                <th
                  class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-[#756C00]"
                >
                  Proyecto / Inmueble
                </th>
                <th
                  class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-[#756C00]"
                >
                  Cuota
                </th>
                <th
                  class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-[#756C00]"
                >
                  Medio / Concepto
                </th>
                <th
                  class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-[#756C00]"
                >
                  Asesor
                </th>
                <th
                  class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-[#756C00]"
                >
                  Valor
                </th>
                <th
                  class="px-4 py-3 text-center text-xs font-bold uppercase tracking-wider text-[#756C00]"
                >
                  Comprobante
                </th>
                <th
                  class="px-4 py-3 text-right text-xs font-bold uppercase tracking-wider text-[#756C00]"
                >
                  Acciones
                </th>
              </tr>
            </thead>

            <tbody class="divide-y divide-gray-200 bg-white">
              <tr
                v-for="pago in pagosFiltrados"
                :key="pago.id_pago"
                class="odd:bg-gray-50 transition hover:bg-yellow-50/40"
              >
                <td class="px-4 py-3 text-sm text-gray-900">
                  {{ formatDate(pago.fecha) }}
                </td>

                <td class="px-4 py-3">
                  <div class="text-sm font-semibold text-gray-900">
                    {{ pago.cliente || '—' }}
                  </div>
                  <div class="text-xs text-gray-500">
                    {{ pago.documento_cliente || '—' }}
                  </div>
                </td>

                <td class="px-4 py-3">
                  <div class="text-sm font-semibold text-gray-900">
                    {{ pago.proyecto || '—' }}
                  </div>
                  <div class="text-xs text-gray-500">
                    {{ pago.inmueble || '—' }}
                  </div>
                </td>

                <td class="px-4 py-3 text-sm text-gray-900">
                  {{ pago.numero_cuota ? `Cuota #${pago.numero_cuota}` : 'No asociada' }}
                </td>

                <td class="px-4 py-3">
                  <div class="text-sm font-semibold text-gray-900">
                    {{ pago.medio_pago || '—' }}
                  </div>
                  <div class="text-xs text-gray-500">
                    {{ pago.concepto_pago || '—' }}
                  </div>
                </td>

                <td class="px-4 py-3 text-sm text-gray-900">
                  {{ pago.asesor || '—' }}
                </td>

                <td class="px-4 py-3 text-sm font-bold text-[#1e3a5f]">
                  {{ formatMoney(pago.valor) }}
                </td>

                <td class="px-4 py-3 text-center">
                  <span
                    v-if="pago.tiene_comprobante"
                    class="inline-flex items-center gap-1 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700"
                  >
                    <PhotoIcon class="h-4 w-4" />
                    Sí
                  </span>
                  <span
                    v-else
                    class="inline-flex rounded-full border border-gray-200 bg-gray-100 px-3 py-1 text-xs font-bold text-gray-600"
                  >
                    No
                  </span>
                </td>

                <td class="px-4 py-3">
                  <div class="flex justify-end gap-2">
                    <button
                      v-if="pago.comprobante_url"
                      type="button"
                      @click="abrirPreview(pago.comprobante_url)"
                      class="inline-flex items-center justify-center rounded-xl border border-sky-200 bg-sky-50 p-2 text-sky-700 transition hover:bg-sky-100"
                      title="Ver comprobante"
                    >
                      <EyeIcon class="h-4 w-4" />
                    </button>

                    <a
                      v-if="pago.comprobante_url"
                      :href="pago.comprobante_url"
                      target="_blank"
                      class="inline-flex items-center justify-center rounded-xl border border-emerald-200 bg-emerald-50 p-2 text-emerald-700 transition hover:bg-emerald-100"
                      title="Abrir comprobante"
                    >
                      <ArrowTopRightOnSquareIcon class="h-4 w-4" />
                    </a>

                    <Link
                      v-if="pago.id_venta"
                      :href="route('contabilidad.ventas.show', pago.id_venta)"
                      class="inline-flex items-center justify-center rounded-xl border border-amber-200 bg-amber-50 p-2 text-amber-700 transition hover:bg-amber-100"
                      title="Ver venta"
                    >
                      <ClipboardDocumentCheckIcon class="h-4 w-4" />
                    </Link>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div v-else class="px-6 py-12 text-center">
          <p class="text-sm font-semibold text-gray-700">
            No hay pagos que coincidan con los filtros aplicados.
          </p>
        </div>
      </section>

      <div
        v-if="previewComprobante"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-4"
        @click.self="cerrarPreview"
      >
        <div class="relative w-full max-w-5xl rounded-3xl bg-white p-4 shadow-2xl">
          <div class="mb-4 flex items-center justify-between">
            <h3 class="text-base font-extrabold text-gray-900">Vista previa del comprobante</h3>
            <button
              type="button"
              @click="cerrarPreview"
              class="rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50"
            >
              Cerrar
            </button>
          </div>

          <div class="overflow-hidden rounded-2xl border border-gray-200 bg-gray-50">
            <img
              :src="previewComprobante"
              alt="Comprobante"
              class="max-h-[80vh] w-full object-contain"
            />
          </div>
        </div>
      </div>
    </div>
  </ContabilidadLayout>
</template>
