<?php
/**
 * Módulo 8: inventario.
 * Historial de movimientos, filtros, ajustes manuales y verificación
 * de que el stock coincida con la suma de entradas y salidas.
 */

requiere_permiso('inventario');
csrf_verificar();

$movimientoModelo = new MovimientoInventario();
$productoModelo = new Producto();
$errores = [];
$accion = (string) get('accion', 'listar');

if (es_post()) {
    $operacion = (string) post('operacion');

    if ($operacion === 'ajustar') {
        requiere_escritura('inventario');
        $idProducto = (int) post('id_producto');
        $nuevoStock = post('nuevo_stock');
        $observacion = texto_seguro(post('observacion'), 300);

        $producto = $productoModelo->porId($idProducto);
        if (!$producto) {
            $errores[] = 'Seleccione un producto válido para el ajuste.';
        }
        if ($nuevoStock === '' || !preg_match('/^\d+$/', (string) $nuevoStock)) {
            $errores[] = 'El nuevo stock debe ser un número entero mayor o igual a cero.';
        }
        if ($observacion === '') {
            $errores[] = 'Debe indicar el motivo del ajuste de inventario.';
        }

        if ($errores === []) {
            try {
                $movimientoModelo->ajustar(
                    $idProducto,
                    (int) $nuevoStock,
                    (int) usuario_actual()['id_usuario'],
                    $observacion
                );
                flash('success', 'Ajuste de inventario registrado correctamente.');
                redirect('inventario', ['producto' => $idProducto]);
            } catch (DomainException $e) {
                $errores[] = $e->getMessage();
            } catch (Throwable $e) {
                error_log('[Alex@sys] Error al ajustar inventario: ' . $e->getMessage());
                $errores[] = 'No se pudo registrar el ajuste. La operación fue revertida.';
            }
        }
    }
}

$idProducto = (int) get('producto', 0);
$tipo = (string) get('tipo', '');
$referencia = (string) get('referencia', '');
$desde = texto_seguro(get('desde', ''), 10);
$hasta = texto_seguro(get('hasta', ''), 10);

if (!in_array($tipo, ['Entrada', 'Salida'], true)) {
    $tipo = '';
}
if (!in_array($referencia, MovimientoInventario::REFERENCIAS, true)) {
    $referencia = '';
}

$movimientos = $movimientoModelo->listar(
    $idProducto,
    $tipo,
    $referencia,
    $desde ?: null,
    $hasta ?: null
);

$verificacion = $movimientoModelo->verificacionStock();
$descuadres = array_values(array_filter(
    $verificacion,
    static fn (array $fila): bool => (int) $fila['diferencia'] !== 0
));

renderizar('inventario', [
    'titulo'        => 'Inventario',
    'subtitulo'     => 'Movimientos, existencias y control de consistencia',
    'movimientos'   => $movimientos,
    'productos'     => $productoModelo->listar(),
    'idProducto'    => $idProducto,
    'tipo'          => $tipo,
    'referencia'    => $referencia,
    'desde'         => $desde,
    'hasta'         => $hasta,
    'resumen'       => $movimientoModelo->resumen($desde ?: null, $hasta ?: null),
    'verificacion'  => $verificacion,
    'descuadres'    => $descuadres,
    'errores'       => $errores,
]);
