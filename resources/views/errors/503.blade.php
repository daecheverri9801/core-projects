@extends('errors.layout')

@section('title', 'Mantenimiento programado')

@section('content')
<div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-200">
    <!-- Header -->
    <div class="bg-gradient-to-r from-[#FFEA00] to-[#D1C000] p-8 text-center">
        <div class="w-24 h-24 mx-auto bg-white/20 backdrop-blur-sm rounded-2xl flex items-center justify-center mb-4">
            <svg class="w-12 h-12 text-[#1A1700]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
            </svg>
        </div>
        <h1 class="text-7xl font-bold text-[#1A1700] mb-2">503</h1>
        <h2 class="text-2xl font-semibold text-[#474100]">Mantenimiento programado</h2>
    </div>

    <!-- Contenido -->
    <div class="p-8 text-center">
        <p class="text-gray-600 mb-6 text-lg">
            Estamos realizando mejoras en nuestro sistema para brindarte un mejor servicio.
        </p>

        <!-- Barra de progreso -->
        <div class="max-w-md mx-auto mb-8">
            <div class="flex justify-between text-sm text-gray-600 mb-2">
                <span>Actualizando sistema</span>
                <span id="progress-percent">75%</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-3 mb-4">
                <div id="progress-bar" class="bg-gradient-to-r from-[#FFEA00] to-[#1e3a5f] h-3 rounded-full transition-all duration-500" style="width: 75%"></div>
            </div>
            <p class="text-sm text-gray-500">
                ⏰ Tiempo estimado: <span id="time-remaining">5 minutos</span>
            </p>
        </div>

        <div class="bg-gray-50 rounded-xl p-6 mb-8 border border-gray-200">
            <p class="text-gray-700 mb-2 font-semibold">📢 ¿Qué estamos haciendo?</p>
            <ul class="text-sm text-gray-600 space-y-2 text-left">
                <li>✓ Optimizando rendimiento de la base de datos</li>
                <li>✓ Actualizando módulo de ventas</li>
                <li>✓ Mejorando seguridad del sistema</li>
                <li class="text-[#1e3a5f]">⟳ Implementando nuevas características</li>
            </ul>
        </div>

        <p class="text-sm text-gray-500">
            Disculpa las molestias. Pronto estaremos de vuelta.
        </p>
    </div>
</div>

<script>
    // Simulación de progreso (opcional)
    let progress = 75;
    const bar = document.getElementById('progress-bar');
    const percent = document.getElementById('progress-percent');
    const timeRemaining = document.getElementById('time-remaining');
    
    const interval = setInterval(() => {
        if (progress < 100) {
            progress += 1;
            bar.style.width = progress + '%';
            percent.textContent = progress + '%';
            
            if (progress === 85) timeRemaining.textContent = '3 minutos';
            if (progress === 95) timeRemaining.textContent = '1 minuto';
            if (progress === 100) {
                timeRemaining.textContent = 'Completado';
                clearInterval(interval);
                setTimeout(() => location.reload(), 2000);
            }
        }
    }, 300);
</script>
@endsection