<script setup>
defineProps({
  phpVersion: String,
  appEnv: String,
  database: String
})
</script>

<template>
  <div class="p-6">
    <h1 class="text-2xl font-bold mb-4">Core-Projects • Healthcheck</h1>
    <ul class="list-disc ml-6 space-y-1">
      <li>PHP: {{ phpVersion }}</li>
      <li>Entorno: {{ appEnv }}</li>
      <li>Base de datos: {{ database }}</li>
    </ul>
  </div>
</template>