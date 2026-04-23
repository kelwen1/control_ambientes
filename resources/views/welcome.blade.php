<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'Gestión de Ambientes') }}</title>

        <!-- Favicon -->
        <link rel="icon" type="image/png" href="{{ asset('images/logos/logo_sena.png') }}">

        <!-- Google Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;0,800&display=swap" rel="stylesheet">

        <!-- CSS Premium -->
        <link rel="stylesheet" href="{{ asset('css/app-premium.css') }}">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-gradient-to-br from-green-50 to-white min-h-screen font-sans antialiased">
        <!-- Header -->
        <header class="w-full border-b-4 border-[#39B54A] bg-white/95 backdrop-blur-sm shadow-soft">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 py-4 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-2 sm:gap-3">
                    <img src="{{ asset('images/logos/logo_sena.png') }}" alt="SENA" class="h-10 sm:h-12 w-auto">
                    <h1 class="text-lg sm:text-xl font-semibold text-[#39B54A]">Gestión de Ambientes</h1>
                </div>
                @if (Route::has('login'))
                    <nav class="flex items-center gap-4 w-full sm:w-auto justify-center sm:justify-end">
                        @auth
                            <a href="{{ route('dashboard') }}" class="px-4 sm:px-5 py-2 border-2 border-[#39B54A] text-[#39B54A] hover:bg-[#39B54A] hover:text-white rounded-xl text-sm font-medium transition-all duration-200">
                                Dashboard
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="px-5 sm:px-6 py-2 bg-[#39B54A] text-white rounded-xl text-sm font-medium hover:bg-[#2d8f3a] hover:shadow-glow transition-all duration-200 shadow-md">
                                Iniciar Sesión
                            </a>
                        @endif
                    </nav>
                @endif
            </div>
        </header>

        <!-- Main Content -->
        <main class="max-w-7xl mx-auto px-4 sm:px-6 py-8 sm:py-12">
            <!-- Hero Section -->
            <section class="text-center mb-12 sm:mb-16">
                <h2 class="text-3xl sm:text-4xl md:text-5xl font-bold mb-4 px-4">Sistema de Gestión de Ambientes</h2>
                <p class="text-lg sm:text-xl text-[#706f6c] dark:text-[#A1A09A] max-w-2xl mx-auto mb-6 sm:mb-8 px-4">
                    Aplicación web para la administración, la reserva y el control de ambientes formativos y académicos.
                </p>
                @if (Route::has('login') && !auth()->check())
                    <a href="{{ route('login') }}" class="btn-primary inline-block px-8 sm:px-10 py-3 sm:py-4 bg-[#39B54A] text-white rounded-xl text-base sm:text-lg font-semibold hover:bg-[#2d8f3a] hover:shadow-glow transition-all duration-200 shadow-md">
                        Acceder al Sistema
                    </a>
                @endif
            </section>

            <!-- Features Section -->
            <section class="mb-12 sm:mb-16">
                <h3 class="text-2xl sm:text-3xl font-bold text-center mb-8 sm:mb-12 px-4">Características Principales</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
                    <!-- Feature 1 -->
                    <div class="card-premium bg-white p-6 rounded-xl border-2 border-[#39B54A] shadow-card hover:shadow-card-hover transition-shadow duration-300">
                        <div class="w-12 h-12 bg-[#39B54A] bg-opacity-20 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-[#39B54A]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <h4 class="text-xl font-semibold mb-2">Programación de ambientes</h4>
                        <p class="text-[#706f6c] dark:text-[#A1A09A]">Sistema completo para reservar salones, consultar disponibilidad y gestionar horarios de clases.</p>
                    </div>

                    <!-- Feature 2 -->
                    <div class="card-premium bg-white p-6 rounded-xl border-2 border-[#39B54A] shadow-card hover:shadow-card-hover transition-shadow duration-300">
                        <div class="w-12 h-12 bg-[#39B54A] bg-opacity-20 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-[#39B54A]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <h4 class="text-xl font-semibold mb-2">Estado en Tiempo Real</h4>
                        <p class="text-[#706f6c] dark:text-[#A1A09A]">Visualización en tiempo real del estado de cada salón: ocupado, disponible o en mantenimiento.</p>
                    </div>

                    <!-- Feature 3 -->
                    <div class="card-premium bg-white p-6 rounded-xl border-2 border-[#39B54A] shadow-card hover:shadow-card-hover transition-shadow duration-300">
                        <div class="w-12 h-12 bg-[#39B54A] bg-opacity-20 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-[#39B54A]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <h4 class="text-xl font-semibold mb-2">Gestión de Equipamiento</h4>
                        <p class="text-[#706f6c] dark:text-[#A1A09A]">Control del equipamiento disponible en cada salón: proyectores, computadoras, pizarras, etc.</p>
                    </div>

                    <!-- Feature 4 -->
                    <div class="card-premium bg-white p-6 rounded-xl border-2 border-[#39B54A] shadow-card hover:shadow-card-hover transition-shadow duration-300">
                        <div class="w-12 h-12 bg-[#39B54A] bg-opacity-20 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-[#39B54A]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                        </div>
                        <h4 class="text-xl font-semibold mb-2">Reportes y Estadísticas</h4>
                        <p class="text-[#706f6c] dark:text-[#A1A09A]">Generación de reportes de uso de salones, ocupación y estadísticas de programación y reservas.</p>
                    </div>

                    <!-- Feature 5 -->
                    <div class="card-premium bg-white p-6 rounded-xl border-2 border-[#39B54A] shadow-card hover:shadow-card-hover transition-shadow duration-300">
                        <div class="w-12 h-12 bg-[#39B54A] bg-opacity-20 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-[#39B54A]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                        </div>
                        <h4 class="text-xl font-semibold mb-2">Seguridad</h4>
                        <p class="text-[#706f6c] dark:text-[#A1A09A]">Sistema seguro con autenticación de usuarios y control de acceso.</p>
                    </div>

                    <!-- Feature 6 -->
                    <div class="card-premium bg-white p-6 rounded-xl border-2 border-[#39B54A] shadow-card hover:shadow-card-hover transition-shadow duration-300">
                        <div class="w-12 h-12 bg-[#39B54A] bg-opacity-20 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-[#39B54A]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                        </div>
                        <h4 class="text-xl font-semibold mb-2">Configuración Flexible</h4>
                        <p class="text-[#706f6c] dark:text-[#A1A09A]">Personalización de salones, horarios, permisos de acceso y reglas de reserva según necesidades.</p>
                    </div>
                </div>
            </section>

            <!-- Technical Specs Section -->
            <section class="bg-gradient-to-r from-[#39B54A] to-green-600 p-6 sm:p-8 rounded-xl shadow-card">
                <h3 class="text-2xl sm:text-3xl font-bold mb-6 sm:mb-8 text-white">Ficha Técnica</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 sm:gap-8">
                    <div>
                        <h4 class="text-xl font-semibold mb-4 text-white">Funcionalidades del Sistema</h4>
                        <ul class="space-y-3 text-white">
                            <li class="flex items-start">
                                <span class="font-medium text-white mr-2">•</span>
                                <span>Gestión y reserva de salones de clases</span>
                            </li>
                            <li class="flex items-start">
                                <span class="font-medium text-[#1b1b18] dark:text-[#EDEDEC] mr-2">•</span>
                                <span>Control de disponibilidad en tiempo real</span>
                            </li>
                            <li class="flex items-start">
                                <span class="font-medium text-[#1b1b18] dark:text-[#EDEDEC] mr-2">•</span>
                                <span>Administración de equipamiento y recursos</span>
                            </li>
                            <li class="flex items-start">
                                <span class="font-medium text-[#1b1b18] dark:text-[#EDEDEC] mr-2">•</span>
                                <span>Calendario interactivo de horarios</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </section>
        </main>

        <!-- Footer -->
        <footer class="footer-premium mt-12 sm:mt-16 border-t-4 border-[#39B54A] bg-gradient-to-r from-[#39B54A] to-green-600 py-6 sm:py-8 relative">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 text-center text-white">
                <p class="text-sm sm:text-base font-semibold px-2">&copy; {{ date('Y') }} Sistema de Gestión de Ambientes - SENA. Todos los derechos reservados.</p>
            </div>
        </footer>
    </body>
</html>
