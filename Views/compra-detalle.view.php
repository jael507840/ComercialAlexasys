<?php
/** Módulo 7: detalle de compra. Variables: $compra, $detalle, $movimientos. */
parcial('head-admin', ['titulo' => $titulo, 'subtitulo' => $subtitulo]);
?>

<div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-4">
    <div>
        <h1 class="titulo-pagina h3 mb-1">Compra <?= e($compra['numero_comprobante']) ?></h1>
        <p class="text-secondary mb-0 small">
            <?= e(formato_fecha($compra['fecha_compra'], true)) ?> ·
            <?= e($compra['proveedor']) ?> ·
            Registrada por <?= e($compra['usuario']) ?>
        </p>
    </div>
    <a class="btn btn-outline-secondary btn-sm" href="<?= url('compras') ?>">
        <i class="fa-solid fa-arrow-left me-1"></i> Volver al listado
    </a>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="metrica py-3">
            <div class="etiqueta">Total de la compra</div>
            <div class="valor fs-5"><?= e(formato_moneda($compra['total_compra'])) ?></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="metrica py-3">
            <div class="etiqueta">Productos</div>
            <div class="valor fs-4"><?= count($detalle) ?></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="metrica py-3">
            <div class="etiqueta">Estado</div>
            <div class="mt-1">
                <span class="badge badge-estado text-bg-<?= $compra['estado'] === 'Registrada' ? 'success' : 'secondary' ?>">
                    <?= e($compra['estado']) ?>
                </span>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="metrica py-3">
            <div class="etiqueta">Proveedor</div>
            <div class="small fw-semibold mt-1"><?= e($compra['proveedor']) ?></div>
            <div class="small text-secondary"><?= e($compra['nit'] ? 'NIT ' . $compra['nit'] : 'Sin NIT') ?></div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header"><i class="fa-solid fa-boxes-stacked me-2 text-primary"></i>Detalle de productos</div>
    <div class="tabla-scroll">
        <table class="table table-alex mb-0">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Producto</th>
                    <th class="text-end">Cantidad</th>
                    <th class="text-end">Costo unitario</th>
                    <th class="text-end">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($detalle === []): ?>
                    <tr><td colspan="5" class="text-center text-secondary py-4">La compra no tiene detalle registrado.</td></tr>
                <?php endif; ?>
                <?php foreach ($detalle as $linea): ?>
                    <tr>
                        <td class="small"><?= e($linea['codigo']) ?></td>
                        <td>
                            <div class="fw-semibold small"><?= e($linea['nombre']) ?></div>
                            <div class="text-secondary" style="font-size:.74rem">
                                <?= e(trim(($linea['marca'] ?? '') . ' ' . ($linea['modelo'] ?? ''))) ?>
                            </div>
                        </td>
                        <td class="text-end"><?= (int) $linea['cantidad'] ?></td>
                        <td class="text-end"><?= e(formato_moneda($linea['costo_unitario'])) ?></td>
                        <td class="text-end fw-semibold"><?= e(formato_moneda($linea['subtotal'])) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="4" class="text-end fw-bold">Total</td>
                    <td class="text-end fw-bold"><?= e(formato_moneda($compra['total_compra'])) ?></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header"><i class="fa-solid fa-arrow-right-arrow-left me-2 text-primary"></i>Movimientos de inventario generados</div>
    <div class="tabla-scroll">
        <table class="table table-alex mb-0">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Tipo</th>
                    <th class="text-end">Cantidad</th>
                    <th>Fecha</th>
                    <th>Registrado por</th>
                    <th>Observación</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($movimientos === []): ?>
                    <tr><td colspan="6" class="text-center text-secondary py-4">No hay movimientos asociados a esta compra.</td></tr>
                <?php endif; ?>
                <?php foreach ($movimientos as $movimiento): ?>
                    <tr>
                        <td class="small">
                            <span class="fw-semibold"><?= e($movimiento['producto']) ?></span>
                            <span class="text-secondary">(<?= e($movimiento['codigo']) ?>)</span>
                        </td>
                        <td>
                            <span class="badge badge-estado text-bg-<?= $movimiento['tipo_movimiento'] === 'Entrada' ? 'success' : 'danger' ?>">
                                <?= e($movimiento['tipo_movimiento']) ?>
                            </span>
                        </td>
                        <td class="text-end fw-semibold"><?= (int) $movimiento['cantidad'] ?></td>
                        <td class="small"><?= e(formato_fecha($movimiento['fecha_movimiento'], true)) ?></td>
                        <td class="small"><?= e($movimiento['usuario']) ?></td>
                        <td class="small text-secondary"><?= e($movimiento['observacion'] ?: '—') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if (!empty($compra['observacion'])): ?>
    <div class="alert alert-light border">
        <i class="fa-solid fa-note-sticky me-1"></i>
        <strong>Observación:</strong> <?= e($compra['observacion']) ?>
    </div>
<?php endif; ?>

<?php parcial('pie-admin'); ?>
