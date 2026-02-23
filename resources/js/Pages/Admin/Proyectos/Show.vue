<!-- resources/js/Pages/Admin/Proyectos/Show.vue -->
<template>
  <TopBannerLayout :empleado="empleado">
    <template #title>Detalle del Proyecto</template>

    <div class="space-y-6">
      <!-- Header -->
      <div class="bg-white rounded-2xl border p-4 md:p-6">
        <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
          <div class="min-w-0">
            <p class="text-xs text-gray-600">Proyectos</p>
            <h2 class="text-xl font-semibold text-gray-900 truncate">
              {{ proyecto?.nombre ?? 'Proyecto' }}
            </h2>
            <p class="mt-1 text-sm text-gray-600">
              {{ ubicacionTexto || '—' }}
            </p>
            <p class="mt-1 text-sm text-gray-600">
              {{ descripcionTexto || '—' }}
            </p>
          </div>

          <div class="flex items-center gap-2">
            <Link href="/proyectos" class="btn-secondary">Volver</Link>
            <Link v-if="idProyecto" :href="`/proyectos/${idProyecto}/edit`" class="btn-primary">
              Editar
            </Link>
          </div>
        </div>
      </div>

      <!-- Datos generales -->
      <div class="bg-white rounded-2xl border p-4 md:p-6">
        <h3 class="text-sm font-semibold text-gray-900 mb-4">Información del proyecto</h3>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <InfoItem label="ID" :value="idProyecto || '—'" />
          <InfoItem label="Nombre" :value="proyecto?.nombre ?? '—'" />
          <InfoItem
            label="Estado"
            :value="proyecto?.estado_proyecto?.nombre ?? proyecto?.estado ?? '—'"
          />
          <InfoItem label="Ubicación" :value="ubicacionTexto || '—'" />
          <InfoItem label="Fecha Inicio" :value="fechaInicioTexto || '—'" />
          <InfoItem label="Fecha Finalizacion" :value="fechaFinTexto || '—'" />
          <InfoItem label="Presupuesto Inicial" :value="presupuestoInicialTexto || '—'" />
          <InfoItem label="Presupuesto Final" :value="presupuestoFinalTexto || '—'" />
          <InfoItem label="Metros Construidos" :value="metrosConstruidosTexto || '—'" />
          <InfoItem label="Cantidad Locales" :value="cantidadLocalesTexto || '—'" />
          <InfoItem label="Cantidad Apartamentos" :value="cantidadApartamentosTexto || '—'" />
          <InfoItem
            label="Cantidad Parqueaderos Vehiculo"
            :value="cantidadParqueaderosTexto || '—'"
          />
          <InfoItem
            label="Cantidad Parqueaderos Moto"
            :value="cantidadParqueaderosMotoTexto || '—'"
          />
          <InfoItem label="Estrato" :value="estratoTexto || '—'" />
          <InfoItem label="Número de Pisos" :value="numeroPisosTexto || '—'" />
          <InfoItem label="Número de Torres" :value="numeroTorresTexto || ' —'" />
          <InfoItem label="Prima por altura" :value="primaAlturaTexto || '—'" />
          <InfoItem label="Prima por altura activa" :value="primaAlturaActivaTexto || '—'" />
          <InfoItem label="Porcentaje cuota inicial" :value="porcentajeCuotaInicialTexto || '—'" />
          <InfoItem
            label="Plazo cuota inicial (meses)"
            :value="plazoCuotaInicialMesesTexto || '—'"
          />
          <InfoItem label="Valor mínimo separación" :value="valorMinSeparacionTexto || '—'" />
          <InfoItem
            label="Plazo máximo separación (días)"
            :value="plazoMaxSeparacionDiasTexto || '—'"
          />
          <InfoItem label="Bloques aplicados" :value="bloquesAplicadosTexto || '—'" />
        </div>
      </div>

      <!-- Accesos directos (9 flujos) -->
      <div class="bg-white rounded-2xl border overflow-hidden">
        <div class="px-4 md:px-6 py-4 border-b bg-gray-50">
          <h3 class="text-sm font-semibold text-gray-900">Accesos por flujo</h3>
          <p class="text-xs text-gray-600 mt-1">Accede a los módulos relacionados.</p>
        </div>

        <div class="p-4 md:p-6">
          <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
            <div
              v-for="f in flows"
              :key="f.key"
              class="rounded-2xl border p-4 hover:shadow-sm transition bg-white"
            >
              <div class="min-w-0">
                <p class="text-sm font-semibold text-gray-900 truncate">{{ f.title }}</p>
                <p class="text-xs text-gray-600 mt-1 line-clamp-2">{{ f.subtitle }}</p>
              </div>

              <div class="mt-4 flex items-center gap-2">
                <Link :href="f.href" class="btn-secondary w-full text-center">Ver</Link>
                <Link
                  v-if="f.createHref"
                  :href="f.createHref"
                  class="btn-primary w-full text-center"
                >
                  Crear
                </Link>
              </div>
            </div>
          </div>
        </div>
      </div>     

      <FlashMessages />
    </div>
  </TopBannerLayout>
</template>

<script setup>
import { computed, defineComponent } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'

import TopBannerLayout from '@/Components/TopBannerLayout.vue'
import FlashMessages from '@/Components/FlashMessages.vue'
import InfoItem from '@/Components/InfoItem.vue'

/** Props reactivos de Inertia */
const page = usePage()

const empleado = computed(() => page.props.empleado ?? null)

const proyecto = computed(
  () => page.props.proyecto ?? page.props?.data?.proyecto ?? page.props?.item ?? null
)

const idProyecto = computed(() => proyecto.value?.id_proyecto ?? '')

// const zonasSociales = computed(() => page.props.zonas_sociales ?? [])

/** Ubicación (barrrio + direccion + ciudad) si existe estructura */
const ubicacionTexto = computed(() => {
  const u = proyecto.value?.ubicacion ?? page.props.ubicacion ?? null
  if (!u) return ''
  const barrio = u?.barrio ?? ''
  const direccion = u?.direccion ?? ''
  const ciudad = u?.ciudad?.nombre ?? u?.ciudad ?? ''
  const left = [barrio, direccion].filter(Boolean).join(' ')
  return [left, ciudad].filter(Boolean).join(left && ciudad ? ', ' : '')
})

/** Descripción del proyecto */
const descripcionTexto = computed(() => {
  const d = proyecto.value?.descripcion ?? page.props.descripcion ?? null
  return d || '—'
})

/** Fecha Inicio */
const fechaInicioTexto = computed(() => {
  const f = proyecto.value?.fecha_inicio ?? page.props.fecha_inicio ?? null
  if (!f) return '—'
  try {
    return new Intl.DateTimeFormat('es-CO', {
      year: 'numeric',
      month: 'long',
      day: 'numeric',
    }).format(new Date(f))
  } catch {
    return String(f)
  }
})

/** Fecha Fin */
const fechaFinTexto = computed(() => {
  const f = proyecto.value?.fecha_finalizacion ?? page.props.fecha_finalizacion ?? null
  if (!f) return '—'
  try {
    return new Intl.DateTimeFormat('es-CO', {
      year: 'numeric',
      month: 'long',
      day: 'numeric',
    }).format(new Date(f))
  } catch {
    return String(f)
  }
})

/** Presupuesto Inicial */
const presupuestoInicialTexto = computed(() => {
  const p = proyecto.value?.presupuesto_inicial ?? page.props.presupuesto_inicial ?? null
  if (p === null) return '—'
  try {
    return new Intl.NumberFormat('es-CO', {
      style: 'currency',
      currency: 'COP',
      maximumFractionDigits: 0,
    }).format(Number(p))
  } catch {
    return String(p)
  }
})

/** Presupuesto Final */
const presupuestoFinalTexto = computed(() => {
  const p = proyecto.value?.presupuesto_final ?? page.props.presupuesto_final ?? null
  if (p === null) return '—'
  try {
    return new Intl.NumberFormat('es-CO', {
      style: 'currency',
      currency: 'COP',
      maximumFractionDigits: 0,
    }).format(Number(p))
  } catch {
    return String(p)
  }
})

/** Metros Construidos */
const metrosConstruidosTexto = computed(() => {
  const m = proyecto.value?.metros_construidos ?? page.props.metros_construidos ?? null
  if (m === null) return '—'
  try {
    return (
      new Intl.NumberFormat('es-CO', {
        maximumFractionDigits: 2,
      }).format(Number(m)) + ' m²'
    )
  } catch {
    return String(m)
  }
})

/** Cantidad Locales */
const cantidadLocalesTexto = computed(() => {
  const c = proyecto.value?.cantidad_locales ?? page.props.cantidad_locales ?? null
  if (c === null) return '—'
  try {
    return new Intl.NumberFormat('es-CO', {
      maximumFractionDigits: 0,
    }).format(Number(c))
  } catch {
    return String(c)
  }
})

/** Cantidad Apartamentos */
const cantidadApartamentosTexto = computed(() => {
  const c = proyecto.value?.cantidad_apartamentos ?? page.props.cantidad_apartamentos ?? null
  if (c === null) return '—'
  try {
    return new Intl.NumberFormat('es-CO', {
      maximumFractionDigits: 0,
    }).format(Number(c))
  } catch {
    return String(c)
  }
})

/** Cantidad Parqueaderos Vehiculo */
const cantidadParqueaderosTexto = computed(() => {
  const c =
    proyecto.value?.cantidad_parqueaderos_vehiculo ??
    page.props.cantidad_parqueaderos_vehiculo ??
    null
  if (c === null) return '—'
  try {
    return new Intl.NumberFormat('es-CO', {
      maximumFractionDigits: 0,
    }).format(Number(c))
  } catch {
    return String(c)
  }
})

/** Cantidad Parqueaderos Moto */
const cantidadParqueaderosMotoTexto = computed(() => {
  const c =
    proyecto.value?.cantidad_parqueaderos_moto ?? page.props.cantidad_parqueaderos_moto ?? null
  if (c === null) return '—'
  try {
    return new Intl.NumberFormat('es-CO', {
      maximumFractionDigits: 0,
    }).format(Number(c))
  } catch {
    return String(c)
  }
})

/** Estrato */
const estratoTexto = computed(() => {
  const e = proyecto.value?.estrato ?? page.props.estrato ?? null
  if (e === null) return '—'
  try {
    return new Intl.NumberFormat('es-CO', {
      maximumFractionDigits: 0,
    }).format(Number(e))
  } catch {
    return String(e)
  }
})

/** Numero de Pisos */
const numeroPisosTexto = computed(() => {
  const n = proyecto.value?.numero_pisos ?? page.props.numero_pisos ?? null
  if (n === null) return '—'
  try {
    return new Intl.NumberFormat('es-CO', {
      maximumFractionDigits: 0,
    }).format(Number(n))
  } catch {
    return String(n)
  }
})

/** Numero de Torres */
const numeroTorresTexto = computed(() => {
  const n = proyecto.value?.numero_torres ?? page.props.numero_torres ?? null
  if (n === null) return '—'
  try {
    return new Intl.NumberFormat('es-CO', {
      maximumFractionDigits: 0,
    }).format(Number(n))
  } catch {
    return String(n)
  }
})

/** Prima Altura */
const primaAlturaTexto = computed(() => {
  const p = proyecto.value?.prima_altura_incremento ?? page.props.prima_altura ?? null
  if (p === null) return '—'
  try {
    return new Intl.NumberFormat('es-CO', {
      style: 'currency',
      currency: 'COP',
      maximumFractionDigits: 2,
    }).format(Number(p))
  } catch {
    return String(p)
  }
})

/** Prima Altura Activa */
const primaAlturaActivaTexto = computed(() => {
  const p = proyecto.value?.prima_altura_activa ?? page.props.prima_altura_activa ?? null
  if (p === null) return '—'
  try {
    return p ? 'Sí' : 'No'
  } catch {
    return String(p)
  }
})

/** Porcentaje Cuota inicial */
const porcentajeCuotaInicialTexto = computed(() => {
  const p =
    proyecto.value?.porcentaje_cuota_inicial_min ?? page.props.porcentaje_cuota_inicial ?? null
  if (p === null) return '—'
  try {
    return new Intl.NumberFormat('es-CO', {
      style: 'percent',
      maximumFractionDigits: 0,
    }).format(Number(p))
  } catch {
    return String(p)
  }
})

/** PLazo Cuota Inicial Meses */
const plazoCuotaInicialMesesTexto = computed(() => {
  const p =
    proyecto.value?.plazo_cuota_inicial_meses ?? page.props.plazo_cuota_inicial_meses ?? null
  if (p === null) return '—'
  try {
    return new Intl.NumberFormat('es-CO', {
      maximumFractionDigits: 0,
    }).format(Number(p))
  } catch {
    return String(p)
  }
})

/** Valor Min Separacion */
const valorMinSeparacionTexto = computed(() => {
  const v = proyecto.value?.valor_min_separacion ?? page.props.valor_min_separacion ?? null
  if (v === null) return '—'
  try {
    return new Intl.NumberFormat('es-CO', {
      style: 'currency',
      currency: 'COP',
      maximumFractionDigits: 0,
    }).format(Number(v))
  } catch {
    return String(v)
  }
})

/** Plazo Max Separacion Dias */
const plazoMaxSeparacionDiasTexto = computed(() => {
  const p =
    proyecto.value?.plazo_max_separacion_dias ?? page.props.plazo_max_separacion_dias ?? null
  if (p === null) return '—'
  try {
    return new Intl.NumberFormat('es-CO', {
      maximumFractionDigits: 0,
    }).format(Number(p))
  } catch {
    return String(p)
  }
})

/** Bloques Aplicados */
const bloquesAplicadosTexto = computed(() => {
  const b = proyecto.value?.bloques_aplicados ?? page.props.bloques_aplicados ?? null
  if (b === null) return '—'
  try {
    return new Intl.NumberFormat('es-CO', {
      maximumFractionDigits: 0,
    }).format(Number(b))
  } catch {
    return String(b)
  }
})



/** Helper: agrega ?proyecto=ID si hay id */
function withProyecto(path) {
  const id = idProyecto.value
  if (!id) return path
  const sep = path.includes('?') ? '&' : '?'
  return `${path}${sep}proyecto=${encodeURIComponent(id)}`
}

/** Componentes locales compatibles con <script setup> */
const flows = computed(() => [
  {
    key: 'politicas',
    title: 'Políticas',
    subtitle: 'Políticas vigentes y escalonamientos.',
    href: withProyecto('/politicas-precio-proyecto'),
    createHref: withProyecto('/politicas-precio-proyecto/crear'),
  },
  {
    key: 'torres',
    title: 'Torres',
    subtitle: 'Torres asociadas al proyecto.',
    href: withProyecto('/admin/torres'),
    createHref: withProyecto('/admin/torres/create'),
  },
  {
    key: 'pisos-torre',
    title: 'Pisos por torre',
    subtitle: 'Niveles configurados por torre.',
    href: withProyecto('/pisos-torre'),
    createHref: withProyecto('/pisos-torre/create'),
  },
  {
    key: 'tipos-apartamento',
    title: 'Tipos de apartamento',
    subtitle: 'Administrar tipos asociados al proyecto.',
    href: withProyecto('/tipos-apartamento'),
    createHref: withProyecto('/tipos-apartamento/create'),
  },
  {
    key: 'apartamentos',
    title: 'Apartamentos',
    subtitle: 'Inventario de apartamentos y valores.',
    href: withProyecto('/apartamentos'),
    createHref: withProyecto('/admin/apartamentos/create'),
  },
  {
    key: 'locales',
    title: 'Locales',
    subtitle: 'Inventario de locales por torre/piso.',
    href: withProyecto('/locales'),
    createHref: withProyecto('/locales/create'),
  },
  {
    key: 'parqueaderos',
    title: 'Parqueaderos',
    subtitle: 'Crear/asignar parqueaderos a apartamentos.',
    href: withProyecto('/parqueaderos'),
    createHref: withProyecto('/parqueaderos/create'),
  },
  {
    key: 'zonas-sociales',
    title: 'Zonas sociales',
    subtitle: 'Registrar y administrar zonas del proyecto.',
    href: withProyecto('/zonas-sociales'),
    createHref: withProyecto('/zonas-sociales/create'),
  },
])
</script>

<style scoped>
.btn-primary {
  @apply inline-flex items-center justify-center gap-2 px-4 py-2 rounded-xl bg-brand-600 text-white hover:bg-brand-700 disabled:opacity-50 transition;
}
.btn-secondary {
  @apply inline-flex items-center justify-center gap-2 px-4 py-2 rounded-xl border border-gray-300 bg-white text-gray-800 hover:bg-gray-50 transition disabled:opacity-50;
}
</style>
