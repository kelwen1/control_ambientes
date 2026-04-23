@extends('layouts.app')

@section('title', 'Nueva Competencia')

@section('content')
    <div class="mb-6 sm:mb-8 animate-fade-slide-up">
        <h1 class="text-2xl sm:text-3xl font-bold text-[#39B54A] mb-2 tracking-tight">
            Crear Nueva Competencia
        </h1>
        <p class="text-gray-600 text-sm sm:text-base">La competencia queda en el <strong>catálogo común</strong> (no se asocia a un solo programa). Las <strong>horas totales</strong> y el <strong>porcentaje</strong> definen las horas en el complejo; el resto corresponde a trabajo autónomo. Cualquier ficha podrá usarla al asignar reservas.</p>
    </div>

    <div class="card-premium bg-white rounded-xl shadow-card p-6 sm:p-8 hover:shadow-card-hover transition-shadow duration-300">
        <form method="POST" action="{{ route('competencias.store') }}" class="space-y-6" id="formCompetencia">
            @csrf

            <div>
                <label for="nombre_competencia" class="block text-sm sm:text-base font-semibold text-gray-700 mb-2">
                    Nombre de la Competencia <span class="text-red-500">*</span>
                </label>
                <input type="text"
                       name="nombre_competencia"
                       id="nombre_competencia"
                       value="{{ old('nombre_competencia') }}"
                       required
                       maxlength="150"
                       pattern="^[A-Za-zÀ-ÿ\s]+$"
                       oninput="this.value = this.value.replace(/[^\p{L}\s]/gu, '')"
                       class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:border-[#39B54A] focus:ring-2 focus:ring-[#39B54A]/20 focus:outline-none transition-all duration-200 text-sm sm:text-base">
                @error('nombre_competencia')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="nombre_norma" class="block text-sm sm:text-base font-semibold text-gray-700 mb-2">
                    Nombre de la Norma <span class="text-red-500">*</span>
                </label>
                <input type="text"
                       name="nombre_norma"
                       id="nombre_norma"
                       value="{{ old('nombre_norma') }}"
                       required
                       maxlength="150"
                       pattern="^[A-Za-zÀ-ÿ\s]+$"
                       oninput="this.value = this.value.replace(/[^\p{L}\s]/gu, '')"
                       class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:border-[#39B54A] focus:ring-2 focus:ring-[#39B54A]/20 focus:outline-none transition-all duration-200 text-sm sm:text-base">
                @error('nombre_norma')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label for="codigo" class="block text-sm sm:text-base font-semibold text-gray-700 mb-2">
                        Código <span class="text-red-500">*</span>
                    </label>
                    <input type="text"
                           name="codigo"
                           id="codigo"
                           value="{{ old('codigo') }}"
                           required
                           inputmode="numeric"
                           maxlength="9"
                           pattern="^\d{1,9}$"
                           oninput="this.value = this.value.replace(/\D/g, '').slice(0,9)"
                           class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:border-[#39B54A] focus:ring-2 focus:ring-[#39B54A]/20 focus:outline-none transition-all duration-200 text-sm sm:text-base">
                    @error('codigo')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="hora_totales" class="block text-sm sm:text-base font-semibold text-gray-700 mb-2">
                        Horas totales <span class="text-red-500">*</span>
                    </label>
                    <input type="text"
                           name="hora_totales"
                           id="hora_totales"
                           value="{{ preg_replace('/\D/', '', (string) old('hora_totales', '')) }}"
                           required
                           maxlength="4"
                           inputmode="numeric"
                           autocomplete="off"
                           class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:border-[#39B54A] focus:ring-2 focus:ring-[#39B54A]/20 focus:outline-none transition-all duration-200 text-sm sm:text-base"
                           oninput="this.value = this.value.replace(/\D/g, '').slice(0, 4);">
                    @error('hora_totales')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="porcentaje_horas" class="block text-sm sm:text-base font-semibold text-gray-700 mb-2">
                        Porcentaje de horas <span class="text-red-500">*</span>
                    </label>
                    <input type="text"
                           name="porcentaje_horas"
                           id="porcentaje_horas"
                           value="{{ substr(preg_replace('/\D/', '', (string) old('porcentaje_horas', '')), 0, 2) }}"
                           required
                           inputmode="numeric"
                           maxlength="2"
                           placeholder="Ej: 75"
                           autocomplete="off"
                           class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:border-[#39B54A] focus:ring-2 focus:ring-[#39B54A]/20 focus:outline-none transition-all duration-200 text-sm sm:text-base"
                           oninput="this.value = this.value.replace(/\D/g,'').slice(0,2);">
                    @error('porcentaje_horas')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="cantidad_resultados" class="block text-sm sm:text-base font-semibold text-gray-700 mb-2">
                        Cantidad de resultados <span class="text-red-500">*</span>
                    </label>
                    <input type="text"
                           name="cantidad_resultados"
                           id="cantidad_resultados"
                           value="{{ old('cantidad_resultados') }}"
                           required
                           inputmode="numeric"
                           maxlength="1"
                           pattern="^[1-9]$"
                           oninput="this.value = this.value.replace(/\D/g, '').slice(0,1); if (this.value !== '' && parseInt(this.value,10) > 9) this.value = '9';"
                           class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:border-[#39B54A] focus:ring-2 focus:ring-[#39B54A]/20 focus:outline-none transition-all duration-200 text-sm sm:text-base">
                    @error('cantidad_resultados')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="rounded-xl border-2 border-gray-200 bg-gray-50/80 p-4 space-y-3">
                <div>
                    <label for="duracion_display" class="block text-sm font-semibold text-gray-700 mb-2">
                        Duración (horas en el complejo)
                    </label>
                    <input type="text"
                           id="duracion_display"
                           readonly
                           tabindex="-1"
                           class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl bg-white text-gray-800 font-medium cursor-default text-sm sm:text-base"
                           value="">
                    <p class="mt-1 text-xs text-gray-500">Calculado: horas totales × (porcentaje ÷ 100). No editable; se guarda al enviar el formulario.</p>
                </div>
                <p id="autonomo_texto" class="text-sm font-medium text-[#39B54A]"></p>
            </div>

            <div class="flex flex-col sm:flex-row gap-4 pt-4 border-t border-gray-200">
                <button type="submit"
                        class="btn-primary flex-1 px-6 py-3 bg-[#39B54A] text-white rounded-xl font-semibold hover:bg-[#2d8f3a] hover:shadow-glow transition-all duration-200 shadow-md">
                    Guardar Competencia
                </button>
                <a href="{{ route('competencias.index') }}"
                   class="flex-1 px-6 py-3 bg-gray-300 text-gray-700 rounded-xl font-semibold hover:bg-gray-400 transition-all duration-200 text-center">
                    Cancelar
                </a>
            </div>
        </form>
    </div>

    <script>
        (function () {
            function clampPorcentaje(n) {
                if (isNaN(n)) return 60;
                if (n > 85) return 85;
                if (n < 60) return 60;
                return n;
            }

            function sync() {
                var ht = parseInt(document.getElementById('hora_totales').value, 10);
                var raw = parseInt(document.getElementById('porcentaje_horas').value, 10);
                var pct = clampPorcentaje(raw);
                var horasTotales = isNaN(ht) || ht < 1 ? 0 : ht;
                var duracion = horasTotales > 0 && !isNaN(raw) ? Math.round(horasTotales * (pct / 100)) : 0;
                document.getElementById('duracion_display').value = duracion > 0 ? String(duracion) : '—';
                var autonomo = Math.max(0, horasTotales - duracion);
                var el = document.getElementById('autonomo_texto');
                if (horasTotales > 0) {
                    el.textContent = autonomo + ' horas serían de trabajo autónomo';
                } else {
                    el.textContent = '';
                }
            }

            ['hora_totales', 'porcentaje_horas'].forEach(function (id) {
                var el = document.getElementById(id);
                if (el) {
                    el.addEventListener('input', sync);
                    if (id === 'hora_totales') {
                        el.addEventListener('paste', function (e) {
                            e.preventDefault();
                            var t = (e.clipboardData || window.clipboardData).getData('text') || '';
                            el.value = t.replace(/\D/g, '').slice(0, 4);
                            sync();
                        });
                    }
                    if (id === 'porcentaje_horas') {
                        el.addEventListener('paste', function (e) {
                            e.preventDefault();
                            var t = (e.clipboardData || window.clipboardData).getData('text') || '';
                            el.value = t.replace(/\D/g, '').slice(0, 2);
                            sync();
                        });
                    }
                    el.addEventListener('blur', function () {
                        if (id === 'porcentaje_horas' && el.value !== '') {
                            var v = parseInt(el.value, 10);
                            el.value = String(clampPorcentaje(isNaN(v) ? 60 : v));
                        }
                        sync();
                    });
                }
            });
            var formComp = document.getElementById('formCompetencia');
            if (formComp) {
                formComp.addEventListener('submit', function () {
                    var htEl = document.getElementById('hora_totales');
                    if (htEl) {
                        htEl.value = htEl.value.replace(/\D/g, '').slice(0, 4);
                    }
                    var p = document.getElementById('porcentaje_horas');
                    if (p) {
                        p.value = p.value.replace(/\D/g, '').slice(0, 2);
                        if (p.value !== '') {
                            var v = parseInt(p.value, 10);
                            p.value = String(clampPorcentaje(isNaN(v) ? 60 : v));
                        }
                    }
                });
            }
            document.addEventListener('DOMContentLoaded', sync);
        })();
    </script>
@endsection
