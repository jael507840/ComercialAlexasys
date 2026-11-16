<?php
/**
 * Módulo 12: órdenes de servicio técnico.
 * Recepción del equipo, asignación de técnico, estado y costos.
 * Las intervenciones, repuestos, pagos y garantías se gestionan en sus
 * propios módulos y se resumen en el detalle de la orden.
 */

requiere_permiso('ordenes-servicio');
csrf_verificar();

$ordenModelo = new OrdenServicio();
$equipoModelo = new EquipoCliente();
$usuarioModelo = new Usuario();

$errores = [];
$accion = (string) get('accion', 'listar');
$id = (int) (get('id', 0) ?: post('id', 0));

$formulario = [
    'id_equipo'         => (int) get('equipo', 0),
    'fecha_recepcion'   => date('Y-m-d\TH:i'),
    'falla_reportada'   => '',
    'accesorios'        => '',
    'id_usuario_tecnico' => 0,
    'costo_servicio'    => '0.00',
    'observaciones'     => '',
];

if (es_post()) {
    $operacion = (string) post('operacion');

    if ($operacion === 'crear') {
        requiere_escritura('ordenes-servicio');
        $accion = 'nuevo';

        $formulario = [
            'id_equipo'          => (int) post('id_equipo'),
            'fecha_recepcion'    => trim((string) post('fecha_recepcion')),
            'falla_reportada'    => texto_seguro(post('falla_reportada'), 2000),
            'accesorios'         => texto_seguro(post('accesorios'), 255),
            'id_usuario_tecnico' => (int) post('id_usuario_tecnico'),
            'costo_servicio'     => trim((string) post('costo_servicio', '0')),
            'observaciones'      => texto_seguro(post('observaciones'), 2000),
        ];

        $equipo = $formulario['id_equipo'] > 0 ? $equipoModelo->porIdConRelaciones($formulario['id_equipo']) : null;
        if (!$equipo) {
            $errores[] = 'Seleccione un equipo válido para la orden.';
        }
        if (!fecha_hora_valida($formulario['fecha_recepcion'])) {
            $errores[] = 'La fecha de recepción no es válida.';
        } elseif (strtotime(str_replace('T', ' ', $formulario['fecha_recepcion'])) > time() + 300) {
            $errores[] = 'La fecha de recepción no puede ser posterior a la actual.';
        }
        if ($formulario['falla_reportada'] === '') {
            $errores[] = 'Registre la falla reportada por el cliente.';
        }
        if (!numero_no_negativo($formulario['costo_servicio'])) {
            $errores[] = 'El costo de servicio no es válido.';
        }
        if ($formulario['id_usuario_tecnico'] > 0) {
            $idsTecnicos = array_map(
                static fn (array $t): int => (int) $t['id_usuario'],
                $usuarioModelo->tecnicos()
            );
            if (!in_array($formulario['id_usuario_tecnico'], $idsTecnicos, true)) {
                $errores[] = 'El técnico seleccionado no es válido o no está activo.';
            }
        }

        if ($errores === []) {
            try {
                $idOrden = $ordenModelo->crear(
                    $formulario['id_equipo'],
                    (int) usuario_actual()['id_usuario'],
                    $formulario['id_usuario_tecnico'] > 0 ? $formulario['id_usuario_tecnico'] : null,
                    $formulario['falla_reportada'],
                    $formulario['accesorios'] !== '' ? $formulario['accesorios'] : null,
                    str_replace('T', ' ', $formulario['fecha_recepcion']) . ':00',
                    (float) $formulario['costo_servicio'],
                    $formulario['observaciones'] !== '' ? $formulario['observaciones'] : null
                );
                flash('success', 'Orden de servicio registrada correctamente.');
                redirect('ordenes-servicio', ['accion' => 'ver', 'id' => $idOrden]);
            } catch (DomainException $e) {
                $errores[] = $e->getMessage();
            } catch (Throwable $e) {
                error_log('[Alex@sys] Error al registrar orden: ' . $e->getMessage());
                $errores[] = 'No se pudo registrar la orden de servicio. La operación fue revertida.';
            }
        }
    }

    if ($operacion === 'actualizar') {
        requiere_escritura('ordenes-servicio');
        $ordenActual = $id > 0 ? $ordenModelo->porId($id) : null;
        if (!$ordenActual) {
            flash('danger', 'La orden solicitada no existe.');
            redirect('ordenes-servicio');
        }

        $estado = (string) post('estado');
        $tecnico = (int) post('id_usuario_tecnico');
        $costo = trim((string) post('costo_servicio', (string) $ordenActual['costo_servicio']));
        $observaciones = texto_seguro(post('observaciones'), 2000);

        if (!in_array($estado, OrdenServicio::ESTADOS, true)) {
            $errores[] = 'Seleccione un estado válido para la orden.';
        }
        if (!numero_no_negativo($costo)) {
            $errores[] = 'El costo de servicio no es válido.';
        }
        if ($tecnico > 0) {
            $idsTecnicos = array_map(
                static fn (array $t): int => (int) $t['id_usuario'],
                $usuarioModelo->tecnicos()
            );
            if (!in_array($tecnico, $idsTecnicos, true)) {
                $errores[] = 'El técnico seleccionado no es válido o no está activo.';
            }
        }

        if ($errores === []) {
            try {
                $ordenModelo->actualizar($id, [
                    'estado'             => $estado,
                    'id_usuario_tecnico' => $tecnico > 0 ? $tecnico : null,
                    'costo_servicio'     => (float) $costo,
                    'observaciones'      => $observaciones,
                ]);
                flash('success', 'Orden de servicio actualizada correctamente.');
                redirect('ordenes-servicio', ['accion' => 'ver', 'id' => $id]);
            } catch (DomainException $e) {
                $errores[] = $e->getMessage();
            } catch (Throwable $e) {
                error_log('[Alex@sys] Error al actualizar orden: ' . $e->getMessage());
                $errores[] = 'No se pudo actualizar la orden de servicio.';
            }
        }
    }
}

if ($accion === 'ver') {
    $orden = $ordenModelo->porIdConRelaciones($id);
    if (!$orden) {
        flash('danger', 'La orden de servicio solicitada no existe.');
        redirect('ordenes-servicio');
    }
    renderizar('orden-servicio-detalle', [
        'titulo'         => 'Orden ' . $orden['numero_orden'],
        'subtitulo'      => 'Recepción, intervenciones, repuestos y cobro del servicio',
        'orden'          => $orden,
        'intervenciones' => (new DetalleServicio())->porOrden($id),
        'repuestos'      => (new RepuestoServicio())->porOrden($id),
        'pagos'          => (new PagoServicio())->porOrden($id),
        'garantias'      => (new Garantia())->porOrden($id),
        'estadoPago'     => $ordenModelo->estadoPago($id),
        'tecnicos'       => $usuarioModelo->tecnicos(),
        'errores'        => $errores,
    ]);
    return;
}

if ($accion === 'nuevo') {
    renderizar('orden-servicio-nueva', [
        'titulo'     => 'Nueva orden de servicio',
        'subtitulo'  => 'Recepción de equipo para diagnóstico y reparación',
        'equipos'    => $equipoModelo->listar(),
        'tecnicos'   => $usuarioModelo->tecnicos(),
        'formulario' => $formulario,
        'errores'    => $errores,
    ]);
    return;
}

$busqueda = texto_seguro(get('q', ''), 80);
$estado = (string) get('estado', '');
$idTecnico = (int) get('tecnico', 0);
$desde = texto_seguro(get('desde', ''), 10);
$hasta = texto_seguro(get('hasta', ''), 10);

renderizar('ordenes-servicio', [
    'titulo'     => 'Órdenes de servicio',
    'subtitulo'  => 'Servicio técnico: recepción, reparación y entrega de equipos',
    'ordenes'    => $ordenModelo->listar($busqueda, $estado, $idTecnico, $desde ?: null, $hasta ?: null),
    'tecnicos'   => $usuarioModelo->tecnicos(),
    'busqueda'   => $busqueda,
    'estado'     => $estado,
    'idTecnico'  => $idTecnico,
    'desde'      => $desde,
    'hasta'      => $hasta,
    'resumen'    => $ordenModelo->resumen($desde ?: null, $hasta ?: null),
]);
