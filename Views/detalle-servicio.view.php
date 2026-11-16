<?php
/** Módulo 13: listado de intervenciones. */
parcial('head-admin', ['titulo' => $titulo, 'subtitulo' => $subtitulo]);
$puedeEscribir = puede_escribir('detalle-servicio');
?>

<div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-4">
    <div>
        <h1 class="titulo-pagina h3 mb-1">Intervenciones</h1>
        <p class="text-secondary mb-0 small">Diagnóstico y trabajo realizado en cada orden de servicio.</p>
    </div>
    <?php if ($puedeEscribir): ?>
        <a class="btn btn-primary btn-sm" href="<?= url('detalle-servicio', ['accion' => 'nuevo']) ?>">
            <i class="fa-solid fa-plus me-1"></i> Nueva intervención
        </a>
    <?php endif; ?>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="metrica py-3">
            <div class="etiqueta">Intervenciones listadas</div>
            <div class="valor fs-4"><?= count($intervenciones) ?></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="metrica py-3">
            <div class="etiqueta">Costo acumulado</div>
            <div class="valor fs-5"><?= e(formato_moneda($totalCosto)) ?></div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header"><i class="fa-solid fa-filter me-2 text-primary"></i>Filtros</div>
    <div class="card-body">
        <form class="row g-2 align-items-end" method="get" action="<?= asset('index.php') ?>">
            <input type="hidden" name="ruta" value="detalle-servicio">
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
                <a class="btn btn-sm btn-outline-secondary" href="<?= url('detalle-servicio') ?>"><i class="fa-solid fa-rotate-left"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fa-solid fa-clipboard-check me-2 text-primary"></i>Historial de intervenciones</span>
        <span class="small text-secondary"><?= count($intervenciones) ?> registro(s)</span>
    </div>
    <div class="tabla-scroll">
        <table class="table table-alex table-hover mb-0">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Orden</th>
                    <th>Cliente</th>
                    <th>Técnico</th>
                    <th>Diagnóstico</th>
                    <th>Trabajo realizado</th>
                    <th class="text-end">Costo</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($intervenciones === []): ?>
                    <tr><td colspan="7" class="text-center text-secondary py-4">No se encontraron intervenciones con los filtros aplicados.</td></tr>
                <?php endif; ?>
                <?php foreach ($intervenciones as $intervencion): ?>
                    <tr>
                        <td class="small"><?= e(formato_fecha($intervencion['fecha_intervencion'], true)) ?></td>
                        <td>
                            <a class="fw-semibold" href="<?= url('ordenes-servicio', ['accion' => 'ver', 'id' => (int) $intervencion['id_orden']]) ?>">
                                <?= e($intervencion['numero_orden']) ?>
                            </a>
                        </td>
                        <td class="small"><?= e($intervencion['cliente']) ?></td>
                        <td class="small"><?= e($intervencion['tecnico']) ?></td>
                        <td class="small"><?= e(mb_strimwidth($intervencion['diagnostico'], 0, 80, '…')) ?></td>
                        <td class="small"><?= e(mb_strimwidth($intervencion['trabajo_realizado'], 0, 80, '…')) ?></td>
                        <td class="text-end fw-semibold"><?= e(formato_moneda($intervencion['costo'])) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php parcial('pie-admin'); ?>
