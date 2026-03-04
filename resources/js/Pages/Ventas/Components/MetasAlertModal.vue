<script setup>
import { ref, computed, onMounted } from 'vue'
import { ExclamationTriangleIcon, XMarkIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  empleado: { type: Object, default: null },
})

const show = ref(false)
const loading = ref(false)
const metas = ref([])

function todayKey() {
  const d = new Date()
  const y = d.getFullYear()
  const m = String(d.getMonth() + 1).padStart(2, '0')
  const day = String(d.getDate()).padStart(2, '0')
  return `${y}-${m}-${day}`
}

function storageKey() {
  const empId = props.empleado?.id_empleado || '0'
  return `metas_modal_${empId}_${todayKey()}`
}

function mesLabel(mes) {
  const MESES = [
    'Enero',
    'Febrero',
    'Marzo',
    'Abril',
    'Mayo',
    'Junio',
    'Julio',
    'Agosto',
    'Septiembre',
    'Octubre',
    'Noviembre',
    'Diciembre',
  ]
  return MESES[(Number(mes) || 1) - 1] || String(mes || '—')
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
  return `${Math.round(Number(valor) * 100)}%`
}

function tipoLabel(tipo) {
  const t = String(tipo || '').toLowerCase()
  if (t === 'ventas') return 'Ventas ($)'
  if (t === 'unidades') return 'Unidades'
  if (t === 'recaudos') return 'Recaudos ($)'
  return tipo || '—'
}

function endOfCurrentMonth() {
  const now = new Date()
  return new Date(now.getFullYear(), now.getMonth() + 1, 0)
}

function diasRestantesMesActual() {
  const start = new Date()
  start.setHours(0, 0, 0, 0)
  const end = endOfCurrentMonth()
  end.setHours(0, 0, 0, 0)
  const diffMs = end.getTime() - start.getTime()
  return Math.max(0, Math.ceil(diffMs / (1000 * 60 * 60 * 24)))
}

const plazoTexto = computed(() => {
  const d = diasRestantesMesActual()
  if (d === 0) return 'Último día'
  return `${d} día(s) restantes`
})

const metasOrdenadas = computed(() => {
  const arr = Array.isArray(metas.value) ? [...metas.value] : []
  // más urgente: menor cumplimiento
  return arr.sort((a, b) => {
    const ca = Math.min(a.cumplimiento_valor ?? 999, a.cumplimiento_unidades ?? 999)
    const cb = Math.min(b.cumplimiento_valor ?? 999, b.cumplimiento_unidades ?? 999)
    return ca - cb
  })
})

function close() {
  show.value = false
  try {
    localStorage.setItem(storageKey(), '1')
  } catch (e) {}
}

async function loadAndMaybeShow() {
  if (!props.empleado?.id_empleado) return

  try {
    const seen = localStorage.getItem(storageKey())
    if (seen) return
  } catch (e) {
    // si falla localStorage, continuamos
  }

  loading.value = true
  try {
    const res = await fetch('/metas/pendientes-mes-actual', {
      method: 'GET',
      headers: { Accept: 'application/json' },
      credentials: 'same-origin',
    })

    if (!res.ok) {
      loading.value = false
      return
    }

    const data = await res.json()
    metas.value = Array.isArray(data?.metas) ? data.metas : []

    if (metas.value.length > 0) {
      show.value = true
    } else {
      // marcar como visto igual para no consultar en cada navegación del día
      try {
        localStorage.setItem(storageKey(), '1')
      } catch (e) {}
    }
  } catch (e) {
    // silencioso
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  loadAndMaybeShow()
})
</script>

<template>
  <teleport to="body">
    <div v-if="show" class="fixed inset-0 z-50">
      <div class="absolute inset-0 bg-black/40" @click="close" />

      <div class="absolute inset-0 flex items-center justify-center p-4">
        <div
          class="relative w-full max-w-3xl rounded-2xl bg-white shadow-xl border border-gray-200 overflow-hidden"
          role="dialog"
          aria-modal="true"
        >
          <!-- header -->
          <div class="px-5 py-4 border-b border-gray-200 flex items-start justify-between gap-3">
            <div class="min-w-0">
              <div class="flex items-center gap-2">
                <ExclamationTriangleIcon class="w-6 h-6 text-amber-600" />
                <h3 class="text-base sm:text-lg font-extrabold text-gray-900">
                  Metas del mes en curso por completar
                </h3>
              </div>
              <p class="text-xs text-gray-500 mt-1">
                {{ plazoTexto }} 
              </p>
            </div>

            <button
              type="button"
              class="inline-flex items-center justify-center rounded-xl p-2 hover:bg-gray-50 text-gray-600 hover:text-gray-900 transition"
              @click="close"
              title="Cerrar"
            >
              <XMarkIcon class="w-5 h-5" />
            </button>
          </div>

          <!-- body -->
          <div class="max-h-[calc(100vh-13rem)] overflow-y-auto px-5 py-5">
            <div v-if="loading" class="text-sm text-gray-600">Cargando…</div>

            <div v-else class="space-y-3">
              <div
                v-for="m in metasOrdenadas"
                :key="m.id_meta"
                class="rounded-xl border border-amber-200 bg-amber-50 p-4"
              >
                <div class="flex items-start justify-between gap-3">
                  <div class="min-w-0">
                    <p class="text-sm font-extrabold text-gray-900">
                      {{ m.proyecto || 'General' }}
                      <span class="text-xs font-semibold text-gray-600 ml-2">
                        · {{ mesLabel(m.mes) }} / {{ m.ano }}
                      </span>
                    </p>

                    <p class="text-xs text-gray-600 mt-1">
                      Tipo: <span class="font-semibold">{{ tipoLabel(m.tipo) }}</span>
                      <span class="mx-2">•</span>
                      Asignación: <span class="font-semibold">{{ m.empleado || 'General' }}</span>
                    </p>
                  </div>

                  <div class="shrink-0 text-right">
                    <p class="text-xs text-gray-500">Cumplimiento</p>
                    <p class="text-sm font-extrabold text-gray-900">
                      {{ pct(m.cumplimiento_valor) }} · {{ pct(m.cumplimiento_unidades) }}
                    </p>
                  </div>
                </div>

                <div class="mt-3 grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                  <div class="rounded-lg bg-white border border-amber-200 p-3">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">
                      Ventas ($)
                    </p>
                    <p class="mt-1 text-sm text-gray-800">
                      Meta: <span class="font-semibold">{{ formatMoney(m.meta_valor) }}</span>
                      <span class="mx-1">·</span>
                      Real: <span class="font-semibold">{{ formatMoney(m.real_valor) }}</span>
                    </p>
                    <p class="mt-1 text-sm text-gray-900">
                      Falta:
                      <span class="font-extrabold text-amber-700">
                        {{ formatMoney(m.faltante_valor) }}
                      </span>
                    </p>
                  </div>

                  <div class="rounded-lg bg-white border border-amber-200 p-3">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">
                      Unidades
                    </p>
                    <p class="mt-1 text-sm text-gray-800">
                      Meta: <span class="font-semibold">{{ m.meta_unidades }}</span>
                      <span class="mx-1">·</span>
                      Real: <span class="font-semibold">{{ m.real_unidades }}</span>
                    </p>
                    <p class="mt-1 text-sm text-gray-900">
                      Falta:
                      <span class="font-extrabold text-amber-700">
                        {{ m.faltante_unidades }}
                      </span>
                    </p>
                  </div>
                </div>

                <div class="mt-3">
                  <div class="h-2 rounded-full bg-white border border-amber-200 overflow-hidden">
                    <div
                      class="h-full rounded-full bg-gradient-to-r from-amber-500 to-orange-400"
                      :style="{
                        width: `${Math.min(100, Math.round(Math.max(m.cumplimiento_valor ?? 0, m.cumplimiento_unidades ?? 0) * 100))}%`,
                      }"
                    />
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- footer -->
          <div
            class="px-5 py-4 border-t border-gray-200 flex items-center justify-end gap-2 bg-white"
          >
            <button
              type="button"
              @click="close"
              class="inline-flex items-center justify-center px-4 py-2 rounded-lg bg-[#1e3a5f] text-white font-semibold hover:bg-[#2c5282] transition"
            >
              Entendido
            </button>
          </div>
        </div>
      </div>
    </div>
  </teleport>
</template>
