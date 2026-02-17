@extends('layouts.app')

@section('title', 'Inventario')

@section('content')
    <!-- Bienvenida -->
    <div class="mb-6 sm:mb-8">
        <h1 class="text-2xl sm:text-3xl font-bold text-[#39B54A] mb-2">
            Gestión de Inventario
        </h1>
        <p class="text-gray-600 text-sm sm:text-base">Administra y consulta el inventario de los ambientes</p>
    </div>

    <!-- Mensaje de éxito -->
    @if (session('success'))
        <div class="mb-6 p-4 bg-green-50 border-l-4 border-[#39B54A] rounded-lg">
            <div class="flex items-center">
                <span class="text-2xl mr-3">✅</span>
                <p class="text-green-800 font-medium">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    <!-- Mensaje de error -->
    @if (session('error'))
        <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 rounded-lg">
            <div class="flex items-center">
                <span class="text-2xl mr-3">❌</span>
                <p class="text-red-800 font-medium">{{ session('error') }}</p>
            </div>
        </div>
    @endif

    <!-- Tabla de inventario -->
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <div class="p-6 sm:p-8 border-b border-gray-200 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <h2 class="text-xl sm:text-2xl font-bold text-gray-800 flex items-center gap-2">
                <span class="text-2xl">📦</span>
                Registros de Inventario
            </h2>
            
            <div class="flex gap-3 w-full sm:w-auto">
                <!-- Buscador (todos los roles) -->
                <form method="GET" action="{{ route('inventario.index') }}" class="flex-1 sm:flex-initial flex gap-2" id="searchForm">
                    <input type="text" 
                           name="search" 
                           id="searchInput"
                           value="{{ $search ?? '' }}"
                           placeholder="Buscar por ambiente (ej: 26)..."
                           class="flex-1 px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-[#39B54A] focus:outline-none transition-colors text-sm">
                    <button type="submit" 
                            class="px-4 py-2 bg-[#39B54A] text-white rounded-lg hover:bg-[#2d8f3a] transition-colors shadow-lg font-medium">
                        🔍 Buscar
                    </button>
                </form>
                
                <button type="button" 
                        onclick="openExportModal('exportModalInventario')"
                        class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors shadow-lg text-sm font-semibold flex items-center gap-2">
                    <span>📥</span>
                    <span>Reportes</span>
                </button>
                @unless(auth()->user()->isCoordinator())
                <a href="{{ route('inventario.create') }}" 
                   class="px-4 sm:px-6 py-2 sm:py-3 bg-[#39B54A] text-white rounded-lg hover:bg-[#2d8f3a] transition-colors shadow-lg transform hover:scale-105 text-sm sm:text-base font-semibold flex items-center gap-2">
                    <span>➕</span>
                    <span>Agregar Inventario</span>
                </a>
                @endunless
            </div>
        </div>

        @if($inventarios && $inventarios->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 sm:px-6 py-3 text-left text-xs sm:text-sm font-semibold text-gray-700 uppercase tracking-wider">Ambiente</th>
                            <th class="px-4 sm:px-6 py-3 text-left text-xs sm:text-sm font-semibold text-gray-700 uppercase tracking-wider">Computadores</th>
                            <th class="px-4 sm:px-6 py-3 text-left text-xs sm:text-sm font-semibold text-gray-700 uppercase tracking-wider">Sillas</th>
                            <th class="px-4 sm:px-6 py-3 text-left text-xs sm:text-sm font-semibold text-gray-700 uppercase tracking-wider">Mesas</th>
                            <th class="px-4 sm:px-6 py-3 text-left text-xs sm:text-sm font-semibold text-gray-700 uppercase tracking-wider">Aire Acondicionado</th>
                            <th class="px-4 sm:px-6 py-3 text-left text-xs sm:text-sm font-semibold text-gray-700 uppercase tracking-wider">Tablero</th>
                            <th class="px-4 sm:px-6 py-3 text-left text-xs sm:text-sm font-semibold text-gray-700 uppercase tracking-wider">Televisor</th>
                            <th class="px-4 sm:px-6 py-3 text-left text-xs sm:text-sm font-semibold text-gray-700 uppercase tracking-wider">Ventiladores</th>
                            <th class="px-4 sm:px-6 py-3 text-left text-xs sm:text-sm font-semibold text-gray-700 uppercase tracking-wider">Videobeam</th>
                            <th class="px-4 sm:px-6 py-3 text-left text-xs sm:text-sm font-semibold text-gray-700 uppercase tracking-wider">Herramientas</th>
                            @unless(auth()->user()->isCoordinator())
                            <th class="sticky right-0 bg-gray-50 px-4 sm:px-6 py-3 text-left text-xs sm:text-sm font-semibold text-gray-700 uppercase tracking-wider z-10">Acciones</th>
                            @endunless
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($inventarios as $inventario)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm font-medium text-[#39B54A]">
                                    {{ $inventario->num_ambiente ?? 'N/A' }}
                                </td>
                                <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-700 font-medium">
                                    {{ $inventario->computadores ?? 0 }}
                                </td>
                                <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-700 font-medium">
                                    {{ $inventario->sillas ?? 0 }}
                                </td>
                                <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-700 font-medium">
                                    {{ $inventario->mesas ?? 0 }}
                                </td>
                                <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-700 font-medium">
                                    {{ $inventario->aire_acondicionado ?? 0 }}
                                </td>
                                <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    <span class="px-2 py-1 rounded-full {{ $inventario->tablero === 'Sí' ? 'bg-green-100 text-green-800' : ($inventario->tablero === 'No' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-600') }}">
                                        {{ $inventario->tablero ?? 'N/A' }}
                                    </span>
                                </td>
                                <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    <span class="px-2 py-1 rounded-full {{ $inventario->televisor === 'Sí' ? 'bg-green-100 text-green-800' : ($inventario->televisor === 'No' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-600') }}">
                                        {{ $inventario->televisor ?? 'N/A' }}
                                    </span>
                                </td>
                                <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    <span class="px-2 py-1 rounded-full {{ $inventario->ventiladores === 'Sí' ? 'bg-green-100 text-green-800' : ($inventario->ventiladores === 'No' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-600') }}">
                                        {{ $inventario->ventiladores ?? 'N/A' }}
                                    </span>
                                </td>
                                <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    <span class="px-2 py-1 rounded-full {{ $inventario->vidiovid === 'Sí' ? 'bg-green-100 text-green-800' : ($inventario->vidiovid === 'No' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-600') }}">
                                        {{ $inventario->vidiovid ?? 'N/A' }}
                                    </span>
                                </td>
                                <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    <span class="px-2 py-1 rounded-full {{ $inventario->herramientas === 'Sí' ? 'bg-green-100 text-green-800' : ($inventario->herramientas === 'No' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-600') }}">
                                        {{ $inventario->herramientas ?? 'N/A' }}
                                    </span>
                                </td>
                                @unless(auth()->user()->isCoordinator())
                                <td class="sticky right-0 bg-white px-4 sm:px-6 py-4 whitespace-nowrap text-sm font-medium z-10">
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('inventario.edit', $inventario->id_Inventario) }}" 
                                           class="px-3 py-1.5 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors text-xs sm:text-sm font-medium">
                                            ✏️ Editar
                                        </a>
                                        <button onclick="openDeleteModal({{ $inventario->id_Inventario }}, {{ json_encode($inventario->num_ambiente ?? 'N/A') }})" 
                                                class="px-3 py-1.5 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors text-xs sm:text-sm font-medium">
                                            🗑️ Eliminar
                                        </button>
                                    </div>
                                </td>
                                @endunless
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Controles de paginación -->
            @if($inventarios->hasPages())
                <div class="px-6 sm:px-8 py-4 border-t border-gray-200">
                    <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                        <div class="text-sm text-gray-700">
                            Mostrando {{ $inventarios->firstItem() }} a {{ $inventarios->lastItem() }} de {{ $inventarios->total() }} registros
                        </div>
                        <div class="flex items-center gap-2">
                            {{ $inventarios->links() }}
                        </div>
                    </div>
                </div>
            @endif
        @else
            <div class="p-12 text-center">
                <span class="text-6xl mb-4 block">📦</span>
                <h3 class="text-xl font-semibold text-gray-700 mb-2">No hay registros de inventario</h3>
                <p class="text-gray-500 mb-6">
                    @if(isset($search) && $search)
                        No se encontraron inventarios para el ambiente "{{ $search }}"
                    @else
                        Comienza asociando un inventario a un ambiente
                    @endif
                </p>
                @if((!isset($search) || !$search) && !auth()->user()->isCoordinator())
                    <a href="{{ route('inventario.create') }}" 
                       class="inline-block px-6 py-3 bg-[#39B54A] text-white rounded-lg font-semibold hover:bg-[#2d8f3a] transition-colors shadow-lg">
                        Crear Primer Registro
                    </a>
                @endif
            </div>
        @endif
    </div>

    <!-- Modal de confirmación para eliminar -->
    <div id="deleteModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 modal-overlay hidden">
        <div class="modal-container glass-container rounded-2xl shadow-2xl p-6 sm:p-8 w-full max-w-md">
            <div class="text-center">
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-100 mb-4">
                    <svg class="h-10 w-10 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <h3 class="text-2xl font-bold text-gray-800 mb-2">Confirmar Eliminación</h3>
                <p class="text-gray-600 mb-6">
                    ¿Estás seguro de que quieres eliminar el inventario del ambiente <span id="ambienteName" class="font-semibold text-[#39B54A]"></span>?
                    <br><span class="text-sm text-red-600">Esta acción no se puede deshacer.</span>
                </p>
                <div class="flex gap-4">
                    <button onclick="closeDeleteModal()" 
                            class="flex-1 bg-gray-300 text-gray-700 py-3 rounded-lg font-semibold text-base hover:bg-gray-400 transition-colors shadow-lg">
                        Cancelar
                    </button>
                    <form id="deleteForm" method="POST" class="flex-1">
                        @csrf
                        @method('DELETE')
                        <button type="submit" 
                                class="w-full bg-red-500 text-white py-3 rounded-lg font-semibold text-base hover:bg-red-600 transition-colors shadow-lg">
                            Eliminar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @include('components.export-modal', [
        'modalId' => 'exportModalInventario',
        'exportPdfUrl' => route('inventario.export', ['search' => $search ?? '']),
    ])

    <style>
        .glass-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
        }
        .modal-overlay {
            background: rgba(0, 0, 0, 0.5);
        }
        /* Estilos para columna sticky de acciones */
        .sticky {
            position: sticky;
        }
        thead th.sticky {
            top: 0;
        }
        tbody td.sticky {
            background-color: white;
        }
        tbody tr:hover td.sticky {
            background-color: #f9fafb;
        }
        /* Estilos para paginación de Laravel */
        .pagination {
            display: flex;
            list-style: none;
            gap: 0.5rem;
            margin: 0;
            padding: 0;
        }
        .pagination li {
            display: inline-block;
        }
        .pagination li a,
        .pagination li span {
            display: inline-block;
            padding: 0.5rem 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            text-decoration: none;
            color: #374151;
            transition: all 0.2s;
        }
        .pagination li a:hover {
            background-color: #39B54A;
            color: white;
            border-color: #39B54A;
        }
        .pagination li.active span {
            background-color: #39B54A;
            color: white;
            border-color: #39B54A;
            font-weight: 600;
        }
        .pagination li.disabled span {
            color: #9ca3af;
            cursor: not-allowed;
            background-color: #f3f4f6;
        }
    </style>

    <script>
        window.openDeleteModal = function(id, ambienteName) {
            const baseUrl = window.location.origin + '/inventario';
            const modal = document.getElementById('deleteModal');
            const form = document.getElementById('deleteForm');
            const ambienteNameElement = document.getElementById('ambienteName');
            
            if (!modal || !form) return;
            
            if (ambienteNameElement) {
                ambienteNameElement.textContent = ambienteName;
            }
            
            form.action = `${baseUrl}/${id}`;
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        };

        window.closeDeleteModal = function() {
            const modal = document.getElementById('deleteModal');
            if (modal) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }
        };

        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');
            const searchForm = document.getElementById('searchForm');
            if (searchInput && searchForm) {
                // Igual que en Ambientes: buscar solo al enviar el formulario
                searchInput.addEventListener('keypress', function(event) {
                    if (event.key === 'Enter') {
                        event.preventDefault();
                        searchForm.submit();
                    }
                });
            }
        });
    </script>
@endsection
