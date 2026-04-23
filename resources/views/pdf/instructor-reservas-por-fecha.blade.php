<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mis reservas por fecha - SENA</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; }
        h1 { color: #39B54A; font-size: 18px; margin-bottom: 4px; }
        .subtitle { color: #666; font-size: 9px; margin-bottom: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { border: 1px solid #ddd; padding: 5px; text-align: left; }
        th { background-color: #39B54A; color: white; font-size: 8px; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        td { font-size: 8px; }
        .footer { margin-top: 16px; font-size: 8px; color: #666; }
    </style>
</head>
<body>
    <h1>Mis reservas (por fecha de sesión)</h1>
    <p class="subtitle">{{ $subtitulo ?? '' }} · Generado: {{ date('d/m/Y H:i') }}</p>
    <table>
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Día</th>
                <th>Ambiente</th>
                <th>Ficha</th>
                <th>Programa</th>
                <th>Competencia</th>
                <th>Resultado</th>
                <th>Jornada</th>
            </tr>
        </thead>
        <tbody>
            @foreach($filas as $r)
                @php
                    $mapaJ = [1=>'manana',2=>'tarde',3=>'noche',4=>'fin_semana'];
                    $k = $mapaJ[$r->id_jornada ?? 0] ?? null;
                    $jornadaLabel = $k ? (config("jornadas.$k.label") ?? ucfirst($k)) : 'N/A';
                    $diaLabel = match($r->dia_semana ?? '') { 'lunes' => 'Lunes', 'martes' => 'Martes', 'miercoles' => 'Miércoles', 'jueves' => 'Jueves', 'viernes' => 'Viernes', 'sabado' => 'Sábado', 'domingo' => 'Domingo', default => ucfirst($r->dia_semana ?? 'N/A') };
                    $fechaFmt = !empty($r->fecha_sesion) ? \Carbon\Carbon::parse($r->fecha_sesion)->format('d/m/Y') : '—';
                @endphp
                <tr>
                    <td>{{ $fechaFmt }}</td>
                    <td>{{ $diaLabel }}</td>
                    <td>{{ $r->num_ambiente ?? 'N/A' }}</td>
                    <td>{{ $r->num_ficha ?? 'N/A' }}</td>
                    <td>{{ Str::limit($r->nombre_programa ?? 'N/A', 28) }}</td>
                    <td>{{ Str::limit($r->nombre_competencia ?? 'N/A', 28) }}</td>
                    <td>{{ Str::limit($r->resultado_denominacion ?? '—', 32) }}</td>
                    <td>{{ $jornadaLabel }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <div class="footer">Sistema de Control de Ambientes - SENA</div>
</body>
</html>
