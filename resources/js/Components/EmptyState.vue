<!-- resources/js/Components/EmptyState.vue -->
<template>
  <div class="mt-5 rounded-2xl border border-gray-200 bg-gray-50 px-6 py-10 text-center">
    <MagnifyingGlassIcon class="w-8 h-8 mx-auto text-brand-700" />
    <p class="mt-3 text-sm font-semibold text-gray-900">{{ title }}</p>
    <p class="mt-1 text-sm text-gray-600">{{ message }}</p>

    <Link
      v-if="actionHref && actionText"
      :href="actionHref"
      class="mt-4 inline-flex items-center gap-2 rounded-xl bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700 transition"
    >
      <PlusIcon class="w-5 h-5" />
      {{ actionText }}
    </Link>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import { MagnifyingGlassIcon, PlusIcon } from '@heroicons/vue/24/outline'

defineProps({
  title: { type: String, default: 'Sin resultados' },
  message: { type: String, default: 'No hay elementos para mostrar.' },
  actionHref: { type: String, default: '' },
  actionText: { type: String, default: '' },
})
</script>
