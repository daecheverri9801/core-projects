<template>
  <AppCard padding="md">
    <div class="flex items-start justify-between gap-3">
      <div class="min-w-0">
        <p class="text-sm font-semibold text-gray-900">Pestaña 1 · Creación del proyecto</p>
        <p class="text-sm text-gray-600 mt-1">
          Guarda para generar el proyecto. Luego podrás crear torres, pisos, inventario, etc.
        </p>
      </div>

      <button
        type="button"
        @click="submit"
        :disabled="form.processing"
        class="rounded-xl bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700 transition disabled:opacity-60"
      >
        {{ form.processing ? 'Guardando…' : proyectoId ? 'Guardar cambios' : 'Guardar y continuar' }}
      </button>
    </div>

    <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
      <FormField label="Nombre" required :error="form.errors.nombre">
        <TextInput v-model="form.nombre" placeholder="Ej: Conjunto Residencial Aurora" />
      </FormField>

      <FormField label="Estado" required :error="form.errors.id_estado">
        <SelectInput v-model="form.id_estado">
          <option value="" disabled>Seleccione un estado</option>
          <option v-for="e in estados" :key="e.id_estado" :value="e.id_estado">{{ e.nombre }}</option>
        </SelectInput>
      </FormField>

      <div class="md:col-span-2">
        <FormField label="Descripción" :error="form.errors.descripcion">
          <TextArea v-model="form.descripcion" rows="3" placeholder="Opcional…" />
        </FormField>
      </div>

      <FormField label="Fecha inicio" :error="form.errors.fecha_inicio">
        <TextInput v-model="form.fecha_inicio" type="date" />
      </FormField>

      <FormField label="Fecha finalización" :error="form.errors.fecha_finalizacion">
        <TextInput v-model="form.fecha_finalizacion" type="date" />
      </FormField>

      <FormField label="Ubicación" required :error="form.errors.id_ubicacion" class="md:col-span-2">
        <SelectInput v-model="form.id_ubicacion">
          <option value="" disabled>Seleccione una ubicación</option>
          <option v-for="u in ubicaciones" :key="u.id_ubicacion" :value="u.id_ubicacion">
            {{ u.direccion }}, {{ u?.ciudad?.nombre }}
          </option>
        </SelectInput>
      </FormField>
    </div>

    <div class="mt-6 border-t pt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
      <FormField label="Presupuesto inicial" :error="form.errors.presupuesto_inicial" hint="COP">
        <TextInput v-model="form.presupuesto_inicial" type="number" step="0.01" min="0" />
      </FormField>

      <FormField label="Presupuesto final" :error="form.errors.presupuesto_final" hint="COP">
        <TextInput v-model="form.presupuesto_final" type="number" step="0.01" min="0" />
      </FormField>

      <FormField label="Metros construidos" :error="form.errors.metros_construidos" hint="m²">
        <TextInput v-model="form.metros_construidos" type="number" step="0.01" min="0" />
      </FormField>
    </div>

    <div class="mt-6 border-t pt-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
      <FormField label="Locales" :error="form.errors.cantidad_locales">
        <TextInput v-model="form.cantidad_locales" type="number" min="0" />
      </FormField>
      <FormField label="Apartamentos" :error="form.errors.cantidad_apartamentos">
        <TextInput v-model="form.cantidad_apartamentos" type="number" min="0" />
      </FormField>
      <FormField label="Parq. vehículo" :error="form.errors.cantidad_parqueaderos_vehiculo">
        <TextInput v-model="form.cantidad_parqueaderos_vehiculo" type="number" min="0" />
      </FormField>
      <FormField label="Parq. moto" :error="form.errors.cantidad_parqueaderos_moto">
        <TextInput v-model="form.cantidad_parqueaderos_moto" type="number" min="0" />
      </FormField>
    </div>

    <div class="mt-6 border-t pt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
      <FormField label="Estrato" :error="form.errors.estrato">
        <TextInput v-model="form.estrato" type="number" min="1" max="6" />
      </FormField>
      <FormField label="Número de pisos" :error="form.errors.numero_pisos">
        <TextInput v-model="form.numero_pisos" type="number" min="1" />
      </FormField>
      <FormField label="Número de torres" :error="form.errors.numero_torres">
        <TextInput v-model="form.numero_torres" type="number" min="1" />
      </FormField>
    </div>

    <div class="mt-6 border-t pt-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
      <FormField label="% Cuota inicial mín." :error="form.errors.porcentaje_cuota_inicial_min">
        <TextInput v-model="form.porcentaje_cuota_inicial_min" type="number" min="0" max="100" step="0.01" />
      </FormField>
      <FormField label="Valor mín. separación" :error="form.errors.valor_min_separacion" hint="COP">
        <TextInput v-model="form.valor_min_separacion" type="number" min="0" step="0.01" />
      </FormField>
      <FormField label="Plazo cuota inicial (meses)" :error="form.errors.plazo_cuota_inicial_meses">
        <TextInput v-model="form.plazo_cuota_inicial_meses" type="number" min="1" />
      </FormField>
      <FormField label="Plazo máx separación (días)" :error="form.errors.plazo_max_separacion_dias">
        <TextInput v-model="form.plazo_max_separacion_dias" type="number" min="1" max="3650" />
      </FormField>
    </div>

    <div class="mt-6 border-t pt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
      <FormField label="Prima altura base" :error="form.errors.prima_altura_base" hint="COP">
        <TextInput v-model.number="form.prima_altura_base" type="number" min="0" step="0.01" />
      </FormField>

      <FormField label="Incremento por piso" :error="form.errors.prima_altura_incremento" hint="COP">
        <TextInput v-model.number="form.prima_altura_incremento" type="number" min="0" step="0.01" />
      </FormField>

      <div class="md:col-span-2">
        <Toggle
          v-model="form.prima_altura_activa"
          label="Activar prima altura en este proyecto"
          description="Si está activo, el sistema aplicará el cálculo según piso."
        />
      </div>
    </div>
  </AppCard>
</template>

<script setup>
import { useForm, router } from '@inertiajs/vue3'

import AppCard from '@/Components/AppCard.vue'
import FormField from '@/Components/FormField.vue'
import TextInput from '@/Components/TextInput.vue'
import TextArea from '@/Components/TextArea.vue'
import SelectInput from '@/Components/SelectInput.vue'
import Toggle from '@/Components/Toggle.vue'

const props = defineProps({
  proyecto: Object,
  proyectoId: [String, Number, null],
  estados: Array,
  ubicaciones: Array,
})

const emit = defineEmits(['saved'])

const form = useForm({
  nombre: props.proyecto?.nombre ?? '',
  descripcion: props.proyecto?.descripcion ?? '',
  fecha_inicio: props.proyecto?.fecha_inicio ?? '',
  fecha_finalizacion: props.proyecto?.fecha_finalizacion ?? '',

  presupuesto_inicial: props.proyecto?.presupuesto_inicial ?? '',
  presupuesto_final: props.proyecto?.presupuesto_final ?? '',
  metros_construidos: props.proyecto?.metros_construidos ?? '',

  cantidad_locales: props.proyecto?.cantidad_locales ?? '',
  cantidad_apartamentos: props.proyecto?.cantidad_apartamentos ?? '',
  cantidad_parqueaderos_vehiculo: props.proyecto?.cantidad_parqueaderos_vehiculo ?? '',
  cantidad_parqueaderos_moto: props.proyecto?.cantidad_parqueaderos_moto ?? '',

  estrato: props.proyecto?.estrato ?? '',
  numero_pisos: props.proyecto?.numero_pisos ?? '',
  numero_torres: props.proyecto?.numero_torres ?? '',

  porcentaje_cuota_inicial_min: props.proyecto?.porcentaje_cuota_inicial_min ?? '',
  valor_min_separacion: props.proyecto?.valor_min_separacion ?? '',
  plazo_cuota_inicial_meses: props.proyecto?.plazo_cuota_inicial_meses ?? '',
  plazo_max_separacion_dias: props.proyecto?.plazo_max_separacion_dias ?? '',

  id_estado: props.proyecto?.id_estado ?? '',
  id_ubicacion: props.proyecto?.id_ubicacion ?? '',

  prima_altura_base: props.proyecto?.prima_altura_base ?? null,
  prima_altura_incremento: props.proyecto?.prima_altura_incremento ?? null,
  prima_altura_activa: !!props.proyecto?.prima_altura_activa,
})

function submit() {
  if (!props.proyectoId) {
    form.post('/proyectos/wizard/proyecto', {
      preserveScroll: true,
    })
    return
  }

  form.put(`/proyectos/wizard/proyecto/${props.proyectoId}`, {
    preserveScroll: true,
    onSuccess: () => emit('saved'),
  })
}
</script>
