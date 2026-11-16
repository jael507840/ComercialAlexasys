<?php
/**
 * Front controller de Alex@sys.
 * Todas las solicitudes pasan por aquí: index.php?ruta=modulo
 */

require __DIR__ . '/Core/bootstrap.php';

$rutasPermitidas = [
    'index', 'login', 'logout', 'registrar-usuario', 'actualizar-usuario',
    'clientes', 'productos', 'categorias', 'proveedores', 'compras',
    'ventas', 'pagos-venta', 'inventario', 'equipos', 'ordenes-servicio',
    'detalle-servicio', 'repuestos-servicio', 'pagos-servicio', 'garantias',
    'reportes', 'consultas-contacto',
];

$ruta = (string) ($_GET['ruta'] ?? 'index');
if (!preg_match('/^[a-z0-9-]+$/', $ruta) || !in_array($ruta, $rutasPermitidas, true)) {
    $ruta = 'index';
}

require __DIR__ . '/Controllers/' . $ruta . '.php';
