<!-- Modal para Editar Correo -->
<div id="editCorreoModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 modal-overlay">
    <div class="modal-container glass-container rounded-2xl shadow-2xl p-6 sm:p-8 w-full max-w-md">
        <div class="mb-6">
            <h3 class="text-2xl font-bold text-gray-800 mb-2">Editar Correo Electrónico</h3>
            <p class="text-gray-600 text-sm">Actualiza tu correo electrónico. Se requiere tu contraseña para confirmar.</p>
        </div>

        <form method="POST" action="{{ route('ajustes.update.correo') }}">
            @csrf
            @method('PUT')

            <div class="mb-4">
                <label for="correo" class="block text-gray-700 font-semibold mb-2 text-sm">Nuevo Correo Electrónico</label>
                <input type="email" 
                       id="correo" 
                       name="correo" 
                       value="{{ old('correo', $user->correo) }}"
                       required
                       pattern="[^\s]+@(gmail|hotmail)\.com$"
                       oninput="this.value = this.value.replace(/\s/g, '')"
                       class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-[#39B54A] focus:outline-none transition-colors text-sm"
                       placeholder="correo@gmail.com o correo@hotmail.com">
                @error('correo')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label for="contraseña_actual_correo" class="block text-gray-700 font-semibold mb-2 text-sm">Contraseña Actual</label>
                <input type="password" 
                       id="contraseña_actual_correo" 
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
                        onclick="closeModal('editCorreoModal')"
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
