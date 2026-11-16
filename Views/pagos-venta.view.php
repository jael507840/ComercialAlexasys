<?php
/** Módulo 10: listado de pagos de ventas. */
parcial('head-admin', ['titulo' => $titulo, 'subtitulo' => $subtitulo]);
$cantidad = (int) ($resumen['cantidad'] ?? 0);
$total = (float) ($resumen['total'] ?? 0);
$puedeEscribir = puede_escribir('pagos-venta');
?>

<div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-4">
    <div>
        <h1 class="titulo-pagina h3 mb-1">Pagos de ventas</h1>
        <p class="text-secondary mb-0 small">
            Cada pago se descuenta del saldo del comprobante. El saldo nunca puede quedar en negativo.
        </p>
    </div>
    <a class="btn btn-outline-primary btn-sm" href="<?= url('ventas') ?>">
        <i class="fa-solid fa-receipt me-1"></i> Ir a ventas
    </a>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="metrica py-3">
            <div class="etiqueta">Pagos del periodo</div>
            <div class="valor fs-4"><?= $cantidad ?></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="metrica py-3">
            <div class="etiqueta">Monto cobrado</div>
            <div class="valor fs-5 text-success"><?= e(formato_moneda($total)) ?></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="metrica py-3">
            <div class="etiqueta">Ventas con saldo</div>
            <div class="valor fs-4 text-danger"><?= count($pendientes) ?></div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header"><i class="fa-solid fa-filter me-2 text-primary"></i>Filtros</div>
    <div class="card-body">
        <form class="row g-2 align-items-end" method="get" action="<?= asset('index.php') ?>">
            <input type="hidden" name="ruta" value="pagos-venta">
            <div class="col-md-3">
                <label class="form-label small mb-1">Desde</label>
                <input class="form-control form-control-sm" type="date" name="desde" value="<?= e($desde) ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label small mb-1">Hasta</label>
                <input class="form-control form-control-sm" type="date" name="hasta" value="<?= e($hasta) ?>">
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button class="btn btn-sm btn-primary flex-grow-1" type="submit">
                    <i class="fa-solid fa-filter me-1"></i>Filtrar
                </button>
                <a class="btn btn-sm btn-outline-secondary" href="<?= url('pagos-venta') ?>">
                    <i class="fa-solid fa-rotate-left"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header"><i class="fa-solid fa-hourglass-half me-2 text-warning"></i>Ventas con saldo pendiente</div>
    <div class="tabla-scroll">
        <table class="table table-alex mb-0">
            <thead>
                <tr>
                    <th>Comprobante</th>
                    <th>Fecha</th>
                    <th>Cliente</th>
                    <th class="text-end">Total</th>
                    <th class="text-end">Pagado</th>
                    <th class="text-end">Saldo</th>
                    <th class="text-end"></th>
                </tr>
            </thead>
            <tbody>
                <?php if ($pendientes === []): ?>
                    <tr><td colspan="7" class="text-center text-secondary py-4">No hay ventas con saldo pendiente.</td></tr>
                <?php endif; ?>
                <?php foreach ($pendientes as $venta): ?>
                    <tr>
                        <td class="fw-semibold"><?= e($venta['numero_comprobante']) ?></td>
                        <td class="small"><?= e(formato_fecha($venta['fecha_venta'], true)) ?></td>
                        <td class="small">
                            <?= e($venta['cliente']) ?>
                            <div class="text-secondary" style="font-size:.72rem">CI <?= e($venta['ci']) ?></div>
                        </td>
                        <td class="text-end"><?= e(formato_moneda($venta['total'])) ?></td>
                        <td class="text-end text-success"><?= e(formato_moneda($venta['pagado'])) ?></td>
                        <td class="text-end fw-semibold text-danger"><?= e(formato_moneda($venta['saldo'])) ?></td>
                        <td class="text-end">
                            <div class="acciones-tabla">
                                <a class="btn btn-sm btn-outline-primary" title="Ver venta"
                                   href="<?= url('ventas', ['accion' => 'ver', 'id' => (int) $venta['id_venta']]) ?>">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                                <?php if ($puedeEscribir): ?>
                                    <a class="btn btn-sm btn-success" title="Registrar pago"
                                       href="<?= url('pagos-venta', ['accion' => 'nuevo', 'venta' => (int) $venta['id_venta']]) ?>">
                                        <i class="fa-solid fa-money-bill-wave"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fa-solid fa-money-bill-wave me-2 text-primary"></i>Pagos registrados</span>
        <span class="small text-secondary"><?= count($pagos) ?> registro(s)</span>
    </div>
    <div class="tabla-scroll">
        <table class="table table-alex table-hover mb-0">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Comprobante</th>
                    <th>Cliente</th>
                    <th>Método</th>
                    <th>Referencia</th>
                    <th class="text-end">Monto</th>
                    <th>Registrado por</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($pagos === []): ?>
                    <tr><td colspan="8" class="text-center text-secondary py-4">No se encontraron pagos con los filtros aplicados.</td></tr>
                <?php endif; ?>
                <?php foreach ($pagos as $pago): ?>
                    <tr>
                        <td class="small"><?= e(formato_fecha($pago['fecha_pago'], true)) ?></td>
                        <td>
                            <a class="fw-semibold" href="<?= url('ventas', ['accion' => 'ver', 'id' => (int) $pago['id_venta']]) ?>">
                                <?= e($pago['numero_comprobante']) ?>
                            </a>
                        </td>
                        <td class="small"><?= e($pago['cliente']) ?></td>
                        <td class="small"><?= e($pago['metodo_pago']) ?></td>
                        <td class="small text-secondary"><?= e($pago['referencia'] ?: '—') ?></td>
                        <td class="text-end fw-semibold"><?= e(formato_moneda($pago['monto'])) ?></td>
                        <td class="small text-secondary"><?= e($pago['usuario']) ?></td>
                        <td>
                            <span class="badge badge-estado text-bg-<?= $pago['estado'] === 'Confirmado' ? 'success' : 'secondary' ?>">
                                <?= e($pago['estado']) ?>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white small text-secondary">
        <i class="fa-solid fa-circle-info me-1"></i>
        Se muestran hasta 300 pagos según los filtros aplicados.
    </div>
</div>

<?php parcial('pie-admin'); ?>
