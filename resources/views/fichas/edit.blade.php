@extends('layouts.app')

@section('title', 'Editar Ficha')

@section('content')
    <!-- Bienvenida -->
    <div class="mb-6 sm:mb-8 animate-fade-slide-up">
        <h1 class="text-2xl sm:text-3xl font-bold text-[#39B54A] mb-2 tracking-tight">
            Editar Ficha
        </h1>
        <p class="text-gray-600 text-sm sm:text-base">Solo puedes modificar la <span class="font-semibold">cantidad de aprendices</span>. El número de ficha, programa, <span class="font-semibold">jornada</span> y fechas están bloqueados.</p>
    </div>

    <!-- Formulario -->
    <div class="card-premium bg-white rounded-xl shadow-card p-6 sm:p-8 hover:shadow-card-hover transition-shadow duration-300">
        <form method="POST" action="{{ route('fichas.update', $ficha->id_ficha) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <input type="hidden" name="id_programa" value="{{ old('id_programa', $ficha->id_programa) }}">
            <input type="hidden" name="num_ficha" value="{{ old('num_ficha', $ficha->num_ficha) }}">
            <input type="hidden" name="fecha_inicio" value="{{ old('fecha_inicio', $ficha->fecha_inicio) }}">

            <!-- Campo: Número de Ficha (solo lectura) -->
            <div>
                <label for="num_ficha_display" class="block text-sm sm:text-base font-semibold text-gray-700 mb-2">
                    Número de Ficha <span class="text-red-500">*</span>
                </label>
                <input type="text"
                       id="num_ficha_display"
                       value="{{ old('num_ficha', $ficha->num_ficha) }}"
                       readonly
                       tabindex="-1"
                       class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl bg-gray-100 text-gray-700 cursor-not-allowed text-sm sm:text-base">
            </div>

            <!-- Campo: Cantidad de Aprendices -->
            <div>
                <label for="cant_aprendices" class="block text-sm sm:text-base font-semibold text-gray-700 mb-2">
                    Cantidad de Aprendices <span class="text-red-500">*</span>
                </label>
                <input type="text"
                       name="cant_aprendices"
                       id="cant_aprendices"
                       value="{{ old('cant_aprendices', $ficha->cant_aprendices) }}"
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

            <!-- Jornada (bloqueada; no se envía al servidor) -->
            <div>
                <label for="id_jornada_display" class="block text-sm sm:text-base font-semibold text-gray-700 mb-2">
                    Jornada del grupo
                </label>
                @php
                    $mapJ = [1 => 'manana', 2 => 'tarde', 3 => 'noche', 4 => 'fin_semana'];
                    $claveJ = $mapJ[(int) ($ficha->id_jornada ?? 0)] ?? null;
                    $labelJornada = $ficha->jornada->jornada ?? ($claveJ ? ($jornadas[$claveJ]['label'] ?? '—') : '—');
                @endphp
                <select id="id_jornada_display" disabled
                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl bg-gray-100 text-gray-700 cursor-not-allowed text-sm sm:text-base opacity-90 pointer-events-none"
                        aria-hidden="true">
                    <option selected>{{ $labelJornada }}</option>
                </select>
                <p class="mt-1 text-xs text-gray-500">No se puede cambiar en edición; afecta a todas las reservas de esta ficha.</p>
            </div>

            <!-- Programa (solo lectura) -->
            <div>
                <label for="programa_display" class="block text-sm sm:text-base font-semibold text-gray-700 mb-2">
                    Programa <span class="text-red-500">*</span>
                </label>
                <input type="text"
                       id="programa_display"
                       value="{{ $ficha->programa->nombre_programa ?? '—' }}"
                       readonly
                       tabindex="-1"
                       class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl bg-gray-100 text-gray-700 cursor-not-allowed text-sm sm:text-base">
                @error('id_programa')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Fechas (solo lectura) -->
            <div>
                <p class="text-xs text-gray-500 mb-3">
                    Las fechas se definieron al crear la ficha y no pueden modificarse desde aquí.
                </p>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-6">
                    <div>
                        <label for="fecha_inicio_display" class="block text-sm sm:text-base font-semibold text-gray-700 mb-2">
                            Fecha Inicio <span class="text-red-500">*</span>
                        </label>
                        <input type="date"
                               id="fecha_inicio_display"
                               value="{{ old('fecha_inicio', $ficha->fecha_inicio) }}"
                               readonly
                               tabindex="-1"
                               class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl bg-gray-100 text-gray-700 cursor-not-allowed text-sm sm:text-base">
                        @error('fecha_inicio')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="fecha_fin_display" class="block text-sm sm:text-base font-semibold text-gray-700 mb-2">
                            Fecha Fin <span class="text-red-500">*</span>
                        </label>
                        <input type="date"
                               id="fecha_fin_display"
                               value="{{ old('fecha_fin', $ficha->fecha_fin) }}"
                               readonly
                               tabindex="-1"
                               class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl bg-gray-100 text-gray-700 cursor-not-allowed text-sm sm:text-base">
                    </div>

                    <div>
                        <label for="fecha_productiva_display" class="block text-sm sm:text-base font-semibold text-gray-700 mb-2">
                            Fecha Productiva <span class="text-red-500">*</span>
                        </label>
                        <input type="date"
                               id="fecha_productiva_display"
                               value="{{ old('fecha_productiva', $ficha->fecha_productiva) }}"
                               readonly
                               tabindex="-1"
                               class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl bg-gray-100 text-gray-700 cursor-not-allowed text-sm sm:text-base">
                    </div>
                </div>
            </div>

            <!-- Botones -->
            <div class="flex gap-4 pt-4">
                <a href="{{ route('fichas.index') }}"
                   class="flex-1 px-6 py-3 bg-gray-300 text-gray-700 rounded-xl font-semibold hover:bg-gray-400 transition-all duration-200 text-center">
                    Cancelar
                </a>
                <button type="submit"
                        class="btn-primary flex-1 px-6 py-3 bg-[#39B54A] text-white rounded-xl font-semibold hover:bg-[#2d8f3a] hover:shadow-glow transition-all duration-200 shadow-md">
                    Actualizar Ficha
                </button>
            </div>
        </form>
    </div>
@endsection
