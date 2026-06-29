<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            color: #1e293b;
            font-size: 12px;
            margin: 0;
            padding: 28px 32px;
        }
        .header {
            background: #7c3aed;
            background: linear-gradient(90deg, #7c3aed, #c026d3);
            color: #ffffff;
            padding: 18px 22px;
            border-radius: 10px;
        }
        .header h1 { margin: 0; font-size: 20px; }
        .header p { margin: 4px 0 0; font-size: 11px; opacity: 0.9; }
        .cards { width: 100%; margin-top: 18px; border-collapse: separate; border-spacing: 8px 0; }
        .cards td {
            width: 25%;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 12px;
            vertical-align: top;
        }
        .cards .valor { font-size: 16px; font-weight: bold; color: #7c3aed; }
        .cards .label { font-size: 10px; color: #475569; margin-top: 4px; }
        .cards .hint { font-size: 9px; color: #94a3b8; margin-top: 2px; }
        h2 { font-size: 14px; color: #1e293b; margin: 24px 0 8px; }
        table.data { width: 100%; border-collapse: collapse; }
        table.data th {
            background: #f1f5f9;
            text-align: left;
            padding: 7px 9px;
            font-size: 11px;
            border-bottom: 2px solid #e2e8f0;
        }
        table.data td {
            padding: 6px 9px;
            font-size: 11px;
            border-bottom: 1px solid #eef2f7;
        }
        table.data td.num, table.data th.num { text-align: right; }
        .empty { color: #94a3b8; font-style: italic; padding: 12px 0; }
        .footer { margin-top: 28px; font-size: 9px; color: #94a3b8; text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Reporte de métricas</h1>
        <p>Generado el {{ $generadoEl }}</p>
    </div>

    <table class="cards">
        <tr>
            @foreach ($resumen as $tarjeta)
                <td>
                    <div class="valor">{{ $tarjeta['valor'] }}</div>
                    <div class="label">{{ $tarjeta['label'] }}</div>
                    <div class="hint">{{ $tarjeta['hint'] }}</div>
                </td>
            @endforeach
        </tr>
    </table>

    <h2>Desglose por categoría</h2>
    @if (count($categorias) > 0)
        <table class="data">
            <thead>
                <tr>
                    <th>Categoría</th>
                    <th class="num">Registros</th>
                    <th class="num">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($categorias as $fila)
                    <tr>
                        <td>{{ $fila['categoria'] }}</td>
                        <td class="num">{{ number_format($fila['registros'], 0, '.', ',') }}</td>
                        <td class="num">${{ number_format($fila['total'], 2, '.', ',') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p class="empty">Aún no hay categorías registradas.</p>
    @endif

    <h2>Detalle de métricas</h2>
    @if (count($registros) > 0)
        <table class="data">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Categoría</th>
                    <th>Etiqueta</th>
                    <th class="num">Valor</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($registros as $registro)
                    <tr>
                        <td>{{ optional($registro->ocurrido_el)->format('Y-m-d') }}</td>
                        <td>{{ $registro->categoria }}</td>
                        <td>{{ $registro->etiqueta }}</td>
                        <td class="num">${{ number_format((float) $registro->valor, 2, '.', ',') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p class="empty">Aún no hay métricas capturadas.</p>
    @endif

    <div class="footer">Impulsado por Overcloud</div>
</body>
</html>
