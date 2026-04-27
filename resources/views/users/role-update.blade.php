@extends('layouts.app')

@section('title', 'Actualización de roles')

@section('content')
    <div class="mb-6 sm:mb-8 animate-fade-slide-up">
        <h1 class="text-2xl sm:text-3xl font-bold text-[#39B54A] mb-2 tracking-tight">Actualización de roles</h1>
        <p class="text-gray-600 text-sm sm:text-base">Cambie el rol de un usuario mediante su cedula (Instructor, Coordinador o Coordinador líder).</p>
    </div>

    @if (session('success'))
        <div class="mb-4 p-4 bg-green-50 border-l-4 border-[#39B54A] rounded-lg text-sm text-green-800">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="mb-4 p-4 bg-red-50 border-l-4 border-red-500 rounded-lg text-sm text-red-800">{{ session('error') }}</div>
    @endif
    @if (session('info'))
        <div class="mb-4 p-4 bg-blue-50 border-l-4 border-blue-500 rounded-lg text-sm text-blue-800">{{ session('info') }}</div>
    @endif

    <div class="card-premium bg-white rounded-xl shadow-card p-6 sm:p-8 hover:shadow-card-hover transition-shadow duration-300 max-w-2xl">
        <form id="form_role_update" method="POST" action="{{ route('users.role-apply') }}" class="space-y-6">
            @csrf
            @method('PUT')

            <div>
                <label for="cedula_input" class="block text-sm font-semibold text-gray-700 mb-2">Cédula <span class="text-red-500">*</span></label>
                <div class="flex flex-col sm:flex-row gap-2 sm:items-center">
                    <input type="text" name="cedula" id="cedula_input" form="form_role_update" required
                           value="{{ old('cedula') }}"
                           inputmode="numeric" maxlength="10" autocomplete="off"
                           placeholder="Solo números"
                           class="w-full sm:max-w-xs px-4 py-2.5 border-2 border-gray-300 rounded-xl focus:border-[#39B54A] focus:ring-2 focus:ring-[#39B54A]/20 focus:outline-none text-sm sm:text-base">
                    <button type="button" id="btn_validar_cedula"
                            class="btn-primary px-4 py-2.5 bg-[#39B54A] text-white rounded-xl hover:bg-[#2d8f3a] transition-all shadow-md text-sm font-medium whitespace-nowrap">
                        Verificar cédula
                    </button>
                </div>
                <p id="cedula_mensaje" class="mt-2 text-sm min-h-[1.25rem]" role="status"></p>
                @error('cedula')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div id="bloque_rol" class="hidden space-y-2">
                <label for="id_rol" class="block text-sm font-semibold text-gray-700">Nuevo rol <span class="text-red-500">*</span></label>
                <select name="id_rol" id="id_rol" form="form_role_update" required
                        class="w-full sm:max-w-md px-4 py-2.5 border-2 border-gray-300 rounded-xl focus:border-[#39B54A] focus:ring-2 focus:ring-[#39B54A]/20 focus:outline-none text-sm sm:text-base appearance-none pr-10 bg-white"
                        style="background-image: url('data:image/svg+xml;charset=UTF-8,%3csvg xmlns=%27http://www.w3.org/2000/svg%27 viewBox=%270 0 24 24%27 fill=%27none%27 stroke=%27%236b7280%27 stroke-width=%272%27%3e%3cpolyline points=%276 9 12 15 18 9%27%3e%3c/polyline%3e%3c/svg%3e'); background-repeat: no-repeat; background-position: right 0.75rem center; background-size: 1.25rem;">
                    <option value="">Seleccione un rol</option>
                    @foreach($rolesCambio as $idRol => $etiqueta)
                        <option value="{{ $idRol }}" {{ (string) old('id_rol') === (string) $idRol ? 'selected' : '' }}>{{ $etiqueta }}</option>
                    @endforeach
                </select>
                @error('id_rol')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div id="card_resumen" class="hidden rounded-xl border-2 border-[#39B54A]/40 bg-[#39B54A]/5 p-4 sm:p-5">
                <p class="text-sm sm:text-base text-gray-800 leading-relaxed" id="card_resumen_texto"></p>
            </div>

            <div id="row_submit" class="hidden pt-2">
                <button type="submit" id="btn_aplicar"
                        class="btn-primary w-full sm:w-auto px-6 py-3 bg-[#39B54A] text-white rounded-xl font-semibold hover:bg-[#2d8f3a] shadow-md transition-all">
                    Aplicar actualización
                </button>
            </div>
        </form>

        <p class="mt-8 pt-4 border-t border-gray-200 text-sm text-gray-500">
            <a href="{{ route('users.index') }}" class="text-[#39B54A] font-medium hover:underline">← Volver a usuarios</a>
        </p>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var cedulaIn = document.getElementById('cedula_input');
            var btnValidar = document.getElementById('btn_validar_cedula');
            var cedulaMsg = document.getElementById('cedula_mensaje');
            var bloqueRol = document.getElementById('bloque_rol');
            var idRol = document.getElementById('id_rol');
            var cardRes = document.getElementById('card_resumen');
            var cardTexto = document.getElementById('card_resumen_texto');
            var rowSubmit = document.getElementById('row_submit');
            var lookupUrl = @json(route('users.role-lookup'));

            var datos = null;

            if (cedulaIn) {
                cedulaIn.addEventListener('input', function() {
                    this.value = (this.value || '').replace(/\D/g, '').slice(0, 10);
                });
            }

            function mostrarResumen() {
                if (!datos || !idRol) return;
                var id = idRol.value;
                if (!id) {
                    cardRes.classList.add('hidden');
                    rowSubmit.classList.add('hidden');
                    if (cardTexto) cardTexto.textContent = '';
                    return;
                }
                var map = { '2': 'Coordinador líder', '3': 'Coordinador', '4': 'Instructor' };
                var destino = map[id] || 'rol seleccionado';
                if (cardTexto) {
                    cardTexto.textContent = 'Actualización de ' + datos.nombre_completo + ' de ' + datos.rol_actual_etiqueta + ' a ' + destino + '.';
                }
                cardRes.classList.remove('hidden');
                rowSubmit.classList.remove('hidden');
            }

            if (idRol) {
                idRol.addEventListener('change', mostrarResumen);
            }

            if (btnValidar) {
                btnValidar.addEventListener('click', function() {
                    datos = null;
                    bloqueRol.classList.add('hidden');
                    cardRes.classList.add('hidden');
                    rowSubmit.classList.add('hidden');
                    if (idRol) idRol.value = '';
                    cedulaMsg.textContent = '';
                    cedulaMsg.className = 'mt-2 text-sm min-h-[1.25rem]';
                    if (!cedulaIn || !cedulaIn.value.trim()) {
                        cedulaMsg.textContent = 'Escriba la cédula (solo números).';
                        cedulaMsg.classList.add('text-red-600');
                        return;
                    }
                    var url = lookupUrl + '?cedula=' + encodeURIComponent(cedulaIn.value.trim());
                    fetch(url, {
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                    })
                        .then(function(r) { return r.json().then(function(b) { return { ok: r.ok, body: b }; }); })
                        .then(function(x) {
                            if (!x.ok || !x.body.ok) {
                                cedulaMsg.textContent = (x.body && x.body.message) ? x.body.message : 'No se pudo verificar la cédula.';
                                cedulaMsg.classList.add('text-red-600');
                                return;
                            }
                            datos = x.body;
                            cedulaMsg.textContent = 'Persona verificada: ' + x.body.nombre_completo + ' (rol actual: ' + x.body.rol_actual_etiqueta + ').';
                            cedulaMsg.classList.add('text-gray-600');
                            bloqueRol.classList.remove('hidden');
                            mostrarResumen();
                        })
                        .catch(function() {
                            cedulaMsg.textContent = 'Error de conexión. Intente de nuevo.';
                            cedulaMsg.classList.add('text-red-600');
                        });
                });
            }
        });
    </script>
@endsection
