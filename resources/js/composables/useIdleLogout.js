import { onMounted, onBeforeUnmount, ref } from 'vue'
import { router } from '@inertiajs/vue3'

export function useIdleLogout({ minutes = 30 } = {}) {
  const timeoutMs = minutes * 60 * 1000
  const timer = ref(null)

  const reset = () => {
    if (timer.value) clearTimeout(timer.value)
    timer.value = setTimeout(() => {
      router.post('/logout', {}, { preserveScroll: true })
    }, timeoutMs)
  }

  const events = ['mousemove', 'mousedown', 'keydown', 'scroll', 'touchstart', 'click']

  onMounted(() => {
    reset()
    events.forEach((e) => window.addEventListener(e, reset, { passive: true }))
  })

  onBeforeUnmount(() => {
    if (timer.value) clearTimeout(timer.value)
    events.forEach((e) => window.removeEventListener(e, reset))
  })

  return { reset }
}
