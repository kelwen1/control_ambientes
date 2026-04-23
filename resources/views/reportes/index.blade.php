@extends('layouts.app')

@section('title', 'Reportes')

@section('content')
    <div class="mb-6 sm:mb-8">
        <h1 class="text-2xl sm:text-3xl font-bold text-[#39B54A] mb-2 tracking-tight">Reportes</h1>
        <p class="text-gray-600 text-sm sm:text-base">Puede descargar reportes en Excel y en PDF. Si lo desea, utilice el mismo criterio de búsqueda que en cada módulo, añadiendo el parámetro <code class="text-xs bg-gray-100 px-1 rounded">?search=</code> a la barra de direcciones (opcional).</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 max-w-5xl">
        {{-- Programación (ocupación / reservas) --}}
        <div class="card-premium bg-white rounded-xl shadow-card p-6 sm:p-8 border-l-4 border-slate-600">
            <h2 class="text-lg font-bold text-gray-800 mb-2 flex items-center gap-2">
                <span>📅</span> Programación
            </h2>
            <p class="text-sm text-gray-600 mb-4">Ocupación por ambiente, ficha, estado, día, jornada y fechas (antes «reservas y ambientes»).</p>
            <div class="flex flex-col sm:flex-row flex-wrap gap-2">
                <a href="{{ route('ambientes.export') }}"
                   class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-slate-700 text-white rounded-xl font-semibold hover:bg-slate-800 shadow-md transition-colors text-sm">
                    📄 Descargar PDF
                </a>
                <a href="{{ route('ambientes.export-excel') }}"
                   class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-[#39B54A] text-white rounded-xl font-semibold hover:bg-[#2d8f3a] shadow-md transition-colors text-sm">
                    📥 Descargar Excel
                </a>
            </div>
            <a href="{{ route('ambientes.index') }}" class="block mt-3 text-sm text-[#39B54A] hover:underline font-medium">Ir a Horarios →</a>
        </div>

        {{-- Fichas --}}
        <div class="card-premium bg-white rounded-xl shadow-card p-6 sm:p-8 border-l-4 border-[#39B54A]">
            <h2 class="text-lg font-bold text-gray-800 mb-2 flex items-center gap-2">
                <span>📋</span> Fichas de formación
            </h2>
            <p class="text-sm text-gray-600 mb-4">Listado de fichas con datos principales (programa, fechas, etc.).</p>
            <div class="flex flex-col sm:flex-row flex-wrap gap-2">
                <a href="{{ route('fichas.export') }}"
                   class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-slate-700 text-white rounded-xl font-semibold hover:bg-slate-800 shadow-md transition-colors text-sm">
                    📄 Descargar PDF
                </a>
                <a href="{{ route('fichas.export-excel') }}"
                   class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-[#39B54A] text-white rounded-xl font-semibold hover:bg-[#2d8f3a] shadow-md transition-colors text-sm">
                    📥 Descargar Excel
                </a>
            </div>
            <a href="{{ route('fichas.index') }}" class="block mt-3 text-sm text-[#39B54A] hover:underline font-medium">Ir a Fichas →</a>
        </div>

        {{-- Catálogo ambientes --}}
        <div class="card-premium bg-white rounded-xl shadow-card p-6 sm:p-8 border-l-4 border-amber-500">
            <h2 class="text-lg font-bold text-gray-800 mb-2 flex items-center gap-2">
                <span>🏢</span> Ambientes (catálogo)
            </h2>
            <p class="text-sm text-gray-600 mb-4">Número, estado, capacidad y tipo de cada ambiente en gestión.</p>
            <div class="flex flex-col sm:flex-row flex-wrap gap-2">
                <a href="{{ route('ambientes.gestion.export') }}"
                   class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-slate-700 text-white rounded-xl font-semibold hover:bg-slate-800 shadow-md transition-colors text-sm">
                    📄 Descargar PDF
                </a>
                <a href="{{ route('ambientes.gestion.export-excel') }}"
                   class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-[#39B54A] text-white rounded-xl font-semibold hover:bg-[#2d8f3a] shadow-md transition-colors text-sm">
                    📥 Descargar Excel
                </a>
            </div>
            <a href="{{ route('ambientes.gestion.index') }}" class="block mt-3 text-sm text-[#39B54A] hover:underline font-medium">Ir a Gestión de ambientes →</a>
        </div>

        {{-- Programas --}}
        <div class="card-premium bg-white rounded-xl shadow-card p-6 sm:p-8 border-l-4 border-indigo-500">
            <h2 class="text-lg font-bold text-gray-800 mb-2 flex items-center gap-2">
                <span>🎓</span> Programas
            </h2>
            <p class="text-sm text-gray-600 mb-4">Programas registrados con nivel y duración asociados.</p>
            <div class="flex flex-col sm:flex-row flex-wrap gap-2">
                <a href="{{ route('programas.export') }}"
                   class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-slate-700 text-white rounded-xl font-semibold hover:bg-slate-800 shadow-md transition-colors text-sm">
                    📄 Descargar PDF
                </a>
                <a href="{{ route('programas.export-excel') }}"
                   class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-[#39B54A] text-white rounded-xl font-semibold hover:bg-[#2d8f3a] shadow-md transition-colors text-sm">
                    📥 Descargar Excel
                </a>
            </div>
            <a href="{{ route('programas.index') }}" class="block mt-3 text-sm text-[#39B54A] hover:underline font-medium">Ir a Programas →</a>
        </div>

        {{-- Competencias --}}
        <div class="card-premium bg-white rounded-xl shadow-card p-6 sm:p-8 border-l-4 border-teal-500 md:col-span-2">
            <h2 class="text-lg font-bold text-gray-800 mb-2 flex items-center gap-2">
                <span>🧩</span> Competencias
            </h2>
            <p class="text-sm text-gray-600 mb-4">Catálogo: norma, códigos, horas, porcentaje y resultados.</p>
            <div class="flex flex-col sm:flex-row flex-wrap gap-2">
                <a href="{{ route('competencias.export') }}"
                   class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-slate-700 text-white rounded-xl font-semibold hover:bg-slate-800 shadow-md transition-colors text-sm">
                    📄 Descargar PDF
                </a>
                <a href="{{ route('competencias.export-excel') }}"
                   class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-[#39B54A] text-white rounded-xl font-semibold hover:bg-[#2d8f3a] shadow-md transition-colors text-sm">
                    📥 Descargar Excel
                </a>
            </div>
            <a href="{{ route('competencias.index') }}" class="block mt-3 text-sm text-[#39B54A] hover:underline font-medium">Ir a Competencias →</a>
        </div>
    </div>
@endsection
