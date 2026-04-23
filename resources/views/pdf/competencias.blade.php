<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Competencias - SENA</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 9px; }
        h1 { color: #39B54A; font-size: 18px; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { border: 1px solid #ddd; padding: 5px; text-align: left; word-wrap: break-word; }
        th { background-color: #39B54A; color: white; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        .footer { margin-top: 20px; font-size: 9px; color: #666; }
    </style>
</head>
<body>
    <h1>Competencias - SENA</h1>
    <p>Generado: {{ date('d/m/Y H:i') }}</p>
    <table>
        <thead>
            <tr>
                <th>Competencia</th>
                <th>Norma</th>
                <th>Código</th>
                <th>H. totales</th>
                <th>% horas</th>
                <th>H. en complejo</th>
                <th>N° res.</th>
                <th>Programa (vínc.)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($competencias as $c)
            <tr>
                <td>{{ $c->nombre_competencia ?? 'N/A' }}</td>
                <td>{{ $c->nombre_norma ?? 'N/A' }}</td>
                <td>{{ $c->codigo ?? 'N/A' }}</td>
                <td>{{ (int) ($c->hora_totales ?? 0) }}</td>
                <td>{{ (int) ($c->porcentaje_horas ?? 0) }}</td>
                <td>{{ (int) $c->horasDuracionEnComplejo() }}</td>
                <td>{{ (int) ($c->cantidad_resultados ?? 0) }}</td>
                <td>{{ $c->id_programa ? ($c->programa->nombre_programa ?? '—') : 'Catálogo' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="footer">Sistema de Control de Ambientes - SENA</div>
</body>
</html>
