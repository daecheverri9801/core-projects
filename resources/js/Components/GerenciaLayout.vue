<script setup>
import { Head, Link, usePage, router } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import ConfirmDialog from '@/Components/ConfirmDialog.vue'
import { useIdleLogout } from '@/composables/useIdleLogout'

useIdleLogout({ minutes: 30 })

const props = defineProps({
  empleado: { type: Object, default: null },
})

const page = usePage()

const showLogoutModal = ref(false)

function logout() {
  showLogoutModal.value = true
}

function handleLogoutConfirm() {
  router.post('/logout')
  showLogoutModal.value = false
}

const empleado = computed(() => {
  if (props.empleado) return props.empleado
  return page.props.auth?.empleado || page.props.empleado || null
})

const empleadoCompleto = computed(() => {
  const emp = empleado.value
  if (!emp) return 'Usuario'
  return [emp.nombre, emp.apellido].filter(Boolean).join(' ') || 'Usuario'
})

const cargoNombre = computed(() => empleado.value?.cargo?.nombre || 'Gerente')
</script>

<template>
  <div class="min-h-screen bg-slate-950 text-slate-100">
    <Head>
      <title>Panel de Gerencia</title>
    </Head>

    <!-- Top bar -->
    <header class="border-b border-slate-800 bg-slate-900/80 backdrop-blur">
      <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between">
        <div class="flex items-center gap-3">
          <div
            class="rounded-lg bg-white flex items-center justify-center shadow-sm border border-gray-100"
          >
            <img src="/images/logo-ayc.png" class="h-[80px]" alt="Logo" />
          </div>
          <div>
            <div class="font-semibold text-slate-50">Constructora A&amp;C</div>
          </div>
        </div>

        <div class="flex items-center gap-4 text-sm">
          <div class="text-right">
            <div class="text-slate-300 font-medium">{{ empleadoCompleto }}</div>
            <div class="text-slate-500 text-xs">{{ empleado?.correo || cargoNombre }}</div>
          </div>
          <Link
            href="/dashboard"
            class="px-3 py-1.5 rounded-lg border border-slate-700 text-xs uppercase tracking-wide hover:bg-slate-800"
          >
            Administrador
          </Link>
          <Link
            href="/ventas"
            class="px-3 py-1.5 rounded-lg border border-slate-700 text-xs uppercase tracking-wide hover:bg-slate-800"
          >
            Ventas
          </Link>
          <Link
            href="/gerencia/metas"
            class="px-3 py-1.5 rounded-lg border border-slate-700 text-xs uppercase tracking-wide hover:bg-slate-800"
          >
            Metas
          </Link>
          <Link
            href="/gerencia/dashboard"
            class="px-3 py-1.5 rounded-lg border border-slate-700 text-xs uppercase tracking-wide hover:bg-slate-800"
          >
            Dashboard
          </Link>
          <Link
            href="/gerencia/login-logs"
            class="px-3 py-1.5 rounded-lg border border-slate-700 text-xs uppercase tracking-wide hover:bg-slate-800"
          >
            Registro de Inicios Sesion
          </Link>
          <button
            @click="logout"
            class="px-3 py-1.5 rounded-lg border border-red-600 text-xs uppercase tracking-wide text-red-400 hover:bg-red-700"
          >
            Logout
          </button>
        </div>
      </div>
    </header>

    <!-- Content -->
    <main class="max-w-7xl mx-auto px-4 py-6" style="overflow-x: hidden; overflow-y: auto">
      <slot />
    </main>
    <ConfirmDialog
      :open="showLogoutModal"
      title="Cerrar sesión"
      message="¿Está seguro que desea cerrar sesión?"
      confirm-text="Sí, cerrar sesión"
      cancel-text="Cancelar"
      @cancel="showLogoutModal = false"
      @confirm="handleLogoutConfirm"
    />
  </div>
</template>
