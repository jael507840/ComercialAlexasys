<?php
/**
 * Módulo 14: repuestos utilizados en servicio técnico.
 * Cada repuesto descuenta inventario con un movimiento de Salida y su
 * costo se toma de la última compra registrada del producto.
 */

requiere_permiso('repuestos-servicio');
csrf_verificar();

$repuestoModelo = new RepuestoServicio();
$ordenModelo = new OrdenServicio();
$productoModelo = new Producto();

$errores = [];
$accion = (string) get('accion', 'listar');

$formulario = [
    'id_orden'    => (int) (get('orden', 0) ?: post('id_orden', 0)),
    'id_producto' => (int) post('id_producto', 0),
    'cantidad'    => '1',
];

if (es_post()) {
    $operacion = (string) post('operacion');

    if ($operacion === 'registrar') {
        requiere_escritura('repuestos-servicio');
        $accion = 'nuevo';

        $formulario = [
            'id_orden'    => (int) post('id_orden'),
            'id_producto' => (int) post('id_producto'),
            'cantidad'    => trim((string) post('cantidad')),
        ];

        $orden = $formulario['id_orden'] > 0 ? $ordenModelo->porId($formulario['id_orden']) : null;
        if (!$orden) {
            $errores[] = 'Seleccione una orden de servicio válida.';
        } elseif (in_array($orden['estado'], ['Entregado', 'Anulado'], true)) {
            $errores[] = 'No se pueden agregar repuestos a una orden entregada o anulada.';
        }

        $producto = $formulario['id_producto'] > 0 ? $productoModelo->porId($formulario['id_producto']) : null;
        if (!$producto) {
            $errores[] = 'Seleccione un producto válido como repuesto.';
        } elseif ($producto['estado'] !== 'Activo') {
            $errores[] = 'El producto seleccionado está inactivo.';
        }

        if (!preg_match('/^\d+$/', $formulario['cantidad']) || (int) $formulario['cantidad'] <= 0) {
            $errores[] = 'La cantidad del repuesto debe ser un entero mayor que cero.';
        } elseif ($producto && (int) $formulario['cantidad'] > (int) $producto['stock_actual']) {
            $errores[] = 'Stock insuficiente de "' . $producto['nombre'] . '". Disponible: '
                . (int) $producto['stock_actual'] . '.';
        }

        if ($errores === []) {
            try {
                $repuestoModelo->registrar(
                    $formulario['id_orden'],
                    $formulario['id_producto'],
                    (int) $formulario['cantidad'],
                    (int) usuario_actual()['id_usuario']
                );
                flash('success', 'Repuesto registrado y stock descontado correctamente.');
                redirect('ordenes-servicio', ['accion' => 'ver', 'id' => $formulario['id_orden']]);
            } catch (DomainException $e) {
                $errores[] = $e->getMessage();
            } catch (Throwable $e) {
                error_log('[Alex@sys] Error al registrar repuesto: ' . $e->getMessage());
                $errores[] = 'No se pudo registrar el repuesto. La operación fue revertida.';
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

    renderizar('repuesto-nuevo', [
        'titulo'            => 'Registrar repuesto',
        'subtitulo'         => 'Consumo de inventario para una orden de servicio',
        'ordenes'           => $ordenes,
        'ordenSeleccionada' => $ordenSeleccionada,
        'productos'         => $productoModelo->activos(),
        'formulario'        => $formulario,
        'errores'           => $errores,
    ]);
    return;
}

$desde = texto_seguro(get('desde', ''), 10);
$hasta = texto_seguro(get('hasta', ''), 10);
$repuestos = $repuestoModelo->listar($desde ?: null, $hasta ?: null);

$totalRepuestos = 0.0;
foreach ($repuestos as $repuesto) {
    $totalRepuestos += (float) $repuesto['subtotal'];
}

renderizar('repuestos-servicio', [
    'titulo'         => 'Repuestos usados',
    'subtitulo'      => 'Consumo de inventario en órdenes de servicio técnico',
    'repuestos'      => $repuestos,
    'desde'          => $desde,
    'hasta'          => $hasta,
    'totalRepuestos' => $totalRepuestos,
]);
