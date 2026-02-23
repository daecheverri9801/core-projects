<template>
  <div class="min-h-screen flex flex-col">
    <!-- Banner superior (igual que en login) -->
    <header class="bg-brand-500/5 border-b border-brand-200/30">
      <div class="max-w-5xl mx-auto px-4 py-4 flex items-center gap-4">
        <Logo class="w-10 h-10" />
        <div>
          <h1 class="text-xl font-semibold text-brand-900">Constructora A&C</h1>
          <p class="text-sm text-slate-600">Panel de Proyectos</p>
        </div>
      </div>
    </header>

    <!-- Contenido principal centrado -->
    <main class="flex-1 flex items-center justify-center px-4">
      <div class="w-full max-w-md">
        <div class="card p-8">
          <h2 class="text-2xl font-bold text-slate-800 mb-2">Recuperar contraseña</h2>
          <p class="text-sm text-slate-500 mb-6">
            Ingresa tu correo electrónico y te enviaremos un enlace para restablecer tu contraseña.
          </p>

          <form @submit.prevent="submit" class="space-y-4" novalidate>
            <!-- Campo de correo -->
            <div>
              <label for="email" class="block text-sm font-medium text-slate-700">
                Correo electrónico
              </label>
              <input
                id="email"
                type="email"
                v-model="form.email"
                @input="clearError('email')"
                :class="inputClass('email')"
                class="mt-1 block w-full rounded-md border shadow-sm"
                required
                autocomplete="username"
                aria-invalid="form.errors.email ? 'true' : 'false'"
                :aria-describedby="form.errors.email ? 'email-error' : null"
              />
              <p v-if="form.errors.email" id="email-error" class="mt-1 text-sm text-red-600">
                {{ form.errors.email }}
              </p>
            </div>

            <!-- Botón de envío -->
            <div>
              <button
                type="submit"
                :disabled="form.processing"
                class="w-full inline-flex items-center justify-center gap-2 rounded-md bg-brand-500 hover:bg-brand-600 text-slate-900 font-semibold py-2 px-4 disabled:opacity-60"
                aria-live="polite"
              >
                <svg
                  v-if="form.processing"
                  class="animate-spin -ml-1 mr-2 h-5 w-5 text-slate-900"
                  xmlns="http://www.w3.org/2000/svg"
                  fill="none"
                  viewBox="0 0 24 24"
                >
                  <circle
                    class="opacity-25"
                    cx="12"
                    cy="12"
                    r="10"
                    stroke="currentColor"
                    stroke-width="4"
                  ></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                </svg>
                Enviar enlace de recuperación
              </button>
            </div>

            <!-- Mensaje de éxito (status) -->
            <p v-if="status" class="mt-4 text-sm text-green-600 text-center">
              {{ status }}
            </p>

            <!-- Enlace para volver al login -->
            <div class="text-center text-sm">
              <a href="/" class="text-brand-700 hover:underline">
                Volver al inicio de sesión
              </a>
            </div>
          </form>
        </div>

        <!-- Footer copyright -->
        <p class="text-center text-xs text-slate-500 mt-4">
          © {{ new Date().getFullYear() }} Constructora A&C. Todos los derechos reservados.
        </p>
      </div>
    </main>
  </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3'
import Logo from '@/Components/Logo.vue'

// Recibimos el posible mensaje de estado (por ejemplo, "Hemos enviado el enlace...")
const props = defineProps({
  status: String,
})

const form = useForm({
  email: '',
})

function inputClass(field) {
  return [
    form.errors[field] ? 'border-red-500' : 'border-slate-200',
    'focus:ring-2 focus:ring-offset-0 focus:ring-brand-500',
  ].join(' ')
}

function submit() {
  form.post(route('empleado.password.email'), {
    onError: () => {
      // Los errores de validación se asignan automáticamente a form.errors
    },
  })
}

function clearError(field) {
  if (form.errors[field]) {
    form.errors[field] = null
  }
}
</script>
