<script setup>
import { computed, ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import VentasLayout from '@/Components/VentasLayout.vue'
import {
  BanknotesIcon,
  EyeIcon,
  MagnifyingGlassIcon,
  PencilSquareIcon,
  PlusIcon,
  TrashIcon,
  PhotoIcon,
} from '@heroicons/vue/24/outline'

const props = defineProps({
  pagos: Array,
  empleado: Object,
})

const busqueda = ref('')

const pagosFiltrados = computed(() => {
  const q = busqueda.value.trim().toLowerCase()
  if (!q) return props.pagos || []

  return (props.pagos || []).filter((pago) => {
    return [
      pago.id_pago,
      pago.cliente,
      pago.documento_cliente,
      pago.proyecto,
      pago.inmueble,
      pago.concepto_pago,
      pago.medio_pago,
      pago.referencia_pago,
      pago.numero_cuota ? `cuota ${pago.numero_cuota}` : '',
    ]
      .filter(Boolean)
      .some((valor) => String(valor).toLowerCase().includes(q))
  })
})

function formatMoney(value) {
  if (value === null || value === undefined || value === '') return '—'
  return Number(value).toLocaleString('es-CO', {
    style: 'currency',
    currency: 'COP',
    maximumFractionDigits: 0,
  })
}

function formatDate(value) {
  if (!value) return '—'

  return new Date(value).toLocaleString('es-CO', {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
  })
}

function eliminarPago(id) {
  if (!confirm('¿Deseas eliminar este pago? Esta acción también eliminará el comprobante si existe.')) {
    return
  }

  router.delete(route('pagos.destroy', id), {
    preserveScroll: true,
  })
}
</script>

<template>
  <VentasLayout :empleado="empleado">
    <div class="space-y-6 p-4 sm:p-6">
      <section class="overflow-hidden rounded-3xl border border-gray-200 bg-white shadow-sm">
        <div class="bg-gradient-to-r from-[#FFEA00] via-[#FFF15C] to-[#FFF9B8] px-6 py-6">
          <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex items-center gap-3 text-[#474100]">
              <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white/70 shadow-sm">
                <BanknotesIcon class="h-6 w-6" />
              </div>
              <div>
                <h1 class="text-2xl font-extrabold tracking-tight sm:text-3xl">Pagos registrados</h1>
                <p class="mt-1 text-sm text-[#474100]/80">
                  Consulta los pagos cargados por los asesores y revisa sus comprobantes.
                </p>
              </div>
            </div>

            <Link
              :href="route('pagos.create')"
              class="inline-flex items-center justify-center gap-2 rounded-2xl bg-[#1e3a5f] px-5 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-[#2c5282]"
            >
              <PlusIcon class="h-5 w-5" />
              Registrar pago
            </Link>
          </div>
        </div>
      </section>

      <section class="rounded-3xl border border-gray-200 bg-white shadow-sm overflow-hidden">
        <div class="border-b border-gray-200 px-5 py-4">
          <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <h2 class="text-base font-extrabold text-gray-900">Listado general</h2>

            <div class="relative w-full lg:max-w-md">
              <MagnifyingGlassIcon class="pointer-events-none absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400" />
              <input
                v-model="busqueda"
                type="text"
                class="w-full rounded-2xl border border-gray-300 bg-white py-3 pl-11 pr-4 text-sm shadow-sm transition focus:border-[#FFEA00] focus:outline-none focus:ring-2 focus:ring-[#FFEA00]"
                placeholder="Buscar por cliente, documento, proyecto, inmueble o cuota"
              />
            </div>
          </div>
        </div>

        <div v-if="pagosFiltrados.length" class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-[#FFFDE6]">
              <tr>
                <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-[#756C00]">Fecha</th>
                <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-[#756C00]">Cliente</th>
                <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-[#756C00]">Proyecto / Inmueble</th>
                <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-[#756C00]">Cuota</th>
                <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-[#756C00]">Concepto</th>
                <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-[#756C00]">Valor</th>
                <th class="px-4 py-3 text-center text-xs font-bold uppercase tracking-wider text-[#756C00]">Comprobante</th>
                <th class="px-4 py-3 text-right text-xs font-bold uppercase tracking-wider text-[#756C00]">Acciones</th>
              </tr>
            </thead>

            <tbody class="divide-y divide-gray-200 bg-white">
              <tr
                v-for="pago in pagosFiltrados"
                :key="pago.id_pago"
                class="odd:bg-gray-50 transition hover:bg-yellow-50/40"
              >
                <td class="px-4 py-3 text-sm text-gray-900">{{ formatDate(pago.fecha) }}</td>

                <td class="px-4 py-3">
                  <div class="text-sm font-semibold text-gray-900">{{ pago.cliente || '—' }}</div>
                  <div class="text-xs text-gray-500">{{ pago.documento_cliente || '—' }}</div>
                </td>

                <td class="px-4 py-3">
                  <div class="text-sm font-semibold text-gray-900">{{ pago.proyecto || '—' }}</div>
                  <div class="text-xs text-gray-500">{{ pago.inmueble || '—' }}</div>
                </td>

                <td class="px-4 py-3 text-sm text-gray-900">
                  {{ pago.numero_cuota ? `#${pago.numero_cuota}` : 'No asociada' }}
                </td>

                <td class="px-4 py-3">
                  <div class="text-sm font-semibold text-gray-900">{{ pago.concepto_pago || '—' }}</div>
                  <div class="text-xs text-gray-500">{{ pago.medio_pago || '—' }}</div>
                </td>

                <td class="px-4 py-3 text-sm font-bold text-[#1e3a5f]">{{ formatMoney(pago.valor) }}</td>

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
                    <Link
                      :href="route('pagos.show', pago.id_pago)"
                      class="inline-flex items-center justify-center rounded-xl border border-sky-200 bg-sky-50 p-2 text-sky-700 transition hover:bg-sky-100"
                      title="Ver detalle"
                    >
                      <EyeIcon class="h-4 w-4" />
                    </Link>

                    <Link
                      :href="route('pagos.edit', pago.id_pago)"
                      class="inline-flex items-center justify-center rounded-xl border border-amber-200 bg-amber-50 p-2 text-amber-700 transition hover:bg-amber-100"
                      title="Editar"
                    >
                      <PencilSquareIcon class="h-4 w-4" />
                    </Link>

                    <button
                      type="button"
                      @click="eliminarPago(pago.id_pago)"
                      class="inline-flex items-center justify-center rounded-xl border border-red-200 bg-red-50 p-2 text-red-700 transition hover:bg-red-100"
                      title="Eliminar"
                    >
                      <TrashIcon class="h-4 w-4" />
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div v-else class="px-6 py-12 text-center">
          <p class="text-sm font-semibold text-gray-700">No hay pagos registrados o no coinciden con la búsqueda.</p>
        </div>
      </section>
    </div>
  </VentasLayout>
</template>