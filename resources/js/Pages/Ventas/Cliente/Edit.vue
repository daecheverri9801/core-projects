<template>
  <VentasLayout :empleado="empleado">
    <VentasPageHeader
      title="Editar Cliente"
      :subtitle="`Actualiza la información de ${cliente.nombre}`"
      :icon="PencilSquareIcon"
    />

    <ClienteForm
      :form="form"
      :tipos-cliente="tiposCliente"
      :tipos-documento="tiposDocumento"
      :is-edit="true"
      submit-text="Actualizar Cliente"
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
import { PencilSquareIcon } from '@heroicons/vue/24/outline'

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

  Inertia.put(
    `/clientes/${props.cliente.documento}`,
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
