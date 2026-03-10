@extends('errors.layout')

@section('title', 'Error interno del servidor')

@section('content')
<div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-200">
    <!-- Header -->
    <div class="bg-gradient-to-r from-[#FFEA00] to-[#D1C000] p-8 text-center">
        <div class="w-24 h-24 mx-auto bg-white/20 backdrop-blur-sm rounded-2xl flex items-center justify-center mb-4">
            <svg class="w-12 h-12 text-[#1A1700]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
            </svg>
        </div>
        <h1 class="text-7xl font-bold text-[#1A1700] mb-2">500</h1>
        <h2 class="text-2xl font-semibold text-[#474100]">Error interno del servidor</h2>
    </div>

    <!-- Contenido -->
    <div class="p-8 text-center">
        <p class="text-gray-600 mb-4 text-lg">
            Lo sentimos, algo salió mal en nuestro servidor.
        </p>
        
        <div class="bg-gray-50 rounded-xl p-6 mb-8 border border-gray-200">
            <p class="text-sm text-gray-700 mb-2">
                <span class="font-semibold">🔧 Información para soporte:</span>
            </p>
            <p class="text-xs text-gray-500 bg-white p-3 rounded border border-gray-200 font-mono">
                Error ID: {{ uniqid() }}<br>
                Fecha: {{ now()->format('Y-m-d H:i:s') }}
            </p>
        </div>

        <div class="flex flex-col sm:flex-row gap-4 justify-center mb-6">
            <button onclick="location.reload()" 
                    class="px-6 py-3 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 transition-colors font-semibold border border-gray-300">
                ⟳ Intentar de nuevo
            </button>
            <a href="{{ route('home') }}" 
               class="px-6 py-3 bg-[#1e3a5f] text-white rounded-xl hover:bg-[#2c5282] transition-colors font-semibold">
                Ir al inicio
            </a>
        </div>

        <!-- Contacto -->
        <div class="bg-[#1e3a5f]/5 rounded-lg p-4">
            <p class="text-sm text-gray-700">
                <span class="font-semibold">📞 ¿El problema persiste?</span><br>
                Contacta a soporte técnico: 
                <a href="mailto:soporte@constructora-ayc.com" class="text-[#1e3a5f] hover:underline font-medium">
                    soporte@constructora-ayc.com
                </a>
                <br>
                <span class="text-xs text-gray-500">Menciona el Error ID al contactarnos</span>
            </p>
        </div>
    </div>
</div>
@endsection