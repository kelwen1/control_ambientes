@extends('layouts.app')

@section('title', 'Editar Resultado')

@section('content')
    @php
        $horasPorSesion = 6;
        $etiquetaCatalogo = 'Catálogo común (todas las fichas / programas)';
        $nombrePrograma = optional($resultado->competencia?->programa)->nombre_programa ?? $etiquetaCatalogo;
        $nombreCompetencia = $resultado->competencia->nombre_competencia ?? '—';
    @endphp
    <div class="mb-6 sm:mb-8 animate-fade-slide-up">
        <h1 class="text-2xl sm:text-3xl font-bold text-[#39B54A] mb-2 tracking-tight">
            Editar Resultado
        </h1>
        <p class="text-gray-600 text-sm sm:text-base">Modifique la denominación, las horas y revise las sesiones calculadas automáticamente.</p>
    </div>

    <div class="card-premium bg-white rounded-xl shadow-card p-6 sm:p-8 hover:shadow-card-hover transition-shadow duration-300">
        <form method="POST"
              action="{{ route('resultados.update', $resultado->id_resultado) }}"
              class="space-y-6"
              data-duracion-complejo="{{ $duracionComplejo }}"
              data-sesiones-totales="{{ $sesionesTotalesCompetencia }}"
              data-horas-cupo="{{ (int) max(0, $horasRestantes) }}"
              data-horas-por-sesion="{{ $horasPorSesion }}">
            @csrf
            @method('PUT')

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
                          placeholder="Solo letras y espacios, máx. 150 caracteres"
                          oninput="this.value = this.value.replace(/[^\p{L}\s]/gu, '').slice(0,150)">{{ old('denominacion', $resultado->denominacion) }}</textarea>
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
                           value="{{ $nombrePrograma }}"
                           readonly
                           tabindex="-1"
                           class="w-full px-4 py-3 border-2 border-gray-200 bg-gray-50 text-gray-700 rounded-xl text-sm sm:text-base cursor-not-allowed">
                </div>

                <div>
                    <label for="id_competencia_display" class="block text-sm sm:text-base font-semibold text-gray-700 mb-2">
                        Competencia
                    </label>
                    <input type="text"
                           id="id_competencia_display"
                           value="{{ $nombreCompetencia }}"
                           readonly
                           tabindex="-1"
                           class="w-full px-4 py-3 border-2 border-gray-200 bg-gray-50 text-gray-700 rounded-xl text-sm sm:text-base cursor-not-allowed">
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
                           value="{{ old('horas', $resultado->horas) }}"
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
                           class="w-full px-4 py-3 border-2 border-gray-200 bg-gray-50 text-gray-800 rounded-xl text-sm sm:text-base cursor-default">
                    <p class="mt-1 text-xs text-gray-500">Horas efectivas (tras el límite) ÷ {{ $horasPorSesion }}. Se guardan al actualizar.</p>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row gap-4 pt-4 border-t border-gray-200">
                <button type="submit"
                        class="btn-primary flex-1 px-6 py-3 bg-[#39B54A] text-white rounded-xl font-semibold hover:bg-[#2d8f3a] hover:shadow-glow transition-all duration-200 shadow-md">
                    Actualizar Resultado
                </button>
                <a href="{{ route('resultados.index', ['competencia' => $resultado->id_competencia]) }}"
                   class="flex-1 px-6 py-3 bg-gray-300 text-gray-700 rounded-xl font-semibold hover:bg-gray-400 transition-all duration-200 text-center">
                    Cancelar
                </a>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.querySelector('form[data-duracion-complejo]');
            const horasInput = document.getElementById('horas');
            const sesionesDisplay = document.getElementById('sesiones_display');
            const horasCupoAviso = document.getElementById('horasCupoAviso');
            const horasLimiteLegend = document.getElementById('horasLimiteLegend');
            const horasErrorServer = document.getElementById('horas-error-server');
            if (!form || !horasInput || !sesionesDisplay) return;

            const HORAS_POR_SESION = form.dataset.horasPorSesion ? parseInt(String(form.dataset.horasPorSesion), 10) : 6;
            const dComp = parseInt(String(form.getAttribute('data-duracion-complejo') || '0').trim(), 10) || 0;
            var maxHorasCupoForResult = parseInt(String(form.getAttribute('data-horas-cupo') || '0').trim(), 10);
            if (isNaN(maxHorasCupoForResult) || maxHorasCupoForResult < 0) {
                maxHorasCupoForResult = 0;
            }

            if (horasLimiteLegend) {
                if (maxHorasCupoForResult >= 1 && maxHorasCupoForResult < HORAS_POR_SESION && dComp > 0) {
                    horasLimiteLegend.textContent = 'Solo puede asignar hasta ' + maxHorasCupoForResult + ' h a este resultado; hacen falta al menos ' + HORAS_POR_SESION + ' h (1 sesión). Reduzca horas en otros resultados o ajuste la competencia.';
                    horasLimiteLegend.classList.remove('hidden');
                } else if (maxHorasCupoForResult > 0 && dComp > 0) {
                    var enOtros = Math.max(0, dComp - maxHorasCupoForResult);
                    if (enOtros > 0) {
                        horasLimiteLegend.innerHTML = 'Puede usar hasta <span style="color:#39B54A;font-weight:600">' + maxHorasCupoForResult + ' h</span> en este resultado (<span style="color:#39B54A;font-weight:600">' + dComp + ' h</span> totales en el complejo; <span style="color:#39B54A;font-weight:600">' + enOtros + ' h</span> ya en <strong>otros</strong> resultados). Mínimo ' + HORAS_POR_SESION + ' h. Si escribe más, se ajusta.';
                    } else {
                        horasLimiteLegend.innerHTML = 'Puede usar hasta <span style="color:#39B54A;font-weight:600">' + maxHorasCupoForResult + ' h</span> (cupo del complejo: <span style="color:#39B54A;font-weight:600">' + dComp + ' h</span>; ningún otro resultado consume horas aún). Mínimo ' + HORAS_POR_SESION + ' h. Si escribe más, se ajusta.';
                    }
                    horasLimiteLegend.classList.remove('hidden');
                } else if (dComp > 0 && maxHorasCupoForResult < 1) {
                    horasLimiteLegend.textContent = 'No quedan horas disponibles en el complejo para este resultado.';
                    horasLimiteLegend.classList.remove('hidden');
                } else if (dComp < 1) {
                    horasLimiteLegend.textContent = 'No hay cupo en el complejo calculado; revise la competencia (duración / porcentaje).';
                    horasLimiteLegend.classList.remove('hidden');
                } else {
                    horasLimiteLegend.textContent = '';
                    horasLimiteLegend.classList.add('hidden');
                }
            }

            function syncSesiones() {
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
                        horasCupoAviso.textContent = 'Solo puede usar hasta ' + maxHorasCupoForResult + ' h en este resultado; el valor se ajustó a ese máximo.';
                        horasCupoAviso.classList.remove('hidden');
                    }
                }
            }

            var insufEdit = maxHorasCupoForResult >= 1 && maxHorasCupoForResult < HORAS_POR_SESION;
            horasInput.placeholder = (maxHorasCupoForResult > 0 && !insufEdit) ? '' : (insufEdit ? 'Mín. ' + HORAS_POR_SESION + ' h' : 'Sin cupo');

            if (maxHorasCupoForResult < 1 || insufEdit) {
                horasInput.setAttribute('readonly', 'readonly');
                horasInput.classList.add('bg-gray-100', 'cursor-not-allowed');
            } else {
                horasInput.removeAttribute('readonly');
                horasInput.classList.remove('bg-gray-100', 'cursor-not-allowed');
            }

            function normalizarHorasEdit() {
                if (horasInput.readOnly) {
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

            horasInput.addEventListener('input', normalizarHorasEdit);
            horasInput.addEventListener('keyup', normalizarHorasEdit);
            horasInput.addEventListener('blur', normalizarHorasEdit);
            horasInput.addEventListener('paste', function () {
                setTimeout(normalizarHorasEdit, 0);
            });

            enforceHorasCupo(true);
            syncSesiones();

            if (form) {
                form.addEventListener('submit', function (e) {
                    if (maxHorasCupoForResult <= 0) {
                        e.preventDefault();
                        showAppMessageModal({
                            type: 'error',
                            title: 'Sin cupo disponible',
                            message: 'No hay cupo de horas en el complejo para este resultado.',
                        });
                        return false;
                    }
                    var h = parseInt(horasInput.value.replace(/\D/g, ''), 10);
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
