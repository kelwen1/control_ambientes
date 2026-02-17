@extends('layouts.app')

@section('title', 'Ambientes')

@section('content')
    <!-- Bienvenida -->
    <div class="mb-6 sm:mb-8">
        <h1 class="text-2xl sm:text-3xl font-bold text-[#39B54A] mb-2">
            Gestión de Ambientes
        </h1>
        <p class="text-gray-600 text-sm sm:text-base">Administra y consulta los ambientes disponibles para asignación de fichas</p>
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

    <!-- Tabla de ambientes -->
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <div class="p-6 sm:p-8 border-b border-gray-200 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <h2 class="text-xl sm:text-2xl font-bold text-gray-800 flex items-center gap-2">
                <span class="text-2xl">🏛️</span>
                Registros de Ambientes
            </h2>
            
            <div class="flex gap-3 w-full sm:w-auto">
                <!-- Buscador (todos los roles) -->
                <form method="GET" action="{{ route('ambientes.index') }}" class="flex-1 sm:flex-initial flex gap-2" id="searchForm">
                    <input type="text" 
                           name="search" 
                           id="searchInput"
                           value="{{ $search ?? '' }}"
                           placeholder="Buscar por ambiente, ficha, estado, día, horario..."
                           pattern="[0-9]*"
                           inputmode="numeric"
                           class="flex-1 px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-[#39B54A] focus:outline-none transition-colors text-sm">
                    <button type="submit" 
                            class="px-4 py-2 bg-[#39B54A] text-white rounded-lg hover:bg-[#2d8f3a] transition-colors shadow-lg font-medium">
                        🔍 Buscar
                    </button>
                </form>
                
                <a href="{{ route('ambientes.disponibilidad') }}" 
                   class="px-3 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors shadow-lg text-xs sm:text-sm font-semibold flex items-center gap-1.5">
                    <span>📅</span>
                    <span>Disponibilidad por jornada</span>
                </a>
                <button type="button" 
                        onclick="openExportModal('exportModalAmbientes')"
                        class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors shadow-lg text-sm font-semibold flex items-center gap-2">
                    <span>📥</span>
                    <span>Reportes</span>
                </button>
                @unless(auth()->user()->isCoordinator())
                <a href="{{ route('reservas.create') }}" 
                   class="px-4 sm:px-6 py-2 sm:py-3 bg-[#39B54A] text-white rounded-lg hover:bg-[#2d8f3a] transition-all shadow-lg transform hover:scale-105 text-sm sm:text-base font-semibold flex items-center gap-2 button-loading">
                    <span>➕</span>
                    <span>Asignar Ambiente</span>
                </a>
                @endunless
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                    <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 sm:px-6 py-3 text-left text-xs sm:text-sm font-semibold text-gray-700 uppercase tracking-wider">Número Ambiente</th>
                        <th class="px-4 sm:px-6 py-3 text-left text-xs sm:text-sm font-semibold text-gray-700 uppercase tracking-wider">Estado</th>
                        <th class="px-4 sm:px-6 py-3 text-left text-xs sm:text-sm font-semibold text-gray-700 uppercase tracking-wider">Ficha Asignada</th>
                        <th class="px-4 sm:px-6 py-3 text-left text-xs sm:text-sm font-semibold text-gray-700 uppercase tracking-wider">Día</th>
                        <th class="px-4 sm:px-6 py-3 text-left text-xs sm:text-sm font-semibold text-gray-700 uppercase tracking-wider">Horario</th>
                        <th class="px-4 sm:px-6 py-3 text-left text-xs sm:text-sm font-semibold text-gray-700 uppercase tracking-wider">Fechas</th>
                        @unless(auth()->user()->isCoordinator())
                        <th class="sticky right-0 bg-gray-50 px-4 sm:px-6 py-3 text-left text-xs sm:text-sm font-semibold text-gray-700 uppercase tracking-wider z-10">Acciones</th>
                        @endunless
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @if($reservas && $reservas->count() > 0)
                        @foreach($reservas as $reserva)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm font-medium text-[#39B54A]">
                                    {{ $reserva->num_ambiente ?? 'N/A' }}
                                </td>
                                <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    @if($reserva->nombre_estado)
                                        @if($reserva->nombre_estado == 'Activa')
                                            <span class="px-2 py-1 rounded-full bg-yellow-100 text-yellow-800">Ocupado</span>
                                        @elseif($reserva->nombre_estado == 'Cancelada')
                                            <span class="px-2 py-1 rounded-full bg-red-100 text-red-800">Cancelada</span>
                                        @elseif($reserva->nombre_estado == 'Finalizada')
                                            <span class="px-2 py-1 rounded-full bg-gray-100 text-gray-800">Finalizada</span>
                                        @else
                                            <span class="px-2 py-1 rounded-full bg-green-100 text-green-800">Disponible</span>
                                        @endif
                                    @else
                                        <span class="px-2 py-1 rounded-full bg-green-100 text-green-800">Disponible</span>
                                    @endif
                                </td>
                                <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    {{ $reserva->num_ficha ?? 'N/A' }}
                                </td>
                                <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    @if($reserva->dia_semana == 'lunes')
                                        Lunes a Viernes
                                    @elseif($reserva->dia_semana == 'sabado')
                                        Sábados
                                    @else
                                        {{ ucfirst($reserva->dia_semana ?? 'N/A') }}
                                    @endif
                                </td>
                                <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    @if($reserva->hora_inicio && $reserva->hora_fin)
                                        {{ \Carbon\Carbon::parse($reserva->hora_inicio)->format('g:i A') }} - {{ \Carbon\Carbon::parse($reserva->hora_fin)->format('g:i A') }}
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    @if($reserva->fecha_inicio && $reserva->fecha_fin)
                                        {{ \Carbon\Carbon::parse($reserva->fecha_inicio)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($reserva->fecha_fin)->format('d/m/Y') }}
                                    @else
                                        N/A
                                    @endif
                                </td>
                                @unless(auth()->user()->isCoordinator())
                                <td class="sticky right-0 bg-white px-4 sm:px-6 py-4 whitespace-nowrap text-sm font-medium z-10">
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('reservas.edit', $reserva->id_reserva) }}" 
                                           class="px-3 py-1.5 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors text-xs sm:text-sm font-medium">
                                            ✏️ Editar
                                        </a>
                                        <button onclick="openDeleteModal({{ $reserva->id_reserva }})" 
                                                class="px-3 py-1.5 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors text-xs sm:text-sm font-medium">
                                            🗑️ Eliminar
                                        </button>
                                    </div>
                                </td>
                                @endunless
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="{{ auth()->user()->isCoordinator() ? 6 : 7 }}" class="px-4 sm:px-6 py-12 text-center">
                                <span class="text-6xl mb-4 block">🏛️</span>
                                <h3 class="text-xl font-semibold text-gray-700 mb-2">No hay reservas registradas</h3>
                                <p class="text-gray-500 mb-6">
                                    @if(isset($search) && $search)
                                        No se encontraron reservas para el ambiente "{{ $search }}"
                                    @else
                                        Comienza asignando un ambiente a una ficha
                                    @endif
                                </p>
                                @if((!isset($search) || !$search) && !auth()->user()->isCoordinator())
                                    <a href="{{ route('reservas.create') }}" 
                                       class="inline-block px-6 py-3 bg-[#39B54A] text-white rounded-lg font-semibold hover:bg-[#2d8f3a] transition-colors shadow-lg transform hover:scale-105">
                                        Crear Primera Reserva
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @endif
            </table>
        </div>

        <!-- Controles de paginación -->
        @if($reservas && $reservas->hasPages())
            <div class="px-6 sm:px-8 py-4 border-t border-gray-200">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="text-sm text-gray-700">
                        Mostrando {{ $reservas->firstItem() }} a {{ $reservas->lastItem() }} de {{ $reservas->total() }} registros
                    </div>
                    <div class="flex items-center gap-2">
                        {{ $reservas->links() }}
                    </div>
                </div>
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
                
                <h3 class="text-2xl font-bold text-gray-800 mb-2">¿Eliminar Reserva?</h3>
                
                <p class="text-gray-600 mb-6">
                    ¿Estás seguro de que deseas eliminar esta reserva? Esta acción no se puede deshacer.
                </p>
                
                <div class="flex gap-3 sm:gap-4">
                    <button onclick="closeDeleteModal()" 
                            class="flex-1 bg-gray-300 text-gray-700 py-3 rounded-lg font-semibold text-base hover:bg-gray-400 transition-colors shadow-lg">
                        Cancelar
                    </button>
                    <form id="deleteForm" method="POST" class="flex-1" data-base-url="{{ url('/reservas') }}">
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

    @include('components.export-modal', [
        'modalId' => 'exportModalAmbientes',
        'exportPdfUrl' => route('ambientes.export', ['search' => $search ?? '']),
    ])

    <style>
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
        .modal-overlay {
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(4px);
            -webkit-backdrop-filter: blur(4px);
        }
        .modal-container {
            animation: modalFadeIn 0.3s ease-out;
        }
        .glass-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
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

    {{-- @vite(['resources/js/ambientes.js']) --}}
    @if(app()->environment('local'))
    <script type="module" src="{{ url('/js/ambientes.js') }}"></script>
    @endif
@endsection
