@extends('errors.layout')

@section('title', 'Acceso no autorizado')

@section('content')
<div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-200">
    <!-- Header con gradiente amarillo -->
    <div class="bg-gradient-to-r from-[#FFEA00] to-[#D1C000] p-8 text-center">
        <div class="w-24 h-24 mx-auto bg-white/20 backdrop-blur-sm rounded-2xl flex items-center justify-center mb-4">
            <svg class="w-12 h-12 text-[#1A1700]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
            </svg>
        </div>
        <h1 class="text-7xl font-bold text-[#1A1700] mb-2">403</h1>
        <h2 class="text-2xl font-semibold text-[#474100]">Acceso no autorizado</h2>
    </div>

    <!-- Contenido -->
    <div class="p-8 text-center">
        <p class="text-gray-600 mb-8 text-lg">
            Lo sentimos, no tienes permisos para acceder a esta página.
        </p>

        <div class="bg-gray-50 rounded-xl p-6 mb-8 border border-gray-200">
            <p class="text-sm text-gray-700">
                <span class="font-semibold">🔒 Área restringida</span><br>
                Si crees que esto es un error, contacta al administrador del sistema.
            </p>
        </div>

        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="javascript:history.back()" 
               class="px-6 py-3 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 transition-colors font-semibold border border-gray-300">
                ← Volver atrás
            </a>
            <a href="{{ route('home') }}" 
               class="px-6 py-3 bg-[#1e3a5f] text-white rounded-xl hover:bg-[#2c5282] transition-colors font-semibold">
                Ir al inicio
            </a>
        </div>

        <!-- Contacto -->
        <div class="mt-8 pt-6 border-t border-gray-200">
            <p class="text-sm text-gray-500">
                ¿Necesitas ayuda? 
                <a href="mailto:soporte@constructora-ayc.com" class="text-[#1e3a5f] hover:text-[#2c5282] hover:underline font-medium">
                    soporte@constructora-ayc.com
                </a>
            </p>
        </div>
    </div>
</div>
@endsection