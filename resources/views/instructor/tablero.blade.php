@extends('layouts.app')

@section('title', 'Mi jornada semanal')

@section('content')
    <div class="mb-6 sm:mb-8">
        <h1 class="text-2xl sm:text-3xl font-bold text-[#39B54A] mb-2">Mi semana de clases</h1>
        <p class="text-gray-600 text-sm sm:text-base">Calendario de 7 días con tus clases: con qué ficha, en qué ambiente y en qué horario.</p>
    </div>

    <div class="bg-white rounded-xl shadow-lg p-4 sm:p-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            @php
                $labels = [
                    'lunes' => 'Lunes',
                    'martes' => 'Martes',
                    'miercoles' => 'Miércoles',
                    'jueves' => 'Jueves',
                    'viernes' => 'Viernes',
                    'sabado' => 'Sábado',
                    'domingo' => 'Domingo',
                ];
            @endphp

            @foreach($diasSemana as $dia)
                @php $items = $porDia[$dia] ?? collect(); @endphp
                <div class="flex flex-col rounded-xl border border-gray-200 bg-gray-50 overflow-hidden min-h-[140px]">
                    <a href="{{ route('instructor.detalle-dia', $dia) }}" class="px-3 py-2 bg-gray-100 border-b border-gray-200 flex items-center justify-between hover:bg-gray-200 transition-colors cursor-pointer">
                        <span class="text-xs font-semibold text-gray-700 uppercase tracking-wide">
                            {{ $labels[$dia] ?? ucfirst($dia) }}
                        </span>
                        <span class="text-[11px] text-gray-500">{{ $items->count() }} {{ Str::plural('clase', $items->count()) }} · Ver detalle →</span>
                    </a>
                    <div class="flex-1 p-3 space-y-2">
                        @if($items->isEmpty())
                            <p class="text-gray-400 text-xs text-center mt-4">Sin clases</p>
                        @else
                            @foreach($items as $r)
                                <div class="rounded-lg bg-white border border-gray-200 px-3 py-2 text-xs">
                                    <p class="font-semibold text-gray-900 mb-0.5">
                                        Ficha {{ $r->ficha->num_ficha ?? '—' }}
                                    </p>
                                    <p class="text-gray-600">
                                        Ambiente {{ $r->ambiente->num_ambiente ?? '—' }}
                                    </p>
                                    @php $mapaJ = [1=>'manana',2=>'tarde',3=>'noche',4=>'fin_semana']; $k = $mapaJ[$r->id_jornada ?? 0] ?? null; $lab = $k ? config("jornadas.$k.label") : 'N/A'; @endphp
                                    <p class="text-[#39B54A] font-semibold mt-0.5">{{ $lab }}</p>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="mt-6">
        <a href="{{ route('dashboard') }}" class="text-[#39B54A] hover:underline font-medium">← Volver al dashboard</a>
    </div>
@endsection
