<?php
/**
 * Módulo 15: registrar pago de servicio técnico.
 * Variables: $orden, $estadoPago, $formulario, $errores.
 */
parcial('head-admin', ['titulo' => $titulo, 'subtitulo' => $subtitulo]);
$saldo = (float) $estadoPago['saldo'];
$equipo = trim((string) ($orden['marca'] ?? '') . ' ' . (string) ($orden['modelo'] ?? ''));
?>
<div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-4">
    <div>
        <h1 class="titulo-pagina h3 mb-1">Registrar pago de servicio</h1>
        <p class="text-secondary mb-0 small">
            Orden <?= e($orden['numero_orden']) ?> ·
            <?= e($orden['cliente']) ?> · CI <?= e($orden['ci']) ?>
        </p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a class="btn btn-outline-secondary btn-sm" href="<?= url('ordenes-servicio', ['accion' => 'ver', 'id' => (int) $orden['id_orden']]) ?>">
            <i class="fa-solid fa-eye me-1"></i> Ver orden
        </a>
        <a class="btn btn-outline-secondary btn-sm" href="<?= url('pagos-servicio') ?>">
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
            <div class="card-header"><i class="fa-solid fa-screwdriver-wrench me-2 text-primary"></i>Resumen de la orden</div>
            <div class="card-body">
                <dl class="row mb-0 small">
                    <dt class="col-6 text-secondary fw-normal">Mano de obra</dt>
                    <dd class="col-6 text-end"><?= e(formato_moneda($orden['costo_servicio'])) ?></dd>
                    <dt class="col-6 text-secondary fw-normal">Repuestos</dt>
                    <dd class="col-6 text-end"><?= e(formato_moneda($orden['repuestos_total'])) ?></dd>
                    <dt class="col-6 text-secondary fw-normal">Total</dt>
                    <dd class="col-6 text-end fw-semibold"><?= e(formato_moneda($estadoPago['total'])) ?></dd>
                    <dt class="col-6 text-secondary fw-normal">Pagado</dt>
                    <dd class="col-6 text-end text-success"><?= e(formato_moneda($estadoPago['pagado'])) ?></dd>
                    <dt class="col-6 text-secondary fw-normal">Saldo</dt>
                    <dd class="col-6 text-end fw-bold text-danger fs-6"><?= e(formato_moneda($saldo)) ?></dd>
                    <dt class="col-6 text-secondary fw-normal">Estado</dt>
                    <dd class="col-6 text-end">
                        <span class="badge badge-estado text-bg-<?= e(clase_estado($orden['estado'])) ?>">
                            <?= e(etiqueta_estado($orden['estado'])) ?>
                        </span>
                    </dd>
                </dl>
                <hr>
                <div class="small text-secondary">
                    <div><i class="fa-solid fa-calendar-day me-1"></i><?= e(formato_fecha($orden['fecha_recepcion'], true)) ?></div>
                    <div class="mt-1"><i class="fa-solid fa-laptop-medical me-1"></i><?= e($equipo !== '' ? $equipo : 'Equipo sin marca/modelo') ?></div>
                    <?php if (!empty($orden['numero_serie'])): ?>
                        <div class="mt-1"><i class="fa-solid fa-barcode me-1"></i>Serie: <?= e($orden['numero_serie']) ?></div>
                    <?php endif; ?>
                    <div class="mt-1"><i class="fa-solid fa-user-gear me-1"></i>Técnico: <?= e($orden['tecnico'] ?: 'Sin asignar') ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card h-100">
            <div class="card-header"><i class="fa-solid fa-hand-holding-dollar me-2 text-success"></i>Datos del pago</div>
            <div class="card-body">
                <form method="post" action="<?= url('pagos-servicio') ?>" novalidate>
                    <?= csrf_field() ?>
                    <input type="hidden" name="operacion" value="registrar">
                    <input type="hidden" name="id_orden" value="<?= (int) $orden['id_orden'] ?>">
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
                                <?php foreach (PagoServicio::METODOS as $metodo): ?>
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
                            <input class="form-control" id="referencia" name="referencia" maxlength="100"
                                   value="<?= e($formulario['referencia']) ?>"
                                   placeholder="N.º de transferencia, voucher, etc.">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="observacion">Observación</label>
                            <input class="form-control" id="observacion" name="observacion" maxlength="200"
                                   value="<?= e($formulario['observacion']) ?>">
                        </div>
                    </div>
                    <div class="mt-4 d-flex flex-wrap gap-2">
                        <button class="btn btn-success" type="submit">
                            <i class="fa-solid fa-floppy-disk me-1"></i> Registrar pago
                        </button>
                        <a class="btn btn-light border" href="<?= url('ordenes-servicio', ['accion' => 'ver', 'id' => (int) $orden['id_orden']]) ?>">
                            Cancelar
                        </a>
                    </div>
                    <div class="form-text mt-2">
                        El sistema rechaza montos negativos o superiores al saldo pendiente de la orden.
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php parcial('pie-admin'); ?>
