@extends('layouts.app')

@section('title', 'Resultados de Aprendizaje')

@section('content')
    <div class="mb-6 sm:mb-8 animate-fade-slide-up">
        <h1 class="text-2xl sm:text-3xl font-bold text-[#39B54A] mb-2 tracking-tight">
            Resultados de Aprendizaje
        </h1>
        <p class="text-gray-600 text-sm sm:text-base">Busque, filtre por competencia o añada un resultado nuevo.</p>
    </div>

    @if (session('success'))
        <div class="mb-4 p-4 bg-green-50 border-l-4 border-[#39B54A] rounded-lg text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="mb-4 p-4 bg-red-50 border-l-4 border-red-500 rounded-lg text-sm text-red-800">
            {{ session('error') }}
        </div>
    @endif

    <div class="card-premium bg-white rounded-xl shadow-card overflow-hidden hover:shadow-card-hover transition-shadow duration-300 max-w-full min-w-0">
        <div class="p-4 sm:p-6 md:p-8 border-b border-gray-200 flex min-w-0 flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div class="min-w-0 md:shrink">
                <h2 class="text-xl sm:text-2xl font-bold text-gray-800 flex items-center gap-2">
                    <span class="text-2xl">📄</span>
                    Resultados
                </h2>
            </div>
            <div class="flex w-full min-w-0 flex-col gap-3 md:w-auto md:min-w-0 md:flex-1 md:items-center md:justify-end">
                @if(empty($filtroFijoCompetencia))
                <form method="GET" action="{{ route('resultados.index') }}" class="flex flex-col sm:flex-row sm:flex-wrap gap-2 w-full min-w-0">
                    <input type="text"
                           name="search"
                           value="{{ $search }}"
                           placeholder="Buscar por denominación..."
                           class="w-full min-w-0 flex-1 px-4 py-2 border-2 border-gray-300 rounded-xl focus:border-[#39B54A] focus:ring-2 focus:ring-[#39B54A]/20 focus:outline-none transition-all duration-200 text-sm sm:min-w-[10rem]">

                    <select name="competencia"
                            class="w-full sm:w-auto sm:min-w-[9rem] px-3 py-2 border-2 border-gray-300 rounded-xl focus:border-[#39B54A] focus:ring-2 focus:ring-[#39B54A]/20 focus:outline-none transition-all duration-200 text-xs sm:text-sm">
                        <option value="">Todas las competencias</option>
                        @foreach($competencias as $comp)
                            <option value="{{ $comp->id_competencia }}" {{ $competenciaSeleccionada == $comp->id_competencia ? 'selected' : '' }}>
                                {{ $comp->nombre_competencia }}
                            </option>
                        @endforeach
                    </select>

                    <button type="submit"
                            class="btn-primary w-full sm:w-auto shrink-0 px-4 py-2 bg-[#39B54A] text-white rounded-xl hover:bg-[#2d8f3a] hover:shadow-glow transition-all duration-200 shadow-md text-sm font-medium">
                        🔍 Buscar
                    </button>
                </form>
                @else
                <div class="flex w-full min-w-0 flex-row flex-nowrap items-center justify-end gap-2 overflow-x-auto pb-0.5 [scrollbar-gutter:stable]">
                    <p class="flex min-w-0 max-w-[50vw] shrink items-baseline gap-1 text-sm text-gray-600 sm:max-w-[12rem] md:max-w-xs lg:max-w-sm" title="{{ $competenciaFija->nombre_competencia ?? 'seleccionada' }}">
                        <span class="shrink-0">Competencia:</span>
                        <span class="min-w-0 truncate font-semibold text-gray-800">{{ $competenciaFija->nombre_competencia ?? 'seleccionada' }}</span>
                    </p>
                    <form method="GET" action="{{ route('resultados.index') }}" class="flex min-w-0 flex-1 flex-row items-center gap-2 sm:min-w-[12rem] md:min-w-[16rem]">
                        <input type="hidden" name="competencia" value="{{ $competenciaSeleccionada }}">
                        <input type="text"
                               name="search"
                               value="{{ $search }}"
                               placeholder="Buscar denominación…"
                               class="min-w-0 flex-1 px-3 py-2 border-2 border-gray-300 rounded-xl focus:border-[#39B54A] focus:ring-2 focus:ring-[#39B54A]/20 focus:outline-none transition-all duration-200 text-sm">
                        <button type="submit"
                                class="btn-primary shrink-0 px-3 py-2 sm:px-4 bg-[#39B54A] text-white rounded-xl hover:bg-[#2d8f3a] hover:shadow-glow transition-all duration-200 shadow-md text-sm font-medium">
                            🔍 Buscar
                        </button>
                    </form>
                    @if(auth()->user()->canManageCatalog())
                    <a href="{{ route('resultados.create', ['competencia' => $competenciaSeleccionada]) }}"
                       class="btn-primary px-3 py-2 sm:px-4 bg-[#39B54A] text-white rounded-xl hover:bg-[#2d8f3a] hover:shadow-glow transition-all duration-200 shadow-md text-sm font-semibold inline-flex items-center justify-center gap-2 shrink-0">
                        <span>➕</span>
                        <span class="whitespace-nowrap">Nuevo Resultado</span>
                    </a>
                    @endif
                </div>
                @endif
            </div>
        </div>

        @if($resultados->count())
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 sm:px-6 py-3 text-left text-xs sm:text-sm font-semibold text-gray-700 uppercase tracking-wider">Competencia</th>
                        <th class="px-4 sm:px-6 py-3 text-left text-xs sm:text-sm font-semibold text-gray-700 uppercase tracking-wider">Resultado</th>
                        <th class="px-4 sm:px-6 py-3 text-left text-xs sm:text-sm font-semibold text-gray-700 uppercase tracking-wider">Horas</th>
                        <th class="px-4 sm:px-6 py-3 text-left text-xs sm:text-sm font-semibold text-gray-700 uppercase tracking-wider">Sesiones</th>
                        @if(auth()->user()->canManageCatalog())
                        <th class="px-4 sm:px-6 py-3 text-right text-xs sm:text-sm font-semibold text-gray-700 uppercase tracking-wider">Acciones</th>
                        @endif
                    </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($resultados as $resultado)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                {{ $resultado->competencia->nombre_competencia ?? '—' }}
                            </td>
                            <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-800">
                                {{ $resultado->denominacion }}
                            </td>
                            <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                {{ $resultado->horas ?? '—' }}
                            </td>
                            <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                {{ $resultado->sesiones ?? '—' }}
                            </td>
                            @if(auth()->user()->canManageCatalog())
                            <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <a href="{{ route('resultados.edit', $resultado->id_resultado) }}"
                                   class="inline-flex items-center px-3 py-1.5 bg-blue-500 text-white rounded-lg hover:bg-blue-600 text-xs sm:text-sm transition-colors mr-2">
                                    ✏️ Editar
                                </a>
                                <form action="{{ route('resultados.destroy', $resultado->id_resultado) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button"
                                            onclick="openDeleteModalResultado('{{ route('resultados.destroy', $resultado->id_resultado) }}', '{{ addslashes($resultado->denominacion) }}')"
                                            class="inline-flex items-center px-3 py-1.5 bg-red-600 text-white rounded-lg hover:bg-red-700 text-xs sm:text-sm transition-colors">
                                        🗑️ Eliminar
                                    </button>
                                </form>
                            </td>
                            @endif
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            @if($resultados->hasPages())
                <div class="px-4 sm:px-6 py-4 border-t border-gray-200">
                    {{ $resultados->links() }}
                </div>
            @endif
        @else
            <div class="p-8 text-center text-gray-500 space-y-2">
                @if(!empty($filtroFijoCompetencia))
                    <p class="text-base">
                        @if($search !== '' && $search !== null)
                            No hay resultados con ese criterio de búsqueda.
                        @else
                            Aún no hay resultados bajo <span class="font-medium text-gray-700">esta competencia</span>.
                        @endif
                    </p>
                    @if(auth()->user()->canManageCatalog())
                        <p class="text-sm">Puede añadir uno con <span class="font-medium text-gray-700">Nuevo Resultado</span> o abrir el <a href="{{ route('resultados.index') }}" class="text-[#39B54A] hover:underline font-medium">listado completo con buscador</a>.</p>
                    @endif
                @else
                    @if($search !== '' && $search !== null || $competenciaSeleccionada)
                        <p>No hay resultados con esos criterios. Ajuste la búsqueda o el filtro de competencia.</p>
                    @else
                        <p>No hay resultados registrados todavía.</p>
                    @endif
                @endif
            </div>
        @endif
    </div>

    <div id="deleteModalResultado" class="fixed inset-0 z-50 hidden items-center justify-center p-4 modal-overlay">
        <div class="modal-container glass-container rounded-2xl shadow-2xl p-6 sm:p-8 w-full max-w-md">
            <div class="text-center">
                <h3 class="text-2xl font-bold text-gray-800 mb-2">¿Eliminar Resultado?</h3>
                <p class="text-gray-600 mb-6">Se eliminará el resultado <span id="deleteResultadoNombre" class="font-semibold"></span>.</p>
                <div class="flex gap-3 sm:gap-4">
                    <button type="button" onclick="closeDeleteModalResultado()" class="flex-1 bg-gray-300 text-gray-700 py-3 rounded-lg font-semibold hover:bg-gray-400 transition-colors">Cancelar</button>
                    <form id="deleteFormResultado" method="POST" class="flex-1">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full bg-red-600 text-white py-3 rounded-lg font-semibold hover:bg-red-700 transition-colors">Eliminar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function openDeleteModalResultado(actionUrl, nombre) {
            const modal = document.getElementById('deleteModalResultado');
            const form = document.getElementById('deleteFormResultado');
            const label = document.getElementById('deleteResultadoNombre');
            if (form) form.action = actionUrl;
            if (label) label.textContent = nombre || 'seleccionado';
            if (modal) { modal.classList.remove('hidden'); modal.classList.add('flex'); }
        }
        function closeDeleteModalResultado() {
            const modal = document.getElementById('deleteModalResultado');
            if (modal) { modal.classList.add('hidden'); modal.classList.remove('flex'); }
        }
    </script>
@endsection

