@extends('layouts.app')

@section('title', 'Editar Ambiente')

@section('content')
    <div class="mb-6 sm:mb-8 animate-fade-slide-up">
        <h1 class="text-2xl sm:text-3xl font-bold text-[#39B54A] mb-2 tracking-tight">
            Editar Ambiente
        </h1>
        <p class="text-gray-600 text-sm sm:text-base">Solo puedes modificar la <span class="font-medium text-gray-800">capacidad máxima</span>. Número, estado y tipo no se pueden cambiar aquí.</p>
    </div>

    <div class="card-premium bg-white rounded-xl shadow-card p-6 sm:p-8 hover:shadow-card-hover transition-shadow duration-300">
        <form method="POST" action="{{ route('ambientes.gestion.update', $ambiente->id_ambiente) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <div>
                <span class="block text-sm sm:text-base font-semibold text-gray-700 mb-2">
                    Número de Ambiente
                </span>
                <div class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl bg-gray-50 text-gray-800 text-sm sm:text-base font-medium cursor-not-allowed select-none"
                     aria-readonly="true">
                    {{ $ambiente->num_ambiente }}
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                <div>
                    <label for="capacidad_max" class="block text-sm sm:text-base font-semibold text-gray-700 mb-2">
                        Capacidad Máxima <span class="text-red-500">*</span>
                    </label>
                    <input type="text"
                           name="capacidad_max"
                           id="capacidad_max"
                           value="{{ old('capacidad_max', $ambiente->capacidad_max) }}"
                           required
                           inputmode="numeric"
                           maxlength="2"
                           pattern="[0-9]{1,2}"
                           class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:border-[#39B54A] focus:ring-2 focus:ring-[#39B54A]/20 focus:outline-none transition-all duration-200 text-sm sm:text-base"
                           placeholder="Entre 30 y 40"
                           oninput="let v=this.value.replace(/\D/g,'').slice(0,2); if(v!==''){let n=parseInt(v,10); if(n>40)v='40'; else if(v.length===2 && n<30)v='30';} this.value=v;">
                    @error('capacidad_max')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <span class="block text-sm sm:text-base font-semibold text-gray-700 mb-2">
                        Estado
                    </span>
                    <div class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl bg-gray-50 text-gray-800 text-sm sm:text-base cursor-not-allowed select-none">
                        {{ $estados[$ambiente->id_estado] ?? '—' }}
                    </div>
                </div>

                <div>
                    <span class="block text-sm sm:text-base font-semibold text-gray-700 mb-2">
                        Tipo de Ambiente
                    </span>
                    <div class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl bg-gray-50 text-gray-800 text-sm sm:text-base cursor-not-allowed select-none">
                        {{ $tipos[$ambiente->id_tipo_ambiente] ?? '—' }}
                    </div>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row gap-4 pt-4 border-t border-gray-200">
                <button type="submit"
                        class="btn-primary flex-1 px-6 py-3 bg-[#39B54A] text-white rounded-xl font-semibold hover:bg-[#2d8f3a] hover:shadow-glow transition-all duration-200 shadow-md">
                    Actualizar Ambiente
                </button>
                <a href="{{ route('ambientes.gestion.index') }}"
                   class="flex-1 px-6 py-3 bg-gray-300 text-gray-700 rounded-xl font-semibold hover:bg-gray-400 transition-all duration-200 text-center">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
@endsection
