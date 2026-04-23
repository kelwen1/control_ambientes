{{-- Tabla HTML para abrir en Excel (.xls); estilos alineados con pdf/fichas --}}
<html xmlns:x="urn:schemas-microsoft-com:office:excel">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <style>
        table { border-collapse: collapse; width: 100%; font-family: Calibri, Arial, sans-serif; font-size: 11pt; }
        th, td { border: 1px solid #d0d0d0; padding: 8px 10px; text-align: left; vertical-align: middle; }
        th { background-color: #39B54A; color: #ffffff; font-weight: bold; }
        tr.data-even td { background-color: #f9f9f9; }
        .title { font-size: 16pt; font-weight: bold; color: #39B54A; padding: 0 0 8px 0; border: none; }
        .meta { font-size: 10pt; color: #666666; padding: 0 0 16px 0; border: none; }
        .footer { font-size: 9pt; color: #666666; margin-top: 20px; border: none; }
    </style>
</head>
<body>
    <table>
        <tr><td colspan="6" class="title">Fichas de Formación - SENA</td></tr>
        <tr><td colspan="6" class="meta">Generado: {{ date('d/m/Y H:i') }}</td></tr>
    </table>
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
            @foreach($fichas as $i => $ficha)
            <tr class="{{ $i % 2 === 1 ? 'data-even' : '' }}">
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
    <table>
        <tr><td colspan="6" class="footer">Sistema de Control de Ambientes - SENA</td></tr>
    </table>
</body>
</html>
