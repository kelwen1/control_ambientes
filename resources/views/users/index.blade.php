@extends('layouts.app')

@section('title', 'Usuarios')

@section('content')
    <!-- Bienvenida -->
    <div class="mb-6 sm:mb-8">
        <h1 class="text-2xl sm:text-3xl font-bold text-[#39B54A] mb-2">
            Gestión de Usuarios
        </h1>
        <p class="text-gray-600 text-sm sm:text-base">Administra las cuentas de acceso de profesores y usuarios</p>
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

    <!-- Tabla de usuarios -->
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <div class="p-6 sm:p-8 border-b border-gray-200 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <h2 class="text-xl sm:text-2xl font-bold text-gray-800 flex items-center gap-2">
                <span class="text-2xl">👥</span>
                Registros de Usuarios
            </h2>
            
            <div class="flex gap-3 w-full sm:w-auto">
                <!-- Buscador -->
                <form method="GET" action="{{ route('users.index') }}" class="flex-1 sm:flex-initial flex gap-2" id="searchForm">
                    <input type="text" 
                           name="search" 
                           id="searchInput"
                           value="{{ $search }}"
                           placeholder="Buscar por nombre, correo o usuario..."
                           class="flex-1 px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-[#39B54A] focus:outline-none transition-colors text-sm">
                    <button type="submit" 
                            class="px-4 py-2 bg-[#39B54A] text-white rounded-lg hover:bg-[#2d8f3a] transition-colors shadow-lg font-medium">
                        🔍 Buscar
                    </button>
                </form>
                
                <a href="{{ route('users.create') }}" 
                   class="px-4 sm:px-6 py-2 sm:py-3 bg-[#39B54A] text-white rounded-lg hover:bg-[#2d8f3a] transition-colors shadow-lg transform hover:scale-105 text-sm sm:text-base font-semibold flex items-center gap-2">
                    <span>➕</span>
                    <span>Agregar Usuario</span>
                </a>
            </div>
        </div>

        @if($users && $users->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 sm:px-6 py-3 text-left text-xs sm:text-sm font-semibold text-gray-700 uppercase tracking-wider">Cédula</th>
                            <th class="px-4 sm:px-6 py-3 text-left text-xs sm:text-sm font-semibold text-gray-700 uppercase tracking-wider">Nombre Completo</th>
                            <th class="px-4 sm:px-6 py-3 text-left text-xs sm:text-sm font-semibold text-gray-700 uppercase tracking-wider">Correo</th>
                            <th class="px-4 sm:px-6 py-3 text-left text-xs sm:text-sm font-semibold text-gray-700 uppercase tracking-wider">Teléfono</th>
                            <th class="px-4 sm:px-6 py-3 text-left text-xs sm:text-sm font-semibold text-gray-700 uppercase tracking-wider">Usuario</th>
                            <th class="px-4 sm:px-6 py-3 text-left text-xs sm:text-sm font-semibold text-gray-700 uppercase tracking-wider">Rol</th>
                            <th class="sticky right-0 bg-gray-50 px-4 sm:px-6 py-3 text-left text-xs sm:text-sm font-semibold text-gray-700 uppercase tracking-wider z-10">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($users as $user)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-700 font-medium">
                                    {{ $user->id_cedula }}
                                </td>
                                <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    {{ $user->nombre }} {{ $user->apellido }}
                                </td>
                                <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    {{ $user->correo }}
                                </td>
                                <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    {{ $user->telefono ?? 'N/A' }}
                                </td>
                                <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    {{ $user->user }}
                                </td>
                                <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    @if($user->id_rol == 1)
                                        <span class="px-2 py-1 bg-purple-100 text-purple-800 rounded-full text-xs font-medium">Administrador</span>
                                    @elseif($user->id_rol == 2)
                                        <span class="px-2 py-1 bg-amber-100 text-amber-800 rounded-full text-xs font-medium">Coordinador</span>
                                    @else
                                        <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-medium">Usuario</span>
                                    @endif
                                </td>
                                <td class="sticky right-0 bg-white px-4 sm:px-6 py-4 whitespace-nowrap text-sm font-medium z-10">
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('users.edit', $user->id_cedula) }}" 
                                           class="px-3 py-1.5 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors text-xs sm:text-sm font-medium">
                                            ✏️ Editar
                                        </a>
                                        <button onclick="openDeleteModal('{{ $user->id_cedula }}', '{{ $user->nombre }} {{ $user->apellido }}')" 
                                                class="px-3 py-1.5 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors text-xs sm:text-sm font-medium">
                                            🗑️ Eliminar
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Controles de paginación -->
            @if($users->hasPages())
                <div class="px-6 sm:px-8 py-4 border-t border-gray-200">
                    <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                        <div class="text-sm text-gray-700">
                            Mostrando {{ $users->firstItem() }} a {{ $users->lastItem() }} de {{ $users->total() }} registros
                        </div>
                        <div class="flex items-center gap-2">
                            {{ $users->links() }}
                        </div>
                    </div>
                </div>
            @endif
        @else
            <div class="p-12 text-center">
                <span class="text-6xl mb-4 block">👥</span>
                <h3 class="text-xl font-semibold text-gray-700 mb-2">No hay usuarios registrados</h3>
                <p class="text-gray-500 mb-6">
                    @if($search)
                        No se encontraron usuarios con "{{ $search }}"
                    @else
                        Comienza agregando un nuevo usuario
                    @endif
                </p>
                @if(!$search)
                    <a href="{{ route('users.create') }}" 
                       class="inline-block px-6 py-3 bg-[#39B54A] text-white rounded-lg font-semibold hover:bg-[#2d8f3a] transition-colors shadow-lg transform hover:scale-105">
                        Agregar Primer Usuario
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
                
                <h3 class="text-2xl font-bold text-gray-800 mb-2">¿Eliminar Usuario?</h3>
                
                <p class="text-gray-600 mb-6">
                    ¿Estás seguro de que deseas eliminar al usuario <span id="userName" class="font-semibold"></span>? Esta acción no se puede deshacer.
                </p>
                
                <div class="flex gap-3 sm:gap-4">
                    <button onclick="closeDeleteModal()" 
                            class="flex-1 bg-gray-300 text-gray-700 py-3 rounded-lg font-semibold text-base hover:bg-gray-400 transition-colors shadow-lg">
                        Cancelar
                    </button>
                    <form id="deleteForm" method="POST" class="flex-1">
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

    <script>
        window.openDeleteModal = function(id, userName) {
            const baseUrl = window.location.origin + '/users';
            const modal = document.getElementById('deleteModal');
            const form = document.getElementById('deleteForm');
            const userNameElement = document.getElementById('userName');
            
            if (!modal || !form) return;
            
            if (userNameElement) {
                userNameElement.textContent = userName;
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

