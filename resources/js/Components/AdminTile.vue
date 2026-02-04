<template>
  <Link
    :href="href"
    class="group relative rounded-2xl border border-brand-300/60 bg-white shadow-sm hover:shadow-md transition overflow-hidden"
  >
    <div
      class="absolute -top-24 -right-24 h-48 w-48 rounded-full bg-brand-600/15 blur-2xl
             opacity-0 group-hover:opacity-100 transition"
    ></div>

    <div class="p-5 flex items-start gap-4">
      <div
        class="rounded-2xl border border-brand-300 bg-brand-200 p-3
               group-hover:bg-brand-300 transition"
      >
        <component :is="iconComponent" class="w-7 h-7 text-brand-900" />
      </div>

      <div class="flex-1">
        <div class="flex justify-between gap-3">
          <div>
            <h3 class="text-base font-semibold text-gray-900">
              {{ title }}
            </h3>
            <p class="mt-1 text-sm text-gray-700">
              {{ description }}
            </p>
          </div>

          <span
            v-if="tag"
            class="h-fit rounded-full border border-brand-300 bg-brand-200
                   px-2.5 py-1 text-xs font-semibold text-brand-950"
          >
            {{ tag }}
          </span>
        </div>

        <div class="mt-4 flex items-center gap-2 text-sm font-semibold text-brand-900">
          Entrar
          <ArrowRightIcon class="w-4 h-4 group-hover:translate-x-0.5 transition" />
        </div>
      </div>
    </div>

    <div class="h-1 w-full bg-brand-200 group-hover:bg-brand-600/60 transition"></div>
  </Link>
</template>

<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import * as HeroIcons from '@heroicons/vue/24/outline'
import { ArrowRightIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  icon: String,
  title: String,
  description: String,
  tag: String,
  href: String,
})

const iconComponent = computed(() => HeroIcons[props.icon] || HeroIcons.Squares2X2Icon)
</script>
