<template>
  <Link
    :href="href"
    class="flex flex-col items-center justify-center p-6 bg-white rounded-lg shadow hover:shadow-lg transition cursor-pointer"
  >
    <component :is="iconComponent" class="w-12 h-12 text-brand-500 mb-3" />
    <span class="text-lg font-semibold text-gray-700">{{ text }}</span>
  </Link>
</template>

<script setup>
import { defineProps, computed } from 'vue';
import { Link } from '@inertiajs/inertia-vue3';
import * as HeroIcons from '@heroicons/vue/24/outline';

const props = defineProps({
  icon: { type: String, required: true },
  text: { type: String, required: true },
  href: { type: String, required: true },
});

const iconComponent = computed(() => HeroIcons[props.icon] || null);
</script>