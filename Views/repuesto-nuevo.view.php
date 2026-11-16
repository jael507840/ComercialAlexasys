<?php
/** Módulo 14: registrar repuesto. */
parcial('head-admin', ['titulo' => $titulo, 'subtitulo' => $subtitulo]);
$volver = $ordenSeleccionada
    ? url('ordenes-servicio', ['accion' => 'ver', 'id' => (int) $ordenSeleccionada['id_orden']])
    : url('repuestos-servicio');
?>

<div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-4">
    <div>
        <h1 class="titulo-pagina h3 mb-1">Registrar repuesto</h1>
        <p class="text-secondary mb-0 small">
            <?= $ordenSeleccionada
                ? 'Orden ' . e($ordenSeleccionada['numero_orden']) . ' · ' . e($ordenSeleccionada['cliente'])
                : 'Seleccione la orden de servicio que consume el repuesto.' ?>
        </p>
    </div>
    <a class="btn btn-outline-secondary btn-sm" href="<?= e($volver) ?>">
        <i class="fa-solid fa-arrow-left me-1"></i> Volver
    </a>
</div>

<?php if ($errores !== []): ?>
    <div class="alert alert-danger">
        <div class="fw-semibold mb-1"><i class="fa-solid fa-circle-exclamation me-1"></i> No se registró el repuesto</div>
        <ul class="mb-0 ps-3"><?php foreach ($errores as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul>
    </div>
<?php endif; ?>

<?php if ($ordenes === [] || $productos === []): ?>
    <div class="alert alert-warning">
        <i class="fa-solid fa-triangle-exclamation me-1"></i>
        Debe existir una orden abierta y al menos un producto activo con stock.
    </div>
<?php endif; ?>

<form method="post" action="<?= url('repuestos-servicio') ?>" novalidate>
    <?= csrf_field() ?>
    <input type="hidden" name="operacion" value="registrar">

    <div class="card mb-4">
        <div class="card-header"><i class="fa-solid fa-gears me-2 text-primary"></i>Datos del repuesto</div>
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
                                    . trim(($orden['marca'] ?? '') . ' ' . ($orden['modelo'] ?? ''))) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label requerido" for="id_producto">Producto / repuesto</label>
                    <select class="form-select" id="id_producto" name="id_producto" required>
                        <option value="0">Seleccione un producto con stock…</option>
                        <?php foreach ($productos as $producto): ?>
                            <?php
                            $costo = $producto['costo_adquisicion'] !== null
                                ? (float) $producto['costo_adquisicion']
                                : (float) $producto['precio_venta'];
                            ?>
                            <option value="<?= (int) $producto['id_producto'] ?>"
                                    data-stock="<?= (int) $producto['stock_actual'] ?>"
                                    data-costo="<?= e(number_format($costo, 2, '.', '')) ?>"
                                <?= (int) $formulario['id_producto'] === (int) $producto['id_producto'] ? 'selected' : '' ?>>
                                <?= e($producto['codigo'] . ' · ' . $producto['nombre'] . ' · stock ' . (int) $producto['stock_actual']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-text">El costo unitario se toma de la última compra registrada.</div>
                </div>
                <div class="col-md-3">
                    <label class="form-label requerido" for="cantidad">Cantidad</label>
                    <input class="form-control text-end" id="cantidad" name="cantidad" type="number" min="1" step="1"
                           value="<?= e($formulario['cantidad']) ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Costo estimado</label>
                    <div class="form-control text-end bg-light" id="costoEstimado">Bs 0,00</div>
                    <div class="form-text">Referencial; el sistema recalcula al confirmar.</div>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex flex-wrap gap-2">
        <button class="btn btn-primary" type="submit" <?= ($ordenes === [] || $productos === []) ? 'disabled' : '' ?>>
            <i class="fa-solid fa-floppy-disk me-1"></i> Registrar y descontar stock
        </button>
        <a class="btn btn-light border" href="<?= e($volver) ?>">Cancelar</a>
    </div>
</form>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const producto = document.getElementById('id_producto');
        const cantidad = document.getElementById('cantidad');
        const estimado = document.getElementById('costoEstimado');
        if (!producto || !cantidad || !estimado) return;

        function actualizar() {
            const costo = parseFloat(producto.selectedOptions[0]?.dataset.costo || '0');
            const qty = parseInt(cantidad.value, 10) || 0;
            estimado.textContent = 'Bs ' + (costo * qty).toFixed(2).replace('.', ',');
        }
        producto.addEventListener('change', actualizar);
        cantidad.addEventListener('input', actualizar);
        actualizar();
    });
</script>

<?php parcial('pie-admin'); ?>
