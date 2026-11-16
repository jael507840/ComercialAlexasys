<?php
/** Módulo 9: listado de ventas. */
parcial('head-admin', ['titulo' => $titulo, 'subtitulo' => $subtitulo]);
$puedeEscribir = puede_escribir('ventas');
?>

<div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-4">
    <div>
        <h1 class="titulo-pagina h3 mb-1">Ventas</h1>
        <p class="text-secondary mb-0 small">Comprobantes internos de venta y estado de cobro de cada operación.</p>
    </div>
    <?php if ($puedeEscribir): ?>
        <a class="btn btn-primary btn-sm" href="<?= url('ventas', ['accion' => 'nuevo']) ?>">
            <i class="fa-solid fa-plus me-1"></i> Registrar venta
        </a>
    <?php endif; ?>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="metrica py-3">
            <div class="etiqueta">Ventas listadas</div>
            <div class="valor fs-4"><?= count($ventas) ?></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="metrica py-3">
            <div class="etiqueta">Monto vendido</div>
            <div class="valor fs-5"><?= e(formato_moneda($montoTotal)) ?></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="metrica py-3">
            <div class="etiqueta">Cobrado</div>
            <div class="valor fs-5 text-success"><?= e(formato_moneda($montoPagado)) ?></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="metrica py-3">
            <div class="etiqueta">Saldo pendiente</div>
            <div class="valor fs-5 <?= $saldoTotal > 0 ? 'text-danger' : 'text-success' ?>"><?= e(formato_moneda($saldoTotal)) ?></div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header"><i class="fa-solid fa-filter me-2 text-primary"></i>Filtros</div>
    <div class="card-body">
        <form class="row g-2 align-items-end" method="get" action="<?= asset('index.php') ?>">
            <input type="hidden" name="ruta" value="ventas">
            <div class="col-md-2">
                <label class="form-label small mb-1">Desde</label>
                <input class="form-control form-control-sm" type="date" name="desde" value="<?= e($desde) ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">Hasta</label>
                <input class="form-control form-control-sm" type="date" name="hasta" value="<?= e($hasta) ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label small mb-1">Buscar</label>
                <input class="form-control form-control-sm" type="search" name="q" value="<?= e($busqueda) ?>"
                       placeholder="Comprobante, CI o cliente">
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">Estado</label>
                <select class="form-select form-select-sm" name="estado">
                    <option value="">Todos</option>
                    <option value="Confirmada" <?= $estado === 'Confirmada' ? 'selected' : '' ?>>Confirmada</option>
                    <option value="Anulada" <?= $estado === 'Anulada' ? 'selected' : '' ?>>Anulada</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button class="btn btn-sm btn-primary flex-grow-1" type="submit"><i class="fa-solid fa-filter me-1"></i>Filtrar</button>
                <a class="btn btn-sm btn-outline-secondary" href="<?= url('ventas') ?>"><i class="fa-solid fa-rotate-left"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fa-solid fa-receipt me-2 text-primary"></i>Comprobantes de venta</span>
        <span class="small text-secondary"><?= count($ventas) ?> resultado(s)</span>
    </div>
    <div class="tabla-scroll">
        <table class="table table-alex table-hover mb-0">
            <thead>
                <tr>
                    <th>Comprobante</th>
                    <th>Fecha</th>
                    <th>Cliente</th>
                    <th>Vendedor</th>
                    <th class="text-end">Total</th>
                    <th class="text-end">Pagado</th>
                    <th class="text-end">Saldo</th>
                    <th>Estado</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($ventas === []): ?>
                    <tr><td colspan="9" class="text-center text-secondary py-4">No se encontraron ventas con los filtros aplicados.</td></tr>
                <?php endif; ?>
                <?php foreach ($ventas as $venta): ?>
                    <?php $saldo = (float) $venta['saldo']; ?>
                    <tr>
                        <td class="fw-semibold"><?= e($venta['numero_comprobante']) ?></td>
                        <td class="small"><?= e(formato_fecha($venta['fecha_venta'], true)) ?></td>
                        <td class="small">
                            <?= e($venta['cliente']) ?>
                            <div class="text-secondary" style="font-size:.72rem">CI <?= e($venta['ci']) ?></div>
                        </td>
                        <td class="small text-secondary"><?= e($venta['vendedor']) ?></td>
                        <td class="text-end fw-semibold"><?= e(formato_moneda($venta['total'])) ?></td>
                        <td class="text-end text-success"><?= e(formato_moneda($venta['pagado'])) ?></td>
                        <td class="text-end <?= $saldo > 0.009 ? 'text-danger fw-semibold' : 'text-success' ?>">
                            <?= e(formato_moneda($saldo)) ?>
                        </td>
                        <td>
                            <span class="badge badge-estado text-bg-<?= $venta['estado'] === 'Confirmada' ? 'success' : 'secondary' ?>">
                                <?= e($venta['estado']) ?>
                            </span>
                        </td>
                        <td class="text-end">
                            <div class="acciones-tabla">
                                <a class="btn btn-sm btn-outline-primary" title="Ver detalle"
                                   href="<?= url('ventas', ['accion' => 'ver', 'id' => (int) $venta['id_venta']]) ?>">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                                <?php if (puede_escribir('pagos-venta') && $venta['estado'] === 'Confirmada' && $saldo > 0.009): ?>
                                    <a class="btn btn-sm btn-outline-success" title="Registrar pago"
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

<?php parcial('pie-admin'); ?>
