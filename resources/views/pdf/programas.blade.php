<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Programas - SENA</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; }
        h1 { color: #39B54A; font-size: 18px; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #39B54A; color: white; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        .footer { margin-top: 20px; font-size: 9px; color: #666; }
    </style>
</head>
<body>
    <h1>Programas de formación - SENA</h1>
    <p>Generado: {{ date('d/m/Y H:i') }}</p>
    <table>
        <thead>
            <tr>
                <th>Programa</th>
                <th>Nivel</th>
                <th>Duración</th>
            </tr>
        </thead>
        <tbody>
            @foreach($programas as $p)
            <tr>
                <td>{{ $p->nombre_programa ?? 'N/A' }}</td>
                <td>{{ $niveles[$p->id_nivel_programa] ?? '—' }}</td>
                <td>{{ $duraciones[$p->id_duracion] ?? '—' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="footer">Sistema de Control de Ambientes - SENA</div>
</body>
</html>
