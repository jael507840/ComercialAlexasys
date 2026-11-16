<?php
/**
 * Módulo 13: intervenciones de servicio técnico.
 * Cada intervención registra diagnóstico, trabajo y costo; el costo de
 * mano de obra de la orden se recalcula automáticamente.
 */

requiere_permiso('detalle-servicio');
csrf_verificar();

$detalleModelo = new DetalleServicio();
$ordenModelo = new OrdenServicio();
$usuarioModelo = new Usuario();

$errores = [];
$accion = (string) get('accion', 'listar');

$formulario = [
    'id_orden'           => (int) (get('orden', 0) ?: post('id_orden', 0)),
    'id_usuario_tecnico' => (int) usuario_actual()['id_usuario'],
    'fecha_intervencion' => date('Y-m-d\TH:i'),
    'diagnostico'        => '',
    'trabajo_realizado'  => '',
    'resultado_prueba'   => '',
    'costo'              => '0.00',
];

if (es_post()) {
    $operacion = (string) post('operacion');

    if ($operacion === 'registrar') {
        requiere_escritura('detalle-servicio');
        $accion = 'nuevo';

        $formulario = [
            'id_orden'           => (int) post('id_orden'),
            'id_usuario_tecnico' => (int) post('id_usuario_tecnico'),
            'fecha_intervencion' => trim((string) post('fecha_intervencion')),
            'diagnostico'        => texto_seguro(post('diagnostico'), 2000),
            'trabajo_realizado'  => texto_seguro(post('trabajo_realizado'), 2000),
            'resultado_prueba'   => texto_seguro(post('resultado_prueba'), 255),
            'costo'              => trim((string) post('costo', '0')),
        ];

        $orden = $formulario['id_orden'] > 0 ? $ordenModelo->porId($formulario['id_orden']) : null;
        if (!$orden) {
            $errores[] = 'Seleccione una orden de servicio válida.';
        } elseif (in_array($orden['estado'], ['Entregado', 'Anulado'], true)) {
            $errores[] = 'No se pueden agregar intervenciones a una orden entregada o anulada.';
        }
        $idsTecnicos = array_map(
            static fn (array $t): int => (int) $t['id_usuario'],
            $usuarioModelo->tecnicos()
        );
        if (!in_array($formulario['id_usuario_tecnico'], $idsTecnicos, true)) {
            $errores[] = 'Seleccione un técnico activo.';
        }
        if (!fecha_hora_valida($formulario['fecha_intervencion'])) {
            $errores[] = 'La fecha de la intervención no es válida.';
        } elseif (strtotime(str_replace('T', ' ', $formulario['fecha_intervencion'])) > time() + 300) {
            $errores[] = 'La fecha de la intervención no puede ser posterior a la actual.';
        }
        if ($formulario['diagnostico'] === '') {
            $errores[] = 'El diagnóstico es obligatorio.';
        }
        if ($formulario['trabajo_realizado'] === '') {
            $errores[] = 'El trabajo realizado es obligatorio.';
        }
        if (!numero_no_negativo($formulario['costo'])) {
            $errores[] = 'El costo de la intervención no es válido.';
        }

        if ($errores === []) {
            try {
                $detalleModelo->registrar(
                    $formulario['id_orden'],
                    $formulario['id_usuario_tecnico'],
                    str_replace('T', ' ', $formulario['fecha_intervencion']) . ':00',
                    $formulario['diagnostico'],
                    $formulario['trabajo_realizado'],
                    $formulario['resultado_prueba'] !== '' ? $formulario['resultado_prueba'] : null,
                    (float) $formulario['costo']
                );
                flash('success', 'Intervención registrada. El costo de la orden se actualizó automáticamente.');
                redirect('ordenes-servicio', ['accion' => 'ver', 'id' => $formulario['id_orden']]);
            } catch (DomainException $e) {
                $errores[] = $e->getMessage();
            } catch (Throwable $e) {
                error_log('[Alex@sys] Error al registrar intervención: ' . $e->getMessage());
                $errores[] = 'No se pudo registrar la intervención. La operación fue revertida.';
            }
        }
    }
}

if ($accion === 'nuevo') {
    $ordenes = array_values(array_filter(
        $ordenModelo->listar(),
        static fn (array $o): bool => !in_array($o['estado'], ['Entregado', 'Anulado'], true)
    ));
    $ordenSeleccionada = $formulario['id_orden'] > 0 ? $ordenModelo->porIdConRelaciones($formulario['id_orden']) : null;

    renderizar('intervencion-nueva', [
        'titulo'            => 'Registrar intervención',
        'subtitulo'         => 'Diagnóstico y trabajo técnico realizado',
        'ordenes'           => $ordenes,
        'ordenSeleccionada' => $ordenSeleccionada,
        'tecnicos'          => $usuarioModelo->tecnicos(),
        'formulario'        => $formulario,
        'errores'           => $errores,
    ]);
    return;
}

$desde = texto_seguro(get('desde', ''), 10);
$hasta = texto_seguro(get('hasta', ''), 10);
$intervenciones = $detalleModelo->listar($desde ?: null, $hasta ?: null);

$totalCosto = 0.0;
foreach ($intervenciones as $intervencion) {
    $totalCosto += (float) $intervencion['costo'];
}

renderizar('detalle-servicio', [
    'titulo'         => 'Intervenciones',
    'subtitulo'      => 'Historial de diagnósticos y trabajos de servicio técnico',
    'intervenciones' => $intervenciones,
    'desde'          => $desde,
    'hasta'          => $hasta,
    'totalCosto'     => $totalCosto,
]);
