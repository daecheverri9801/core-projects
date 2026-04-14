<script setup>
import VentasLayout from '@/Components/VentasLayout.vue'
import Form from './Form.vue'
import { Link } from '@inertiajs/vue3'
import { BanknotesIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  ventas: Array,
  conceptos: Array,
  medios: Array,
  cuotas: Array,
  empleado: Object,
})
</script>

<template>
  <VentasLayout :empleado="empleado">
    <div class="space-y-6 p-4 sm:p-6">
      <section class="overflow-hidden rounded-3xl border border-gray-200 bg-white shadow-sm">
        <div class="bg-gradient-to-r from-[#FFEA00] via-[#FFF15C] to-[#FFF9B8] px-6 py-6">
          <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex items-center gap-3 text-[#474100]">
              <div
                class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white/70 shadow-sm"
              >
                <BanknotesIcon class="h-6 w-6" />
              </div>
              <div>
                <h1 class="text-2xl font-extrabold tracking-tight sm:text-3xl">Registrar pago</h1>
                <p class="mt-1 text-sm text-[#474100]/80">
                  Registra el pago realizado y adjunta la imagen del comprobante.
                </p>
              </div>
            </div>

            <Link
              :href="route('pagos.index')"
              class="inline-flex items-center justify-center rounded-2xl border border-[#474100]/10 bg-white/80 px-5 py-3 text-sm font-bold text-[#474100] shadow-sm transition hover:bg-white"
            >
              Volver al listado
            </Link>
          </div>
        </div>
      </section>

      <Form
        form-mode="create"
        :ventas="ventas"
        :conceptos="conceptos"
        :medios="medios"
        :cuotas="cuotas"
        :submit-route="route('pagos.store')"
        submit-method="post"
        :back-route="route('pagos.index')"
      />
    </div>
  </VentasLayout>
</template>
