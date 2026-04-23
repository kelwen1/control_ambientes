@extends('layouts.app')

@section('title', 'Gestión de ambientes')

@section('content')
    <div class="mb-6 sm:mb-8 animate-fade-slide-up">
        <h1 class="text-2xl sm:text-3xl font-bold text-[#39B54A] mb-2 tracking-tight">
            Catálogo de ambientes
        </h1>
        <p class="text-gray-600 text-sm sm:text-base">Administra los salones y laboratorios (número, capacidad, estado y tipo)</p>
    </div>

    @if (session('success'))
        <div class="mb-6 p-4 bg-green-50 border-l-4 border-[#39B54A] rounded-lg">
            <div class="flex items-center">
                <span class="text-2xl mr-3">✅</span>
                <p class="text-green-800 font-medium">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    @if (session('error'))
        <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 rounded-lg">
            <div class="flex items-center">
                <span class="text-2xl mr-3">❌</span>
                <p class="text-red-800 font-medium">{{ session('error') }}</p>
            </div>
        </div>
    @endif

    <div class="card-premium bg-white rounded-xl shadow-card overflow-hidden hover:shadow-card-hover transition-shadow duration-300 max-w-full min-w-0">
        <div class="p-4 sm:p-6 md:p-8 border-b border-gray-200 flex flex-col sm:flex-row sm:flex-wrap justify-between items-stretch sm:items-center gap-4 min-w-0">
            <h2 class="text-xl sm:text-2xl font-bold text-gray-800 flex items-center gap-2">
                <span class="text-2xl">🏛️</span>
                Ambientes
            </h2>

            <div class="flex flex-col sm:flex-row sm:flex-wrap gap-2 sm:gap-3 w-full min-w-0 sm:w-auto sm:justify-end sm:items-center">
                <form method="GET" action="{{ route('ambientes.gestion.index') }}" class="flex flex-col sm:flex-row gap-2 flex-1 min-w-0 sm:min-w-[12rem] sm:max-w-xl" id="searchForm">
                    <input type="text"
                           name="search"
                           id="searchInput"
                           value="{{ $search }}"
                           placeholder="Buscar por número de ambiente..."
                           class="w-full min-w-0 flex-1 px-4 py-2 border-2 border-gray-300 rounded-xl focus:border-[#39B54A] focus:ring-2 focus:ring-[#39B54A]/20 focus:outline-none transition-all duration-200 text-sm">
                    <button type="submit"
                            class="btn-primary w-full sm:w-auto shrink-0 px-4 py-2 bg-[#39B54A] text-white rounded-xl hover:bg-[#2d8f3a] hover:shadow-glow transition-all duration-200 shadow-md font-medium">
                        🔍 Buscar
                    </button>
                </form>

                @if(auth()->user()->canManageCatalog())
                <a href="{{ route('ambientes.gestion.create') }}"
                   class="btn-primary px-4 sm:px-6 py-2 sm:py-3 bg-[#39B54A] text-white rounded-xl hover:bg-[#2d8f3a] hover:shadow-glow transition-all duration-200 shadow-md text-sm sm:text-base font-semibold inline-flex items-center justify-center gap-2 w-full sm:w-auto">
                    <span>➕</span>
                    <span>Nuevo Ambiente</span>
                </a>
                @endif
            </div>
        </div>

        @if($ambientes && $ambientes->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 sm:px-6 py-3 text-left text-xs sm:text-sm font-semibold text-gray-700 uppercase tracking-wider">Número</th>
                            <th class="px-4 sm:px-6 py-3 text-left text-xs sm:text-sm font-semibold text-gray-700 uppercase tracking-wider">Capacidad máx.</th>
                            <th class="px-4 sm:px-6 py-3 text-left text-xs sm:text-sm font-semibold text-gray-700 uppercase tracking-wider">Estado</th>
                            @if(auth()->user()->canManageCatalog())
                            <th class="px-4 sm:px-6 py-3 text-right text-xs sm:text-sm font-semibold text-gray-700 uppercase tracking-wider">Acciones</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($ambientes as $amb)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    {{ $amb->num_ambiente }}
                                </td>
                                <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    {{ $amb->capacidad_max ?? '—' }}
                                </td>
                                <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    @php
                                        $estado = $estados[$amb->id_estado] ?? 'Desconocido';
                                    @endphp
                                    @if($amb->id_estado == 1)
                                        <span class="px-2 py-1 rounded-full bg-green-100 text-green-800 text-xs sm:text-sm">{{ $estado }}</span>
                                    @elseif($amb->id_estado == 2)
                                        <span class="px-2 py-1 rounded-full bg-yellow-100 text-yellow-800 text-xs sm:text-sm">{{ $estado }}</span>
                                    @elseif($amb->id_estado == 3)
                                        <span class="px-2 py-1 rounded-full bg-red-100 text-red-800 text-xs sm:text-sm">{{ $estado }}</span>
                                    @else
                                        <span class="px-2 py-1 rounded-full bg-gray-100 text-gray-800 text-xs sm:text-sm">{{ $estado }}</span>
                                    @endif
                                </td>
                                @if(auth()->user()->canManageCatalog())
                                <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <a href="{{ route('ambientes.gestion.edit', $amb->id_ambiente) }}"
                                       class="inline-flex items-center px-3 py-1.5 bg-blue-500 text-white rounded-lg hover:bg-blue-600 text-xs sm:text-sm transition-colors mr-2">
                                        ✏️ Editar
                                    </a>
                                    <form action="{{ route('ambientes.gestion.destroy', $amb->id_ambiente) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button"
                                                onclick="openDeleteModalAmbiente('{{ route('ambientes.gestion.destroy', $amb->id_ambiente) }}', '{{ addslashes((string) $amb->num_ambiente) }}')"
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

            @if($ambientes->hasPages())
                <div class="px-4 sm:px-6 py-4 border-t border-gray-200">
                    {{ $ambientes->links() }}
                </div>
            @endif
        @else
            <div class="p-8 text-center text-gray-500">
                No hay ambientes registrados.
            </div>
        @endif
    </div>

    <div id="deleteModalAmbiente" class="fixed inset-0 z-50 hidden items-center justify-center p-4 modal-overlay">
        <div class="modal-container glass-container rounded-2xl shadow-2xl p-6 sm:p-8 w-full max-w-md">
            <div class="text-center">
                <h3 class="text-2xl font-bold text-gray-800 mb-2">¿Eliminar Ambiente?</h3>
                <p class="text-gray-600 mb-6">Se eliminará el ambiente <span id="deleteAmbienteNombre" class="font-semibold"></span>.</p>
                <div class="flex gap-3 sm:gap-4">
                    <button type="button" onclick="closeDeleteModalAmbiente()" class="flex-1 bg-gray-300 text-gray-700 py-3 rounded-lg font-semibold hover:bg-gray-400 transition-colors">Cancelar</button>
                    <form id="deleteFormAmbiente" method="POST" class="flex-1">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full bg-red-600 text-white py-3 rounded-lg font-semibold hover:bg-red-700 transition-colors">Eliminar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function openDeleteModalAmbiente(actionUrl, nombre) {
            const modal = document.getElementById('deleteModalAmbiente');
            const form = document.getElementById('deleteFormAmbiente');
            const label = document.getElementById('deleteAmbienteNombre');
            if (form) form.action = actionUrl;
            if (label) label.textContent = nombre || 'seleccionado';
            if (modal) { modal.classList.remove('hidden'); modal.classList.add('flex'); }
        }
        function closeDeleteModalAmbiente() {
            const modal = document.getElementById('deleteModalAmbiente');
            if (modal) { modal.classList.add('hidden'); modal.classList.remove('flex'); }
        }
    </script>
@endsection
