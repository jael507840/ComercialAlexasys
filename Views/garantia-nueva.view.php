<?php
/** Formulario para registrar una garantía asociada a un origen válido. */
parcial('head-admin', ['titulo' => $titulo, 'subtitulo' => $subtitulo]);
?>

<div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-4">
    <div>
        <h1 class="titulo-pagina h3 mb-1">Registrar garantía</h1>
        <p class="text-secondary mb-0 small">El origen de la garantía se toma del producto vendido o de la orden.</p>
    </div>
    <a class="btn btn-outline-secondary btn-sm" href="<?= url('garantias') ?>">
        <i class="fa-solid fa-arrow-left me-1"></i> Volver al listado
    </a>
</div>

<?php if ($errores !== []): ?>
    <div class="alert alert-danger">
        <ul class="mb-0"><?php foreach ($errores as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul>
    </div>
<?php endif; ?>

<div class="card mb-4">
    <div class="card-header"><i class="fa-solid fa-link me-2 text-primary"></i>Origen de la garantía</div>
    <div class="card-body">
        <?php if ($origen['tipo'] === 'Producto'): ?>
            <?php $detalle = $origen['detalle_venta']; ?>
            <div class="row g-3">
                <div class="col-md-4"><div class="small text-secondary">Cliente</div><div class="fw-semibold"><?= e($detalle['cliente']) ?></div></div>
                <div class="col-md-4"><div class="small text-secondary">Comprobante</div><div class="fw-semibold"><?= e($detalle['numero_comprobante']) ?></div></div>
                <div class="col-md-4"><div class="small text-secondary">Producto</div><div class="fw-semibold"><?= e($detalle['nombre']) ?></div></div>
            </div>
        <?php else: ?>
            <?php $orden = $origen['orden']; ?>
            <div class="row g-3">
                <div class="col-md-4"><div class="small text-secondary">Cliente</div><div class="fw-semibold"><?= e($orden['cliente']) ?></div></div>
                <div class="col-md-4"><div class="small text-secondary">Orden</div><div class="fw-semibold"><?= e($orden['numero_orden']) ?></div></div>
                <div class="col-md-4"><div class="small text-secondary">Equipo</div><div class="fw-semibold"><?= e(trim(($orden['marca'] ?? '') . ' ' . ($orden['modelo'] ?? ''))) ?></div></div>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="card">
    <div class="card-header">Datos de la garantía · <?= e($origen['tipo']) ?></div>
    <div class="card-body">
        <form method="post" action="<?= url('garantias', ['accion' => 'nuevo']) ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="operacion" value="registrar">
            <input type="hidden" name="id_detalle_venta" value="<?= (int) $idDetalleVenta ?>">
            <input type="hidden" name="id_orden" value="<?= (int) $idOrden ?>">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label" for="fecha_inicio">Inicio</label>
                    <input class="form-control" type="date" id="fecha_inicio" name="fecha_inicio"
                           value="<?= e($formulario['fecha_inicio']) ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="fecha_fin">Vencimiento</label>
                    <input class="form-control" type="date" id="fecha_fin" name="fecha_fin"
                           value="<?= e($formulario['fecha_fin']) ?>" required>
                </div>
                <div class="col-12">
                    <label class="form-label" for="descripcion">Descripción / alcance</label>
                    <input class="form-control" type="text" id="descripcion" name="descripcion" maxlength="255"
                           value="<?= e($formulario['descripcion']) ?>" required>
                </div>
                <div class="col-12">
                    <label class="form-label" for="condiciones">Condiciones (opcional)</label>
                    <textarea class="form-control" id="condiciones" name="condiciones" rows="3" maxlength="2000"><?= e($formulario['condiciones']) ?></textarea>
                </div>
            </div>
            <div class="d-flex justify-content-end gap-2 mt-4">
                <a class="btn btn-outline-secondary" href="<?= url('garantias') ?>">Cancelar</a>
                <button class="btn btn-primary" type="submit"><i class="fa-solid fa-floppy-disk me-1"></i>Registrar garantía</button>
            </div>
        </form>
    </div>
</div>

<?php parcial('pie-admin'); ?>
