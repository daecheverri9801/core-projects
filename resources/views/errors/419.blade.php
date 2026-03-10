@extends('errors.layout')

@section('title', 'Sesión expirada')

@section('content')
<div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-200">
    <!-- Header -->
    <div class="bg-gradient-to-r from-[#FFEA00] to-[#D1C000] p-8 text-center">
        <div class="w-24 h-24 mx-auto bg-white/20 backdrop-blur-sm rounded-2xl flex items-center justify-center mb-4">
            <svg class="w-12 h-12 text-[#1A1700]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
        </div>
        <h1 class="text-7xl font-bold text-[#1A1700] mb-2">419</h1>
        <h2 class="text-2xl font-semibold text-[#474100]">Sesión expirada</h2>
    </div>

    <!-- Contenido -->
    <div class="p-8 text-center">
        <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-6 mb-8">
            <p class="text-gray-700 mb-2">
                Tu sesión ha expirado por razones de seguridad después de un período de inactividad.
            </p>
            <p class="text-sm text-gray-600">
                ⏱️ Por favor, inicia sesión nuevamente para continuar.
            </p>
        </div>

        <a href="{{ route('login') }}" 
           class="inline-block px-8 py-4 bg-[#FFEA00] text-[#1A1700] rounded-xl hover:bg-[#D1C000] transition-colors font-semibold text-lg shadow-md">
            Iniciar sesión nuevamente
        </a>

        <p class="mt-6 text-sm text-gray-500">
            Serás redirigido automáticamente en <span id="countdown">5</span> segundos...
        </p>
    </div>
</div>

<script>
    // Redirección automática
    let seconds = 5;
    const countdownEl = document.getElementById('countdown');
    
    const interval = setInterval(() => {
        seconds--;
        if (countdownEl) countdownEl.textContent = seconds;
        
        if (seconds <= 0) {
            clearInterval(interval);
            window.location.href = '{{ route("home") }}';
        }
    }, 1000);
</script>
@endsection