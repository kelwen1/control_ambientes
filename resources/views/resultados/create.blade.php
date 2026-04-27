@extends('layouts.app')

@section('title', 'Nuevo Resultado')

@section('content')
    @php
        $horasPorSesion = 6;
        $etiquetaCatalogo = 'Catálogo común (todas las fichas / programas)';
        $nombreProgramaFijo = !empty($competenciaPreseleccionada)
            ? (optional($competenciaPreseleccionada->programa)->nombre_programa ?? $etiquetaCatalogo)
            : '';
        $dComplejoIni = !empty($competenciaPreseleccionada) ? $competenciaPreseleccionada->horasDuracionEnComplejo() : 0;
        $sesTotCompIni = $dComplejoIni > 0 ? intdiv($dComplejoIni, $horasPorSesion) : 0;
        $restantesIni = $horasRestantesPreseleccion ?? 0;
    @endphp
    <div class="mb-6 sm:mb-8 animate-fade-slide-up">
        <h1 class="text-2xl sm:text-3xl font-bold text-[#39B54A] mb-2 tracking-tight">
            Crear Nuevo Resultado
        </h1>
        <p class="text-gray-600 text-sm sm:text-base">Asocia el resultado a una competencia.</p>
    </div>

    <div class="card-premium bg-white rounded-xl shadow-card p-6 sm:p-8 hover:shadow-card-hover transition-shadow duration-300">
        <form id="formResultadoCreate"
              method="POST"
              action="{{ route('resultados.store') }}"
              class="space-y-6"
              data-old-programa="{{ old('id_programa') }}"
              data-old-competencia="{{ old('id_competencia', $competenciaPreseleccionada->id_competencia ?? '') }}"
              data-fixed-competencia="{{ $competenciaPreseleccionada->id_competencia ?? '' }}"
              data-fixed-programa=""
              data-fixed-programa-nombre="{{ $nombreProgramaFijo !== '' ? $nombreProgramaFijo : $etiquetaCatalogo }}"
              data-fixed-duracion-complejo="{{ $dComplejoIni }}"
              data-fixed-sesiones-totales="{{ $sesTotCompIni }}"
              data-fixed-horas-restantes="{{ (int) ($restantesIni ?? 0) }}"
              data-horas-por-sesion="{{ $horasPorSesion }}">
            @csrf

            <div>
                <label for="denominacion" class="block text-sm sm:text-base font-semibold text-gray-700 mb-2">
                    Denominación del Resultado <span class="text-red-500">*</span>
                </label>
                <textarea name="denominacion"
                          id="denominacion"
                          rows="3"
                          required
                          maxlength="150"
                          class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:border-[#39B54A] focus:ring-2 focus:ring-[#39B54A]/20 focus:outline-none transition-all duration-200 text-sm sm:text-base"
                          placeholder="Describe el resultado de aprendizaje (solo letras y espacios, máx. 150 caracteres)"
                          oninput="this.value = this.value.replace(/[^\p{L}\s]/gu, '').slice(0,150)">{{ old('denominacion') }}</textarea>
                <p class="mt-1 text-xs text-gray-500">Máximo 150 caracteres; solo letras y espacios.</p>
                @error('denominacion')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label for="id_programa_display" class="block text-sm sm:text-base font-semibold text-gray-700 mb-2">
                        Ámbito
                    </label>
                    <input type="text"
                           id="id_programa_display"
                           value="{{ $nombreProgramaFijo !== '' ? $nombreProgramaFijo : $etiquetaCatalogo }}"
                           readonly
                           class="w-full px-4 py-3 border-2 border-gray-200 bg-gray-50 text-gray-700 rounded-xl text-sm sm:text-base cursor-not-allowed">
                    <input type="hidden" name="id_programa" id="id_programa" value="{{ old('id_programa', '') }}">
                </div>

                <div>
                    <label for="id_competencia" class="block text-sm sm:text-base font-semibold text-gray-700 mb-2">
                        Competencia <span class="text-red-500">*</span>
                    </label>
                    @if(!empty($competenciaPreseleccionada))
                    <input type="text"
                           id="id_competencia_display"
                           value="{{ $competenciaPreseleccionada->nombre_competencia }}"
                           readonly
                           class="w-full px-4 py-3 border-2 border-gray-200 bg-gray-50 text-gray-700 rounded-xl text-sm sm:text-base cursor-not-allowed">
                    <input type="hidden" name="id_competencia" id="id_competencia" value="{{ $competenciaPreseleccionada->id_competencia }}">
                    @else
                    <select name="id_competencia"
                            id="id_competencia"
                            required
                            class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:border-[#39B54A] focus:ring-2 focus:ring-[#39B54A]/20 focus:outline-none transition-all duration-200 text-sm sm:text-base">
                        <option value="">Seleccione una competencia</option>
                        @foreach($competencias as $competencia)
                            @php
                                $cantidad = (int) ($competencia->cantidad_resultados ?? 1);
                                $dComp = $competencia->horasDuracionEnComplejo();
                                $usadas = (int) ($horasUsadasPorCompetencia[$competencia->id_competencia] ?? 0);
                                $restantes = max(0, $dComp - $usadas);
                                $sesTotComp = $dComp > 0 ? intdiv($dComp, $horasPorSesion) : 0;
                                $resultadosActuales = $resultadosPorCompetencia[$competencia->id_competencia] ?? 0;
                                $estaLlena = $cantidad > 0 && $resultadosActuales >= $cantidad;
                            @endphp
                            <option value="{{ $competencia->id_competencia }}"
                                    data-programa-nombre="{{ $etiquetaCatalogo }}"
                                    data-duracion-complejo="{{ $dComp }}"
                                    data-sesiones-totales-comp="{{ $sesTotComp }}"
                                    data-horas-restantes="{{ $restantes }}"
                                    data-cantidad="{{ $cantidad }}"
                                    data-llena="{{ $estaLlena ? '1' : '0' }}"
                                    {{ old('id_competencia') == $competencia->id_competencia ? 'selected' : '' }}
                                    {{ $estaLlena ? 'disabled' : '' }}>
                                {{ $competencia->nombre_competencia }}{{ $estaLlena ? ' (máx. alcanzado)' : '' }}
                            </option>
                        @endforeach
                    </select>
                    @endif
                    @error('id_competencia')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label for="horas" class="block text-sm sm:text-base font-semibold text-gray-700 mb-2">
                        Horas <span class="text-red-500">*</span>
                    </label>
                    <p id="horasLimiteLegend" class="mb-2 text-sm text-gray-700 leading-snug hidden"></p>
                    <input type="text"
                           name="horas"
                           id="horas"
                           value="{{ old('horas') }}"
                           required
                           inputmode="numeric"
                           maxlength="7"
                           autocomplete="off"
                           placeholder=""
                           class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:border-[#39B54A] focus:ring-2 focus:ring-[#39B54A]/20 focus:outline-none transition-all duration-200 text-sm sm:text-base">
                    <p class="mt-1 text-xs text-gray-500">Mínimo {{ $horasPorSesion }} h (1 sesión de {{ $horasPorSesion }} h). Enteros. Si supera el máximo del cupo, el valor se ajusta; las sesiones = horas ÷ {{ $horasPorSesion }}.</p>
                    <p id="horasCupoAviso" class="mt-1 text-sm text-amber-800 bg-amber-50 border border-amber-200 rounded-lg px-3 py-2 hidden"></p>
                    @error('horas')
                        <p id="horas-error-server" class="mt-1 text-sm text-red-600" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="sesiones_display" class="block text-sm sm:text-base font-semibold text-gray-700 mb-2">
                        Sesiones
                    </label>
                    <input type="text"
                           id="sesiones_display"
                           readonly
                           tabindex="-1"
                           value=""
                           placeholder="—"
                           class="w-full px-4 py-3 border-2 border-gray-200 bg-gray-50 text-gray-800 rounded-xl text-sm sm:text-base cursor-default">
                    <p class="mt-1 text-xs text-gray-500">Horas efectivas (tras el límite) ÷ {{ $horasPorSesion }}.</p>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row gap-4 pt-4 border-t border-gray-200">
                <button type="submit"
                        class="btn-primary flex-1 px-6 py-3 bg-[#39B54A] text-white rounded-xl font-semibold hover:bg-[#2d8f3a] hover:shadow-glow transition-all duration-200 shadow-md">
                    Guardar Resultado
                </button>
                <a href="{{ !empty($competenciaPreseleccionada) ? route('resultados.index', ['competencia' => $competenciaPreseleccionada->id_competencia]) : route('resultados.index') }}"
                   class="flex-1 px-6 py-3 bg-gray-300 text-gray-700 rounded-xl font-semibold hover:bg-gray-400 transition-all duration-200 text-center">
                    Cancelar
                </a>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const programaDisplay = document.getElementById('id_programa_display');
            const programaHidden = document.getElementById('id_programa');
            const competenciaSelect = document.getElementById('id_competencia');
            const horasInput = document.getElementById('horas');
            const sesionesDisplay = document.getElementById('sesiones_display');
            const horasCupoAviso = document.getElementById('horasCupoAviso');
            const horasLimiteLegend = document.getElementById('horasLimiteLegend');
            const horasErrorServer = document.getElementById('horas-error-server');
            const form = document.getElementById('formResultadoCreate');
            const HORAS_POR_SESION = form && form.dataset.horasPorSesion ? parseInt(String(form.dataset.horasPorSesion), 10) : 6;
            const oldCompetencia = form && form.dataset.oldCompetencia ? form.dataset.oldCompetencia : null;
            const fixedCompetencia = form && form.dataset.fixedCompetencia ? form.dataset.fixedCompetencia : '';
            const fixedPrograma = form && form.dataset.fixedPrograma ? form.dataset.fixedPrograma : '';
            const fixedProgramaNombre = form && form.dataset.fixedProgramaNombre ? form.dataset.fixedProgramaNombre : '';
            let maxHorasCupoForResult = 0;

            function aplicarContextoCompetencia(dComp, sesTot, restantes) {
                dComp = parseInt(String(dComp).trim(), 10);
                if (isNaN(dComp)) { dComp = 0; }
                sesTot = parseInt(String(sesTot).trim(), 10);
                if (isNaN(sesTot)) { sesTot = 0; }
                var restNum = parseInt(String(restantes).trim(), 10);
                if (isNaN(restNum) || restNum < 0) { restNum = 0; }
                maxHorasCupoForResult = restNum;
                if (horasLimiteLegend) {
                    if (maxHorasCupoForResult > 0 && dComp > 0) {
                        var yaUsadas = Math.max(0, dComp - restNum);
                        if (yaUsadas > 0) {
                            horasLimiteLegend.innerHTML = 'Quedan <span style="color:#39B54A;font-weight:600">' + restNum + ' h</span> libres en el complejo (<span style="color:#39B54A;font-weight:600">' + dComp + ' h</span> totales; <span style="color:#39B54A;font-weight:600">' + yaUsadas + ' h</span> ya en otros resultados). Ese es el máximo que puede poner en <strong>este</strong> resultado. Si escribe más, se ajusta.';
                        } else {
                            horasLimiteLegend.innerHTML = 'Puede usar hasta <span style="color:#39B54A;font-weight:600">' + restNum + ' h</span> (todo el cupo del complejo está libre: <span style="color:#39B54A;font-weight:600">' + dComp + ' h</span>). Si escribe más, se ajusta.';
                        }
                        horasLimiteLegend.classList.remove('hidden');
                    } else if (dComp > 0 && maxHorasCupoForResult >= 1 && maxHorasCupoForResult < HORAS_POR_SESION) {
                        horasLimiteLegend.textContent = 'Solo quedan ' + maxHorasCupoForResult + ' h libres; hacen falta al menos ' + HORAS_POR_SESION + ' h (1 sesión) para un resultado nuevo.';
                        horasLimiteLegend.classList.remove('hidden');
                    } else if (dComp > 0 && maxHorasCupoForResult < 1) {
                        horasLimiteLegend.textContent = 'No quedan horas disponibles en el complejo para un resultado nuevo.';
                        horasLimiteLegend.classList.remove('hidden');
                    } else if (dComp < 1) {
                        horasLimiteLegend.textContent = 'No hay cupo en el complejo calculado; revise la competencia (duración / porcentaje).';
                        horasLimiteLegend.classList.remove('hidden');
                    } else {
                        horasLimiteLegend.textContent = '';
                        horasLimiteLegend.classList.add('hidden');
                    }
                }
                if (horasInput) {
                    var insufCupo = maxHorasCupoForResult >= 1 && maxHorasCupoForResult < HORAS_POR_SESION;
                    horasInput.placeholder = (maxHorasCupoForResult > 0 && !insufCupo) ? '' : (insufCupo ? 'Mín. ' + HORAS_POR_SESION + ' h' : 'Sin cupo');
                    if (maxHorasCupoForResult < 1 || insufCupo) {
                        horasInput.setAttribute('readonly', 'readonly');
                        horasInput.classList.add('bg-gray-100', 'cursor-not-allowed');
                        horasInput.value = '';
                    } else {
                        horasInput.removeAttribute('readonly');
                        horasInput.classList.remove('bg-gray-100', 'cursor-not-allowed');
                    }
                }
                if (horasCupoAviso) {
                    horasCupoAviso.classList.add('hidden');
                    horasCupoAviso.textContent = '';
                }
                if (maxHorasCupoForResult > 0) {
                    enforceHorasCupo(true);
                } else if (horasCupoAviso) {
                    horasCupoAviso.classList.add('hidden');
                    horasCupoAviso.textContent = '';
                }
                syncSesiones();
            }

            function syncSesiones() {
                if (!horasInput || !sesionesDisplay) return;
                var raw = horasInput.value.replace(/\D/g, '');
                var h = parseInt(raw, 10);
                if (isNaN(h) || h < 1) {
                    sesionesDisplay.value = '';
                    return;
                }
                var efectivas = h;
                if (maxHorasCupoForResult >= 1) {
                    efectivas = Math.min(h, maxHorasCupoForResult);
                }
                sesionesDisplay.value = String(Math.floor(efectivas / HORAS_POR_SESION));
            }

            function enforceHorasCupo(mostrarAviso) {
                if (!horasInput) return;
                if (horasCupoAviso && !mostrarAviso) {
                    horasCupoAviso.classList.add('hidden');
                }
                horasInput.classList.remove('border-amber-500', 'ring-2', 'ring-amber-300');
                var raw = horasInput.value.replace(/\D/g, '');
                var h = parseInt(raw, 10);
                if (isNaN(h) || h < 1) {
                    if (horasCupoAviso) {
                        horasCupoAviso.classList.add('hidden');
                        horasCupoAviso.textContent = '';
                    }
                    return;
                }
                if (maxHorasCupoForResult < 1) {
                    return;
                }
                if (h > maxHorasCupoForResult) {
                    horasInput.value = String(maxHorasCupoForResult);
                    horasInput.classList.add('border-amber-500', 'ring-2', 'ring-amber-300');
                    if (horasCupoAviso && mostrarAviso) {
                        horasCupoAviso.textContent = 'Solo quedan ' + maxHorasCupoForResult + ' h libres en el complejo; el valor se ajustó a ese máximo.';
                        horasCupoAviso.classList.remove('hidden');
                    }
                }
            }

            function onCompetenciaChange() {
                const opt = competenciaSelect.options[competenciaSelect.selectedIndex];
                if (opt && opt.value) {
                    const pnombre = opt.getAttribute('data-programa-nombre') || '';
                    if (programaDisplay) programaDisplay.value = pnombre;
                    if (programaHidden) programaHidden.value = '';
                    aplicarContextoCompetencia(
                        opt.getAttribute('data-duracion-complejo'),
                        opt.getAttribute('data-sesiones-totales-comp'),
                        opt.getAttribute('data-horas-restantes')
                    );
                } else {
                    if (programaDisplay) programaDisplay.value = '';
                    if (programaHidden) programaHidden.value = '';
                    maxHorasCupoForResult = 0;
                    if (horasLimiteLegend) {
                        horasLimiteLegend.textContent = '';
                        horasLimiteLegend.classList.add('hidden');
                    }
                    if (horasInput) {
                        horasInput.setAttribute('readonly', 'readonly');
                        horasInput.classList.add('bg-gray-100', 'cursor-not-allowed');
                        horasInput.value = '';
                        horasInput.placeholder = 'Seleccione competencia';
                    }
                    if (sesionesDisplay) sesionesDisplay.value = '';
                }
            }

            function normalizarHorasInput() {
                if (!horasInput || horasInput.readOnly) {
                    return;
                }
                if (horasErrorServer) {
                    horasErrorServer.classList.add('hidden');
                    horasErrorServer.setAttribute('aria-hidden', 'true');
                }
                horasInput.value = horasInput.value.replace(/\D/g, '').slice(0, 7);
                enforceHorasCupo(true);
                syncSesiones();
            }

            if (horasInput) {
                horasInput.addEventListener('input', normalizarHorasInput);
                horasInput.addEventListener('keyup', normalizarHorasInput);
                horasInput.addEventListener('blur', normalizarHorasInput);
                horasInput.addEventListener('paste', function () {
                    setTimeout(normalizarHorasInput, 0);
                });
            }

            if (fixedCompetencia) {
                const competenciaHidden = document.getElementById('id_competencia');
                if (competenciaHidden && !competenciaHidden.value) {
                    competenciaHidden.value = fixedCompetencia;
                }
                if (programaHidden && !programaHidden.value) {
                    programaHidden.value = fixedPrograma;
                }
                if (programaDisplay) {
                    programaDisplay.value = fixedProgramaNombre;
                }
                var fd = form.getAttribute('data-fixed-duracion-complejo') || '0';
                var fs = form.getAttribute('data-fixed-sesiones-totales') || '0';
                var fr = form.getAttribute('data-fixed-horas-restantes') || '0';
                aplicarContextoCompetencia(fd, fs, fr);
                normalizarHorasInput();
            } else if (competenciaSelect && competenciaSelect.tagName === 'SELECT') {
                competenciaSelect.addEventListener('change', onCompetenciaChange);
                if (oldCompetencia) {
                    const opt = competenciaSelect.querySelector('option[value="' + oldCompetencia + '"]');
                    if (opt && !opt.disabled) {
                        competenciaSelect.value = oldCompetencia;
                    }
                }
                onCompetenciaChange();
                normalizarHorasInput();
            }

            if (form) {
                form.addEventListener('submit', function (e) {
                    if (maxHorasCupoForResult <= 0) {
                        e.preventDefault();
                        showAppMessageModal({
                            type: 'error',
                            title: 'Sin cupo disponible',
                            message: 'No hay horas disponibles en el complejo para asignar a este resultado.',
                        });
                        return false;
                    }
                    var h = parseInt((horasInput && horasInput.value) ? horasInput.value.replace(/\D/g, '') : '', 10);
                    if (isNaN(h) || h < 1 || h > maxHorasCupoForResult) {
                        e.preventDefault();
                        showAppMessageModal({
                            type: 'error',
                            title: 'Horas fuera de rango',
                            message: 'Las horas deben estar entre 1 y ' + maxHorasCupoForResult + ' (cupo en el complejo).',
                        });
                        return false;
                    }
                });
            }
        });
    </script>
@endsection

