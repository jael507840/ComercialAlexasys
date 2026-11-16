<?php
/**
 * Módulo 7: compras.
 * El registro de una compra genera, dentro de una sola transacción,
 * el documento, su detalle y una entrada de inventario por producto.
 */

requiere_permiso('compras');
csrf_verificar();

$compraModelo = new Compra();
$proveedorModelo = new Proveedor();
$productoModelo = new Producto();

$errores = [];
$accion = (string) get('accion', 'listar');

$formulario = [
    'id_proveedor'       => (int) get('proveedor', 0),
    'fecha_compra'       => date('Y-m-d'),
    'numero_comprobante' => '',
    'observacion'        => '',
];

// Líneas propuestas por defecto (el JS permite agregar más).
$lineas = [
    ['id_producto' => 0, 'cantidad' => 1, 'costo_unitario' => '0.00'],
];

if (es_post()) {
    $operacion = (string) post('operacion');

    if ($operacion === 'registrar') {
        requiere_escritura('compras');
        $accion = 'nuevo';

        $formulario = [
            'id_proveedor'       => (int) post('id_proveedor'),
            'fecha_compra'       => texto_seguro(post('fecha_compra'), 10),
            'numero_comprobante' => texto_seguro(post('numero_comprobante'), 30),
            'observacion'        => texto_seguro(post('observacion'), 500),
        ];

        $proveedor = $formulario['id_proveedor'] > 0
            ? $proveedorModelo->porId($formulario['id_proveedor'])
            : null;
        if (!$proveedor) {
            $errores[] = 'Seleccione un proveedor válido.';
        } elseif ($proveedor['estado'] !== 'Activo') {
            $errores[] = 'El proveedor seleccionado está inactivo; no se pueden registrar compras.';
        }

        $fecha = DateTime::createFromFormat('Y-m-d', $formulario['fecha_compra']);
        if (!$fecha || $fecha->format('Y-m-d') !== $formulario['fecha_compra']) {
            $errores[] = 'La fecha de la compra no es válida.';
        } elseif ($formulario['fecha_compra'] > date('Y-m-d')) {
            $errores[] = 'La fecha de la compra no puede ser posterior a la fecha actual.';
        }

        if ($formulario['numero_comprobante'] !== ''
            && !preg_match('/^[A-Za-z0-9._\/-]{2,30}$/', $formulario['numero_comprobante'])) {
            $errores[] = 'El número de comprobante contiene caracteres no permitidos (2 a 30).';
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
                $costo = (string) ($fila['costo_unitario'] ?? '');
                if ($idProducto === 0 && $cantidad === 0 && $costo === '') {
                    continue;
                }

                $lineas[] = [
                    'id_producto'    => $idProducto,
                    'cantidad'       => (int) ($fila['cantidad'] ?? 0),
                    'costo_unitario' => $costo,
                ];

                if ($idProducto <= 0) {
                    $errores[] = 'Una de las líneas no tiene producto seleccionado.';
                    continue;
                }
                if (isset($productosVistos[$idProducto])) {
                    $errores[] = 'Un producto está repetido en la compra; consolide la cantidad en una sola línea.';
                    continue;
                }
                $productosVistos[$idProducto] = true;

                $producto = $productoModelo->porId($idProducto);
                if (!$producto) {
                    $errores[] = 'Uno de los productos seleccionados no existe.';
                    continue;
                }
                if ($producto['estado'] !== 'Activo') {
                    $errores[] = 'El producto "' . $producto['nombre'] . '" está inactivo y no puede comprarse.';
                    continue;
                }
                if ($cantidad <= 0) {
                    $errores[] = 'La cantidad de "' . $producto['nombre'] . '" debe ser mayor que cero.';
                    continue;
                }
                if ($costo === '' || !numero_no_negativo($costo)) {
                    $errores[] = 'El costo unitario de "' . $producto['nombre'] . '" no es válido.';
                    continue;
                }

                $items[] = [
                    'id_producto'    => $idProducto,
                    'cantidad'       => $cantidad,
                    'costo_unitario' => round((float) $costo, 2),
                ];
            }
        }

        if ($lineas === []) {
            $errores[] = 'Debe agregar al menos una línea de producto a la compra.';
        }

        if ($errores === []) {
            try {
                $idCompra = $compraModelo->registrar(
                    $formulario['id_proveedor'],
                    (int) usuario_actual()['id_usuario'],
                    $formulario['fecha_compra'] . ' ' . date('H:i:s'),
                    $formulario['numero_comprobante'] !== '' ? $formulario['numero_comprobante'] : null,
                    $formulario['observacion'] !== '' ? $formulario['observacion'] : null,
                    $items
                );
                flash('success', 'Compra registrada. Se actualizó el stock de los productos incluidos.');
                redirect('compras', ['accion' => 'ver', 'id' => $idCompra]);
            } catch (DomainException $e) {
                $errores[] = $e->getMessage();
            } catch (Throwable $e) {
                error_log('[Alex@sys] Error al registrar compra: ' . $e->getMessage());
                $errores[] = 'No se pudo registrar la compra. La operación fue revertida en su totalidad.';
            }
        }

        if ($lineas === []) {
            $lineas = [['id_producto' => 0, 'cantidad' => 1, 'costo_unitario' => '0.00']];
        }
    }
}

if ($accion === 'ver') {
    $id = (int) get('id', 0);
    $compra = $compraModelo->porIdConRelaciones($id);
    if (!$compra) {
        flash('danger', 'La compra solicitada no existe.');
        redirect('compras');
    }
    renderizar('compra-detalle', [
        'titulo'    => 'Compra ' . $compra['numero_comprobante'],
        'subtitulo' => 'Detalle de la compra y entradas de inventario generadas',
        'compra'    => $compra,
        'detalle'   => $compraModelo->detalle($id),
        'movimientos' => $compraModelo->movimientos($id),
    ]);
    return;
}

if ($accion === 'nuevo') {
    renderizar('compra-nueva', [
        'titulo'      => 'Registrar compra',
        'subtitulo'   => 'Ingreso de mercadería al inventario',
        'proveedores' => $proveedorModelo->activos(),
        'productos'   => $productoModelo->activos(),
        'formulario'  => $formulario,
        'lineas'      => $lineas,
        'errores'     => $errores,
    ]);
    return;
}

$desde = texto_seguro(get('desde', ''), 10);
$hasta = texto_seguro(get('hasta', ''), 10);
$idProveedor = (int) get('proveedor', 0);
$busqueda = texto_seguro(get('q', ''), 80);

renderizar('compras', [
    'titulo'        => 'Compras',
    'subtitulo'     => 'Adquisiciones de mercadería y su efecto en el inventario',
    'compras'       => $compraModelo->listar($desde ?: null, $hasta ?: null, $idProveedor, $busqueda),
    'proveedores'   => $proveedorModelo->listar(),
    'desde'         => $desde,
    'hasta'         => $hasta,
    'idProveedor'   => $idProveedor,
    'busqueda'      => $busqueda,
    'totalPeriodo'  => $compraModelo->totalPeriodo($desde ?: null, $hasta ?: null),
    'cantidadPeriodo' => $compraModelo->contarPeriodo($desde ?: null, $hasta ?: null),
]);
