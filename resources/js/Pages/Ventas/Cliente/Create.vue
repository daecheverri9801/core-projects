<template>
  <VentasLayout :empleado="empleado">
    <Head title="Registrar Nuevo Cliente" />

    <VentasPageHeader
      title="Registrar Nuevo Cliente"
      subtitle="Completa la información del cliente para agregarlo al sistema"
      :icon="UserPlusIcon"
    />

    <ClienteForm
      :form="form"
      :tipos-cliente="tiposCliente"
      :tipos-documento="tiposDocumento"
      :is-edit="false"
      submit-text="Crear Cliente"
      :processing="form.processing"
      cancel-url="/clientes"
      @submit="submit"
    />

    <FlashMessages />
  </VentasLayout>
</template>

<script setup>
import { reactive, watch } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import VentasLayout from '@/Components/VentasLayout.vue'
import FlashMessages from '@/Components/FlashMessages.vue'
import VentasPageHeader from '../Components/VentasPageHeader.vue'
import ClienteForm from '../Components/ClienteForm.vue'
import { UserPlusIcon } from '@heroicons/vue/24/outline'

defineProps({
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

function setFieldError(field, message) {
  form.errors = {
    ...form.errors,
    [field]: message,
  }
}

function clearFieldError(field) {
  if (!form.errors[field]) return

  const newErrors = { ...form.errors }
  delete newErrors[field]
  form.errors = newErrors
}

function validateNombre() {
  const value = form.nombre?.trim() || ''

  if (!value) {
    setFieldError('nombre', 'El nombre es obligatorio.')
    return false
  }

  if (value.length < 3) {
    setFieldError('nombre', 'El nombre debe tener al menos 3 caracteres.')
    return false
  }

  if (!/^[A-Za-zÁÉÍÓÚáéíóúÑñÜü\s]+$/.test(value)) {
    setFieldError('nombre', 'El nombre solo puede contener letras y espacios.')
    return false
  }

  clearFieldError('nombre')
  return true
}

function validateTipoCliente() {
  if (!form.id_tipo_cliente) {
    setFieldError('id_tipo_cliente', 'Debes seleccionar un tipo de cliente.')
    return false
  }

  clearFieldError('id_tipo_cliente')
  return true
}

function validateTipoDocumento() {
  if (!form.id_tipo_documento) {
    setFieldError('id_tipo_documento', 'Debes seleccionar un tipo de documento.')
    return false
  }

  clearFieldError('id_tipo_documento')
  return true
}

function validateDocumento() {
  const value = (form.documento || '').trim()

  if (!value) {
    setFieldError('documento', 'El número de documento es obligatorio.')
    return false
  }

  if (!/^\d+$/.test(value)) {
    setFieldError('documento', 'El documento solo puede contener números.')
    return false
  }

  if (value.length < 5) {
    setFieldError('documento', 'El documento debe tener al menos 5 dígitos.')
    return false
  }

  if (value.length > 20) {
    setFieldError('documento', 'El documento no puede superar los 20 dígitos.')
    return false
  }

  clearFieldError('documento')
  return true
}

function validateDireccion() {
  const value = (form.direccion || '').trim()

  if (!value) {
    clearFieldError('direccion')
    return true
  }

  if (value.length < 5) {
    setFieldError('direccion', 'La dirección debe tener al menos 5 caracteres.')
    return false
  }

  if (value.length > 255) {
    setFieldError('direccion', 'La dirección no puede superar los 255 caracteres.')
    return false
  }

  clearFieldError('direccion')
  return true
}

function validateTelefono() {
  const value = (form.telefono || '').trim()

  if (!value) {
    clearFieldError('telefono')
    return true
  }

  if (!/^\d+$/.test(value)) {
    setFieldError('telefono', 'El teléfono solo puede contener números.')
    return false
  }

  if (value.length < 7) {
    setFieldError('telefono', 'El teléfono debe tener al menos 7 dígitos.')
    return false
  }

  if (value.length > 15) {
    setFieldError('telefono', 'El teléfono no puede superar los 15 dígitos.')
    return false
  }

  clearFieldError('telefono')
  return true
}

function validateCorreo() {
  const value = (form.correo || '').trim()

  if (!value) {
    clearFieldError('correo')
    return true
  }

  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/

  if (!emailRegex.test(value)) {
    setFieldError('correo', 'Debes ingresar un correo electrónico válido.')
    return false
  }

  if (value.length > 255) {
    setFieldError('correo', 'El correo no puede superar los 255 caracteres.')
    return false
  }

  clearFieldError('correo')
  return true
}

function validateForm() {
  const results = [
    validateNombre(),
    validateTipoCliente(),
    validateTipoDocumento(),
    validateDocumento(),
    validateDireccion(),
    validateTelefono(),
    validateCorreo(),
  ]

  return results.every(Boolean)
}

/**
 * Normalización en tiempo real
 */
watch(
  () => form.nombre,
  (value) => {
    const limpio = (value || '').replace(/[^A-Za-zÁÉÍÓÚáéíóúÑñÜü\s]/g, '')
    if (limpio !== value) form.nombre = limpio
    validateNombre()
  }
)

watch(
  () => form.id_tipo_cliente,
  () => validateTipoCliente()
)

watch(
  () => form.id_tipo_documento,
  () => validateTipoDocumento()
)

watch(
  () => form.documento,
  (value) => {
    const limpio = (value || '').replace(/\D/g, '')
    if (limpio !== value) form.documento = limpio
    validateDocumento()
  }
)

watch(
  () => form.direccion,
  () => validateDireccion()
)

watch(
  () => form.telefono,
  (value) => {
    const limpio = (value || '').replace(/\D/g, '')
    if (limpio !== value) form.telefono = limpio
    validateTelefono()
  }
)

watch(
  () => form.correo,
  (value) => {
    const limpio = (value || '').trimStart()
    if (limpio !== value) form.correo = limpio
    validateCorreo()
  }
)

function resetForm() {
  form.nombre = ''
  form.id_tipo_cliente = ''
  form.id_tipo_documento = ''
  form.documento = ''
  form.direccion = ''
  form.telefono = ''
  form.correo = ''
  form.errors = {}
}

function submit() {
  form.errors = {}

  if (!validateForm()) return

  form.processing = true

  router.post(
    '/clientes',
    {
      nombre: form.nombre.trim(),
      id_tipo_cliente: form.id_tipo_cliente,
      id_tipo_documento: form.id_tipo_documento,
      documento: form.documento.trim(),
      direccion: form.direccion?.trim() || '',
      telefono: form.telefono?.trim() || '',
      correo: form.correo?.trim() || '',
    },
    {
      preserveScroll: true,
      onSuccess: () => {
        resetForm()
      },
      onError: (errors) => {
        form.errors = errors || {}
      },
      onFinish: () => {
        form.processing = false
      },
    }
  )
}
</script>
