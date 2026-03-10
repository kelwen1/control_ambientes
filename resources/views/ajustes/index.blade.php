@extends('layouts.app')

@section('title', 'Ajustes')

@section('content')
    <!-- Bienvenida -->
    <div class="mb-6 sm:mb-8">
        <h1 class="text-2xl sm:text-3xl font-bold text-[#39B54A] mb-2">
            Ajustes de Perfil
        </h1>
        <p class="text-gray-600 text-sm sm:text-base">Gestiona tu información personal y configuración de cuenta</p>
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

    <!-- Información del Perfil -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Información Personal -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="p-6 sm:p-8 border-b border-gray-200 bg-gradient-to-r from-[#39B54A] to-green-600">
                <h2 class="text-xl sm:text-2xl font-bold text-white flex items-center gap-2">
                    <span class="text-2xl">👤</span>
                    Información Personal
                </h2>
            </div>
            <div class="p-6 sm:p-8 space-y-4">
                <!-- Nombre -->
                <div class="border-b border-gray-200 pb-4">
                    <label class="block text-sm font-medium text-gray-600 mb-1">Nombre</label>
                    <div class="flex items-center justify-between">
                        <p class="text-gray-800 font-semibold">{{ $user->nombre ?? 'No especificado' }}</p>
                        <button onclick="openEditModal('nombre')" 
                                class="px-3 py-1.5 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors text-xs sm:text-sm font-medium">
                            ✏️ Editar
                        </button>
                    </div>
                </div>

                <!-- Apellido -->
                <div class="border-b border-gray-200 pb-4">
                    <label class="block text-sm font-medium text-gray-600 mb-1">Apellido</label>
                    <div class="flex items-center justify-between">
                        <p class="text-gray-800 font-semibold">{{ $user->apellido ?? 'No especificado' }}</p>
                        <button onclick="openEditModal('apellido')" 
                                class="px-3 py-1.5 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors text-xs sm:text-sm font-medium">
                            ✏️ Editar
                        </button>
                    </div>
                </div>

                <!-- Cédula -->
                <div class="border-b border-gray-200 pb-4">
                    <label class="block text-sm font-medium text-gray-600 mb-1">Cédula</label>
                    <p class="text-gray-800 font-semibold">{{ $user->id_cedula }}</p>
                    <p class="text-xs text-gray-500 mt-1">No se puede modificar</p>
                </div>

                <!-- Teléfono -->
                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1">Teléfono</label>
                    <div class="flex items-center justify-between">
                        <p class="text-gray-800 font-semibold">{{ $user->telefono ?? 'No especificado' }}</p>
                        <button onclick="openEditModal('telefono')" 
                                class="px-3 py-1.5 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors text-xs sm:text-sm font-medium">
                            ✏️ Editar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Información de Cuenta -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="p-6 sm:p-8 border-b border-gray-200 bg-gradient-to-r from-[#39B54A] to-green-600">
                <h2 class="text-xl sm:text-2xl font-bold text-white flex items-center gap-2">
                    <span class="text-2xl">🔐</span>
                    Información de Cuenta
                </h2>
            </div>
            <div class="p-6 sm:p-8 space-y-4">
                <!-- Correo -->
                <div class="border-b border-gray-200 pb-4">
                    <label class="block text-sm font-medium text-gray-600 mb-1">Correo Electrónico</label>
                    <div class="flex items-center justify-between">
                        <p class="text-gray-800 font-semibold">{{ $user->correo ?? 'No especificado' }}</p>
                        <button onclick="openEditModal('correo')" 
                                class="px-3 py-1.5 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors text-xs sm:text-sm font-medium">
                            ✏️ Editar
                        </button>
                    </div>
                </div>

                <!-- Usuario -->
                <div class="border-b border-gray-200 pb-4">
                    <label class="block text-sm font-medium text-gray-600 mb-1">Usuario</label>
                    <div class="flex items-center justify-between">
                        <p class="text-gray-800 font-semibold">{{ $user->user ?? 'No especificado' }}</p>
                        <button onclick="openEditModal('usuario')" 
                                class="px-3 py-1.5 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors text-xs sm:text-sm font-medium">
                            ✏️ Editar
                        </button>
                    </div>
                </div>

                <!-- Rol -->
                <div class="border-b border-gray-200 pb-4">
                    <label class="block text-sm font-medium text-gray-600 mb-1">Rol</label>
                    <p class="text-gray-800 font-semibold">
                        @php $idRol = (int) $user->id_rol; @endphp
                        @if($idRol === config('roles.ids.administrador', 1))
                            <span class="px-2 py-1 bg-purple-100 text-purple-800 rounded-full text-xs font-medium">administrador</span>
                        @elseif($idRol === config('roles.ids.coordinacion_L', 2))
                            <span class="px-2 py-1 bg-amber-100 text-amber-800 rounded-full text-xs font-medium">coordinacion_L</span>
                        @elseif($idRol === config('roles.ids.coordinacion', 3))
                            <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-medium">coordinacion</span>
                        @else
                            <span class="px-2 py-1 bg-gray-100 text-gray-800 rounded-full text-xs font-medium">{{ $user->persona->rol->rol ?? 'instructor' }}</span>
                        @endif
                    </p>
                    <p class="text-xs text-gray-500 mt-1">No se puede modificar</p>
                </div>

                <!-- Contraseña -->
                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1">Contraseña</label>
                    <button onclick="openEditModal('contraseña')" 
                            class="px-4 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600 transition-colors text-sm font-medium">
                        🔑 Cambiar Contraseña
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modales para editar campos -->
    @include('ajustes.modals.edit-nombre')
    @include('ajustes.modals.edit-apellido')
    @include('ajustes.modals.edit-correo')
    @include('ajustes.modals.edit-telefono')
    @include('ajustes.modals.edit-usuario')
    @include('ajustes.modals.edit-contraseña')

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
    </style>

    <script>
        // Función para abrir modales de edición
        window.openEditModal = function(field) {
            const modalMap = {
                'nombre': 'editNombreModal',
                'apellido': 'editApellidoModal',
                'telefono': 'editTelefonoModal',
                'correo': 'editCorreoModal',
                'usuario': 'editUsuarioModal',
                'contraseña': 'editContraseñaModal'
            };
            
            const modalId = modalMap[field];
            if (modalId) {
                const modal = document.getElementById(modalId);
                if (modal) {
                    modal.classList.remove('hidden');
                    modal.classList.add('flex');
                }
            }
        };

        window.closeModal = function(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                const form = modal.querySelector('form');
                if (form) {
                    form.reset();
                }
            }
        };

        document.addEventListener('DOMContentLoaded', function() {
            document.addEventListener('click', function(event) {
                if (event.target.classList.contains('modal-overlay')) {
                    const modal = event.target.closest('.modal-overlay');
                    if (modal) {
                        closeModal(modal.id);
                        const form = modal.querySelector('form');
                        if (form) {
                            form.reset();
                        }
                    }
                }
            });
        });
    </script>
@endsection
