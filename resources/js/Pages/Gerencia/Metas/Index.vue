<script setup>
import { ref, computed } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import GerenciaLayout from '@/Components/GerenciaLayout.vue'

const props = defineProps({
  metas: Array, // metas enriquecidas (con real_* y cumplimiento)
  alertas: Array,
  resumenProyecto: Array,
  resumenAsesor: Array,
  proyectos: Array,
  empleados: Array,
  filtros: Object,
})

const tab = ref('resumen') // 'resumen' | 'gestion'

const filtros = ref({
  ano: props.filtros.ano || new Date().getFullYear(),
  mes_desde: props.filtros.mes_desde || 1,
  mes_hasta: props.filtros.mes_hasta || 12,
  id_proyecto: props.filtros.id_proyecto || '',
  id_empleado: props.filtros.id_empleado || '',
})

const MESES = [
  { value: 1, label: 'Enero' },
  { value: 2, label: 'Febrero' },
  { value: 3, label: 'Marzo' },
  { value: 4, label: 'Abril' },
  { value: 5, label: 'Mayo' },
  { value: 6, label: 'Junio' },
  { value: 7, label: 'Julio' },
  { value: 8, label: 'Agosto' },
  { value: 9, label: 'Septiembre' },
  { value: 10, label: 'Octubre' },
  { value: 11, label: 'Noviembre' },
  { value: 12, label: 'Diciembre' },
]

function mesLabel(m) {
  const n = Number(m)
  return MESES.find((x) => x.value === n)?.label || String(m ?? '—')
}

function aplicarFiltros() {
  router.get('/gerencia/metas', { ...filtros.value }, { preserveState: true, replace: true })
}

// ================== Form para crear meta ==================
const form = ref({
  tipo: '',
  mes: '',
  ano: new Date().getFullYear(),
  meta_valor: 0,
  meta_unidades: 0,
  id_proyecto: '',
  id_empleado: '',
})

function crearMeta() {
  router.post('/gerencia/metas', form.value, {
    onSuccess: () => {
      form.value = {
        tipo: '',
        mes: '',
        ano: new Date().getFullYear(),
        meta_valor: 0,
        meta_unidades: 0,
        id_proyecto: '',
        id_empleado: '',
      }
    },
  })
}

function eliminarMeta(id) {
  if (!confirm('¿Eliminar meta?')) return
  router.delete(`/gerencia/metas/${id}`)
}

function formatMoney(v) {
  return Number(v || 0).toLocaleString('es-CO', {
    style: 'currency',
    currency: 'COP',
    maximumFractionDigits: 0,
  })
}

function pct(valor) {
  if (valor === null || valor === undefined) return '—'
  return `${Math.round(valor * 100)}%`
}

// Para barras
function widthFromCumpl(c) {
  if (c === null || c === undefined) return '0%'
  return `${Math.min(120, Math.round(c * 100))}%`
}

const metasOrdenadas = computed(() =>
  [...props.metas].sort((a, b) => {
    if (a.ano !== b.ano) return a.ano - b.ano
    if (a.mes !== b.mes) return a.mes - b.mes
    return (a.proyecto || '').localeCompare(b.proyecto || '')
  })
)
</script>

<template>
  <GerenciaLayout>
    <Head title="Metas Comerciales" />

    <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="text-3xl font-bold text-slate-100">Metas Comerciales</h1>
        <p class="text-slate-400 text-sm mt-1">
          Seguimiento consolidado de metas vs resultados por proyecto y asesor.
        </p>
      </div>

      <div class="flex gap-2">
        <button
          :class="[
            'px-3 py-1.5 rounded-full text-xs font-semibold border',
            tab === 'resumen'
              ? 'bg-brand-600 border-brand-500 text-white'
              : 'border-slate-700 text-slate-300 hover:bg-slate-800',
          ]"
          @click="tab = 'resumen'"
        >
          Resumen & Alertas
        </button>
        <button
          :class="[
            'px-3 py-1.5 rounded-full text-xs font-semibold border',
            tab === 'gestion'
              ? 'bg-brand-600 border-brand-500 text-white'
              : 'border-slate-700 text-slate-300 hover:bg-slate-800',
          ]"
          @click="tab = 'gestion'"
        >
          Gestión de metas
        </button>
      </div>
    </div>

    <!-- FILTROS -->
    <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-4 mb-6">
      <div class="flex items-center justify-between mb-3">
        <h2 class="text-sm font-semibold text-slate-300 uppercase tracking-wide">
          Filtros de análisis
        </h2>
        <button
          @click="aplicarFiltros"
          class="px-3 py-1.5 text-xs rounded-lg bg-brand-600 text-white hover:bg-brand-500"
        >
          Aplicar filtros
        </button>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-5 gap-4 text-sm">
        <div>
          <label class="text-slate-400 text-xs mb-1 block">Año</label>
          <input
            type="number"
            v-model="filtros.ano"
            class="w-full bg-slate-800 text-slate-100 rounded p-2 border border-slate-700"
          />
        </div>

        <div>
          <label class="text-slate-400 text-xs mb-1 block">Mes desde</label>
          <select
            v-model.number="filtros.mes_desde"
            class="w-full bg-slate-800 text-slate-100 rounded p-2 border border-slate-700"
          >
            <option v-for="m in MESES" :key="m.value" :value="m.value">{{ m.label }}</option>
          </select>
        </div>

        <div>
          <label class="text-slate-400 text-xs mb-1 block">Mes hasta</label>
          <select
            v-model.number="filtros.mes_hasta"
            class="w-full bg-slate-800 text-slate-100 rounded p-2 border border-slate-700"
          >
            <option v-for="m in MESES" :key="m.value" :value="m.value">{{ m.label }}</option>
          </select>
        </div>

        <div>
          <label class="text-slate-400 text-xs mb-1 block">Proyecto</label>
          <select
            v-model="filtros.id_proyecto"
            class="w-full bg-slate-800 text-slate-100 rounded p-2 border border-slate-700"
          >
            <option value="">Todos</option>
            <option v-for="p in proyectos" :key="p.id_proyecto" :value="p.id_proyecto">
              {{ p.nombre }}
            </option>
          </select>
        </div>

        <div>
          <label class="text-slate-400 text-xs mb-1 block">Asesor</label>
          <select
            v-model="filtros.id_empleado"
            class="w-full bg-slate-800 text-slate-100 rounded p-2 border border-slate-700"
          >
            <option value="">Todos</option>
            <option v-for="e in empleados" :key="e.id_empleado" :value="e.id_empleado">
              {{ e.nombre }} {{ e.apellido }}
            </option>
          </select>
        </div>
      </div>
    </div>

    <!-- ====================== TAB RESUMEN ====================== -->
    <div v-if="tab === 'resumen'" class="space-y-6">
      <!-- Resumen por proyecto / asesor -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Por proyecto -->
        <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-5">
          <h2 class="text-sm font-semibold text-slate-300 uppercase tracking-wide mb-3">
            Meta vs Real · Proyectos
          </h2>

          <div v-if="!resumenProyecto.length" class="text-slate-500 text-sm">
            No hay metas registradas para los filtros actuales.
          </div>

          <div v-for="p in resumenProyecto" :key="p.id_proyecto" class="mb-4 last:mb-0">
            <div class="flex items-center justify-between text-xs mb-1">
              <div class="font-semibold text-slate-100">
                {{ p.proyecto || 'General' }}
              </div>
              <div class="text-slate-400">
                {{ pct(p.cumplimiento_valor) }} · {{ pct(p.cumplimiento_unidades) }}
              </div>
            </div>

            <div class="space-y-1">
              <!-- Valor -->
              <div class="flex items-center justify-between text-[11px] text-slate-400">
                <span>Meta $: {{ formatMoney(p.meta_valor) }}</span>
                <span>Real $: {{ formatMoney(p.real_valor) }}</span>
              </div>
              <div class="h-2 rounded-full bg-slate-800 overflow-hidden">
                <div
                  class="h-full rounded-full bg-gradient-to-r from-emerald-500 to-lime-400"
                  :style="{ width: widthFromCumpl(p.cumplimiento_valor) }"
                ></div>
              </div>

              <!-- Unidades -->
              <div class="flex items-center justify-between text-[11px] text-slate-400">
                <span>Meta unidades: {{ p.meta_unidades }}</span>
                <span>Real unidades: {{ p.real_unidades }}</span>
              </div>
              <div class="h-2 rounded-full bg-slate-800 overflow-hidden">
                <div
                  class="h-full rounded-full bg-gradient-to-r from-sky-500 to-cyan-400"
                  :style="{ width: widthFromCumpl(p.cumplimiento_unidades) }"
                ></div>
              </div>
            </div>
          </div>
        </div>

        <!-- Por asesor -->
        <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-5">
          <h2 class="text-sm font-semibold text-slate-300 uppercase tracking-wide mb-3">
            Meta vs Real · Asesores
          </h2>

          <div v-if="!resumenAsesor.length" class="text-slate-500 text-sm">
            No hay metas registradas para los filtros actuales.
          </div>

          <div v-for="a in resumenAsesor" :key="a.id_empleado" class="mb-4 last:mb-0">
            <div class="flex items-center justify-between text-xs mb-1">
              <div class="font-semibold text-slate-100">
                {{ a.empleado || 'General' }}
              </div>
              <div class="text-slate-400">
                {{ pct(a.cumplimiento_valor) }} · {{ pct(a.cumplimiento_unidades) }}
              </div>
            </div>

            <div class="space-y-1">
              <div class="flex items-center justify-between text-[11px] text-slate-400">
                <span>Meta $: {{ formatMoney(a.meta_valor) }}</span>
                <span>Real $: {{ formatMoney(a.real_valor) }}</span>
              </div>
              <div class="h-2 rounded-full bg-slate-800 overflow-hidden">
                <div
                  class="h-full rounded-full bg-gradient-to-r from-fuchsia-500 to-pink-400"
                  :style="{ width: widthFromCumpl(a.cumplimiento_valor) }"
                ></div>
              </div>

              <div class="flex items-center justify-between text-[11px] text-slate-400">
                <span>Meta unidades: {{ a.meta_unidades }}</span>
                <span>Real unidades: {{ a.real_unidades }}</span>
              </div>
              <div class="h-2 rounded-full bg-slate-800 overflow-hidden">
                <div
                  class="h-full rounded-full bg-gradient-to-r from-amber-500 to-orange-400"
                  :style="{ width: widthFromCumpl(a.cumplimiento_unidades) }"
                ></div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Alertas -->
      <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-5">
        <div class="flex items-center justify-between mb-3">
          <h2 class="text-sm font-semibold text-slate-300 uppercase tracking-wide">
            Alertas estratégicas
          </h2>
          <span class="text-xs text-slate-400">
            {{ alertas.length }} alerta(s) para el periodo filtrado
          </span>
        </div>

        <div v-if="!alertas.length" class="text-slate-500 text-sm">
          No hay alertas para las metas en el rango seleccionado.
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
          <div
            v-for="al in alertas"
            :key="al.id_meta"
            class="border border-red-500/40 bg-red-900/20 rounded-xl p-3 text-xs text-red-100"
          >
            <div class="flex items-center justify-between mb-1">
              <div class="font-semibold">
                {{ al.proyecto || 'General' }} ·
                {{ al.empleado || 'Equipo' }}
              </div>
              <div class="text-[11px] text-red-300">{{ al.mes }}/{{ al.ano }}</div>
            </div>
            <div class="space-y-1">
              <div>
                Meta $: {{ formatMoney(al.meta_valor) }} · Real $:
                {{ formatMoney(al.real_valor) }} ·
                {{ pct(al.cumplimiento_valor) }}
              </div>
              <div>
                Meta unidades: {{ al.meta_unidades }} · Real: {{ al.real_unidades }} ·
                {{ pct(al.cumplimiento_unidades) }}
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Tabla detalle metas vs resultados -->
      <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-5">
        <h2 class="text-sm font-semibold text-slate-300 uppercase tracking-wide mb-3">
          Detalle metas vs resultados
        </h2>

        <div class="overflow-x-auto">
          <table class="min-w-full text-xs text-slate-200 border border-slate-800 rounded">
            <thead class="bg-slate-800">
              <tr>
                <th class="p-2 border border-slate-700">Año</th>
                <th class="p-2 border border-slate-700">Mes</th>
                <th class="p-2 border border-slate-700">Proyecto</th>
                <th class="p-2 border border-slate-700">Asesor</th>
                <th class="p-2 border border-slate-700">Meta $</th>
                <th class="p-2 border border-slate-700">Real $</th>
                <th class="p-2 border border-slate-700">% $</th>
                <th class="p-2 border border-slate-700">Meta U</th>
                <th class="p-2 border border-slate-700">Real U</th>
                <th class="p-2 border border-slate-700">% U</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="m in metasOrdenadas" :key="m.id_meta" class="odd:bg-slate-900/60">
                <td class="p-2 border border-slate-800 text-center">{{ m.ano }}</td>
                <td class="p-2 border border-slate-800 text-center">{{ mesLabel(m.mes) }}</td>
                <td class="p-2 border border-slate-800">{{ m.proyecto || '—' }}</td>
                <td class="p-2 border border-slate-800">{{ m.empleado || '—' }}</td>
                <td class="p-2 border border-slate-800 text-right">
                  {{ formatMoney(m.meta_valor) }}
                </td>
                <td class="p-2 border border-slate-800 text-right">
                  {{ formatMoney(m.real_valor) }}
                </td>
                <td class="p-2 border border-slate-800 text-center">
                  {{ pct(m.cumplimiento_valor) }}
                </td>
                <td class="p-2 border border-slate-800 text-center">
                  {{ m.meta_unidades }}
                </td>
                <td class="p-2 border border-slate-800 text-center">
                  {{ m.real_unidades }}
                </td>
                <td class="p-2 border border-slate-800 text-center">
                  {{ pct(m.cumplimiento_unidades) }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- ====================== TAB GESTIÓN ====================== -->
    <div v-else class="space-y-6">
      <!-- Form crear meta -->
      <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-5">
        <h2 class="text-sm font-semibold text-slate-300 uppercase tracking-wide mb-3">
          Crear nueva meta
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
          <div>
            <label class="text-slate-400 text-xs mb-1 block">Tipo de meta</label>
            <select
              v-model="form.tipo"
              class="w-full bg-slate-800 text-slate-100 rounded p-2 border border-slate-700"
            >
              <option value="">Seleccione...</option>
              <option value="ventas">Ventas ($)</option>
              <option value="unidades">Unidades</option>
              <option value="recaudos">Recaudos ($)</option>
            </select>
          </div>

          <div>
            <label class="text-slate-400 text-xs mb-1 block">Proyecto</label>
            <select
              v-model="form.id_proyecto"
              class="w-full bg-slate-800 text-slate-100 rounded p-2 border border-slate-700"
            >
              <option value="">General</option>
              <option v-for="p in proyectos" :key="p.id_proyecto" :value="p.id_proyecto">
                {{ p.nombre }}
              </option>
            </select>
          </div>

          <div>
            <label class="text-slate-400 text-xs mb-1 block">Asesor</label>
            <select
              v-model="form.id_empleado"
              class="w-full bg-slate-800 text-slate-100 rounded p-2 border border-slate-700"
            >
              <option value="">General</option>
              <option v-for="e in empleados" :key="e.id_empleado" :value="e.id_empleado">
                {{ e.nombre }} {{ e.apellido }}
              </option>
            </select>
          </div>

          <div>
            <label class="text-slate-400 text-xs mb-1 block">Mes</label>
            <select
              v-model.number="form.mes"
              class="w-full bg-slate-800 text-slate-100 rounded p-2 border border-slate-700"
            >
              <option value="">Seleccione...</option>
              <option v-for="m in MESES" :key="m.value" :value="m.value">{{ m.label }}</option>
            </select>
          </div>

          <div>
            <label class="text-slate-400 text-xs mb-1 block">Año</label>
            <input
              type="number"
              v-model="form.ano"
              class="w-full bg-slate-800 text-slate-100 rounded p-2 border border-slate-700"
            />
          </div>

          <div>
            <label class="text-slate-400 text-xs mb-1 block">Meta ($)</label>
            <input
              type="number"
              v-model="form.meta_valor"
              class="w-full bg-slate-800 text-slate-100 rounded p-2 border border-slate-700"
            />
          </div>

          <div>
            <label class="text-slate-400 text-xs mb-1 block">Meta unidades</label>
            <input
              type="number"
              v-model="form.meta_unidades"
              class="w-full bg-slate-800 text-slate-100 rounded p-2 border border-slate-700"
            />
          </div>
        </div>

        <button
          @click="crearMeta"
          class="mt-4 px-6 py-2 bg-brand-600 text-white rounded-lg hover:bg-brand-500 text-sm"
        >
          Guardar meta
        </button>
      </div>

      <!-- Tabla metas CRUD -->
      <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-5">
        <h2 class="text-sm font-semibold text-slate-300 uppercase tracking-wide mb-3">
          Metas registradas
        </h2>

        <div class="overflow-x-auto">
          <table class="min-w-full text-xs text-slate-200 border border-slate-800 rounded">
            <thead class="bg-slate-800">
              <tr>
                <th class="p-2 border border-slate-700">Tipo</th>
                <th class="p-2 border border-slate-700">Proyecto</th>
                <th class="p-2 border border-slate-700">Asesor</th>
                <th class="p-2 border border-slate-700">Mes</th>
                <th class="p-2 border border-slate-700">Año</th>
                <th class="p-2 border border-slate-700">Meta $</th>
                <th class="p-2 border border-slate-700">Meta U</th>
                <th class="p-2 border border-slate-700">Acciones</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="m in metasOrdenadas" :key="m.id_meta" class="odd:bg-slate-900/60">
                <td class="p-2 border border-slate-800">{{ m.tipo }}</td>
                <td class="p-2 border border-slate-800">{{ m.proyecto || '—' }}</td>
                <td class="p-2 border border-slate-800">{{ m.empleado || '—' }}</td>
                <td class="p-2 border border-slate-800 text-center">{{ mesLabel(m.mes) }}</td>
                <td class="p-2 border border-slate-800 text-center">{{ m.ano }}</td>
                <td class="p-2 border border-slate-800 text-right">
                  {{ formatMoney(m.meta_valor) }}
                </td>
                <td class="p-2 border border-slate-800 text-center">
                  {{ m.meta_unidades }}
                </td>
                <td class="p-2 border border-slate-800 text-center">
                  <div class="flex items-center justify-center gap-2">
                    <Link
                      :href="`/gerencia/metas/${m.id_meta}/edit`"
                      class="px-2 py-1 text-[11px] rounded bg-slate-700 hover:bg-slate-600"
                    >
                      Editar
                    </Link>
                    <button
                      class="px-2 py-1 text-[11px] rounded bg-red-700 hover:bg-red-600"
                      @click="eliminarMeta(m.id_meta)"
                    >
                      Eliminar
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </GerenciaLayout>
</template>
