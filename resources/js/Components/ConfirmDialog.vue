<template>
  <teleport to="body">
    <transition name="modal">
      <div v-if="isOpen" class="fixed inset-0 z-[9999] overflow-y-auto" @click.self="cancel">
        <!-- Overlay -->
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm transition-opacity"></div>

        <!-- Modal Container -->
        <div class="flex min-h-full items-center justify-center p-4">
          <div
            class="relative bg-white rounded-2xl shadow-2xl max-w-md w-full transform transition-all"
            @click.stop
          >
            <!-- Icono -->
            <div class="pt-8 pb-4 flex justify-center">
              <div
                class="w-16 h-16 rounded-full flex items-center justify-center"
                :class="iconBgClass"
              >
                <component :is="icon" class="w-9 h-9" :class="iconColorClass" />
              </div>
            </div>

            <!-- Contenido -->
            <div class="px-8 pb-6 text-center">
              <h3 class="text-xl font-bold text-gray-900 mb-2">
                {{ title }}
              </h3>
              <p class="text-sm text-gray-600 leading-relaxed">
                {{ message }}
              </p>
            </div>

            <!-- Botones -->
            <div class="px-8 pb-8 flex gap-3">
              <button
                @click="cancel"
                type="button"
                class="flex-1 px-4 py-3 border border-gray-300 rounded-lg text-gray-700 font-semibold hover:bg-gray-50 transition-colors"
              >
                {{ cancelText }}
              </button>
              <button
                @click="confirm"
                type="button"
                class="flex-1 px-4 py-3 rounded-lg font-semibold transition-all"
                :class="confirmButtonClass"
              >
                {{ confirmText }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </transition>
  </teleport>
</template>

<script setup>
import { computed } from 'vue'
import {
  ExclamationTriangleIcon,
  TrashIcon,
  InformationCircleIcon,
  CheckCircleIcon,
} from '@heroicons/vue/24/outline'

const props = defineProps({
  isOpen: { type: Boolean, default: false },
  title: { type: String, default: '¿Estás seguro?' },
  message: { type: String, default: 'Esta acción no se puede deshacer.' },
  confirmText: { type: String, default: 'Confirmar' },
  cancelText: { type: String, default: 'Cancelar' },
  variant: { type: String, default: 'danger' }, // danger, warning, info, success
})

const emit = defineEmits(['confirm', 'cancel'])

const icon = computed(() => {
  const icons = {
    danger: TrashIcon,
    warning: ExclamationTriangleIcon,
    info: InformationCircleIcon,
    success: CheckCircleIcon,
  }
  return icons[props.variant] || ExclamationTriangleIcon
})

const iconBgClass = computed(() => {
  const classes = {
    danger: 'bg-red-100',
    warning: 'bg-yellow-100',
    info: 'bg-blue-100',
    success: 'bg-green-100',
  }
  return classes[props.variant] || classes.danger
})

const iconColorClass = computed(() => {
  const classes = {
    danger: 'text-red-600',
    warning: 'text-yellow-600',
    info: 'text-blue-600',
    success: 'text-green-600',
  }
  return classes[props.variant] || classes.danger
})

const confirmButtonClass = computed(() => {
  const classes = {
    danger: 'bg-red-600 text-white hover:bg-red-700 hover:shadow-lg',
    warning: 'bg-yellow-500 text-white hover:bg-yellow-600 hover:shadow-lg',
    info: 'bg-blue-600 text-white hover:bg-blue-700 hover:shadow-lg',
    success: 'bg-green-600 text-white hover:bg-green-700 hover:shadow-lg',
  }
  return classes[props.variant] || classes.danger
})

function confirm() {
  emit('confirm')
}

function cancel() {
  emit('cancel')
}
</script>

<style scoped>
.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}

.modal-enter-active .relative,
.modal-leave-active .relative {
  transition:
    transform 0.3s ease,
    opacity 0.3s ease;
}

.modal-enter-from .relative,
.modal-leave-to .relative {
  transform: scale(0.9);
  opacity: 0;
}
</style>
