<!-- Modal para Cambiar Contraseña -->
<div id="editContraseñaModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 modal-overlay">
    <div class="modal-container glass-container rounded-2xl shadow-2xl p-6 sm:p-8 w-full max-w-md">
        <div class="mb-6">
            <h3 class="text-2xl font-bold text-gray-800 mb-2">Cambiar Contraseña</h3>
            <p class="text-gray-600 text-sm">Ingresa tu contraseña actual y tu nueva contraseña dos veces.</p>
        </div>

        <form method="POST" action="{{ route('ajustes.update.contraseña') }}" id="contraseñaForm">
            @csrf
            @method('PUT')

            <div class="mb-4">
                <label for="contraseña_actual_contraseña" class="block text-gray-700 font-semibold mb-2 text-sm">Contraseña Actual</label>
                <div class="relative">
                    <input type="password" 
                           id="contraseña_actual_contraseña" 
                           name="contraseña_actual" 
                           required
                           class="w-full px-4 py-3 pr-10 border-2 border-gray-300 rounded-lg focus:border-[#39B54A] focus:outline-none transition-colors text-sm"
                           placeholder="Ingresa tu contraseña actual">
                    <button type="button" onclick="togglePassword('contraseña_actual_contraseña', this)" class="absolute right-2 top-1/2 -translate-y-1/2 p-1.5 text-gray-500 hover:text-gray-700 focus:outline-none" tabindex="-1" aria-label="Mostrar contraseña">
                        <svg class="w-5 h-5 eye-open" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        <svg class="w-5 h-5 eye-closed hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                    </button>
                </div>
                @error('contraseña_actual')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="contraseña_nueva" class="block text-gray-700 font-semibold mb-2 text-sm">Nueva Contraseña</label>
                <div class="relative">
                    <input type="password" 
                           id="contraseña_nueva" 
                           name="contraseña_nueva" 
                           required
                           minlength="8"
                           class="w-full px-4 py-3 pr-10 border-2 border-gray-300 rounded-lg focus:border-[#39B54A] focus:outline-none transition-colors text-sm"
                           placeholder="Mínimo 8 caracteres">
                    <button type="button" onclick="togglePassword('contraseña_nueva', this)" class="absolute right-2 top-1/2 -translate-y-1/2 p-1.5 text-gray-500 hover:text-gray-700 focus:outline-none" tabindex="-1" aria-label="Mostrar contraseña">
                        <svg class="w-5 h-5 eye-open" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        <svg class="w-5 h-5 eye-closed hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                    </button>
                </div>
                @error('contraseña_nueva')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label for="contraseña_nueva_confirmacion" class="block text-gray-700 font-semibold mb-2 text-sm">Confirmar Nueva Contraseña</label>
                <div class="relative">
                    <input type="password" 
                           id="contraseña_nueva_confirmacion" 
                           name="contraseña_nueva_confirmacion" 
                           required
                           minlength="8"
                           class="w-full px-4 py-3 pr-10 border-2 border-gray-300 rounded-lg focus:border-[#39B54A] focus:outline-none transition-colors text-sm"
                           placeholder="Repite la nueva contraseña">
                    <button type="button" onclick="togglePassword('contraseña_nueva_confirmacion', this)" class="absolute right-2 top-1/2 -translate-y-1/2 p-1.5 text-gray-500 hover:text-gray-700 focus:outline-none" tabindex="-1" aria-label="Mostrar contraseña">
                        <svg class="w-5 h-5 eye-open" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        <svg class="w-5 h-5 eye-closed hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                    </button>
                </div>
                @error('contraseña_nueva_confirmacion')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p id="passwordMatch" class="mt-1 text-sm text-red-600 hidden">Las contraseñas no coinciden</p>
            </div>

            <div class="flex gap-3 sm:gap-4">
                <button type="button" 
                        onclick="closeModal('editContraseñaModal')"
                        class="flex-1 bg-gray-300 text-gray-700 py-3 rounded-lg font-semibold text-base hover:bg-gray-400 transition-colors shadow-lg">
                    Cancelar
                </button>
                <button type="submit" 
                        class="flex-1 bg-orange-500 text-white py-3 rounded-lg font-semibold text-base hover:bg-orange-600 transition-colors shadow-lg transform hover:scale-105">
                    Cambiar Contraseña
                </button>
            </div>
        </form>
    </div>
</div>


