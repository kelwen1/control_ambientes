@extends('layouts.app')

@section('title', 'Editar Usuario')

@section('content')
    <div class="mb-6 sm:mb-8 animate-fade-slide-up">
        <h1 class="text-2xl sm:text-3xl font-bold text-[#39B54A] mb-2 tracking-tight">Editar Usuario</h1>
        <p class="text-gray-600 text-sm sm:text-base">Puedes actualizar <span class="font-medium text-gray-800">nombres</span>, <span class="font-medium text-gray-800">apellidos</span>, <span class="font-medium text-gray-800">teléfono</span> y, si aplica, la <span class="font-medium text-gray-800">contraseña</span>. Cédula, correo y rol no se modifican aquí.</p>
    </div>

    <div class="card-premium bg-white rounded-xl shadow-card p-6 sm:p-8 hover:shadow-card-hover transition-shadow duration-300">
        <form method="POST" action="{{ route('users.update', $user->id_usuario) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                <div>
                    <span class="block text-sm sm:text-base font-semibold text-gray-700 mb-2">Cédula</span>
                    <div class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl bg-gray-50 text-gray-800 text-sm sm:text-base cursor-not-allowed select-none"
                         aria-readonly="true">{{ $user->id_cedula }}</div>
                    <p class="mt-1 text-xs text-gray-500">La cédula y el usuario de acceso no se pueden modificar.</p>
                </div>

                <div>
                    <span class="block text-sm sm:text-base font-semibold text-gray-700 mb-2">Correo</span>
                    <div class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl bg-gray-50 text-gray-800 text-sm sm:text-base break-all cursor-not-allowed select-none"
                         aria-readonly="true">{{ $user->correo }}</div>
                    <p class="mt-1 text-xs text-gray-500">El correo no se puede cambiar desde esta pantalla.</p>
                </div>

                <div>
                    <span class="block text-sm sm:text-base font-semibold text-gray-700 mb-2">Rol</span>
                    <div class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl bg-gray-50 text-gray-800 text-sm sm:text-base cursor-not-allowed select-none"
                         aria-readonly="true">{{ $user->persona?->rol?->rol ?? '—' }}</div>
                    <p class="mt-1 text-xs text-gray-500">El rol no se puede cambiar desde esta pantalla.</p>
                </div>

                <div>
                    <label for="nombres" class="block text-sm sm:text-base font-semibold text-gray-700 mb-2">Nombres <span class="text-red-500">*</span></label>
                    <input type="text" id="nombres" name="nombres" value="{{ old('nombres', $user->nombre) }}" required maxlength="40"
                           class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:border-[#39B54A] focus:ring-2 focus:ring-[#39B54A]/20 focus:outline-none transition-all duration-200 text-sm sm:text-base"
                           placeholder="Solo letras y espacios, máx. 40"
                           oninput="let v=this.value.replace(/[^\p{L}\s]/gu,'').slice(0,40); if(v!==this.value)this.value=v;">
                    @error('nombres')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="apellidos" class="block text-sm sm:text-base font-semibold text-gray-700 mb-2">Apellidos <span class="text-red-500">*</span></label>
                    <input type="text" id="apellidos" name="apellidos" value="{{ old('apellidos', $user->apellido) }}" required maxlength="50"
                           class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:border-[#39B54A] focus:ring-2 focus:ring-[#39B54A]/20 focus:outline-none transition-all duration-200 text-sm sm:text-base"
                           placeholder="Solo letras y espacios, máx. 50"
                           oninput="let v=this.value.replace(/[^\p{L}\s]/gu,'').slice(0,50); if(v!==this.value)this.value=v;">
                    @error('apellidos')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="telefono" class="block text-sm sm:text-base font-semibold text-gray-700 mb-2">Teléfono</label>
                    <input type="text" id="telefono" name="telefono" value="{{ old('telefono', $user->telefono) }}" maxlength="10"
                           inputmode="numeric" autocomplete="off"
                           class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:border-[#39B54A] focus:ring-2 focus:ring-[#39B54A]/20 focus:outline-none transition-all duration-200 text-sm sm:text-base"
                           placeholder="Solo números, máx. 10 dígitos"
                           oninput="this.value = this.value.replace(/\D/g, '').slice(0, 10);">
                    @error('telefono')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div class="sm:col-span-2">
                    <label for="contraseña_actual" class="block text-sm sm:text-base font-semibold text-gray-700 mb-2">Contraseña actual del usuario</label>
                    <div class="relative">
                        <input type="password" id="contraseña_actual" name="contraseña_actual"
                               class="w-full px-4 py-3 pr-10 border-2 border-gray-300 rounded-xl focus:border-[#39B54A] focus:ring-2 focus:ring-[#39B54A]/20 focus:outline-none transition-all duration-200 text-sm sm:text-base"
                               placeholder="Solo si vas a cambiar la contraseña">
                        <button type="button" onclick="togglePassword('contraseña_actual', this)" class="absolute right-2 top-1/2 -translate-y-1/2 p-1.5 text-gray-500 hover:text-gray-700" tabindex="-1" aria-label="Mostrar contraseña">
                            <svg class="w-5 h-5 eye-open" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            <svg class="w-5 h-5 eye-closed hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                        </button>
                    </div>
                    @error('contraseña_actual')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    <p class="mt-1 text-xs text-gray-500">Obligatoria solo si ingresas una nueva contraseña abajo.</p>
                </div>

                <div>
                    <label for="contraseña" class="block text-sm sm:text-base font-semibold text-gray-700 mb-2">Nueva contraseña</label>
                    <div class="relative">
                        <input type="password" id="contraseña" name="contraseña" minlength="8"
                               class="w-full px-4 py-3 pr-10 border-2 border-gray-300 rounded-xl focus:border-[#39B54A] focus:ring-2 focus:ring-[#39B54A]/20 focus:outline-none transition-all duration-200 text-sm sm:text-base"
                               placeholder="Dejar en blanco para no cambiar">
                        <button type="button" onclick="togglePassword('contraseña', this)" class="absolute right-2 top-1/2 -translate-y-1/2 p-1.5 text-gray-500 hover:text-gray-700" tabindex="-1" aria-label="Mostrar contraseña">
                            <svg class="w-5 h-5 eye-open" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            <svg class="w-5 h-5 eye-closed hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                        </button>
                    </div>
                    @error('contraseña')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="contraseña_confirmation" class="block text-sm sm:text-base font-semibold text-gray-700 mb-2">Confirmar nueva contraseña</label>
                    <div class="relative">
                        <input type="password" id="contraseña_confirmation" name="contraseña_confirmation" minlength="8"
                               class="w-full px-4 py-3 pr-10 border-2 border-gray-300 rounded-xl focus:border-[#39B54A] focus:ring-2 focus:ring-[#39B54A]/20 focus:outline-none transition-all duration-200 text-sm sm:text-base"
                               placeholder="Repetir nueva contraseña">
                        <button type="button" onclick="togglePassword('contraseña_confirmation', this)" class="absolute right-2 top-1/2 -translate-y-1/2 p-1.5 text-gray-500 hover:text-gray-700" tabindex="-1" aria-label="Mostrar contraseña">
                            <svg class="w-5 h-5 eye-open" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            <svg class="w-5 h-5 eye-closed hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                        </button>
                    </div>
                </div>
            </div>

            <div class="flex gap-4 pt-4">
                <a href="{{ route('users.index') }}" class="flex-1 px-6 py-3 bg-gray-300 text-gray-700 rounded-xl font-semibold hover:bg-gray-400 transition-all duration-200 text-center">Cancelar</a>
                <button type="submit" class="btn-primary flex-1 px-6 py-3 bg-[#39B54A] text-white rounded-xl font-semibold hover:bg-[#2d8f3a] hover:shadow-glow transition-all duration-200 shadow-md">Actualizar Usuario</button>
            </div>
        </form>
    </div>
@endsection
