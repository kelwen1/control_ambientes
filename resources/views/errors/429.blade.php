<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Demasiadas solicitudes - SENA</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logos/logo_sena.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: { 'sena-green': '#39B54A' }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50 min-h-screen flex flex-col">
    <!-- Barra superior -->
    <nav class="bg-white border-b-4 border-[#39B54A] shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6">
            <div class="flex justify-between items-center min-h-[4.5rem] sm:min-h-[5.5rem] py-2">
                <div class="flex items-center gap-2 sm:gap-3">
                    <img src="{{ asset('images/logos/logo_sena.png') }}" alt="SENA" class="h-9 sm:h-11 w-auto">
                    <span class="text-base sm:text-xl font-semibold text-[#39B54A] hidden sm:inline">Gestión de Ambientes</span>
                </div>
                <div>
                    <a href="{{ url('/') }}" class="px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 rounded-md transition-colors">Inicio</a>
                    @auth
                        <a href="{{ route('dashboard') }}" class="px-3 py-2 text-sm font-medium bg-[#39B54A] text-white rounded-md hover:bg-[#2d8f3a] transition-colors">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="px-3 py-2 text-sm font-medium bg-[#39B54A] text-white rounded-md hover:bg-[#2d8f3a] transition-colors">Iniciar sesión</a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <main class="flex-1 max-w-7xl mx-auto px-4 sm:px-6 py-6 sm:py-8 w-full flex items-center justify-center">
        <div class="bg-white rounded-xl shadow-lg p-6 sm:p-10 w-full max-w-lg border-l-4 border-amber-500">
            <div class="text-center">
                <div class="mx-auto flex items-center justify-center h-20 w-20 rounded-full bg-amber-50 mb-6">
                    <span class="text-5xl">⏱️</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-800 mb-2">Demasiadas solicitudes</h1>
                <p class="text-gray-600 text-sm sm:text-base mb-2">Error 429</p>
                <p class="text-gray-600 text-sm sm:text-base mb-6">
                    Has realizado muchas peticiones en poco tiempo. Por seguridad, el sistema limita la cantidad de acciones por minuto.
                    Espera un momento e intenta de nuevo.
                </p>
                <div class="flex flex-col sm:flex-row gap-3 justify-center">
                    <a href="javascript:history.back()" class="inline-flex justify-center px-6 py-3 bg-[#39B54A] text-white rounded-lg font-semibold hover:bg-[#2d8f3a] transition-colors shadow-lg">
                        Volver atrás
                    </a>
                    @auth
                        <a href="{{ route('dashboard') }}" class="inline-flex justify-center px-6 py-3 bg-gray-300 text-gray-700 rounded-lg font-semibold hover:bg-gray-400 transition-colors">
                            Ir al Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="inline-flex justify-center px-6 py-3 bg-gray-300 text-gray-700 rounded-lg font-semibold hover:bg-gray-400 transition-colors">
                            Iniciar sesión
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </main>

    <footer class="bg-white border-t border-gray-200 py-4">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 text-center text-gray-500 text-sm">
            Sistema de Control de Ambientes - SENA
        </div>
    </footer>
</body>
</html>
