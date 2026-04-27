@extends('layouts.app')

@section('title', 'Crear Ficha')

@section('content')
    <!-- Bienvenida -->
    <div class="mb-6 sm:mb-8 animate-fade-slide-up">
        <h1 class="text-2xl sm:text-3xl font-bold text-[#39B54A] mb-2 tracking-tight">
            Crear Nueva Ficha
        </h1>
        <p class="text-gray-600 text-sm sm:text-base">Completa el formulario para registrar una nueva ficha</p>
    </div>

    <!-- Formulario -->
    <div class="card-premium bg-white rounded-xl shadow-card p-6 sm:p-8 hover:shadow-card-hover transition-shadow duration-300">
        <form method="POST" action="{{ route('fichas.store') }}" class="space-y-6" id="formFicha">
            @csrf

            <!-- Campo: Número de Ficha -->
            <div>
                <label for="num_ficha" class="block text-sm sm:text-base font-semibold text-gray-700 mb-2">
                    Número de Ficha <span class="text-red-500">*</span>
                </label>
                <input type="text"
                       name="num_ficha"
                       id="num_ficha"
                       value="{{ old('num_ficha') }}"
                       required
                       inputmode="numeric"
                       autocomplete="off"
                       maxlength="8"
                       pattern="[0-9]{1,8}"
                       class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:border-[#39B54A] focus:ring-2 focus:ring-[#39B54A]/20 focus:outline-none transition-all duration-200 text-sm sm:text-base"
                       placeholder="Solo números, máx. 8 dígitos"
                       oninput="this.value = this.value.replace(/\D/g, '').slice(0, 8)">
                @error('num_ficha')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Campo: Cantidad de Aprendices -->
            <div>
                <label for="cant_aprendices" class="block text-sm sm:text-base font-semibold text-gray-700 mb-2">
                    Cantidad de Aprendices <span class="text-red-500">*</span>
                </label>
                <input type="text"
                       name="cant_aprendices"
                       id="cant_aprendices"
                       value="{{ old('cant_aprendices') }}"
                       required
                       inputmode="numeric"
                       pattern="[0-9]{1,3}"
                       maxlength="3"
                       min="20"
                       max="100"
                       class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:border-[#39B54A] focus:ring-2 focus:ring-[#39B54A]/20 focus:outline-none transition-all duration-200 text-sm sm:text-base"
                       placeholder="Entre 20 y 100 aprendices"
                       oninput="let v=this.value.replace(/\D/g,'').slice(0,3); if(v!==''){let n=parseInt(v,10); if(n>100)v='100'; this.value=v;} else this.value='';">
                @error('cant_aprendices')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Campo: Programa -->
            <div>
                <label for="id_programa" class="block text-sm sm:text-base font-semibold text-gray-700 mb-2">
                    Programa <span class="text-red-500">*</span>
                </label>
                <select name="id_programa"
                        id="id_programa"
                        required
                        class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:border-[#39B54A] focus:ring-2 focus:ring-[#39B54A]/20 focus:outline-none transition-all duration-200 text-sm sm:text-base">
                    <option value="">Seleccione un programa</option>
                    @foreach($programas as $programa)
                        <option value="{{ $programa->id_programa }}" {{ old('id_programa') == $programa->id_programa ? 'selected' : '' }}>
                            {{ $programa->nombre_programa }}
                        </option>
                    @endforeach
                </select>
                @error('id_programa')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Jornada del grupo (FK a tabla jornada; las reservas heredan esta jornada) -->
            <div>
                <label for="id_jornada" class="block text-sm sm:text-base font-semibold text-gray-700 mb-2">
                    Jornada del grupo <span class="text-red-500">*</span>
                </label>
                <select name="id_jornada" id="id_jornada" required
                        class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:border-[#39B54A] focus:ring-2 focus:ring-[#39B54A]/20 focus:outline-none transition-all duration-200 text-sm sm:text-base">
                    <option value="">Seleccione</option>
                    @if(isset($jornadasRegistro) && $jornadasRegistro->isNotEmpty())
                        @foreach($jornadasRegistro as $jr)
                            <option value="{{ $jr->id_jornada }}" {{ (string) old('id_jornada') === (string) $jr->id_jornada ? 'selected' : '' }}>
                                {{ $jr->jornada }}
                            </option>
                        @endforeach
                    @else
                        @foreach(['manana' => 1, 'tarde' => 2, 'noche' => 3, 'fin_semana' => 4] as $clave => $idJ)
                            <option value="{{ $idJ }}" {{ (string) old('id_jornada') === (string) $idJ ? 'selected' : '' }}>
                                {{ $jornadas[$clave]['label'] ?? $clave }}
                            </option>
                        @endforeach
                    @endif
                </select>
                <p class="mt-1 text-xs text-gray-500">Entre semana: mañana, tarde o noche. Fin de semana: sábado o domingo. Esta jornada se aplicará a todas las reservas de la ficha.</p>
                @error('id_jornada')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Campos de Fechas -->
            <div>
                <p class="text-xs text-gray-500 mb-3">
                    La <span class="font-semibold">fecha de fin</span> se calcula según el nivel del programa:
                    Media técnica 12 meses, Técnica 18 meses, Tecnología 24 meses o segun los que existan.
                    La <span class="font-semibold">fecha productiva</span> queda 6 meses antes de la fecha fin.
                </p>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-6">
                    <div>
                        <label for="fecha_inicio" class="block text-sm sm:text-base font-semibold text-gray-700 mb-2">
                            Fecha Inicio <span class="text-red-500">*</span>
                        </label>
                        <input type="date"
                               name="fecha_inicio"
                               id="fecha_inicio"
                               value="{{ old('fecha_inicio') }}"
                               required
                               class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:border-[#39B54A] focus:ring-2 focus:ring-[#39B54A]/20 focus:outline-none transition-all duration-200 text-sm sm:text-base">
                        @error('fecha_inicio')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="fecha_fin" class="block text-sm sm:text-base font-semibold text-gray-700 mb-2">
                            Fecha Fin <span class="text-red-500">*</span>
                        </label>
                        <input type="date"
                               id="fecha_fin"
                               value="{{ old('fecha_fin') }}"
                               readonly
                               tabindex="-1"
                               class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl bg-gray-100 text-gray-700 cursor-not-allowed text-sm sm:text-base">
                    </div>

                    <div>
                        <label for="fecha_productiva" class="block text-sm sm:text-base font-semibold text-gray-700 mb-2">
                            Fecha Productiva <span class="text-red-500">*</span>
                        </label>
                        <input type="date"
                               id="fecha_productiva"
                               value="{{ old('fecha_productiva') }}"
                               readonly
                               tabindex="-1"
                               class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl bg-gray-100 text-gray-700 cursor-not-allowed text-sm sm:text-base">
                    </div>
                </div>
                <p id="fechas-ficha-msg" class="mt-2 text-sm min-h-[1.25rem]" role="status"></p>
            </div>

            <!-- Botones -->
            <div class="flex gap-4 pt-4">
                <a href="{{ route('fichas.index') }}"
                   class="flex-1 px-6 py-3 bg-gray-300 text-gray-700 rounded-xl font-semibold hover:bg-gray-400 transition-all duration-200 text-center">
                    Cancelar
                </a>
                <button type="submit"
                        id="btnGuardarFicha"
                        class="btn-primary flex-1 px-6 py-3 bg-[#39B54A] text-white rounded-xl font-semibold hover:bg-[#2d8f3a] hover:shadow-glow transition-all duration-200 shadow-md">
                    Guardar Ficha
                </button>
            </div>
        </form>
    </div>

    <script>
        (function () {
            const baseUrl = @json(route('fichas.fechas-por-programa'));
            const elProg = document.getElementById('id_programa');
            const elIni = document.getElementById('fecha_inicio');
            const elFin = document.getElementById('fecha_fin');
            const elProd = document.getElementById('fecha_productiva');
            const elMsg = document.getElementById('fechas-ficha-msg');
            const form = document.getElementById('formFicha');

            async function actualizarFechas() {
                elMsg.textContent = '';
                elMsg.className = 'mt-2 text-sm min-h-[1.25rem]';
                const id = elProg.value;
                const fi = elIni.value;
                if (!id || !fi) {
                    elFin.value = '';
                    elProd.value = '';
                    return;
                }
                const u = new URL(baseUrl, window.location.origin);
                u.searchParams.set('id_programa', id);
                u.searchParams.set('fecha_inicio', fi);
                try {
                    const r = await fetch(u.toString(), {
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                        credentials: 'same-origin',
                    });
                    const data = await r.json().catch(function () { return {}; });
                    if (!r.ok) {
                        elFin.value = '';
                        elProd.value = '';
                        elMsg.textContent = data.message || 'No se pudieron calcular las fechas.';
                        elMsg.classList.add('text-red-600');
                        return;
                    }
                    elFin.value = data.fecha_fin || '';
                    elProd.value = data.fecha_productiva || '';
                } catch (e) {
                    elFin.value = '';
                    elProd.value = '';
                    elMsg.textContent = 'Error al calcular las fechas. Intente de nuevo.';
                    elMsg.classList.add('text-red-600');
                }
            }

            elProg.addEventListener('change', actualizarFechas);
            elIni.addEventListener('change', actualizarFechas);
            document.addEventListener('DOMContentLoaded', actualizarFechas);

            const elJornada = document.getElementById('id_jornada');
            form.addEventListener('submit', function (e) {
                if (!elJornada || !elJornada.value) {
                    e.preventDefault();
                    elMsg.textContent = 'Seleccione la jornada del grupo.';
                    elMsg.className = 'mt-2 text-sm min-h-[1.25rem] text-red-600';
                    return;
                }
                if (!elFin.value || !elProd.value) {
                    e.preventDefault();
                    elMsg.textContent = 'Seleccione programa y fecha de inicio para calcular fecha fin y fecha productiva antes de guardar.';
                    elMsg.className = 'mt-2 text-sm min-h-[1.25rem] text-red-600';
                }
            });
        })();
    </script>
@endsection
