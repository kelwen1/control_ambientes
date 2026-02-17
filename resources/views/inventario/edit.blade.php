@extends('layouts.app')

@section('title', 'Editar Inventario')

@section('content')
    <!-- Bienvenida -->
    <div class="mb-6 sm:mb-8">
        <h1 class="text-2xl sm:text-3xl font-bold text-[#39B54A] mb-2">
            Editar Inventario
        </h1>
        <p class="text-gray-600 text-sm sm:text-base">Modifica la información del inventario del ambiente</p>
    </div>

    <!-- Formulario -->
    <div class="bg-white rounded-xl shadow-lg p-6 sm:p-8">
        <form method="POST" action="{{ route('inventario.update', $inventario->id_Inventario) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Campo: Ambiente -->
            <div>
                <label for="num_ambiente" class="block text-sm sm:text-base font-semibold text-gray-700 mb-2">
                    Ambiente <span class="text-red-500">*</span>
                </label>
                <select name="num_ambiente" 
                        id="num_ambiente" 
                        required
                        class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-[#39B54A] focus:outline-none transition-colors text-sm sm:text-base">
                    <option value="">Seleccione un ambiente</option>
                    @foreach($opcionesAmbientes as $opcion)
                        <option value="{{ $opcion['value'] }}" {{ (old('num_ambiente', $inventario->num_ambiente ?? '') == $opcion['value']) ? 'selected' : '' }}>
                            Ambiente {{ $opcion['label'] }}
                        </option>
                    @endforeach
                </select>
                @error('num_ambiente')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Cantidades (solo números; máx 2 dígitos o 1 según el campo) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
                <div>
                    <label for="computadores" class="block text-sm sm:text-base font-semibold text-gray-700 mb-2">
                        Cantidad de computadores <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="computadores" id="computadores" maxlength="2" required
                           value="{{ old('computadores', $inventario->computadores ?? 0) }}"
                           class="inventario-cantidad w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-[#39B54A] focus:outline-none transition-colors text-sm sm:text-base"
                           inputmode="numeric" pattern="[0-9]*" data-maxlength="2" data-max="35" placeholder="0">
                    <p class="mt-1 text-xs text-gray-500">Máximo 35</p>
                    @error('computadores')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="sillas" class="block text-sm sm:text-base font-semibold text-gray-700 mb-2">
                        Cantidad de sillas <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="sillas" id="sillas" maxlength="2" required
                           value="{{ old('sillas', $inventario->sillas ?? 0) }}"
                           class="inventario-cantidad w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-[#39B54A] focus:outline-none transition-colors text-sm sm:text-base"
                           inputmode="numeric" pattern="[0-9]*" data-maxlength="2" data-max="40" placeholder="0">
                    <p class="mt-1 text-xs text-gray-500">Máximo 40</p>
                    @error('sillas')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="mesas" class="block text-sm sm:text-base font-semibold text-gray-700 mb-2">
                        Cantidad de mesas <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="mesas" id="mesas" maxlength="2" required
                           value="{{ old('mesas', $inventario->mesas ?? 0) }}"
                           class="inventario-cantidad w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-[#39B54A] focus:outline-none transition-colors text-sm sm:text-base"
                           inputmode="numeric" pattern="[0-9]*" data-maxlength="2" data-max="20" placeholder="0">
                    <p class="mt-1 text-xs text-gray-500">Máximo 20</p>
                    @error('mesas')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="aire_acondicionado" class="block text-sm sm:text-base font-semibold text-gray-700 mb-2">
                        Cantidad de aires <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="aire_acondicionado" id="aire_acondicionado" maxlength="1" required
                           value="{{ old('aire_acondicionado', $inventario->aire_acondicionado ?? 0) }}"
                           class="inventario-cantidad w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-[#39B54A] focus:outline-none transition-colors text-sm sm:text-base"
                           inputmode="numeric" pattern="[0-9]*" data-maxlength="1" data-max="9" placeholder="0">
                    <p class="mt-1 text-xs text-gray-500">1 dígito (0-9)</p>
                    @error('aire_acondicionado')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
            <script>
            document.addEventListener('DOMContentLoaded', function() {
                document.querySelectorAll('.inventario-cantidad').forEach(function(input) {
                    var maxLen = parseInt(input.getAttribute('data-maxlength') || input.getAttribute('maxlength') || '2', 10);
                    input.addEventListener('keypress', function(e) {
                        if (!/^[0-9]$/.test(e.key)) { e.preventDefault(); return; }
                        if (this.value.length >= maxLen) e.preventDefault();
                    });
                    input.addEventListener('input', function() {
                        var val = this.value.replace(/\D/g, '').slice(0, maxLen);
                        var maxVal = parseInt(this.getAttribute('data-max'), 10);
                        if (!isNaN(maxVal) && parseInt(val, 10) > maxVal) val = String(maxVal);
                        this.value = val;
                    });
                    input.addEventListener('paste', function(e) {
                        e.preventDefault();
                        var maxVal = parseInt(this.getAttribute('data-max'), 10);
                        var pasted = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '').slice(0, maxLen);
                        if (!isNaN(maxVal) && parseInt(pasted, 10) > maxVal) pasted = String(maxVal);
                        this.value = pasted;
                    });
                });
            });
            </script>

            <!-- Resto: Sí/No -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6 mt-6">
                <!-- Tablero -->
                <div>
                    <label for="tablero" class="block text-sm sm:text-base font-semibold text-gray-700 mb-2">
                        Tablero <span class="text-red-500">*</span>
                    </label>
                    <select name="tablero" 
                            id="tablero"
                            required
                            class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-[#39B54A] focus:outline-none transition-colors text-sm sm:text-base">
                        <option value="">Seleccione</option>
                        <option value="Sí" {{ old('tablero', $inventario->tablero ?? '') == 'Sí' ? 'selected' : '' }}>Sí</option>
                        <option value="No" {{ old('tablero', $inventario->tablero ?? '') == 'No' ? 'selected' : '' }}>No</option>
                    </select>
                    @error('tablero')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Televisor -->
                <div>
                    <label for="televisor" class="block text-sm sm:text-base font-semibold text-gray-700 mb-2">
                        Televisor <span class="text-red-500">*</span>
                    </label>
                    <select name="televisor" 
                            id="televisor"
                            required
                            class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-[#39B54A] focus:outline-none transition-colors text-sm sm:text-base">
                        <option value="">Seleccione</option>
                        <option value="Sí" {{ old('televisor', $inventario->televisor ?? '') == 'Sí' ? 'selected' : '' }}>Sí</option>
                        <option value="No" {{ old('televisor', $inventario->televisor ?? '') == 'No' ? 'selected' : '' }}>No</option>
                    </select>
                    @error('televisor')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Ventiladores -->
                <div>
                    <label for="ventiladores" class="block text-sm sm:text-base font-semibold text-gray-700 mb-2">
                        Ventiladores <span class="text-red-500">*</span>
                    </label>
                    <select name="ventiladores" 
                            id="ventiladores"
                            required
                            class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-[#39B54A] focus:outline-none transition-colors text-sm sm:text-base">
                        <option value="">Seleccione</option>
                        <option value="Sí" {{ old('ventiladores', $inventario->ventiladores ?? '') == 'Sí' ? 'selected' : '' }}>Sí</option>
                        <option value="No" {{ old('ventiladores', $inventario->ventiladores ?? '') == 'No' ? 'selected' : '' }}>No</option>
                    </select>
                    @error('ventiladores')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Videobeam (vidiovid) -->
                <div>
                    <label for="vidiovid" class="block text-sm sm:text-base font-semibold text-gray-700 mb-2">
                        Videobeam <span class="text-red-500">*</span>
                    </label>
                    <select name="vidiovid" 
                            id="vidiovid"
                            required
                            class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-[#39B54A] focus:outline-none transition-colors text-sm sm:text-base">
                        <option value="">Seleccione</option>
                        <option value="Sí" {{ old('vidiovid', $inventario->vidiovid ?? '') == 'Sí' ? 'selected' : '' }}>Sí</option>
                        <option value="No" {{ old('vidiovid', $inventario->vidiovid ?? '') == 'No' ? 'selected' : '' }}>No</option>
                    </select>
                    @error('vidiovid')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Herramientas -->
                <div>
                    <label for="herramientas" class="block text-sm sm:text-base font-semibold text-gray-700 mb-2">
                        Herramientas <span class="text-red-500">*</span>
                    </label>
                    <select name="herramientas" 
                            id="herramientas"
                            required
                            class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-[#39B54A] focus:outline-none transition-colors text-sm sm:text-base">
                        <option value="">Seleccione</option>
                        <option value="Sí" {{ old('herramientas', $inventario->herramientas ?? '') == 'Sí' ? 'selected' : '' }}>Sí</option>
                        <option value="No" {{ old('herramientas', $inventario->herramientas ?? '') == 'No' ? 'selected' : '' }}>No</option>
                    </select>
                    @error('herramientas')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Botones -->
            <div class="flex flex-col sm:flex-row gap-4 pt-4 border-t border-gray-200">
                <button type="submit" 
                        class="flex-1 px-6 py-3 bg-[#39B54A] text-white rounded-lg font-semibold hover:bg-[#2d8f3a] transition-colors shadow-lg transform hover:scale-105">
                    Actualizar Inventario
                </button>
                <a href="{{ route('inventario.index') }}" 
                   class="flex-1 px-6 py-3 bg-gray-300 text-gray-700 rounded-lg font-semibold hover:bg-gray-400 transition-colors text-center">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
@endsection

