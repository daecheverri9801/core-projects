<template>
  <div class="fixed top-4 right-4 z-50 space-y-2 max-w-xs">
    <transition name="fade" mode="out-in">
      <div
        v-if="flash.success"
        key="success"
        class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded shadow"
        role="alert"
      >
        {{ flash.success }}
      </div>
    </transition>

    <transition name="fade" mode="out-in">
      <div
        v-if="flash.error"
        key="error"
        class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded shadow"
        role="alert"
      >
        {{ flash.error }}
      </div>
    </transition>
  </div>
</template>

<script setup>
import { usePage } from '@inertiajs/inertia-vue3';

const page = usePage();
const flash = page.props.value.flash || {};
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>