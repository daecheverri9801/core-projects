<script setup>
import { Link, router } from '@inertiajs/vue3'
import VentasLayout from '@/Components/VentasLayout.vue'
import {
  ArrowTopRightOnSquareIcon,
  BanknotesIcon,
  PencilSquareIcon,
  TrashIcon,
  PhotoIcon,
} from '@heroicons/vue/24/outline'

const props = defineProps({
  pago: Object,
  empleado: Object,
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

  router.delete(route('pagos.destroy', id))
}

function inmuebleLabel() {
  if (props.pago?.venta?.apartamento) return `Apto ${props.pago.venta.apartamento.numero}`
  if (props.pago?.venta?.local) return `Local ${props.pago.venta.local.numero}`
  return '—'
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
                <h1 class="text-2xl font-extrabold tracking-tight sm:text-3xl">Detalle del pago</h1>
                <p class="mt-1 text-sm text-[#474100]/80">
                  Revisa la información del pago y el comprobante cargado.
                </p>
              </div>
            </div>

            <div class="flex flex-wrap gap-3">
              <Link
                :href="route('pagos.index')"
                class="inline-flex items-center justify-center rounded-2xl border border-[#474100]/10 bg-white/80 px-5 py-3 text-sm font-bold text-[#474100] shadow-sm transition hover:bg-white"
              >
                Volver
              </Link>

              <Link
                :href="route('pagos.edit', pago.id_pago)"
                class="inline-flex items-center justify-center gap-2 rounded-2xl bg-[#1e3a5f] px-5 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-[#2c5282]"
              >
                <PencilSquareIcon class="h-5 w-5" />
                Editar
              </Link>

              <button
                type="button"
                @click="eliminarPago(pago.id_pago)"
                class="inline-flex items-center justify-center gap-2 rounded-2xl border border-red-200 bg-red-50 px-5 py-3 text-sm font-bold text-red-700 transition hover:bg-red-100"
              >
                <TrashIcon class="h-5 w-5" />
                Eliminar
              </button>
            </div>
          </div>
        </div>
      </section>

      <div class="grid grid-cols-1 gap-6 xl:grid-cols-12">
        <section class="xl:col-span-7 space-y-6">
          <div class="rounded-3xl border border-gray-200 bg-white shadow-sm overflow-hidden">
            <div class="border-b border-gray-200 px-5 py-4">
              <h2 class="text-base font-extrabold text-gray-900">Información general</h2>
            </div>

            <div class="grid grid-cols-1 gap-4 p-5 md:grid-cols-2">
              <div class="rounded-2xl bg-gray-50 px-4 py-3">
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Fecha</div>
                <div class="mt-1 font-semibold text-[#1e3a5f]">{{ formatDate(pago.fecha) }}</div>
              </div>

              <div class="rounded-2xl bg-gray-50 px-4 py-3">
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Valor</div>
                <div class="mt-1 font-semibold text-[#1e3a5f]">{{ formatMoney(pago.valor) }}</div>
              </div>

              <div class="rounded-2xl bg-gray-50 px-4 py-3">
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Cliente</div>
                <div class="mt-1 font-semibold text-[#1e3a5f]">{{ pago.venta?.cliente?.nombre || '—' }}</div>
              </div>

              <div class="rounded-2xl bg-gray-50 px-4 py-3">
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Documento</div>
                <div class="mt-1 font-semibold text-[#1e3a5f]">{{ pago.venta?.cliente?.documento || '—' }}</div>
              </div>

              <div class="rounded-2xl bg-gray-50 px-4 py-3">
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Proyecto</div>
                <div class="mt-1 font-semibold text-[#1e3a5f]">{{ pago.venta?.proyecto?.nombre || '—' }}</div>
              </div>

              <div class="rounded-2xl bg-gray-50 px-4 py-3">
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Inmueble</div>
                <div class="mt-1 font-semibold text-[#1e3a5f]">{{ inmuebleLabel() }}</div>
              </div>

              <div class="rounded-2xl bg-gray-50 px-4 py-3">
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Concepto</div>
                <div class="mt-1 font-semibold text-[#1e3a5f]">{{ pago.concepto_pago?.concepto || '—' }}</div>
              </div>

              <div class="rounded-2xl bg-gray-50 px-4 py-3">
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Medio de pago</div>
                <div class="mt-1 font-semibold text-[#1e3a5f]">{{ pago.medio_pago?.medio_pago || '—' }}</div>
              </div>

              <div class="rounded-2xl bg-gray-50 px-4 py-3 md:col-span-2">
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Referencia</div>
                <div class="mt-1 font-semibold text-[#1e3a5f]">{{ pago.referencia_pago || '—' }}</div>
              </div>

              <div class="rounded-2xl bg-gray-50 px-4 py-3 md:col-span-2">
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Descripción</div>
                <div class="mt-1 font-semibold text-[#1e3a5f] whitespace-pre-line">{{ pago.descripcion || '—' }}</div>
              </div>

              <div class="rounded-2xl border border-[#FFEA00]/50 bg-[#FFFDE6] px-4 py-3 md:col-span-2">
                <div class="text-xs font-semibold uppercase tracking-wide text-[#756C00]">Cuota asociada</div>
                <div class="mt-1 font-semibold text-[#474100]">
                  {{ pago.cuota ? `Cuota #${pago.cuota.numero_cuota}` : 'No asociada' }}
                </div>
                <div v-if="pago.cuota" class="mt-1 text-sm text-[#756C00]">
                  Vence: {{ formatDate(pago.cuota.fecha_vencimiento) }} · Estado: {{ pago.cuota.estado || '—' }}
                </div>
              </div>
            </div>
          </div>
        </section>

        <aside class="xl:col-span-5">
          <div class="rounded-3xl border border-gray-200 bg-white shadow-sm overflow-hidden">
            <div class="border-b border-gray-200 px-5 py-4">
              <div class="flex items-center gap-2">
                <PhotoIcon class="h-5 w-5 text-[#1e3a5f]" />
                <h2 class="text-base font-extrabold text-gray-900">Comprobante</h2>
              </div>
            </div>

            <div class="p-5">
              <div v-if="pago.tiene_comprobante && pago.comprobante_url" class="space-y-4">
                <img
                  :src="pago.comprobante_url"
                  alt="Comprobante del pago"
                  class="max-h-[520px] w-full rounded-3xl border border-gray-200 object-contain bg-gray-50"
                />

                <div class="rounded-2xl bg-gray-50 px-4 py-3">
                  <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Archivo</p>
                  <p class="mt-1 break-all text-sm font-semibold text-gray-900">
                    {{ pago.comprobante_nombre_original || 'Comprobante cargado' }}
                  </p>
                </div>

                <a
                  :href="pago.comprobante_url"
                  target="_blank"
                  class="inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-[#1e3a5f] px-5 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-[#2c5282]"
                >
                  <ArrowTopRightOnSquareIcon class="h-5 w-5" />
                  Abrir comprobante
                </a>
              </div>

              <div v-else class="rounded-3xl border border-dashed border-gray-300 bg-gray-50 px-6 py-12 text-center">
                <p class="text-sm font-semibold text-gray-700">Este pago no tiene comprobante cargado.</p>
              </div>
            </div>
          </div>
        </aside>
      </div>
    </div>
  </VentasLayout>
</template>