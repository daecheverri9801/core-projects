<!-- resources/js/Pages/Admin/ZonaSocial/Create.vue -->
<!-- Ajuste: integra Banner Flujo (8/8) y preselección por ?proyecto -->
<template>
  <TopBannerLayout :empleado="empleado" panel-name="Proyectos">
    <div class="space-y-6">
      <PageHeader
        title="Nueva zona social"
        kicker="Zonas sociales"
        subtitle="Registra una nueva zona social asociada a un proyecto."
      >
      </PageHeader>

      <!-- Banner Flujo (8/8) -->
      <AppCard padding="md" v-if="flowProyectoId">
        <div class="flex flex-col gap-4">
          <div class="flex items-start justify-between gap-4">
            <div class="min-w-0">
              <p class="text-sm font-semibold text-gray-900">Flujo de configuración</p>
              <p class="mt-1 text-sm text-gray-700">
                Proyecto <span class="font-semibold">#{{ flowProyectoId }}</span> · Paso
                <span class="font-semibold">8/8</span> (Zonas sociales)
              </p>
            </div>

            <Link
              :href="`/proyectos/${flowProyectoId}`"
              class="shrink-0 rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-800 hover:bg-gray-50 transition"
            >
              Volver al proyecto
            </Link>
          </div>

          <div class="overflow-x-auto">
            <ol class="min-w-[900px] grid grid-cols-8 gap-2">
              <li v-for="s in steps" :key="s.key">
                <Link
                  :href="s.href"
                  class="block rounded-xl border px-3 py-2 text-xs font-semibold transition"
                  :class="
                    s.key === activeStep
                      ? 'border-brand-400 bg-brand-50 text-brand-900'
                      : 'border-gray-200 bg-white text-gray-700 hover:bg-gray-50'
                  "
                >
                  <div class="flex items-center justify-between gap-2">
                    <span class="truncate">{{ s.label }}</span>
                    <span class="text-[10px] opacity-70">{{ s.n }}</span>
                  </div>
                </Link>
              </li>
            </ol>
          </div>

          <div class="flex items-center justify-between gap-2">
            <Link
              :href="steps[6].href"
              class="rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-800 hover:bg-gray-50 transition"
            >
              Anterior: Parqueaderos
            </Link>

            <Link
              :href="`/proyectos/${flowProyectoId}`"
              class="rounded-xl bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700 transition"
            >
              Finalizar: Ver proyecto
            </Link>
          </div>
        </div>
      </AppCard>

      <AppCard padding="md" class="max-w-3xl">
        <form @submit.prevent="submit" class="space-y-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div class="md:col-span-2">
              <label class="block text-sm font-semibold text-gray-900 mb-1">
                Proyecto <span class="text-red-500">*</span>
              </label>
              <select v-model="form.id_proyecto" class="field">
                <option value="">Seleccione un proyecto</option>
                <option v-for="p in proyectos" :key="p.id_proyecto" :value="p.id_proyecto">
                  {{ p.nombre }}
                </option>
              </select>
              <p v-if="errors.id_proyecto" class="mt-1 text-sm text-red-600">
                {{ errors.id_proyecto }}
              </p>
              <p class="mt-1 text-xs text-gray-500">
                La zona social quedará asociada a este proyecto.
              </p>
            </div>

            <div class="md:col-span-2">
              <label class="block text-sm font-semibold text-gray-900 mb-1">
                Nombre <span class="text-red-500">*</span>
              </label>
              <input
                v-model="form.nombre"
                type="text"
                maxlength="100"
                class="field"
                placeholder="Ej: Gimnasio, Piscina, Salón Social"
              />
              <p v-if="errors.nombre" class="mt-1 text-sm text-red-600">{{ errors.nombre }}</p>
            </div>

            <div class="md:col-span-2">
              <label class="block text-sm font-semibold text-gray-900 mb-1">
                Descripción <span class="text-gray-400 font-medium">(opcional)</span>
              </label>
              <textarea
                v-model="form.descripcion"
                rows="3"
                maxlength="100"
                class="field"
                placeholder="Breve descripción (máx. 100 caracteres)"
              />
              <div class="mt-1 flex items-center justify-between">
                <p v-if="errors.descripcion" class="text-sm text-red-600">
                  {{ errors.descripcion }}
                </p>
                <p class="text-xs text-gray-500 ml-auto">
                  {{ (form.descripcion || '').length }}/100
                </p>
              </div>
            </div>
          </div>

          <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-end">
            <Link
              href="/zonas-sociales"
              class="rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-800 hover:bg-gray-50 transition text-center"
            >
              Cancelar
            </Link>

            <button
              type="submit"
              class="rounded-xl bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-brand-700 transition disabled:opacity-60 disabled:cursor-not-allowed"
              :disabled="processing"
            >
              <span v-if="processing">Guardando…</span>
              <span v-else>Guardar</span>
            </button>
          </div>
        </form>
      </AppCard>

      <FlashMessages />
    </div>
  </TopBannerLayout>
</template>

<script setup>
import { reactive, ref, computed, onMounted } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'

import TopBannerLayout from '@/Components/TopBannerLayout.vue'
import FlashMessages from '@/Components/FlashMessages.vue'
import AppCard from '@/Components/AppCard.vue'
import PageHeader from '@/Components/PageHeader.vue'

const props = defineProps({
  proyectos: { type: Array, default: () => [] },
  empleado: { type: Object, default: null },
})

const page = usePage()
const flowProyectoId = computed(() => {
  const url = page?.url || ''
  const qs = url.split('?')[1] || ''
  const sp = new URLSearchParams(qs)
  return sp.get('proyecto')
})

const activeStep = 'zonas'
const steps = computed(() => {
  if (!flowProyectoId.value) return []
  const pid = flowProyectoId.value
  return [
    {
      n: '1/8',
      key: 'politicas',
      label: 'Políticas',
      href: `/politicas-precio-proyecto/crear?proyecto=${pid}`,
    },
    { n: '2/8', key: 'torres', label: 'Torres', href: `/admin/torres/create?proyecto=${pid}` },
    { n: '3/8', key: 'pisos', label: 'Pisos', href: `/pisos-torre/create?proyecto=${pid}` },
    {
      n: '4/8',
      key: 'tipos',
      label: 'Tipos apto',
      href: `/tipos-apartamento/create?proyecto=${pid}`,
    },
    {
      n: '5/8',
      key: 'apartamentos',
      label: 'Apartamentos',
      href: `/admin/apartamentos/create?proyecto=${pid}`,
    },
    { n: '6/8', key: 'locales', label: 'Locales', href: `/locales/create?proyecto=${pid}` },
    {
      n: '7/8',
      key: 'parqueaderos',
      label: 'Parqueaderos',
      href: `/parqueaderos/create?proyecto=${pid}`,
    },
    {
      n: '8/8',
      key: 'zonas',
      label: 'Zonas sociales',
      href: `/zonas-sociales/create?proyecto=${pid}`,
    },
  ]
})

const form = reactive({ id_proyecto: '', nombre: '', descripcion: '' })
const errors = ref({})
const processing = ref(false)

onMounted(() => {
  if (flowProyectoId.value && !form.id_proyecto) {
    form.id_proyecto = String(flowProyectoId.value)
  }
})

function submit() {
  errors.value = {}
  processing.value = true

  router.post(
    route('zonas-sociales.store'), // o '/zonas-sociales'
    {
      id_proyecto: form.id_proyecto || '',
      nombre: form.nombre,
      descripcion: form.descripcion || '',
    },
    {
      preserveScroll: true,
      preserveState: true, // evita que se pierda estado en la recarga
      onSuccess: () => {
        // deja el proyecto seleccionado y limpia campos
        const pid = form.id_proyecto
        form.nombre = ''
        form.descripcion = ''
        form.id_proyecto = pid
      },
      onError: (e) => {
        errors.value = e || {}
      },
      onFinish: () => {
        processing.value = false
      },
    }
  )
}
</script>

<style scoped>
.field {
  @apply w-full rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500;
}
</style>
