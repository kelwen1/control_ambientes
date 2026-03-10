@extends('layouts.app')

@section('title', 'Mi jornada semanal')

@section('content')
    <div class="mb-6 sm:mb-8">
        <h1 class="text-2xl sm:text-3xl font-bold text-[#39B54A] mb-2">Mi jornada (Lunes a Viernes)</h1>
        <p class="text-gray-600 text-sm sm:text-base">Salón, ficha y horario por día. Haz clic en un día para ver el detalle.</p>
    </div>

    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[600px]">
                <thead class="bg-gray-50 border-b-2 border-gray-200">
                    <tr>
                        @foreach($diasSemana as $dia)
                            <th class="px-3 py-4 text-center text-sm font-semibold text-gray-700 uppercase">
                                {{ ucfirst($dia) }}
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr>
                        @foreach($diasSemana as $dia)
                            <td class="align-top p-3 min-h-[120px]">
                                @php $items = $porDia[$dia] ?? collect(); @endphp
                                @if($items->isEmpty())
                                    <p class="text-gray-400 text-sm py-4 text-center">Sin clases</p>
                                @else
                                    <ul class="space-y-2">
                                        @foreach($items as $r)
                                            <li class="text-sm border border-gray-200 rounded-lg p-2 bg-gray-50">
                                                <p class="font-semibold text-gray-800">Ambiente {{ $r->ambiente->num_ambiente ?? '—' }}</p>
                                                <p class="text-gray-600">Ficha {{ $r->ficha->num_ficha ?? '—' }}</p>
                                                <p class="text-xs text-[#39B54A] font-medium">
                                                    {{ \Carbon\Carbon::parse($r->hora_inicio)->format('H:i') }} - {{ \Carbon\Carbon::parse($r->hora_fin)->format('H:i') }}
                                                </p>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                                <a href="{{ route('instructor.detalle-dia', $dia) }}"
                                   class="mt-2 block text-center text-sm text-[#39B54A] hover:underline font-medium">
                                    Ver detalle del día →
                                </a>
                            </td>
                        @endforeach
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6">
        <a href="{{ route('dashboard') }}" class="text-[#39B54A] hover:underline font-medium">← Volver al dashboard</a>
    </div>
@endsection
