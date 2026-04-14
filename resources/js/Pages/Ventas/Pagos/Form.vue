<script setup>
import { computed, watch, ref, reactive } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import {
  BanknotesIcon,
  CalendarDaysIcon,
  CreditCardIcon,
  DocumentArrowUpIcon,
  DocumentTextIcon,
  HashtagIcon,
  MagnifyingGlassIcon,
  PhotoIcon,
  TrashIcon,
  IdentificationIcon,
} from '@heroicons/vue/24/outline'

const props = defineProps({
  formMode: {
    type: String,
    default: 'create',
  },
  pago: {
    type: Object,
    default: null,
  },
  ventas: {
    type: Array,
    default: () => [],
  },
  conceptos: {
    type: Array,
    default: () => [],
  },
  medios: {
    type: Array,
    default: () => [],
  },
  cuotas: {
    type: Array,
    default: () => [],
  },
  submitRoute: {
    type: String,
    required: true,
  },
  submitMethod: {
    type: String,
    default: 'post',
  },
  backRoute: {
    type: String,
    default: '',
  },
})

const form = useForm({
  fecha: props.pago?.fecha
    ? String(props.pago.fecha).slice(0, 10)
    : new Date().toISOString().slice(0, 10),
  id_venta: props.pago?.id_venta ? String(props.pago.id_venta) : '',
  referencia_pago: props.pago?.referencia_pago ?? '',
  id_concepto_pago: props.pago?.id_concepto_pago ? String(props.pago.id_concepto_pago) : '',
  id_medio_pago: props.pago?.id_medio_pago ? String(props.pago.id_medio_pago) : '',
  descripcion: props.pago?.descripcion ?? '',
  valor: props.pago?.valor ?? '',
  id_cuota: props.pago?.id_cuota ? String(props.pago.id_cuota) : '',
  comprobante: null,
  eliminar_comprobante: false,
})

const previewUrl = ref(props.pago?.comprobante_url ?? null)

const busquedaCliente = reactive({
  documento: props.pago
    ? String(
        props.ventas.find((v) => String(v.id_venta) === String(props.pago?.id_venta))
          ?.documento_cliente ?? ''
      )
    : '',
  error: '',
})

const clienteEncontrado = ref(
  props.pago
    ? props.ventas.find((v) => String(v.id_venta) === String(props.pago?.id_venta)) || null
    : null
)

function normalizarDocumento(value) {
  return String(value || '').replace(/\D/g, '')
}

const ventasFiltradas = computed(() => {
  const doc = normalizarDocumento(busquedaCliente.documento)
  if (!doc) return []

  return props.ventas.filter((venta) => normalizarDocumento(venta.documento_cliente) === doc)
})

const ventaSeleccionada = computed(() => {
  return props.ventas.find((v) => String(v.id_venta) === String(form.id_venta)) || null
})

const cuotasFiltradas = computed(() => {
  if (!form.id_venta) return []
  return props.cuotas.filter((c) => String(c.id_venta) === String(form.id_venta))
})

const cuotaSeleccionada = computed(() => {
  return props.cuotas.find((c) => String(c.id_cuota) === String(form.id_cuota)) || null
})

watch(
  () => busquedaCliente.documento,
  (value) => {
    const limpio = normalizarDocumento(value)

    if (limpio !== value) {
      busquedaCliente.documento = limpio
      return
    }

    if (!limpio) {
      busquedaCliente.error = ''
      clienteEncontrado.value = null
      form.id_venta = ''
      form.id_cuota = ''
    }
  }
)

watch(
  () => form.id_venta,
  (nuevo, anterior) => {
    if (!nuevo) {
      form.id_cuota = ''
      return
    }

    if (nuevo !== anterior) {
      const cuotaValida = cuotasFiltradas.value.some(
        (c) => String(c.id_cuota) === String(form.id_cuota)
      )
      if (!cuotaValida) {
        form.id_cuota = ''
      }
    }
  }
)

function buscarClientePorDocumento() {
  busquedaCliente.error = ''
  form.id_venta = ''
  form.id_cuota = ''

  const doc = normalizarDocumento(busquedaCliente.documento)

  if (!doc) {
    clienteEncontrado.value = null
    busquedaCliente.error = 'Ingresa el número de documento del cliente.'
    return
  }

  const ventas = props.ventas.filter(
    (venta) => normalizarDocumento(venta.documento_cliente) === doc
  )

  if (!ventas.length) {
    clienteEncontrado.value = null
    busquedaCliente.error = 'No se encontraron ventas asociadas a ese documento.'
    return
  }

  clienteEncontrado.value = ventas[0]
}

function fieldClass(disabled = false) {
  return [
    'w-full rounded-2xl border px-4 py-3 text-sm shadow-sm transition',
    'focus:outline-none focus:ring-2 focus:ring-[#FFEA00] focus:border-[#FFEA00]',
    disabled
      ? 'bg-gray-100 border-gray-200 text-gray-500 cursor-not-allowed'
      : 'bg-white border-gray-300 text-gray-900',
  ].join(' ')
}

function labelClass() {
  return 'mb-2 block text-sm font-semibold text-gray-700'
}

function formatMoney(value) {
  if (value === null || value === undefined || value === '') return '—'
  return Number(value).toLocaleString('es-CO', {
    style: 'currency',
    currency: 'COP',
    maximumFractionDigits: 0,
  })
}

function handleFileChange(event) {
  const file = event.target.files?.[0] ?? null
  form.comprobante = file

  if (!file) {
    previewUrl.value = props.pago?.comprobante_url ?? null
    return
  }

  previewUrl.value = URL.createObjectURL(file)
  form.eliminar_comprobante = false
}

function quitarComprobante() {
  form.comprobante = null
  form.eliminar_comprobante = true
  previewUrl.value = null
}

function submit() {
  form.transform((data) => ({
    ...data,
    valor: data.valor === '' ? null : data.valor,
    id_concepto_pago: data.id_concepto_pago || null,
    id_medio_pago: data.id_medio_pago || null,
    id_cuota: data.id_cuota || null,
    ...(props.submitMethod === 'put' ? { _method: 'put' } : {}),
  }))

  form.post(props.submitRoute, {
    forceFormData: true,
    preserveScroll: true,
  })
}
</script>

<template>
  <div class="grid grid-cols-1 gap-6 xl:grid-cols-12">
    <section class="xl:col-span-8 space-y-6">
      <div class="rounded-3xl border border-gray-200 bg-white shadow-sm overflow-hidden">
        <div class="border-b border-gray-200 px-5 py-4">
          <h2 class="text-base font-extrabold text-gray-900">Información del pago</h2>
        </div>

        <div class="grid grid-cols-1 gap-5 p-5 md:grid-cols-2">
          <div>
            <label :class="labelClass()">Fecha</label>
            <div class="relative">
              <CalendarDaysIcon
                class="pointer-events-none absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400"
              />
              <input v-model="form.fecha" type="date" :class="fieldClass()" class="pl-11" />
            </div>
            <p v-if="form.errors.fecha" class="mt-2 text-xs text-red-500">
              {{ form.errors.fecha }}
            </p>
          </div>

          <div>
            <label :class="labelClass()">Valor del pago</label>
            <div class="relative">
              <BanknotesIcon
                class="pointer-events-none absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400"
              />
              <input
                v-model="form.valor"
                type="number"
                min="0"
                step="0.01"
                :class="fieldClass()"
                class="pl-11"
                placeholder="Ingresa el valor pagado"
              />
            </div>
            <p v-if="form.errors.valor" class="mt-2 text-xs text-red-500">
              {{ form.errors.valor }}
            </p>
          </div>

          <div class="md:col-span-2">
            <label :class="labelClass()">Buscar cliente por documento</label>

            <div class="rounded-2xl border border-gray-200 bg-gray-50/60 p-4">
              <div class="flex flex-col gap-3 lg:flex-row">
                <div class="relative flex-1">
                  <IdentificationIcon
                    class="pointer-events-none absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400"
                  />
                  <input
                    v-model="busquedaCliente.documento"
                    type="text"
                    inputmode="numeric"
                    maxlength="20"
                    autocomplete="off"
                    :class="fieldClass()"
                    class="pl-11"
                    placeholder="Digita el número de documento"
                    @keyup.enter="buscarClientePorDocumento"
                  />
                </div>

                <button
                  type="button"
                  @click="buscarClientePorDocumento"
                  class="inline-flex items-center justify-center gap-2 rounded-2xl bg-[#1e3a5f] px-5 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-[#2c5282]"
                >
                  <MagnifyingGlassIcon class="h-4 w-4" />
                  Buscar
                </button>
              </div>

              <p v-if="busquedaCliente.error" class="mt-2 text-xs text-red-500">
                {{ busquedaCliente.error }}
              </p>

              <div
                v-if="clienteEncontrado"
                class="mt-4 rounded-2xl border border-sky-200 bg-sky-50 p-4"
              >
                <div class="grid grid-cols-1 gap-3 text-sm md:grid-cols-2">
                  <div class="rounded-xl bg-white/80 px-4 py-3">
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-sky-700">
                      Cliente
                    </p>
                    <p class="mt-1 font-semibold text-gray-900">
                      {{ clienteEncontrado.cliente || '—' }}
                    </p>
                  </div>

                  <div class="rounded-xl bg-white/80 px-4 py-3">
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-sky-700">
                      Documento
                    </p>
                    <p class="mt-1 font-semibold text-gray-900">
                      {{ clienteEncontrado.documento_cliente || '—' }}
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="md:col-span-2">
            <label :class="labelClass()">Ventas asociadas al cliente</label>
            <div class="relative">
              <DocumentTextIcon
                class="pointer-events-none absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400"
              />
              <select
                v-model="form.id_venta"
                :class="fieldClass(ventasFiltradas.length === 0)"
                class="pl-11"
                :disabled="ventasFiltradas.length === 0"
              >
                <option value="">Seleccione una venta</option>
                <option
                  v-for="venta in ventasFiltradas"
                  :key="venta.id_venta"
                  :value="String(venta.id_venta)"
                >
                  {{ venta.inmueble }} · {{ venta.proyecto }}
                </option>
              </select>
            </div>
            <p v-if="form.errors.id_venta" class="mt-2 text-xs text-red-500">
              {{ form.errors.id_venta }}
            </p>
          </div>

          <div class="md:col-span-2">
            <label :class="labelClass()">Cuota asociada</label>
            <div class="relative">
              <HashtagIcon
                class="pointer-events-none absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400"
              />
              <select
                v-model="form.id_cuota"
                :class="fieldClass(!form.id_venta)"
                class="pl-11"
                :disabled="!form.id_venta"
              >
                <option value="">Seleccione una cuota</option>
                <option
                  v-for="cuota in cuotasFiltradas"
                  :key="cuota.id_cuota"
                  :value="String(cuota.id_cuota)"
                >
                  {{ `Cuota #${cuota.numero_cuota} - ${formatMoney(cuota.valor_cuota)}` }}
                </option>
              </select>
            </div>
            <p v-if="form.errors.id_cuota" class="mt-2 text-xs text-red-500">
              {{ form.errors.id_cuota }}
            </p>
          </div>

          <div>
            <label :class="labelClass()">Concepto de pago</label>
            <select v-model="form.id_concepto_pago" :class="fieldClass()">
              <option value="">Seleccione...</option>
              <option
                v-for="concepto in conceptos"
                :key="concepto.id_concepto_pago"
                :value="String(concepto.id_concepto_pago)"
              >
                {{ concepto.concepto }}
              </option>
            </select>
            <p v-if="form.errors.id_concepto_pago" class="mt-2 text-xs text-red-500">
              {{ form.errors.id_concepto_pago }}
            </p>
          </div>

          <div>
            <label :class="labelClass()">Medio de pago</label>
            <div class="relative">
              <CreditCardIcon
                class="pointer-events-none absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400"
              />
              <select v-model="form.id_medio_pago" :class="fieldClass()" class="pl-11">
                <option value="">Seleccione...</option>
                <option
                  v-for="medio in medios"
                  :key="medio.id_medio_pago"
                  :value="String(medio.id_medio_pago)"
                >
                  {{ medio.medio_pago }}
                </option>
              </select>
            </div>
            <p v-if="form.errors.id_medio_pago" class="mt-2 text-xs text-red-500">
              {{ form.errors.id_medio_pago }}
            </p>
          </div>

          <div class="md:col-span-2">
            <label :class="labelClass()">Referencia del pago</label>
            <input
              v-model="form.referencia_pago"
              type="text"
              maxlength="60"
              :class="fieldClass()"
              placeholder="Ej. transferencia, consignación, número de comprobante"
            />
            <p v-if="form.errors.referencia_pago" class="mt-2 text-xs text-red-500">
              {{ form.errors.referencia_pago }}
            </p>
          </div>

          <div class="md:col-span-2">
            <label :class="labelClass()">Descripción</label>
            <textarea
              v-model="form.descripcion"
              rows="4"
              :class="fieldClass()"
              placeholder="Observaciones adicionales del pago"
            />
            <p v-if="form.errors.descripcion" class="mt-2 text-xs text-red-500">
              {{ form.errors.descripcion }}
            </p>
          </div>
        </div>
      </div>

      <div class="rounded-3xl border border-gray-200 bg-white shadow-sm overflow-hidden">
        <div class="border-b border-gray-200 px-5 py-4">
          <h2 class="text-base font-extrabold text-gray-900">Comprobante del pago</h2>
        </div>

        <div class="p-5 space-y-4">
          <label :class="labelClass()">Imagen del comprobante</label>

          <label
            class="flex cursor-pointer flex-col items-center justify-center gap-3 rounded-3xl border-2 border-dashed border-gray-300 bg-gray-50 px-6 py-8 text-center transition hover:border-[#FFEA00] hover:bg-[#FFFDE6]"
          >
            <DocumentArrowUpIcon class="h-10 w-10 text-gray-400" />
            <div>
              <p class="text-sm font-bold text-gray-900">Selecciona una imagen</p>
              <p class="mt-1 text-xs text-gray-500">
                Formatos permitidos: JPG, JPEG, PNG, WEBP. Máximo 1 MB.
              </p>
            </div>
            <input
              type="file"
              class="hidden"
              accept=".jpg,.jpeg,.png,.webp,image/*"
              @change="handleFileChange"
            />
          </label>

          <p v-if="form.errors.comprobante" class="text-xs text-red-500">
            {{ form.errors.comprobante }}
          </p>

          <div v-if="previewUrl" class="rounded-3xl border border-gray-200 bg-white p-4">
            <div class="mb-3 flex items-center justify-between gap-3">
              <div class="flex items-center gap-2">
                <PhotoIcon class="h-5 w-5 text-[#1e3a5f]" />
                <p class="text-sm font-bold text-gray-900">Vista previa del comprobante</p>
              </div>

              <button
                type="button"
                @click="quitarComprobante"
                class="inline-flex items-center gap-2 rounded-2xl border border-red-200 bg-red-50 px-3 py-2 text-xs font-bold text-red-700 transition hover:bg-red-100"
              >
                <TrashIcon class="h-4 w-4" />
                Quitar
              </button>
            </div>

            <img
              :src="previewUrl"
              alt="Vista previa comprobante"
              class="max-h-[420px] w-full rounded-2xl border border-gray-200 object-contain bg-gray-50"
            />
          </div>
        </div>
      </div>
    </section>

    <aside class="xl:col-span-4">
      <div class="sticky top-6 space-y-6">
        <div class="rounded-3xl border border-gray-200 bg-white shadow-sm overflow-hidden">
          <div class="bg-gradient-to-r from-[#FFEA00] via-[#FFF15C] to-[#FFF9B8] px-5 py-5">
            <h3 class="text-lg font-extrabold text-[#474100]">
              {{ formMode === 'edit' ? 'Editar pago' : 'Registrar pago' }}
            </h3>
            <p class="mt-1 text-sm text-[#474100]/80">
              Relaciona el pago con la venta, la cuota y el comprobante.
            </p>
          </div>

          <div class="p-5 space-y-4">
            <div class="rounded-2xl bg-gray-50 px-4 py-3">
              <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Cliente</p>
              <p class="mt-1 text-sm font-bold text-gray-900">
                {{ ventaSeleccionada?.cliente || 'Pendiente' }}
              </p>
            </div>

            <div class="rounded-2xl bg-gray-50 px-4 py-3">
              <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Proyecto</p>
              <p class="mt-1 text-sm font-bold text-gray-900">
                {{ ventaSeleccionada?.proyecto || 'Pendiente' }}
              </p>
            </div>

            <div class="rounded-2xl bg-gray-50 px-4 py-3">
              <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Inmueble</p>
              <p class="mt-1 text-sm font-bold text-gray-900">
                {{ ventaSeleccionada?.inmueble || 'Pendiente' }}
              </p>
            </div>

            <div class="rounded-2xl bg-gray-50 px-4 py-3">
              <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                Cuota seleccionada
              </p>
              <p class="mt-1 text-sm font-bold text-gray-900">
                {{ cuotaSeleccionada ? `Cuota #${cuotaSeleccionada.numero_cuota}` : 'No asociada' }}
              </p>
              <p v-if="cuotaSeleccionada" class="mt-1 text-xs text-gray-500">
                {{ formatMoney(cuotaSeleccionada.valor_cuota) }} · Estado:
                {{ cuotaSeleccionada.estado || '—' }}
              </p>
            </div>

            <div class="rounded-2xl border border-[#FFEA00]/40 bg-[#FFFDE6] px-4 py-4">
              <p class="text-xs font-semibold uppercase tracking-wide text-[#756C00]">
                Valor digitado
              </p>
              <p class="mt-1 text-xl font-extrabold text-[#474100]">
                {{ formatMoney(form.valor) }}
              </p>
            </div>

            <div class="flex flex-col gap-3">
              <button
                type="button"
                @click="submit"
                :disabled="form.processing"
                class="inline-flex w-full items-center justify-center rounded-2xl bg-[#1e3a5f] px-5 py-3.5 text-sm font-extrabold text-white shadow-sm transition hover:bg-[#2c5282] disabled:cursor-not-allowed disabled:opacity-60"
              >
                {{
                  form.processing
                    ? 'Guardando...'
                    : formMode === 'edit'
                      ? 'Actualizar pago'
                      : 'Guardar pago'
                }}
              </button>

              <Link
                :href="backRoute"
                class="inline-flex w-full items-center justify-center rounded-2xl border border-gray-300 bg-white px-5 py-3 text-sm font-semibold text-gray-700 transition hover:bg-gray-50"
              >
                Cancelar
              </Link>
            </div>
          </div>
        </div>
      </div>
    </aside>
  </div>
</template>
