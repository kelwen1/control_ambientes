@extends('layouts.app')

@section('title', 'Ambientes')

@section('content')
    <!-- Bienvenida -->
    <div class="mb-6 sm:mb-8">
        <h1 class="text-2xl sm:text-3xl font-bold text-[#39B54A] mb-2">
            Horarios
        </h1>
        <p class="text-gray-600 text-sm sm:text-base">Administre y consulte los ambientes disponibles para la asignación de fichas.</p>
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
    <div class="card-premium bg-white rounded-xl shadow-card overflow-hidden hover:shadow-card-hover transition-shadow duration-300 max-w-full min-w-0">
        <div class="p-4 sm:p-6 md:p-8 border-b border-gray-200 flex flex-col sm:flex-row sm:flex-wrap sm:items-center sm:justify-between gap-4 min-w-0">
            <h2 class="text-xl sm:text-2xl font-bold text-gray-800 flex items-center gap-2 shrink-0">
                <span class="text-2xl">🏛️</span>
                Programación
            </h2>

            <div class="flex flex-col sm:flex-row sm:flex-wrap gap-2 sm:gap-3 w-full min-w-0 sm:w-auto sm:justify-end sm:items-center sm:ml-auto">
                <form method="GET" action="{{ route('ambientes.index') }}" class="flex flex-col sm:flex-row gap-2 w-full min-w-0 sm:w-auto sm:max-w-xl shrink-0" id="searchForm">
                    <input type="text" 
                           name="search" 
                           id="searchInput"
                           value="{{ $search ?? '' }}"
                           placeholder="Buscar por número de ambiente..."
                           pattern="[0-9]*"
                           inputmode="numeric"
                           autocomplete="off"
                           class="w-full min-w-0 flex-1 px-4 py-2 border-2 border-gray-300 rounded-xl focus:border-[#39B54A] focus:ring-2 focus:ring-[#39B54A]/20 focus:outline-none transition-all duration-200 text-sm">
                    <button type="submit" 
                            class="btn-primary w-full sm:w-auto shrink-0 px-4 py-2 bg-[#39B54A] text-white rounded-xl hover:bg-[#2d8f3a] hover:shadow-glow transition-all duration-200 shadow-md font-medium">
                        🔍 Buscar
                    </button>
                </form>
                <a href="{{ route('ambientes.disponibilidad') }}" 
                   class="px-3 py-2.5 bg-gray-600 text-white rounded-xl hover:bg-gray-700 transition-colors shadow-md text-xs sm:text-sm font-semibold inline-flex items-center justify-center gap-1.5 w-full sm:w-auto sm:min-w-0 text-center">
                    <span aria-hidden="true">📅</span>
                    <span class="sm:hidden">Disp. instructor</span>
                    <span class="hidden sm:inline">Disponibilidad de instructor</span>
                </a>
                <a href="{{ route('ambientes.disponibilidad-ambiente') }}" 
                   class="px-3 py-2.5 bg-gray-600 text-white rounded-xl hover:bg-gray-700 transition-colors shadow-md text-xs sm:text-sm font-semibold inline-flex items-center justify-center gap-1.5 w-full sm:w-auto sm:min-w-0 text-center">
                    <span aria-hidden="true">🏛️</span>
                    <span class="sm:hidden">Disp. ambiente</span>
                    <span class="hidden sm:inline">Disponibilidad de ambiente</span>
                </a>
                <a href="{{ route('reservas.create') }}" 
                   class="btn-primary px-4 sm:px-6 py-2 sm:py-3 bg-[#39B54A] text-white rounded-xl hover:bg-[#2d8f3a] hover:shadow-glow transition-all duration-200 shadow-md text-sm sm:text-base font-semibold inline-flex items-center justify-center gap-2 button-loading w-full sm:w-auto">
                    <span>➕</span>
                    <span>Asignar Ambiente</span>
                </a>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                    <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 sm:px-6 py-3 text-left text-xs sm:text-sm font-semibold text-gray-700 uppercase tracking-wider">Ambiente</th>
                        <th class="px-4 sm:px-6 py-3 text-left text-xs sm:text-sm font-semibold text-gray-700 uppercase tracking-wider">Estado</th>
                        <th class="px-4 sm:px-6 py-3 text-left text-xs sm:text-sm font-semibold text-gray-700 uppercase tracking-wider">Ficha</th>
                        <th class="px-4 sm:px-6 py-3 text-left text-xs sm:text-sm font-semibold text-gray-700 uppercase tracking-wider">Día</th>
                        <th class="px-4 sm:px-6 py-3 text-left text-xs sm:text-sm font-semibold text-gray-700 uppercase tracking-wider">Horario</th>
                        <th class="px-4 sm:px-6 py-3 text-left text-xs sm:text-sm font-semibold text-gray-700 uppercase tracking-wider">Fecha</th>
                        @unless(auth()->user()->isCoordinatorOnly())
                        <th class="sticky right-0 bg-gray-50 px-4 sm:px-6 py-3 text-left text-xs sm:text-sm font-semibold text-gray-700 uppercase tracking-wider z-10">Acciones</th>
                        @endunless
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @if($reservas && $reservas->count() > 0)
                        @foreach($reservas as $reserva)
                            <tr class="table-row-hover hover:bg-gray-50/80">
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
                                <td class="px-4 sm:px-6 py-4 text-sm text-gray-700 whitespace-normal sm:whitespace-nowrap max-w-[14rem] sm:max-w-none">
                                    @php
                                        $diasLabels = ['lunes' => 'Lunes', 'martes' => 'Martes', 'miercoles' => 'Miércoles', 'jueves' => 'Jueves', 'viernes' => 'Viernes', 'sabado' => 'Sábado', 'domingo' => 'Domingo'];
                                    @endphp
                                    {{ $reserva->etiqueta_dias_listado ?? ($diasLabels[$reserva->dia_semana ?? ''] ?? ucfirst($reserva->dia_semana ?? 'N/A')) }}
                                </td>
                                <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    @php
                                        $mapa = [1 => 'Mañana', 2 => 'Tarde', 3 => 'Noche', 4 => 'Fin de semana'];
                                        $label = $mapa[$reserva->id_jornada ?? 0] ?? 'N/A';
                                    @endphp
                                    {{ $label }}
                                </td>
                                <td class="px-4 sm:px-6 py-4 text-sm text-gray-700 align-top">
                                    @php
                                        $diaModalLabel = $reserva->modal_etiqueta_dia ?? ($diasLabels[$reserva->dia_semana ?? ''] ?? ucfirst($reserva->dia_semana ?? 'Día'));
                                    @endphp
                                    @if($reserva->fecha_inicio && $reserva->fecha_fin && !empty($reserva->fechas_clase_modal))
                                        <button type="button"
                                                class="btn-fechas-clase inline-flex flex-col gap-0.5 px-3 py-2 rounded-xl border-2 border-emerald-200 bg-emerald-50/90 text-emerald-900 text-xs sm:text-sm font-semibold hover:bg-emerald-100 hover:border-emerald-300 transition-all text-left max-w-full"
                                                title="Ver todas las fechas de clase en el periodo"
                                                data-dia="{{ e($diaModalLabel) }}"
                                                data-fechas='@json($reserva->fechas_clase_modal)'>
                                            <span class="flex items-center gap-1.5 text-sm text-emerald-900">
                                                <span aria-hidden="true">📋</span>
                                                <span>Lista de días</span>
                                            </span>
                                            <span class="text-[11px] sm:text-xs text-emerald-800/75 font-medium pl-6">{{ $reserva->fecha_primera_clase }} — {{ $reserva->fecha_ultima_clase }}</span>
                                        </button>
                                        @if(!empty($reserva->id_reserva_original))
                                            <button type="button" class="btn-modal-liberado block mt-1 text-[#39B54A] hover:text-[#2d8f3a] hover:underline font-medium cursor-pointer bg-transparent border-none p-0 text-xs"
                                                    data-id="{{ (int) $reserva->id_reserva_original }}">
                                                (Día liberado)
                                            </button>
                                        @endif
                                    @elseif($reserva->fecha_inicio && $reserva->fecha_fin)
                                        <span class="text-gray-500 text-xs">Sin fechas de clase en el rango</span>
                                        @if(!empty($reserva->id_reserva_original))
                                            <button type="button" class="btn-modal-liberado block mt-1 text-[#39B54A] hover:text-[#2d8f3a] hover:underline font-medium cursor-pointer bg-transparent border-none p-0 text-xs"
                                                    data-id="{{ (int) $reserva->id_reserva_original }}">
                                                (Día liberado)
                                            </button>
                                        @endif
                                    @else
                                        N/A
                                    @endif
                                </td>
                                @unless(auth()->user()->isCoordinatorOnly())
                                <td class="sticky right-0 bg-white px-4 sm:px-6 py-4 whitespace-nowrap text-sm font-medium z-10">
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('reservas.edit', $reserva->id_reserva) }}" 
                                           class="px-3 py-1.5 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors text-xs sm:text-sm font-medium">
                                            ✏️ Editar
                                        </a>
                                        @if(!empty($reserva->puede_eliminar_reserva))
                                            <button type="button" onclick="openDeleteModalReservas(@json($reserva->reserva_ids_grupo ?? [(int) $reserva->id_reserva]))" 
                                                    class="px-3 py-1.5 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors text-xs sm:text-sm font-medium">
                                                🗑️ Eliminar
                                            </button>
                                        @else
                                            <span class="px-3 py-1.5 bg-gray-200 text-gray-500 rounded-lg text-xs sm:text-sm font-medium cursor-not-allowed" title="No se puede eliminar mientras queden fechas de clase por cumplir">
                                                🗑️ Eliminar
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                @endunless
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="{{ auth()->user()->isCoordinatorOnly() ? 6 : 7 }}" class="px-4 sm:px-6 py-12 text-center">
                                <span class="text-6xl mb-4 block">🏛️</span>
                                <h3 class="text-xl font-semibold text-gray-700 mb-2">No hay reservas registradas</h3>
                                <p class="text-gray-500 mb-6">
                                    @if(isset($search) && $search)
                                        No se encontraron reservas para el ambiente "{{ $search }}"
                                    @else
                                        Comienza asignando un ambiente a una ficha
                                    @endif
                                </p>
                                @if(!isset($search) || !$search)
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
            <div class="px-4 sm:px-6 md:px-8 py-4 border-t border-gray-200 min-w-0">
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4 min-w-0">
                    <div class="text-sm text-gray-700 text-center sm:text-left shrink-0">
                        Mostrando {{ $reservas->firstItem() }} a {{ $reservas->lastItem() }} de {{ $reservas->total() }} registros
                    </div>
                    <div class="flex items-center justify-center gap-2 overflow-x-auto max-w-full pb-1 min-w-0">
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
                
                <h3 class="text-2xl font-bold text-gray-800 mb-2">¿Eliminar asignación?</h3>
                
                <p id="deleteModalMensaje" class="text-gray-600 mb-6">
                    ¿Estás seguro de que deseas eliminar esta asignación? Esta acción no se puede deshacer.
                </p>
                
                <div class="flex gap-3 sm:gap-4">
                    <button type="button" onclick="closeDeleteModalReservas()" 
                            class="flex-1 bg-gray-300 text-gray-700 py-3 rounded-lg font-semibold text-base hover:bg-gray-400 transition-colors shadow-lg">
                        Cancelar
                    </button>
                    <form id="deleteForm" method="POST" action="{{ route('reservas.destroy-lote') }}" class="flex-1">
                        @csrf
                        <div id="deleteIdsContainer"></div>
                        <button type="submit" 
                                class="w-full bg-red-600 text-white py-3 rounded-lg font-semibold text-base hover:bg-red-700 transition-colors shadow-lg transform hover:scale-105">
                            Eliminar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: todas las fechas de clase (día de la semana de la reserva) -->
    <div id="modalFechasClase" class="fixed inset-0 z-50 hidden items-center justify-center p-4 modal-overlay" onclick="if(event.target===this) cerrarModalFechasClase()">
        <div class="modal-container glass-container rounded-2xl shadow-2xl p-6 sm:p-8 w-full max-w-md max-h-[85vh] overflow-hidden flex flex-col" onclick="event.stopPropagation()">
            <div class="flex justify-between items-start gap-3 mb-4">
                <div>
                    <h3 class="text-xl font-bold text-gray-800">Lista de días</h3>
                    <p class="text-sm text-gray-600 mt-1">Sesiones en <span id="modal-fechas-clase-dia" class="font-semibold text-[#39B54A]"></span> durante el periodo</p>
                </div>
                <button type="button" onclick="cerrarModalFechasClase()" class="text-gray-500 hover:text-gray-700 text-2xl leading-none shrink-0" aria-label="Cerrar">&times;</button>
            </div>
            <div class="overflow-y-auto flex-1 min-h-0 border border-gray-100 rounded-xl bg-gray-50/80 p-3">
                <ol id="modal-fechas-clase-lista" class="list-decimal list-inside space-y-1.5 text-sm text-gray-800"></ol>
            </div>
            <button type="button" onclick="cerrarModalFechasClase()"
                    class="mt-5 w-full px-4 py-2.5 bg-[#39B54A] text-white rounded-xl font-semibold hover:bg-[#2d8f3a] transition-colors">
                Cerrar
            </button>
        </div>
    </div>

    <!-- Modal Reserva Original (día liberado) -->
    <div id="modalReservaOriginal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 modal-overlay" onclick="if(event.target===this) cerrarModalReservaOriginal()">
        <div class="modal-container glass-container rounded-2xl shadow-2xl p-6 sm:p-8 w-full max-w-md" onclick="event.stopPropagation()">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-bold text-gray-800">Reserva original</h3>
                <button type="button" onclick="cerrarModalReservaOriginal()" class="text-gray-500 hover:text-gray-700 text-2xl leading-none">&times;</button>
            </div>
            <p class="text-sm text-gray-600 mb-4">Reserva de la que proviene el día liberado:</p>
            <dl class="space-y-2 text-sm">
                <div class="flex flex-wrap"><dt class="font-semibold text-gray-700 w-28">Ambiente:</dt><dd id="modal-orig-ambiente" class="text-gray-800">—</dd></div>
                <div class="flex flex-wrap"><dt class="font-semibold text-gray-700 w-28">Ficha:</dt><dd id="modal-orig-ficha" class="text-gray-800">—</dd></div>
                <div class="flex flex-wrap"><dt class="font-semibold text-gray-700 w-28">Instructor:</dt><dd id="modal-orig-instructor" class="text-gray-800">—</dd></div>
                <div class="flex flex-wrap"><dt class="font-semibold text-gray-700 w-28">Día:</dt><dd id="modal-orig-dia" class="text-gray-800">—</dd></div>
                <div class="flex flex-wrap"><dt class="font-semibold text-gray-700 w-28">Jornada:</dt><dd id="modal-orig-jornada" class="text-gray-800">—</dd></div>
                <div class="flex flex-wrap"><dt class="font-semibold text-gray-700 w-28">Fechas:</dt><dd id="modal-orig-fechas" class="text-gray-800">—</dd></div>
                <div class="flex flex-wrap"><dt class="font-semibold text-gray-700 w-28">Estado:</dt><dd id="modal-orig-estado" class="text-gray-800">—</dd></div>
            </dl>
            <div class="mt-6">
                <button type="button" onclick="cerrarModalReservaOriginal()"
                        class="w-full px-4 py-2 bg-gray-600 text-white rounded-lg font-semibold hover:bg-gray-700 transition-colors">
                    Cerrar
                </button>
            </div>
        </div>
    </div>

    <script>
        function openDeleteModalReservas(ids) {
            var list = Array.isArray(ids) ? ids : [ids];
            list = list.map(function (x) { return parseInt(x, 10); }).filter(function (n) { return n > 0; });
            if (!list.length) return;
            var container = document.getElementById('deleteIdsContainer');
            if (!container) return;
            container.innerHTML = '';
            list.forEach(function (id) {
                var inp = document.createElement('input');
                inp.type = 'hidden';
                inp.name = 'ids[]';
                inp.value = String(id);
                container.appendChild(inp);
            });
            var msg = document.getElementById('deleteModalMensaje');
            if (msg) {
                msg.textContent = list.length > 1
                    ? 'Se eliminarán ' + list.length + ' fechas de esta asignación (una por cada registro en base de datos). Esta acción no se puede deshacer.'
                    : '¿Estás seguro de que deseas eliminar esta reserva? Esta acción no se puede deshacer.';
            }
            var modal = document.getElementById('deleteModal');
            if (modal) {
                modal.classList.remove('hidden');
                modal.classList.add('flex');
                modal.style.display = 'flex';
            }
        }
        function closeDeleteModalReservas() {
            var modal = document.getElementById('deleteModal');
            if (modal) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                modal.style.display = '';
            }
        }
        function abrirModalFechasClase(diaLabel, fechas) {
            document.getElementById('modal-fechas-clase-dia').textContent = diaLabel || '—';
            var ol = document.getElementById('modal-fechas-clase-lista');
            ol.innerHTML = '';
            (fechas || []).forEach(function (f) {
                var li = document.createElement('li');
                li.textContent = f;
                li.className = 'pl-1';
                ol.appendChild(li);
            });
            var modal = document.getElementById('modalFechasClase');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            modal.style.display = 'flex';
        }
        function cerrarModalFechasClase() {
            var modal = document.getElementById('modalFechasClase');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            modal.style.display = '';
        }
        function abrirModalReservaOriginal(data) {
            if (!data) return;
            document.getElementById('modal-orig-ambiente').textContent = data.ambiente || '—';
            document.getElementById('modal-orig-ficha').textContent = data.ficha || '—';
            document.getElementById('modal-orig-instructor').textContent = data.instructor || '—';
            document.getElementById('modal-orig-dia').textContent = data.dia || '—';
            document.getElementById('modal-orig-jornada').textContent = data.jornada || '—';
            document.getElementById('modal-orig-fechas').textContent = data.fechas || '—';
            document.getElementById('modal-orig-estado').textContent = data.estado || '—';
            var modal = document.getElementById('modalReservaOriginal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            modal.style.display = 'flex';
        }
        function cerrarModalReservaOriginal() {
            var modal = document.getElementById('modalReservaOriginal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            modal.style.display = '';
        }
        document.addEventListener('DOMContentLoaded', function() {
            document.addEventListener('click', function(e) {
                var btnFechas = e.target.closest('.btn-fechas-clase');
                if (btnFechas) {
                    e.preventDefault();
                    var raw = btnFechas.getAttribute('data-fechas');
                    var dia = btnFechas.getAttribute('data-dia') || '';
                    var arr = [];
                    try {
                        arr = raw ? JSON.parse(raw) : [];
                    } catch (err) {
                        arr = [];
                    }
                    if (arr.length) {
                        abrirModalFechasClase(dia, arr);
                    }
                    return;
                }
                var btn = e.target.closest('.btn-modal-liberado');
                if (btn) {
                    e.preventDefault();
                    e.stopPropagation();
                    var id = btn.getAttribute('data-id');
                    if (!id) return;
                    fetch('{{ route("ambientes.reserva-original", ["id" => "__ID__"]) }}'.replace('__ID__', id), {
                        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                    })
                    .then(function(r) { return r.json(); })
                    .then(function(data) {
                        if (data.error) return;
                        abrirModalReservaOriginal(data);
                    })
                    .catch(function(err) { console.error('Error:', err); });
                }
            });
            document.getElementById('deleteModal')?.addEventListener('click', function(e) {
                if (e.target === this) closeDeleteModalReservas();
            });
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    var del = document.getElementById('deleteModal');
                    if (del && !del.classList.contains('hidden')) closeDeleteModalReservas();
                    var mf = document.getElementById('modalFechasClase');
                    if (mf && !mf.classList.contains('hidden')) cerrarModalFechasClase();
                    var m = document.getElementById('modalReservaOriginal');
                    if (m && !m.classList.contains('hidden')) cerrarModalReservaOriginal();
                }
            });
        });
    </script>

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
