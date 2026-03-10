@extends('layouts.app')

@section('title', 'Editar Reserva')

@section('content')
    <!-- Bienvenida -->
    <div class="mb-6 sm:mb-8">
        <h1 class="text-2xl sm:text-3xl font-bold text-[#39B54A] mb-2">
            Editar Reserva
        </h1>
        <p class="text-gray-600 text-sm sm:text-base">Modifica la información de la reserva</p>
    </div>

    <!-- Mensaje de éxito -->
    @if (session('success'))
        <div class="mb-6 p-4 bg-green-50 border-l-4 border-[#39B54A] rounded-lg">
            <div class="flex items-center">
                <span class="text-2xl mr-3">✅</span>
                <p class="text-green-800 font-medium">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    <!-- Mensaje de error -->
    @if (session('error'))
        <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 rounded-lg">
            <div class="flex items-center">
                <span class="text-2xl mr-3">❌</span>
                <p class="text-red-800 font-medium">{{ session('error') }}</p>
            </div>
        </div>
    @endif

    <!-- Formulario -->
    <div class="bg-white rounded-xl shadow-lg p-6 sm:p-8">
        <form method="POST" action="{{ route('reservas.update', $reserva->id_reserva) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Campo: Ambiente -->
                <div>
                    <label for="id_ambiente" class="block text-sm sm:text-base font-semibold text-gray-700 mb-2">
                        Ambiente <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input type="text" 
                               id="ambiente_search" 
                               placeholder="Buscar por número de ambiente (ej: 4, 17A, 17B)..."
                               class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-[#39B54A] focus:outline-none transition-colors text-sm sm:text-base">
                        <span class="absolute right-3 top-3 text-gray-400">🔍</span>
                    </div>
                    <select name="id_ambiente" 
                            id="id_ambiente" 
                            required
                            size="8"
                            class="w-full mt-2 px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-[#39B54A] focus:outline-none transition-colors text-sm sm:text-base">
                        <option value="">Seleccione un ambiente</option>
                        @foreach($ambientes as $ambiente)
                            <option value="{{ $ambiente->id_ambiente }}" 
                                    data-num="{{ $ambiente->num_ambiente }}"
                                    {{ (old('id_ambiente', $reserva->id_ambiente) == $ambiente->id_ambiente) ? 'selected' : '' }}>
                                Ambiente {{ $ambiente->num_ambiente }}
                            </option>
                        @endforeach
                    </select>
                    @error('id_ambiente')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Campo: Ficha -->
                <div>
                    <label for="id_ficha" class="block text-sm sm:text-base font-semibold text-gray-700 mb-2">
                        Ficha <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input type="text" 
                               id="ficha_search" 
                               placeholder="Buscar por número de ficha (ej: 2557843)..."
                               class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-[#39B54A] focus:outline-none transition-colors text-sm sm:text-base">
                        <span class="absolute right-3 top-3 text-gray-400">🔍</span>
                    </div>
                    <select name="id_ficha" 
                            id="id_ficha" 
                            required
                            size="8"
                            class="w-full mt-2 px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-[#39B54A] focus:outline-none transition-colors text-sm sm:text-base">
                        <option value="">Seleccione una ficha</option>
                        @foreach($fichas as $ficha)
                            <option value="{{ $ficha->id_ficha }}" 
                                    data-num="{{ $ficha->num_ficha }}"
                                    {{ (old('id_ficha', $reserva->id_ficha) == $ficha->id_ficha) ? 'selected' : '' }}>
                                Ficha {{ $ficha->num_ficha }}
                            </option>
                        @endforeach
                    </select>
                    @error('id_ficha')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            @if(isset($instructores) && $instructores->isNotEmpty())
            <div>
                <label for="id_persona" class="block text-sm sm:text-base font-semibold text-gray-700 mb-2">Instructor asignado</label>
                <select name="id_persona" id="id_persona"
                        class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-[#39B54A] focus:outline-none transition-colors text-sm sm:text-base">
                    <option value="">Sin asignar</option>
                    @foreach($instructores as $inst)
                        <option value="{{ $inst->id_persona }}" {{ (old('id_persona', $reserva->id_persona ?? '') == $inst->id_persona) ? 'selected' : '' }}>
                            {{ $inst->nombres }} {{ $inst->apellidos }}
                        </option>
                    @endforeach
                </select>
                @error('id_persona')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            @endif

            <!-- Día de la semana -->
            <div>
                <label for="dia_semana" class="block text-sm sm:text-base font-semibold text-gray-700 mb-2">
                    Día de la Semana <span class="text-red-500">*</span>
                </label>
                <select name="dia_semana" 
                        id="dia_semana" 
                        required
                        class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-[#39B54A] focus:outline-none transition-colors text-sm sm:text-base">
                    <option value="">Seleccione un día</option>
                    <option value="lunes" {{ (old('dia_semana', $reserva->dia_semana) == 'lunes') ? 'selected' : '' }}>Lunes a Viernes</option>
                    <option value="sabado" {{ (old('dia_semana', $reserva->dia_semana) == 'sabado') ? 'selected' : '' }}>Sábados</option>
                    <option value="domingo" {{ (old('dia_semana', $reserva->dia_semana) == 'domingo') ? 'selected' : '' }}>Domingos</option>
                </select>
                <p class="mt-1 text-sm text-gray-500">Sábados y domingos: horario único 7 am - 5 pm (una reserva por ambiente por día).</p>
                @error('dia_semana')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Jornada (horario) -->
            <div>
                <label for="jornada" class="block text-sm sm:text-base font-semibold text-gray-700 mb-2">
                    Jornada / Horario <span class="text-red-500">*</span>
                </label>
                <select name="jornada" 
                        id="jornada" 
                        required
                        class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-[#39B54A] focus:outline-none transition-colors text-sm sm:text-base">
                    <option value="">Seleccione una jornada</option>
                    @foreach($jornadas as $key => $j)
                        <option value="{{ $key }}" 
                                data-inicio="{{ $j['inicio'] }}" 
                                data-fin="{{ $j['fin'] }}"
                                {{ (old('jornada', $jornadaSeleccionada) === $key) ? 'selected' : '' }}>
                            {{ $j['label'] }}
                        </option>
                    @endforeach
                </select>
                <input type="hidden" name="hora_inicio" id="hora_inicio" value="{{ old('hora_inicio', $reserva->hora_inicio ? \Carbon\Carbon::parse($reserva->hora_inicio)->format('H:i') : '') }}">
                <input type="hidden" name="hora_fin" id="hora_fin" value="{{ old('hora_fin', $reserva->hora_fin ? \Carbon\Carbon::parse($reserva->hora_fin)->format('H:i') : '') }}">
                @error('hora_inicio')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                @error('hora_fin')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Fechas -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Fecha de inicio -->
                <div>
                    <label for="fecha_inicio" class="block text-sm sm:text-base font-semibold text-gray-700 mb-2">
                        Fecha de Inicio <span class="text-red-500">*</span>
                    </label>
                    <input type="date" 
                           name="fecha_inicio" 
                           id="fecha_inicio" 
                           value="{{ old('fecha_inicio', $reserva->fecha_inicio) }}"
                           required
                           class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-[#39B54A] focus:outline-none transition-colors text-sm sm:text-base">
                    @error('fecha_inicio')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Fecha de fin -->
                <div>
                    <label for="fecha_fin" class="block text-sm sm:text-base font-semibold text-gray-700 mb-2">
                        Fecha de Fin <span class="text-red-500">*</span>
                    </label>
                    <input type="date" 
                           name="fecha_fin" 
                           id="fecha_fin" 
                           value="{{ old('fecha_fin', $reserva->fecha_fin) }}"
                           required
                           class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-[#39B54A] focus:outline-none transition-colors text-sm sm:text-base">
                    @error('fecha_fin')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Estado de Reserva -->
            <div>
                <label for="id_estado_reserva" class="block text-sm sm:text-base font-semibold text-gray-700 mb-2">
                    Estado de Reserva <span class="text-red-500">*</span>
                </label>
                <select name="id_estado_reserva" 
                        id="id_estado_reserva" 
                        required
                        class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-[#39B54A] focus:outline-none transition-colors text-sm sm:text-base">
                    @foreach($estados as $estado)
                        <option value="{{ $estado->id_estado_reserva }}" 
                                {{ (old('id_estado_reserva', $reserva->id_estado_reserva) == $estado->id_estado_reserva) ? 'selected' : '' }}>
                            {{ $estado->nombre_estado }}
                        </option>
                    @endforeach
                </select>
                @error('id_estado_reserva')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Observaciones -->
            <div>
                <label for="observaciones" class="block text-sm sm:text-base font-semibold text-gray-700 mb-2">
                    Observaciones
                </label>
                <textarea name="observaciones" 
                          id="observaciones" 
                          rows="3"
                          placeholder="Escriba observaciones adicionales (opcional)..."
                          class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-[#39B54A] focus:outline-none transition-colors text-sm sm:text-base">{{ old('observaciones', $reserva->observaciones) }}</textarea>
                @error('observaciones')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Botones -->
            <div class="flex flex-col sm:flex-row gap-4 pt-4 border-t border-gray-200">
                <button type="submit" 
                        class="flex-1 px-6 py-3 bg-[#39B54A] text-white rounded-lg font-semibold hover:bg-[#2d8f3a] transition-colors shadow-lg transform hover:scale-105">
                    Actualizar Reserva
                </button>
                <a href="{{ route('ambientes.index') }}" 
                   class="flex-1 px-6 py-3 bg-gray-300 text-gray-700 rounded-lg font-semibold hover:bg-gray-400 transition-colors text-center">
                    Cancelar
                </a>
            </div>
        </form>
    </div>

    <script>
        function initSelectSearch(searchInputId, selectId) {
            const searchInput = document.getElementById(searchInputId);
            const select = document.getElementById(selectId);

            if (!searchInput || !select) return;

            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase().trim();
                const options = select.querySelectorAll('option');
                
                options.forEach(option => {
                    if (option.value === '') {
                        return;
                    }
                    
                    const numValue = option.getAttribute('data-num')?.toLowerCase() || '';
                    const optionText = option.textContent.toLowerCase();
                    
                    if (searchTerm === '' || numValue.includes(searchTerm) || optionText.includes(searchTerm)) {
                        option.style.display = '';
                    } else {
                        option.style.display = 'none';
                    }
                });
            });

            select.addEventListener('change', function() {
                if (this.value) {
                    const selectedOption = this.options[this.selectedIndex];
                    searchInput.value = selectedOption.getAttribute('data-num') || '';
                } else {
                    searchInput.value = '';
                }
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            initSelectSearch('ambiente_search', 'id_ambiente');
            initSelectSearch('ficha_search', 'id_ficha');

            const diaSemanaSelect = document.getElementById('dia_semana');
            const jornadaSelect = document.getElementById('jornada');
            const horaInicioInput = document.getElementById('hora_inicio');
            const horaFinInput = document.getElementById('hora_fin');

            function esFinDeSemana() {
                return diaSemanaSelect && (diaSemanaSelect.value === 'sabado' || diaSemanaSelect.value === 'domingo');
            }

            function actualizarOpcionesJornada() {
                if (!jornadaSelect) return;
                var opts = jornadaSelect.querySelectorAll('option[value="manana"], option[value="tarde"], option[value="noche"], option[value="fin_semana"]');
                opts.forEach(function(o) {
                    if (esFinDeSemana()) {
                        o.style.display = o.value === 'fin_semana' ? '' : 'none';
                        if (jornadaSelect.value !== 'fin_semana') {
                            jornadaSelect.value = 'fin_semana';
                        }
                    } else {
                        o.style.display = o.value === 'fin_semana' ? 'none' : '';
                        if (jornadaSelect.value === 'fin_semana') {
                            jornadaSelect.value = 'manana';
                        }
                    }
                });
                actualizarHorasDesdeJornada();
            }

            function actualizarHorasDesdeJornada() {
                if (!jornadaSelect || !jornadaSelect.value) {
                    horaInicioInput.value = '';
                    horaFinInput.value = '';
                    return;
                }
                const opt = jornadaSelect.options[jornadaSelect.selectedIndex];
                if (opt && opt.value) {
                    horaInicioInput.value = opt.getAttribute('data-inicio') || '';
                    horaFinInput.value = opt.getAttribute('data-fin') || '';
                }
            }
            if (jornadaSelect) {
                jornadaSelect.addEventListener('change', actualizarHorasDesdeJornada);
                actualizarHorasDesdeJornada();
            }
            if (diaSemanaSelect) {
                diaSemanaSelect.addEventListener('change', actualizarOpcionesJornada);
                actualizarOpcionesJornada();
            }

            const ambienteSelect = document.getElementById('id_ambiente');
            const fichaSelect = document.getElementById('id_ficha');
            const ambienteSearch = document.getElementById('ambiente_search');
            const fichaSearch = document.getElementById('ficha_search');

            if (ambienteSelect && ambienteSearch && ambienteSelect.value) {
                const selectedAmbiente = ambienteSelect.options[ambienteSelect.selectedIndex];
                if (selectedAmbiente) {
                    ambienteSearch.value = selectedAmbiente.getAttribute('data-num') || '';
                }
            }

            if (fichaSelect && fichaSearch && fichaSelect.value) {
                const selectedFicha = fichaSelect.options[fichaSelect.selectedIndex];
                if (selectedFicha) {
                    fichaSearch.value = selectedFicha.getAttribute('data-num') || '';
                }
            }
        });
    </script>
@endsection

