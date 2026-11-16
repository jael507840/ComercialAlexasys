<?php
/** Módulo 8: inventario. */
parcial('head-admin', ['titulo' => $titulo, 'subtitulo' => $subtitulo]);
$puedeAjustar = puede_escribir('inventario');
$entradas = (int) ($resumen['entradas'] ?? 0);
$salidas = (int) ($resumen['salidas'] ?? 0);
$totalMovimientos = (int) ($resumen['total_movimientos'] ?? 0);
?>

<div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-4">
    <div>
        <h1 class="titulo-pagina h3 mb-1">Inventario</h1>
        <p class="text-secondary mb-0 small">
            Todo cambio de stock queda registrado con su documento de origen (compra, venta, repuesto o ajuste).
        </p>
    </div>
    <a class="btn btn-outline-primary btn-sm" href="<?= url('productos') ?>">
        <i class="fa-solid fa-box me-1"></i> Ver catálogo
    </a>
</div>

<?php if ($errores !== []): ?>
    <div class="alert alert-danger">
        <div class="fw-semibold mb-1"><i class="fa-solid fa-circle-exclamation me-1"></i> No se registró el ajuste</div>
        <ul class="mb-0 ps-3"><?php foreach ($errores as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul>
    </div>
<?php endif; ?>

<?php if ($descuadres !== []): ?>
    <div class="alert alert-danger">
        <div class="fw-semibold mb-1"><i class="fa-solid fa-triangle-exclamation me-1"></i> Se detectaron diferencias de stock</div>
        <p class="small mb-2">
            Los siguientes productos tienen un stock almacenado distinto a la suma de sus movimientos.
            Revise el detalle antes de operar; no se corrige automáticamente.
        </p>
        <ul class="mb-0 ps-3 small">
            <?php foreach (array_slice($descuadres, 0, 10) as $fila): ?>
                <li>
                    <?= e($fila['codigo'] . ' · ' . $fila['nombre']) ?>:
                    stock <?= (int) $fila['stock_actual'] ?>,
                    movimientos <?= (int) $fila['entradas'] - (int) $fila['salidas'] ?>
                    (diferencia <?= (int) $fila['diferencia'] ?>)
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php else: ?>
    <div class="alert alert-success d-flex align-items-center gap-2">
        <i class="fa-solid fa-circle-check"></i>
        <span class="small">Verificación correcta: el stock de todos los productos coincide con la suma de sus movimientos.</span>
    </div>
<?php endif; ?>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="metrica py-3">
            <div class="etiqueta">Entradas del periodo</div>
            <div class="valor fs-4 text-success"><?= $entradas ?></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="metrica py-3">
            <div class="etiqueta">Salidas del periodo</div>
            <div class="valor fs-4 text-danger"><?= $salidas ?></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="metrica py-3">
            <div class="etiqueta">Movimientos</div>
            <div class="valor fs-4"><?= $totalMovimientos ?></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="metrica py-3">
            <div class="etiqueta">Productos verificados</div>
            <div class="valor fs-4"><?= count($verificacion) ?></div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-<?= $puedeAjustar ? '7' : '12' ?>">
        <div class="card h-100">
            <div class="card-header"><i class="fa-solid fa-filter me-2 text-primary"></i>Filtros de movimientos</div>
            <div class="card-body">
                <form class="row g-2 align-items-end" method="get" action="<?= asset('index.php') ?>">
                    <input type="hidden" name="ruta" value="inventario">
                    <div class="col-md-4">
                        <label class="form-label small mb-1">Producto</label>
                        <select class="form-select form-select-sm" name="producto">
                            <option value="0">Todos los productos</option>
                            <?php foreach ($productos as $producto): ?>
                                <option value="<?= (int) $producto['id_producto'] ?>" <?= $idProducto === (int) $producto['id_producto'] ? 'selected' : '' ?>>
                                    <?= e($producto['codigo'] . ' · ' . $producto['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small mb-1">Tipo</label>
                        <select class="form-select form-select-sm" name="tipo">
                            <option value="">Todos</option>
                            <option value="Entrada" <?= $tipo === 'Entrada' ? 'selected' : '' ?>>Entrada</option>
                            <option value="Salida" <?= $tipo === 'Salida' ? 'selected' : '' ?>>Salida</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small mb-1">Origen</label>
                        <select class="form-select form-select-sm" name="referencia">
                            <option value="">Todos</option>
                            <?php foreach (MovimientoInventario::REFERENCIAS as $origen): ?>
                                <option value="<?= e($origen) ?>" <?= $referencia === $origen ? 'selected' : '' ?>><?= e($origen) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small mb-1">Desde</label>
                        <input class="form-control form-control-sm" type="date" name="desde" value="<?= e($desde) ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small mb-1">Hasta</label>
                        <input class="form-control form-control-sm" type="date" name="hasta" value="<?= e($hasta) ?>">
                    </div>
                    <div class="col-md-3 d-flex gap-2">
                        <button class="btn btn-sm btn-primary flex-grow-1" type="submit"><i class="fa-solid fa-filter me-1"></i>Filtrar</button>
                        <a class="btn btn-sm btn-outline-secondary" href="<?= url('inventario') ?>"><i class="fa-solid fa-rotate-left"></i></a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php if ($puedeAjustar): ?>
        <div class="col-lg-5">
            <div class="card h-100">
                <div class="card-header"><i class="fa-solid fa-sliders me-2 text-warning"></i>Ajuste manual de stock</div>
                <div class="card-body">
                    <form method="post" action="<?= url('inventario') ?>" novalidate>
                        <?= csrf_field() ?>
                        <input type="hidden" name="operacion" value="ajustar">
                        <div class="mb-2">
                            <label class="form-label small mb-1 requerido" for="id_producto">Producto</label>
                            <select class="form-select form-select-sm" id="id_producto" name="id_producto" required>
                                <option value="0">Seleccione un producto…</option>
                                <?php foreach ($productos as $producto): ?>
                                    <option value="<?= (int) $producto['id_producto'] ?>">
                                        <?= e($producto['nombre'] . ' · stock ' . (int) $producto['stock_actual']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="row g-2 mb-2">
                            <div class="col-5">
                                <label class="form-label small mb-1 requerido" for="nuevo_stock">Nuevo stock</label>
                                <input class="form-control form-control-sm" id="nuevo_stock" name="nuevo_stock"
                                       type="number" min="0" step="1" required>
                            </div>
                            <div class="col-7">
                                <label class="form-label small mb-1 requerido" for="observacion">Motivo</label>
                                <input class="form-control form-control-sm" id="observacion" name="observacion"
                                       maxlength="300" required placeholder="Conteo físico, merma, corrección…">
                            </div>
                        </div>
                        <button class="btn btn-sm btn-warning" type="submit">
                            <i class="fa-solid fa-check me-1"></i> Registrar ajuste
                        </button>
                        <div class="form-text mt-2">
                            El ajuste genera automáticamente una entrada o salida con origen <strong>Ajuste</strong>.
                            No se permite dejar el stock en negativo.
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fa-solid fa-arrow-right-arrow-left me-2 text-primary"></i>Historial de movimientos</span>
        <span class="small text-secondary"><?= count($movimientos) ?> registro(s)</span>
    </div>
    <div class="tabla-scroll">
        <table class="table table-alex table-hover mb-0">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Producto</th>
                    <th>Tipo</th>
                    <th class="text-end">Cantidad</th>
                    <th>Origen</th>
                    <th>Documento</th>
                    <th>Registrado por</th>
                    <th>Observación</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($movimientos === []): ?>
                    <tr><td colspan="8" class="text-center text-secondary py-4">No se encontraron movimientos con los filtros aplicados.</td></tr>
                <?php endif; ?>
                <?php foreach ($movimientos as $movimiento): ?>
                    <tr>
                        <td class="small"><?= e(formato_fecha($movimiento['fecha_movimiento'], true)) ?></td>
                        <td class="small">
                            <span class="fw-semibold"><?= e($movimiento['producto']) ?></span>
                            <div class="text-secondary" style="font-size:.72rem"><?= e($movimiento['codigo']) ?></div>
                        </td>
                        <td>
                            <span class="badge badge-estado text-bg-<?= $movimiento['tipo_movimiento'] === 'Entrada' ? 'success' : 'danger' ?>">
                                <?= e($movimiento['tipo_movimiento']) ?>
                            </span>
                        </td>
                        <td class="text-end fw-semibold"><?= (int) $movimiento['cantidad'] ?></td>
                        <td class="small"><?= e($movimiento['referencia_tipo']) ?></td>
                        <td class="small text-secondary">
                            <?php if ($movimiento['referencia_tipo'] === 'Compra' && $movimiento['referencia_id']): ?>
                                <a href="<?= url('compras', ['accion' => 'ver', 'id' => (int) $movimiento['referencia_id']]) ?>">
                                    #<?= (int) $movimiento['referencia_id'] ?>
                                </a>
                            <?php elseif ($movimiento['referencia_tipo'] === 'Venta' && $movimiento['referencia_id']): ?>
                                <a href="<?= url('ventas', ['accion' => 'ver', 'id' => (int) $movimiento['referencia_id']]) ?>">
                                    #<?= (int) $movimiento['referencia_id'] ?>
                                </a>
                            <?php elseif ($movimiento['referencia_tipo'] === 'Repuesto' && $movimiento['referencia_id']): ?>
                                <a href="<?= url('ordenes-servicio', ['accion' => 'ver', 'id' => (int) $movimiento['referencia_id']]) ?>">
                                    Orden #<?= (int) $movimiento['referencia_id'] ?>
                                </a>
                            <?php else: ?>
                                —
                            <?php endif; ?>
                        </td>
                        <td class="small text-secondary"><?= e($movimiento['usuario']) ?></td>
                        <td class="small text-secondary"><?= e($movimiento['observacion'] ?: '—') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white small text-secondary">
        <i class="fa-solid fa-circle-info me-1"></i>
        Se muestran hasta 300 movimientos según los filtros aplicados.
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fa-solid fa-clipboard-check me-2 text-primary"></i>Verificación de stock</span>
        <span class="badge text-bg-<?= $descuadres === [] ? 'success' : 'danger' ?>">
            <?= $descuadres === [] ? 'Sin diferencias' : count($descuadres) . ' diferencia(s)' ?>
        </span>
    </div>
    <div class="tabla-scroll">
        <table class="table table-alex mb-0">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th class="text-end">Stock actual</th>
                    <th class="text-end">Entradas</th>
                    <th class="text-end">Salidas</th>
                    <th class="text-end">Calculado</th>
                    <th class="text-end">Diferencia</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($verificacion as $fila): ?>
                    <?php $diferencia = (int) $fila['diferencia']; ?>
                    <tr class="<?= $diferencia !== 0 ? 'table-danger' : '' ?>">
                        <td class="small">
                            <span class="fw-semibold"><?= e($fila['nombre']) ?></span>
                            <span class="text-secondary">(<?= e($fila['codigo']) ?>)</span>
                        </td>
                        <td class="text-end"><?= (int) $fila['stock_actual'] ?></td>
                        <td class="text-end text-success"><?= (int) $fila['entradas'] ?></td>
                        <td class="text-end text-danger"><?= (int) $fila['salidas'] ?></td>
                        <td class="text-end"><?= (int) $fila['entradas'] - (int) $fila['salidas'] ?></td>
                        <td class="text-end fw-bold <?= $diferencia !== 0 ? 'text-danger' : 'text-success' ?>">
                            <?= $diferencia ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php parcial('pie-admin'); ?>
