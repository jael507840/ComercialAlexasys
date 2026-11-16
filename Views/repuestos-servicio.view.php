<?php
/** Módulo 14: listado de repuestos usados. */
parcial('head-admin', ['titulo' => $titulo, 'subtitulo' => $subtitulo]);
$puedeEscribir = puede_escribir('repuestos-servicio');
?>

<div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-4">
    <div>
        <h1 class="titulo-pagina h3 mb-1">Repuestos usados</h1>
        <p class="text-secondary mb-0 small">Cada repuesto descuenta stock y suma al total de la orden de servicio.</p>
    </div>
    <?php if ($puedeEscribir): ?>
        <a class="btn btn-primary btn-sm" href="<?= url('repuestos-servicio', ['accion' => 'nuevo']) ?>">
            <i class="fa-solid fa-plus me-1"></i> Registrar repuesto
        </a>
    <?php endif; ?>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="metrica py-3">
            <div class="etiqueta">Repuestos listados</div>
            <div class="valor fs-4"><?= count($repuestos) ?></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="metrica py-3">
            <div class="etiqueta">Costo acumulado</div>
            <div class="valor fs-5"><?= e(formato_moneda($totalRepuestos)) ?></div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header"><i class="fa-solid fa-filter me-2 text-primary"></i>Filtros</div>
    <div class="card-body">
        <form class="row g-2 align-items-end" method="get" action="<?= asset('index.php') ?>">
            <input type="hidden" name="ruta" value="repuestos-servicio">
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
                <a class="btn btn-sm btn-outline-secondary" href="<?= url('repuestos-servicio') ?>"><i class="fa-solid fa-rotate-left"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fa-solid fa-gears me-2 text-primary"></i>Repuestos registrados</span>
        <span class="small text-secondary"><?= count($repuestos) ?> registro(s)</span>
    </div>
    <div class="tabla-scroll">
        <table class="table table-alex table-hover mb-0">
            <thead>
                <tr>
                    <th>Orden</th>
                    <th>Producto</th>
                    <th class="text-end">Cantidad</th>
                    <th class="text-end">Costo unitario</th>
                    <th class="text-end">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($repuestos === []): ?>
                    <tr><td colspan="5" class="text-center text-secondary py-4">No hay repuestos registrados en el periodo.</td></tr>
                <?php endif; ?>
                <?php foreach ($repuestos as $repuesto): ?>
                    <tr>
                        <td>
                            <a class="fw-semibold" href="<?= url('ordenes-servicio', ['accion' => 'ver', 'id' => (int) $repuesto['id_orden']]) ?>">
                                <?= e($repuesto['numero_orden']) ?>
                            </a>
                        </td>
                        <td class="small">
                            <?= e($repuesto['nombre']) ?>
                            <span class="text-secondary">(<?= e($repuesto['codigo']) ?>)</span>
                        </td>
                        <td class="text-end"><?= (int) $repuesto['cantidad'] ?></td>
                        <td class="text-end"><?= e(formato_moneda($repuesto['costo_unitario'])) ?></td>
                        <td class="text-end fw-semibold"><?= e(formato_moneda($repuesto['subtotal'])) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php parcial('pie-admin'); ?>
