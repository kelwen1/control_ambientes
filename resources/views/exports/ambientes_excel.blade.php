{{-- Tabla HTML para abrir en Excel (.xls); estilos alineados con pdf/ambientes --}}
@php
    $diasLabels = ['lunes' => 'Lunes', 'martes' => 'Martes', 'miercoles' => 'Miércoles', 'jueves' => 'Jueves', 'viernes' => 'Viernes', 'sabado' => 'Sábado', 'domingo' => 'Domingo'];
    $mapaJ = [1 => 'manana', 2 => 'tarde', 3 => 'noche', 4 => 'fin_semana'];
@endphp
<html xmlns:x="urn:schemas-microsoft-com:office:excel">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <style>
        table { border-collapse: collapse; width: 100%; font-family: Calibri, Arial, sans-serif; font-size: 10pt; }
        th, td { border: 1px solid #d0d0d0; padding: 6px 8px; text-align: left; vertical-align: middle; }
        th { background-color: #39B54A; color: #ffffff; font-weight: bold; }
        tr.data-even td { background-color: #f9f9f9; }
        .title { font-size: 16pt; font-weight: bold; color: #39B54A; padding: 0 0 8px 0; border: none; }
        .meta { font-size: 10pt; color: #666666; padding: 0 0 16px 0; border: none; }
        .footer { font-size: 9pt; color: #666666; margin-top: 20px; border: none; }
    </style>
</head>
<body>
    <table>
        <tr><td colspan="7" class="title">Programación - SENA</td></tr>
        <tr><td colspan="7" class="meta">Generado: {{ date('d/m/Y H:i') }}</td></tr>
    </table>
    <table>
        <thead>
            <tr>
                <th>Ambiente</th>
                <th>Ficha</th>
                <th>Estado</th>
                <th>Día</th>
                <th>Jornada</th>
                <th>Fecha Inicio</th>
                <th>Fecha Fin</th>
            </tr>
        </thead>
        <tbody>
            @foreach($reservas as $i => $r)
            @php
                $diaTxt = $diasLabels[$r->dia_semana ?? ''] ?? ucfirst($r->dia_semana ?? 'N/A');
                $jk = $mapaJ[$r->id_jornada ?? 0] ?? null;
                $jornadaTxt = $jk ? (config("jornadas.$jk.label") ?? ucfirst($jk)) : 'N/A';
            @endphp
            <tr class="{{ $i % 2 === 1 ? 'data-even' : '' }}">
                <td>{{ $r->num_ambiente ?? 'N/A' }}</td>
                <td>{{ $r->num_ficha ?? 'N/A' }}</td>
                <td>{{ $r->nombre_estado ?? 'N/A' }}</td>
                <td>{{ $diaTxt }}</td>
                <td>{{ $jornadaTxt }}</td>
                <td>{{ $r->fecha_inicio ? \Carbon\Carbon::parse($r->fecha_inicio)->format('d/m/Y') : 'N/A' }}</td>
                <td>{{ $r->fecha_fin ? \Carbon\Carbon::parse($r->fecha_fin)->format('d/m/Y') : 'N/A' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <table>
        <tr><td colspan="7" class="footer">Sistema de Control de Ambientes - SENA</td></tr>
    </table>
</body>
</html>
