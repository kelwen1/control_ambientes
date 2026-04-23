@extends('layouts.app')

@section('title', 'Programas')

@section('content')
    <div class="mb-6 sm:mb-8 animate-fade-slide-up">
        <h1 class="text-2xl sm:text-3xl font-bold text-[#39B54A] mb-2 tracking-tight">
            Programas de Formación
        </h1>
        <p class="text-gray-600 text-sm sm:text-base">
            Administra los programas asociados a las fichas y competencias.
            <a href="{{ route('niveles-programa.index') }}" class="text-[#39B54A] hover:underline font-medium whitespace-nowrap">Niveles de programa</a>
        </p>
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
        <div class="p-4 sm:p-6 md:p-8 border-b border-gray-200 flex flex-col sm:flex-row sm:flex-wrap justify-between gap-4 min-w-0 items-stretch sm:items-center">
            <h2 class="text-xl sm:text-2xl font-bold text-gray-800 flex items-center gap-2">
                <span class="text-2xl">🎓</span>
                Programas
            </h2>
            <div class="flex flex-col sm:flex-row sm:flex-wrap gap-2 sm:gap-3 w-full min-w-0 sm:w-auto sm:justify-end sm:items-center">
                <form method="GET" action="{{ route('programas.index') }}" class="flex flex-col sm:flex-row gap-2 flex-1 min-w-0 sm:min-w-[12rem] sm:max-w-xl">
                    <input type="text"
                           name="search"
                           id="programasSearchInput"
                           value="{{ $search }}"
                           autocomplete="off"
                           placeholder=" nombre del programa..."
                           class="w-full min-w-0 flex-1 px-4 py-2 border-2 border-gray-300 rounded-xl focus:border-[#39B54A] focus:ring-2 focus:ring-[#39B54A]/20 focus:outline-none transition-all duration-200 text-sm"
                           oninput="this.value = this.value.replace(/[^\p{L}\s]/gu, '');">
                    <button type="submit"
                            class="btn-primary w-full sm:w-auto shrink-0 px-4 py-2 bg-[#39B54A] text-white rounded-xl hover:bg-[#2d8f3a] hover:shadow-glow transition-all duration-200 shadow-md text-sm font-medium">
                        🔍 Buscar
                    </button>
                </form>
                @if(auth()->user()->canManageCatalog())
                <a href="{{ route('programas.create') }}"
                   class="btn-primary px-4 py-2 bg-[#39B54A] text-white rounded-xl hover:bg-[#2d8f3a] hover:shadow-glow transition-all duration-200 shadow-md text-sm font-semibold inline-flex items-center justify-center gap-2 w-full sm:w-auto">
                    <span>➕</span>
                    <span>Nuevo Programa</span>
                </a>
                @endif
            </div>
        </div>

        @if($programas->count())
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 sm:px-6 py-3 text-left text-xs sm:text-sm font-semibold text-gray-700 uppercase tracking-wider">Nombre</th>
                            <th class="px-4 sm:px-6 py-3 text-left text-xs sm:text-sm font-semibold text-gray-700 uppercase tracking-wider">Nivel</th>
                            <th class="px-4 sm:px-6 py-3 text-left text-xs sm:text-sm font-semibold text-gray-700 uppercase tracking-wider">Duración</th>
                            @if(auth()->user()->canManageCatalog())
                            <th class="px-4 sm:px-6 py-3 text-right text-xs sm:text-sm font-semibold text-gray-700 uppercase tracking-wider">Acciones</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($programas as $programa)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-800">
                                    {{ $programa->nombre_programa }}
                                </td>
                                <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    {{ $niveles[$programa->id_nivel_programa] ?? '—' }}
                                </td>
                                <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    {{ $duraciones[$programa->id_duracion] ?? '—' }}
                                </td>
                                @if(auth()->user()->canManageCatalog())
                                <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <a href="{{ route('programas.edit', $programa->id_programa) }}"
                                       class="inline-flex items-center px-3 py-1.5 bg-blue-500 text-white rounded-lg hover:bg-blue-600 text-xs sm:text-sm transition-colors mr-2">
                                        ✏️ Editar
                                    </a>
                                    <form action="{{ route('programas.destroy', $programa->id_programa) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button"
                                                onclick="openDeleteModalPrograma('{{ route('programas.destroy', $programa->id_programa) }}', '{{ addslashes($programa->nombre_programa) }}')"
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
            @if($programas->hasPages())
                <div class="px-4 sm:px-6 py-4 border-t border-gray-200">
                    {{ $programas->links() }}
                </div>
            @endif
        @else
            <div class="p-8 text-center text-gray-500">
                No hay programas registrados.
            </div>
        @endif
    </div>

    <div id="deleteModalPrograma" class="fixed inset-0 z-50 hidden items-center justify-center p-4 modal-overlay">
        <div class="modal-container glass-container rounded-2xl shadow-2xl p-6 sm:p-8 w-full max-w-md">
            <div class="text-center">
                <h3 class="text-2xl font-bold text-gray-800 mb-2">¿Eliminar Programa?</h3>
                <p class="text-gray-600 mb-6">Se eliminará el programa <span id="deleteProgramaNombre" class="font-semibold"></span>.</p>
                <div class="flex gap-3 sm:gap-4">
                    <button type="button" onclick="closeDeleteModalPrograma()" class="flex-1 bg-gray-300 text-gray-700 py-3 rounded-lg font-semibold hover:bg-gray-400 transition-colors">Cancelar</button>
                    <form id="deleteFormPrograma" method="POST" class="flex-1">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full bg-red-600 text-white py-3 rounded-lg font-semibold hover:bg-red-700 transition-colors">Eliminar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function openDeleteModalPrograma(actionUrl, nombre) {
            const modal = document.getElementById('deleteModalPrograma');
            const form = document.getElementById('deleteFormPrograma');
            const label = document.getElementById('deleteProgramaNombre');
            if (form) form.action = actionUrl;
            if (label) label.textContent = nombre || 'seleccionado';
            if (modal) { modal.classList.remove('hidden'); modal.classList.add('flex'); }
        }
        function closeDeleteModalPrograma() {
            const modal = document.getElementById('deleteModalPrograma');
            if (modal) { modal.classList.add('hidden'); modal.classList.remove('flex'); }
        }
    </script>
@endsection

