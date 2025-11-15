<template>
  <VentasLayout :empleado="empleado">
    <VentasPageHeader
      title="Registrar Nuevo Cliente"
      subtitle="Completa la información del cliente para agregarlo al sistema"
      :icon="UserPlusIcon"
    />

    <ClienteForm
      :form="form"
      :tipos-cliente="tiposCliente"
      :tipos-documento="tiposDocumento"
      submit-text="Crear Cliente"
      :processing="form.processing"
      @submit="submit"
    />

    <FlashMessages />
  </VentasLayout>
</template>

<script setup>
import { reactive } from 'vue'
import { Inertia } from '@inertiajs/inertia'
import VentasLayout from '@/Components/VentasLayout.vue'
import FlashMessages from '@/Components/FlashMessages.vue'
import VentasPageHeader from '../Components/VentasPageHeader.vue'
import ClienteForm from '../Components/ClienteForm.vue'
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

  Inertia.post(
    '/clientes',
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
        // Resetear formulario
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
