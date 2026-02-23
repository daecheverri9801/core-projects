<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import GerenciaLayout from '@/Components/GerenciaLayout.vue'

const props = defineProps({
  meta: Object,
  proyectos: Array,
  empleados: Array,
})

const form = useForm({
  tipo: props.meta.tipo,
  mes: props.meta.mes,
  ano: props.meta.ano,
  meta_valor: props.meta.meta_valor,
  meta_unidades: props.meta.meta_unidades,
  id_proyecto: props.meta.id_proyecto || '',
  id_empleado: props.meta.id_empleado || '',
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

form.mes = Number(form.mes || 1)

function submit() {
  form.put(`/gerencia/metas/${props.meta.id_meta}`)
}
</script>

<template>
  <GerenciaLayout>
    <Head title="Editar Meta" />

    <div class="flex items-center justify-between mb-6">
      <h1 class="text-2xl font-bold text-slate-100">Editar Meta</h1>
      <Link href="/gerencia/metas" class="text-sm text-slate-300 hover:underline">
        ← Volver a Metas
      </Link>
    </div>

    <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-6">
      <form @submit.prevent="submit" class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
          <label class="text-slate-400 text-sm mb-1 block">Tipo de meta</label>
          <select v-model="form.tipo" class="w-full bg-slate-800 text-slate-100 rounded p-2">
            <option value="ventas">Ventas ($)</option>
            <option value="unidades">Unidades</option>
            <option value="recaudos">Recaudos ($)</option>
          </select>
        </div>

        <div>
          <label class="text-slate-400 text-sm mb-1 block">Proyecto</label>
          <select v-model="form.id_proyecto" class="w-full bg-slate-800 text-slate-100 rounded p-2">
            <option value="">General</option>
            <option v-for="p in proyectos" :key="p.id_proyecto" :value="p.id_proyecto">
              {{ p.nombre }}
            </option>
          </select>
        </div>

        <div>
          <label class="text-slate-400 text-sm mb-1 block">Asesor</label>
          <select v-model="form.id_empleado" class="w-full bg-slate-800 text-slate-100 rounded p-2">
            <option value="">General</option>
            <option v-for="e in empleados" :key="e.id_empleado" :value="e.id_empleado">
              {{ e.nombre }} {{ e.apellido }}
            </option>
          </select>
        </div>

        <div>
          <label class="text-slate-400 text-sm mb-1 block">Mes</label>
          <select v-model.number="form.mes" class="w-full bg-slate-800 text-slate-100 rounded p-2">
            <option v-for="m in MESES" :key="m.value" :value="m.value">
              {{ m.label }}
            </option>
          </select>
        </div>

        <div>
          <label class="text-slate-400 text-sm mb-1 block">Año</label>
          <input
            type="number"
            v-model="form.ano"
            class="w-full bg-slate-800 text-slate-100 rounded p-2"
          />
        </div>

        <div>
          <label class="text-slate-400 text-sm mb-1 block">Meta ($)</label>
          <input
            type="number"
            v-model="form.meta_valor"
            class="w-full bg-slate-800 text-slate-100 rounded p-2"
          />
        </div>

        <div>
          <label class="text-slate-400 text-sm mb-1 block">Meta unidades</label>
          <input
            type="number"
            v-model="form.meta_unidades"
            class="w-full bg-slate-800 text-slate-100 rounded p-2"
          />
        </div>

        <div class="md:col-span-3 flex justify-end mt-4">
          <button
            type="submit"
            :disabled="form.processing"
            class="px-6 py-2 bg-brand-700 text-white rounded-lg hover:bg-brand-600 disabled:opacity-50"
          >
            Guardar cambios
          </button>
        </div>
      </form>
    </div>
  </GerenciaLayout>
</template>
