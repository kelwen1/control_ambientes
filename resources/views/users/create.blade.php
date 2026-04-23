@extends('layouts.app')

@section('title', 'Crear Usuario')

@section('content')
    <div class="mb-6 sm:mb-8 animate-fade-slide-up">
        <h1 class="text-2xl sm:text-3xl font-bold text-[#39B54A] mb-2 tracking-tight">Crear Nuevo Usuario</h1>
        <p class="text-gray-600 text-sm sm:text-base">Registra una nueva persona y su cuenta. Usuario y contraseña inicial serán la <strong>cédula</strong>.</p>
    </div>

    <div class="card-premium bg-white rounded-xl shadow-card p-6 sm:p-8 hover:shadow-card-hover transition-shadow duration-300">
        <form method="POST" action="{{ route('users.store') }}" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                <div>
                    <label for="cedula" class="block text-sm sm:text-base font-semibold text-gray-700 mb-2">Cédula <span class="text-red-500">*</span></label>
                    <input type="text" id="cedula" name="cedula" value="{{ old('cedula') }}" required maxlength="10"
                           inputmode="numeric" autocomplete="off" pattern="[0-9]{1,10}"
                           class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:border-[#39B54A] focus:ring-2 focus:ring-[#39B54A]/20 focus:outline-none transition-all duration-200 text-sm sm:text-base"
                           placeholder="Solo números, máx. 10 dígitos"
                           oninput="this.value = this.value.replace(/\D/g, '').slice(0, 10);">
                    @error('cedula')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="nombres" class="block text-sm sm:text-base font-semibold text-gray-700 mb-2">Nombres <span class="text-red-500">*</span></label>
                    <input type="text" id="nombres" name="nombres" value="{{ old('nombres') }}" required maxlength="40"
                           class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:border-[#39B54A] focus:ring-2 focus:ring-[#39B54A]/20 focus:outline-none transition-all duration-200 text-sm sm:text-base"
                           placeholder="Solo letras y espacios, máx. 40"
                           oninput="let v=this.value.replace(/[^\p{L}\s]/gu,'').slice(0,40); if(v!==this.value)this.value=v;">
                    @error('nombres')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="apellidos" class="block text-sm sm:text-base font-semibold text-gray-700 mb-2">Apellidos <span class="text-red-500">*</span></label>
                    <input type="text" id="apellidos" name="apellidos" value="{{ old('apellidos') }}" required maxlength="50"
                           class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:border-[#39B54A] focus:ring-2 focus:ring-[#39B54A]/20 focus:outline-none transition-all duration-200 text-sm sm:text-base"
                           placeholder="Solo letras y espacios, máx. 50"
                           oninput="let v=this.value.replace(/[^\p{L}\s]/gu,'').slice(0,50); if(v!==this.value)this.value=v;">
                    @error('apellidos')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="correo" class="block text-sm sm:text-base font-semibold text-gray-700 mb-2">Correo <span class="text-red-500">*</span></label>
                    <input type="email" id="correo" name="correo" value="{{ old('correo') }}" required maxlength="50"
                           class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:border-[#39B54A] focus:ring-2 focus:ring-[#39B54A]/20 focus:outline-none transition-all duration-200 text-sm sm:text-base"
                           placeholder="nombre@gmail.com">
                    <p class="mt-1 text-xs text-gray-500">@@gmail, @@outlook o @@hotmail y .com, .co, .edu o .sena.</p>
                    @error('correo')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="telefono" class="block text-sm sm:text-base font-semibold text-gray-700 mb-2">Teléfono</label>
                    <input type="text" id="telefono" name="telefono" value="{{ old('telefono') }}" maxlength="10"
                           inputmode="numeric" autocomplete="off" pattern="[0-9]{0,10}"
                           class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:border-[#39B54A] focus:ring-2 focus:ring-[#39B54A]/20 focus:outline-none transition-all duration-200 text-sm sm:text-base"
                           placeholder="Solo números, máx. 10 dígitos"
                           oninput="this.value = this.value.replace(/\D/g, '').slice(0, 10);">
                    @error('telefono')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="id_rol" class="block text-sm sm:text-base font-semibold text-gray-700 mb-2">Rol <span class="text-red-500">*</span></label>
                    <select id="id_rol" name="id_rol" required
                            class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:border-[#39B54A] focus:ring-2 focus:ring-[#39B54A]/20 focus:outline-none transition-all duration-200 text-sm sm:text-base">
                        <option value="">Seleccione un rol</option>
                        @foreach ($roles as $rol)
                            <option value="{{ $rol->id_rol }}" {{ old('id_rol') == $rol->id_rol ? 'selected' : '' }}>{{ $rol->rol }}</option>
                        @endforeach
                    </select>
                    @error('id_rol')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <p class="text-sm text-gray-500">Inicio de sesión: <strong>usuario</strong> = cédula, <strong>contraseña</strong> = cédula (podrá cambiarla en Ajustes).</p>

            <div class="flex gap-4 pt-4">
                <a href="{{ route('users.index') }}" class="flex-1 px-6 py-3 bg-gray-300 text-gray-700 rounded-xl font-semibold hover:bg-gray-400 transition-all duration-200 text-center">Cancelar</a>
                <button type="submit" class="btn-primary flex-1 px-6 py-3 bg-[#39B54A] text-white rounded-xl font-semibold hover:bg-[#2d8f3a] hover:shadow-glow transition-all duration-200 shadow-md">Crear Usuario</button>
            </div>
        </form>
    </div>
@endsection
