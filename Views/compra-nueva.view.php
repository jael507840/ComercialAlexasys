<?php
/** Módulo 7: registrar compra. Variables: $proveedores, $productos, $formulario, $lineas, $errores. */
parcial('head-admin', ['titulo' => $titulo, 'subtitulo' => $subtitulo]);

$filaHtml = static function (int $indice, array $linea) use ($productos): string {
    ob_start();
    ?>
    <tr class="linea-compra">
        <td>
            <select class="form-select form-select-sm producto-compra-select" name="items[<?= $indice ?>][id_producto]">
                <option value="0">Seleccione un producto…</option>
                <?php foreach ($productos as $producto): ?>
                    <?php
                    $costo = $producto['costo_adquisicion'] !== null ? (float) $producto['costo_adquisicion'] : 0.0;
                    ?>
                    <option value="<?= (int) $producto['id_producto'] ?>"
                            data-costo="<?= e(number_format($costo, 2, '.', '')) ?>"
                            <?= (int) $linea['id_producto'] === (int) $producto['id_producto'] ? 'selected' : '' ?>>
                        <?= e($producto['codigo'] . ' · ' . $producto['nombre']) ?>
                        (stock <?= (int) $producto['stock_actual'] ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </td>
        <td style="width:110px">
            <input class="form-control form-control-sm text-end cantidad-input" type="number" min="1" step="1"
                   name="items[<?= $indice ?>][cantidad]" value="<?= (int) $linea['cantidad'] ?>">
        </td>
        <td style="width:140px">
            <input class="form-control form-control-sm text-end costo-input" type="number" min="0" step="0.01"
                   name="items[<?= $indice ?>][costo_unitario]" value="<?= e($linea['costo_unitario']) ?>">
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
        <h1 class="titulo-pagina h3 mb-1">Registrar compra</h1>
        <p class="text-secondary mb-0 small">El total se calcula automáticamente y el stock ingresa al confirmar.</p>
    </div>
    <a class="btn btn-outline-secondary btn-sm" href="<?= url('compras') ?>">
        <i class="fa-solid fa-arrow-left me-1"></i> Volver al listado
    </a>
</div>

<?php if ($errores !== []): ?>
    <div class="alert alert-danger">
        <div class="fw-semibold mb-1"><i class="fa-solid fa-circle-exclamation me-1"></i> No se registró la compra</div>
        <ul class="mb-0 ps-3"><?php foreach ($errores as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul>
    </div>
<?php endif; ?>

<?php if ($proveedores === [] || $productos === []): ?>
    <div class="alert alert-warning">
        <i class="fa-solid fa-triangle-exclamation me-1"></i>
        Para registrar una compra debe existir al menos un proveedor activo y un producto activo.
    </div>
<?php endif; ?>

<form method="post" action="<?= url('compras') ?>" novalidate>
    <?= csrf_field() ?>
    <input type="hidden" name="operacion" value="registrar">

    <div class="card mb-4">
        <div class="card-header"><i class="fa-solid fa-file-invoice me-2 text-primary"></i>Datos de la compra</div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label requerido" for="id_proveedor">Proveedor</label>
                    <select class="form-select" id="id_proveedor" name="id_proveedor" required>
                        <option value="0">Seleccione un proveedor…</option>
                        <?php foreach ($proveedores as $proveedor): ?>
                            <option value="<?= (int) $proveedor['id_proveedor'] ?>"
                                <?= (int) $formulario['id_proveedor'] === (int) $proveedor['id_proveedor'] ? 'selected' : '' ?>>
                                <?= e($proveedor['nombre'] . ($proveedor['nit'] ? ' · NIT ' . $proveedor['nit'] : '')) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label requerido" for="fecha_compra">Fecha</label>
                    <input class="form-control" id="fecha_compra" name="fecha_compra" type="date" required
                           max="<?= e(date('Y-m-d')) ?>" value="<?= e($formulario['fecha_compra']) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="numero_comprobante">Comprobante del proveedor</label>
                    <input class="form-control" id="numero_comprobante" name="numero_comprobante" maxlength="30"
                           placeholder="Factura, recibo o nota" value="<?= e($formulario['numero_comprobante']) ?>">
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
            <span><i class="fa-solid fa-boxes-stacked me-2 text-primary"></i>Productos comprados</span>
            <button class="btn btn-sm btn-outline-primary" type="button" id="btnAgregarLineaCompra">
                <i class="fa-solid fa-plus me-1"></i> Agregar línea
            </button>
        </div>
        <div class="tabla-scroll">
            <table class="table table-alex mb-0" id="lineasCompra" data-moneda="Bs">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th class="text-end">Cantidad</th>
                        <th class="text-end">Costo unitario (Bs)</th>
                        <th class="text-end">Subtotal</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($lineas as $indice => $linea): ?>
                        <?= $filaHtml((int) $indice, $linea) ?>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" class="text-end fw-bold">Total de la compra</td>
                        <td class="text-end fs-5 fw-bold text-primary" id="totalCompra">Bs 0.00</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <template id="plantillaLineaCompra">
        <?= $filaHtml(999, ['id_producto' => 0, 'cantidad' => 1, 'costo_unitario' => '0.00']) ?>
    </template>

    <div class="d-flex flex-wrap gap-2">
        <button class="btn btn-primary" type="submit" <?= ($proveedores === [] || $productos === []) ? 'disabled' : '' ?>>
            <i class="fa-solid fa-floppy-disk me-1"></i> Registrar compra e ingresar stock
        </button>
        <a class="btn btn-light border" href="<?= url('compras') ?>">Cancelar</a>
    </div>
    <div class="form-text mt-2">
        La operación es transaccional: si una línea falla, no se registra la compra ni se modifica el stock.
    </div>
</form>

<script>
    // Propone el último costo de compra al elegir un producto y en la primera línea.
    document.addEventListener('DOMContentLoaded', function () {
        const contenedor = document.getElementById('lineasCompra');
        if (!contenedor) return;

        function proponerCosto(select) {
            if (!select) return;
            const fila = select.closest('.linea-compra');
            if (!fila) return;
            const costo = fila.querySelector('.costo-input');
            if (!costo) return;
            const sugerido = select.selectedOptions[0]?.dataset.costo;
            if (sugerido !== undefined && (costo.value === '' || parseFloat(costo.value) === 0)) {
                costo.value = parseFloat(sugerido).toFixed(2);
                costo.dispatchEvent(new Event('input', { bubbles: true }));
            }
        }

        contenedor.addEventListener('change', function (evento) {
            if (evento.target.classList.contains('producto-compra-select')) {
                const costo = evento.target.closest('.linea-compra')?.querySelector('.costo-input');
                const sugerido = evento.target.selectedOptions[0]?.dataset.costo;
                if (costo && sugerido !== undefined && parseInt(evento.target.value, 10) > 0) {
                    costo.value = parseFloat(sugerido).toFixed(2);
                    costo.dispatchEvent(new Event('input', { bubbles: true }));
                }
            }
        });

        contenedor.querySelectorAll('.producto-compra-select').forEach(function (select) {
            if (parseInt(select.value, 10) > 0) proponerCosto(select);
        });
    });
</script>

<?php parcial('pie-admin'); ?>
