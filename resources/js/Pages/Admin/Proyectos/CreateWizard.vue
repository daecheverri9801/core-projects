<!-- resources/js/Pages/Admin/Proyectos/CreateWizard.vue -->
<template>
  <TopBannerLayout :empleado="empleado" panel-name="Panel administrador">
    <!-- Toast Notifications -->
    <ToastNotification />

    <div class="space-y-6">
      <!-- Header -->
      <PageHeader title="Crear proyecto" kicker="Proyectos" :subtitle="wizardSubtitle">
        <template #actions>
          <div class="flex items-center gap-2">
            <Link href="/proyectos" class="btn-secondary"> Volver </Link>

            <!-- Guardar proyecto solo en pestaña 1 -->
            <button
              v-if="activeTab === 'general'"
              type="button"
              @click="handleSaveProyecto"
              :disabled="formProyecto.processing"
              class="btn-primary"
            >
              {{ formProyecto.processing ? 'Guardando…' : 'Guardar proyecto' }}
            </button>
          </div>
        </template>
      </PageHeader>

      <!-- Progress Bar -->
      <div v-if="canUseProject" class="rounded-xl bg-white border border-gray-200 p-4">
        <div class="flex items-center justify-between mb-2">
          <span class="text-sm font-medium text-gray-700">Progreso del wizard</span>
          <span class="text-sm font-semibold text-brand-600">{{ progressPercentage }}%</span>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-2">
          <div
            class="bg-brand-600 h-2 rounded-full transition-all duration-500"
            :style="{ width: `${progressPercentage}%` }"
          ></div>
        </div>
      </div>

      <!-- Wizard Tabs -->
      <ProjectWizardTabs
        v-model:active="activeTab"
        :title="wizardTitle"
        :subtitle="wizardSubtitle"
        :tabs="tabs"
      >
        <!-- TAB 1: Proyecto + info general -->
        <TabGeneral
          v-if="activeTab === 'general'"
          :form="formProyecto"
          :estados="estados"
          :ubicaciones="ubicacionesLocal"
          :proyecto-id="proyectoId"
          @save="handleSaveProyecto"
          @ubicacion-created="handleUbicacionCreated"
        />

        <!-- TAB 2: Políticas de precio -->
        <TabPoliticas
          v-else-if="activeTab === 'politicas'"
          :can-use-project="canUseProject"
          :proyecto-id="proyectoId"
          :proyecto-label="proyectoLabel"
          @save-success="() => markStepCompleted('politicas')"
          @prev="goPrev"
          @next="goNext"
        />

        <!-- TAB 3: Torres + Pisos -->
        <TabTorresPisos
          v-else-if="activeTab === 'torres_pisos'"
          :can-use-project="canUseProject"
          :proyecto-id="proyectoId"
          :proyecto-label="proyectoLabel"
          :estados="estados"
          :torres="torres"
          @save-success="handleTorresPisosSuccess"
          @prev="goPrev"
          @next="goNext"
        />

        <!-- TAB 4: Tipos de apartamento -->
        <TabTiposApartamento
          v-else-if="activeTab === 'tipos_apto'"
          :can-use-project="canUseProject"
          :proyecto-id="proyectoId"
          :proyecto-label="proyectoLabel"
          @save-success="handleTiposSuccess"
          @prev="goPrev"
          @next="goNext"
        />

        <!-- TAB 5: Apartamentos + Locales -->
        <TabInventario
          v-else-if="activeTab === 'inventario'"
          :can-use-project="canUseProject"
          :proyecto-id="proyectoId"
          :torres="torres"
          :tipos-apartamento="tiposApartamento"
          :estados-inmueble="estadosInmueble"
          :apartamentos="apartamentos"
          @save-success="handleInventarioSuccess"
          @prev="goPrev"
          @next="goNext"
        />

        <!-- TAB 6: Parqueaderos -->
        <TabParqueaderos
          v-else-if="activeTab === 'parqueaderos'"
          :can-use-project="canUseProject"
          :apartamentos="apartamentos"
          @save-success="() => markStepCompleted('parqueaderos')"
          @prev="goPrev"
        />
      </ProjectWizardTabs>
    </div>
  </TopBannerLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useForm, Link, router } from '@inertiajs/vue3'
import { useWizardState } from '@/composables/useWizardState'
import { useProjectData } from '@/composables/useProjectData'
import { useGlobalToast } from '@/composables/useToast'
import { useApiCall } from '@/composables/useApiCall'
import { toNull, toNumOrNull, toIntOrNull, toBool } from '@/utils/validation'

// Componentes
import TopBannerLayout from '@/Components/TopBannerLayout.vue'
import PageHeader from '@/Components/PageHeader.vue'
import ProjectWizardTabs from '@/Components/ProjectWizardTabs.vue'
import ToastNotification from '@/Components/ToastNotification.vue'

// Tabs (los crearemos después)
import TabGeneral from './Tabs/TabGeneral.vue'
import TabPoliticas from './Tabs/TabPoliticas.vue'
import TabTorresPisos from './Tabs/TabTorresPisos.vue'
import TabTiposApartamento from './Tabs/TabTiposApartamento.vue'
import TabInventario from './Tabs/TabInventario.vue'
import TabParqueaderos from './Tabs/TabParqueaderos.vue'

/** Props desde backend */
const props = defineProps({
  empleado: { type: Object, default: null },
  estados: { type: Array, default: () => [] },
  ubicaciones: { type: Array, default: () => [] },
  estadosInmueble: { type: Array, default: () => [] },
  proyecto: { type: Object, default: null },
  tiposApartamento: { type: Array, default: () => [] },
})

/** Composables */
const toast = useGlobalToast()
const { post } = useApiCall()

// Estado del wizard
const {
  activeTab,
  proyectoId,
  tabs,
  canUseProject,
  wizardTitle,
  wizardSubtitle,
  progressPercentage,
  canGoNext,
  canGoPrev,
  goNext,
  goPrev,
  markStepCompleted,
  setProyectoId,
  initFromUrl,
  loadProgressFromStorage,
} = useWizardState(props.proyecto?.id_proyecto)

// Datos del proyecto
const {
  torres,
  tiposApartamento,
  apartamentos,
  refreshAll: refreshProjectData,
} = useProjectData(proyectoId)

/** Estado local */
const ubicacionesLocal = ref([...(props.ubicaciones ?? [])])
const proyectoLabel = computed(() =>
  proyectoId.value ? `Proyecto #${proyectoId.value}` : 'Proyecto (nuevo)'
)

/** Formulario del proyecto */
const formProyecto = useForm({
  nombre: '',
  descripcion: '',
  fecha_inicio: '',
  fecha_finalizacion: '',
  presupuesto_inicial: '',
  presupuesto_final: '',
  metros_construidos: '',
  cantidad_locales: '',
  cantidad_apartamentos: '',
  cantidad_parqueaderos_vehiculo: '',
  cantidad_parqueaderos_moto: '',
  estrato: '',
  numero_pisos: '',
  numero_torres: '',
  porcentaje_cuota_inicial_min: '',
  valor_min_separacion: '',
  plazo_cuota_inicial_meses: '',
  plazo_max_separacion_dias: '',
  id_estado: '',
  id_ubicacion: '',
  prima_altura_base: null,
  prima_altura_incremento: null,
  prima_altura_activa: false,
})

/**
 * Construye el payload para guardar el proyecto
 */
function buildProyectoPayload() {
  const f = formProyecto
  return {
    nombre: String(f.nombre || '').trim(),
    id_estado: toIntOrNull(f.id_estado),
    id_ubicacion: toIntOrNull(f.id_ubicacion),
    descripcion: toNull(String(f.descripcion || '').trim()),
    fecha_inicio: toNull(f.fecha_inicio),
    fecha_finalizacion: toNull(f.fecha_finalizacion),
    presupuesto_inicial: toNumOrNull(f.presupuesto_inicial),
    presupuesto_final: toNumOrNull(f.presupuesto_final),
    metros_construidos: toNumOrNull(f.metros_construidos),
    cantidad_locales: toIntOrNull(f.cantidad_locales),
    cantidad_apartamentos: toIntOrNull(f.cantidad_apartamentos),
    cantidad_parqueaderos_vehiculo: toIntOrNull(f.cantidad_parqueaderos_vehiculo),
    cantidad_parqueaderos_moto: toIntOrNull(f.cantidad_parqueaderos_moto),
    estrato: toIntOrNull(f.estrato),
    numero_pisos: toIntOrNull(f.numero_pisos),
    numero_torres: toIntOrNull(f.numero_torres),
    porcentaje_cuota_inicial_min: toNumOrNull(f.porcentaje_cuota_inicial_min),
    valor_min_separacion: toNumOrNull(f.valor_min_separacion),
    plazo_cuota_inicial_meses: toIntOrNull(f.plazo_cuota_inicial_meses),
    plazo_max_separacion_dias: toIntOrNull(f.plazo_max_separacion_dias),
    prima_altura_base: toNumOrNull(f.prima_altura_base),
    prima_altura_incremento: toNumOrNull(f.prima_altura_incremento),
    prima_altura_activa: toBool(f.prima_altura_activa),
  }
}

/**
 * Guarda el proyecto
 */
async function handleSaveProyecto() {
  formProyecto.clearErrors()
  formProyecto.processing = true

  const payload = buildProyectoPayload()
  const result = await post('/proyectos', payload)

  formProyecto.processing = false

  if (!result.success) {
    formProyecto.setError(result.errors)
    toast.error(result.message || 'Error al guardar el proyecto')
    return
  }

  // Éxito
  const newId = result.data?.id_proyecto ?? result.data?.data?.id_proyecto
  if (newId) {
    setProyectoId(newId)
    toast.success('Proyecto creado exitosamente. Puedes continuar con las demás pestañas.')

    // Auto-avanzar a la siguiente pestaña
    setTimeout(() => {
      if (canGoNext.value) {
        goNext()
      }
    }, 1500)
  }
}

/**
 * Maneja la creación de una nueva ubicación
 */
function handleUbicacionCreated(nuevaUbicacion) {
  if (nuevaUbicacion?.id_ubicacion) {
    ubicacionesLocal.value = [nuevaUbicacion, ...ubicacionesLocal.value]
    formProyecto.id_ubicacion = nuevaUbicacion.id_ubicacion
    toast.success('Ubicación creada exitosamente')
  }
}

/**
 * Maneja el éxito al guardar torres/pisos
 */
async function handleTorresPisosSuccess() {
  markStepCompleted('torres_pisos')
  await refreshProjectData()
  toast.success('Torres y pisos guardados exitosamente')
}

/**
 * Maneja el éxito al guardar tipos
 */
async function handleTiposSuccess() {
  markStepCompleted('tipos_apto')
  await refreshProjectData()
  toast.success('Tipos de apartamento guardados exitosamente')
}

/**
 * Maneja el éxito al guardar inventario
 */
async function handleInventarioSuccess() {
  markStepCompleted('inventario')
  await refreshProjectData()
  toast.success('Inventario guardado exitosamente')
}

/** Inicialización */
onMounted(() => {
  initFromUrl()
  loadProgressFromStorage()
})
</script>

<style scoped>
.btn-primary {
  @apply inline-flex items-center gap-2 rounded-xl bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700 transition disabled:opacity-60;
}

.btn-secondary {
  @apply inline-flex items-center gap-2 rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-800 hover:bg-gray-50 transition;
}
</style>
