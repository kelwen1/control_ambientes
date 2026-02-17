<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Fichas - SENA</title>
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
    <h1>Fichas de Formación - SENA</h1>
    <p>Generado: {{ date('d/m/Y H:i') }}</p>
    <table>
        <thead>
            <tr>
                <th>Número Ficha</th>
                <th>Cant. Aprendices</th>
                <th>Programa</th>
                <th>Fecha Inicio</th>
                <th>Fecha Fin</th>
                <th>Fecha Productiva</th>
            </tr>
        </thead>
        <tbody>
            @foreach($fichas as $ficha)
            <tr>
                <td>{{ $ficha->num_ficha ?? 'N/A' }}</td>
                <td>{{ $ficha->cant_aprendices }}</td>
                <td>{{ $ficha->programa->nombre_programa ?? 'N/A' }}</td>
                <td>{{ $ficha->fecha_inicio ? \Carbon\Carbon::parse($ficha->fecha_inicio)->format('d/m/Y') : 'N/A' }}</td>
                <td>{{ $ficha->fecha_fin ? \Carbon\Carbon::parse($ficha->fecha_fin)->format('d/m/Y') : 'N/A' }}</td>
                <td>{{ $ficha->fecha_productiva ? \Carbon\Carbon::parse($ficha->fecha_productiva)->format('d/m/Y') : '—' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="footer">Sistema de Control de Ambientes - SENA</div>
</body>
</html>
