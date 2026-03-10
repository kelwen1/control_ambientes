@extends('layouts.app')

@section('title', 'Dashboard Principal')

@section('content')
    <!-- Bienvenida -->
    <div class="mb-6 sm:mb-8">
        <h1 class="text-2xl sm:text-3xl font-bold text-[#39B54A] mb-2">
            Bienvenid@, {{ Auth::user()->nombre }} {{ Auth::user()->apellido }}
        </h1>
        <p class="text-gray-600 text-sm sm:text-base">Panel principal de gestión y administración</p>
    </div>

    @if(!empty($esInstructor))
    <!-- Tablero instructor: Mi jornada L-V -->
    <div class="mb-6 sm:mb-8">
        <div class="bg-white rounded-xl shadow-lg p-6 sm:p-8 border-l-4 border-[#39B54A]">
            <h2 class="text-xl sm:text-2xl font-bold text-gray-800 flex items-center gap-2 mb-2">
                <span class="text-2xl">📅</span>
                Mi jornada semanal
            </h2>
            <p class="text-gray-600 text-sm sm:text-base mb-4">Consulta en qué salón te toca, con qué ficha y en qué horario de lunes a viernes.</p>
            <a href="{{ route('instructor.tablero') }}"
               class="inline-flex items-center gap-2 px-5 py-3 bg-[#39B54A] text-white rounded-lg hover:bg-[#2d8f3a] transition-colors font-semibold">
                Ver mi tablero L-V →
            </a>
        </div>
    </div>
    @endif

    <!-- Contadores principales -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-6 sm:mb-8">
        <!-- Contador: Ambientes Disponibles -->
        <div class="bg-white rounded-xl shadow-lg p-4 sm:p-6 border-l-4 border-[#39B54A] hover:shadow-xl transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-xs sm:text-sm font-medium mb-1">Ambientes Totales</p>
                    <p class="text-2xl sm:text-3xl font-bold text-[#39B54A]">{{ $totalAmbientes ?? 0 }}</p>
                </div>
                <div class="bg-[#39B54A] bg-opacity-10 p-3 rounded-full">
                    <span class="text-2xl sm:text-3xl">🏛️</span>
                </div>
            </div>
        </div>

        <!-- Contador: Fichas -->
        <div class="bg-white rounded-xl shadow-lg p-4 sm:p-6 border-l-4 border-blue-500 hover:shadow-xl transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-xs sm:text-sm font-medium mb-1">Total de Fichas</p>
                    <p class="text-2xl sm:text-3xl font-bold text-blue-600">{{ $totalFichas ?? 0 }}</p>
                </div>
                <div class="bg-blue-500 bg-opacity-10 p-3 rounded-full">
                    <span class="text-2xl sm:text-3xl">📋</span>
                </div>
            </div>
        </div>

        <!-- Contador: Ambientes Ocupados -->
        <div class="bg-white rounded-xl shadow-lg p-4 sm:p-6 border-l-4 border-orange-500 hover:shadow-xl transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-xs sm:text-sm font-medium mb-1">Ambientes Ocupados</p>
                    <p class="text-2xl sm:text-3xl font-bold text-orange-600">{{ $ambientesOcupados ?? 0 }}</p>
                </div>
                <div class="bg-orange-500 bg-opacity-10 p-3 rounded-full">
                    <span class="text-2xl sm:text-3xl">🔒</span>
                </div>
            </div>
        </div>

        <!-- Contador: Usuarios Activos -->
        <div class="bg-white rounded-xl shadow-lg p-4 sm:p-6 border-l-4 border-purple-500 hover:shadow-xl transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-xs sm:text-sm font-medium mb-1">Usuarios Activos</p>
                    <p class="text-2xl sm:text-3xl font-bold text-purple-600">{{ $usuariosActivos ?? 0 }}</p>
                </div>
                <div class="bg-purple-500 bg-opacity-10 p-3 rounded-full">
                    <span class="text-2xl sm:text-3xl">👥</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Sección de cards informativas -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 sm:gap-8">
        <!-- Card: Resumen de Ambientes -->
        <div class="bg-white rounded-xl shadow-lg p-6 sm:p-8">
            <div class="flex items-center justify-between mb-4 sm:mb-6">
                <h2 class="text-xl sm:text-2xl font-bold text-gray-800 flex items-center gap-2">
                    <span class="text-2xl">🏛️</span>
                    Resumen de Ambientes
                </h2>
            </div>
            <div class="space-y-4">
                <div class="flex items-center justify-between p-3 bg-green-50 rounded-lg">
                    <span class="text-sm sm:text-base text-gray-700">Disponibles</span>
                    <span class="text-lg sm:text-xl font-semibold text-[#39B54A]">{{ $ambientesDisponibles ?? 0 }}</span>
                </div>
                <div class="flex items-center justify-between p-3 bg-orange-50 rounded-lg">
                    <span class="text-sm sm:text-base text-gray-700">Ocupados</span>
                    <span class="text-lg sm:text-xl font-semibold text-orange-600">{{ $ambientesOcupados ?? 0 }}</span>
                </div>
                <div class="flex items-center justify-between p-3 bg-blue-50 rounded-lg">
                    <span class="text-sm sm:text-base text-gray-700 font-semibold">Total</span>
                    <span class="text-lg sm:text-xl font-bold text-blue-600">{{ $totalAmbientes ?? 0 }}</span>
                </div>
            </div>
        </div>

        <!-- Card: Estadísticas de Fichas -->
        <div class="bg-white rounded-xl shadow-lg p-6 sm:p-8">
            <div class="flex items-center justify-between mb-4 sm:mb-6">
                <h2 class="text-xl sm:text-2xl font-bold text-gray-800 flex items-center gap-2">
                    <span class="text-2xl">📋</span>
                    Estadísticas de Fichas
                </h2>
            </div>
            <div class="space-y-4">
                <div class="flex items-center justify-between p-4 bg-blue-50 rounded-lg">
                    <div>
                        <p class="text-sm sm:text-base text-gray-700 font-medium">Total de Fichas</p>
                        <p class="text-2xl sm:text-3xl font-bold text-blue-600 mt-1">{{ $totalFichas ?? 0 }}</p>
                    </div>
                    <span class="text-3xl sm:text-4xl">📋</span>
                </div>
                <div class="grid grid-cols-2 gap-4 mt-4">
                    <div class="p-3 bg-green-50 rounded-lg text-center">
                        <p class="text-xs sm:text-sm text-gray-600">Activas</p>
                        <p class="text-lg sm:text-xl font-bold text-[#39B54A] mt-1">{{ $fichasActivas ?? 0 }}</p>
                    </div>
                    <div class="p-3 bg-gray-50 rounded-lg text-center">
                        <p class="text-xs sm:text-sm text-gray-600">Inactivas</p>
                        <p class="text-lg sm:text-xl font-bold text-gray-600 mt-1">{{ $fichasInactivas ?? 0 }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card: Accesos Rápidos -->
        <div class="bg-white rounded-xl shadow-lg p-6 sm:p-8">
            <div class="flex items-center justify-between mb-4 sm:mb-6">
                <h2 class="text-xl sm:text-2xl font-bold text-gray-800 flex items-center gap-2">
                    <span class="text-2xl">⚡</span>
                    Accesos Rápidos
                </h2>
            </div>
            <div class="grid grid-cols-2 gap-3 sm:gap-4">
                <a href="{{ route('fichas.index') }}" 
                   class="p-4 bg-[#39B54A] bg-opacity-10 hover:bg-opacity-20 rounded-lg transition-colors text-center group">
                    <span class="text-3xl sm:text-4xl block mb-2 group-hover:scale-110 transition-transform">📋</span>
                    <p class="text-xs sm:text-sm font-semibold text-gray-700">Administrar Fichas</p>
                </a>
                <a href="{{ route('ambientes.index') }}" 
                   class="p-4 bg-blue-500 bg-opacity-10 hover:bg-opacity-20 rounded-lg transition-colors text-center group">
                    <span class="text-3xl sm:text-4xl block mb-2 group-hover:scale-110 transition-transform">🏛️</span>
                    <p class="text-xs sm:text-sm font-semibold text-gray-700">Gestionar Ambientes</p>
                </a>
                <a href="{{ route('ajustes.index') }}" 
                   class="p-4 bg-purple-500 bg-opacity-10 hover:bg-opacity-20 rounded-lg transition-colors text-center group">
                    <span class="text-3xl sm:text-4xl block mb-2 group-hover:scale-110 transition-transform">⚙️</span>
                    <p class="text-xs sm:text-sm font-semibold text-gray-700">Ajustes</p>
                </a>
            </div>
        </div>
    </div>
@endsection
