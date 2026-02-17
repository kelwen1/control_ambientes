<!-- Modal para Editar Usuario -->
<div id="editUsuarioModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 modal-overlay">
    <div class="modal-container glass-container rounded-2xl shadow-2xl p-6 sm:p-8 w-full max-w-md">
        <div class="mb-6">
            <h3 class="text-2xl font-bold text-gray-800 mb-2">Editar Usuario</h3>
            <p class="text-gray-600 text-sm">Actualiza tu nombre de usuario. Se requiere tu contraseña para confirmar.</p>
        </div>

        <form method="POST" action="{{ route('ajustes.update.usuario') }}">
            @csrf
            @method('PUT')

            <div class="mb-4">
                <label for="user" class="block text-gray-700 font-semibold mb-2 text-sm">Nuevo Usuario</label>
                <input type="text" 
                       id="user" 
                       name="user" 
                       value="{{ old('user', $user->user) }}"
                       required
                       maxlength="15"
                       pattern="[a-zA-Z0-9]+"
                       oninput="this.value = this.value.replace(/[^a-zA-Z0-9]/g, '')"
                       class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-[#39B54A] focus:outline-none transition-colors text-sm"
                       placeholder="Ingresa tu nuevo usuario (solo letras y números, máx. 15 caracteres)">
                @error('user')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label for="contraseña_actual_usuario" class="block text-gray-700 font-semibold mb-2 text-sm">Contraseña Actual</label>
                <input type="password" 
                       id="contraseña_actual_usuario" 
                       name="contraseña_actual" 
                       required
                       class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-[#39B54A] focus:outline-none transition-colors text-sm"
                       placeholder="Ingresa tu contraseña actual">
                @error('contraseña_actual')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex gap-3 sm:gap-4">
                <button type="button" 
                        onclick="closeModal('editUsuarioModal')"
                        class="flex-1 bg-gray-300 text-gray-700 py-3 rounded-lg font-semibold text-base hover:bg-gray-400 transition-colors shadow-lg">
                    Cancelar
                </button>
                <button type="submit" 
                        class="flex-1 bg-[#39B54A] text-white py-3 rounded-lg font-semibold text-base hover:bg-[#2d8f3a] transition-colors shadow-lg transform hover:scale-105">
                    Guardar Cambios
                </button>
            </div>
        </form>
    </div>
</div>

