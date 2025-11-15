<template>
  <form @submit.prevent="$emit('submit')" class="space-y-6">
    <!-- Información Básica -->
    <VentasCard>
      <template #header>
        <h3 class="text-lg font-semibold text-gray-900">Información Básica</h3>
      </template>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Nombre -->
        <div class="md:col-span-2">
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Nombre Completo <span class="text-red-500">*</span>
          </label>
          <input
            v-model="form.nombre"
            type="text"
            required
            :disabled="processing"
            class="w-full px-4 py-2.5 border rounded-lg transition focus:ring-2 focus:ring-[#FFEA00] focus:border-transparent disabled:bg-gray-100 disabled:cursor-not-allowed"
            :class="form.errors?.nombre ? 'border-red-500' : 'border-gray-300'"
            placeholder="Ej: Juan Carlos Pérez García"
          />
          <p v-if="form.errors?.nombre" class="text-xs text-red-500 mt-1">
            {{ form.errors.nombre }}
          </p>
        </div>

        <!-- Tipo de Cliente -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Tipo de Cliente <span class="text-red-500">*</span>
          </label>
          <select
            v-model="form.id_tipo_cliente"
            required
            :disabled="processing"
            class="w-full px-4 py-2.5 border rounded-lg transition focus:ring-2 focus:ring-[#FFEA00] focus:border-transparent disabled:bg-gray-100 disabled:cursor-not-allowed"
            :class="form.errors?.id_tipo_cliente ? 'border-red-500' : 'border-gray-300'"
          >
            <option value="">Seleccione...</option>
            <option
              v-for="tipo in tiposCliente"
              :key="tipo.id_tipo_cliente"
              :value="tipo.id_tipo_cliente"
            >
              {{ tipo.tipo_cliente }}
            </option>
          </select>
          <p v-if="form.errors?.id_tipo_cliente" class="text-xs text-red-500 mt-1">
            {{ form.errors.id_tipo_cliente }}
          </p>
        </div>

        <!-- Tipo de Documento -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Tipo de Documento <span class="text-red-500">*</span>
          </label>
          <select
            v-model="form.id_tipo_documento"
            required
            :disabled="processing"
            class="w-full px-4 py-2.5 border rounded-lg transition focus:ring-2 focus:ring-[#FFEA00] focus:border-transparent disabled:bg-gray-100 disabled:cursor-not-allowed"
            :class="form.errors?.id_tipo_documento ? 'border-red-500' : 'border-gray-300'"
          >
            <option value="">Seleccione...</option>
            <option
              v-for="tipo in tiposDocumento"
              :key="tipo.id_tipo_documento"
              :value="tipo.id_tipo_documento"
            >
              {{ tipo.tipo_documento }}
            </option>
          </select>
          <p v-if="form.errors?.id_tipo_documento" class="text-xs text-red-500 mt-1">
            {{ form.errors.id_tipo_documento }}
          </p>
        </div>

        <!-- Documento -->
        <div class="md:col-span-2">
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Número de Documento <span class="text-red-500">*</span>
          </label>
          <input
            v-model="form.documento"
            type="text"
            required
            :disabled="isEdit || processing"
            class="w-full px-4 py-2.5 border rounded-lg transition focus:ring-2 focus:ring-[#FFEA00] focus:border-transparent disabled:bg-gray-100 disabled:cursor-not-allowed"
            :class="form.errors?.documento ? 'border-red-500' : 'border-gray-300'"
            placeholder="Ej: 1053789456"
          />
          <p v-if="isEdit" class="text-xs text-gray-500 mt-1">
            El documento no puede ser modificado
          </p>
          <p v-if="form.errors?.documento" class="text-xs text-red-500 mt-1">
            {{ form.errors.documento }}
          </p>
        </div>
      </div>
    </VentasCard>

    <!-- Información de Contacto -->
    <VentasCard>
      <template #header>
        <h3 class="text-lg font-semibold text-gray-900">Información de Contacto</h3>
      </template>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Dirección -->
        <div class="md:col-span-2">
          <label class="block text-sm font-medium text-gray-700 mb-2">Dirección</label>
          <input
            v-model="form.direccion"
            type="text"
            :disabled="processing"
            class="w-full px-4 py-2.5 border rounded-lg transition focus:ring-2 focus:ring-[#FFEA00] focus:border-transparent disabled:bg-gray-100 disabled:cursor-not-allowed"
            :class="form.errors?.direccion ? 'border-red-500' : 'border-gray-300'"
            placeholder="Ej: Calle 23 #45-67, Manizales"
          />
          <p v-if="form.errors?.direccion" class="text-xs text-red-500 mt-1">
            {{ form.errors.direccion }}
          </p>
        </div>

        <!-- Teléfono -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Teléfono</label>
          <input
            v-model="form.telefono"
            type="tel"
            :disabled="processing"
            class="w-full px-4 py-2.5 border rounded-lg transition focus:ring-2 focus:ring-[#FFEA00] focus:border-transparent disabled:bg-gray-100 disabled:cursor-not-allowed"
            :class="form.errors?.telefono ? 'border-red-500' : 'border-gray-300'"
            placeholder="Ej: 3201234567"
          />
          <p v-if="form.errors?.telefono" class="text-xs text-red-500 mt-1">
            {{ form.errors.telefono }}
          </p>
        </div>

        <!-- Correo -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Correo Electrónico</label>
          <input
            v-model="form.correo"
            type="email"
            :disabled="processing"
            class="w-full px-4 py-2.5 border rounded-lg transition focus:ring-2 focus:ring-[#FFEA00] focus:border-transparent disabled:bg-gray-100 disabled:cursor-not-allowed"
            :class="form.errors?.correo ? 'border-red-500' : 'border-gray-300'"
            placeholder="Ej: cliente@email.com"
          />
          <p v-if="form.errors?.correo" class="text-xs text-red-500 mt-1">
            {{ form.errors.correo }}
          </p>
        </div>
      </div>
    </VentasCard>

    <!-- Botones de Acción -->
    <div class="flex items-center justify-end gap-3 pt-4">
      <Link
        :href="cancelUrl"
        class="px-6 py-2.5 border border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-50 transition"
        :class="{ 'opacity-50 pointer-events-none': processing }"
      >
        Cancelar
      </Link>
      <button
        type="submit"
        :disabled="processing"
        class="px-6 py-2.5 bg-gradient-to-r from-[#FFEA00] to-[#D1C000] text-[#474100] font-semibold rounded-lg hover:shadow-lg transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
      >
        <svg
          v-if="processing"
          class="animate-spin h-5 w-5"
          xmlns="http://www.w3.org/2000/svg"
          fill="none"
          viewBox="0 0 24 24"
        >
          <circle
            class="opacity-25"
            cx="12"
            cy="12"
            r="10"
            stroke="currentColor"
            stroke-width="4"
          ></circle>
          <path
            class="opacity-75"
            fill="currentColor"
            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
          ></path>
        </svg>
        {{ processing ? 'Guardando...' : submitText }}
      </button>
    </div>
  </form>
</template>

<script setup>
import { Link } from '@inertiajs/inertia-vue3'
import VentasCard from './VentasCard.vue'

defineProps({
  form: { type: Object, required: true },
  tiposCliente: { type: Array, default: () => [] },
  tiposDocumento: { type: Array, default: () => [] },
  isEdit: { type: Boolean, default: false },
  submitText: { type: String, default: 'Guardar' },
  cancelUrl: { type: String, default: '/clientes' },
  processing: { type: Boolean, default: false },
})

defineEmits(['submit'])
</script>
