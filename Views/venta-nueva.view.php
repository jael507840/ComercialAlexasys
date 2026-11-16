<?php
/** Módulo 9: registrar venta. */
parcial('head-admin', ['titulo' => $titulo, 'subtitulo' => $subtitulo]);

$filaHtml = static function (int $indice, array $linea) use ($productos): string {
    ob_start();
    ?>
    <tr class="linea-venta">
        <td>
            <select class="form-select form-select-sm producto-select" name="items[<?= $indice ?>][id_producto]">
                <option value="0">Seleccione un producto…</option>
                <?php foreach ($productos as $producto): ?>
                    <option value="<?= (int) $producto['id_producto'] ?>"
                            data-precio="<?= e(number_format((float) $producto['precio_venta'], 2, '.', '')) ?>"
                            data-stock="<?= (int) $producto['stock_actual'] ?>"
                            <?= (int) $linea['id_producto'] === (int) $producto['id_producto'] ? 'selected' : '' ?>>
                        <?= e($producto['codigo'] . ' · ' . $producto['nombre']) ?>
                        (stock <?= (int) $producto['stock_actual'] ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </td>
        <td style="width:100px">
            <input class="form-control form-control-sm text-end cantidad-input" type="number" min="1" step="1"
                   name="items[<?= $indice ?>][cantidad]" value="<?= (int) $linea['cantidad'] ?>">
        </td>
        <td style="width:130px">
            <input class="form-control form-control-sm text-end precio-input" type="number" min="0" step="0.01"
                   name="items[<?= $indice ?>][precio_unitario]" value="<?= e($linea['precio_unitario']) ?>">
        </td>
        <td style="width:120px">
            <input class="form-control form-control-sm text-end descuento-linea" type="number" min="0" step="0.01"
                   name="items[<?= $indice ?>][descuento]" value="<?= e($linea['descuento']) ?>">
        </td>
        <td class="text-end fw-semibold subtotal-linea" style="width:130px">Bs 0.00</td>
        <td class="text-end" style="width:50px">
            <button class="btn btn-sm btn-outline-danger btn-quitar-linea" type="button" title="Quitar línea">
                <i class="fa-solid fa-trash-can"></i>
            </button>
        </td>
    </tr>
    <?php
    return (string) ob_get_clean();
};
?>

<div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-4">
    <div>
        <h1 class="titulo-pagina h3 mb-1">Registrar venta</h1>
        <p class="text-secondary mb-0 small">El stock se descuenta automáticamente al confirmar la venta.</p>
    </div>
    <a class="btn btn-outline-secondary btn-sm" href="<?= url('ventas') ?>">
        <i class="fa-solid fa-arrow-left me-1"></i> Volver al listado
    </a>
</div>

<?php if ($errores !== []): ?>
    <div class="alert alert-danger">
        <div class="fw-semibold mb-1"><i class="fa-solid fa-circle-exclamation me-1"></i> No se registró la venta</div>
        <ul class="mb-0 ps-3"><?php foreach ($errores as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul>
    </div>
<?php endif; ?>

<?php if ($clientes === [] || $productos === []): ?>
    <div class="alert alert-warning">
        <i class="fa-solid fa-triangle-exclamation me-1"></i>
        Para registrar una venta debe existir al menos un cliente registrado y un producto activo con stock.
    </div>
<?php endif; ?>

<form method="post" action="<?= url('ventas') ?>" novalidate>
    <?= csrf_field() ?>
    <input type="hidden" name="operacion" value="registrar">

    <div class="card mb-4">
        <div class="card-header"><i class="fa-solid fa-receipt me-2 text-primary"></i>Datos de la venta</div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label requerido" for="id_cliente">Cliente</label>
                    <select class="form-select" id="id_cliente" name="id_cliente" required>
                        <option value="0">Seleccione un cliente…</option>
                        <?php foreach ($clientes as $cliente): ?>
                            <option value="<?= (int) $cliente['id_cliente'] ?>"
                                <?= (int) $formulario['id_cliente'] === (int) $cliente['id_cliente'] ? 'selected' : '' ?>>
                                <?= e($cliente['ci'] . ' · ' . nombre_completo($cliente)) ?>
                                <?= $cliente['estado'] === 'Inactivo' ? ' (inactivo)' : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label requerido" for="fecha_venta">Fecha</label>
                    <input class="form-control" id="fecha_venta" name="fecha_venta" type="date" required
                           max="<?= e(date('Y-m-d')) ?>" value="<?= e($formulario['fecha_venta']) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="numero_comprobante">Comprobante</label>
                    <input class="form-control" id="numero_comprobante" name="numero_comprobante" maxlength="30"
                           placeholder="Opcional" value="<?= e($formulario['numero_comprobante']) ?>">
                    <div class="form-text">Si se deja vacío se genera un número interno.</div>
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="observacion">Observación</label>
                    <input class="form-control" id="observacion" name="observacion" maxlength="500"
                           value="<?= e($formulario['observacion']) ?>">
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="fa-solid fa-cart-shopping me-2 text-primary"></i>Productos vendidos</span>
            <button class="btn btn-sm btn-outline-primary" type="button" id="btnAgregarLinea">
                <i class="fa-solid fa-plus me-1"></i> Agregar línea
            </button>
        </div>
        <div class="tabla-scroll">
            <table class="table table-alex mb-0" id="lineasVenta" data-moneda="Bs">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th class="text-end">Cantidad</th>
                        <th class="text-end">Precio (Bs)</th>
                        <th class="text-end">Descuento</th>
                        <th class="text-end">Subtotal</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($lineas as $indice => $linea): ?>
                        <?= $filaHtml((int) $indice, $linea) ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="card-body border-top">
            <div class="row g-2 justify-content-end">
                <div class="col-md-4">
                    <label class="form-label small" for="descuentoGlobal">Descuento general (Bs)</label>
                    <input class="form-control text-end" id="descuentoGlobal" name="descuento" type="number"
                           min="0" step="0.01" value="<?= e($formulario['descuento']) ?>">
                </div>
                <div class="col-md-4">
                    <div class="d-flex justify-content-between small text-secondary">
                        <span>Subtotal</span>
                        <span id="subtotalVenta">Bs 0.00</span>
                    </div>
                    <div class="d-flex justify-content-between fs-5 fw-bold text-primary mt-1">
                        <span>Total</span>
                        <span id="totalVenta">Bs 0.00</span>
                    </div>
                    <div class="form-text">El descuento general no puede superar el subtotal.</div>
                </div>
            </div>
        </div>
    </div>

    <template id="plantillaLineaVenta">
        <?= $filaHtml(999, ['id_producto' => 0, 'cantidad' => 1, 'precio_unitario' => '0.00', 'descuento' => '0.00']) ?>
    </template>

    <div class="d-flex flex-wrap gap-2">
        <button class="btn btn-primary" type="submit" <?= ($clientes === [] || $productos === []) ? 'disabled' : '' ?>>
            <i class="fa-solid fa-floppy-disk me-1"></i> Registrar venta y descontar stock
        </button>
        <a class="btn btn-light border" href="<?= url('ventas') ?>">Cancelar</a>
    </div>
    <div class="form-text mt-2">
        La operación es transaccional: si un producto no tiene stock suficiente, no se registra la venta.
    </div>
</form>

<?php parcial('pie-admin'); ?>
