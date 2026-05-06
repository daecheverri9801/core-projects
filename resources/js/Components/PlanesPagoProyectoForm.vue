<template>
  <AppCard padding="md">
    <SectionHeader
      title="Planes de venta / pago"
      subtitle="Configura hasta 10 planes comerciales para este proyecto."
      icon="CreditCardIcon"
    />

    <div class="mt-5 space-y-4">
      <div
        v-if="planesLocal.length === 0"
        class="rounded-2xl border border-dashed border-gray-300 bg-gray-50 p-6 text-center"
      >
        <p class="text-sm font-semibold text-gray-900">Sin planes configurados</p>
        <p class="mt-1 text-sm text-gray-600">
          Agrega al menos un plan si este proyecto manejará condiciones comerciales propias.
        </p>

        <button
          type="button"
          @click="addPlan"
          class="mt-4 rounded-xl bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700 transition"
        >
          Agregar plan
        </button>
      </div>

      <div
        v-for="(plan, index) in planesLocal"
        :key="plan.uid"
        class="rounded-2xl border border-brand-200/70 bg-white p-4"
      >
        <div class="mb-4 flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
          <div>
            <p class="text-sm font-semibold text-gray-900">Plan {{ index + 1 }}</p>
            <p class="text-xs text-gray-600">
              La separación se entiende incluida dentro de la cuota inicial, saldo contado o precio
              con descuento.
            </p>
          </div>

          <div class="flex items-center gap-2">
            <label class="inline-flex items-center gap-2 text-sm font-semibold text-gray-700">
              <input
                v-model="plan.activo"
                type="checkbox"
                class="h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-600"
              />
              Activo
            </label>

            <button
              type="button"
              @click="removePlan(index)"
              class="rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-xs font-semibold text-red-700 hover:bg-red-100 transition"
            >
              Eliminar
            </button>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
          <FormField label="Código" required :error="planError(index, 'codigo')">
            <TextInput v-model="plan.codigo" maxlength="30" placeholder="Ej: PLAN-01" />
          </FormField>

          <FormField label="Nombre" required :error="planError(index, 'nombre')">
            <TextInput v-model="plan.nombre" maxlength="120" placeholder="Ej: Plan 01" />
          </FormField>

          <FormField label="Tipo de plan" required :error="planError(index, 'tipo_plan')">
            <SelectInput v-model="plan.tipo_plan" @change="normalizarPlanSegunTipo(index)">
              <option v-for="tipo in tiposPlan" :key="tipo.value" :value="tipo.value">
                {{ tipo.label }}
              </option>
            </SelectInput>
          </FormField>

          <FormField label="Orden" :error="planError(index, 'orden')">
            <TextInput v-model="plan.orden" type="number" min="1" max="10" placeholder="1" />
          </FormField>

          <FormField
            label="Valor separación"
            :error="planError(index, 'valor_separacion')"
            hint="COP"
          >
            <TextInput
              v-model="plan.valor_separacion"
              type="number"
              min="0"
              step="0.01"
              placeholder="0"
            />
          </FormField>

          <FormField
            v-if="plan.tipo_plan !== 'pago_total_diferido'"
            label="% cuota inicial"
            :error="planError(index, 'porcentaje_cuota_inicial')"
            hint="0–100"
          >
            <TextInput
              v-model="plan.porcentaje_cuota_inicial"
              type="number"
              min="0"
              max="100"
              step="0.0001"
              placeholder="35"
            />
          </FormField>

          <FormField
            v-if="plan.tipo_plan === 'cuota_inicial_mensual'"
            label="Plazo cuota inicial"
            :error="planError(index, 'plazo_cuota_inicial_meses')"
            hint="Meses"
          >
            <TextInput
              v-model="plan.plazo_cuota_inicial_meses"
              type="number"
              min="1"
              placeholder="36"
            />
          </FormField>

          <FormField
            v-if="plan.tipo_plan === 'cuota_inicial_mensual'"
            label="Frecuencia cuotas"
            :error="planError(index, 'frecuencia_cuota_inicial_meses')"
            hint="Cada cuántos meses"
          >
            <TextInput
              v-model="plan.frecuencia_cuota_inicial_meses"
              type="number"
              min="1"
              placeholder="1"
            />
          </FormField>

          <FormField
            v-if="plan.tipo_plan === 'pago_total_diferido'"
            label="Plazo pago saldo"
            :error="planError(index, 'plazo_pago_total_dias')"
            hint="Días"
          >
            <TextInput
              v-model="plan.plazo_pago_total_dias"
              type="number"
              min="1"
              placeholder="60"
            />
          </FormField>

          <FormField
            v-if="plan.tipo_plan !== 'pago_total_diferido'"
            label="% escritura"
            :error="planError(index, 'porcentaje_escritura')"
            hint="0–100"
          >
            <TextInput
              v-model="plan.porcentaje_escritura"
              type="number"
              min="0"
              max="100"
              step="0.0001"
              placeholder="65"
            />
          </FormField>

          <FormField label="Tipo descuento" :error="planError(index, 'tipo_descuento')">
            <SelectInput v-model="plan.tipo_descuento" @change="normalizarDescuento(index)">
              <option v-for="tipo in tiposDescuento" :key="tipo.value" :value="tipo.value">
                {{ tipo.label }}
              </option>
            </SelectInput>
          </FormField>

          <FormField
            v-if="plan.tipo_descuento !== 'ninguno'"
            label="Valor descuento"
            :error="planError(index, 'valor_descuento')"
            :hint="plan.tipo_descuento === 'porcentaje' ? '%' : 'COP'"
          >
            <TextInput
              v-model="plan.valor_descuento"
              type="number"
              min="0"
              :max="plan.tipo_descuento === 'porcentaje' ? 100 : null"
              step="0.0001"
              placeholder="0"
            />
          </FormField>

          <FormField
            v-if="plan.tipo_descuento !== 'ninguno'"
            label="Base descuento"
            :error="planError(index, 'base_descuento')"
          >
            <SelectInput v-model="plan.base_descuento">
              <option
                v-for="base in basesDescuentoFiltradas(plan)"
                :key="base.value"
                :value="base.value"
              >
                {{ base.label }}
              </option>
            </SelectInput>
          </FormField>

          <div
            v-if="plan.tipo_plan === 'especial_manual'"
            class="md:col-span-2 lg:col-span-4 rounded-2xl border border-amber-200 bg-amber-50 p-4"
          >
            <p class="text-sm font-semibold text-amber-900">Plan especial manual</p>
            <p class="mt-1 text-sm text-amber-800">
              En este plan, durante la venta se permitirá definir manualmente el plazo de cuota
              inicial y el valor de cada cuota. Los demás parámetros quedan definidos desde este
              proyecto.
            </p>
          </div>

          <div class="md:col-span-2 lg:col-span-4">
            <FormField
              label="Beneficio / compromiso comercial"
              :error="planError(index, 'beneficio_comercial')"
              hint='Opcional. Ej: Smart TV 65"'
            >
              <TextArea
                v-model="plan.beneficio_comercial"
                rows="2"
                maxlength="1000"
                placeholder="Describe beneficios comerciales, dotaciones o compromisos adicionales..."
              />
            </FormField>
          </div>
        </div>
      </div>

      <div class="flex justify-end">
        <button
          type="button"
          @click="addPlan"
          :disabled="planesLocal.length >= 10"
          class="rounded-xl border border-brand-300 bg-brand-50 px-4 py-2 text-sm font-semibold text-brand-900 hover:bg-brand-100 transition disabled:opacity-50"
        >
          Agregar otro plan
        </button>
      </div>

      <p v-if="planesLocal.length >= 10" class="text-xs text-gray-500 text-right">
        Máximo 10 planes por proyecto.
      </p>
    </div>
  </AppCard>
</template>

<script setup>
import { ref, watch, nextTick } from 'vue'

import AppCard from '@/Components/AppCard.vue'
import SectionHeader from '@/Components/SectionHeader.vue'
import FormField from '@/Components/FormField.vue'
import TextInput from '@/Components/TextInput.vue'
import TextArea from '@/Components/TextArea.vue'
import SelectInput from '@/Components/SelectInput.vue'

const props = defineProps({
  modelValue: {
    type: Array,
    default: () => [],
  },
  errors: {
    type: Object,
    default: () => ({}),
  },
})

const emit = defineEmits(['update:modelValue'])

const tiposPlan = [
  { value: 'cuota_inicial_mensual', label: 'Cuota inicial mensual' },
  { value: 'cuota_inicial_contado', label: 'Cuota inicial de contado' },
  { value: 'pago_total_diferido', label: 'Pago total diferido' },
  { value: 'especial_manual', label: 'Especial manual' },
]

const tiposDescuento = [
  { value: 'ninguno', label: 'Sin descuento' },
  { value: 'valor_fijo', label: 'Valor fijo' },
  { value: 'porcentaje', label: 'Porcentaje' },
]

const basesDescuento = [
  { value: 'ninguna', label: 'Ninguna' },
  { value: 'precio_total', label: 'Precio total' },
  { value: 'cuota_inicial', label: 'Cuota inicial' },
]

const planesLocal = ref([])
let actualizandoDesdePadre = false

watch(
  () => props.modelValue,
  async (value) => {
    actualizandoDesdePadre = true
    planesLocal.value = normalizarPlanesIniciales(value || [])
    await nextTick()
    actualizandoDesdePadre = false
  },
  { immediate: true, deep: true }
)

watch(
  planesLocal,
  (value) => {
    if (actualizandoDesdePadre) return
    emit('update:modelValue', value)
  },
  { deep: true }
)

function nuevoPlan(overrides = {}) {
  const index = planesLocal.value.length + 1
  const codigo = `PLAN-${String(index).padStart(2, '0')}`

  return {
    uid: crypto?.randomUUID ? crypto.randomUUID() : `${Date.now()}-${Math.random()}`,
    id_plan_pago_proyecto: null,
    codigo,
    nombre: `Plan ${String(index).padStart(2, '0')}`,
    descripcion: '',
    orden: index,
    tipo_plan: 'cuota_inicial_mensual',
    valor_separacion: '',
    porcentaje_cuota_inicial: '',
    plazo_cuota_inicial_meses: '',
    frecuencia_cuota_inicial_meses: 1,
    plazo_pago_total_dias: '',
    porcentaje_escritura: '',
    tipo_descuento: 'ninguno',
    valor_descuento: '',
    base_descuento: 'ninguna',
    beneficio_comercial: '',
    permite_plazo_manual: false,
    permite_cuotas_manuales: false,
    activo: true,
    ...overrides,
  }
}

function normalizarPlanesIniciales(planes) {
  return planes.map((plan, index) =>
    nuevoPlan({
      uid: plan.uid || (crypto?.randomUUID ? crypto.randomUUID() : `${Date.now()}-${index}`),
      id_plan_pago_proyecto: plan.id_plan_pago_proyecto ?? null,
      codigo: plan.codigo ?? `PLAN-${String(index + 1).padStart(2, '0')}`,
      nombre: plan.nombre ?? `Plan ${String(index + 1).padStart(2, '0')}`,
      descripcion: plan.descripcion ?? '',
      orden: plan.orden ?? index + 1,
      tipo_plan: plan.tipo_plan ?? 'cuota_inicial_mensual',
      valor_separacion: plan.valor_separacion ?? '',
      porcentaje_cuota_inicial: plan.porcentaje_cuota_inicial ?? '',
      plazo_cuota_inicial_meses: plan.plazo_cuota_inicial_meses ?? '',
      frecuencia_cuota_inicial_meses: plan.frecuencia_cuota_inicial_meses ?? 1,
      plazo_pago_total_dias: plan.plazo_pago_total_dias ?? '',
      porcentaje_escritura: plan.porcentaje_escritura ?? '',
      tipo_descuento: plan.tipo_descuento ?? 'ninguno',
      valor_descuento: plan.valor_descuento ?? '',
      base_descuento: plan.base_descuento ?? 'ninguna',
      beneficio_comercial: plan.beneficio_comercial ?? '',
      permite_plazo_manual: Boolean(plan.permite_plazo_manual ?? false),
      permite_cuotas_manuales: Boolean(plan.permite_cuotas_manuales ?? false),
      activo: plan.activo ?? true,
    })
  )
}

function addPlan() {
  if (planesLocal.value.length >= 10) return
  planesLocal.value.push(nuevoPlan())
}

function removePlan(index) {
  planesLocal.value.splice(index, 1)

  planesLocal.value = planesLocal.value.map((plan, i) => ({
    ...plan,
    orden: i + 1,
  }))
}

function normalizarPlanSegunTipo(index) {
  const plan = planesLocal.value[index]
  if (!plan) return

  if (plan.tipo_plan === 'cuota_inicial_mensual') {
    plan.permite_plazo_manual = false
    plan.permite_cuotas_manuales = false
    plan.plazo_pago_total_dias = ''
    if (!plan.frecuencia_cuota_inicial_meses) {
      plan.frecuencia_cuota_inicial_meses = 1
    }
  }

  if (plan.tipo_plan === 'cuota_inicial_contado') {
    plan.permite_plazo_manual = false
    plan.permite_cuotas_manuales = false
    plan.plazo_cuota_inicial_meses = ''
    plan.frecuencia_cuota_inicial_meses = 1
    plan.plazo_pago_total_dias = ''
  }

  if (plan.tipo_plan === 'pago_total_diferido') {
    plan.porcentaje_cuota_inicial = ''
    plan.plazo_cuota_inicial_meses = ''
    plan.frecuencia_cuota_inicial_meses = 1
    plan.porcentaje_escritura = 0
    plan.permite_plazo_manual = false
    plan.permite_cuotas_manuales = false

    if (plan.base_descuento === 'cuota_inicial') {
      plan.base_descuento = 'precio_total'
    }
  }

  if (plan.tipo_plan === 'especial_manual') {
    plan.plazo_cuota_inicial_meses = ''
    plan.frecuencia_cuota_inicial_meses = 1
    plan.plazo_pago_total_dias = ''
    plan.permite_plazo_manual = true
    plan.permite_cuotas_manuales = true
  }
}

function normalizarDescuento(index) {
  const plan = planesLocal.value[index]
  if (!plan) return

  if (plan.tipo_descuento === 'ninguno') {
    plan.valor_descuento = ''
    plan.base_descuento = 'ninguna'
    return
  }

  if (plan.base_descuento === 'ninguna') {
    plan.base_descuento = 'precio_total'
  }
}

function basesDescuentoFiltradas(plan) {
  if (plan.tipo_plan === 'pago_total_diferido') {
    return basesDescuento.filter((base) => base.value !== 'cuota_inicial')
  }

  return basesDescuento
}

function planError(index, field) {
  return props.errors?.[`planes_pago.${index}.${field}`] || null
}
</script>
