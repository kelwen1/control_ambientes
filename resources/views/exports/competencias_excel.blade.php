{{-- Tabla HTML para abrir en Excel (.xls) --}}
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
        <tr><td colspan="8" class="title">Competencias - SENA</td></tr>
        <tr><td colspan="8" class="meta">Generado: {{ date('d/m/Y H:i') }}</td></tr>
    </table>
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
            @foreach($competencias as $i => $c)
            <tr class="{{ $i % 2 === 1 ? 'data-even' : '' }}">
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
    <table>
        <tr><td colspan="8" class="footer">Sistema de Control de Ambientes - SENA</td></tr>
    </table>
</body>
</html>
