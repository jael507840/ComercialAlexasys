<?php
/**
 * Documento PDF de un reporte. Se renderiza con Dompdf desde
 * Controllers/reportes.php cuando la URL incluye formato=pdf.
 */
$camposFecha = ['fecha_venta', 'fecha_compra', 'fecha_pago', 'fecha_recepcion', 'fecha_registro'];
$camposMoneda = [
    'precio_venta', 'total', 'pagado', 'saldo', 'total_compra', 'costo_adquisicion',
    'margen', 'monto',
];
$camposCantidad = ['items', 'total_items', 'stock_actual', 'stock_minimo', 'total_ordenes'];
$tituloReporte = $reportesDisponibles[$tipoReporte] ?? 'Reporte';
$totalRegistros = (int) ($resumen['registros'] ?? count($datos));
?><!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?= e($tituloReporte) ?> · Alex@sys</title>
    <style>
        @page { margin: 14mm 10mm 17mm 10mm; }
        * { box-sizing: border-box; }
        body {
            font-family: "DejaVu Sans", sans-serif;
            font-size: 8.5px;
            color: #1f2937;
            margin: 0;
        }
        .cabecera { border-bottom: 2px solid #0f172a; padding-bottom: 6px; margin-bottom: 10px; }
        .cabecera table { width: 100%; border-collapse: collapse; }
        .cabecera td { vertical-align: middle; }
        .marca { font-size: 21px; font-weight: bold; color: #0f172a; letter-spacing: -0.5px; }
        .marca span { color: #0ea5e9; }
        .lema { font-size: 8px; color: #64748b; margin-top: 2px; }
        .meta { text-align: right; font-size: 8px; color: #475569; line-height: 1.55; }
        h1 { font-size: 15px; margin: 0 0 2px; color: #0f172a; }
        .subtitulo { font-size: 8.5px; color: #475569; margin-bottom: 8px; }
        .filtros {
            background: #f1f5f9;
            border-left: 3px solid #0ea5e9;
            padding: 5px 8px;
            margin: 0 0 8px;
            font-size: 8px;
            color: #334155;
        }
        .resumen { width: 100%; border-collapse: collapse; margin-bottom: 9px; }
        .resumen td { border: 1px solid #cbd5e1; padding: 4px 6px; background: #f8fafc; }
        .resumen .etiqueta {
            display: block;
            font-size: 7px;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .resumen .valor { display: block; font-size: 11px; font-weight: bold; color: #0f172a; }
        table.datos { width: 100%; border-collapse: collapse; }
        table.datos thead { display: table-header-group; }
        table.datos th {
            background: #0f172a;
            color: #ffffff;
            font-size: 7.5px;
            text-align: left;
            padding: 4px 5px;
            border: 1px solid #0f172a;
        }
        table.datos td {
            font-size: 7.5px;
            padding: 3px 5px;
            border: 1px solid #e2e8f0;
            vertical-align: top;
        }
        table.datos tr.par td { background: #f8fafc; }
        .num { text-align: right; white-space: nowrap; }
        .vacio { text-align: center; color: #64748b; padding: 16px; }
        footer {
            position: fixed;
            bottom: -12mm;
            left: 0;
            right: 0;
            border-top: 1px solid #cbd5e1;
            padding-top: 3px;
            font-size: 7px;
            color: #64748b;
        }
        footer .izq { float: left; }
        footer .der { float: right; }
        footer .pagina:before { content: counter(page); }
        footer .paginas:before { content: counter(pages); }
    </style>
</head>
<body>
    <div class="cabecera">
        <table>
            <tr>
                <td style="width:55%">
                    <div class="marca">Alex<span>@</span>sys</div>
                    <div class="lema">Tecnología, ventas y servicio técnico</div>
                </td>
                <td class="meta">
                    <div><strong>Reporte:</strong> <?= e($tituloReporte) ?></div>
                    <div>Generado: <?= e($generadoEn) ?></div>
                    <div>Usuario: <?= e($usuario) ?></div>
                </td>
            </tr>
        </table>
    </div>

    <h1><?= e($tituloReporte) ?></h1>
    <div class="subtitulo">Informe operativo generado desde la base de datos <strong>dbalexasys</strong>.</div>

    <div class="filtros"><strong>Filtros:</strong> <?= e(implode(' · ', $filtrosTexto)) ?></div>

    <table class="resumen">
        <tr>
            <td>
                <span class="etiqueta">Registros</span>
                <span class="valor"><?= $totalRegistros ?></span>
            </td>
            <?php foreach (($resumen['totales'] ?? []) as $total): ?>
                <td>
                    <span class="etiqueta"><?= e($total['etiqueta']) ?></span>
                    <span class="valor"><?= e($total['valor']) ?></span>
                </td>
            <?php endforeach; ?>
        </tr>
    </table>

    <table class="datos">
        <thead>
            <tr>
                <?php foreach ($columnas as $campo => $etiqueta): ?>
                    <th class="<?= in_array($campo, array_merge($camposMoneda, $camposCantidad), true) || $campo === 'margen_porcentaje' ? 'num' : '' ?>">
                        <?= e($etiqueta) ?>
                    </th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php if ($datos === []): ?>
                <tr>
                    <td class="vacio" colspan="<?= count($columnas) ?>">
                        No hay datos para mostrar con los filtros seleccionados.
                    </td>
                </tr>
            <?php endif; ?>
            <?php foreach ($datos as $indice => $fila): ?>
                <tr class="<?= $indice % 2 === 1 ? 'par' : '' ?>">
                    <?php foreach ($columnas as $campo => $etiqueta): ?>
                        <?php
                        $valor = $fila[$campo] ?? null;
                        if ($valor === null || $valor === '') {
                            $contenido = '—';
                        } elseif ($campo === 'margen_porcentaje') {
                            $contenido = number_format((float) $valor, 2, ',', '.') . '%';
                        } elseif (in_array($campo, $camposMoneda, true)) {
                            $contenido = formato_moneda($valor);
                        } elseif (in_array($campo, $camposFecha, true)) {
                            $contenido = formato_fecha((string) $valor, str_contains((string) $valor, ':'));
                        } elseif (in_array($campo, $camposCantidad, true)) {
                            $contenido = (string) (int) $valor;
                        } elseif ($campo === 'estado') {
                            $contenido = etiqueta_estado((string) $valor);
                        } else {
                            $contenido = (string) $valor;
                        }
                        $esNumero = in_array($campo, $camposMoneda, true)
                            || in_array($campo, $camposCantidad, true)
                            || $campo === 'margen_porcentaje';
                        ?>
                        <td class="<?= $esNumero ? 'num' : '' ?>"><?= e($contenido) ?></td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <footer>
        <span class="izq">Alex@sys · dbalexasys · Documento generado automáticamente</span>
        <span class="der">Página <span class="pagina"></span> de <span class="paginas"></span></span>
    </footer>
</body>
</html>
