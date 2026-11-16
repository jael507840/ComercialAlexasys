<?php
/** Módulo 12: nueva orden de servicio. Variables: $equipos, $tecnicos, $formulario, $errores. */
parcial('head-admin', ['titulo' => $titulo, 'subtitulo' => $subtitulo]);
?>

<div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-4">
    <div>
        <h1 class="titulo-pagina h3 mb-1">Nueva orden de servicio</h1>
        <p class="text-secondary mb-0 small">Registre la falla y los accesorios recibidos junto con el equipo.</p>
    </div>
    <a class="btn btn-outline-secondary btn-sm" href="<?= url('ordenes-servicio') ?>">
        <i class="fa-solid fa-arrow-left me-1"></i> Volver al listado
    </a>
</div>

<?php if ($errores !== []): ?>
    <div class="alert alert-danger">
        <div class="fw-semibold mb-1"><i class="fa-solid fa-circle-exclamation me-1"></i> No se registró la orden</div>
        <ul class="mb-0 ps-3"><?php foreach ($errores as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul>
    </div>
<?php endif; ?>

<?php if ($equipos === []): ?>
    <div class="alert alert-warning">
        <i class="fa-solid fa-triangle-exclamation me-1"></i>
        Primero debe registrar un equipo de cliente en el módulo
        <a href="<?= url('equipos') ?>">Equipos de clientes</a>.
    </div>
<?php endif; ?>

<form method="post" action="<?= url('ordenes-servicio') ?>" novalidate>
    <?= csrf_field() ?>
    <input type="hidden" name="operacion" value="crear">

    <div class="card mb-4">
        <div class="card-header"><i class="fa-solid fa-laptop-medical me-2 text-primary"></i>Equipo y recepción</div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-5">
                    <label class="form-label requerido" for="id_equipo">Equipo del cliente</label>
                    <select class="form-select" id="id_equipo" name="id_equipo" required>
                        <option value="0">Seleccione un equipo…</option>
                        <?php foreach ($equipos as $equipo): ?>
                            <option value="<?= (int) $equipo['id_equipo'] ?>"
                                <?= (int) $formulario['id_equipo'] === (int) $equipo['id_equipo'] ? 'selected' : '' ?>>
                                <?= e(nombre_completo($equipo) . ' · ' . $equipo['ci'] . ' · '
                                    . trim(($equipo['marca'] ?? '') . ' ' . ($equipo['modelo'] ?? ''))
                                    . ($equipo['numero_serie'] ? ' · ' . $equipo['numero_serie'] : '')) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label requerido" for="fecha_recepcion">Fecha de recepción</label>
                    <input class="form-control" id="fecha_recepcion" name="fecha_recepcion" type="datetime-local"
                           value="<?= e($formulario['fecha_recepcion']) ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="id_usuario_tecnico">Técnico asignado</label>
                    <select class="form-select" id="id_usuario_tecnico" name="id_usuario_tecnico">
                        <option value="0">Asignar más adelante</option>
                        <?php foreach ($tecnicos as $tecnico): ?>
                            <option value="<?= (int) $tecnico['id_usuario'] ?>"
                                <?= (int) $formulario['id_usuario_tecnico'] === (int) $tecnico['id_usuario'] ? 'selected' : '' ?>>
                                <?= e(nombre_completo($tecnico)) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-8">
                    <label class="form-label requerido" for="falla_reportada">Falla reportada por el cliente</label>
                    <textarea class="form-control" id="falla_reportada" name="falla_reportada" rows="3" maxlength="2000" required><?= e($formulario['falla_reportada']) ?></textarea>
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="accesorios">Accesorios recibidos</label>
                    <textarea class="form-control" id="accesorios" name="accesorios" rows="3" maxlength="255"
                              placeholder="Cargador, mouse, funda…"><?= e($formulario['accesorios']) ?></textarea>
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="costo_servicio">Costo inicial de servicio (Bs)</label>
                    <input class="form-control text-end" id="costo_servicio" name="costo_servicio" type="number"
                           min="0" step="0.01" value="<?= e($formulario['costo_servicio']) ?>">
                    <div class="form-text">Si registra intervenciones, el costo se recalcula con la suma de ellas.</div>
                </div>
                <div class="col-md-9">
                    <label class="form-label" for="observaciones">Observaciones</label>
                    <textarea class="form-control" id="observaciones" name="observaciones" rows="2" maxlength="2000"><?= e($formulario['observaciones']) ?></textarea>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex flex-wrap gap-2">
        <button class="btn btn-primary" type="submit" <?= $equipos === [] ? 'disabled' : '' ?>>
            <i class="fa-solid fa-floppy-disk me-1"></i> Registrar orden de servicio
        </button>
        <a class="btn btn-light border" href="<?= url('ordenes-servicio') ?>">Cancelar</a>
    </div>
</form>

<?php parcial('pie-admin'); ?>
