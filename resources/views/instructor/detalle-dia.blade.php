@extends('layouts.app')

@section('title', 'Detalle del día - ' . $diaLabel)

@section('content')
    @if (session('success'))
        <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-800 rounded-xl">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-800 rounded-xl">{{ session('error') }}</div>
    @endif
    @if (session('info'))
        <div class="mb-4 p-4 bg-blue-50 border border-blue-200 text-blue-800 rounded-xl">{{ session('info') }}</div>
    @endif

    <div class="mb-6 sm:mb-8 animate-fade-slide-up">
        <a href="{{ route('dashboard') }}" class="text-[#39B54A] hover:underline font-medium mb-2 inline-block transition-colors duration-200">← Volver al inicio</a>
        <h1 class="text-2xl sm:text-3xl font-bold text-[#39B54A] mb-2 tracking-tight">{{ $diaLabel }} – Detalle de reservas</h1>
        <p class="text-gray-600 text-sm sm:text-base">Propiedades completas de cada reserva de este día.</p>
    </div>

    @if($reservas->isEmpty())
        <div class="card-premium bg-white rounded-xl shadow-card p-8 text-center text-gray-500 hover:shadow-card-hover transition-shadow duration-300">
            No tienes clases asignadas este día.
        </div>
    @else
        <div class="space-y-6">
            @foreach($reservas as $r)
                @php
                    $mapaJ = [1=>'manana',2=>'tarde',3=>'noche',4=>'fin_semana'];
                    $kJ = $mapaJ[$r->id_jornada ?? 0] ?? null;
                    $jornadaLabel = $kJ ? config("jornadas.$kJ.label") : 'N/A';
                    $resultadoDenom = $r->resultado_denominacion ?? '—';
                    $sesionesData = $sesionesPorReserva[$r->id_reserva] ?? ['total' => 0, 'consumidas' => 0, 'suspendidas' => 0, 'restantes' => 0, 'cupo_libre_calendario' => 0];
                    $instructor = trim(($r->instructor_nombres ?? '') . ' ' . ($r->instructor_apellidos ?? ''));
                @endphp
                <div class="card-premium bg-white rounded-xl shadow-card p-6 border-l-4 border-[#39B54A] hover:shadow-card-hover transition-shadow duration-300">
                    <div class="flex flex-wrap items-center gap-2 mb-4">
                        <span class="badge-premium px-3 py-1.5 bg-[#39B54A] text-white rounded-lg text-sm font-medium">Ambiente {{ $r->num_ambiente ?? '—' }}</span>
                        <span class="badge-premium px-3 py-1.5 bg-gray-200 text-gray-700 rounded-lg text-sm">{{ $jornadaLabel }}</span>
                    </div>
                    <dl class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 text-sm">
                        <div><dt class="text-gray-500 font-medium">Ambiente</dt><dd class="text-gray-900 font-semibold">{{ $r->num_ambiente ?? '—' }}</dd></div>
                        <div><dt class="text-gray-500 font-medium">Ficha</dt><dd class="text-gray-900 font-semibold">{{ $r->num_ficha ?? '—' }}</dd></div>
                        <div><dt class="text-gray-500 font-medium">Programa</dt><dd class="text-gray-900 font-semibold">{{ $r->nombre_programa ?? '—' }}</dd></div>
                        <div><dt class="text-gray-500 font-medium">Competencia</dt><dd class="text-gray-900 font-semibold">{{ $r->nombre_competencia ?? '—' }}</dd></div>
                        <div><dt class="text-gray-500 font-medium">Resultado</dt><dd class="text-gray-900 font-semibold">{{ $resultadoDenom }}</dd></div>
                        <div><dt class="text-gray-500 font-medium">Instructor</dt><dd class="text-gray-900 font-semibold">{{ $instructor ?: '—' }}</dd></div>
                        <div><dt class="text-gray-500 font-medium">Pendientes de impartir</dt><dd class="text-gray-900 font-semibold"><span class="text-[#39B54A]">{{ $sesionesData['restantes'] }}</span> <span class="text-gray-400">/ {{ $sesionesData['total'] }} cupo del resultado</span>@if(($sesionesData['cupo_libre_calendario'] ?? 0) > 0)<br><span class="text-gray-500 text-xs font-normal">Cupo libre en calendario: {{ $sesionesData['cupo_libre_calendario'] }} sesión(es) sin agendar.</span>@endif<br><span class="text-gray-500 text-xs font-normal">{{ $sesionesData['consumidas'] }} ya impartidas · {{ $sesionesData['suspendidas'] ?? 0 }} suspendidas (liberadas, cuentan para el cupo)</span></dd></div>
                        <div><dt class="text-gray-500 font-medium">Día de clases</dt><dd class="text-gray-900">{{ ucfirst($r->dia_semana_text ?? '—') }}</dd></div>
                        <div><dt class="text-gray-500 font-medium">Fecha inicio de la reserva</dt><dd class="text-gray-900 font-semibold">{{ $r->fecha_inicio ? \Carbon\Carbon::parse($r->fecha_inicio)->format('d/m/Y') : '—' }}</dd></div>
                        <div><dt class="text-gray-500 font-medium">Fecha fin de la reserva</dt><dd class="text-gray-900 font-semibold">{{ $r->fecha_fin ? \Carbon\Carbon::parse($r->fecha_fin)->format('d/m/Y') : '—' }}</dd></div>
                    </dl>

                    {{-- Liberar día: solo administrador o coordinación L --}}
                    @if(auth()->user() && (auth()->user()->isAdmin() || auth()->user()->isCoordinatorL()))
                    <div class="mt-4 pt-4 border-t border-gray-200">
                        <p class="text-sm font-medium text-gray-700 mb-2">Liberar ambiente un día</p>
                        <p class="text-xs text-gray-500 mb-3">Libera el ambiente en una fecha concreta para que otro instructor pueda reservarlo (gestión por coordinación o administrador).</p>
                        @php
                            $proximas = $proximasFechasPorReserva[$r->id_reserva] ?? [];
                            $liberados = $diasLiberadosPorReserva[$r->id_reserva] ?? [];
                        @endphp
                        @if(!empty($proximas))
                            <form action="{{ route('reservas.liberar-dia') }}" method="POST" class="inline-flex flex-wrap gap-2 items-center">
                                @csrf
                                <input type="hidden" name="id_reserva" value="{{ $r->id_reserva }}">
                                <input type="hidden" name="redirect" value="{{ url()->current() }}">
                                <select name="fecha" class="text-sm border border-gray-300 rounded-lg px-3 py-1.5 focus:ring-2 focus:ring-[#39B54A] focus:border-[#39B54A]">
                                    @foreach($proximas as $pf)
                                        <option value="{{ $pf['valor'] }}">{{ $pf['label'] }}</option>
                                    @endforeach
                                </select>
                                <button type="submit" class="text-sm px-3 py-1.5 bg-amber-100 text-amber-800 hover:bg-amber-200 rounded-lg font-medium transition-colors">
                                    Liberar este día
                                </button>
                            </form>
                        @else
                            <p class="text-xs text-gray-500 italic">No hay fechas futuras en el rango de la reserva.</p>
                        @endif
                        @if(!empty($liberados))
                            <div class="mt-3">
                                <p class="text-xs font-medium text-gray-600 mb-1">Días liberados:</p>
                                <ul class="flex flex-wrap gap-2">
                                    @foreach($liberados as $fl)
                                        <li class="inline-flex items-center gap-1.5 text-xs bg-amber-50 text-amber-800 px-3 py-1.5 rounded-lg border border-amber-200">
                                            <span>{{ $fl['label'] }}</span>
                                            <button type="button" onclick="abrirModalRecuperar({{ $r->id_reserva }}, '{{ $fl['valor'] }}', '{{ $fl['label'] }}', '{{ $r->num_ambiente ?? '—' }}', '{{ $jornadaLabel }}')"
                                                    class="text-[#39B54A] hover:text-[#2d8f3a] font-semibold hover:underline transition-colors" title="Recuperar este día">
                                                Recuperar
                                            </button>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        {{-- Liberar días festivos de Colombia --}}
                        @php $festivos = $festivosPorReserva[$r->id_reserva] ?? []; @endphp
                        @if(!empty($festivos))
                            <div class="mt-4 pt-4 border-t border-gray-200">
                                <p class="text-sm font-medium text-gray-700 mb-1">Días festivos (Colombia) en tu reserva</p>
                                <p class="text-xs text-gray-500 mb-2">Libera todos los festivos que caen en tu día de clase para que otro instructor pueda reservar esos días.</p>
                                <ul class="text-xs text-gray-600 mb-2 space-y-0.5">
                                    @foreach($festivos as $f)
                                        <li>{{ \Carbon\Carbon::parse($f['fecha'])->format('d/m/Y') }} – {{ $f['nombre'] }}</li>
                                    @endforeach
                                </ul>
                                <form action="{{ route('reservas.liberar-festivos') }}" method="POST" class="inline">
                                    @csrf
                                    <input type="hidden" name="id_reserva" value="{{ $r->id_reserva }}">
                                    <input type="hidden" name="redirect" value="{{ url()->current() }}">
                                    <button type="submit" class="text-sm px-3 py-1.5 bg-blue-100 text-blue-800 hover:bg-blue-200 rounded-lg font-medium transition-colors">
                                        Liberar todos los festivos
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif

    {{-- Modal recuperar día liberado --}}
    <div id="modalRecuperarDia" class="fixed inset-0 z-50 hidden items-center justify-center p-4 modal-overlay" onclick="if (event.target === this) cerrarModalRecuperar()">
        <div class="modal-container glass-container rounded-2xl shadow-2xl p-6 sm:p-8 w-full max-w-md" onclick="event.stopPropagation()">
            <div class="text-center">
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-[#39B54A]/20 mb-4">
                    <span class="text-3xl">📅</span>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-2">¿Recuperar este día?</h3>
                <p class="text-gray-600 text-sm mb-4">
                    Volverás a ocupar el ambiente para el día seleccionado. Solo podrás recuperarlo si nadie más lo ha reservado.
                </p>
                <div id="modalRecuperarDetalle" class="mb-6 p-4 bg-gray-50 rounded-xl text-left text-sm">
                    <p class="font-semibold text-gray-800"><span id="modalRecuperarFecha">—</span></p>
                    <p class="text-gray-600 mt-1">Ambiente <span id="modalRecuperarAmbiente">—</span> · <span id="modalRecuperarJornada">—</span></p>
                </div>
                <form id="formRecuperarDia" action="{{ route('reservas.revertir-dia') }}" method="POST" class="flex gap-3 sm:gap-4">
                    @csrf
                    <input type="hidden" name="id_reserva" id="modalRecuperarIdReserva">
                    <input type="hidden" name="fecha" id="modalRecuperarFechaValor">
                    <button type="button" onclick="cerrarModalRecuperar()"
                            class="flex-1 bg-gray-200 text-gray-700 py-3 rounded-xl font-semibold hover:bg-gray-300 transition-colors">
                        Cancelar
                    </button>
                    <button type="submit"
                            class="flex-1 bg-[#39B54A] text-white py-3 rounded-xl font-semibold hover:bg-[#2d8f3a] transition-colors shadow-md">
                        Sí, recuperar
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function abrirModalRecuperar(idReserva, fechaValor, fechaLabel, ambiente, jornada) {
            document.getElementById('modalRecuperarIdReserva').value = idReserva;
            document.getElementById('modalRecuperarFechaValor').value = fechaValor;
            document.getElementById('modalRecuperarFecha').textContent = fechaLabel;
            document.getElementById('modalRecuperarAmbiente').textContent = ambiente;
            document.getElementById('modalRecuperarJornada').textContent = jornada;
            document.getElementById('modalRecuperarDia').classList.remove('hidden');
            document.getElementById('modalRecuperarDia').classList.add('flex');
        }
        function cerrarModalRecuperar() {
            document.getElementById('modalRecuperarDia').classList.add('hidden');
            document.getElementById('modalRecuperarDia').classList.remove('flex');
        }
    </script>
@endsection
