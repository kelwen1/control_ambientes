@extends('layouts.app')

@section('title', 'Disponibilidad por Jornada')

@section('content')
    <!-- Bienvenida -->
    <div class="mb-6 sm:mb-8">
        <h1 class="text-2xl sm:text-3xl font-bold text-[#39B54A] mb-2">
            Disponibilidad por Jornada
        </h1>
        <p class="text-gray-600 text-sm sm:text-base">Consulta qué ambientes están libres según el tipo de día y la jornada (mañana, tarde, noche)</p>
    </div>

    <!-- Filtro -->
    <div class="bg-white rounded-xl shadow-lg p-6 sm:p-8 mb-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Filtrar disponibilidad</h2>
        <form method="GET" action="{{ route('ambientes.disponibilidad') }}" class="flex flex-wrap items-end gap-4">
            <div>
                <label for="dia_tipo" class="block text-sm font-semibold text-gray-700 mb-1">Tipo de día</label>
                <select name="dia_tipo" 
                        id="dia_tipo" 
                        required
                        class="px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-[#39B54A] focus:outline-none text-sm">
                    <option value="">Seleccione</option>
                    <option value="lunes_viernes" {{ ($dia_tipo ?? '') == 'lunes_viernes' ? 'selected' : '' }}>Lunes a Viernes</option>
                    <option value="sabado_domingo" {{ ($dia_tipo ?? '') == 'sabado_domingo' ? 'selected' : '' }}>Sábados y Domingos</option>
                </select>
            </div>
            <div>
                <label for="jornada" class="block text-sm font-semibold text-gray-700 mb-1">Jornada</label>
                <select name="jornada" 
                        id="jornada" 
                        required
                        class="px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-[#39B54A] focus:outline-none text-sm">
                    <option value="">Seleccione</option>
                    <option value="manana" {{ ($jornada ?? '') == 'manana' ? 'selected' : '' }}>Mañana (7 am - 1 pm)</option>
                    <option value="tarde" {{ ($jornada ?? '') == 'tarde' ? 'selected' : '' }}>Tarde (1 pm - 7 pm)</option>
                    <option value="noche" {{ ($jornada ?? '') == 'noche' ? 'selected' : '' }}>Noche (6 pm - 10 pm)</option>
                </select>
            </div>
            <button type="submit" 
                    class="px-4 py-2 bg-[#39B54A] text-white rounded-lg hover:bg-[#2d8f3a] transition-colors font-medium text-sm">
                Ver disponibilidad
            </button>
        </form>
    </div>

    <!-- Enlace volver a Ambientes -->
    <div class="mb-4">
        <a href="{{ route('ambientes.index') }}" 
           class="inline-flex items-center gap-2 text-sm font-medium text-[#39B54A] hover:text-[#2d8f3a]">
            ← Volver a Ambientes
        </a>
    </div>

    <!-- Resultado -->
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <div class="p-6 sm:p-8 border-b border-gray-200">
            <h2 class="text-xl font-bold text-gray-800 flex items-center gap-2">
                <span>🏛️</span>
                Ambientes disponibles
                @if($dia_tipo && $jornada)
                    <span class="text-base font-normal text-gray-600">
                        — {{ $dia_tipo === 'sabado_domingo' ? 'Sábados y Domingos' : 'Lunes a Viernes' }},
                        {{ $jornada === 'manana' ? 'Mañana' : ($jornada === 'tarde' ? 'Tarde' : 'Noche') }}
                    </span>
                @endif
            </h2>
        </div>

        @if($mensaje)
            <div class="p-8 text-center">
                <p class="text-gray-600">{{ $mensaje }}</p>
            </div>
        @elseif($ambientes->isNotEmpty())
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 sm:px-6 py-3 text-left text-xs sm:text-sm font-semibold text-gray-700 uppercase tracking-wider">Número</th>
                            <th class="px-4 sm:px-6 py-3 text-left text-xs sm:text-sm font-semibold text-gray-700 uppercase tracking-wider">Estado</th>
                            <th class="px-4 sm:px-6 py-3 text-left text-xs sm:text-sm font-semibold text-gray-700 uppercase tracking-wider">Capacidad máx.</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($ambientes as $amb)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm font-medium text-[#39B54A]">
                                    {{ $amb->num_ambiente ?? 'N/A' }}
                                </td>
                                <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    @if($amb->id_estado == 2)
                                        <span class="px-2 py-1 rounded-full bg-yellow-100 text-yellow-800">Mantenimiento</span>
                                    @else
                                        <span class="px-2 py-1 rounded-full bg-green-100 text-green-800">Disponible</span>
                                    @endif
                                </td>
                                <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    {{ $amb->capacidad_max ?? '—' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-6 sm:px-8 py-4 border-t border-gray-200 text-sm text-gray-600">
                Total: {{ $ambientes->count() }} ambiente(s) disponible(s) en la jornada seleccionada.
            </div>
        @else
            <div class="p-8 text-center text-gray-500 text-sm">
                Seleccione tipo de día y jornada, luego pulse «Ver disponibilidad».
            </div>
        @endif
    </div>
@endsection
