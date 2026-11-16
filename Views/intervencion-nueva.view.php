<?php
/** Módulo 13: nueva intervención. */
parcial('head-admin', ['titulo' => $titulo, 'subtitulo' => $subtitulo]);
$volver = $ordenSeleccionada
    ? url('ordenes-servicio', ['accion' => 'ver', 'id' => (int) $ordenSeleccionada['id_orden']])
    : url('detalle-servicio');
?>

<div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-4">
    <div>
        <h1 class="titulo-pagina h3 mb-1">Registrar intervención</h1>
        <p class="text-secondary mb-0 small">
            <?= $ordenSeleccionada
                ? 'Orden ' . e($ordenSeleccionada['numero_orden']) . ' · ' . e($ordenSeleccionada['cliente'])
                : 'Seleccione la orden de servicio correspondiente.' ?>
        </p>
    </div>
    <a class="btn btn-outline-secondary btn-sm" href="<?= e($volver) ?>">
        <i class="fa-solid fa-arrow-left me-1"></i> Volver
    </a>
</div>

<?php if ($errores !== []): ?>
    <div class="alert alert-danger">
        <div class="fw-semibold mb-1"><i class="fa-solid fa-circle-exclamation me-1"></i> No se registró la intervención</div>
        <ul class="mb-0 ps-3"><?php foreach ($errores as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul>
    </div>
<?php endif; ?>

<?php if ($ordenes === []): ?>
    <div class="alert alert-warning">
        <i class="fa-solid fa-triangle-exclamation me-1"></i>
        No hay órdenes de servicio abiertas. Registre una orden primero en
        <a href="<?= url('ordenes-servicio', ['accion' => 'nuevo']) ?>">Órdenes de servicio</a>.
    </div>
<?php endif; ?>

<form method="post" action="<?= url('detalle-servicio') ?>" novalidate>
    <?= csrf_field() ?>
    <input type="hidden" name="operacion" value="registrar">

    <div class="card mb-4">
        <div class="card-header"><i class="fa-solid fa-clipboard-check me-2 text-primary"></i>Datos de la intervención</div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label requerido" for="id_orden">Orden de servicio</label>
                    <select class="form-select" id="id_orden" name="id_orden" required>
                        <option value="0">Seleccione una orden abierta…</option>
                        <?php foreach ($ordenes as $orden): ?>
                            <option value="<?= (int) $orden['id_orden'] ?>"
                                <?= (int) $formulario['id_orden'] === (int) $orden['id_orden'] ? 'selected' : '' ?>>
                                <?= e($orden['numero_orden'] . ' · ' . $orden['cliente'] . ' · '
                                    . trim(($orden['marca'] ?? '') . ' ' . ($orden['modelo'] ?? ''))
                                    . ' · ' . etiqueta_estado($orden['estado'])) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label requerido" for="id_usuario_tecnico">Técnico</label>
                    <select class="form-select" id="id_usuario_tecnico" name="id_usuario_tecnico" required>
                        <?php foreach ($tecnicos as $tecnico): ?>
                            <option value="<?= (int) $tecnico['id_usuario'] ?>"
                                <?= (int) $formulario['id_usuario_tecnico'] === (int) $tecnico['id_usuario'] ? 'selected' : '' ?>>
                                <?= e(nombre_completo($tecnico)) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label requerido" for="fecha_intervencion">Fecha y hora</label>
                    <input class="form-control" id="fecha_intervencion" name="fecha_intervencion" type="datetime-local"
                           value="<?= e($formulario['fecha_intervencion']) ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label requerido" for="diagnostico">Diagnóstico</label>
                    <textarea class="form-control" id="diagnostico" name="diagnostico" rows="3" maxlength="2000" required><?= e($formulario['diagnostico']) ?></textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label requerido" for="trabajo_realizado">Trabajo realizado</label>
                    <textarea class="form-control" id="trabajo_realizado" name="trabajo_realizado" rows="3" maxlength="2000" required><?= e($formulario['trabajo_realizado']) ?></textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="resultado_prueba">Resultado de la prueba</label>
                    <input class="form-control" id="resultado_prueba" name="resultado_prueba" maxlength="255"
                           value="<?= e($formulario['resultado_prueba']) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="costo">Costo de la intervención (Bs)</label>
                    <input class="form-control text-end" id="costo" name="costo" type="number" min="0" step="0.01"
                           value="<?= e($formulario['costo']) ?>">
                    <div class="form-text">La mano de obra de la orden se recalcula con la suma de intervenciones.</div>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex flex-wrap gap-2">
        <button class="btn btn-primary" type="submit" <?= $ordenes === [] ? 'disabled' : '' ?>>
            <i class="fa-solid fa-floppy-disk me-1"></i> Registrar intervención
        </button>
        <a class="btn btn-light border" href="<?= e($volver) ?>">Cancelar</a>
    </div>
</form>

<?php parcial('pie-admin'); ?>
