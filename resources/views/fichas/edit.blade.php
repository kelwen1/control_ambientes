@extends('layouts.app')

@section('title', 'Editar Ficha')

@section('content')
    <!-- Bienvenida -->
    <div class="mb-6 sm:mb-8">
        <h1 class="text-2xl sm:text-3xl font-bold text-[#39B54A] mb-2">
            Editar Ficha
        </h1>
        <p class="text-gray-600 text-sm sm:text-base">Modifica la información de la ficha</p>
    </div>

    <!-- Formulario -->
    <div class="bg-white rounded-xl shadow-lg p-6 sm:p-8">
        <form method="POST" action="{{ route('fichas.update', $ficha->id_ficha) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Campo: Número de Ficha -->
            <div>
                <label for="num_ficha" class="block text-sm sm:text-base font-semibold text-gray-700 mb-2">
                    Número de Ficha <span class="text-red-500">*</span>
                </label>
                <input type="number" 
                       name="num_ficha" 
                       id="num_ficha" 
                       value="{{ old('num_ficha', $ficha->num_ficha) }}"
                       required
                       min="1"
                       maxlength="9"
                       oninput="if (this.value.length > 9) this.value = this.value.slice(0, 9);"
                       class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-[#39B54A] focus:outline-none transition-colors text-sm sm:text-base"
                       placeholder="Ingrese el número de ficha">
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
                       value="{{ old('cant_aprendices', $ficha->cant_aprendices) }}"
                       required
                       inputmode="numeric"
                       pattern="[0-9]*"
                       maxlength="2"
                       class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-[#39B54A] focus:outline-none transition-colors text-sm sm:text-base"
                       placeholder="Ej: 25 (máx. 40)"
                       oninput="this.value = this.value.replace(/\D/g, '').slice(0, 2)">
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
                        class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-[#39B54A] focus:outline-none transition-colors text-sm sm:text-base">
                    <option value="">Seleccione un programa</option>
                    @foreach($programas as $programa)
                        <option value="{{ $programa->id_programa }}" {{ old('id_programa', $ficha->id_programa ?? '') == $programa->id_programa ? 'selected' : '' }}>
                            {{ $programa->nombre_programa }}
                        </option>
                    @endforeach
                </select>
                @error('id_programa')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Campos de Fechas -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-6">
                <!-- Fecha Inicio -->
                <div>
                    <label for="fecha_inicio" class="block text-sm sm:text-base font-semibold text-gray-700 mb-2">
                        Fecha Inicio <span class="text-red-500">*</span>
                    </label>
                    <input type="date" 
                           name="fecha_inicio" 
                           id="fecha_inicio" 
                           value="{{ old('fecha_inicio', $ficha->fecha_inicio) }}"
                           required
                           class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-[#39B54A] focus:outline-none transition-colors text-sm sm:text-base">
                    @error('fecha_inicio')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Fecha Fin -->
                <div>
                    <label for="fecha_fin" class="block text-sm sm:text-base font-semibold text-gray-700 mb-2">
                        Fecha Fin <span class="text-red-500">*</span>
                    </label>
                    <input type="date" 
                           name="fecha_fin" 
                           id="fecha_fin" 
                           value="{{ old('fecha_fin', $ficha->fecha_fin) }}"
                           required
                           class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-[#39B54A] focus:outline-none transition-colors text-sm sm:text-base">
                    @error('fecha_fin')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Fecha Productiva -->
                <div>
                    <label for="fecha_productiva" class="block text-sm sm:text-base font-semibold text-gray-700 mb-2">
                        Fecha Productiva
                    </label>
                    <input type="date" 
                           name="fecha_productiva" 
                           id="fecha_productiva" 
                           value="{{ old('fecha_productiva', $ficha->fecha_productiva) }}"
                           class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-[#39B54A] focus:outline-none transition-colors text-sm sm:text-base">
                    @error('fecha_productiva')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Botones -->
            <div class="flex gap-4 pt-4">
                <a href="{{ route('fichas.index') }}" 
                   class="flex-1 px-6 py-3 bg-gray-300 text-gray-700 rounded-lg font-semibold hover:bg-gray-400 transition-colors shadow-lg text-center">
                    Cancelar
                </a>
                <button type="submit" 
                        class="flex-1 px-6 py-3 bg-[#39B54A] text-white rounded-lg font-semibold hover:bg-[#2d8f3a] transition-colors shadow-lg transform hover:scale-105">
                    Actualizar Ficha
                </button>
            </div>
        </form>
    </div>
@endsection

