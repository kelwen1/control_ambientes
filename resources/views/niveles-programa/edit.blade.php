@extends('layouts.app')

@section('title', 'Editar nivel de programa')

@section('content')
    <div class="mb-6 sm:mb-8">
        <h1 class="text-2xl sm:text-3xl font-bold text-[#39B54A] mb-2 tracking-tight">Editar nivel de programa</h1>
        <p class="text-gray-600 text-sm">El <strong>nombre</strong> se actualiza en <span class="font-medium">nivel_programa</span>. Los <strong>meses</strong> sincronizan la tabla <span class="font-medium">duracion</span>. La duración de las fichas usa la del <span class="font-medium">programa</span>.</p>
    </div>

    <div class="card-premium bg-white rounded-xl shadow-card p-6 sm:p-8 max-w-xl">
        <form method="POST" action="{{ route('niveles-programa.update', $nivel->id_nivel_programa) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <div>
                <label for="nivel_programa" class="block text-sm font-semibold text-gray-700 mb-2">
                    Nombre del nivel <span class="text-red-500">*</span>
                </label>
                <input type="text"
                       name="nivel_programa"
                       id="nivel_programa"
                       value="{{ old('nivel_programa', $nivel->nivel_programa) }}"
                       required
                       maxlength="120"
                       class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:border-[#39B54A] focus:ring-2 focus:ring-[#39B54A]/20 focus:outline-none text-sm sm:text-base"
                       oninput="let v=this.value.replace(/[^\p{L}\s]/gu,'').slice(0,120); if(v!==this.value)this.value=v;">
                @error('nivel_programa')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="meses" class="block text-sm font-semibold text-gray-700 mb-2">
                    Meses que dura este nivel <span class="text-red-500">*</span>
                </label>
                <input type="number"
                       name="meses"
                       id="meses"
                       value="{{ old('meses', $mesesActual ?? '') }}"
                       required
                       min="1"
                       max="120"
                       step="1"
                       inputmode="numeric"
                       class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:border-[#39B54A] focus:ring-2 focus:ring-[#39B54A]/20 focus:outline-none text-sm sm:text-base"
                       placeholder="Ej: 24">
                <p class="mt-1.5 text-xs text-gray-500">1–120. Afecta la tabla <span class="font-medium">duracion</span>. Valor sugerido por nombre del nivel si aplica.</p>
                @error('meses')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex flex-col sm:flex-row gap-3">
                <button type="submit" class="btn-primary flex-1 px-6 py-3 bg-[#39B54A] text-white rounded-xl font-semibold hover:bg-[#2d8f3a] shadow-md">
                    Actualizar
                </button>
                <a href="{{ route('niveles-programa.index') }}"
                   class="flex-1 px-6 py-3 bg-gray-200 text-gray-800 rounded-xl font-semibold hover:bg-gray-300 text-center">
                    Volver
                </a>
            </div>
        </form>
    </div>
@endsection
