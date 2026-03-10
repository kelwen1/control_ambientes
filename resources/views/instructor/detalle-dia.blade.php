@extends('layouts.app')

@section('title', 'Detalle del día - ' . $diaLabel)

@section('content')
    <div class="mb-6 sm:mb-8">
        <a href="{{ route('instructor.tablero') }}" class="text-[#39B54A] hover:underline font-medium mb-2 inline-block">← Mi jornada</a>
        <h1 class="text-2xl sm:text-3xl font-bold text-[#39B54A] mb-2">{{ $diaLabel }} – Detalle</h1>
        <p class="text-gray-600 text-sm sm:text-base">Programa, ficha, hasta cuándo tienes clases, competencia, resultado y sección.</p>
    </div>

    @if($reservas->isEmpty())
        <div class="bg-white rounded-xl shadow-lg p-8 text-center text-gray-500">
            No tienes clases asignadas este día.
        </div>
    @else
        <div class="space-y-6">
            @foreach($reservas as $r)
                <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-[#39B54A]">
                    <div class="flex flex-wrap items-center gap-2 mb-4">
                        <span class="px-2 py-1 bg-[#39B54A] text-white rounded text-sm font-medium">
                            Ambiente {{ $r->ambiente->num_ambiente ?? '—' }}
                        </span>
                        <span class="text-gray-500 text-sm">
                            {{ \Carbon\Carbon::parse($r->hora_inicio)->format('H:i') }} - {{ \Carbon\Carbon::parse($r->hora_fin)->format('H:i') }}
                        </span>
                    </div>
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                        <div>
                            <dt class="text-gray-500 font-medium">Programa</dt>
                            <dd class="text-gray-900 font-semibold">{{ $r->ficha->programa->nombre_programa ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500 font-medium">Ficha</dt>
                            <dd class="text-gray-900 font-semibold">{{ $r->ficha->num_ficha ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500 font-medium">Hasta cuándo tienes clases con ellos</dt>
                            <dd class="text-gray-900">{{ $r->ficha->fecha_fin ? \Carbon\Carbon::parse($r->ficha->fecha_fin)->format('d/m/Y') : '—' }}</dd>
                        </div>
                        @php $avance = $r->ficha->avanceActual; @endphp
                        <div>
                            <dt class="text-gray-500 font-medium">Competencia que estás viendo</dt>
                            <dd class="text-gray-900">{{ $avance->competencia->nombre_competencia ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500 font-medium">Resultado en que van</dt>
                            <dd class="text-gray-900">{{ $avance->resultado->nombre_resultado ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500 font-medium">Sección de ese resultado</dt>
                            <dd class="text-gray-900">{{ $avance->seccion ?? '—' }}</dd>
                        </div>
                    </dl>
                </div>
            @endforeach
        </div>
    @endif
@endsection
