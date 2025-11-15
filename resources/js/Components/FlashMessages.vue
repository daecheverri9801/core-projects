<template>
  <teleport to="body">
    <div class="fixed top-4 right-4 z-[9999] space-y-3 max-w-md w-full px-4">
      <transition-group name="slide-fade">
        <div
          v-for="(message, index) in messages"
          :key="message.id"
          class="rounded-lg shadow-lg border-l-4 p-4 flex items-start gap-3 backdrop-blur-sm"
          :class="getAlertClasses(message.type)"
        >
          <!-- Icono -->
          <div class="flex-shrink-0">
            <component
              :is="getIcon(message.type)"
              class="w-6 h-6"
              :class="getIconColor(message.type)"
            />
          </div>

          <!-- Contenido -->
          <div class="flex-1 min-w-0">
            <p class="text-sm font-semibold" :class="getTitleColor(message.type)">
              {{ getTitle(message.type) }}
            </p>
            <p class="text-sm mt-1" :class="getTextColor(message.type)">
              {{ message.text }}
            </p>
          </div>

          <!-- Botón Cerrar -->
          <button
            @click="removeMessage(index)"
            class="flex-shrink-0 rounded-lg p-1 transition-colors"
            :class="getCloseButtonClass(message.type)"
          >
            <XMarkIcon class="w-5 h-5" />
          </button>
        </div>
      </transition-group>
    </div>
  </teleport>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue'
import { usePage } from '@inertiajs/inertia-vue3'
import {
  CheckCircleIcon,
  ExclamationTriangleIcon,
  InformationCircleIcon,
  XCircleIcon,
  XMarkIcon,
} from '@heroicons/vue/24/outline'

const page = usePage()
const messages = ref([])
let messageId = 0

// Observar cambios en flash messages
watch(
  () => page.props.value.flash,
  (flash) => {
    if (flash?.success) {
      addMessage('success', flash.success)
    }
    if (flash?.error) {
      addMessage('error', flash.error)
    }
    if (flash?.warning) {
      addMessage('warning', flash.warning)
    }
    if (flash?.info) {
      addMessage('info', flash.info)
    }
  },
  { deep: true }
)

// Verificar mensajes al montar
onMounted(() => {
  const flash = page.props.value.flash
  if (flash?.success) addMessage('success', flash.success)
  if (flash?.error) addMessage('error', flash.error)
  if (flash?.warning) addMessage('warning', flash.warning)
  if (flash?.info) addMessage('info', flash.info)
})

function addMessage(type, text) {
  const id = messageId++
  messages.value.push({ id, type, text })

  // Auto-remover después de 5 segundos
  setTimeout(() => {
    const index = messages.value.findIndex((m) => m.id === id)
    if (index !== -1) {
      removeMessage(index)
    }
  }, 5000)
}

function removeMessage(index) {
  messages.value.splice(index, 1)
}

function getAlertClasses(type) {
  const classes = {
    success: 'bg-green-50 border-green-500',
    error: 'bg-red-50 border-red-500',
    warning: 'bg-yellow-50 border-yellow-500',
    info: 'bg-blue-50 border-blue-500',
  }
  return classes[type] || classes.info
}

function getIcon(type) {
  const icons = {
    success: CheckCircleIcon,
    error: XCircleIcon,
    warning: ExclamationTriangleIcon,
    info: InformationCircleIcon,
  }
  return icons[type] || InformationCircleIcon
}

function getIconColor(type) {
  const colors = {
    success: 'text-green-600',
    error: 'text-red-600',
    warning: 'text-yellow-600',
    info: 'text-blue-600',
  }
  return colors[type] || colors.info
}

function getTitleColor(type) {
  const colors = {
    success: 'text-green-900',
    error: 'text-red-900',
    warning: 'text-yellow-900',
    info: 'text-blue-900',
  }
  return colors[type] || colors.info
}

function getTextColor(type) {
  const colors = {
    success: 'text-green-700',
    error: 'text-red-700',
    warning: 'text-yellow-700',
    info: 'text-blue-700',
  }
  return colors[type] || colors.info
}

function getCloseButtonClass(type) {
  const classes = {
    success: 'hover:bg-green-100 text-green-600',
    error: 'hover:bg-red-100 text-red-600',
    warning: 'hover:bg-yellow-100 text-yellow-600',
    info: 'hover:bg-blue-100 text-blue-600',
  }
  return classes[type] || classes.info
}

function getTitle(type) {
  const titles = {
    success: '¡Éxito!',
    error: 'Error',
    warning: 'Advertencia',
    info: 'Información',
  }
  return titles[type] || 'Notificación'
}
</script>

<style scoped>
.slide-fade-enter-active {
  transition: all 0.3s ease-out;
}

.slide-fade-leave-active {
  transition: all 0.2s ease-in;
}

.slide-fade-enter-from {
  transform: translateX(100%);
  opacity: 0;
}

.slide-fade-leave-to {
  transform: translateX(100%);
  opacity: 0;
}

.slide-fade-move {
  transition: transform 0.3s ease;
}
</style>
