@extends('layouts.app')

@section('title', 'Asignar Ambiente a Ficha')

@section('content')
    <!-- Bienvenida -->
    <div class="mb-6 sm:mb-8">
        <h1 class="text-2xl sm:text-3xl font-bold text-[#39B54A] mb-2">
            Asignar Ambiente a Ficha
        </h1>
        <p class="text-gray-600 text-sm sm:text-base">Completa el formulario para asignar un ambiente a una ficha específica</p>
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
        <form id="form_reserva" method="POST" action="{{ route('reservas.store') }}" class="space-y-6">
            @csrf

            {{-- FICHA: número de ficha | Competencia | Resultado --}}
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Ficha</p>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="id_ficha" class="block text-xs text-gray-500 mb-1">Número de ficha</label>
                        <select name="id_ficha" id="id_ficha" required
                                class="w-full px-3 py-2 border-2 border-gray-300 rounded-xl focus:border-[#39B54A] focus:ring-2 focus:ring-[#39B54A]/20 focus:outline-none transition-all duration-200 text-sm">
                            <option value="">Seleccione</option>
                            @foreach($fichas as $f)
                                <option value="{{ $f->id_ficha }}" data-num="{{ $f->num_ficha }}" data-id-programa="{{ $f->id_programa }}" data-id-jornada="{{ $f->id_jornada ?? '' }}"
                                        {{ old('id_ficha') == $f->id_ficha ? 'selected' : '' }}>
                                    {{ $f->num_ficha }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_ficha')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="id_competencia" class="block text-xs text-gray-500 mb-1">Competencia</label>
                        <select name="id_competencia" id="id_competencia" required
                                class="w-full px-3 py-2 border-2 border-gray-300 rounded-xl focus:border-[#39B54A] focus:ring-2 focus:ring-[#39B54A]/20 focus:outline-none transition-all duration-200 text-sm appearance-none pr-8"
                                style="background-image: url('data:image/svg+xml;charset=UTF-8,%3csvg xmlns=%27http://www.w3.org/2000/svg%27 viewBox=%270 0 24 24%27 fill=%27none%27 stroke=%27%236b7280%27 stroke-width=%272%27 stroke-linecap=%27round%27 stroke-linejoin=%27round%27%3e%3cpolyline points=%276 9 12 15 18 9%27%3e%3c/polyline%3e%3c/svg%3e'); background-repeat: no-repeat; background-position: right 0.5rem center; background-size: 1.25rem;">
                            <option value="">Primero elija una ficha</option>
                            @foreach($competencias as $c)
                                <option value="{{ $c->id_competencia }}"
                                        {{ old('id_competencia') == $c->id_competencia ? 'selected' : '' }}>
                                    {{ $c->nombre_competencia }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_competencia')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="id_resultado" class="block text-xs text-gray-500 mb-1">Resultado</label>
                        <select name="id_resultado" id="id_resultado" disabled
                                class="w-full px-3 py-2 border-2 border-gray-300 rounded-xl focus:border-[#39B54A] focus:ring-2 focus:ring-[#39B54A]/20 focus:outline-none transition-all duration-200 text-sm appearance-none pr-8 bg-gray-50"
                                style="background-image: url('data:image/svg+xml;charset=UTF-8,%3csvg xmlns=%27http://www.w3.org/2000/svg%27 viewBox=%270 0 24 24%27 fill=%27none%27 stroke=%27%236b7280%27 stroke-width=%272%27%3e%3cpolyline points=%276 9 12 15 18 9%27%3e%3c/polyline%3e%3c/svg%3e'); background-repeat: no-repeat; background-position: right 0.5rem center; background-size: 1.25rem;">
                            <option value="">Primero elija una competencia</option>
                            @foreach($resultados ?? [] as $r)
                                <option value="{{ $r->id_resultado }}" data-id-competencia="{{ $r->id_competencia }}" data-sesiones="{{ (int) ($r->sesiones ?? 0) }}">
                                    {{ Str::limit($r->denominacion, 60) }}
                                </option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-gray-500">Se habilita al elegir competencia, solo resultados de esa competencia.</p>
                    </div>
                </div>
            </div>

            @php
                $instructoresLookup = ($instructores ?? collect())->values()->map(function ($i) {
                    return [
                        'id' => (string) $i->id_persona,
                        'cedula' => (string) $i->id_persona,
                        'nombre' => trim(($i->nombres ?? '') . ' ' . ($i->apellidos ?? '')),
                    ];
                });
            @endphp
            {{-- INSTRUCTOR: buscar por cédula arriba; datos abajo --}}
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Instructor</p>
                <div class="mb-3">
                    <label for="instructor_buscar_cedula" class="block text-xs text-gray-500 mb-1">Buscar por cédula</label>
                    <input type="text" id="instructor_buscar_cedula" autocomplete="off"
                           inputmode="numeric" maxlength="10" pattern="[0-9]*"
                           placeholder="7 a 10 dígitos"
                           class="w-full max-w-md px-3 py-2 border-2 border-gray-300 rounded-xl focus:border-[#39B54A] focus:ring-2 focus:ring-[#39B54A]/20 focus:outline-none transition-all duration-200 text-sm">
                    <input type="hidden" name="id_persona" id="id_persona" value="{{ old('id_persona') }}">
                    <p class="mt-1 text-xs text-gray-500">Entre 7 y 10 dígitos. Debe coincidir con la cédula registrada.</p>
                    @error('id_persona')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="instructor_cedula" class="block text-xs text-gray-500 mb-1">Id o cédula</label>
                        <input type="text" id="instructor_cedula" readonly tabindex="-1"
                               placeholder="—"
                               class="w-full px-3 py-2 border-2 border-gray-200 rounded-xl bg-gray-50 text-gray-600 text-sm">
                    </div>
                    <div>
                        <label for="instructor_nombre" class="block text-xs text-gray-500 mb-1">Nombre del instructor</label>
                        <input type="text" id="instructor_nombre" readonly tabindex="-1"
                               placeholder="—"
                               class="w-full px-3 py-2 border-2 border-gray-200 rounded-xl bg-gray-50 text-gray-600 text-sm">
                    </div>
                </div>
            </div>

            {{-- AMBIENTE: número de ambiente --}}
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Ambiente</p>
                <div>
                    <label for="id_ambiente" class="block text-xs text-gray-500 mb-1">Número de ambiente</label>
                    <select name="id_ambiente" id="id_ambiente" required
                            class="w-full max-w-xs px-3 py-2 border-2 border-gray-300 rounded-xl focus:border-[#39B54A] focus:ring-2 focus:ring-[#39B54A]/20 focus:outline-none transition-all duration-200 text-sm">
                        <option value="">Seleccione</option>
                        @foreach($ambientes as $a)
                            <option value="{{ $a->id_ambiente }}" data-num="{{ $a->num_ambiente }}"
                                    {{ old('id_ambiente') == $a->id_ambiente ? 'selected' : '' }}>
                                {{ $a->num_ambiente }}
                            </option>
                        @endforeach
                    </select>
                    @error('id_ambiente')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>

            {{-- FECHA: rango + días de la semana (varias) + lista editable de sesiones --}}
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Fecha</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="fecha_inicio" class="block text-xs text-gray-500 mb-1">Inicio del periodo</label>
                        <input type="date" name="fecha_inicio" id="fecha_inicio" value="{{ old('fecha_inicio') }}" required
                               min="{{ now()->format('Y-m-d') }}"
                               class="w-full px-3 py-2 border-2 border-gray-300 rounded-xl focus:border-[#39B54A] focus:ring-2 focus:ring-[#39B54A]/20 focus:outline-none transition-all duration-200 text-sm">
                        @error('fecha_inicio')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="fecha_fin" class="block text-xs text-gray-500 mb-1">Fin del periodo</label>
                        <input type="date" name="fecha_fin" id="fecha_fin" value="{{ old('fecha_fin') }}" required
                               min="{{ now()->format('Y-m-d') }}"
                               class="w-full px-3 py-2 border-2 border-gray-300 rounded-xl focus:border-[#39B54A] focus:ring-2 focus:ring-[#39B54A]/20 focus:outline-none transition-all duration-200 text-sm">
                        @error('fecha_fin')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>
                <p class="mt-2 text-xs text-gray-500">El periodo delimita el rango; luego elija <strong class="font-medium text-gray-700">qué días de la semana</strong> tendrán clase (puede marcar varios). Según la jornada de la ficha: entre semana solo lunes a viernes; fin de semana solo sábado y domingo.</p>
                @php
                    $diasOld = collect(old('dias_semana', []))->map(fn ($x) => strtolower(trim((string) $x)))->all();
                    $dias = [
                        ['lunes', 'Lun', 'lv'],
                        ['martes', 'Mar', 'lv'],
                        ['miercoles', 'Mié', 'lv'],
                        ['jueves', 'Jue', 'lv'],
                        ['viernes', 'Vie', 'lv'],
                        ['sabado', 'Sáb', 'fs'],
                        ['domingo', 'Dom', 'fs'],
                    ];
                @endphp
                <div class="mt-4">
                    <p class="text-xs font-medium text-gray-600 mb-2">Días de la semana con sesión</p>
                    <div id="dias_semana_checks" class="flex flex-wrap gap-3">
                        @foreach($dias as $d)
                            <label class="js-dia-label js-dia-{{ $d[2] }} hidden inline-flex items-center gap-2 px-3 py-2 rounded-xl border-2 border-gray-200 bg-white cursor-pointer hover:border-[#39B54A]/50 transition-colors">
                                <input type="checkbox" name="dias_semana[]" value="{{ $d[0] }}"
                                       class="js-dia-check w-4 h-4 rounded text-[#39B54A] border-gray-300 focus:ring-[#39B54A]"
                                       {{ in_array($d[0], $diasOld, true) ? 'checked' : '' }}>
                                <span class="text-sm font-medium text-gray-700">{{ $d[1] }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('dias_semana')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    @error('dias_semana.*')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                <div id="fechas_sesion_panel" class="mt-5 rounded-2xl border-2 border-emerald-200/90 bg-emerald-50/90 p-4 sm:p-5 shadow-sm {{ old('fechas_sesion') ? '' : 'hidden' }}">
                    <p class="text-sm font-semibold text-emerald-900 mb-1">Fechas de sesión a guardar</p>
                    <p class="text-xs text-emerald-800/90 mb-3">Se creará <strong>una reserva por cada fecha</strong>. Use <span class="font-semibold">×</span> para quitar un día en el que no habrá clase.</p>
                    <ul id="fechas_sesion_list" class="space-y-2 mb-3 max-h-56 overflow-y-auto"></ul>
                    <div id="fechas_sesion_inputs"></div>
                    @error('fechas_sesion')<p class="mt-2 text-xs text-red-600">{{ $message }}</p>@enderror
                    @error('fechas_sesion.*')<p class="mt-2 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <p id="hint_sesiones_resultado" class="mt-3 text-sm text-red-600 font-semibold leading-snug hidden" role="status"></p>
            </div>

            @php
                $jornadaIdToLabel = [];
                foreach (['manana' => 1, 'tarde' => 2, 'noche' => 3, 'fin_semana' => 4] as $k => $jid) {
                    $jornadaIdToLabel[$jid] = $jornadas[$k]['label'] ?? $k;
                }
            @endphp
            {{-- JORNADA: definida en la ficha (solo lectura) --}}
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Jornada</p>
                <p class="text-xs text-gray-500 mb-2">Corresponde a la jornada del grupo (se define al crear la ficha).</p>
                <input type="text" id="jornada_ficha_display" readonly tabindex="-1" value=""
                       placeholder="Seleccione primero una ficha"
                       class="w-full max-w-md px-3 py-2 border-2 border-gray-200 rounded-xl bg-gray-50 text-gray-700 text-sm cursor-default">
                <p id="jornada_ficha_aviso" class="mt-1 text-xs text-amber-700 hidden" role="status"></p>
            </div>

            <div class="flex flex-col sm:flex-row gap-4 pt-4 border-t border-gray-200">
                <button type="submit"
                        class="btn-primary flex-1 px-6 py-3 bg-[#39B54A] text-white rounded-xl font-semibold hover:bg-[#2d8f3a] hover:shadow-glow transition-all duration-200 shadow-md">
                    Guardar Reserva
                </button>
                <a href="{{ route('ambientes.index') }}"
                   class="flex-1 px-6 py-3 bg-gray-300 text-gray-700 rounded-xl font-semibold hover:bg-gray-400 transition-all duration-200 text-center">
                    Cancelar
                </a>
            </div>
        </form>
    </div>

    <script>
        window.__oldFechasSesion = @json(collect(old('fechas_sesion', []))->filter()->values()->all());
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const idFicha = document.getElementById('id_ficha');
            const idCompetencia = document.getElementById('id_competencia');
            const idResultado = document.getElementById('id_resultado');
            const instructoresLookup = @json($instructoresLookup);
            const buscarCedula = document.getElementById('instructor_buscar_cedula');
            const hiddenIdPersona = document.getElementById('id_persona');
            const instructorCedula = document.getElementById('instructor_cedula');
            const instructorNombre = document.getElementById('instructor_nombre');
            const formReserva = document.getElementById('form_reserva');
            const jornadaIdToLabel = @json($jornadaIdToLabel ?? []);
            const jornadaDisplay = document.getElementById('jornada_ficha_display');
            const jornadaAviso = document.getElementById('jornada_ficha_aviso');
            const fechaInicioInput = document.getElementById('fecha_inicio');
            const fechaFinInput = document.getElementById('fecha_fin');
            const fechasPanel = document.getElementById('fechas_sesion_panel');
            const fechasList = document.getElementById('fechas_sesion_list');
            const fechasInputs = document.getElementById('fechas_sesion_inputs');
            const diasChecks = document.querySelectorAll('.js-dia-check');

            function ymdLocal(d) {
                var y = d.getFullYear();
                var m = String(d.getMonth() + 1).padStart(2, '0');
                var day = String(d.getDate()).padStart(2, '0');
                return y + '-' + m + '-' + day;
            }
            function aplicarMinFechasNoPasadas() {
                var hoy = ymdLocal(new Date());
                if (fechaInicioInput) {
                    fechaInicioInput.setAttribute('min', hoy);
                    if (fechaInicioInput.value && fechaInicioInput.value < hoy) {
                        fechaInicioInput.value = '';
                    }
                }
                if (fechaFinInput) {
                    var ini = fechaInicioInput && fechaInicioInput.value ? fechaInicioInput.value : '';
                    var minFin = (ini && ini >= hoy) ? ini : hoy;
                    fechaFinInput.setAttribute('min', minFin);
                    if (fechaFinInput.value && fechaFinInput.value < minFin) {
                        fechaFinInput.value = '';
                    }
                }
            }

            function idJornadaDesdeFicha() {
                if (!idFicha || !idFicha.value) return null;
                var opt = idFicha.options[idFicha.selectedIndex];
                if (!opt) return null;
                var jid = opt.getAttribute('data-id-jornada');
                if (jid === null || jid === '') return null;
                return parseInt(jid, 10);
            }

            function fechaDiaCompatibleConJornada(n, ymd) {
                if (!ymd || !/^\d{4}-\d{2}-\d{2}$/.test(ymd)) return true;
                var p = ymd.split('-').map(function(x) { return parseInt(x, 10); });
                var dt = new Date(p[0], p[1] - 1, p[2]);
                if (isNaN(dt.getTime())) return true;
                var dow = dt.getDay();
                var esFin = dow === 0 || dow === 6;
                if (n === 4) return esFin;
                return [1, 2, 3].indexOf(n) !== -1 && !esFin;
            }

            function diasSeleccionadosMarcados() {
                var out = [];
                diasChecks.forEach(function(cb) {
                    if (cb.checked && !cb.closest('.js-dia-label').classList.contains('hidden')) {
                        out.push(cb.value);
                    }
                });
                return out;
            }

            function generarFechasSesionCliente() {
                var jid = idJornadaDesdeFicha();
                var yIni = fechaInicioInput ? fechaInicioInput.value : '';
                var yFin = fechaFinInput ? fechaFinInput.value : '';
                if (jid === null || !yIni || !yFin || !/^\d{4}-\d{2}-\d{2}$/.test(yIni) || !/^\d{4}-\d{2}-\d{2}$/.test(yFin)) {
                    return [];
                }
                var diasSel = diasSeleccionadosMarcados();
                if (!diasSel.length) return [];
                var map = { lunes: 1, martes: 2, miercoles: 3, jueves: 4, viernes: 5, sabado: 6, domingo: 0 };
                var targets = {};
                diasSel.forEach(function(d) {
                    var k = d.toLowerCase();
                    if (map[k] !== undefined) targets[map[k]] = true;
                });
                var a = yIni.split('-').map(function(x) { return parseInt(x, 10); });
                var b = yFin.split('-').map(function(x) { return parseInt(x, 10); });
                var d1 = new Date(a[0], a[1] - 1, a[2]);
                var d2 = new Date(b[0], b[1] - 1, b[2]);
                if (isNaN(d1.getTime()) || isNaN(d2.getTime()) || d1 > d2) return [];
                var out = [];
                var cur = new Date(d1);
                while (cur <= d2) {
                    var y = cur.getFullYear();
                    var m = String(cur.getMonth() + 1).padStart(2, '0');
                    var day = String(cur.getDate()).padStart(2, '0');
                    var ymd = y + '-' + m + '-' + day;
                    if (targets[cur.getDay()] && fechaDiaCompatibleConJornada(jid, ymd)) {
                        out.push(ymd);
                    }
                    cur.setDate(cur.getDate() + 1);
                }
                return out;
            }

            function syncHiddenInputsDesdeLista() {
                if (!fechasInputs || !fechasList) return;
                fechasInputs.innerHTML = '';
                fechasList.querySelectorAll('li[data-ymd]').forEach(function(li) {
                    var ymd = li.getAttribute('data-ymd');
                    var inp = document.createElement('input');
                    inp.type = 'hidden';
                    inp.name = 'fechas_sesion[]';
                    inp.value = ymd;
                    fechasInputs.appendChild(inp);
                });
            }

            function formatoDmY(ymd) {
                var p = ymd.split('-');
                return p[2] + '/' + p[1] + '/' + p[0];
            }

            function reconstruirListaFechas() {
                if (!fechasList || !fechasPanel) return;
                var fechas = generarFechasSesionCliente();
                fechasList.innerHTML = '';
                fechas.forEach(function(ymd) {
                    var li = document.createElement('li');
                    li.setAttribute('data-ymd', ymd);
                    li.className = 'flex items-center justify-between gap-2 px-3 py-2 rounded-xl bg-white border border-emerald-200 text-sm text-gray-800';
                    var span = document.createElement('span');
                    span.textContent = formatoDmY(ymd);
                    var btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'shrink-0 w-8 h-8 rounded-lg bg-red-100 text-red-700 font-bold hover:bg-red-200 transition-colors';
                    btn.setAttribute('aria-label', 'Quitar fecha');
                    btn.textContent = '×';
                    btn.addEventListener('click', function() {
                        li.remove();
                        syncHiddenInputsDesdeLista();
                        actualizarHintSesiones();
                    });
                    li.appendChild(span);
                    li.appendChild(btn);
                    fechasList.appendChild(li);
                });
                syncHiddenInputsDesdeLista();
                if (fechas.length) {
                    fechasPanel.classList.remove('hidden');
                } else {
                    fechasPanel.classList.add('hidden');
                }
                aplicarOldFechasSiHay();
                actualizarHintSesiones();
            }

            function aplicarOldFechasSiHay() {
                var keep = window.__oldFechasSesion || [];
                if (!keep.length || !fechasList) return;
                var flip = {};
                keep.forEach(function(x) { flip[x] = true; });
                fechasList.querySelectorAll('li[data-ymd]').forEach(function(li) {
                    var y = li.getAttribute('data-ymd');
                    if (!flip[y]) li.remove();
                });
                syncHiddenInputsDesdeLista();
                window.__oldFechasSesion = [];
            }

            function actualizarHintSesiones() {
                var hintSes = document.getElementById('hint_sesiones_resultado');
                if (!hintSes || !idResultado) return;
                var optR = idResultado.options[idResultado.selectedIndex];
                var n = fechasList ? fechasList.querySelectorAll('li[data-ymd]').length : 0;
                if (!optR || !optR.value || !n) {
                    hintSes.classList.add('hidden');
                    hintSes.textContent = '';
                    return;
                }
                var lim = parseInt(optR.getAttribute('data-sesiones') || '0', 10);
                hintSes.textContent = 'Sesiones en la lista: ' + n + '. Límite del resultado: ' + lim + ' sesión(es).';
                hintSes.classList.remove('hidden');
                if (n > lim) {
                    hintSes.classList.add('text-red-800', 'font-bold');
                    hintSes.classList.remove('text-red-600', 'font-semibold');
                } else {
                    hintSes.classList.add('text-red-600', 'font-semibold');
                    hintSes.classList.remove('text-red-800', 'font-bold');
                }
            }

            function idProgramaDesdeFicha() {
                if (!idFicha || !idFicha.value) return null;
                var opt = idFicha.options[idFicha.selectedIndex];
                if (!opt) return null;
                var v = opt.getAttribute('data-id-programa');
                return v !== null && v !== '' ? parseInt(v, 10) : null;
            }

            function aplicarEstiloResultadoHabilitada(habilitada) {
                if (!idResultado) return;
                if (habilitada) {
                    idResultado.disabled = false;
                    idResultado.classList.remove('bg-gray-50');
                } else {
                    idResultado.disabled = true;
                    idResultado.classList.add('bg-gray-50');
                    idResultado.value = '';
                }
            }

            function filtrarCompetenciasPorPrograma() {
                if (!idCompetencia) return;
                idCompetencia.querySelectorAll('option[value]').forEach(function(o) {
                    if (o.value === '') return;
                    o.style.display = '';
                });
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
                aplicarEstiloResultadoHabilitada(idComp > 0);
            }

            function actualizarVistaDiasSemana() {
                var lv = document.querySelectorAll('.js-dia-lv');
                var fs = document.querySelectorAll('.js-dia-fs');
                if (!idFicha) return;
                var opt = idFicha.options[idFicha.selectedIndex];
                var jid = opt && opt.getAttribute('data-id-jornada');
                if (!opt || !opt.value || jid === null || jid === '') {
                    lv.forEach(function(el) { el.classList.add('hidden'); });
                    fs.forEach(function(el) { el.classList.add('hidden'); });
                    return;
                }
                var n = parseInt(jid, 10);
                if (n === 4) {
                    lv.forEach(function(el) { el.classList.add('hidden'); });
                    fs.forEach(function(el) { el.classList.remove('hidden'); });
                } else {
                    lv.forEach(function(el) { el.classList.remove('hidden'); });
                    fs.forEach(function(el) { el.classList.add('hidden'); });
                }
            }

            function actualizarJornadaDesdeFicha() {
                if (!jornadaDisplay || !idFicha) return;
                var opt = idFicha.options[idFicha.selectedIndex];
                var jid = opt && opt.getAttribute('data-id-jornada');
                if (!opt || !opt.value || jid === null || jid === '') {
                    jornadaDisplay.value = '';
                    if (jornadaAviso) { jornadaAviso.classList.add('hidden'); jornadaAviso.textContent = ''; }
                    return;
                }
                var n = parseInt(jid, 10);
                jornadaDisplay.value = jornadaIdToLabel[n] || ('ID ' + jid);
                validarFechaVsJornadaFicha();
            }

            function validarFechaVsJornadaFicha() {
                if (!jornadaAviso || !idFicha) return;
                var opt = idFicha.options[idFicha.selectedIndex];
                var jid = opt && opt.getAttribute('data-id-jornada');
                if (!jid) { jornadaAviso.classList.add('hidden'); reconstruirListaFechas(); return; }
                var n = parseInt(jid, 10);
                var yIni = fechaInicioInput ? fechaInicioInput.value : '';
                var yFin = fechaFinInput ? fechaFinInput.value : '';
                var okIni = fechaDiaCompatibleConJornada(n, yIni);
                var okFin = fechaDiaCompatibleConJornada(n, yFin);
                if (okIni && okFin) {
                    jornadaAviso.classList.add('hidden');
                    jornadaAviso.textContent = '';
                    reconstruirListaFechas();
                    return;
                }
                var esEntreSemana = [1, 2, 3].indexOf(n) !== -1;
                var msg = esEntreSemana
                    ? 'Jornada entre semana: inicio y fin deben ser lunes a viernes.'
                    : 'Jornada fin de semana: inicio y fin deben ser sábado o domingo.';
                if (!okIni && !okFin) {
                    jornadaAviso.textContent = msg;
                } else if (!okIni) {
                    jornadaAviso.textContent = esEntreSemana
                        ? 'La fecha de inicio debe ser lunes a viernes.'
                        : 'La fecha de inicio debe ser sábado o domingo.';
                } else {
                    jornadaAviso.textContent = esEntreSemana
                        ? 'La fecha de fin debe ser lunes a viernes.'
                        : 'La fecha de fin debe ser sábado o domingo.';
                }
                jornadaAviso.classList.remove('hidden');
                reconstruirListaFechas();
            }

            function alCambiarFicha() {
                if (idCompetencia) {
                    idCompetencia.value = '';
                    filtrarCompetenciasPorPrograma();
                    filtrarResultadosPorCompetencia(true);
                }
                actualizarJornadaDesdeFicha();
                actualizarVistaDiasSemana();
                reconstruirListaFechas();
            }

            if (idFicha) idFicha.addEventListener('change', alCambiarFicha);
            if (idCompetencia) {
                idCompetencia.addEventListener('change', function() {
                    filtrarResultadosPorCompetencia(true);
                    actualizarHintSesiones();
                });
            }
            if (idResultado) idResultado.addEventListener('change', actualizarHintSesiones);

            diasChecks.forEach(function(cb) {
                cb.addEventListener('change', function() { reconstruirListaFechas(); });
            });

            filtrarCompetenciasPorPrograma();
            if (idProgramaDesdeFicha() !== null) {
                filtrarResultadosPorCompetencia(false);
            } else {
                aplicarEstiloResultadoHabilitada(false);
            }
            actualizarJornadaDesdeFicha();
            actualizarVistaDiasSemana();
            aplicarMinFechasNoPasadas();

            if (fechaInicioInput) {
                fechaInicioInput.addEventListener('change', function() {
                    aplicarMinFechasNoPasadas();
                    validarFechaVsJornadaFicha();
                });
                fechaInicioInput.addEventListener('input', function() {
                    aplicarMinFechasNoPasadas();
                    validarFechaVsJornadaFicha();
                });
            }
            if (fechaFinInput) {
                fechaFinInput.addEventListener('change', validarFechaVsJornadaFicha);
                fechaFinInput.addEventListener('input', validarFechaVsJornadaFicha);
            }

            reconstruirListaFechas();

            if (formReserva) {
                formReserva.addEventListener('submit', function(e) {
                    if (idResultado) idResultado.disabled = false;
                    syncHiddenInputsDesdeLista();
                    var cnt = fechasInputs ? fechasInputs.querySelectorAll('input[name="fechas_sesion[]"]').length : 0;
                    if (!cnt) {
                        e.preventDefault();
                        showAppMessageModal({
                            type: 'warning',
                            title: 'Fechas de sesión',
                            message: 'Debe generar al menos una fecha de sesión (elija rango y días de la semana).',
                        });
                    }
                });
            }

            function sanitizarSoloDigitosCedula() {
                if (!buscarCedula) return;
                var s = String(buscarCedula.value || '').replace(/\D/g, '').slice(0, 10);
                if (buscarCedula.value !== s) {
                    buscarCedula.value = s;
                }
            }

            function sincronizarInstructorPorCedula() {
                if (!buscarCedula || !hiddenIdPersona || !instructorCedula || !instructorNombre) return;
                sanitizarSoloDigitosCedula();
                var val = String(buscarCedula.value || '');
                if (val === '') {
                    hiddenIdPersona.value = '';
                    instructorCedula.value = '';
                    instructorNombre.value = '';
                    return;
                }
                var inst = null;
                for (var i = 0; i < instructoresLookup.length; i++) {
                    if (String(instructoresLookup[i].cedula) === val) {
                        inst = instructoresLookup[i];
                        break;
                    }
                }
                if (inst) {
                    hiddenIdPersona.value = inst.id;
                    instructorCedula.value = inst.cedula;
                    instructorNombre.value = inst.nombre;
                } else {
                    hiddenIdPersona.value = '';
                    instructorCedula.value = '';
                    instructorNombre.value = '';
                }
            }
            if (buscarCedula) {
                buscarCedula.addEventListener('input', sincronizarInstructorPorCedula);
                buscarCedula.addEventListener('paste', function() {
                    setTimeout(function() { sincronizarInstructorPorCedula(); }, 0);
                });
                if (hiddenIdPersona && hiddenIdPersona.value) {
                    var oldInst = instructoresLookup.find(function(x) { return String(x.id) === String(hiddenIdPersona.value); });
                    if (oldInst) {
                        buscarCedula.value = String(oldInst.cedula || '').replace(/\D/g, '').slice(0, 10);
                        instructorCedula.value = oldInst.cedula;
                        instructorNombre.value = oldInst.nombre;
                    }
                }
            }
        });
    </script>
@endsection
