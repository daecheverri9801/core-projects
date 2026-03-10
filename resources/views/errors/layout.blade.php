<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Error') - Constructora A&C</title>
    
    <!-- Tailwind CSS (asumiendo que lo usas) -->
    @vite(['resources/css/app.css'])
    
    <style>
        /* Estilos adicionales para mantener consistencia */
        .bg-gradient-ayc {
            background: linear-gradient(135deg, #FFF9B8 0%, #FFFFFF 100%);
        }
        .bg-yellow-ayc {
            background-color: #FFEA00;
        }
        .bg-yellow-ayc-hover:hover {
            background-color: #D1C000;
        }
        .text-yellow-ayc {
            color: #FFEA00;
        }
        .border-yellow-ayc {
            border-color: #FFEA00;
        }
        .bg-blue-ayc {
            background-color: #1e3a5f;
        }
        .bg-blue-ayc-hover:hover {
            background-color: #2c5282;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-50 to-gray-100 font-sans antialiased min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-2xl">
        @yield('content')
    </div>
</body>
</html>