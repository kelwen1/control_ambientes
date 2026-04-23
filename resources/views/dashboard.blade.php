@extends('layouts.app')

@section('title', 'Dashboard Principal')

@section('content')
    <!-- Bienvenida -->
    <div class="mb-6 sm:mb-8 animate-fade-slide-up">
        <h1 class="text-2xl sm:text-3xl font-bold text-[#39B54A] mb-2 tracking-tight">
            Bienvenid@, {{ Auth::user()->nombre }} {{ Auth::user()->apellido }}
        </h1>
        <p class="text-gray-600 text-sm sm:text-base">Panel principal de gestión y administración</p>
    </div>

    @if(!empty($esInstructor))
        <!-- Calendario semanal: contenido principal del instructor -->
        <div class="mb-6 sm:mb-8 animate-fade-slide-up animate-delay-1">
            <div class="card-premium bg-white rounded-xl shadow-card p-4 sm:p-6 border-l-4 border-[#39B54A] hover:shadow-card-hover">
                <div class="mb-4 flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                    <div class="min-w-0 flex-1">
                        <h2 class="text-xl sm:text-2xl font-bold text-[#39B54A] flex items-center gap-2">
                            <span class="text-2xl">📅</span>
                            Mi semana de clases
                        </h2>
                        <p class="text-gray-600 text-sm sm:text-base mt-1">
                            Solo se muestran las sesiones de la semana elegida (una tarjeta por ficha, ambiente y jornada).
                        </p>
                    </div>
                    <div class="flex flex-col items-stretch sm:items-end gap-2 shrink-0">
                        <a href="{{ route('instructor.reporte-reservas-filtro') }}"
                           class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-[#39B54A] text-white rounded-xl hover:bg-[#2d8f3a] transition-all text-sm font-semibold shadow-md whitespace-nowrap">
                            <span>📥</span>
                            Mi reporte PDF
                        </a>
                        @if(!empty($semanaAnterior) && !empty($semanaSiguiente) && !empty($semanaEtiqueta))
                            <div class="flex items-center gap-2 flex-wrap justify-end">
                                <a href="{{ route('dashboard', ['semana' => $semanaAnterior]) }}"
                                   class="px-3 py-1.5 rounded-lg border border-gray-300 text-gray-700 text-sm font-medium hover:bg-gray-50 transition-colors"
                                   title="Semana anterior">←</a>
                                <span class="text-sm font-semibold text-gray-800 text-center px-2">{{ $semanaEtiqueta }}</span>
                                <a href="{{ route('dashboard', ['semana' => $semanaSiguiente]) }}"
                                   class="px-3 py-1.5 rounded-lg border border-gray-300 text-gray-700 text-sm font-medium hover:bg-gray-50 transition-colors"
                                   title="Semana siguiente">→</a>
                            </div>
                        @endif
                    </div>
                </div>

                @php
                    $labelsSemana = [
                        'lunes' => 'Lunes',
                        'martes' => 'Martes',
                        'miercoles' => 'Miércoles',
                        'jueves' => 'Jueves',
                        'viernes' => 'Viernes',
                        'sabado' => 'Sábado',
                        'domingo' => 'Domingo',
                    ];
                @endphp

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    @foreach(($diasSemanaInstructor ?? []) as $dia)
                        @php $itemsDia = ($reservasInstructorPorDia[$dia] ?? collect()); @endphp
                        <div class="flex flex-col rounded-xl border border-gray-200 bg-gray-50 overflow-hidden min-h-[140px]">
                            <a href="{{ route('instructor.detalle-dia', $dia) }}" class="px-3 py-2 bg-gray-100 border-b border-gray-200 flex items-center justify-between hover:bg-gray-200 transition-colors cursor-pointer">
                                <span class="text-xs font-semibold text-gray-700 uppercase tracking-wide">
                                    {{ $labelsSemana[$dia] ?? ucfirst($dia) }}
                                </span>
                                <span class="text-[11px] text-gray-500">{{ $itemsDia->count() }} {{ Str::plural('clase', $itemsDia->count()) }} · Ver detalle →</span>
                            </a>
                            <div class="flex-1 p-3 space-y-2">
                                @if($itemsDia->isEmpty())
                                    <p class="text-gray-400 text-xs text-center mt-4">Sin clases</p>
                                @else
                                    @foreach($itemsDia as $r)
                                        @php $mapaJ = [1=>'manana',2=>'tarde',3=>'noche',4=>'fin_semana']; $k = $mapaJ[$r->id_jornada ?? 0] ?? null; $lab = $k ? config("jornadas.$k.label") : 'N/A'; @endphp
                                        <div class="rounded-lg bg-white border border-gray-200 px-3 py-2 text-xs">
                                            <p class="font-semibold text-gray-900 mb-0.5">
                                                Ficha {{ $r->ficha->num_ficha ?? '—' }}
                                            </p>
                                            <p class="text-gray-600">
                                                Ambiente {{ $r->ambiente->num_ambiente ?? '—' }}
                                            </p>
                                            <p class="text-[#39B54A] font-semibold mt-0.5">{{ $lab }}</p>
                                            @if(!empty($r->resultado_denominacion))
                                                <p class="text-gray-500 mt-1 text-[11px] truncate" title="{{ $r->resultado_denominacion }}">{{ Str::limit($r->resultado_denominacion, 45) }}</p>
                                            @endif
                                            @if(isset($r->sesiones_restantes) && $r->sesiones_total > 0)
                                                <p class="text-gray-600 mt-1.5 font-medium">
                                                    <span class="text-[#39B54A]">{{ $r->sesiones_restantes }}</span> pendientes de impartir
                                                    <span class="text-gray-400">/ {{ $r->sesiones_total }} total</span>
                                                </p>
                                            @endif
                                            @if(!empty($r->clases_agrupadas_en_celda) && (int) $r->clases_agrupadas_en_celda > 1)
                                                <p class="text-[11px] text-gray-500 mt-1">{{ (int) $r->clases_agrupadas_en_celda }} registros este día (misma asignación)</p>
                                            @endif
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    @unless(!empty($esInstructor))
    <!-- Contadores principales (solo para no instructores); «Usuarios activos» solo para administrador -->
    <div class="grid grid-cols-1 sm:grid-cols-2 {{ Auth::user()->isAdmin() ? 'lg:grid-cols-4' : 'lg:grid-cols-3' }} gap-4 sm:gap-6 mb-6 sm:mb-8">
        <!-- Contador: Ambientes Disponibles -->
        <div class="card-premium bg-white rounded-xl shadow-card p-4 sm:p-6 border-l-4 border-[#39B54A] hover:shadow-card-hover animate-fade-slide-up animate-delay-2">
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
        <div class="card-premium bg-white rounded-xl shadow-card p-4 sm:p-6 border-l-4 border-blue-500 hover:shadow-card-hover animate-fade-slide-up animate-delay-2">
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
        <div class="card-premium bg-white rounded-xl shadow-card p-4 sm:p-6 border-l-4 border-orange-500 hover:shadow-card-hover animate-fade-slide-up animate-delay-2">
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

        @if(Auth::user()->isAdmin())
        <!-- Contador: usuarios activos (solo administrador) -->
        <div class="card-premium bg-white rounded-xl shadow-card p-4 sm:p-6 border-l-4 border-purple-500 hover:shadow-card-hover animate-fade-slide-up animate-delay-2">
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
        @endif
    </div>

    <!-- Sección de cards informativas -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 sm:gap-8">
        <!-- Card: Resumen de Ambientes -->
        <div class="card-premium bg-white rounded-xl shadow-card p-6 sm:p-8 hover:shadow-card-hover animate-fade-slide-up animate-delay-3">
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
        <div class="card-premium bg-white rounded-xl shadow-card p-6 sm:p-8 hover:shadow-card-hover animate-fade-slide-up animate-delay-3">
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
        <div class="card-premium bg-white rounded-xl shadow-card p-6 sm:p-8 hover:shadow-card-hover animate-fade-slide-up animate-delay-4">
            <div class="flex items-center justify-between mb-4 sm:mb-6">
                <h2 class="text-xl sm:text-2xl font-bold text-gray-800 flex items-center gap-2">
                    <span class="text-2xl">⚡</span>
                    Accesos Rápidos
                </h2>
            </div>
            <div class="grid grid-cols-2 gap-3 sm:gap-4">
                <a href="{{ route('fichas.index') }}"
                   class="p-4 bg-[#39B54A] bg-opacity-10 hover:bg-opacity-20 rounded-xl transition-all duration-200 text-center group hover:shadow-md">
                    <span class="text-3xl sm:text-4xl block mb-2 group-hover:scale-110 transition-transform duration-200">📋</span>
                    <p class="text-xs sm:text-sm font-semibold text-gray-700">Administrar Fichas</p>
                </a>
                <a href="{{ route('ambientes.index') }}"
                   class="p-4 bg-blue-500 bg-opacity-10 hover:bg-opacity-20 rounded-xl transition-all duration-200 text-center group hover:shadow-md">
                    <span class="text-3xl sm:text-4xl block mb-2 group-hover:scale-110 transition-transform duration-200">📅</span>
                    <p class="text-xs sm:text-sm font-semibold text-gray-700">Gestionar Reservas</p>
                </a>
                @if(auth()->user()->canManageCatalog() || auth()->user()->isCoordinatorOnly())
                <a href="{{ route('ambientes.gestion.index') }}"
                   class="p-4 bg-teal-500 bg-opacity-10 hover:bg-opacity-20 rounded-xl transition-all duration-200 text-center group hover:shadow-md">
                    <span class="text-3xl sm:text-4xl block mb-2 group-hover:scale-110 transition-transform duration-200">🏗️</span>
                    <p class="text-xs sm:text-sm font-semibold text-gray-700">Catálogo de ambientes</p>
                </a>
                @endif
                <a href="{{ route('ajustes.index') }}"
                   class="p-4 bg-purple-500 bg-opacity-10 hover:bg-opacity-20 rounded-xl transition-all duration-200 text-center group hover:shadow-md">
                    <span class="text-3xl sm:text-4xl block mb-2 group-hover:scale-110 transition-transform duration-200">⚙️</span>
                    <p class="text-xs sm:text-sm font-semibold text-gray-700">Ajustes</p>
                </a>
            </div>
        </div>
    </div>
    @endunless
@endsection
