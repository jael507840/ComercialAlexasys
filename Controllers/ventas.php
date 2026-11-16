<?php
/**
 * Módulo 9: ventas.
 * Registro de ventas con descuento por línea y descuento global.
 * El stock se descuenta mediante movimientos de inventario dentro de
 * la misma transacción que crea la venta.
 */

requiere_permiso('ventas');
csrf_verificar();

$ventaModelo = new Venta();
$clienteModelo = new Cliente();
$productoModelo = new Producto();
$pagoModelo = new PagoVenta();

$errores = [];
$accion = (string) get('accion', 'listar');

$formulario = [
    'id_cliente'         => (int) get('cliente', 0),
    'fecha_venta'        => date('Y-m-d'),
    'numero_comprobante' => '',
    'descuento'          => '0.00',
    'observacion'        => '',
];

$lineas = [
    ['id_producto' => 0, 'cantidad' => 1, 'precio_unitario' => '0.00', 'descuento' => '0.00'],
];

if (es_post()) {
    $operacion = (string) post('operacion');

    if ($operacion === 'registrar') {
        requiere_escritura('ventas');
        $accion = 'nuevo';

        $formulario = [
            'id_cliente'         => (int) post('id_cliente'),
            'fecha_venta'        => texto_seguro(post('fecha_venta'), 10),
            'numero_comprobante' => texto_seguro(post('numero_comprobante'), 30),
            'descuento'          => (string) post('descuento'),
            'observacion'        => texto_seguro(post('observacion'), 500),
        ];

        $cliente = $formulario['id_cliente'] > 0
            ? $clienteModelo->porId($formulario['id_cliente'])
            : null;
        if (!$cliente) {
            $errores[] = 'Seleccione un cliente válido.';
        } elseif ($cliente['estado'] !== 'Activo') {
            $errores[] = 'El cliente seleccionado está inactivo; no se pueden registrar ventas.';
        }

        $fecha = DateTime::createFromFormat('Y-m-d', $formulario['fecha_venta']);
        if (!$fecha || $fecha->format('Y-m-d') !== $formulario['fecha_venta']) {
            $errores[] = 'La fecha de la venta no es válida.';
        } elseif ($formulario['fecha_venta'] > date('Y-m-d')) {
            $errores[] = 'La fecha de la venta no puede ser posterior a la fecha actual.';
        }

        if ($formulario['numero_comprobante'] !== ''
            && !preg_match('/^[A-Za-z0-9._\/-]{2,30}$/', $formulario['numero_comprobante'])) {
            $errores[] = 'El número de comprobante contiene caracteres no permitidos (2 a 30).';
        }

        $descuentoGlobal = 0.0;
        if (!numero_no_negativo($formulario['descuento'])) {
            $errores[] = 'El descuento general no es válido.';
        } else {
            $descuentoGlobal = round((float) $formulario['descuento'], 2);
        }

        $lineas = [];
        $items = [];
        $productosVistos = [];
        $entrada = $_POST['items'] ?? [];
        if (is_array($entrada)) {
            foreach ($entrada as $fila) {
                if (!is_array($fila)) {
                    continue;
                }
                $idProducto = (int) ($fila['id_producto'] ?? 0);
                $cantidad = (int) ($fila['cantidad'] ?? 0);
                $precio = (string) ($fila['precio_unitario'] ?? '');
                $descuentoLinea = (string) ($fila['descuento'] ?? '0');

                if ($idProducto === 0 && $cantidad === 0 && $precio === '') {
                    continue;
                }

                $lineas[] = [
                    'id_producto'      => $idProducto,
                    'cantidad'         => (int) ($fila['cantidad'] ?? 0),
                    'precio_unitario'  => $precio,
                    'descuento'        => $descuentoLinea,
                ];

                if ($idProducto <= 0) {
                    $errores[] = 'Una de las líneas no tiene producto seleccionado.';
                    continue;
                }
                if (isset($productosVistos[$idProducto])) {
                    $errores[] = 'Un producto está repetido en la venta; consolide la cantidad en una sola línea.';
                    continue;
                }
                $productosVistos[$idProducto] = true;

                $producto = $productoModelo->porId($idProducto);
                if (!$producto) {
                    $errores[] = 'Uno de los productos seleccionados no existe.';
                    continue;
                }
                if ($producto['estado'] !== 'Activo') {
                    $errores[] = 'El producto "' . $producto['nombre'] . '" está inactivo y no puede venderse.';
                    continue;
                }
                if ($cantidad <= 0) {
                    $errores[] = 'La cantidad de "' . $producto['nombre'] . '" debe ser mayor que cero.';
                    continue;
                }
                if ($cantidad > (int) $producto['stock_actual']) {
                    $errores[] = 'Stock insuficiente de "' . $producto['nombre'] . '". Disponible: '
                        . (int) $producto['stock_actual'] . '.';
                    continue;
                }
                if ($precio === '' || !numero_no_negativo($precio)) {
                    $errores[] = 'El precio unitario de "' . $producto['nombre'] . '" no es válido.';
                    continue;
                }
                if (!numero_no_negativo($descuentoLinea)) {
                    $errores[] = 'El descuento de "' . $producto['nombre'] . '" no es válido.';
                    continue;
                }
                if ((float) $descuentoLinea > round($cantidad * (float) $precio, 2)) {
                    $errores[] = 'El descuento de "' . $producto['nombre'] . '" supera el importe bruto de la línea.';
                    continue;
                }

                $items[] = [
                    'id_producto'     => $idProducto,
                    'cantidad'        => $cantidad,
                    'precio_unitario' => round((float) $precio, 2),
                    'descuento'       => round((float) $descuentoLinea, 2),
                ];
            }
        }

        if ($lineas === []) {
            $errores[] = 'Debe agregar al menos una línea de producto a la venta.';
        }

        if ($errores === []) {
            try {
                $idVenta = $ventaModelo->registrar(
                    $formulario['id_cliente'],
                    (int) usuario_actual()['id_usuario'],
                    $formulario['fecha_venta'] . ' ' . date('H:i:s'),
                    $formulario['numero_comprobante'] !== '' ? $formulario['numero_comprobante'] : null,
                    $descuentoGlobal,
                    $formulario['observacion'] !== '' ? $formulario['observacion'] : null,
                    $items
                );
                flash('success', 'Venta registrada y stock descontado correctamente.');
                redirect('ventas', ['accion' => 'ver', 'id' => $idVenta]);
            } catch (DomainException $e) {
                $errores[] = $e->getMessage();
            } catch (Throwable $e) {
                error_log('[Alex@sys] Error al registrar venta: ' . $e->getMessage());
                $errores[] = 'No se pudo registrar la venta. La operación fue revertida en su totalidad.';
            }
        }

        if ($lineas === []) {
            $lineas = [['id_producto' => 0, 'cantidad' => 1, 'precio_unitario' => '0.00', 'descuento' => '0.00']];
        }
    }
}

if ($accion === 'ver') {
    $id = (int) get('id', 0);
    $venta = $ventaModelo->porIdConRelaciones($id);
    if (!$venta) {
        flash('danger', 'La venta solicitada no existe.');
        redirect('ventas');
    }
    renderizar('venta-detalle', [
        'titulo'     => 'Venta ' . $venta['numero_comprobante'],
        'subtitulo'  => 'Detalle de productos, pagos y estado de cobro',
        'venta'      => $venta,
        'detalle'    => $ventaModelo->detalle($id),
        'pagos'      => $pagoModelo->porVenta($id),
        'estadoPago' => $ventaModelo->estadoPago($id),
        'movimientos' => $ventaModelo->movimientos($id),
    ]);
    return;
}

if ($accion === 'nuevo') {
    renderizar('venta-nueva', [
        'titulo'     => 'Registrar venta',
        'subtitulo'  => 'Venta de productos con control de stock',
        'clientes'   => $clienteModelo->listar(),
        'productos'  => $productoModelo->disponibles(),
        'formulario' => $formulario,
        'lineas'     => $lineas,
        'errores'    => $errores,
    ]);
    return;
}

$desde = texto_seguro(get('desde', ''), 10);
$hasta = texto_seguro(get('hasta', ''), 10);
$busqueda = texto_seguro(get('q', ''), 80);
$estado = (string) get('estado', '');
$ventas = $ventaModelo->listar($desde ?: null, $hasta ?: null, $busqueda, $estado);

$montoTotal = 0.0;
$montoPagado = 0.0;
$saldoTotal = 0.0;
foreach ($ventas as $venta) {
    if ($venta['estado'] === 'Confirmada') {
        $montoTotal += (float) $venta['total'];
        $montoPagado += (float) $venta['pagado'];
        $saldoTotal += max(0, (float) $venta['saldo']);
    }
}

renderizar('ventas', [
    'titulo'      => 'Ventas',
    'subtitulo'   => 'Comprobantes internos, cobros y saldos pendientes',
    'ventas'      => $ventas,
    'desde'       => $desde,
    'hasta'       => $hasta,
    'busqueda'    => $busqueda,
    'estado'      => $estado,
    'montoTotal'  => $montoTotal,
    'montoPagado' => $montoPagado,
    'saldoTotal'  => $saldoTotal,
]);
