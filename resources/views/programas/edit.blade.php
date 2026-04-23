@extends('layouts.app')

@section('title', 'Editar Programa')

@section('content')
    <div class="mb-6 sm:mb-8 animate-fade-slide-up">
        <h1 class="text-2xl sm:text-3xl font-bold text-[#39B54A] mb-2 tracking-tight">
            Editar Programa
        </h1>
        <p class="text-gray-600 text-sm sm:text-base">Puedes corregir el <span class="font-medium text-gray-800">nombre</span>. El nivel y la duración no se cambian aquí (quedan como al crear el programa).</p>
    </div>

    <div class="card-premium bg-white rounded-xl shadow-card p-6 sm:p-8 hover:shadow-card-hover transition-shadow duration-300">
        <form method="POST" action="{{ route('programas.update', $programa->id_programa) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <div>
                <label for="nombre_programa" class="block text-sm sm:text-base font-semibold text-gray-700 mb-2">
                    Nombre del Programa <span class="text-red-500">*</span>
                </label>
                <input type="text"
                       name="nombre_programa"
                       id="nombre_programa"
                       value="{{ old('nombre_programa', $programa->nombre_programa) }}"
                       required
                       maxlength="100"
                       autocomplete="off"
                       class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:border-[#39B54A] focus:ring-2 focus:ring-[#39B54A]/20 focus:outline-none transition-all duration-200 text-sm sm:text-base"
                       placeholder="Solo letras y espacios, máx. 100 caracteres"
                       oninput="let v=this.value.replace(/[^\p{L}\s]/gu,'').slice(0,100); if(v!==this.value)this.value=v;">
                @error('nombre_programa')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <span class="block text-sm sm:text-base font-semibold text-gray-700 mb-2">Nivel de programa</span>
                    <div class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl bg-gray-50 text-gray-800 text-sm sm:text-base cursor-not-allowed select-none"
                         aria-readonly="true">{{ $nivelEtiqueta }}</div>
                    <p class="mt-1 text-xs text-gray-500">No editable en esta pantalla.</p>
                </div>

                <div>
                    <span class="block text-sm sm:text-base font-semibold text-gray-700 mb-2">Duración</span>
                    <div class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl bg-gray-50 text-gray-800 text-sm sm:text-base cursor-not-allowed select-none"
                         aria-readonly="true">{{ $duracionEtiqueta }}</div>
                    <p class="mt-1 text-xs text-gray-500">No editable en esta pantalla.</p>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row gap-4 pt-4 border-t border-gray-200">
                <button type="submit"
                        class="btn-primary flex-1 px-6 py-3 bg-[#39B54A] text-white rounded-xl font-semibold hover:bg-[#2d8f3a] hover:shadow-glow transition-all duration-200 shadow-md">
                    Actualizar Programa
                </button>
                <a href="{{ route('programas.index') }}"
                   class="flex-1 px-6 py-3 bg-gray-300 text-gray-700 rounded-xl font-semibold hover:bg-gray-400 transition-all duration-200 text-center">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
@endsection
