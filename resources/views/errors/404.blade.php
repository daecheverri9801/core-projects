@extends('errors.layout')

@section('title', 'Página no encontrada')

@section('content')
<div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-200">
    <!-- Header -->
    <div class="bg-gradient-to-r from-[#FFEA00] to-[#D1C000] p-8 text-center">
        <div class="w-24 h-24 mx-auto bg-white/20 backdrop-blur-sm rounded-2xl flex items-center justify-center mb-4">
            <svg class="w-12 h-12 text-[#1A1700]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12h5l.5-3 3 3L15 7l2 5h4M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
            </svg>
        </div>
        <h1 class="text-7xl font-bold text-[#1A1700] mb-2">404</h1>
        <h2 class="text-2xl font-semibold text-[#474100]">Página no encontrada</h2>
    </div>

    <!-- Contenido -->
    <div class="p-8 text-center">
        <p class="text-gray-600 mb-6 text-lg">
            La página que estás buscando no existe o ha sido movida.
        </p>

        <!-- Buscador simple -->
        <div class="max-w-md mx-auto mb-8">
            <div class="relative">
                <input type="text" 
                       placeholder="Buscar en el sitio..."
                       class="w-full pl-10 pr-4 py-3 rounded-xl border border-gray-300 focus:border-[#1e3a5f] focus:ring focus:ring-[#1e3a5f] focus:ring-opacity-50"
                       id="searchInput"
                       onkeypress="if(event.key==='Enter') window.location.href='/search?q='+this.value">
                <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </div>
        </div>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="javascript:history.back()" 
               class="px-6 py-3 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 transition-colors font-semibold border border-gray-300">
                ← Volver
            </a>
            <a href="{{ route('home') }}" 
               class="px-6 py-3 bg-[#1e3a5f] text-white rounded-xl hover:bg-[#2c5282] transition-colors font-semibold">
                Ir al inicio
            </a>
        </div>
    </div>
</div>
@endsection