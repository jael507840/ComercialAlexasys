<?php
/** Historial del proveedor. Variables: $proveedor, $compras. */
parcial('head-admin', ['titulo' => $titulo, 'subtitulo' => $subtitulo]);
$totalComprado = 0.0;
foreach ($compras as $compra) {
    if ($compra['estado'] === 'Registrada') {
        $totalComprado += (float) $compra['total_compra'];
    }
}
?>

<div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-4">
    <div>
        <h1 class="titulo-pagina h3 mb-1"><?= e($proveedor['nombre']) ?></h1>
        <p class="text-secondary mb-0 small">
            <?= e($proveedor['nit'] ? 'NIT ' . $proveedor['nit'] : 'Sin NIT registrado') ?> ·
            <?= e($proveedor['telefono'] ?: 'Sin teléfono') ?> ·
            <?= e($proveedor['correo'] ?: 'Sin correo') ?>
        </p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <?php if (puede_escribir('compras') && $proveedor['estado'] === 'Activo'): ?>
            <a class="btn btn-primary btn-sm" href="<?= url('compras', ['accion' => 'nuevo', 'proveedor' => (int) $proveedor['id_proveedor']]) ?>">
                <i class="fa-solid fa-file-invoice me-1"></i> Registrar compra
            </a>
        <?php endif; ?>
        <a class="btn btn-outline-secondary btn-sm" href="<?= url('proveedores') ?>">
            <i class="fa-solid fa-arrow-left me-1"></i> Volver
        </a>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="metrica py-3">
            <div class="etiqueta">Compras registradas</div>
            <div class="valor fs-4"><?= count($compras) ?></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="metrica py-3">
            <div class="etiqueta">Total comprado</div>
            <div class="valor fs-5"><?= e(formato_moneda($totalComprado)) ?></div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="metrica py-3">
            <div class="etiqueta">Estado</div>
            <div class="mt-1">
                <span class="badge badge-estado text-bg-<?= $proveedor['estado'] === 'Activo' ? 'success' : 'secondary' ?>">
                    <?= e($proveedor['estado']) ?>
                </span>
            </div>
            <div class="small text-secondary mt-2"><?= e($proveedor['direccion'] ?: 'Sin dirección registrada') ?></div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fa-solid fa-file-invoice me-2 text-primary"></i>Historial de compras</span>
        <span class="small text-secondary"><?= count($compras) ?> registro(s)</span>
    </div>
    <div class="tabla-scroll">
        <table class="table table-alex mb-0">
            <thead>
                <tr>
                    <th>Comprobante</th>
                    <th>Fecha</th>
                    <th>Registrado por</th>
                    <th class="text-end">Total</th>
                    <th>Estado</th>
                    <th class="text-end"></th>
                </tr>
            </thead>
            <tbody>
                <?php if ($compras === []): ?>
                    <tr><td colspan="6" class="text-center text-secondary py-4">El proveedor aún no tiene compras registradas.</td></tr>
                <?php endif; ?>
                <?php foreach ($compras as $compra): ?>
                    <tr>
                        <td class="fw-semibold"><?= e($compra['numero_comprobante']) ?></td>
                        <td class="small"><?= e(formato_fecha($compra['fecha_compra'], true)) ?></td>
                        <td class="small"><?= e(nombre_completo($compra)) ?></td>
                        <td class="text-end fw-semibold"><?= e(formato_moneda($compra['total_compra'])) ?></td>
                        <td>
                            <span class="badge badge-estado text-bg-<?= $compra['estado'] === 'Registrada' ? 'success' : 'secondary' ?>">
                                <?= e($compra['estado']) ?>
                            </span>
                        </td>
                        <td class="text-end">
                            <a class="btn btn-sm btn-outline-primary"
                               href="<?= url('compras', ['accion' => 'ver', 'id' => (int) $compra['id_compra']]) ?>">
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
