<script setup>
import { Head, Link, usePage, router } from '@inertiajs/vue3'

const page = usePage()
const user = page.props.auth?.user || null

function logout() {
  router.post('/logout')
}
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
          <img src="/images/logo-ayc.png" class="h-8" alt="Logo" />
          <div>
            <div class="text-xs uppercase tracking-[0.2em] text-slate-400">Panel estratégico</div>
            <div class="font-semibold text-slate-50">Constructora A&amp;C · Gerencia</div>
          </div>
        </div>

        <div class="flex items-center gap-4 text-sm">
          <div class="text-right">
            <div class="text-slate-300 font-medium">{{ user?.name ?? 'Usuario' }}</div>
            <div class="text-slate-500 text-xs">Gerencia</div>
          </div>
          <Link
            href="/ventas"
            class="px-3 py-1.5 rounded-lg border border-slate-700 text-xs uppercase tracking-wide hover:bg-slate-800"
          >
            Ir a Ventas
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
            href="#"
            @click.prevent="logout"
            class="px-3 py-1.5 rounded-lg border border-red-600 text-xs uppercase tracking-wide text-red-400 hover:bg-red-700"
          >
            Logout
          </Link>
        </div>
      </div>
    </header>

    <!-- Content -->
    <main class="max-w-7xl mx-auto px-4 py-6" style="overflow-x: hidden; overflow-y: auto">
      <slot />
    </main>
  </div>
</template>
