<template>
  <div class="min-h-screen bg-slate-50 flex flex-col justify-between font-sans">
    <header class="bg-white border-b border-slate-200/80 shadow-sm">
      <div class="max-w-6xl mx-auto px-6 py-4 flex items-center justify-between">
        <div class="flex items-center gap-4">
          <Logo class="h-12 w-auto object-contain" />
          <div class="h-6 w-[1px] bg-slate-300 hidden sm:block"></div>
          <h1 class="text-lg font-bold tracking-wider text-slate-700 hidden sm:block">
            PANEL DE PROYECTOS
          </h1>
        </div>
      </div>
    </header>

    <main class="flex-1 flex items-center justify-center px-4 py-12">
      <div class="w-full max-w-md">
        <div
          class="bg-white rounded-2xl shadow-xl shadow-slate-200/50 border border-slate-100 p-8 sm:p-10"
        >
          <div class="text-center sm:text-left mb-8">
            <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Iniciar sesión</h2>
            <p class="text-sm text-slate-500 mt-2 leading-relaxed">
              Ingresa con tu correo institucional para acceder al sistema de la constructora.
            </p>
          </div>

          <form @submit.prevent="submit" class="space-y-5" novalidate>
            <div>
              <label
                for="email"
                class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5"
              >
                Correo Electrónico
              </label>
              <input
                id="email"
                type="email"
                v-model="form.email"
                @input="clearError('email')"
                :class="inputClass('email')"
                class="block w-full rounded-lg px-4 py-2.5 text-sm transition-all duration-200 bg-slate-50 focus:bg-white border shadow-sm outline-none"
                required
                autocomplete="username"
                aria-invalid="form.errors.email ? 'true' : 'false'"
                :aria-describedby="form.errors.email ? 'email-error' : null"
                placeholder="usuario@olize.com"
              />
              <p
                v-if="form.errors.email"
                id="email-error"
                class="mt-1.5 text-xs font-medium text-red-600 flex items-center gap-1"
              >
                ⚠️ {{ form.errors.email }}
              </p>
            </div>

            <div>
              <label
                for="password"
                class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5"
              >
                Contraseña
              </label>
              <input
                id="password"
                type="password"
                v-model="form.password"
                @input="clearError('password')"
                :class="inputClass('password')"
                class="block w-full rounded-lg px-4 py-2.5 text-sm transition-all duration-200 bg-slate-50 focus:bg-white border shadow-sm outline-none"
                required
                autocomplete="current-password"
                aria-invalid="form.errors.password ? 'true' : 'false'"
                :aria-describedby="form.errors.password ? 'password-error' : null"
                placeholder="••••••••"
              />
              <p
                v-if="form.errors.password"
                id="password-error"
                class="mt-1.5 text-xs font-medium text-red-600 flex items-center gap-1"
              >
                ⚠️ {{ form.errors.password }}
              </p>
            </div>

            <div class="flex items-center justify-between text-sm pt-1">
              <label class="flex items-center gap-2 text-slate-600 cursor-pointer select-none">
                <input
                  type="checkbox"
                  v-model="form.remember"
                  class="rounded border-slate-300 text-slate-900 focus:ring-0 focus:ring-offset-0 w-4 h-4 cursor-pointer"
                />
                <span class="text-sm">Recordarme</span>
              </label>
              <a
                href="/forgot-password"
                class="text-xs font-semibold text-amber-600 hover:text-amber-700 hover:underline transition-colors"
              >
                ¿Olvidaste tu contraseña?
              </a>
            </div>

            <div class="pt-2">
              <button
                type="submit"
                :disabled="form.processing"
                class="w-full inline-flex items-center justify-center gap-2 rounded-lg bg-[#7C7C83] hover:bg-[#65656C] active:scale-[0.98] text-slate-900 font-bold py-3 px-4 shadow-md shadow-grey-400/20 transition-all duration-150 disabled:opacity-60 disabled:pointer-events-none"
                aria-live="polite"
              >
                <svg
                  v-if="form.processing"
                  class="animate-spin h-5 w-5 text-slate-900"
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
                <span>{{ form.processing ? 'Ingresando...' : 'Iniciar sesión' }}</span>
              </button>
            </div>

            <div
              v-if="serverError"
              class="text-sm font-medium text-red-600 text-center bg-red-50 py-2 rounded-lg border border-red-100"
            >
              {{ serverError }}
            </div>
          </form>
        </div>
      </div>
    </main>

    <footer class="py-6 border-t border-slate-200 bg-white w-full">
      <p class="text-center text-xs text-slate-400 font-medium">
        © {{ new Date().getFullYear() }} Olize Constructora. Todos los derechos reservados.
      </p>
    </footer>
  </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3'
import Logo from '@/Components/Logo.vue'
import { ref } from 'vue'

const form = useForm({
  email: '',
  password: '',
  remember: false,
})

const serverError = ref('')

function inputClass(field) {
  return form.errors[field]
    ? 'border-red-400 focus:border-red-500 focus:ring-4 focus:ring-red-100'
    : 'border-slate-200 focus:border-slate-400 focus:ring-4 focus:ring-slate-100'
}

function submit() {
  serverError.value = ''
  form.post('/login', {
    onError: () => {},
    onFinish: () => {},
  })
}

function clearError(field) {
  if (form.errors[field]) {
    form.errors[field] = null
  }
}
</script>
