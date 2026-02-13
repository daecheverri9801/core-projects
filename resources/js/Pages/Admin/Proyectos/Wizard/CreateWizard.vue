<template>
  <TopBannerLayout :empleado="empleado" panel-name="Panel administrador">
    <div class="space-y-6">
      <PageHeader
        title="Crear proyecto (Wizard)"
        kicker="Proyectos"
        subtitle="Completa las pestañas. Guarda cada sección para continuar sin salir del módulo."
      >
        <template #actions>
          <div class="flex items-center gap-2">
            <Link
              href="/proyectos"
              class="rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-800 hover:bg-gray-50 transition"
            >
              Volver
            </Link>
          </div>
        </template>
      </PageHeader>

      <WizardTabs :active="activeTab" :proyecto-id="proyectoId" @change="goTab" />

      <div
        v-if="!proyectoId && activeTab !== '1'"
        class="rounded-2xl border border-amber-200 bg-amber-50 p-4"
      >
        <p class="text-sm font-semibold text-amber-900">Primero crea el proyecto (Pestaña 1).</p>
        <p class="text-sm text-amber-900/80 mt-1">
          Las demás pestañas se habilitan cuando exista el proyecto.
        </p>
      </div>

      <!-- TAB 1 -->
      <ProyectoGeneralTab
        v-show="activeTab === '1'"
        :proyecto="proyecto"
        :estados="estadosProyectoSafe"
        :ubicaciones="ubicaciones"
        :proyecto-id="proyectoId"
        @saved="onSavedProyecto"
      />

      <!-- TAB 2 -->
      <PoliticasPrecioTab
        v-show="activeTab === '2'"
        :proyecto-id="proyectoId"
        :disabled="!proyectoId"
        @saved="reloadLite(['proyecto'])"
      />

      <!-- TAB 3 -->
      <TorresTab
        v-show="activeTab === '3'"
        :proyecto-id="proyectoId"
        :estados-proyecto="estadosProyectoSafe"
        :disabled="!proyectoId"
        @saved="reloadLite(['torres'])"
      />

      <!-- TAB 4 -->
      <PisosTorreTab
        v-show="activeTab === '4'"
        :proyecto-id="proyectoId"
        :torres="torres"
        :disabled="!proyectoId"
        @saved="reloadLite(['pisos'])"
      />

      <!-- TAB 5 -->
      <TiposApartamentoTab
        v-show="activeTab === '5'"
        :proyecto-id="proyectoId"
        :disabled="!proyectoId"
        @saved="reloadLite(['tiposApartamento'])"
      />

      <!-- TAB 6 -->
      <InventarioTab
        v-show="activeTab === '6'"
        :proyecto-id="proyectoId"
        :torres="torres"
        :pisos="pisos"
        :tipos-apartamento="tiposApartamento"
        :estados-inmueble="estadosInmueble"
        :disabled="!proyectoId"
        @saved-apartamentos="reloadLite(['apartamentosLite'])"
        @saved-locales="reloadLite([])"
      />

      <!-- TAB 7 -->
      <ParqueaderosTab
        v-show="activeTab === '7'"
        :proyecto-id="proyectoId"
        :apartamentos="apartamentosLite"
        :disabled="!proyectoId"
      />
    </div>
  </TopBannerLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'

import TopBannerLayout from '@/Components/TopBannerLayout.vue'
import PageHeader from '@/Components/PageHeader.vue'

import WizardTabs from '@/Pages/Admin/Proyectos/Wizard/Partials/WizardTabs.vue'

import ProyectoGeneralTab from '@/Pages/Admin/Proyectos/Wizard/Tabs/ProyectoGeneralTab.vue'
import PoliticasPrecioTab from '@/Pages/Admin/Proyectos/Wizard/Tabs/PoliticasPrecioTab.vue'
import TorresTab from '@/Pages/Admin/Proyectos/Wizard/Tabs/TorresTab.vue'
import PisosTorreTab from '@/Pages/Admin/Proyectos/Wizard/Tabs/PisosTorreTab.vue'
import TiposApartamentoTab from '@/Pages/Admin/Proyectos/Wizard/Tabs/TiposApartamentoTab.vue'
import InventarioTab from '@/Pages/Admin/Proyectos/Wizard/Tabs/InventarioTab.vue'
import ParqueaderosTab from '@/Pages/Admin/Proyectos/Wizard/Tabs/ParqueaderosTab.vue'

const props = defineProps({
  empleado: Object,
  tab: String,
  proyectoId: [String, Number, null],
  proyecto: Object,

  estadosProyecto: Array,
  estados: Array,
  ubicaciones: Array,

  torres: Array,
  pisos: Array,
  tiposApartamento: Array,
  estadosInmueble: Array,
  apartamentosLite: Array,
})

const estadosProyectoSafe = computed(() => {
  return (props.estadosProyecto?.length ? props.estadosProyecto : props.estados) ?? []
})

const activeTab = computed(() => String(props.tab || '1'))

function goTab(tab) {
  router.get(
    '/proyectos/wizard',
    { proyecto_id: props.proyectoId || null, tab },
    { preserveScroll: true, preserveState: true }
  )
}

function onSavedProyecto(newId) {
  // Cuando se crea por primera vez, backend redirige a wizard con proyecto_id y tab=2.
  // Si fue update, recargamos solo el proyecto.
  if (!props.proyectoId && newId) return
  reloadLite(['proyecto'])
}

function reloadLite(only = []) {
  router.reload({
    preserveScroll: true,
    preserveState: true,
    only: only.length ? only : undefined,
  })
}
</script>
