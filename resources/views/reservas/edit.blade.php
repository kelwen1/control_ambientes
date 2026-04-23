@extends('layouts.app')

@section('title', 'Editar Reserva')

@section('content')
    <div class="mb-6 sm:mb-8">
        <h1 class="text-2xl sm:text-3xl font-bold text-[#39B54A] mb-2">
            Editar Reserva
        </h1>
        <p class="text-gray-600 text-sm sm:text-base">Modifica la información de la reserva</p>
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

    <div class="card-premium bg-white rounded-xl shadow-card p-6 sm:p-8 hover:shadow-card-hover transition-shadow duration-300">
        <form id="form_reserva_edit" method="POST" action="{{ route('reservas.update', $reserva->id_reserva) }}" class="space-y-6">
            @csrf
            @method('PUT')

            {{-- FICHA: número de ficha | Competencia | Resultado --}}
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Ficha</p>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="id_ficha_display" class="block text-xs text-gray-500 mb-1">Número de ficha</label>
                        <input type="hidden" name="id_ficha" value="{{ old('id_ficha', $reserva->id_ficha) }}">
                        <select id="id_ficha_display" disabled required
                                class="w-full px-3 py-2 border-2 border-gray-200 rounded-xl bg-gray-50 text-gray-700 cursor-not-allowed text-sm">
                            <option value="">Seleccione</option>
                            @foreach($fichas as $f)
                                <option value="{{ $f->id_ficha }}" data-num="{{ $f->num_ficha }}" data-id-programa="{{ $f->id_programa }}"
                                        {{ old('id_ficha', $reserva->id_ficha) == $f->id_ficha ? 'selected' : '' }}>
                                    {{ $f->num_ficha }}
                                </option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-gray-500">No editable en esta pantalla.</p>
                        @error('id_ficha')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="id_competencia" class="block text-xs text-gray-500 mb-1">Competencia</label>
                        <select name="id_competencia" id="id_competencia" required
                                class="w-full px-3 py-2 border-2 border-gray-300 rounded-xl focus:border-[#39B54A] focus:ring-2 focus:ring-[#39B54A]/20 focus:outline-none transition-all duration-200 text-sm appearance-none pr-8"
                                style="background-image: url('data:image/svg+xml;charset=UTF-8,%3csvg xmlns=%27http://www.w3.org/2000/svg%27 viewBox=%270 0 24 24%27 fill=%27none%27 stroke=%27%236b7280%27 stroke-width=%272%27 stroke-linecap=%27round%27 stroke-linejoin=%27round%27%3e%3cpolyline points=%276 9 12 15 18 9%27%3e%3c/polyline%3e%3c/svg%3e'); background-repeat: no-repeat; background-position: right 0.5rem center; background-size: 1.25rem;">
                            <option value="">Seleccione</option>
                            @foreach($competencias as $c)
                                <option value="{{ $c->id_competencia }}"
                                        {{ old('id_competencia', $reserva->id_competencia) == $c->id_competencia ? 'selected' : '' }}>
                                    {{ $c->nombre_competencia }}
                                </option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-gray-500">Competencias del catálogo común; el vínculo con el programa lo define la ficha.</p>
                        @error('id_competencia')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="id_resultado" class="block text-xs text-gray-500 mb-1">Resultado</label>
                        <select name="id_resultado" id="id_resultado"
                                class="w-full px-3 py-2 border-2 border-gray-300 rounded-xl focus:border-[#39B54A] focus:ring-2 focus:ring-[#39B54A]/20 focus:outline-none transition-all duration-200 text-sm appearance-none pr-8 bg-gray-50"
                                style="background-image: url('data:image/svg+xml;charset=UTF-8,%3csvg xmlns=%27http://www.w3.org/2000/svg%27 viewBox=%270 0 24 24%27 fill=%27none%27 stroke=%27%236b7280%27 stroke-width=%272%27%3e%3cpolyline points=%276 9 12 15 18 9%27%3e%3c/polyline%3e%3c/svg%3e'); background-repeat: no-repeat; background-position: right 0.5rem center; background-size: 1.25rem;">
                            <option value="">Primero elija una competencia</option>
                            @foreach($resultados ?? [] as $r)
                                <option value="{{ $r->id_resultado }}" data-id-competencia="{{ $r->id_competencia }}" data-sesiones="{{ (int) ($r->sesiones ?? 0) }}"
                                        {{ old('id_resultado', $reserva->id_resultado) == $r->id_resultado ? 'selected' : '' }}>
                                    {{ Str::limit($r->denominacion, 60) }}
                                </option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-gray-500">Se habilita al elegir competencia; solo resultados de esa competencia.</p>
                    </div>
                </div>
            </div>

            @php
                $idPersonaReserva = old('id_persona', $reserva->id_persona ?? '');
                $instructorEdit = ($instructores ?? collect())->firstWhere('id_persona', $idPersonaReserva);
                $cedulaInstructorEdit = $instructorEdit ? (string) $instructorEdit->id_persona : '';
                $nombreInstructorEdit = $instructorEdit ? trim(($instructorEdit->nombres ?? '') . ' ' . ($instructorEdit->apellidos ?? '')) : '';
            @endphp
            {{-- INSTRUCTOR: mismo orden que crear (cédula arriba; datos abajo); solo lectura --}}
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Instructor</p>
                <div class="mb-3">
                    <label for="instructor_buscar_cedula" class="block text-xs text-gray-500 mb-1">Buscar por cédula</label>
                    <input type="text" id="instructor_buscar_cedula" readonly tabindex="-1"
                           value="{{ $cedulaInstructorEdit }}"
                           placeholder="—"
                           class="w-full max-w-md px-3 py-2 border-2 border-gray-200 rounded-xl bg-gray-50 text-gray-600 text-sm cursor-not-allowed">
                    <input type="hidden" name="id_persona" value="{{ $idPersonaReserva !== '' && $idPersonaReserva !== null ? $idPersonaReserva : '' }}">
                    <p class="mt-1 text-xs text-gray-500">No editable en esta pantalla.</p>
                    @error('id_persona')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="instructor_cedula" class="block text-xs text-gray-500 mb-1">Id o cédula</label>
                        <input type="text" id="instructor_cedula" readonly tabindex="-1"
                               value="{{ $cedulaInstructorEdit }}"
                               placeholder="—"
                               class="w-full px-3 py-2 border-2 border-gray-200 rounded-xl bg-gray-50 text-gray-600 text-sm">
                    </div>
                    <div>
                        <label for="instructor_nombre" class="block text-xs text-gray-500 mb-1">Nombre del instructor</label>
                        <input type="text" id="instructor_nombre" readonly tabindex="-1"
                               value="{{ $nombreInstructorEdit }}"
                               placeholder="—"
                               class="w-full px-3 py-2 border-2 border-gray-200 rounded-xl bg-gray-50 text-gray-600 text-sm">
                    </div>
                </div>
            </div>

            {{-- AMBIENTE --}}
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Ambiente</p>
                <div>
                    <label for="id_ambiente" class="block text-xs text-gray-500 mb-1">Número de ambiente</label>
                    <select name="id_ambiente" id="id_ambiente" required
                            class="w-full max-w-xs px-3 py-2 border-2 border-gray-300 rounded-xl focus:border-[#39B54A] focus:ring-2 focus:ring-[#39B54A]/20 focus:outline-none transition-all duration-200 text-sm">
                        <option value="">Seleccione</option>
                        @foreach($ambientes as $a)
                            <option value="{{ $a->id_ambiente }}" data-num="{{ $a->num_ambiente }}"
                                    {{ old('id_ambiente', $reserva->id_ambiente) == $a->id_ambiente ? 'selected' : '' }}>
                                {{ $a->num_ambiente }}
                            </option>
                        @endforeach
                    </select>
                    @error('id_ambiente')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>

            {{-- FECHA --}}
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Fecha</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="fecha_inicio" class="block text-xs text-gray-500 mb-1">Inicio</label>
                        <input type="date" name="fecha_inicio" id="fecha_inicio"
                               value="{{ old('fecha_inicio', $reserva->fecha_inicio) }}" required readonly
                               tabindex="-1"
                               class="w-full px-3 py-2 border-2 border-gray-200 rounded-xl bg-gray-50 text-gray-700 cursor-not-allowed text-sm">
                        @error('fecha_inicio')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="fecha_fin" class="block text-xs text-gray-500 mb-1">Fin</label>
                        <input type="date" name="fecha_fin" id="fecha_fin"
                               value="{{ old('fecha_fin', $reserva->fecha_fin) }}" required readonly
                               tabindex="-1"
                               class="w-full px-3 py-2 border-2 border-gray-200 rounded-xl bg-gray-50 text-gray-700 cursor-not-allowed text-sm">
                        @error('fecha_fin')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>
                <p class="mt-1 text-xs text-gray-500">Las fechas no se editan aquí. Cuando ya pasó la última fecha de clase del periodo, el sistema puede marcar la reserva como finalizada (proceso automático diario).</p>
                <p class="mt-1 text-xs text-gray-400">Las clases son los días indicados entre inicio y fin, <strong class="font-medium text-gray-600">incluyendo</strong> esas dos fechas si coinciden con el día de la semana elegido.</p>
            </div>

            {{-- DÍA: igual que crear (oculto + radios deshabilitados) --}}
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Día</p>
                <p class="text-xs text-gray-500 mb-2">Lo define la <span class="font-medium">fecha de inicio</span>. El día <span class="font-medium">sí se envía al servidor y se guarda</span> en la base de datos; los botones en gris solo muestran cuál corresponde (el navegador no envía controles deshabilitados, por eso va un campo oculto y el servidor además lo calcula desde la fecha).</p>
                @php
                    $fechaParaDia = old('fecha_inicio', $reserva->fecha_inicio ?? null);
                    $diaSegunInicio = '';
                    if ($fechaParaDia) {
                        try {
                            $dowIni = \Carbon\Carbon::parse($fechaParaDia)->dayOfWeek;
                            $mapDow = ['domingo', 'lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado'];
                            $diaSegunInicio = $mapDow[$dowIni] ?? '';
                        } catch (\Throwable $e) {
                            $diaSegunInicio = '';
                        }
                    }
                    $dias = [
                        ['lunes', 'L'],
                        ['martes', 'M'],
                        ['miercoles', 'M'],
                        ['jueves', 'J'],
                        ['viernes', 'V'],
                        ['sabado', 'S'],
                        ['domingo', 'D'],
                    ];
                @endphp
                <input type="hidden" name="dia_semana" id="dia_semana_hidden" value="{{ $diaSegunInicio }}" autocomplete="off">
                <div class="flex flex-wrap gap-4 items-center pointer-events-none select-none opacity-90" aria-hidden="true">
                    @foreach($dias as $d)
                        <label class="inline-flex items-center gap-2 cursor-default">
                            <input type="radio" value="{{ $d[0] }}" disabled
                                   {{ $diaSegunInicio === $d[0] ? 'checked' : '' }}
                                   class="js-dia-semana-vista w-4 h-4 text-[#39B54A] border-gray-300">
                            <span class="text-sm font-medium text-gray-700">{{ $d[1] }}</span>
                        </label>
                    @endforeach
                </div>
                <p class="mt-1 text-xs text-gray-500">Sábados y domingos: horario único 7 am - 5 pm (una reserva por ambiente por día).</p>
                @error('dia_semana')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            {{-- JORNADA --}}
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Jornada</p>
                <p class="text-xs text-gray-500 mb-2">Mañana - Tarde - Noche o fin de semana</p>
                <input type="hidden" name="jornada" id="jornada" value="{{ old('jornada', $jornadaSeleccionada) }}">
                <select id="jornada_display" disabled required
                        class="w-full max-w-md px-3 py-2 border-2 border-gray-200 rounded-xl bg-gray-50 text-gray-700 cursor-not-allowed text-sm">
                    <option value="">Seleccione</option>
                    @foreach($jornadas ?? [] as $key => $j)
                        <option value="{{ $key }}" {{ old('jornada', $jornadaSeleccionada) === $key ? 'selected' : '' }}>{{ $j['label'] }}</option>
                    @endforeach
                </select>
                <p class="mt-1 text-xs text-gray-500">No editable en esta pantalla.</p>
                @error('jornada')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            {{-- ESTADO (solo lectura: lo actualiza el sistema al cerrar el periodo) --}}
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Estado</p>
                <div>
                    <label for="id_estado_reserva_display" class="block text-xs text-gray-500 mb-1">Estado de la reserva</label>
                    <input type="hidden" name="id_estado_reserva" value="{{ old('id_estado_reserva', $reserva->id_estado_reserva) }}">
                    <select id="id_estado_reserva_display" disabled required
                            class="w-full max-w-md px-3 py-2 border-2 border-gray-200 rounded-xl bg-gray-50 text-gray-700 cursor-not-allowed text-sm">
                        @foreach($estados as $estado)
                            <option value="{{ $estado->id_estado_reserva }}"
                                    {{ old('id_estado_reserva', $reserva->id_estado_reserva) == $estado->id_estado_reserva ? 'selected' : '' }}>
                                {{ $estado->nombre_estado }}
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-gray-500">No editable manualmente; al pasar la última fecha de clase el comando diario actualiza el estado a finalizada y el ambiente libera el cupo si no hay más reservas activas.</p>
                    @error('id_estado_reserva')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="flex flex-col sm:flex-row gap-4 pt-4 border-t border-gray-200">
                <button type="submit"
                        class="btn-primary flex-1 px-6 py-3 bg-[#39B54A] text-white rounded-xl font-semibold hover:bg-[#2d8f3a] hover:shadow-glow transition-all duration-200 shadow-md">
                    Actualizar Reserva
                </button>
                <a href="{{ route('ambientes.index') }}"
                   class="flex-1 px-6 py-3 bg-gray-300 text-gray-700 rounded-xl font-semibold hover:bg-gray-400 transition-all duration-200 text-center">
                    Cancelar
                </a>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const idFichaDisplay = document.getElementById('id_ficha_display');
            const idCompetencia = document.getElementById('id_competencia');
            const idResultado = document.getElementById('id_resultado');
            const formReservaEdit = document.getElementById('form_reserva_edit');

            function idProgramaDesdeFicha() {
                if (!idFichaDisplay || !idFichaDisplay.value) return null;
                var opt = idFichaDisplay.options[idFichaDisplay.selectedIndex];
                if (!opt) return null;
                var v = opt.getAttribute('data-id-programa');
                return v !== null && v !== '' ? parseInt(v, 10) : null;
            }

            function filtrarCompetenciasPorPrograma() {
                if (!idCompetencia) return;
                idCompetencia.querySelectorAll('option[value]').forEach(function(o) {
                    if (o.value === '') return;
                    o.style.display = '';
                });
            }

            function aplicarResultadoHabilitado() {
                if (!idCompetencia || !idResultado) return;
                var idComp = idCompetencia.value ? parseInt(idCompetencia.value, 10) : 0;
                var habilita = idComp > 0;
                idResultado.disabled = !habilita;
                if (habilita) {
                    idResultado.classList.remove('bg-gray-50');
                } else {
                    idResultado.classList.add('bg-gray-50');
                }
            }

            function filtrarResultadosPorCompetencia(limpiarValor) {
                if (!idCompetencia || !idResultado) return;
                var idComp = idCompetencia.value ? parseInt(idCompetencia.value, 10) : 0;
                idResultado.querySelectorAll('option[data-id-competencia]').forEach(function(o) {
                    var dataId = parseInt(o.getAttribute('data-id-competencia') || '0', 10);
                    o.style.display = idComp > 0 && dataId === idComp ? '' : 'none';
                });
                if (limpiarValor) {
                    idResultado.value = '';
                }
                aplicarResultadoHabilitado();
            }

            filtrarCompetenciasPorPrograma();
            filtrarResultadosPorCompetencia(false);

            if (idCompetencia) {
                idCompetencia.addEventListener('change', function() { filtrarResultadosPorCompetencia(true); });
            }

            if (formReservaEdit) {
                formReservaEdit.addEventListener('submit', function() {
                    if (idResultado) idResultado.disabled = false;
                });
            }

            function limitarJornadaPorDia() {
                const jornadaHidden = document.getElementById('jornada');
                const hidden = document.getElementById('dia_semana_hidden');
                if (!jornadaHidden || !hidden) return;
                var diaVal = hidden.value;
                if (!diaVal) return;
                var esFin = diaVal === 'sabado' || diaVal === 'domingo';
                const jornadaDisplay = document.getElementById('jornada_display');
                const opts = jornadaDisplay ? jornadaDisplay.querySelectorAll('option[value="manana"], option[value="tarde"], option[value="noche"], option[value="fin_semana"]') : [];
                opts.forEach(function(o) {
                    if (esFin) {
                        o.style.display = o.value === 'fin_semana' ? '' : 'none';
                    } else {
                        o.style.display = o.value === 'fin_semana' ? 'none' : '';
                    }
                });
            }
            limitarJornadaPorDia();
        });
    </script>
@endsection
