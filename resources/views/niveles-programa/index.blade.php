@extends('layouts.app')

@section('title', 'Niveles de programa')

@section('content')
    <div class="mb-6 sm:mb-8 animate-fade-slide-up">
        <h1 class="text-2xl sm:text-3xl font-bold text-[#39B54A] mb-2 tracking-tight">
            Niveles de programa
        </h1>
        <p class="text-gray-600 text-sm sm:text-base">El <strong>nombre</strong> se guarda en <span class="font-medium">nivel_programa</span>. Los <strong>meses</strong> que indique al crear/editar se registran en la tabla <span class="font-medium">duracion</span>. La relación completa (nivel + duración) queda en cada <span class="font-medium">programa</span>.</p>
    </div>

    @if (session('success'))
        <div class="mb-4 p-4 bg-green-50 border-l-4 border-[#39B54A] rounded-lg text-sm text-green-800">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="mb-4 p-4 bg-red-50 border-l-4 border-red-500 rounded-lg text-sm text-red-800">{{ session('error') }}</div>
    @endif

    <div class="card-premium bg-white rounded-xl shadow-card overflow-hidden hover:shadow-card-hover transition-shadow duration-300 max-w-full min-w-0">
        <div class="p-4 sm:p-6 md:p-8 border-b border-gray-200 flex flex-col sm:flex-row sm:flex-wrap justify-between items-stretch sm:items-center gap-4 min-w-0">
            <h2 class="text-xl sm:text-2xl font-bold text-gray-800 flex items-center gap-2">
                <span class="text-2xl">📚</span>
                Niveles registrados
            </h2>
            <div class="flex flex-col sm:flex-row flex-wrap gap-2 w-full sm:w-auto">
                @if(auth()->user()->canManageCatalog())
                <a href="{{ route('niveles-programa.create') }}"
                   class="btn-primary px-4 py-2 bg-[#39B54A] text-white rounded-xl hover:bg-[#2d8f3a] transition-all shadow-md text-sm font-semibold inline-flex items-center justify-center gap-2 w-full sm:w-auto">
                    <span>➕</span> Nuevo nivel
                </a>
                @endif
                <a href="{{ route('programas.index') }}"
                   class="px-4 py-2 border-2 border-gray-300 text-gray-700 rounded-xl hover:bg-gray-50 transition-all text-sm font-medium text-center w-full sm:w-auto">
                    ← Programas
                </a>
            </div>
        </div>

        @if($niveles->isEmpty())
            <div class="p-10 text-center text-gray-500">No hay niveles registrados.</div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 sm:px-6 py-3 text-left text-xs sm:text-sm font-semibold text-gray-700 uppercase tracking-wider">Nivel</th>
                        <th class="px-4 sm:px-6 py-3 text-left text-xs sm:text-sm font-semibold text-gray-700 uppercase tracking-wider">Meses (referencia)</th>
                        <th class="px-4 sm:px-6 py-3 text-left text-xs sm:text-sm font-semibold text-gray-700 uppercase tracking-wider">Programas</th>
                        @if(auth()->user()->canManageCatalog())
                        <th class="px-4 sm:px-6 py-3 text-right text-xs sm:text-sm font-semibold text-gray-700 uppercase tracking-wider">Acciones</th>
                        @endif
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                    @foreach($niveles as $nivel)
                        @php
                            $n = (int) ($conProgramas[$nivel->id_nivel_programa] ?? 0);
                            $hintMeses = \App\Helpers\FichaProgramaDuracionHelper::mesesPorNombreNivel((string) $nivel->nivel_programa);
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 sm:px-6 py-4 text-sm text-gray-900 capitalize">{{ $nivel->nivel_programa }}</td>
                            <td class="px-4 sm:px-6 py-4 text-sm text-gray-700">
                                @if($hintMeses !== null)
                                    <span class="text-gray-800">~{{ $hintMeses }} meses</span>
                                    <span class="block text-xs text-gray-500">Solo guía por nombre; la duración real del programa se elige al crear el programa.</span>
                                @else
                                    <span class="text-gray-500 text-sm">Elija duración al crear el <strong>programa</strong></span>
                                @endif
                            </td>
                            <td class="px-4 sm:px-6 py-4 text-sm text-gray-600">{{ $n }} {{ Str::plural('programa', $n) }}</td>
                            @if(auth()->user()->canManageCatalog())
                            <td class="px-4 sm:px-6 py-4 text-right text-sm whitespace-nowrap">
                                <a href="{{ route('niveles-programa.edit', $nivel->id_nivel_programa) }}"
                                   class="inline-flex px-3 py-1.5 bg-blue-500 text-white rounded-lg hover:bg-blue-600 text-xs sm:text-sm mr-2">✏️ Editar</a>
                                @if($n === 0)
                                    <button type="button"
                                            onclick='openDeleteModalNivelPrograma(@json(route("niveles-programa.destroy", $nivel->id_nivel_programa)), @json($nivel->nivel_programa))'
                                            class="inline-flex px-3 py-1.5 bg-red-600 text-white rounded-lg hover:bg-red-700 text-xs sm:text-sm transition-colors">
                                        🗑️ Eliminar
                                    </button>
                                @else
                                    <span class="text-xs text-gray-400" title="Tiene programas asociados">—</span>
                                @endif
                            </td>
                            @endif
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <div id="deleteModalNivelPrograma" class="fixed inset-0 z-50 hidden items-center justify-center p-4 modal-overlay">
        <div class="modal-container glass-container rounded-2xl shadow-2xl p-6 sm:p-8 w-full max-w-md">
            <div class="text-center">
                <h3 class="text-2xl font-bold text-gray-800 mb-2">¿Eliminar nivel de programa?</h3>
                <p class="text-gray-600 mb-6">Se eliminará el nivel <span id="deleteNivelProgramaNombre" class="font-semibold"></span>.</p>
                <div class="flex gap-3 sm:gap-4">
                    <button type="button" onclick="closeDeleteModalNivelPrograma()" class="flex-1 bg-gray-300 text-gray-700 py-3 rounded-lg font-semibold hover:bg-gray-400 transition-colors">Cancelar</button>
                    <form id="deleteFormNivelPrograma" method="POST" class="flex-1">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full bg-red-600 text-white py-3 rounded-lg font-semibold hover:bg-red-700 transition-colors">Eliminar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function openDeleteModalNivelPrograma(actionUrl, nombre) {
            const modal = document.getElementById('deleteModalNivelPrograma');
            const form = document.getElementById('deleteFormNivelPrograma');
            const label = document.getElementById('deleteNivelProgramaNombre');
            if (form) form.action = actionUrl;
            if (label) label.textContent = nombre || 'seleccionado';
            if (modal) { modal.classList.remove('hidden'); modal.classList.add('flex'); }
        }
        function closeDeleteModalNivelPrograma() {
            const modal = document.getElementById('deleteModalNivelPrograma');
            if (modal) { modal.classList.add('hidden'); modal.classList.remove('flex'); }
        }
    </script>
@endsection
