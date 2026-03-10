@extends('errors.layout')

@section('title', 'Error de conexión')

@section('content')
<div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-200">
    <!-- Header -->
    <div class="bg-gradient-to-r from-[#FFEA00] to-[#D1C000] p-8 text-center">
        <div class="w-24 h-24 mx-auto bg-white/20 backdrop-blur-sm rounded-2xl flex items-center justify-center mb-4">
            <svg class="w-12 h-12 text-[#1A1700]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
            </svg>
        </div>
        <h1 class="text-7xl font-bold text-[#1A1700] mb-2">502</h1>
        <h2 class="text-2xl font-semibold text-[#474100]">Error de conexión</h2>
    </div>

    <!-- Contenido -->
    <div class="p-8 text-center">
        <p class="text-gray-600 mb-6 text-lg">
            Estamos teniendo problemas temporales de conexión con nuestros servidores.
        </p>

        <!-- Animación de carga -->
        <div class="flex justify-center items-center space-x-2 mb-8">
            <div class="w-3 h-3 bg-[#FFEA00] rounded-full animate-bounce" style="animation-delay: 0s"></div>
            <div class="w-3 h-3 bg-[#D1C000] rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
            <div class="w-3 h-3 bg-[#1e3a5f] rounded-full animate-bounce" style="animation-delay: 0.4s"></div>
        </div>

        <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-6 mb-8">
            <p class="text-gray-700">
                ⏰ Intentando reconectar automáticamente...
            </p>
            <p class="text-sm text-gray-600 mt-2">
                Esto generalmente se resuelve en pocos segundos.
            </p>
        </div>

        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <button onclick="location.reload()" 
                    class="px-6 py-3 bg-[#FFEA00] text-[#1A1700] rounded-xl hover:bg-[#D1C000] transition-colors font-semibold">
                ⟳ Reintentar ahora
            </button>
            <a href="{{ route('home') }}" 
               class="px-6 py-3 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 transition-colors font-semibold border border-gray-300">
                Ir al inicio
            </a>
        </div>
    </div>
</div>

<script>
    // Auto-retry cada 10 segundos
    setTimeout(() => {
        location.reload();
    }, 10000);
</script>
@endsection