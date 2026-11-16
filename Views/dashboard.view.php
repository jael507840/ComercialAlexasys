<?php
/**
 * Panel principal.
 * Variables: $datos (Reporte::dashboard()), $perfil, $usuarioPanel.
 * El contenido se adapta al perfil: Gerente, Vendedor o Tecnico.
 */
parcial('head-admin', ['titulo' => $titulo, 'subtitulo' => $subtitulo]);

$perfil = $perfil ?? (es_gerente() ? 'Gerente' : 'Usuario');
$usuarioPanel = $usuarioPanel ?? (usuario_actual() ?? []);
$nombreUsuario = trim(($usuarioPanel['nombres'] ?? '') . ' ' . ($usuarioPanel['apellidos'] ?? ''));
$hora = (int) date('G');
$saludo = $hora < 12 ? 'Buenos días' : ($hora < 19 ? 'Buenas tardes' : 'Buenas noches');

$esGerente = $perfil === 'Gerente';
$esVendedor = $perfil === 'Vendedor';
$esTecnico = $perfil === 'Tecnico';

$ventasHoy = $datos['ventas_hoy'] ?? ['cantidad' => 0, 'monto' => 0];
$ventasMes = $datos['ventas_mes'] ?? ['cantidad' => 0, 'monto' => 0];
$serie = $datos['serie_ventas'] ?? [];
$stockBajo = $datos['stock_bajo'] ?? [];
$ventasRecientes = $datos['ventas_recientes'] ?? [];
$ordenesRecientes = $datos['ordenes_recientes'] ?? [];
$garantiasProximas = $datos['garantias_proximas'] ?? [];
$ventasPendientes = $datos['ventas_pendientes'] ?? [];
$ordenesPendientes = $datos['ordenes_pendientes'] ?? [];
$misOrdenes = $datos['mis_ordenes'] ?? [];
$ordenesPorEstado = $datos['ordenes_por_estado'] ?? [];

$maxMonto = 0.0;
foreach ($serie as $punto) {
    $maxMonto = max($maxMonto, (float) $punto['monto']);
}

$coloresPerfil = [
    'Gerente'  => ['text-bg-primary', 'fa-user-tie', 'Control general del negocio'],
    'Vendedor' => ['text-bg-success', 'fa-cash-register', 'Ventas, cobros y clientes'],
    'Tecnico'  => ['text-bg-info', 'fa-screwdriver-wrench', 'Taller y equipos de clientes'],
];
[$clasePerfil, $iconoPerfil, $descripcionPerfil] = $coloresPerfil[$perfil] ?? ['text-bg-secondary', 'fa-user', 'Panel de trabajo'];

$accesos = [
    ['clientes', 'Clientes', 'clientes.svg', 'Registro y consulta'],
    ['ventas', 'Ventas', 'ventas.svg', 'Comprobantes y detalle'],
    ['pagos-venta', 'Pagos de ventas', 'pagos.svg', 'Cobros de comprobantes'],
    ['categorias', 'Categorías', 'categorias.svg', 'Clasificación del catálogo'],
    ['productos', 'Productos', 'productos.svg', 'Catálogo y precios'],
    ['proveedores', 'Proveedores', 'proveedores.svg', 'Contactos de compra'],
    ['compras', 'Compras', 'compras.svg', 'Ingreso de mercadería'],
    ['inventario', 'Inventario', 'inventario.svg', 'Movimientos y stock'],
    ['equipos', 'Equipos de clientes', 'equipos.svg', 'Equipos recibidos'],
    ['ordenes-servicio', 'Órdenes de servicio', 'servicio.svg', 'Recepción y taller'],
    ['detalle-servicio', 'Intervenciones', 'intervenciones.svg', 'Diagnóstico y trabajo'],
    ['repuestos-servicio', 'Repuestos usados', 'repuestos.svg', 'Consumo de inventario'],
    ['pagos-servicio', 'Pagos de servicios', 'pagos.svg', 'Cobros del taller'],
    ['garantias', 'Garantías', 'garantias.svg', 'Vigencia y control'],
    ['reportes', 'Reportes', 'reportes.svg', 'Consultas del sistema'],
    ['registrar-usuario', 'Usuarios y roles', 'usuarios.svg', 'Personal y permisos'],
];
$accesosVisibles = array_values(array_filter(
    $accesos,
    static fn (array $acceso): bool => puede($acceso[0])
));
?>

<div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-4">
    <div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <h1 class="titulo-pagina h3 mb-0"><?= e($saludo) ?>, <?= e($nombreUsuario ?: $usuarioPanel['usuario'] ?? 'usuario') ?></h1>
            <span class="badge <?= e($clasePerfil) ?> badge-estado">
                <i class="fa-solid <?= e($iconoPerfil) ?> me-1"></i><?= e($perfil) ?>
            </span>
        </div>
        <p class="text-secondary mb-0 small mt-1">
            <?= e($descripcionPerfil) ?> · Datos reales de MySQL · <?= e(date('d/m/Y H:i')) ?>
        </p>
    </div>
    <div class="d-flex flex-wrap gap-2 no-imprimir">
        <?php if (puede_escribir('ventas')): ?>
            <a class="btn btn-primary btn-sm" href="<?= url('ventas', ['accion' => 'nuevo']) ?>">
                <i class="fa-solid fa-plus me-1"></i> Nueva venta
            </a>
        <?php endif; ?>
        <?php if (puede_escribir('ordenes-servicio')): ?>
            <a class="btn btn-outline-primary btn-sm" href="<?= url('ordenes-servicio', ['accion' => 'nuevo']) ?>">
                <i class="fa-solid fa-screwdriver-wrench me-1"></i> Nueva orden
            </a>
        <?php endif; ?>
        <?php if (puede('reportes')): ?>
            <a class="btn btn-outline-secondary btn-sm" href="<?= url('reportes') ?>">
                <i class="fa-solid fa-chart-line me-1"></i> Reportes
            </a>
        <?php endif; ?>
    </div>
</div>

<?php /* ------------------------- Métricas por perfil ------------------------- */ ?>
<div class="row g-3 metricas mb-4">
    <?php if ($esGerente): ?>
        <div class="col-6 col-xl-3">
            <div class="metrica">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="etiqueta">Ventas de hoy</div>
                        <div class="valor"><?= e(formato_moneda($ventasHoy['monto'])) ?></div>
                        <div class="small text-secondary"><?= (int) $ventasHoy['cantidad'] ?> venta(s) confirmada(s)</div>
                    </div>
                    <span class="icono bg-success-subtle text-success"><i class="fa-solid fa-cart-shopping"></i></span>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="metrica">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="etiqueta">Ventas del mes</div>
                        <div class="valor"><?= e(formato_moneda($ventasMes['monto'])) ?></div>
                        <div class="small text-secondary"><?= (int) $ventasMes['cantidad'] ?> venta(s) registradas</div>
                    </div>
                    <span class="icono bg-primary-subtle text-primary"><i class="fa-solid fa-chart-column"></i></span>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="metrica">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="etiqueta">Cobros de hoy</div>
                        <div class="valor"><?= e(formato_moneda((float) $datos['pagos_venta_hoy'] + (float) $datos['pagos_servicio_hoy'])) ?></div>
                        <div class="small text-secondary">
                            Ventas <?= e(formato_moneda($datos['pagos_venta_hoy'])) ?> ·
                            Servicios <?= e(formato_moneda($datos['pagos_servicio_hoy'])) ?>
                        </div>
                    </div>
                    <span class="icono bg-info-subtle text-info"><i class="fa-solid fa-money-bill-wave"></i></span>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="metrica">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="etiqueta">Órdenes activas</div>
                        <div class="valor"><?= (int) $datos['ordenes_activas'] ?></div>
                        <div class="small text-secondary">En taller o listas para entrega</div>
                    </div>
                    <span class="icono bg-warning-subtle text-warning"><i class="fa-solid fa-screwdriver-wrench"></i></span>
                </div>
            </div>
        </div>
    <?php elseif ($esVendedor): ?>
        <div class="col-6 col-xl-3">
            <div class="metrica">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="etiqueta">Ventas de hoy</div>
                        <div class="valor"><?= e(formato_moneda($ventasHoy['monto'])) ?></div>
                        <div class="small text-secondary"><?= (int) $ventasHoy['cantidad'] ?> venta(s) confirmada(s)</div>
                    </div>
                    <span class="icono bg-success-subtle text-success"><i class="fa-solid fa-cart-shopping"></i></span>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="metrica">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="etiqueta">Ventas del mes</div>
                        <div class="valor"><?= e(formato_moneda($ventasMes['monto'])) ?></div>
                        <div class="small text-secondary"><?= (int) $ventasMes['cantidad'] ?> venta(s) registradas</div>
                    </div>
                    <span class="icono bg-primary-subtle text-primary"><i class="fa-solid fa-chart-column"></i></span>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="metrica">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="etiqueta">Cobros de ventas hoy</div>
                        <div class="valor"><?= e(formato_moneda($datos['pagos_venta_hoy'])) ?></div>
                        <div class="small text-secondary">Mes: <?= e(formato_moneda($datos['pagos_venta_mes'])) ?></div>
                    </div>
                    <span class="icono bg-info-subtle text-info"><i class="fa-solid fa-money-bill-wave"></i></span>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="metrica">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="etiqueta">Clientes activos</div>
                        <div class="valor"><?= (int) $datos['clientes'] ?></div>
                        <div class="small text-secondary">Nuevos este mes: <?= (int) $datos['clientes_nuevos_mes'] ?></div>
                    </div>
                    <span class="icono bg-warning-subtle text-warning"><i class="fa-solid fa-users"></i></span>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="col-6 col-xl-3">
            <div class="metrica">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="etiqueta">Órdenes activas</div>
                        <div class="valor"><?= (int) $datos['ordenes_activas'] ?></div>
                        <div class="small text-secondary">En taller o listas para entrega</div>
                    </div>
                    <span class="icono bg-warning-subtle text-warning"><i class="fa-solid fa-screwdriver-wrench"></i></span>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="metrica">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="etiqueta">Mis órdenes abiertas</div>
                        <div class="valor"><?= (int) ($datos['mis_ordenes_activas'] ?? 0) ?></div>
                        <div class="small text-secondary">Asignadas a su usuario</div>
                    </div>
                    <span class="icono bg-primary-subtle text-primary"><i class="fa-solid fa-user-gear"></i></span>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="metrica">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="etiqueta">Recibidas hoy</div>
                        <div class="valor"><?= (int) $datos['ordenes_hoy'] ?></div>
                        <div class="small text-secondary">Nuevos ingresos al taller</div>
                    </div>
                    <span class="icono bg-info-subtle text-info"><i class="fa-solid fa-laptop-medical"></i></span>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="metrica">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="etiqueta">Cobros de servicios hoy</div>
                        <div class="valor"><?= e(formato_moneda($datos['pagos_servicio_hoy'])) ?></div>
                        <div class="small text-secondary">Mes: <?= e(formato_moneda($datos['pagos_servicio_mes'])) ?></div>
                    </div>
                    <span class="icono bg-success-subtle text-success"><i class="fa-solid fa-hand-holding-dollar"></i></span>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php /* ------------------------- Indicadores secundarios ------------------------- */ ?>
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="metrica py-3">
            <div class="etiqueta"><?= $esVendedor ? 'Clientes nuevos del mes' : 'Clientes activos' ?></div>
            <div class="valor fs-4"><?= $esVendedor ? (int) $datos['clientes_nuevos_mes'] : (int) $datos['clientes'] ?></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="metrica py-3">
            <div class="etiqueta">Productos activos</div>
            <div class="valor fs-4"><?= (int) $datos['productos'] ?></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="metrica py-3">
            <a class="text-decoration-none text-reset d-block" href="<?= url('productos', ['bajo' => 1]) ?>">
                <div class="etiqueta">Stock bajo</div>
                <div class="valor fs-4 text-danger"><?= (int) $datos['bajo_stock'] ?></div>
            </a>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="metrica py-3">
            <div class="etiqueta"><?= $esTecnico ? 'Por entregar' : 'Garantías vigentes' ?></div>
            <div class="valor fs-4 <?= $esTecnico ? 'text-warning' : 'text-success' ?>">
                <?= $esTecnico ? (int) $datos['ordenes_por_entregar'] : (int) $datos['garantias'] ?>
            </div>
        </div>
    </div>
</div>

<?php /* ------------------------- Accesos rápidos con iconos ------------------------- */ ?>
<?php if ($accesosVisibles !== []): ?>
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="fa-solid fa-table-cells-large me-2 text-primary"></i>Accesos rápidos</span>
            <span class="small text-secondary">Módulos disponibles para su perfil</span>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <?php foreach ($accesosVisibles as [$rutaAcceso, $textoAcceso, $iconoAcceso, $detalleAcceso]): ?>
                    <div class="col-12 col-sm-6 col-lg-4 col-xxl-3">
                        <a class="acceso-rapido" href="<?= url($rutaAcceso) ?>">
                            <img src="<?= asset('imagen/iconos/' . $iconoAcceso) ?>" alt="">
                            <span class="min-w-0">
                                <span class="titulo d-block"><?= e($textoAcceso) ?></span>
                                <span class="detalle d-block"><?= e($detalleAcceso) ?></span>
                            </span>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php /* ------------------------- Ventas / taller ------------------------- */ ?>
<div class="row g-4 mb-4">
    <?php if (!$esTecnico): ?>
        <div class="col-lg-7">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="fa-solid fa-chart-simple me-2 text-primary"></i>Ventas de los últimos 7 días</span>
                    <span class="small text-secondary">Monto confirmado</span>
                </div>
                <div class="card-body">
                    <?php if ($maxMonto <= 0): ?>
                        <div class="text-center text-secondary py-4">
                            <i class="fa-regular fa-chart-bar fa-2x mb-2 d-block"></i>
                            No hay ventas confirmadas en el periodo.
                        </div>
                    <?php else: ?>
                        <div class="d-flex align-items-end justify-content-between gap-2" style="height:180px">
                            <?php foreach ($serie as $punto): ?>
                                <?php $altura = max(4, (int) round(((float) $punto['monto'] / $maxMonto) * 150)); ?>
                                <div class="text-center flex-fill d-flex flex-column justify-content-end h-100"
                                     title="<?= e($punto['etiqueta'] . ': ' . formato_moneda($punto['monto'])) ?>">
                                    <div class="small text-secondary mb-1" style="font-size:.65rem">
                                        <?= $punto['monto'] > 0 ? e(number_format((float) $punto['monto'], 0, ',', '.')) : '' ?>
                                    </div>
                                    <div class="rounded-top mx-auto w-100"
                                         style="height:<?= $altura ?>px;max-width:42px;background:linear-gradient(180deg,#06b6d4,#1d4ed8)"></div>
                                    <div class="small text-secondary mt-1" style="font-size:.7rem"><?= e($punto['etiqueta']) ?></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="col-lg-7">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="fa-solid fa-list-check me-2 text-primary"></i>Mis órdenes abiertas</span>
                    <a class="small" href="<?= url('ordenes-servicio', ['tecnico' => (int) ($usuarioPanel['id_usuario'] ?? 0)]) ?>">Ver todas</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-alex mb-0">
                        <thead>
                            <tr><th>Orden</th><th>Cliente / equipo</th><th>Estado</th><th class="text-end">Saldo</th></tr>
                        </thead>
                        <tbody>
                            <?php if ($misOrdenes === []): ?>
                                <tr><td colspan="4" class="text-center text-secondary py-4">No tiene órdenes abiertas asignadas.</td></tr>
                            <?php endif; ?>
                            <?php foreach ($misOrdenes as $orden): ?>
                                <?php $saldoOrden = (float) $orden['saldo']; ?>
                                <tr>
                                    <td>
                                        <a class="fw-semibold" href="<?= url('ordenes-servicio', ['accion' => 'ver', 'id' => (int) $orden['id_orden']]) ?>">
                                            <?= e($orden['numero_orden']) ?>
                                        </a>
                                        <div class="text-secondary" style="font-size:.72rem"><?= e(formato_fecha($orden['fecha_recepcion'])) ?></div>
                                    </td>
                                    <td class="small">
                                        <?= e($orden['cliente']) ?>
                                        <div class="text-secondary" style="font-size:.72rem">
                                            <?= e(trim(($orden['marca'] ?? '') . ' ' . ($orden['modelo'] ?? ''))) ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-estado text-bg-<?= e(clase_estado($orden['estado'])) ?>">
                                            <?= e(etiqueta_estado($orden['estado'])) ?>
                                        </span>
                                    </td>
                                    <td class="text-end <?= $saldoOrden > 0.009 ? 'text-danger fw-semibold' : 'text-success' ?>">
                                        <?= e(formato_moneda($saldoOrden)) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="col-lg-5">
        <?php if (!$esTecnico): ?>
            <div class="card h-100">
                <div class="card-header"><i class="fa-solid fa-triangle-exclamation me-2 text-danger"></i>Productos con stock bajo</div>
                <div class="table-responsive">
                    <table class="table table-sm table-alex mb-0">
                        <thead>
                            <tr><th>Producto</th><th class="text-end">Stock</th><th class="text-end">Mínimo</th></tr>
                        </thead>
                        <tbody>
                            <?php if ($stockBajo === []): ?>
                                <tr><td colspan="3" class="text-center text-secondary py-4">Sin alertas de stock.</td></tr>
                            <?php endif; ?>
                            <?php foreach ($stockBajo as $producto): ?>
                                <tr>
                                    <td>
                                        <div class="fw-semibold small"><?= e($producto['nombre']) ?></div>
                                        <div class="text-secondary" style="font-size:.75rem"><?= e($producto['codigo']) ?></div>
                                    </td>
                                    <td class="text-end fw-bold text-danger"><?= (int) $producto['stock_actual'] ?></td>
                                    <td class="text-end"><?= (int) $producto['stock_minimo'] ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="card-footer bg-white text-end">
                    <a class="small fw-semibold" href="<?= url('productos', ['bajo' => 1]) ?>">Ver todos <i class="fa-solid fa-arrow-right ms-1"></i></a>
                </div>
            </div>
        <?php else: ?>
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="fa-solid fa-gauge-high me-2 text-primary"></i>Estado del taller</span>
                    <a class="small" href="<?= url('ordenes-servicio') ?>">Ver órdenes</a>
                </div>
                <div class="card-body">
                    <?php if ($ordenesPorEstado === []): ?>
                        <p class="text-secondary small mb-0">Sin órdenes registradas.</p>
                    <?php endif; ?>
                    <?php foreach ($ordenesPorEstado as $estadoTaller => $cantidadTaller): ?>
                        <?php
                        $totalTaller = max(1, array_sum($ordenesPorEstado));
                        $porcentaje = (int) round(((int) $cantidadTaller / $totalTaller) * 100);
                        ?>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between small mb-1">
                                <span><?= e(etiqueta_estado($estadoTaller)) ?></span>
                                <span class="fw-semibold"><?= (int) $cantidadTaller ?></span>
                            </div>
                            <div class="progress" style="height:7px">
                                <div class="progress-bar bg-<?= e(clase_estado($estadoTaller)) ?>"
                                     style="width:<?= $porcentaje ?>%"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php if ($esTecnico): ?>
    <?php /* Repuestos con stock bajo para el técnico */ ?>
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="fa-solid fa-gears me-2 text-primary"></i>Repuestos con stock bajo</span>
            <a class="small" href="<?= url('productos', ['bajo' => 1]) ?>">Ver productos</a>
        </div>
        <div class="table-responsive">
            <table class="table table-alex mb-0">
                <thead>
                    <tr><th>Producto</th><th>Código</th><th class="text-end">Stock</th><th class="text-end">Mínimo</th></tr>
                </thead>
                <tbody>
                    <?php if ($stockBajo === []): ?>
                        <tr><td colspan="4" class="text-center text-secondary py-4">Sin alertas de stock de repuestos.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($stockBajo as $producto): ?>
                        <tr>
                            <td class="fw-semibold small"><?= e($producto['nombre']) ?></td>
                            <td class="small text-secondary"><?= e($producto['codigo']) ?></td>
                            <td class="text-end fw-bold text-danger"><?= (int) $producto['stock_actual'] ?></td>
                            <td class="text-end"><?= (int) $producto['stock_minimo'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<?php if (!$esTecnico): ?>
    <?php /* Ventas con saldo pendiente: útil para vendedor y gerente */ ?>
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="fa-solid fa-hourglass-half me-2 text-warning"></i>Ventas con saldo pendiente</span>
            <?php if (puede('pagos-venta')): ?>
                <a class="small" href="<?= url('pagos-venta') ?>">Gestionar cobros</a>
            <?php endif; ?>
        </div>
        <div class="table-responsive">
            <table class="table table-alex mb-0">
                <thead>
                    <tr>
                        <th>Comprobante</th><th>Cliente</th><th>Fecha</th>
                        <th class="text-end">Total</th><th class="text-end">Pagado</th>
                        <th class="text-end">Saldo</th><th class="text-end"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($ventasPendientes === []): ?>
                        <tr><td colspan="7" class="text-center text-secondary py-4">No hay ventas con saldo pendiente.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($ventasPendientes as $pendiente): ?>
                        <tr>
                            <td class="fw-semibold"><?= e($pendiente['numero_comprobante']) ?></td>
                            <td class="small">
                                <?= e($pendiente['cliente']) ?>
                                <div class="text-secondary" style="font-size:.72rem">CI <?= e($pendiente['ci']) ?></div>
                            </td>
                            <td class="small text-secondary"><?= e(formato_fecha($pendiente['fecha_venta'])) ?></td>
                            <td class="text-end"><?= e(formato_moneda($pendiente['total'])) ?></td>
                            <td class="text-end text-success"><?= e(formato_moneda($pendiente['pagado'])) ?></td>
                            <td class="text-end fw-semibold text-danger"><?= e(formato_moneda($pendiente['saldo'])) ?></td>
                            <td class="text-end">
                                <a class="btn btn-sm btn-outline-primary"
                                   href="<?= url('ventas', ['accion' => 'ver', 'id' => (int) $pendiente['id_venta']]) ?>">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<?php /* ------------------------- Últimas ventas y órdenes ------------------------- */ ?>
<div class="row g-4">
    <?php if (!$esTecnico): ?>
        <div class="col-lg-7">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="fa-solid fa-receipt me-2 text-primary"></i>Últimas ventas</span>
                    <a class="small" href="<?= url('ventas') ?>">Ver todas</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-alex mb-0">
                        <thead>
                            <tr><th>Comprobante</th><th>Cliente</th><th>Fecha</th><th class="text-end">Total</th><th class="text-end">Saldo</th></tr>
                        </thead>
                        <tbody>
                            <?php if ($ventasRecientes === []): ?>
                                <tr><td colspan="5" class="text-center text-secondary py-4">Aún no se registraron ventas.</td></tr>
                            <?php endif; ?>
                            <?php foreach ($ventasRecientes as $venta): ?>
                                <tr>
                                    <td><a class="fw-semibold" href="<?= url('ventas', ['accion' => 'ver', 'id' => (int) $venta['id_venta']]) ?>"><?= e($venta['numero_comprobante']) ?></a></td>
                                    <td class="small"><?= e($venta['cliente']) ?></td>
                                    <td class="small text-secondary"><?= e(formato_fecha($venta['fecha_venta'])) ?></td>
                                    <td class="text-end"><?= e(formato_moneda($venta['total'])) ?></td>
                                    <td class="text-end">
                                        <?php $saldo = (float) $venta['total'] - (float) $venta['pagado']; ?>
                                        <?php if ($saldo <= 0.009): ?>
                                            <span class="badge text-bg-success">Pagado</span>
                                        <?php else: ?>
                                            <span class="badge text-bg-warning"><?= e(formato_moneda($saldo)) ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="<?= $esTecnico ? 'col-12' : 'col-lg-5' ?>">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fa-solid fa-screwdriver-wrench me-2 text-primary"></i>Órdenes recientes</span>
                <a class="small" href="<?= url('ordenes-servicio') ?>">Ver todas</a>
            </div>
            <div class="table-responsive">
                <table class="table table-alex mb-0">
                    <thead>
                        <tr><th>Orden</th><th>Cliente / equipo</th><th>Estado</th></tr>
                    </thead>
                    <tbody>
                        <?php if ($ordenesRecientes === []): ?>
                            <tr><td colspan="3" class="text-center text-secondary py-4">No hay órdenes registradas.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($ordenesRecientes as $orden): ?>
                            <tr>
                                <td>
                                    <a class="fw-semibold" href="<?= url('ordenes-servicio', ['accion' => 'ver', 'id' => (int) $orden['id_orden']]) ?>">
                                        <?= e($orden['numero_orden']) ?>
                                    </a>
                                    <div class="text-secondary" style="font-size:.72rem"><?= e(formato_fecha($orden['fecha_recepcion'])) ?></div>
                                </td>
                                <td class="small">
                                    <?= e($orden['cliente']) ?>
                                    <div class="text-secondary" style="font-size:.72rem"><?= e(trim((string) $orden['equipo'])) ?></div>
                                </td>
                                <td><span class="badge badge-estado text-bg-<?= e(clase_estado($orden['estado'])) ?>"><?= e(etiqueta_estado($orden['estado'])) ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php if (!$esTecnico && $ordenesPendientes !== []): ?>
    <div class="card mt-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="fa-solid fa-hand-holding-dollar me-2 text-warning"></i>Órdenes de servicio con saldo pendiente</span>
            <?php if (puede('pagos-servicio')): ?>
                <a class="small" href="<?= url('pagos-servicio') ?>">Gestionar cobros</a>
            <?php endif; ?>
        </div>
        <div class="table-responsive">
            <table class="table table-alex mb-0">
                <thead>
                    <tr><th>Orden</th><th>Cliente</th><th>Estado</th><th class="text-end">Total</th><th class="text-end">Saldo</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($ordenesPendientes as $pendiente): ?>
                        <tr>
                            <td>
                                <a class="fw-semibold" href="<?= url('ordenes-servicio', ['accion' => 'ver', 'id' => (int) $pendiente['id_orden']]) ?>">
                                    <?= e($pendiente['numero_orden']) ?>
                                </a>
                            </td>
                            <td class="small"><?= e($pendiente['cliente']) ?></td>
                            <td>
                                <span class="badge badge-estado text-bg-<?= e(clase_estado($pendiente['estado'])) ?>">
                                    <?= e(etiqueta_estado($pendiente['estado'])) ?>
                                </span>
                            </td>
                            <td class="text-end"><?= e(formato_moneda($pendiente['total'])) ?></td>
                            <td class="text-end fw-semibold text-danger"><?= e(formato_moneda($pendiente['saldo'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<?php if ($garantiasProximas !== []): ?>
    <div class="card mt-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="fa-solid fa-shield-halved me-2 text-warning"></i>Garantías próximas a vencer (30 días)</span>
            <a class="small" href="<?= url('garantias') ?>">Ver garantías</a>
        </div>
        <div class="table-responsive">
            <table class="table table-alex mb-0">
                <thead><tr><th>Tipo</th><th>Descripción</th><th>Vence</th><th class="text-end">Días restantes</th></tr></thead>
                <tbody>
                    <?php foreach ($garantiasProximas as $garantia): ?>
                        <tr>
                            <td><span class="badge text-bg-light border"><?= e($garantia['tipo_garantia']) ?></span></td>
                            <td class="small"><?= e($garantia['descripcion']) ?></td>
                            <td class="small"><?= e(formato_fecha($garantia['fecha_fin'])) ?></td>
                            <td class="text-end fw-bold"><?= (int) $garantia['dias_restantes'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<?php parcial('pie-admin'); ?>
