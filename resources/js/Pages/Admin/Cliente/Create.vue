<!-- resources/js/Pages/Admin/Ventas/Clientes/Create.vue -->
<template>
  <TopBannerLayout :empleado="empleado" panel-name="Panel administrador">
    <Head title="Registrar cliente" />

    <div class="space-y-6">
      <VentasPageHeader
        title="Registrar nuevo cliente"
        subtitle="Completa la información del cliente para agregarlo al sistema."
        :icon="UserPlusIcon"
      />

      <VentasCard>
        <template #header>
          <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
              <h2 class="text-lg font-semibold text-gray-900">Datos del cliente</h2>
              <p class="text-sm text-gray-600 mt-1">
                Los campos marcados como obligatorios deben completarse para guardar.
              </p>
            </div>

            <Link
              href="/clientes"
              class="rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-800 hover:bg-gray-50 transition whitespace-nowrap"
            >
              Volver
            </Link>
          </div>
        </template>

        <ClienteForm
          :form="form"
          :tipos-cliente="tiposCliente"
          :tipos-documento="tiposDocumento"
          submit-text="Crear cliente"
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
import ClienteForm from '@/Components/ClienteForm.vue'
import FlashMessages from '@/Components/FlashMessages.vue'

import { UserPlusIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  tiposCliente: { type: Array, default: () => [] },
  tiposDocumento: { type: Array, default: () => [] },
  empleado: { type: Object, default: null },
})

const form = reactive({
  nombre: '',
  id_tipo_cliente: '',
  id_tipo_documento: '',
  documento: '',
  direccion: '',
  telefono: '',
  correo: '',
  processing: false,
  errors: {},
})

function submit() {
  form.processing = true
  form.errors = {}

  router.post(
    '/admin/clientes',
    {
      nombre: form.nombre,
      id_tipo_cliente: form.id_tipo_cliente,
      id_tipo_documento: form.id_tipo_documento,
      documento: form.documento,
      direccion: form.direccion,
      telefono: form.telefono,
      correo: form.correo,
    },
    {
      preserveScroll: true,
      onSuccess: () => {
        form.nombre = ''
        form.id_tipo_cliente = ''
        form.id_tipo_documento = ''
        form.documento = ''
        form.direccion = ''
        form.telefono = ''
        form.correo = ''
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
