@extends('layouts.app')

@section('title', 'Reporte de Mis Reservas')

@section('content')
    <div class="mb-6 sm:mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-[#39B54A] mb-2">Reporte de Mis Reservas</h1>
            <p class="text-gray-600 text-sm sm:text-base">Reservas activas asignadas a ti (vista previa).</p>
        </div>
        <div class="flex flex-col sm:flex-row gap-2 shrink-0">
            <a href="{{ route('instructor.reporte-reservas-filtro') }}"
               class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-[#39B54A] text-white rounded-xl hover:bg-[#2d8f3a] font-semibold text-sm shadow-md">
                <span>📥</span> Descargar reporte (filtros)
            </a>
            <a href="{{ route('instructor.export-reservas') }}"
               class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-slate-700 text-white rounded-xl hover:bg-slate-800 font-semibold text-sm">
                PDF listado completo
            </a>
        </div>
    </div>

    <div class="card-premium bg-white rounded-xl shadow-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[720px]">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Ambiente</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Ficha</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Programa</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Día</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Jornada</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Inicio</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Fin</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($reservas as $r)
                        @php
                            $mapaJ = [1=>'manana',2=>'tarde',3=>'noche',4=>'fin_semana'];
                            $k = $mapaJ[$r->id_jornada ?? 0] ?? null;
                            $jornadaLabel = $k ? (config("jornadas.$k.label") ?? $k) : 'N/A';
                            $diaLabel = match($r->dia_semana ?? '') {
                                'lunes' => 'Lunes', 'martes' => 'Martes', 'miercoles' => 'Miércoles', 'jueves' => 'Jueves',
                                'viernes' => 'Viernes', 'sabado' => 'Sábado', 'domingo' => 'Domingo',
                                default => ucfirst($r->dia_semana ?? 'N/A'),
                            };
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm">{{ $r->num_ambiente ?? 'N/A' }}</td>
                            <td class="px-4 py-3 text-sm">{{ $r->num_ficha ?? 'N/A' }}</td>
                            <td class="px-4 py-3 text-sm max-w-[200px] truncate" title="{{ $r->nombre_programa ?? '' }}">{{ Str::limit($r->nombre_programa ?? 'N/A', 40) }}</td>
                            <td class="px-4 py-3 text-sm">{{ $diaLabel }}</td>
                            <td class="px-4 py-3 text-sm">{{ $jornadaLabel }}</td>
                            <td class="px-4 py-3 text-sm">{{ $r->fecha_inicio ? \Carbon\Carbon::parse($r->fecha_inicio)->format('d/m/Y') : 'N/A' }}</td>
                            <td class="px-4 py-3 text-sm">{{ $r->fecha_fin ? \Carbon\Carbon::parse($r->fecha_fin)->format('d/m/Y') : 'N/A' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center text-gray-500">No tienes reservas activas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
