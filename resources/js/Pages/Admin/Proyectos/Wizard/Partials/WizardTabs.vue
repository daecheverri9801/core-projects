<template>
  <div class="rounded-2xl border border-brand-200/60 bg-white/70 backdrop-blur px-2 py-2">
    <div class="hidden md:flex md:items-center md:gap-1">
      <button
        v-for="t in tabs"
        :key="t.id"
        type="button"
        class="group flex-1 min-w-0 inline-flex items-center justify-center gap-2 rounded-2xl px-3 py-2 text-sm font-semibold transition"
        :class="isActive(t.id)
          ? 'bg-brand-200 border border-brand-300 text-brand-950 shadow-sm'
          : 'text-brand-800 hover:bg-brand-50 border border-transparent'"
        @click="$emit('change', t.id)"
        :disabled="t.needsProject && !proyectoId"
      >
        <span class="truncate">{{ t.label }}</span>
        <span v-if="isActive(t.id)" class="ml-1 h-2 w-2 rounded-full bg-brand-500 shrink-0"></span>
      </button>
    </div>

    <div class="md:hidden flex gap-2 overflow-x-auto no-scrollbar">
      <button
        v-for="t in tabs"
        :key="t.id"
        type="button"
        class="whitespace-nowrap rounded-2xl px-3 py-2 text-sm font-semibold transition border"
        :class="isActive(t.id) ? 'bg-brand-200 border-brand-300 text-brand-950' : 'bg-white border-gray-200 text-gray-800'"
        @click="$emit('change', t.id)"
        :disabled="t.needsProject && !proyectoId"
      >
        {{ t.label }}
      </button>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  active: { type: String, default: '1' },
  proyectoId: { type: [String, Number, null], default: null },
})

defineEmits(['change'])

const tabs = [
  { id: '1', label: '1. Proyecto', needsProject: false },
  { id: '2', label: '2. Políticas', needsProject: true },
  { id: '3', label: '3. Torres', needsProject: true },
  { id: '4', label: '4. Pisos', needsProject: true },
  { id: '5', label: '5. Tipos Apt', needsProject: true },
  { id: '6', label: '6. Inventario', needsProject: true },
  { id: '7', label: '7. Parqueaderos', needsProject: true },
]

function isActive(id) {
  return String(props.active) === String(id)
}
</script>
