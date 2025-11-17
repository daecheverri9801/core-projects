<template>
  <div class="min-h-screen flex flex-col">
    <!-- Banner superior -->
    <header class="bg-brand-500/5 border-b border-brand-200/30">
      <div class="max-w-5xl mx-auto px-4 py-4 flex items-center gap-4">
        <Logo class="w-10 h-10" />
        <div>
          <h1 class="text-xl font-semibold text-brand-900">Constructora A&C</h1>
          <p class="text-sm text-slate-600">Panel de Proyectos — Administración</p>
        </div>
      </div>
    </header>

    <!-- Contenido central -->
    <main class="flex-1 flex items-center justify-center px-4">
      <div class="w-full max-w-md">
        <div class="card p-8">
          <h2 class="text-2xl font-bold text-slate-800 mb-2">Iniciar sesión</h2>
          <p class="text-sm text-slate-500 mb-6">
            Ingresa con tu correo institucional para acceder al sistema.
          </p>

          <form @submit.prevent="submit" class="space-y-4" novalidate>
            <div>
              <label for="email" class="block text-sm font-medium text-slate-700">Correo</label>
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

            <div>
              <label for="password" class="block text-sm font-medium text-slate-700"
                >Contraseña</label
              >
              <input
                id="password"
                type="password"
                v-model="form.password"
                @input="clearError('password')"
                :class="inputClass('password')"
                class="mt-1 block w-full rounded-md border shadow-sm"
                required
                autocomplete="current-password"
                aria-invalid="form.errors.password ? 'true' : 'false'"
                :aria-describedby="form.errors.password ? 'password-error' : null"
              />
              <p v-if="form.errors.password" id="password-error" class="mt-1 text-sm text-red-600">
                {{ form.errors.password }}
              </p>
            </div>

            <div class="flex items-center justify-between text-sm">
              <label class="flex items-center gap-2 text-slate-700">
                <input
                  type="checkbox"
                  v-model="form.remember"
                  class="rounded text-brand-500 focus:ring-0"
                />
                Recordarme
              </label>
              <a href="/forgot-password" class="text-brand-700 hover:underline"
                >¿Olvidaste tu contraseña?</a
              >
            </div>

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
                Iniciar sesión
              </button>
            </div>

            <div v-if="serverError" class="text-sm text-red-600 text-center">{{ serverError }}</div>
          </form>
        </div>

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
import { ref, computed } from 'vue'

const form = useForm({
  email: '',
  password: '',
  remember: false,
})

const serverError = ref('')

function inputClass(field) {
  return [
    form.errors[field] ? 'border-red-500' : 'border-slate-200',
    'focus:ring-2 focus:ring-offset-0 focus:ring-brand-500',
  ].join(' ')
}

function submit() {
  serverError.value = ''
  form.post('/login', {
    onError: (errors) => {
      // Los errores de validación se asignan automáticamente a form.errors
      // Si quieres manejar errores generales, puedes hacerlo aquí
    },
    onFinish: () => {
      // Opcional: acciones después de submit
    },
  })
}

function clearError(field) {
  if (form.errors[field]) {
    form.errors[field] = null
  }
}
</script>
