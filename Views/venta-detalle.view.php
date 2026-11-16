<?php
/** Módulo 9: detalle de venta. Variables: $venta, $detalle, $pagos, $estadoPago, $movimientos. */
parcial('head-admin', ['titulo' => $titulo, 'subtitulo' => $subtitulo]);
$puedePagar = puede_escribir('pagos-venta') && $venta['estado'] === 'Confirmada' && $estadoPago['saldo'] > 0.009;
?>

<div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-4">
    <div>
        <h1 class="titulo-pagina h3 mb-1">Venta <?= e($venta['numero_comprobante']) ?></h1>
        <p class="text-secondary mb-0 small">
            <?= e(formato_fecha($venta['fecha_venta'], true)) ?> ·
            <?= e($venta['cliente']) ?> · CI <?= e($venta['ci']) ?> ·
            Vendedor: <?= e($venta['vendedor']) ?>
        </p>
    </div>
    <div class="d-flex flex-wrap gap-2 no-imprimir">
        <?php if ($puedePagar): ?>
            <a class="btn btn-success btn-sm" href="<?= url('pagos-venta', ['accion' => 'nuevo', 'venta' => (int) $venta['id_venta']]) ?>">
                <i class="fa-solid fa-money-bill-wave me-1"></i> Registrar pago
            </a>
        <?php endif; ?>
        <button class="btn btn-outline-secondary btn-sm" type="button" onclick="window.print()">
            <i class="fa-solid fa-print me-1"></i> Imprimir
        </button>
        <a class="btn btn-outline-secondary btn-sm" href="<?= url('ventas') ?>">
            <i class="fa-solid fa-arrow-left me-1"></i> Volver
        </a>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="metrica py-3">
            <div class="etiqueta">Total</div>
            <div class="valor fs-5"><?= e(formato_moneda($estadoPago['total'])) ?></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="metrica py-3">
            <div class="etiqueta">Pagado</div>
            <div class="valor fs-5 text-success"><?= e(formato_moneda($estadoPago['pagado'])) ?></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="metrica py-3">
            <div class="etiqueta">Saldo</div>
            <div class="valor fs-5 <?= $estadoPago['saldo'] > 0.009 ? 'text-danger' : 'text-success' ?>">
                <?= e(formato_moneda($estadoPago['saldo'])) ?>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="metrica py-3">
            <div class="etiqueta">Estado</div>
            <div class="mt-1">
                <?php
                $clase = match ($estadoPago['estado']) {
                    'Pagado' => 'success',
                    'Parcial' => 'warning',
                    'Pendiente' => 'danger',
                    default => 'secondary',
                };
                ?>
                <span class="badge badge-estado text-bg-<?= $clase ?>"><?= e($estadoPago['estado']) ?></span>
                <span class="badge badge-estado text-bg-<?= $venta['estado'] === 'Confirmada' ? 'success' : 'secondary' ?>">
                    <?= e($venta['estado']) ?>
                </span>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header"><i class="fa-solid fa-cart-shopping me-2 text-primary"></i>Productos vendidos</div>
    <div class="tabla-scroll">
        <table class="table table-alex mb-0">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Producto</th>
                    <th class="text-end">Cantidad</th>
                    <th class="text-end">Precio unitario</th>
                    <th class="text-end">Descuento</th>
                    <th class="text-end">Subtotal</th>
                    <th class="text-center">Garantía</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($detalle === []): ?>
                    <tr><td colspan="7" class="text-center text-secondary py-4">La venta no tiene detalle registrado.</td></tr>
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
                        <td class="text-end"><?= e(formato_moneda($linea['precio_unitario'])) ?></td>
                        <td class="text-end"><?= e(formato_moneda($linea['descuento'])) ?></td>
                        <td class="text-end fw-semibold"><?= e(formato_moneda($linea['subtotal'])) ?></td>
                        <td class="text-center">
                            <?php if ((int) $linea['tiene_garantia'] > 0): ?>
                                <span class="badge text-bg-success">Registrada</span>
                            <?php elseif (puede_escribir('garantias')): ?>
                                <a class="btn btn-sm btn-outline-warning"
                                   href="<?= url('garantias', ['accion' => 'nuevo', 'detalle_venta' => (int) $linea['id_detalle_venta']]) ?>"
                                   title="Registrar garantía">
                                    <i class="fa-solid fa-shield-halved"></i>
                                </a>
                            <?php else: ?>
                                <span class="badge text-bg-light border">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="5" class="text-end">Subtotal</td>
                    <td class="text-end"><?= e(formato_moneda($venta['subtotal'])) ?></td>
                    <td></td>
                </tr>
                <tr>
                    <td colspan="5" class="text-end">Descuento general</td>
                    <td class="text-end">− <?= e(formato_moneda($venta['descuento'])) ?></td>
                    <td></td>
                </tr>
                <tr>
                    <td colspan="5" class="text-end fw-bold">Total</td>
                    <td class="text-end fw-bold fs-6"><?= e(formato_moneda($venta['total'])) ?></td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-7">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fa-solid fa-money-bill-wave me-2 text-primary"></i>Pagos registrados</span>
                <span class="small text-secondary"><?= count($pagos) ?> pago(s)</span>
            </div>
            <div class="tabla-scroll">
                <table class="table table-alex mb-0">
                    <thead>
                        <tr><th>Fecha</th><th>Método</th><th>Referencia</th><th class="text-end">Monto</th><th>Registrado por</th></tr>
                    </thead>
                    <tbody>
                        <?php if ($pagos === []): ?>
                            <tr><td colspan="5" class="text-center text-secondary py-4">La venta aún no tiene pagos registrados.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($pagos as $pago): ?>
                            <tr>
                                <td class="small"><?= e(formato_fecha($pago['fecha_pago'], true)) ?></td>
                                <td class="small"><?= e($pago['metodo_pago']) ?></td>
                                <td class="small text-secondary"><?= e($pago['referencia'] ?: '—') ?></td>
                                <td class="text-end fw-semibold"><?= e(formato_moneda($pago['monto'])) ?></td>
                                <td class="small text-secondary"><?= e($pago['usuario']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card h-100">
            <div class="card-header"><i class="fa-solid fa-arrow-right-arrow-left me-2 text-primary"></i>Movimientos de inventario</div>
            <div class="tabla-scroll">
                <table class="table table-alex mb-0">
                    <thead><tr><th>Producto</th><th class="text-end">Cantidad</th><th>Tipo</th></tr></thead>
                    <tbody>
                        <?php if ($movimientos === []): ?>
                            <tr><td colspan="3" class="text-center text-secondary py-4">Sin movimientos asociados.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($movimientos as $movimiento): ?>
                            <tr>
                                <td class="small"><?= e($movimiento['producto']) ?></td>
                                <td class="text-end"><?= (int) $movimiento['cantidad'] ?></td>
                                <td>
                                    <span class="badge badge-estado text-bg-<?= $movimiento['tipo_movimiento'] === 'Entrada' ? 'success' : 'danger' ?>">
                                        <?= e($movimiento['tipo_movimiento']) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($venta['observacion'])): ?>
    <div class="alert alert-light border">
        <i class="fa-solid fa-note-sticky me-1"></i>
        <strong>Observación:</strong> <?= e($venta['observacion']) ?>
    </div>
<?php endif; ?>

<?php parcial('pie-admin'); ?>
