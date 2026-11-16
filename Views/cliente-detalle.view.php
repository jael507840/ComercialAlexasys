<?php
/** Historial del cliente. Variables: $cliente, $ventas, $equipos, $servicios. */
parcial('head-admin', ['titulo' => $titulo, 'subtitulo' => $subtitulo]);
$totalComprado = 0.0;
$saldoPendiente = 0.0;
foreach ($ventas as $venta) {
    if ($venta['estado'] === 'Confirmada') {
        $totalComprado += (float) $venta['total'];
        $saldoPendiente += max(0, (float) $venta['saldo']);
    }
}
?>

<div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-4">
    <div>
        <h1 class="titulo-pagina h3 mb-1"><?= e(nombre_completo($cliente)) ?></h1>
        <p class="text-secondary mb-0 small">
            CI <?= e($cliente['ci']) ?> ·
            <?= e($cliente['telefono'] ?: 'Sin teléfono') ?> ·
            <?= e($cliente['correo'] ?: 'Sin correo') ?>
        </p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <?php if (puede_escribir('ventas')): ?>
            <a class="btn btn-primary btn-sm" href="<?= url('ventas', ['accion' => 'nuevo', 'cliente' => (int) $cliente['id_cliente']]) ?>">
                <i class="fa-solid fa-cart-plus me-1"></i> Nueva venta
            </a>
        <?php endif; ?>
        <?php if (puede_escribir('equipos')): ?>
            <a class="btn btn-outline-primary btn-sm" href="<?= url('equipos', ['accion' => 'nuevo', 'cliente' => (int) $cliente['id_cliente']]) ?>">
                <i class="fa-solid fa-laptop-medical me-1"></i> Registrar equipo
            </a>
        <?php endif; ?>
        <a class="btn btn-outline-secondary btn-sm" href="<?= url('clientes') ?>">
            <i class="fa-solid fa-arrow-left me-1"></i> Volver
        </a>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="metrica py-3">
            <div class="etiqueta">Ventas confirmadas</div>
            <div class="valor fs-4"><?= count(array_filter($ventas, static fn (array $v): bool => $v['estado'] === 'Confirmada')) ?></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="metrica py-3">
            <div class="etiqueta">Total comprado</div>
            <div class="valor fs-5"><?= e(formato_moneda($totalComprado)) ?></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="metrica py-3">
            <div class="etiqueta">Saldo pendiente</div>
            <div class="valor fs-5 <?= $saldoPendiente > 0 ? 'text-danger' : 'text-success' ?>"><?= e(formato_moneda($saldoPendiente)) ?></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="metrica py-3">
            <div class="etiqueta">Equipos registrados</div>
            <div class="valor fs-4"><?= count($equipos) ?></div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header"><i class="fa-solid fa-user me-2 text-primary"></i>Datos del cliente</div>
    <div class="card-body">
        <dl class="row mb-0 small">
            <dt class="col-sm-3 text-secondary">Cédula de identidad</dt><dd class="col-sm-3"><?= e($cliente['ci']) ?></dd>
            <dt class="col-sm-3 text-secondary">Fecha de registro</dt><dd class="col-sm-3"><?= e(formato_fecha($cliente['fecha_registro'], true)) ?></dd>
            <dt class="col-sm-3 text-secondary">Teléfono</dt><dd class="col-sm-3"><?= e($cliente['telefono'] ?: '—') ?></dd>
            <dt class="col-sm-3 text-secondary">Correo</dt><dd class="col-sm-3"><?= e($cliente['correo'] ?: '—') ?></dd>
            <dt class="col-sm-3 text-secondary">Dirección</dt><dd class="col-sm-3"><?= e($cliente['direccion'] ?: '—') ?></dd>
            <dt class="col-sm-3 text-secondary">Estado</dt>
            <dd class="col-sm-3">
                <span class="badge badge-estado text-bg-<?= $cliente['estado'] === 'Activo' ? 'success' : 'secondary' ?>">
                    <?= e($cliente['estado']) ?>
                </span>
            </dd>
        </dl>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fa-solid fa-receipt me-2 text-primary"></i>Historial de ventas</span>
        <span class="small text-secondary"><?= count($ventas) ?> registro(s)</span>
    </div>
    <div class="tabla-scroll">
        <table class="table table-alex mb-0">
            <thead>
                <tr><th>Comprobante</th><th>Fecha</th><th class="text-end">Total</th><th class="text-end">Pagado</th><th class="text-end">Saldo</th><th>Estado</th><th></th></tr>
            </thead>
            <tbody>
                <?php if ($ventas === []): ?>
                    <tr><td colspan="7" class="text-center text-secondary py-4">El cliente aún no tiene ventas registradas.</td></tr>
                <?php endif; ?>
                <?php foreach ($ventas as $venta): ?>
                    <tr>
                        <td class="fw-semibold"><?= e($venta['numero_comprobante']) ?></td>
                        <td class="small"><?= e(formato_fecha($venta['fecha_venta'])) ?></td>
                        <td class="text-end"><?= e(formato_moneda($venta['total'])) ?></td>
                        <td class="text-end text-success"><?= e(formato_moneda($venta['pagado'])) ?></td>
                        <td class="text-end <?= (float) $venta['saldo'] > 0.009 ? 'text-danger fw-semibold' : '' ?>">
                            <?= e(formato_moneda($venta['saldo'])) ?>
                        </td>
                        <td>
                            <span class="badge badge-estado text-bg-<?= $venta['estado'] === 'Confirmada' ? 'success' : 'secondary' ?>">
                                <?= e($venta['estado']) ?>
                            </span>
                        </td>
                        <td class="text-end">
                            <a class="btn btn-sm btn-outline-primary" href="<?= url('ventas', ['accion' => 'ver', 'id' => (int) $venta['id_venta']]) ?>">
                                <i class="fa-solid fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header"><i class="fa-solid fa-laptop me-2 text-primary"></i>Equipos del cliente</div>
            <div class="tabla-scroll">
                <table class="table table-alex mb-0">
                    <thead><tr><th>Equipo</th><th>Serie</th><th class="text-center">Órdenes</th></tr></thead>
                    <tbody>
                        <?php if ($equipos === []): ?>
                            <tr><td colspan="3" class="text-center text-secondary py-4">Sin equipos registrados.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($equipos as $equipo): ?>
                            <tr>
                                <td>
                                    <div class="fw-semibold small"><?= e(trim(($equipo['marca'] ?? '') . ' ' . ($equipo['modelo'] ?? ''))) ?></div>
                                    <div class="text-secondary" style="font-size:.75rem"><?= e($equipo['tipo_equipo']) ?></div>
                                </td>
                                <td class="small"><?= e($equipo['numero_serie'] ?: '—') ?></td>
                                <td class="text-center">
                                    <a class="badge text-bg-light border" href="<?= url('equipos', ['accion' => 'ver', 'id' => (int) $equipo['id_equipo']]) ?>">
                                        <?= (int) $equipo['total_ordenes'] ?>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header"><i class="fa-solid fa-screwdriver-wrench me-2 text-primary"></i>Servicios técnicos</div>
            <div class="tabla-scroll">
                <table class="table table-alex mb-0">
                    <thead><tr><th>Orden</th><th>Equipo</th><th>Estado</th></tr></thead>
                    <tbody>
                        <?php if ($servicios === []): ?>
                            <tr><td colspan="3" class="text-center text-secondary py-4">Sin órdenes de servicio.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($servicios as $servicio): ?>
                            <tr>
                                <td>
                                    <a class="fw-semibold" href="<?= url('ordenes-servicio', ['accion' => 'ver', 'id' => (int) $servicio['id_orden']]) ?>">
                                        <?= e($servicio['numero_orden']) ?>
                                    </a>
                                    <div class="text-secondary" style="font-size:.72rem"><?= e(formato_fecha($servicio['fecha_recepcion'])) ?></div>
                                </td>
                                <td class="small"><?= e(trim(($servicio['marca'] ?? '') . ' ' . ($servicio['modelo'] ?? ''))) ?></td>
                                <td><span class="badge badge-estado text-bg-<?= e(clase_estado($servicio['estado'])) ?>"><?= e(etiqueta_estado($servicio['estado'])) ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php parcial('pie-admin'); ?>
