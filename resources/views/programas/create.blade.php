@extends('layouts.app')

@section('title', 'Nuevo Programa')

@section('content')
    <div class="mb-6 sm:mb-8 animate-fade-slide-up">
        <h1 class="text-2xl sm:text-3xl font-bold text-[#39B54A] mb-2 tracking-tight">
            Crear Nuevo Programa
        </h1>
        <p class="text-gray-600 text-sm sm:text-base">Registra un nuevo programa de formación.</p>
        @if (session('success'))
            <div class="mt-4 p-4 bg-green-50 border-l-4 border-[#39B54A] rounded-lg text-sm text-green-800">{{ session('success') }}</div>
        @endif
    </div>

    <div class="card-premium bg-white rounded-xl shadow-card p-6 sm:p-8 hover:shadow-card-hover transition-shadow duration-300">
        <form method="POST" action="{{ route('programas.store') }}" class="space-y-6" id="formCrearPrograma">
            @csrf

            <div>
                <label for="nombre_programa" class="block text-sm sm:text-base font-semibold text-gray-700 mb-2">
                    Nombre del Programa <span class="text-red-500">*</span>
                </label>
                <input type="text"
                       name="nombre_programa"
                       id="nombre_programa"
                       value="{{ old('nombre_programa') }}"
                       required
                       maxlength="100"
                       autocomplete="off"
                       class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:border-[#39B54A] focus:ring-2 focus:ring-[#39B54A]/20 focus:outline-none transition-all duration-200 text-sm sm:text-base"
                       placeholder="Ej: Análisis y Desarrollo de Software"
                       oninput="let v=this.value.replace(/[^\p{L}\s]/gu,'').slice(0,100); if(v!==this.value)this.value=v;">
                @error('nombre_programa')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label for="id_nivel_programa" class="block text-sm sm:text-base font-semibold text-gray-700 mb-2">
                        Nivel de programa <span class="text-red-500">*</span>
                    </label>
                    <select name="id_nivel_programa"
                            id="id_nivel_programa"
                            required
                            class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:border-[#39B54A] focus:ring-2 focus:ring-[#39B54A]/20 focus:outline-none transition-all duration-200 text-sm sm:text-base">
                        <option value="">Seleccione un nivel</option>
                        @foreach($niveles as $id => $nivel)
                            <option value="{{ $id }}" {{ (string) old('id_nivel_programa', request('nivel')) === (string) $id ? 'selected' : '' }}>
                                {{ $nivel }}
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-2">
                        <a href="{{ route('niveles-programa.create', ['desde' => 'programa']) }}"
                           class="text-sm text-[#39B54A] hover:text-[#2d8f3a] hover:underline font-medium">
                            Añadir nuevo nivel de programa
                        </a>
                    </p>
                    @error('id_nivel_programa')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <span class="block text-sm sm:text-base font-semibold text-gray-700 mb-2">
                        Duración del programa <span class="text-red-500">*</span>
                    </span>
                    <input type="hidden" name="id_duracion" id="id_duracion" value="">
                    <div id="duracion_mostrar"
                         class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl bg-gray-50 text-gray-800 text-sm sm:text-base min-h-[3.25rem] flex items-center"
                         aria-live="polite">
                        —
                    </div>
                    @error('id_duracion')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex flex-col sm:flex-row gap-4 pt-4 border-t border-gray-200">
                <button type="submit"
                        class="btn-primary flex-1 px-6 py-3 bg-[#39B54A] text-white rounded-xl font-semibold hover:bg-[#2d8f3a] hover:shadow-glow transition-all duration-200 shadow-md">
                    Guardar Programa
                </button>
                <a href="{{ route('programas.index') }}"
                   class="flex-1 px-6 py-3 bg-gray-300 text-gray-700 rounded-xl font-semibold hover:bg-gray-400 transition-all duration-200 text-center">
                    Cancelar
                </a>
            </div>
        </form>
    </div>

    <script>
        (function () {
            var map = @json($nivelDuracionIds ?? []);
            var duraciones = @json($duraciones ?? []);
            var hiddenDur = document.getElementById('id_duracion');
            var mostrar = document.getElementById('duracion_mostrar');
            var nivel = document.getElementById('id_nivel_programa');

            function etiquetaDur(id) {
                var k = String(id);
                return duraciones[k] !== undefined ? duraciones[k] : (duraciones[id] !== undefined ? duraciones[id] : '—');
            }

            function sync() {
                var nid = nivel.value;
                var idDur = map[nid];
                if (!nid) {
                    hiddenDur.value = '';
                    mostrar.textContent = 'Seleccione primero un nivel de programa.';
                    mostrar.classList.remove('border-red-300', 'bg-red-50');
                    return;
                }
                if (idDur == null || idDur === '') {
                    hiddenDur.value = '';
                    mostrar.textContent = 'Este nivel no tiene duración asociada. Defina los meses en «Niveles de programa» o use un nombre reconocible.';
                    mostrar.classList.add('border-red-300', 'bg-red-50');
                    return;
                }
                hiddenDur.value = String(idDur);
                mostrar.textContent = etiquetaDur(idDur);
                mostrar.classList.remove('border-red-300', 'bg-red-50');
            }

            nivel.addEventListener('change', sync);
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', sync);
            } else {
                sync();
            }
        })();
    </script>
@endsection
