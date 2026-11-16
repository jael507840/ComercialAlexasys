<?php
/**
 * Módulo de garantías de productos vendidos y servicios técnicos.
 */

requiere_permiso('garantias');
csrf_verificar();

$garantiaModelo = new Garantia();
$ventaModelo = new Venta();
$detalleVentaModelo = new DetalleVenta();
$ordenModelo = new OrdenServicio();
$errores = [];
$accion = (string) get('accion', 'listar');
$idDetalleVenta = (int) (get('detalle_venta', 0) ?: post('id_detalle_venta', 0));
$idOrden = (int) (get('orden', 0) ?: post('id_orden', 0));

$cargarOrigen = static function (int $idDetalleVenta, int $idOrden) use (
    $detalleVentaModelo,
    $ventaModelo,
    $ordenModelo
): array {
    if (($idDetalleVenta > 0) === ($idOrden > 0)) {
        throw new DomainException('Seleccione un detalle de venta o una orden de servicio.');
    }

    if ($idDetalleVenta > 0) {
        $detalle = $detalleVentaModelo->porIdConProducto($idDetalleVenta);
        if (!$detalle) {
            throw new DomainException('El detalle de venta indicado no existe.');
        }
        $venta = $ventaModelo->porId((int) $detalle['id_venta']);
        if (!$venta || $venta['estado'] !== 'Confirmada') {
            throw new DomainException('La garantía debe corresponder a una venta confirmada.');
        }
        return [
            'tipo' => 'Producto',
            'detalle_venta' => $detalle,
            'orden' => null,
            'fecha_inicio' => substr((string) $detalle['fecha_venta'], 0, 10),
        ];
    }

    $orden = $ordenModelo->porIdConRelaciones($idOrden);
    if (!$orden) {
        throw new DomainException('La orden de servicio indicada no existe.');
    }
    if (!in_array($orden['estado'], ['Terminado', 'Entregado'], true)) {
        throw new DomainException('Solo se puede garantizar una orden terminada o entregada.');
    }
    return [
        'tipo' => 'Servicio',
        'detalle_venta' => null,
        'orden' => $orden,
        'fecha_inicio' => substr(
            (string) ($orden['fecha_entrega'] ?: date('Y-m-d')),
            0,
            10
        ),
    ];
};

if (es_post()) {
    $operacion = (string) post('operacion');

    if ($operacion === 'anular') {
        requiere_escritura('garantias');
        try {
            $garantiaModelo->anular((int) post('id_garantia'));
            flash('success', 'La garantía fue anulada correctamente.');
        } catch (DomainException $e) {
            flash('danger', $e->getMessage());
        }
        redirect('garantias');
    }

    if ($operacion === 'registrar') {
        requiere_escritura('garantias');
        $accion = 'nuevo';
        $formulario = [
            'fecha_inicio' => texto_seguro(post('fecha_inicio'), 10),
            'fecha_fin' => texto_seguro(post('fecha_fin'), 10),
            'descripcion' => texto_seguro(post('descripcion'), 255),
            'condiciones' => texto_seguro(post('condiciones'), 2000),
        ];

        try {
            $origen = $cargarOrigen($idDetalleVenta, $idOrden);
            if ($garantiaModelo->existePorOrigen($idDetalleVenta, $idOrden)) {
                throw new DomainException('Este origen ya tiene una garantía registrada.');
            }
            if (!fecha_valida($formulario['fecha_inicio']) || !fecha_valida($formulario['fecha_fin'])) {
                $errores[] = 'Las fechas de la garantía no son válidas.';
            } elseif ($formulario['fecha_fin'] < $formulario['fecha_inicio']) {
                $errores[] = 'La fecha de vencimiento no puede ser anterior a la fecha de inicio.';
            }
            if ($formulario['descripcion'] === '') {
                $errores[] = 'Describa el alcance de la garantía.';
            }

            if ($errores === []) {
                $garantiaModelo->crear(
                    $idDetalleVenta > 0 ? $idDetalleVenta : null,
                    $idOrden > 0 ? $idOrden : null,
                    $formulario['fecha_inicio'],
                    $formulario['fecha_fin'],
                    $origen['tipo'],
                    $formulario['descripcion'],
                    $formulario['condiciones'] !== '' ? $formulario['condiciones'] : null
                );
                flash('success', 'La garantía fue registrada correctamente.');
                redirect('garantias');
            }
        } catch (DomainException $e) {
            $errores[] = $e->getMessage();
        } catch (Throwable $e) {
            error_log('[Alex@sys] Error al registrar garantía: ' . $e->getMessage());
            $errores[] = 'No se pudo registrar la garantía.';
        }
    }
}

if ($accion === 'nuevo') {
    requiere_escritura('garantias');
    try {
        $origen = $cargarOrigen($idDetalleVenta, $idOrden);
    } catch (DomainException $e) {
        flash('danger', $e->getMessage());
        redirect('garantias');
    }

    if ($garantiaModelo->existePorOrigen($idDetalleVenta, $idOrden)) {
        flash('warning', 'Este origen ya tiene una garantía registrada.');
        redirect('garantias');
    }

    $claveDias = $origen['tipo'] === 'Producto'
        ? 'seguridad.dias_garantia_producto'
        : 'seguridad.dias_garantia_servicio';
    $dias = max(0, (int) config($claveDias, $origen['tipo'] === 'Producto' ? 365 : 60));
    $fechaInicio = $origen['fecha_inicio'];
    $formulario = $formulario ?? [
        'fecha_inicio' => $fechaInicio,
        'fecha_fin' => date('Y-m-d', strtotime($fechaInicio . ' +' . $dias . ' days')),
        'descripcion' => '',
        'condiciones' => '',
    ];

    renderizar('garantia-nueva', [
        'titulo' => 'Registrar garantía',
        'subtitulo' => 'Garantía de ' . strtolower($origen['tipo']),
        'origen' => $origen,
        'idDetalleVenta' => $idDetalleVenta,
        'idOrden' => $idOrden,
        'formulario' => $formulario,
        'errores' => $errores,
    ]);
    return;
}

$estado = (string) get('estado', '');
$tipo = (string) get('tipo', '');
$busqueda = texto_seguro(get('q', ''), 80);
if (!in_array($estado, ['Vigente', 'Vencida', 'Anulada'], true)) {
    $estado = '';
}
if (!in_array($tipo, ['Producto', 'Servicio'], true)) {
    $tipo = '';
}

renderizar('garantias', [
    'titulo' => 'Garantías',
    'subtitulo' => 'Garantías de productos vendidos y servicios técnicos',
    'garantias' => $garantiaModelo->listar($estado, $tipo, $busqueda),
    'estado' => $estado,
    'tipo' => $tipo,
    'busqueda' => $busqueda,
]);
