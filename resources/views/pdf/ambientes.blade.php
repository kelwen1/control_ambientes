<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Programación - SENA</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; }
        h1 { color: #39B54A; font-size: 18px; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: left; }
        th { background-color: #39B54A; color: white; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        .footer { margin-top: 20px; font-size: 9px; color: #666; }
    </style>
</head>
<body>
    <h1>Programación - SENA</h1>
    <p>Generado: {{ date('d/m/Y H:i') }}</p>
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
            @foreach($reservas as $r)
            <tr>
                <td>{{ $r->num_ambiente ?? 'N/A' }}</td>
                <td>{{ $r->num_ficha ?? 'N/A' }}</td>
                <td>{{ $r->nombre_estado ?? 'N/A' }}</td>
                <td>{{ match($r->dia_semana ?? '') { 'lunes' => 'Lunes', 'martes' => 'Martes', 'miercoles' => 'Miércoles', 'jueves' => 'Jueves', 'viernes' => 'Viernes', 'sabado' => 'Sábado', 'domingo' => 'Domingo', default => ucfirst($r->dia_semana ?? 'N/A') } }}</td>
                <td>@php $m = [1=>'manana',2=>'tarde',3=>'noche',4=>'fin_semana']; $k = $m[$r->id_jornada ?? 0] ?? null; echo $k ? (config("jornadas.$k.label") ?? ucfirst($k)) : 'N/A'; @endphp</td>
                <td>{{ $r->fecha_inicio ? \Carbon\Carbon::parse($r->fecha_inicio)->format('d/m/Y') : 'N/A' }}</td>
                <td>{{ $r->fecha_fin ? \Carbon\Carbon::parse($r->fecha_fin)->format('d/m/Y') : 'N/A' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="footer">Sistema de Control de Ambientes - SENA</div>
</body>
</html>
