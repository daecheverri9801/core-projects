<template>
  <SidebarBannerLayout :empleado="empleado">
    <template #title>Dashboard</template>

    <div class="space-y-6">
      <!-- HERO -->
      <section
        class="rounded-2xl border border-brand-300/60 bg-white shadow-sm overflow-hidden"
      >
        <div class="p-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
          <div>
            <p class="text-sm text-gray-600">Panel administrativo</p>

            <h3 class="text-2xl font-semibold text-gray-900">
              Bienvenido,
              <span class="text-brand-900">{{ empleadoNombre }}</span>
            </h3>

            <p class="text-sm text-gray-700 mt-1 max-w-2xl">
              Accede rápidamente a los módulos del sistema y gestiona la información clave.
            </p>
          </div>

          <div class="flex items-center gap-3">
            <div
              class="hidden sm:flex items-center gap-2 rounded-xl border border-brand-300 bg-brand-200 px-3 py-2"
            >
              <span class="h-2 w-2 rounded-full bg-brand-700"></span>
              <span class="text-sm font-semibold text-brand-950">
                {{ cargoNombre }}
              </span>
            </div>

            <Link
              href="/perfil"
              class="inline-flex items-center gap-2 rounded-xl bg-brand-600 px-4 py-2 text-sm font-semibold text-white shadow
                     hover:bg-brand-700 active:bg-brand-800 transition"
            >
              <UserIcon class="w-4 h-4" />
              Mi perfil
            </Link>
          </div>
        </div>

        <div class="h-1.5 w-full bg-gradient-to-r from-brand-300 via-brand-600 to-brand-300"></div>
      </section>

      <!-- BUSCADOR -->
      <section class="rounded-2xl border border-brand-300/60 bg-white shadow-sm">
        <div class="p-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
          <div>
            <h4 class="text-base font-semibold text-gray-900">Módulos</h4>
            <p class="text-sm text-gray-600">Busca por nombre o categoría.</p>
          </div>

          <div class="w-full sm:w-[420px]">
            <QuickSearch v-model="query" placeholder="Buscar módulos…" />
          </div>
        </div>
      </section>

      <!-- GRID -->
      <section class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
        <AdminTile
          v-for="item in filteredItems"
          :key="item.href"
          :href="item.href"
          :icon="item.icon"
          :title="item.title"
          :description="item.description"
          :tag="item.tag"
        />
      </section>

      <!-- EMPTY STATE -->
      <section
        v-if="filteredItems.length === 0"
        class="rounded-2xl border border-dashed border-brand-300 bg-white p-10 text-center"
      >
        <MagnifyingGlassIcon class="w-8 h-8 mx-auto text-brand-700" />
        <h4 class="mt-3 text-base font-semibold text-gray-900">Sin resultados</h4>
        <p class="mt-1 text-sm text-gray-600">
          No se encontraron módulos para
          <span class="font-semibold">"{{ query }}"</span>
        </p>

        <button
          @click="query = ''"
          class="mt-4 rounded-xl bg-brand-600 px-4 py-2 text-sm font-semibold text-white
                 hover:bg-brand-700 transition"
        >
          Limpiar búsqueda
        </button>
      </section>
    </div>
  </SidebarBannerLayout>
</template>

<script setup>
import { computed, ref } from 'vue'
import { Link } from '@inertiajs/vue3'
import SidebarBannerLayout from '@/Components/SidebarBannerLayout.vue'
import AdminTile from '@/Components/AdminTile.vue'
import QuickSearch from '@/Components/QuickSearch.vue'

import { UserIcon, MagnifyingGlassIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  empleado: Object,
})

const empleadoNombre = computed(() => {
  if (!props.empleado) return 'Usuario'
  return [props.empleado.nombre, props.empleado.apellido].filter(Boolean).join(' ')
})

const cargoNombre = computed(() => props.empleado?.cargo?.nombre || 'Cargo')

const query = ref('')

const items = [
  { title: 'Proyectos', description: 'Gestión general de proyectos.', icon: 'FolderIcon', href: '/proyectos', tag: 'Core' },
  { title: 'Empleados', description: 'Administración de personal.', icon: 'UsersIcon', href: '/empleados', tag: 'Admin' },
  { title: 'Estados', description: 'Catálogo de estados.', icon: 'CheckCircleIcon', href: '/estados', tag: 'Config' },
  { title: 'Dependencias y Cargos', description: 'Estructura organizacional.', icon: 'IdentificationIcon', href: '/dependencias-cargos', tag: 'Admin' },
  { title: 'Torres', description: 'Gestión de torres.', icon: 'BuildingOfficeIcon', href: '/admin/torres', tag: 'Inmobiliario' },
  { title: 'Pisos Torre', description: 'Configuración de pisos.', icon: 'HomeIcon', href: '/pisos-torre', tag: 'Inmobiliario' },
  { title: 'Apartamentos', description: 'Inventario de apartamentos.', icon: 'HomeIcon', href: '/apartamentos', tag: 'Inmobiliario' },
  { title: 'Tipos Apartamento', description: 'Tipologías de unidades.', icon: 'Squares2X2Icon', href: '/tipos-apartamento', tag: 'Config' },
  { title: 'Locales', description: 'Locales comerciales.', icon: 'BuildingStorefrontIcon', href: '/locales', tag: 'Inmobiliario' },
  { title: 'Zonas Sociales', description: 'Amenidades y zonas comunes.', icon: 'SparklesIcon', href: '/zonas-sociales', tag: 'Inmobiliario' },
  { title: 'Parqueaderos', description: 'Gestión de parqueaderos.', icon: 'TruckIcon', href: '/parqueaderos', tag: 'Inmobiliario' },
  { title: 'Políticas', description: 'Políticas de precios.', icon: 'ShieldCheckIcon', href: '/politicas-precio-proyecto', tag: 'Comercial' },
]

const filteredItems = computed(() => {
  if (!query.value) return items
  const q = query.value.toLowerCase()
  return items.filter(i =>
    i.title.toLowerCase().includes(q) ||
    i.description.toLowerCase().includes(q) ||
    i.tag.toLowerCase().includes(q)
  )
})
</script>
