<?php
/** Módulo 10: registrar pago de venta. Variables: $venta, $estadoPago, $formulario, $errores. */
parcial('head-admin', ['titulo' => $titulo, 'subtitulo' => $subtitulo]);
$saldo = (float) $estadoPago['saldo'];
?>

<div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-4">
    <div>
        <h1 class="titulo-pagina h3 mb-1">Registrar pago de venta</h1>
        <p class="text-secondary mb-0 small">
            Comprobante <?= e($venta['numero_comprobante']) ?> ·
            <?= e($venta['cliente']) ?> · CI <?= e($venta['ci']) ?>
        </p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a class="btn btn-outline-secondary btn-sm" href="<?= url('ventas', ['accion' => 'ver', 'id' => (int) $venta['id_venta']]) ?>">
            <i class="fa-solid fa-eye me-1"></i> Ver venta
        </a>
        <a class="btn btn-outline-secondary btn-sm" href="<?= url('pagos-venta') ?>">
            <i class="fa-solid fa-arrow-left me-1"></i> Volver
        </a>
    </div>
</div>

<?php if ($errores !== []): ?>
    <div class="alert alert-danger">
        <div class="fw-semibold mb-1"><i class="fa-solid fa-circle-exclamation me-1"></i> No se registró el pago</div>
        <ul class="mb-0 ps-3"><?php foreach ($errores as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul>
    </div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header"><i class="fa-solid fa-receipt me-2 text-primary"></i>Resumen de la venta</div>
            <div class="card-body">
                <dl class="row mb-0 small">
                    <dt class="col-6 text-secondary fw-normal">Total</dt>
                    <dd class="col-6 text-end fw-semibold"><?= e(formato_moneda($estadoPago['total'])) ?></dd>
                    <dt class="col-6 text-secondary fw-normal">Pagado</dt>
                    <dd class="col-6 text-end text-success"><?= e(formato_moneda($estadoPago['pagado'])) ?></dd>
                    <dt class="col-6 text-secondary fw-normal">Saldo</dt>
                    <dd class="col-6 text-end fw-bold text-danger fs-6"><?= e(formato_moneda($saldo)) ?></dd>
                    <dt class="col-6 text-secondary fw-normal">Estado</dt>
                    <dd class="col-6 text-end">
                        <span class="badge badge-estado text-bg-warning"><?= e($estadoPago['estado']) ?></span>
                    </dd>
                </dl>
                <hr>
                <div class="small text-secondary">
                    <div><i class="fa-solid fa-calendar-day me-1"></i><?= e(formato_fecha($venta['fecha_venta'], true)) ?></div>
                    <div class="mt-1"><i class="fa-solid fa-user-tie me-1"></i>Vendedor: <?= e($venta['vendedor']) ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card h-100">
            <div class="card-header"><i class="fa-solid fa-money-bill-wave me-2 text-success"></i>Datos del pago</div>
            <div class="card-body">
                <form method="post" action="<?= url('pagos-venta') ?>" novalidate>
                    <?= csrf_field() ?>
                    <input type="hidden" name="operacion" value="registrar">
                    <input type="hidden" name="id_venta" value="<?= (int) $venta['id_venta'] ?>">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label requerido" for="monto">Monto (Bs)</label>
                            <input class="form-control text-end" id="monto" name="monto" type="number"
                                   min="0.01" max="<?= e(number_format($saldo, 2, '.', '')) ?>" step="0.01"
                                   value="<?= e($formulario['monto']) ?>" required>
                            <div class="form-text">Máximo permitido: <?= e(formato_moneda($saldo)) ?>.</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label requerido" for="metodo_pago">Método de pago</label>
                            <select class="form-select" id="metodo_pago" name="metodo_pago" required>
                                <?php foreach (PagoVenta::METODOS as $metodo): ?>
                                    <option value="<?= e($metodo) ?>" <?= $formulario['metodo_pago'] === $metodo ? 'selected' : '' ?>>
                                        <?= e($metodo) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label requerido" for="fecha_pago">Fecha y hora</label>
                            <input class="form-control" id="fecha_pago" name="fecha_pago" type="datetime-local"
                                   value="<?= e($formulario['fecha_pago']) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="referencia">Referencia</label>
                            <input class="form-control" id="referencia" name="referencia" maxlength="80"
                                   value="<?= e($formulario['referencia']) ?>"
                                   placeholder="N.º de transferencia, voucher, etc.">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="observacion">Observación</label>
                            <input class="form-control" id="observacion" name="observacion" maxlength="300"
                                   value="<?= e($formulario['observacion']) ?>">
                        </div>
                    </div>
                    <div class="mt-4 d-flex flex-wrap gap-2">
                        <button class="btn btn-success" type="submit">
                            <i class="fa-solid fa-floppy-disk me-1"></i> Registrar pago
                        </button>
                        <a class="btn btn-light border" href="<?= url('ventas', ['accion' => 'ver', 'id' => (int) $venta['id_venta']]) ?>">
                            Cancelar
                        </a>
                    </div>
                    <div class="form-text mt-2">
                        El sistema rechaza montos negativos o superiores al saldo pendiente de la venta.
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php parcial('pie-admin'); ?>
