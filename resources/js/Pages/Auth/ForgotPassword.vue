<template>
  <GuestLayout>
    <div class="max-w-md mx-auto p-6 bg-white rounded shadow">
      <h1 class="text-xl font-bold mb-4">Recuperar contraseña</h1>

      <form @submit.prevent="submit">
        <InputText
          label="Correo electrónico"
          v-model="form.email"
          :error="form.errors.email"
          required
          type="email"
        />

        <button
          type="submit"
          :disabled="form.processing"
          class="btn-primary mt-4 w-full"
        >
          Enviar enlace de recuperación
        </button>
      </form>

      <p v-if="status" class="mt-4 text-green-600">{{ status }}</p>
    </div>
  </GuestLayout>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3'
import GuestLayout from '@/Layouts/GuestLayout.vue'
import InputText from '@/Components/InputText.vue'


const props = defineProps({
  status: String,
})

const form = useForm({
  email: '',
})

function submit() {
  form.post(route('empleado.password.email'))
}
</script>