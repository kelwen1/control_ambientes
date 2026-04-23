@extends('layouts.app')

@section('title', 'Fichas')

@section('content')
    <!-- Bienvenida -->
    <div class="mb-6 sm:mb-8 animate-fade-slide-up">
        <h1 class="text-2xl sm:text-3xl font-bold text-[#39B54A] mb-2 tracking-tight">
            Gestión de Fichas
        </h1>
        <p class="text-gray-600 text-sm sm:text-base">Administra y consulta las fichas de formación</p>
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

    <!-- Tabla de fichas -->
    <div class="card-premium bg-white rounded-xl shadow-card overflow-hidden hover:shadow-card-hover transition-shadow duration-300 max-w-full min-w-0">
        <div class="p-4 sm:p-6 md:p-8 border-b border-gray-200 flex flex-col sm:flex-row sm:flex-wrap justify-between items-stretch sm:items-center gap-4 min-w-0">
            <h2 class="text-xl sm:text-2xl font-bold text-gray-800 flex items-center gap-2">
                <span class="text-2xl">📋</span>
                Registros de Fichas
            </h2>
            
            <div class="flex flex-col sm:flex-row sm:flex-wrap gap-2 sm:gap-3 w-full min-w-0 sm:w-auto sm:justify-end sm:items-center">
                <!-- Buscador (todos los roles) -->
                <form method="GET" action="{{ route('fichas.index') }}" class="flex flex-col sm:flex-row gap-2 flex-1 min-w-0 sm:min-w-[12rem] sm:max-w-xl" id="searchForm">
                    <input type="text" 
                           name="search" 
                           id="searchInput"
                           value="{{ $search }}"
                           placeholder="Buscar por número de ficha..."
                           inputmode="numeric"
                           autocomplete="off"
                           class="w-full min-w-0 flex-1 px-4 py-2 border-2 border-gray-300 rounded-xl focus:border-[#39B54A] focus:ring-2 focus:ring-[#39B54A]/20 focus:outline-none transition-all duration-200 text-sm">
                    <button type="submit" 
                            class="btn-primary w-full sm:w-auto shrink-0 px-4 py-2 bg-[#39B54A] text-white rounded-xl hover:bg-[#2d8f3a] hover:shadow-glow transition-all duration-200 shadow-md font-medium">
                        🔍 Buscar
                    </button>
                </form>
                @unless(auth()->user()->isCoordinatorOnly())
                <a href="{{ route('fichas.create') }}" 
                   class="btn-primary px-4 sm:px-6 py-2 sm:py-3 bg-[#39B54A] text-white rounded-xl hover:bg-[#2d8f3a] hover:shadow-glow transition-all duration-200 shadow-md text-sm sm:text-base font-semibold inline-flex items-center justify-center gap-2 button-loading w-full sm:w-auto">
                    <span>➕</span>
                    <span>Agregar Ficha</span>
                </a>
                @endunless
            </div>
        </div>

        @if($fichas && $fichas->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 sm:px-6 py-3 text-left text-xs sm:text-sm font-semibold text-gray-700 uppercase tracking-wider">Número Ficha</th>
                            <th class="px-4 sm:px-6 py-3 text-left text-xs sm:text-sm font-semibold text-gray-700 uppercase tracking-wider">Cant. Aprendices</th>
                            <th class="px-4 sm:px-6 py-3 text-left text-xs sm:text-sm font-semibold text-gray-700 uppercase tracking-wider">Programa</th>
                            <th class="px-4 sm:px-6 py-3 text-left text-xs sm:text-sm font-semibold text-gray-700 uppercase tracking-wider">Fecha Inicio</th>
                            <th class="px-4 sm:px-6 py-3 text-left text-xs sm:text-sm font-semibold text-gray-700 uppercase tracking-wider">Fecha Fin</th>
                            <th class="px-4 sm:px-6 py-3 text-left text-xs sm:text-sm font-semibold text-gray-700 uppercase tracking-wider">Fecha Productiva</th>
                            @unless(auth()->user()->isCoordinatorOnly())
                            <th class="sticky right-0 bg-gray-50 px-4 sm:px-6 py-3 text-left text-xs sm:text-sm font-semibold text-gray-700 uppercase tracking-wider z-10">Acciones</th>
                            @endunless
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($fichas as $ficha)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    {{ $ficha->num_ficha ?? 'N/A' }}
                                </td>
                                <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    {{ $ficha->cant_aprendices }}
                                </td>
                                <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    {{ $ficha->programa->nombre_programa ?? 'N/A' }}
                                </td>
                                <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    {{ $ficha->fecha_inicio ? \Carbon\Carbon::parse($ficha->fecha_inicio)->format('d/m/Y') : 'N/A' }}
                                </td>
                                <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    {{ $ficha->fecha_fin ? \Carbon\Carbon::parse($ficha->fecha_fin)->format('d/m/Y') : 'N/A' }}
                                </td>
                                <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    {{ $ficha->fecha_productiva ? \Carbon\Carbon::parse($ficha->fecha_productiva)->format('d/m/Y') : 'N/A' }}
                                </td>
                                @unless(auth()->user()->isCoordinatorOnly())
                                <td class="sticky right-0 bg-white px-4 sm:px-6 py-4 whitespace-nowrap text-sm font-medium z-10">
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('fichas.edit', $ficha->id_ficha) }}" 
                                           class="px-3 py-1.5 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors text-xs sm:text-sm font-medium">
                                            ✏️ Editar
                                        </a>
                                        <button onclick="openDeleteModal({{ $ficha->id_ficha }}, {{ json_encode($ficha->num_ficha ?? 'N/A') }})" 
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
            @if($fichas->hasPages())
                <div class="px-6 sm:px-8 py-4 border-t border-gray-200">
                    <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                        <div class="text-sm text-gray-700">
                            Mostrando {{ $fichas->firstItem() }} a {{ $fichas->lastItem() }} de {{ $fichas->total() }} registros
                        </div>
                        <div class="flex items-center gap-2">
                            {{ $fichas->links() }}
                        </div>
                    </div>
                </div>
            @endif
        @else
            <div class="p-12 text-center">
                <span class="text-6xl mb-4 block">📋</span>
                <h3 class="text-xl font-semibold text-gray-700 mb-2">No hay fichas registradas</h3>
                <p class="text-gray-500 mb-6">
                    @if($search)
                        No se encontraron fichas con el número "{{ $search }}"
                    @else
                        Comienza agregando una nueva ficha
                    @endif
                </p>
                @if(!$search && !auth()->user()->isCoordinatorOnly())
                    <a href="{{ route('fichas.create') }}" 
                       class="inline-block px-6 py-3 bg-[#39B54A] text-white rounded-lg font-semibold hover:bg-[#2d8f3a] transition-colors shadow-lg transform hover:scale-105">
                        Agregar Primera Ficha
                    </a>
                @endif
            </div>
        @endif
    </div>

    <!-- Modal de Confirmación de Eliminación -->
    <div id="deleteModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 modal-overlay">
        <div class="modal-container glass-container rounded-2xl shadow-2xl p-6 sm:p-8 w-full max-w-md">
            <div class="text-center">
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-100 mb-4">
                    <svg class="h-10 w-10 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                
                <h3 class="text-2xl font-bold text-gray-800 mb-2">¿Eliminar Ficha?</h3>
                
                <p class="text-gray-600 mb-6">
                    ¿Estás seguro de que deseas eliminar la ficha <span id="fichaNum" class="font-semibold"></span>? Esta acción no se puede deshacer.
                </p>
                
                <div class="flex gap-3 sm:gap-4">
                    <button onclick="closeDeleteModal()" 
                            class="flex-1 bg-gray-300 text-gray-700 py-3 rounded-lg font-semibold text-base hover:bg-gray-400 transition-colors shadow-lg">
                        Cancelar
                    </button>
                    <form id="deleteForm" method="POST" class="flex-1" data-destroy-template="{{ route('fichas.destroy', ['id' => '__ID__']) }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" 
                                class="w-full bg-red-600 text-white py-3 rounded-lg font-semibold text-base hover:bg-red-700 transition-colors shadow-lg transform hover:scale-105">
                            Eliminar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <style>
        .modal-overlay {
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(4px);
            -webkit-backdrop-filter: blur(4px);
        }
        .modal-container {
            animation: modalFadeIn 0.3s ease-out;
        }
        @keyframes modalFadeIn {
            from {
                opacity: 0;
                transform: scale(0.95) translateY(-10px);
            }
            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }
        .glass-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }
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

    <!-- Funciones inline para el modal de eliminación y búsqueda -->
    <script>
        // Ejecutar al final para sobrescribir cualquier otra definición
        (function() {
            // Función para abrir modal de eliminación - DEFINIR DIRECTAMENTE
            window.openDeleteModal = function(id, fichaNum) {
                const modal = document.getElementById('deleteModal');
                const form = document.getElementById('deleteForm');
                const fichaNumElement = document.getElementById('fichaNum');

                if (!modal || !form) {
                    return;
                }

                if (fichaNumElement) {
                    fichaNumElement.textContent = fichaNum;
                }

                var tpl = form.dataset.destroyTemplate || '';
                form.action = tpl.replace('__ID__', String(id));

                modal.classList.remove('hidden');
                modal.classList.add('flex');
            };
            
            // Función para cerrar modal de eliminación
            window.closeDeleteModal = function() {
                const modal = document.getElementById('deleteModal');
                if (modal) {
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                }
            };
            
            // Búsqueda: el filtrado se ejecuta solo al enviar el formulario
            // (botón Buscar o tecla Enter). No se recarga automáticamente
            // al borrar el campo, igual que en Ambientes.
            document.addEventListener('DOMContentLoaded', function() {
                const searchInput = document.getElementById('searchInput');
                const searchForm = document.getElementById('searchForm');
                if (searchInput && searchForm) {
                    searchInput.addEventListener('keypress', function(event) {
                        if (event.key === 'Enter') {
                            event.preventDefault();
                            searchForm.submit();
                        }
                    });
                }
            });
        })();
        
        // Event listeners para cerrar el modal (ejecutar inmediatamente)
        (function() {
            // Cerrar con ESC
            document.addEventListener('keydown', function(event) {
                if (event.key === 'Escape') {
                    const modal = document.getElementById('deleteModal');
                    if (modal && !modal.classList.contains('hidden')) {
                        closeDeleteModal();
                    }
                }
            });
            
            // Cerrar al hacer clic fuera (cuando el DOM esté listo)
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', function() {
                    const deleteModal = document.getElementById('deleteModal');
                    if (deleteModal) {
                        deleteModal.addEventListener('click', function(event) {
                            if (event.target === this) {
                                closeDeleteModal();
                            }
                        });
                    }
                });
            } else {
                // DOM ya está listo
                const deleteModal = document.getElementById('deleteModal');
                if (deleteModal) {
                    deleteModal.addEventListener('click', function(event) {
                        if (event.target === this) {
                            closeDeleteModal();
                        }
                    });
                }
            }
        })();
    </script>
@endsection
