<template>
  <GuestLayout>
    <div class="max-w-md mx-auto p-6 bg-white rounded shadow">
      <h1 class="text-xl font-bold mb-4">Restablecer contraseña</h1>

      <form @submit.prevent="submit">
        <input type="hidden" v-model="form.token" />
        <InputText
          label="Correo electrónico"
          v-model="form.email"
          :error="form.errors.email"
          required
          type="email"
        />
        <InputText
          label="Nueva contraseña"
          v-model="form.password"
          :error="form.errors.password"
          required
          type="password"
        />
        <InputText
          label="Confirmar contraseña"
          v-model="form.password_confirmation"
          :error="form.errors.password_confirmation"
          required
          type="password"
        />

        <button
          type="submit"
          :disabled="form.processing"
          class="btn-primary mt-4 w-full"
        >
          Restablecer contraseña
        </button>
      </form>
    </div>
  </GuestLayout>
</template>

<script setup>
import { useForm } from '@inertiajs/inertia-vue3'
import GuestLayout from '@/Layouts/GuestLayout.vue'
import InputText from '@/Components/InputText.vue'

const props = defineProps({
  token: String,
  email: String,
})

const form = useForm({
  token: props.token,
  email: props.email || '',
  password: '',
  password_confirmation: '',
})

function submit() {
  form.post(route('empleado.password.update'))
}
</script>