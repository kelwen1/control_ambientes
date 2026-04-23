@extends('layouts.app')

@section('title', 'Crear Ambiente')

@section('content')
    <div class="mb-6 sm:mb-8 animate-fade-slide-up">
        <h1 class="text-2xl sm:text-3xl font-bold text-[#39B54A] mb-2 tracking-tight">
            Crear Nuevo Ambiente
        </h1>
        <p class="text-gray-600 text-sm sm:text-base">Completa el formulario para registrar un nuevo ambiente</p>
    </div>

    <div class="card-premium bg-white rounded-xl shadow-card p-6 sm:p-8 hover:shadow-card-hover transition-shadow duration-300">
        <form method="POST" action="{{ route('ambientes.gestion.store') }}" class="space-y-6" id="formCrearAmbiente">
            @csrf

            <div>
                <label for="num_ambiente" class="block text-sm sm:text-base font-semibold text-gray-700 mb-2">
                    Número de Ambiente <span class="text-red-500">*</span>
                </label>
                <input type="text"
                       name="num_ambiente"
                       id="num_ambiente"
                       value="{{ old('num_ambiente') }}"
                       required
                       inputmode="numeric"
                       autocomplete="off"
                       maxlength="2"
                       pattern="[0-9]{1,2}"
                       class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:border-[#39B54A] focus:ring-2 focus:ring-[#39B54A]/20 focus:outline-none transition-all duration-200 text-sm sm:text-base"
                       placeholder="1 o 2 dígitos, ej: 12"
                       oninput="this.value = this.value.replace(/\D/g, '').slice(0, 2); window.ambienteNumeroCambiado && window.ambienteNumeroCambiado();">
                @error('num_ambiente')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror

                {{-- Panel de estado: número libre / en uso + regla de alta en Disponible --}}
                <div id="panelEstadoNumero"
                     class="mt-4 rounded-2xl border-2 border-dashed border-gray-200 bg-gradient-to-br from-gray-50 to-white px-4 py-4 sm:px-5 sm:py-4 shadow-sm transition-all duration-300"
                     role="status"
                     aria-live="polite">
                    <div class="flex items-start gap-3 sm:gap-4">
                        <div id="panelEstadoNumeroIcono"
                             class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-gray-200/80 text-xl transition-colors duration-300"
                             aria-hidden="true">ℹ️</div>
                        <div class="min-w-0 flex-1 space-y-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="text-xs font-bold uppercase tracking-wide text-gray-500">Estado del número</span>
                                <span id="panelEstadoNumeroBadge"
                                      class="hidden rounded-full px-2.5 py-0.5 text-xs font-semibold"></span>
                            </div>
                            <p id="panelEstadoNumeroTexto" class="text-sm text-gray-600 leading-relaxed">
                                Escribe <span class="font-medium text-gray-800">1 o 2 dígitos</span> para comprobar si está <span class="font-medium text-gray-800">libre</span>.
                                Si puedes crear el ambiente, al guardar quedará como
                                <span class="font-semibold text-[#39B54A]">Disponible</span>.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label for="capacidad_max" class="block text-sm sm:text-base font-semibold text-gray-700 mb-2">
                        Capacidad Máxima <span class="text-red-500">*</span>
                    </label>
                    <input type="text"
                           name="capacidad_max"
                           id="capacidad_max"
                           value="{{ old('capacidad_max') }}"
                           required
                           inputmode="numeric"
                           maxlength="2"
                           pattern="[0-9]{1,2}"
                           class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:border-[#39B54A] focus:ring-2 focus:ring-[#39B54A]/20 focus:outline-none transition-all duration-200 text-sm sm:text-base"
                           placeholder="Entre 30 y 40"
                           oninput="let v=this.value.replace(/\D/g,'').slice(0,2); if(v!==''){let n=parseInt(v,10); if(n>40)v='40'; else if(v.length===2 && n<30)v='30';} this.value=v;">
                    @error('capacidad_max')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="id_tipo_ambiente" class="block text-sm sm:text-base font-semibold text-gray-700 mb-2">
                        Tipo de Ambiente <span class="text-red-500">*</span>
                    </label>
                    <select name="id_tipo_ambiente"
                            id="id_tipo_ambiente"
                            required
                            class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:border-[#39B54A] focus:ring-2 focus:ring-[#39B54A]/20 focus:outline-none transition-all duration-200 text-sm sm:text-base">
                        <option value="">Seleccione un tipo</option>
                        @foreach($tipos as $id => $label)
                            <option value="{{ $id }}" {{ old('id_tipo_ambiente') == $id ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    @error('id_tipo_ambiente')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex flex-col sm:flex-row gap-4 pt-4 border-t border-gray-200">
                <button type="submit" id="btnGuardarAmbiente"
                        class="btn-primary flex-1 px-6 py-3 bg-[#39B54A] text-white rounded-xl font-semibold hover:bg-[#2d8f3a] hover:shadow-glow transition-all duration-200 shadow-md disabled:opacity-50 disabled:cursor-not-allowed">
                    Guardar Ambiente
                </button>
                <a href="{{ route('ambientes.gestion.index') }}"
                   class="flex-1 px-6 py-3 bg-gray-300 text-gray-700 rounded-xl font-semibold hover:bg-gray-400 transition-all duration-200 text-center">
                    Cancelar
                </a>
            </div>
        </form>
    </div>

    <script>
        (function () {
            const urlVerificar = @json(route('ambientes.gestion.verificar-numero'));
            const inputNum = document.getElementById('num_ambiente');
            const panel = document.getElementById('panelEstadoNumero');
            const icono = document.getElementById('panelEstadoNumeroIcono');
            const badge = document.getElementById('panelEstadoNumeroBadge');
            const texto = document.getElementById('panelEstadoNumeroTexto');
            const form = document.getElementById('formCrearAmbiente');
            let timer = null;
            let numeroDisponible = null;

            var TXT_INICIO = 'Escribe <span class="font-medium text-gray-800">1 o 2 dígitos</span> para comprobar si el número está <span class="font-medium text-gray-800">libre</span>. Si puedes crear el ambiente, al guardar quedará como <span class="font-semibold text-[#39B54A]">Disponible</span>.';

            function setEstadoVisual(modo, htmlDetalle, badgeText) {
                panel.className = 'mt-4 rounded-2xl border-2 px-4 py-4 sm:px-5 sm:py-4 shadow-sm transition-all duration-300 ';
                icono.className = 'flex h-11 w-11 shrink-0 items-center justify-center rounded-xl text-xl transition-colors duration-300 ';
                badge.className = 'rounded-full px-2.5 py-0.5 text-xs font-semibold ';
                if (modo === 'idle') {
                    panel.className += 'border-dashed border-gray-200 bg-gradient-to-br from-gray-50 to-white';
                    icono.className += 'bg-gray-200/80';
                    icono.textContent = 'ℹ️';
                    badge.textContent = '';
                    badge.classList.add('hidden');
                    texto.innerHTML = TXT_INICIO;
                    return;
                }
                badge.classList.remove('hidden');
                if (modo === 'loading') {
                    panel.className += 'border-blue-200 bg-gradient-to-br from-blue-50/80 to-white';
                    icono.className += 'bg-blue-100 text-blue-600 animate-pulse';
                    icono.textContent = '⏳';
                    badge.textContent = badgeText || 'Comprobando';
                    badge.className += 'bg-blue-100 text-blue-800';
                    texto.innerHTML = htmlDetalle;
                    return;
                }
                if (modo === 'ok') {
                    panel.className += 'border-[#39B54A] bg-gradient-to-br from-green-50 to-white ring-1 ring-[#39B54A]/20';
                    icono.className += 'bg-[#39B54A]/15 text-[#39B54A]';
                    icono.textContent = '✓';
                    badge.textContent = badgeText || 'Disponible';
                    badge.className += 'bg-[#39B54A] text-white shadow-sm';
                    texto.innerHTML = htmlDetalle;
                    return;
                }
                if (modo === 'ocupado') {
                    panel.className += 'border-red-300 bg-gradient-to-br from-red-50 to-white ring-1 ring-red-100';
                    icono.className += 'bg-red-100 text-red-600';
                    icono.textContent = '✕';
                    badge.textContent = badgeText || 'Ocupado';
                    badge.className += 'bg-red-600 text-white shadow-sm';
                    texto.innerHTML = htmlDetalle;
                    return;
                }
                if (modo === 'error') {
                    panel.className += 'border-amber-300 bg-gradient-to-br from-amber-50 to-white';
                    icono.className += 'bg-amber-100 text-amber-700';
                    icono.textContent = '⚠';
                    badge.textContent = badgeText || 'Atención';
                    badge.className += 'bg-amber-500 text-white';
                    texto.innerHTML = htmlDetalle;
                    return;
                }
            }

            function setFeedbackIdle() {
                numeroDisponible = null;
                setEstadoVisual('idle');
            }

            async function verificar() {
                const raw = (inputNum.value || '').trim();
                if (!raw) {
                    setFeedbackIdle();
                    return;
                }
                if (!/^\d{1,2}$/.test(raw)) {
                    numeroDisponible = null;
                    setEstadoVisual('error', 'El número debe tener <span class="font-medium">1 o 2 dígitos</span> (solo números).', 'Formato');
                    return;
                }
                setEstadoVisual('loading', 'Consultando si el número <span class="font-semibold text-gray-800">' + escapeHtml(raw) + '</span> está libre…', 'Comprobando');
                numeroDisponible = null;
                const u = new URL(urlVerificar, window.location.origin);
                u.searchParams.set('num_ambiente', raw);
                try {
                    const r = await fetch(u.toString(), {
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                        credentials: 'same-origin',
                    });
                    const data = await r.json().catch(function () { return {}; });
                    if (!r.ok) {
                        var errMsg = data.message;
                        if (data.errors && data.errors.num_ambiente && data.errors.num_ambiente[0]) {
                            errMsg = data.errors.num_ambiente[0];
                        }
                        setEstadoVisual('error', escapeHtml(errMsg || 'No se pudo verificar.'), 'Error');
                        return;
                    }
                    if (data.disponible) {
                        numeroDisponible = true;
                        var detalle = (data.mensaje ? escapeHtml(data.mensaje) + ' ' : '') +
                            'El ambiente se registrará con estado <span class="font-semibold text-[#39B54A]">Disponible</span>.';
                        setEstadoVisual('ok', detalle, 'Disponible');
                    } else {
                        numeroDisponible = false;
                        var detOcup = data.mensaje ? escapeHtml(data.mensaje) : 'Ya existe un ambiente con este número. No puedes crear otro igual.';
                        setEstadoVisual('ocupado', detOcup + ' <span class="block mt-2 text-sm text-red-700 font-medium">No es posible guardar hasta usar un número libre.</span>', 'Ocupado');
                    }
                } catch (e) {
                    setEstadoVisual('error', 'Error de conexión al verificar el número. Revisa tu red e inténtalo de nuevo.', 'Conexión');
                }
            }

            function escapeHtml(s) {
                if (!s) return '';
                var d = document.createElement('div');
                d.textContent = s;
                return d.innerHTML;
            }

            window.ambienteNumeroCambiado = function () {
                numeroDisponible = null;
                clearTimeout(timer);
                const raw = (inputNum.value || '').trim();
                if (!raw) {
                    setFeedbackIdle();
                    return;
                }
                timer = setTimeout(verificar, 450);
            };

            inputNum.addEventListener('blur', function () {
                clearTimeout(timer);
                verificar();
            });

            form.addEventListener('submit', function (e) {
                const raw = (inputNum.value || '').trim();
                if (!raw || !/^\d{1,2}$/.test(raw)) {
                    e.preventDefault();
                    setEstadoVisual('error', 'Ingrese un número válido: <span class="font-medium">1 o 2 dígitos</span>.', 'Revisar');
                    return;
                }
                if (numeroDisponible !== true) {
                    e.preventDefault();
                    setEstadoVisual('error', 'Espera a que termine la verificación o elige un número <span class="font-semibold">disponible</span> para poder guardar.', 'Pendiente');
                    verificar();
                }
            });

            document.addEventListener('DOMContentLoaded', function () {
                if ((inputNum.value || '').trim()) {
                    verificar();
                }
            });
        })();
    </script>
@endsection
