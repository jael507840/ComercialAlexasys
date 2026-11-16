<?php
/** Módulo 15: listado de pagos de servicio. */
parcial('head-admin', ['titulo' => $titulo, 'subtitulo' => $subtitulo]);
$cantidad = (int) ($resumen['cantidad'] ?? 0);
$total = (float) ($resumen['total'] ?? 0);
$puedeEscribir = puede_escribir('pagos-servicio');
?>

<div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-4">
    <div>
        <h1 class="titulo-pagina h3 mb-1">Pagos de servicios</h1>
        <p class="text-secondary mb-0 small">Cobros registrados contra el total de cada orden de servicio técnico.</p>
    </div>
    <a class="btn btn-outline-primary btn-sm" href="<?= url('ordenes-servicio') ?>">
        <i class="fa-solid fa-screwdriver-wrench me-1"></i> Ir a órdenes
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
            <div class="etiqueta">Órdenes con saldo</div>
            <div class="valor fs-4 text-danger"><?= count($pendientes) ?></div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header"><i class="fa-solid fa-filter me-2 text-primary"></i>Filtros</div>
    <div class="card-body">
        <form class="row g-2 align-items-end" method="get" action="<?= asset('index.php') ?>">
            <input type="hidden" name="ruta" value="pagos-servicio">
            <div class="col-md-3">
                <label class="form-label small mb-1">Desde</label>
                <input class="form-control form-control-sm" type="date" name="desde" value="<?= e($desde) ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label small mb-1">Hasta</label>
                <input class="form-control form-control-sm" type="date" name="hasta" value="<?= e($hasta) ?>">
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button class="btn btn-sm btn-primary flex-grow-1" type="submit"><i class="fa-solid fa-filter me-1"></i>Filtrar</button>
                <a class="btn btn-sm btn-outline-secondary" href="<?= url('pagos-servicio') ?>"><i class="fa-solid fa-rotate-left"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header"><i class="fa-solid fa-hourglass-half me-2 text-warning"></i>Órdenes con saldo pendiente</div>
    <div class="tabla-scroll">
        <table class="table table-alex mb-0">
            <thead>
                <tr>
                    <th>Orden</th>
                    <th>Recepción</th>
                    <th>Cliente</th>
                    <th>Estado</th>
                    <th class="text-end">Total</th>
                    <th class="text-end">Pagado</th>
                    <th class="text-end">Saldo</th>
                    <th class="text-end"></th>
                </tr>
            </thead>
            <tbody>
                <?php if ($pendientes === []): ?>
                    <tr><td colspan="8" class="text-center text-secondary py-4">No hay órdenes con saldo pendiente.</td></tr>
                <?php endif; ?>
                <?php foreach ($pendientes as $pendiente): ?>
                    <tr>
                        <td class="fw-semibold"><?= e($pendiente['numero_orden']) ?></td>
                        <td class="small"><?= e(formato_fecha($pendiente['fecha_recepcion'], true)) ?></td>
                        <td class="small"><?= e($pendiente['cliente']) ?></td>
                        <td>
                            <span class="badge badge-estado text-bg-<?= clase_estado($pendiente['estado']) ?>">
                                <?= e(etiqueta_estado($pendiente['estado'])) ?>
                            </span>
                        </td>
                        <td class="text-end"><?= e(formato_moneda($pendiente['total'])) ?></td>
                        <td class="text-end text-success"><?= e(formato_moneda($pendiente['pagado'])) ?></td>
                        <td class="text-end fw-semibold text-danger"><?= e(formato_moneda($pendiente['saldo'])) ?></td>
                        <td class="text-end">
                            <div class="acciones-tabla">
                                <a class="btn btn-sm btn-outline-primary" title="Ver orden"
                                   href="<?= url('ordenes-servicio', ['accion' => 'ver', 'id' => (int) $pendiente['id_orden']]) ?>">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                                <?php if ($puedeEscribir): ?>
                                    <a class="btn btn-sm btn-success" title="Registrar pago"
                                       href="<?= url('pagos-servicio', ['accion' => 'nuevo', 'orden' => (int) $pendiente['id_orden']]) ?>">
                                        <i class="fa-solid fa-hand-holding-dollar"></i>
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
        <span><i class="fa-solid fa-hand-holding-dollar me-2 text-primary"></i>Pagos registrados</span>
        <span class="small text-secondary"><?= count($pagos) ?> registro(s)</span>
    </div>
    <div class="tabla-scroll">
        <table class="table table-alex table-hover mb-0">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Orden</th>
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
                            <a class="fw-semibold" href="<?= url('ordenes-servicio', ['accion' => 'ver', 'id' => (int) $pago['id_orden']]) ?>">
                                <?= e($pago['numero_orden']) ?>
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
