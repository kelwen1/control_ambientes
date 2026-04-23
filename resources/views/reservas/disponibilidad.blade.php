@extends('layouts.app')

@section('title', 'Disponibilidad de Instructor')

@section('content')
    <div class="mb-6 sm:mb-8">
        <div class="mb-2">
            <br>
        </div>
        <h1 class="text-2xl sm:text-3xl font-bold text-[#39B54A] mb-2">
            Disponibilidad de Instructor
        </h1>
        <p class="text-gray-600 text-sm sm:text-base">Consulta qué días tiene ocupados o libres un instructor según el día de la semana y la jornada</p>
    </div>

    <!-- Filtros -->
    <div class="bg-white rounded-xl shadow-lg p-4 sm:p-6 md:p-8 mb-6 max-w-full min-w-0">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Consultar disponibilidad</h2>
        <form method="GET" action="{{ route('ambientes.disponibilidad') }}" class="flex flex-col md:flex-row md:flex-wrap items-stretch md:items-end gap-4 w-full min-w-0" id="formDisponibilidad">
            <div class="w-full md:w-auto min-w-0">
                <label for="id_persona" class="block text-sm font-semibold text-gray-700 mb-1">Instructor</label>
                <select name="id_persona" id="id_persona" required
                        class="w-full md:min-w-[200px] max-w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-[#39B54A] focus:outline-none text-sm">
                    <option value="">Seleccione instructor</option>
                    @foreach($instructores as $inst)
                        <option value="{{ $inst->id_persona }}" {{ ($id_persona ?? '') == $inst->id_persona ? 'selected' : '' }}>
                            {{ $inst->nombres }} {{ $inst->apellidos }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="w-full md:w-auto min-w-0">
                <label for="dia_semana" class="block text-sm font-semibold text-gray-700 mb-1">Día de la semana</label>
                <select name="dia_semana" id="dia_semana" required
                        class="w-full md:w-auto max-w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-[#39B54A] focus:outline-none text-sm">
                    <option value="">Seleccione día</option>
                    @foreach($diasSemana as $valor => $etiqueta)
                        <option value="{{ $valor }}" {{ ($dia_semana ?? '') == $valor ? 'selected' : '' }}>{{ $etiqueta }}</option>
                    @endforeach
                </select>
            </div>
            <div class="w-full md:w-auto min-w-0">
                <label for="jornada" class="block text-sm font-semibold text-gray-700 mb-1">Jornada</label>
                <select name="jornada" id="jornada" required
                        class="w-full md:w-auto max-w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-[#39B54A] focus:outline-none text-sm">
                    <option value="">Seleccione jornada</option>
                    <option value="manana" data-fin-semana="0" {{ ($jornada ?? '') == 'manana' ? 'selected' : '' }}>Mañana</option>
                    <option value="tarde" data-fin-semana="0" {{ ($jornada ?? '') == 'tarde' ? 'selected' : '' }}>Tarde</option>
                    <option value="noche" data-fin-semana="0" {{ ($jornada ?? '') == 'noche' ? 'selected' : '' }}>Noche</option>
                    <option value="fin_semana" data-fin-semana="1" {{ ($jornada ?? '') == 'fin_semana' ? 'selected' : '' }}>Fin de semana</option>
                </select>
            </div>
            <input type="hidden" name="mes" id="inputMes" value="{{ $mes }}">
            <input type="hidden" name="anio" id="inputAnio" value="{{ $anio }}">
            <button type="submit"
                    class="w-full md:w-auto shrink-0 px-4 py-2.5 bg-[#39B54A] text-white rounded-lg hover:bg-[#2d8f3a] transition-colors font-medium text-sm">
                Ver calendario
            </button>
        </form>
    </div>

    <!-- Leyenda -->
    @if($id_persona && $dia_semana && $jornada)
        <div class="flex flex-wrap gap-6 mb-4 text-sm">
            <span class="flex items-center gap-2">
                <span class="w-5 h-5 rounded bg-red-500"></span>
                <span>Ocupado (tiene reserva)</span>
            </span>
            <span class="flex items-center gap-2">
                <span class="w-5 h-5 rounded bg-green-500"></span>
                <span>Disponible (sin reserva)</span>
            </span>
        </div>
    @endif

    <!-- Calendario -->
    @if($id_persona && $dia_semana && $jornada)
        @php
            $dowMap = ['lunes' => 1, 'martes' => 2, 'miercoles' => 3, 'jueves' => 4, 'viernes' => 5, 'sabado' => 6, 'domingo' => 7];
            $dowTarget = $dowMap[$dia_semana] ?? 1;
            $fechasOcupadasSet = $fechasOcupadas->flip()->toArray();
        @endphp
        <div class="flex justify-center">
        <div class="bg-white rounded-xl shadow-lg overflow-hidden max-w-md" style="max-width: min(704px, 100%);">
            <div class="px-5 py-3.5 border-b border-gray-200 flex flex-wrap justify-between items-center gap-2">
                <h2 class="text-base font-bold text-gray-800">
                    {{ $mesInicio->translatedFormat('F Y') }}
                </h2>
                <div class="flex gap-1">
                    @php
                        $mesAnt = $mesInicio->copy()->subMonth();
                        $mesSig = $mesInicio->copy()->addMonth();
                    @endphp
                    <a href="{{ route('ambientes.disponibilidad', array_merge(request()->query(), ['mes' => $mesAnt->month, 'anio' => $mesAnt->year])) }}"
                       class="px-2 py-1 bg-gray-200 hover:bg-gray-300 rounded text-xs font-medium transition-colors">←</a>
                    <a href="{{ route('ambientes.disponibilidad', array_merge(request()->query(), ['mes' => now()->month, 'anio' => now()->year])) }}"
                       class="px-2 py-1 bg-gray-200 hover:bg-gray-300 rounded text-xs font-medium transition-colors">Hoy</a>
                    <a href="{{ route('ambientes.disponibilidad', array_merge(request()->query(), ['mes' => $mesSig->month, 'anio' => $mesSig->year])) }}"
                       class="px-2 py-1 bg-gray-200 hover:bg-gray-300 rounded text-xs font-medium transition-colors">→</a>
                </div>
            </div>
            <div class="p-4">
                <div class="grid grid-cols-7 gap-1.5 text-center">
                    @foreach(['Lun','Mar','Mié','Jue','Vie','Sáb','Dom'] as $cab)
                        <div class="text-xs font-semibold text-gray-600 py-1">{{ $cab }}</div>
                    @endforeach
                    @php
                        $primerDia = $mesInicio->copy();
                        $padStart = $primerDia->dayOfWeekIso - 1;
                        $diasEnMes = $primerDia->daysInMonth;
                        $total = $padStart + $diasEnMes;
                        $celdas = (int) ceil($total / 7) * 7;
                    @endphp
                    @for ($i = 0; $i < $celdas; $i++)
                        @php
                            if ($i < $padStart) {
                                $fecha = $primerDia->copy()->subDays($padStart - $i);
                            } elseif ($i < $padStart + $diasEnMes) {
                                $diaNum = $i - $padStart + 1;
                                $fecha = $primerDia->copy()->day($diaNum);
                            } else {
                                $fecha = $primerDia->copy()->endOfMonth()->addDays($i - $padStart - $diasEnMes + 1);
                            }
                            $esDelMes = $fecha->month === $mesInicio->month;
                            $esDiaObjetivo = $fecha->dayOfWeekIso === $dowTarget;
                            $fechaStr = $fecha->format('Y-m-d');
                            $ocupado = $esDelMes && $esDiaObjetivo && isset($fechasOcupadasSet[$fechaStr]);
                            $libre = $esDelMes && $esDiaObjetivo && !$ocupado;
                        @endphp
                        <div class="w-11 h-10 flex items-center justify-center rounded text-sm font-medium
                            @if(!$esDelMes) text-gray-300
                            @elseif($ocupado) bg-red-500 text-white
                            @elseif($libre) bg-green-500 text-white
                            @else text-gray-600
                            @endif">
                            {{ $fecha->day }}
                        </div>
                    @endfor
                </div>
            </div>
        </div>
        </div>
    @else
        <div class="bg-white rounded-xl shadow-lg p-12 text-center text-gray-500">
            <p class="text-lg">Seleccione instructor, día y jornada, luego pulse «Ver calendario».</p>
        </div>
    @endif

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var diaSemana = document.getElementById('dia_semana');
            var jornadaSelect = document.getElementById('jornada');
            if (!diaSemana || !jornadaSelect) return;

            function actualizarJornada() {
                var dia = diaSemana.value;
                var esFinDeSemana = (dia === 'sabado' || dia === 'domingo');
                var opts = jornadaSelect.querySelectorAll('option[value]');
                opts.forEach(function(opt) {
                    if (opt.value === '') return;
                    var esFinSemanaOpt = opt.getAttribute('data-fin-semana') === '1';
                    var mostrar = esFinSemanaOpt ? esFinDeSemana : !esFinDeSemana;
                    opt.style.display = mostrar ? '' : 'none';
                    opt.disabled = !mostrar;
                });
                if (esFinDeSemana && jornadaSelect.value !== 'fin_semana') {
                    jornadaSelect.value = 'fin_semana';
                } else if (!esFinDeSemana && jornadaSelect.value === 'fin_semana') {
                    jornadaSelect.value = 'manana';
                }
            }
            diaSemana.addEventListener('change', actualizarJornada);
            actualizarJornada();
        });
    </script>
@endsection
