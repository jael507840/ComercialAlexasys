<?php
/**
 * Módulo 10: pagos de ventas.
 * SALDO = TOTAL DE LA VENTA - SUMA DE PAGOS CONFIRMADOS.
 * Los pagos de ventas se mantienen separados de los pagos de servicio.
 */

requiere_permiso('pagos-venta');
csrf_verificar();

$pagoModelo = new PagoVenta();
$ventaModelo = new Venta();

$errores = [];
$accion = (string) get('accion', 'listar');

$formulario = [
    'id_venta'    => (int) (get('venta', 0) ?: post('id_venta', 0)),
    'monto'       => '',
    'metodo_pago' => 'Efectivo',
    'referencia'  => '',
    'observacion' => '',
    'fecha_pago'  => date('Y-m-d\TH:i'),
];

if (es_post()) {
    $operacion = (string) post('operacion');

    if ($operacion === 'registrar') {
        requiere_escritura('pagos-venta');
        $accion = 'nuevo';

        $formulario = [
            'id_venta'    => (int) post('id_venta'),
            'monto'       => trim((string) post('monto')),
            'metodo_pago' => (string) post('metodo_pago'),
            'referencia'  => texto_seguro(post('referencia'), 80),
            'observacion' => texto_seguro(post('observacion'), 300),
            'fecha_pago'  => trim((string) post('fecha_pago')),
        ];

        $venta = $formulario['id_venta'] > 0 ? $ventaModelo->porId($formulario['id_venta']) : null;
        if (!$venta) {
            $errores[] = 'Seleccione una venta válida para registrar el pago.';
        } elseif ($venta['estado'] !== 'Confirmada') {
            $errores[] = 'No se pueden registrar pagos de una venta anulada.';
        }

        if (!is_numeric($formulario['monto']) || (float) $formulario['monto'] <= 0) {
            $errores[] = 'El monto del pago debe ser mayor que cero.';
        }
        if (!in_array($formulario['metodo_pago'], PagoVenta::METODOS, true)) {
            $errores[] = 'Seleccione un método de pago válido.';
        }
        if (!fecha_hora_valida($formulario['fecha_pago'])) {
            $errores[] = 'La fecha y hora del pago no son válidas.';
        } elseif (strtotime(str_replace('T', ' ', $formulario['fecha_pago'])) > time() + 300) {
            $errores[] = 'La fecha del pago no puede ser posterior a la fecha actual.';
        }

        if ($errores === []) {
            try {
                $idPago = $pagoModelo->registrar(
                    $formulario['id_venta'],
                    (int) usuario_actual()['id_usuario'],
                    (float) $formulario['monto'],
                    $formulario['metodo_pago'],
                    $formulario['referencia'] !== '' ? $formulario['referencia'] : null,
                    $formulario['observacion'] !== '' ? $formulario['observacion'] : null,
                    str_replace('T', ' ', $formulario['fecha_pago']) . ':00'
                );
                flash('success', 'Pago de venta registrado correctamente (operación #' . $idPago . ').');
                redirect('ventas', ['accion' => 'ver', 'id' => $formulario['id_venta']]);
            } catch (DomainException $e) {
                $errores[] = $e->getMessage();
            } catch (Throwable $e) {
                error_log('[Alex@sys] Error al registrar pago de venta: ' . $e->getMessage());
                $errores[] = 'No se pudo registrar el pago. La operación fue revertida.';
            }
        }
    }
}

if ($accion === 'nuevo') {
    $idVenta = (int) ($formulario['id_venta'] ?: get('venta', 0));
    $venta = $idVenta > 0 ? $ventaModelo->porIdConRelaciones($idVenta) : null;
    if (!$venta) {
        flash('danger', 'La venta indicada no existe.');
        redirect('ventas');
    }
    if ($venta['estado'] !== 'Confirmada') {
        flash('danger', 'No se pueden registrar pagos de una venta anulada.');
        redirect('ventas');
    }
    $estadoPago = $ventaModelo->estadoPago($idVenta);
    if ($estadoPago['saldo'] <= 0.009) {
        flash('warning', 'La venta ' . $venta['numero_comprobante'] . ' ya está pagada en su totalidad.');
        redirect('ventas', ['accion' => 'ver', 'id' => $idVenta]);
    }

    renderizar('pago-venta-nuevo', [
        'titulo'     => 'Registrar pago de venta',
        'subtitulo'  => 'Cobro del saldo pendiente del comprobante ' . $venta['numero_comprobante'],
        'venta'      => $venta,
        'estadoPago' => $estadoPago,
        'formulario' => $formulario,
        'errores'    => $errores,
    ]);
    return;
}

$desde = texto_seguro(get('desde', ''), 10);
$hasta = texto_seguro(get('hasta', ''), 10);

renderizar('pagos-venta', [
    'titulo'       => 'Pagos de ventas',
    'subtitulo'    => 'Cobros registrados y ventas con saldo pendiente',
    'pagos'        => $pagoModelo->listar(0, $desde ?: null, $hasta ?: null),
    'resumen'      => $pagoModelo->resumen($desde ?: null, $hasta ?: null),
    'pendientes'   => $ventaModelo->conSaldo(),
    'desde'        => $desde,
    'hasta'        => $hasta,
]);
