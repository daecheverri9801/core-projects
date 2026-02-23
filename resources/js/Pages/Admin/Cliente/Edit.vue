<!-- resources/js/Pages/Admin/Ventas/Clientes/Edit.vue -->
<template>
  <TopBannerLayout :empleado="empleado">
    <Head title="Editar cliente" />

    <div class="space-y-6">
      <VentasPageHeader
        title="Editar cliente"
        :subtitle="`Actualiza la información de ${cliente?.nombre || '—'}`"
        :icon="PencilSquareIcon"
      />

      <VentasCard>
        <template #header>
          <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
              <h2 class="text-lg font-semibold text-gray-900">Datos del cliente</h2>
              <p class="text-sm text-gray-600 mt-1">
                Modifica la información necesaria y guarda los cambios.
              </p>
            </div>

            <div class="flex items-center gap-2">
              <Link
                href="/admin/clientes"
                class="rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-800 hover:bg-gray-50 transition whitespace-nowrap"
              >
                Volver
              </Link>
            </div>
          </div>
        </template>

        <ClienteFormAdmin
          :form="form"
          :tipos-cliente="tiposCliente"
          :tipos-documento="tiposDocumento"
          :is-edit="true"
          submit-text="Actualizar cliente"
          :processing="form.processing"
          @submit="submit"
        />
      </VentasCard>

      <FlashMessages />
    </div>
  </TopBannerLayout>
</template>

<script setup>
import { reactive } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'

import TopBannerLayout from '@/Components/TopBannerLayout.vue'
import VentasCard from '@/Components/VentasCard.vue'
import VentasPageHeader from '@/Components/VentasPageHeader.vue'
import FlashMessages from '@/Components/FlashMessages.vue'

import { PencilSquareIcon } from '@heroicons/vue/24/outline'
import ClienteFormAdmin from '@/Components/ClienteFormAdmin.vue'

const props = defineProps({
  cliente: { type: Object, required: true },
  tiposCliente: { type: Array, default: () => [] },
  tiposDocumento: { type: Array, default: () => [] },
  empleado: { type: Object, default: null },
})

const form = reactive({
  nombre: props.cliente.nombre || '',
  id_tipo_cliente: props.cliente.id_tipo_cliente || '',
  id_tipo_documento: props.cliente.id_tipo_documento || '',
  documento: props.cliente.documento || '',
  direccion: props.cliente.direccion || '',
  telefono: props.cliente.telefono || '',
  correo: props.cliente.correo || '',
  processing: false,
  errors: {},
})

function submit() {
  form.processing = true
  form.errors = {}

  router.put(
    `/admin/clientes/${props.cliente.documento}`,
    {
      nombre: form.nombre,
      id_tipo_cliente: form.id_tipo_cliente,
      id_tipo_documento: form.id_tipo_documento,
      direccion: form.direccion,
      telefono: form.telefono,
      correo: form.correo,
    },
    {
      preserveScroll: true,
      onSuccess: () => {
        form.processing = false
      },
      onError: (errors) => {
        form.errors = errors
        form.processing = false
      },
      onFinish: () => {
        form.processing = false
      },
    }
  )
}
</script>
