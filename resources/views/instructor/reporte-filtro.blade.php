@extends('layouts.app')

@section('title', 'Descargar reporte de reservas')

@section('content')
    <div class="w-full max-w-2xl mx-auto">
    <div class="mb-6 sm:mb-8 text-center">
        <h1 class="text-2xl sm:text-3xl font-bold text-[#39B54A] mb-2">Descargar reporte</h1>
        <p class="text-gray-600 text-sm sm:text-base max-w-xl mx-auto">Elige año, y opcionalmente mes y semana. El PDF lista tus sesiones de clase (fechas concretas) en ese periodo.</p>
    </div>

    @if (session('error'))
        <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 rounded-lg text-sm text-red-800">{{ session('error') }}</div>
    @endif
    @if (session('info'))
        <div class="mb-6 p-4 bg-blue-50 border-l-4 border-blue-500 rounded-lg text-sm text-blue-800">{{ session('info') }}</div>
    @endif

    <div class="card-premium bg-white rounded-xl shadow-card p-6 sm:p-8 w-full">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">1. Ajustar periodo</h2>
        <form method="GET" action="{{ route('instructor.reporte-reservas-filtro') }}" class="space-y-4 mb-8">
            <div>
                <label for="anio" class="block text-sm font-medium text-gray-700 mb-1">Año</label>
                <select name="anio" id="anio" class="w-full px-4 py-2.5 border-2 border-gray-300 rounded-xl focus:border-[#39B54A] focus:outline-none text-sm">
                    @foreach($aniosLista as $y)
                        <option value="{{ $y }}" @selected((int) $anioSel === (int) $y)>{{ $y }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="mes" class="block text-sm font-medium text-gray-700 mb-1">Mes (opcional)</label>
                <select name="mes" id="mes" class="w-full px-4 py-2.5 border-2 border-gray-300 rounded-xl focus:border-[#39B54A] focus:outline-none text-sm">
                    <option value="">Todo el año</option>
                    @foreach(range(1, 12) as $m)
                        <option value="{{ $m }}" @selected($mesSel !== null && (int) $mesSel === $m)>
                            {{ \Carbon\Carbon::createFromDate(2000, $m, 1)->locale('es')->translatedFormat('F') }}
                        </option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="w-full sm:w-auto px-6 py-2.5 bg-gray-700 text-white rounded-xl font-semibold hover:bg-gray-800 transition-colors">
                Actualizar opciones
            </button>
        </form>

        <h2 class="text-lg font-semibold text-gray-800 mb-4">2. Descargar PDF</h2>
        <form method="GET" action="{{ route('instructor.export-reservas-filtro') }}" class="space-y-4">
            <input type="hidden" name="anio" value="{{ $anioSel }}">
            @if($mesSel !== null)
                <input type="hidden" name="mes" value="{{ $mesSel }}">
            @endif
            @if($mesSel !== null && count($semanasDelMes) > 0)
                <div>
                    <label for="semana_inicio" class="block text-sm font-medium text-gray-700 mb-1">Semana dentro del mes (opcional)</label>
                    <select name="semana_inicio" id="semana_inicio" class="w-full px-4 py-2.5 border-2 border-gray-300 rounded-xl focus:border-[#39B54A] focus:outline-none text-sm">
                        <option value="">Todo el mes seleccionado</option>
                        @foreach($semanasDelMes as $sem)
                            <option value="{{ $sem['value'] }}">{{ $sem['label'] }}</option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-gray-500">Si no eliges semana, el PDF incluye todas las sesiones del mes.</p>
                </div>
            @endif
            <button type="submit" class="w-full sm:w-auto px-6 py-3 bg-[#39B54A] text-white rounded-xl font-semibold hover:bg-[#2d8f3a] shadow-md transition-colors">
                📥 Descargar PDF
            </button>
        </form>

        <p class="mt-8 pt-6 border-t border-gray-200 text-sm text-center">
            <a href="{{ route('instructor.reporte-reservas') }}" class="text-[#39B54A] hover:underline font-medium">Ver tabla completa de mis reservas activas</a>
        </p>
    </div>
    </div>
@endsection
