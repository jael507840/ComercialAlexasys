<?php
/**
 * Módulo 15: pagos de servicio técnico.
 * SALDO = (MANO DE OBRA + REPUESTOS) - PAGOS CONFIRMADOS.
 * Se mantienen en una tabla separada de los pagos de ventas.
 */

requiere_permiso('pagos-servicio');
csrf_verificar();

$pagoModelo = new PagoServicio();
$ordenModelo = new OrdenServicio();

$errores = [];
$accion = (string) get('accion', 'listar');

$formulario = [
    'id_orden'    => (int) (get('orden', 0) ?: post('id_orden', 0)),
    'monto'       => '',
    'metodo_pago' => 'Efectivo',
    'referencia'  => '',
    'observacion' => '',
    'fecha_pago'  => date('Y-m-d\TH:i'),
];

if (es_post()) {
    $operacion = (string) post('operacion');

    if ($operacion === 'registrar') {
        requiere_escritura('pagos-servicio');
        $accion = 'nuevo';

        $formulario = [
            'id_orden'    => (int) post('id_orden'),
            'monto'       => trim((string) post('monto')),
            'metodo_pago' => (string) post('metodo_pago'),
            'referencia'  => texto_seguro(post('referencia'), 100),
            'observacion' => texto_seguro(post('observacion'), 200),
            'fecha_pago'  => trim((string) post('fecha_pago')),
        ];

        $orden = $formulario['id_orden'] > 0 ? $ordenModelo->porId($formulario['id_orden']) : null;
        if (!$orden) {
            $errores[] = 'Seleccione una orden de servicio válida.';
        } elseif ($orden['estado'] === 'Anulado') {
            $errores[] = 'No se pueden registrar pagos de una orden anulada.';
        }

        if (!is_numeric($formulario['monto']) || (float) $formulario['monto'] <= 0) {
            $errores[] = 'El monto del pago debe ser mayor que cero.';
        }
        if (!in_array($formulario['metodo_pago'], PagoServicio::METODOS, true)) {
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
                    $formulario['id_orden'],
                    (int) usuario_actual()['id_usuario'],
                    (float) $formulario['monto'],
                    $formulario['metodo_pago'],
                    $formulario['referencia'] !== '' ? $formulario['referencia'] : null,
                    $formulario['observacion'] !== '' ? $formulario['observacion'] : null,
                    str_replace('T', ' ', $formulario['fecha_pago']) . ':00'
                );
                flash('success', 'Pago de servicio registrado correctamente (operación #' . $idPago . ').');
                redirect('ordenes-servicio', ['accion' => 'ver', 'id' => $formulario['id_orden']]);
            } catch (DomainException $e) {
                $errores[] = $e->getMessage();
            } catch (Throwable $e) {
                error_log('[Alex@sys] Error al registrar pago de servicio: ' . $e->getMessage());
                $errores[] = 'No se pudo registrar el pago. La operación fue revertida.';
            }
        }
    }
}

if ($accion === 'nuevo') {
    $idOrden = (int) ($formulario['id_orden'] ?: get('orden', 0));
    $orden = $idOrden > 0 ? $ordenModelo->porIdConRelaciones($idOrden) : null;
    if (!$orden) {
        flash('danger', 'La orden de servicio indicada no existe.');
        redirect('ordenes-servicio');
    }
    if ($orden['estado'] === 'Anulado') {
        flash('danger', 'No se pueden registrar pagos de una orden anulada.');
        redirect('ordenes-servicio');
    }
    $estadoPago = $ordenModelo->estadoPago($idOrden);
    if ($estadoPago['total'] <= 0.009) {
        flash('warning', 'La orden ' . $orden['numero_orden'] . ' aún no tiene un costo definido para cobrar.');
        redirect('ordenes-servicio', ['accion' => 'ver', 'id' => $idOrden]);
    }
    if ($estadoPago['saldo'] <= 0.009) {
        flash('warning', 'La orden ' . $orden['numero_orden'] . ' ya está pagada en su totalidad.');
        redirect('ordenes-servicio', ['accion' => 'ver', 'id' => $idOrden]);
    }

    renderizar('pago-servicio-nuevo', [
        'titulo'     => 'Registrar pago de servicio',
        'subtitulo'  => 'Cobro del saldo pendiente de la orden ' . $orden['numero_orden'],
        'orden'      => $orden,
        'estadoPago' => $estadoPago,
        'formulario' => $formulario,
        'errores'    => $errores,
    ]);
    return;
}

$desde = texto_seguro(get('desde', ''), 10);
$hasta = texto_seguro(get('hasta', ''), 10);

renderizar('pagos-servicio', [
    'titulo'     => 'Pagos de servicios',
    'subtitulo'  => 'Cobros del servicio técnico y órdenes con saldo pendiente',
    'pagos'      => $pagoModelo->listar(0, $desde ?: null, $hasta ?: null),
    'resumen'    => $pagoModelo->resumen($desde ?: null, $hasta ?: null),
    'pendientes' => $ordenModelo->conSaldo(),
    'desde'      => $desde,
    'hasta'      => $hasta,
]);
