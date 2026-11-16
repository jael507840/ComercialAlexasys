<?php
/** Módulo 7: listado de compras. */
parcial('head-admin', ['titulo' => $titulo, 'subtitulo' => $subtitulo]);
$puedeEscribir = puede_escribir('compras');
?>

<div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-4">
    <div>
        <h1 class="titulo-pagina h3 mb-1">Compras</h1>
        <p class="text-secondary mb-0 small">Cada compra registrada genera entradas de inventario por sus productos.</p>
    </div>
    <?php if ($puedeEscribir): ?>
        <a class="btn btn-primary btn-sm" href="<?= url('compras', ['accion' => 'nuevo']) ?>">
            <i class="fa-solid fa-plus me-1"></i> Registrar compra
        </a>
    <?php endif; ?>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="metrica py-3">
            <div class="etiqueta">Compras del periodo</div>
            <div class="valor fs-4"><?= (int) $cantidadPeriodo ?></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="metrica py-3">
            <div class="etiqueta">Total comprado</div>
            <div class="valor fs-5"><?= e(formato_moneda($totalPeriodo)) ?></div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header"><i class="fa-solid fa-filter me-2 text-primary"></i>Filtros</div>
    <div class="card-body">
        <form class="row g-2 align-items-end" method="get" action="<?= asset('index.php') ?>">
            <input type="hidden" name="ruta" value="compras">
            <div class="col-md-2">
                <label class="form-label small mb-1">Desde</label>
                <input class="form-control form-control-sm" type="date" name="desde" value="<?= e($desde) ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">Hasta</label>
                <input class="form-control form-control-sm" type="date" name="hasta" value="<?= e($hasta) ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label small mb-1">Proveedor</label>
                <select class="form-select form-select-sm" name="proveedor">
                    <option value="0">Todos los proveedores</option>
                    <?php foreach ($proveedores as $proveedor): ?>
                        <option value="<?= (int) $proveedor['id_proveedor'] ?>" <?= $idProveedor === (int) $proveedor['id_proveedor'] ? 'selected' : '' ?>>
                            <?= e($proveedor['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small mb-1">Buscar</label>
                <input class="form-control form-control-sm" type="search" name="q" value="<?= e($busqueda) ?>"
                       placeholder="Comprobante o proveedor">
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button class="btn btn-sm btn-primary flex-grow-1" type="submit"><i class="fa-solid fa-filter me-1"></i>Filtrar</button>
                <a class="btn btn-sm btn-outline-secondary" href="<?= url('compras') ?>"><i class="fa-solid fa-rotate-left"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fa-solid fa-file-invoice me-2 text-primary"></i>Compras registradas</span>
        <span class="small text-secondary"><?= count($compras) ?> resultado(s)</span>
    </div>
    <div class="tabla-scroll">
        <table class="table table-alex table-hover mb-0">
            <thead>
                <tr>
                    <th>Comprobante</th>
                    <th>Fecha</th>
                    <th>Proveedor</th>
                    <th class="text-center">Ítems</th>
                    <th class="text-end">Total</th>
                    <th>Registrado por</th>
                    <th>Estado</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($compras === []): ?>
                    <tr><td colspan="8" class="text-center text-secondary py-4">No se encontraron compras con los filtros aplicados.</td></tr>
                <?php endif; ?>
                <?php foreach ($compras as $compra): ?>
                    <tr>
                        <td class="fw-semibold"><?= e($compra['numero_comprobante']) ?></td>
                        <td class="small"><?= e(formato_fecha($compra['fecha_compra'], true)) ?></td>
                        <td class="small"><?= e($compra['proveedor']) ?></td>
                        <td class="text-center"><span class="badge text-bg-light border"><?= (int) $compra['total_items'] ?></span></td>
                        <td class="text-end fw-semibold"><?= e(formato_moneda($compra['total_compra'])) ?></td>
                        <td class="small text-secondary"><?= e($compra['usuario']) ?></td>
                        <td>
                            <span class="badge badge-estado text-bg-<?= $compra['estado'] === 'Registrada' ? 'success' : 'secondary' ?>">
                                <?= e($compra['estado']) ?>
                            </span>
                        </td>
                        <td class="text-end">
                            <a class="btn btn-sm btn-outline-primary" href="<?= url('compras', ['accion' => 'ver', 'id' => (int) $compra['id_compra']]) ?>">
                                <i class="fa-solid fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php parcial('pie-admin'); ?>
