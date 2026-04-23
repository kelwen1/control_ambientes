@extends('layouts.app')

@section('title', 'Competencias')

@section('content')
    <div class="mb-6 sm:mb-8 animate-fade-slide-up">
        <h1 class="text-2xl sm:text-3xl font-bold text-[#39B54A] mb-2 tracking-tight">
            Competencias
        </h1>
        <p class="text-gray-600 text-sm sm:text-base">El catálogo de competencias es común: cualquier programa la puede usar al trabajar con fichas y reservas.</p>
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
        <div class="p-4 sm:p-6 md:p-8 border-b border-gray-200 flex flex-col sm:flex-row sm:flex-wrap sm:items-center sm:justify-between gap-4 min-w-0">
            <div class="min-w-0 shrink-0">
                <h2 class="text-xl sm:text-2xl font-bold text-gray-800 flex items-center gap-2">
                    <span class="text-2xl">🧩</span>
                    Competencias
                </h2>
                <p class="text-xs text-gray-500 mt-1">Catálogo compartido para todos los programas de formación.</p>
            </div>
            <div class="flex flex-col sm:flex-row sm:flex-nowrap sm:items-center gap-2 sm:gap-3 w-full min-w-0 sm:w-auto sm:ml-auto sm:justify-end">
                <form method="GET" action="{{ route('competencias.index') }}" class="flex flex-col sm:flex-row sm:flex-nowrap sm:items-center gap-2 w-full min-w-0 sm:w-auto sm:min-w-0 sm:max-w-3xl">
                    <input type="text"
                           name="search"
                           value="{{ $search }}"
                           placeholder="Buscar por nombre de competencia..."
                           class="w-full min-w-0 sm:min-w-[8rem] sm:max-w-[14rem] md:max-w-xs px-4 py-2 border-2 border-gray-300 rounded-xl focus:border-[#39B54A] focus:ring-2 focus:ring-[#39B54A]/20 focus:outline-none transition-all duration-200 text-sm shrink">
                    <button type="submit"
                            class="btn-primary w-full sm:w-auto shrink-0 px-4 py-2 bg-[#39B54A] text-white rounded-xl hover:bg-[#2d8f3a] hover:shadow-glow transition-all duration-200 shadow-md text-sm font-medium">
                        🔍 Buscar
                    </button>
                </form>
                @if(auth()->user()->canManageCatalog())
                <a href="{{ route('competencias.create') }}"
                   class="btn-primary px-4 py-2 bg-[#39B54A] text-white rounded-xl hover:bg-[#2d8f3a] hover:shadow-glow transition-all duration-200 shadow-md text-sm font-semibold inline-flex items-center justify-center gap-2 w-full sm:w-auto shrink-0 whitespace-nowrap">
                    <span>➕</span>
                    <span>Nueva Competencia</span>
                </a>
                @endif
            </div>
        </div>

        @if($competencias->count())
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 sm:px-6 py-3 text-left text-xs sm:text-sm font-semibold text-gray-700 uppercase tracking-wider">Competencia</th>
                        <th class="px-4 sm:px-6 py-3 text-right text-xs sm:text-sm font-semibold text-gray-700 uppercase tracking-wider">{{ auth()->user()->canManageCatalog() ? 'Acciones' : 'Consulta' }}</th>
                    </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($competencias as $competencia)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-800">
                                {{ $competencia->nombre_competencia }}
                            </td>
                            <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                @if(auth()->user()->canManageCatalog())
                                <a href="{{ route('competencias.edit', $competencia->id_competencia) }}"
                                   class="inline-flex items-center px-3 py-1.5 bg-blue-500 text-white rounded-lg hover:bg-blue-600 text-xs sm:text-sm transition-colors mr-2">
                                    ✏️ Editar
                                </a>
                                @endif
                                <a href="{{ route('resultados.index', ['competencia' => $competencia->id_competencia]) }}"
                                   class="inline-flex items-center px-3 py-1.5 bg-emerald-500 text-white rounded-lg hover:bg-emerald-600 text-xs sm:text-sm transition-colors mr-2"
                                   title="Ver resultados de esta competencia">
                                    📄 Resultados
                                </a>
                                @if(auth()->user()->canManageCatalog())
                                <form action="{{ route('competencias.destroy', $competencia->id_competencia) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button"
                                            onclick="openDeleteModalCompetencia('{{ route('competencias.destroy', $competencia->id_competencia) }}', '{{ addslashes($competencia->nombre_competencia) }}')"
                                            class="inline-flex items-center px-3 py-1.5 bg-red-600 text-white rounded-lg hover:bg-red-700 text-xs sm:text-sm transition-colors">
                                        🗑️ Eliminar
                                    </button>
                                </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            @if($competencias->hasPages())
                <div class="px-4 sm:px-6 py-4 border-t border-gray-200">
                    {{ $competencias->links() }}
                </div>
            @endif
        @else
            <div class="p-8 text-center text-gray-500">
                No hay competencias registradas.
            </div>
        @endif
    </div>

    <div id="deleteModalCompetencia" class="fixed inset-0 z-50 hidden items-center justify-center p-4 modal-overlay">
        <div class="modal-container glass-container rounded-2xl shadow-2xl p-6 sm:p-8 w-full max-w-md">
            <div class="text-center">
                <h3 class="text-2xl font-bold text-gray-800 mb-2">¿Eliminar Competencia?</h3>
                <p class="text-gray-600 mb-6">Se eliminará la competencia <span id="deleteCompetenciaNombre" class="font-semibold"></span>.</p>
                <div class="flex gap-3 sm:gap-4">
                    <button type="button" onclick="closeDeleteModalCompetencia()" class="flex-1 bg-gray-300 text-gray-700 py-3 rounded-lg font-semibold hover:bg-gray-400 transition-colors">Cancelar</button>
                    <form id="deleteFormCompetencia" method="POST" class="flex-1">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full bg-red-600 text-white py-3 rounded-lg font-semibold hover:bg-red-700 transition-colors">Eliminar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function openDeleteModalCompetencia(actionUrl, nombre) {
            const modal = document.getElementById('deleteModalCompetencia');
            const form = document.getElementById('deleteFormCompetencia');
            const label = document.getElementById('deleteCompetenciaNombre');
            if (form) form.action = actionUrl;
            if (label) label.textContent = nombre || 'seleccionada';
            if (modal) { modal.classList.remove('hidden'); modal.classList.add('flex'); }
        }
        function closeDeleteModalCompetencia() {
            const modal = document.getElementById('deleteModalCompetencia');
            if (modal) { modal.classList.add('hidden'); modal.classList.remove('flex'); }
        }
    </script>
@endsection

