<?php
/** Módulo 11: detalle del equipo. Variables: $equipo, $ordenes. */
parcial('head-admin', ['titulo' => $titulo, 'subtitulo' => $subtitulo]);
?>

<div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-4">
    <div>
        <h1 class="titulo-pagina h3 mb-1">
            <?= e(trim(($equipo['marca'] ?? '') . ' ' . ($equipo['modelo'] ?? '')) ?: 'Equipo sin marca') ?>
        </h1>
        <p class="text-secondary mb-0 small">
            <?= e($equipo['tipo_equipo']) ?> ·
            Serie <?= e($equipo['numero_serie'] ?: 'sin registrar') ?> ·
            <?= e($equipo['cliente']) ?> (CI <?= e($equipo['ci']) ?>)
        </p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <?php if (puede_escribir('ordenes-servicio')): ?>
            <a class="btn btn-primary btn-sm" href="<?= url('ordenes-servicio', ['accion' => 'nuevo', 'equipo' => (int) $equipo['id_equipo']]) ?>">
                <i class="fa-solid fa-screwdriver-wrench me-1"></i> Nueva orden
            </a>
        <?php endif; ?>
        <a class="btn btn-outline-secondary btn-sm" href="<?= url('equipos') ?>">
            <i class="fa-solid fa-arrow-left me-1"></i> Volver
        </a>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="metrica py-3">
            <div class="etiqueta">Órdenes de servicio</div>
            <div class="valor fs-4"><?= count($ordenes) ?></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="metrica py-3">
            <div class="etiqueta">Teléfono del cliente</div>
            <div class="valor fs-6 mt-1"><?= e($equipo['telefono'] ?: '—') ?></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="metrica py-3">
            <div class="etiqueta">Registrado</div>
            <div class="valor fs-6 mt-1"><?= e(formato_fecha($equipo['fecha_registro'], true)) ?></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="metrica py-3">
            <div class="etiqueta">Producto asociado</div>
            <div class="valor fs-6 mt-1"><?= e($equipo['producto'] ? $equipo['producto_codigo'] . ' · ' . $equipo['producto'] : 'Ninguno') ?></div>
        </div>
    </div>
</div>

<?php if (!empty($equipo['descripcion'])): ?>
    <div class="alert alert-light border small">
        <i class="fa-solid fa-circle-info me-1"></i><?= e($equipo['descripcion']) ?>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fa-solid fa-screwdriver-wrench me-2 text-primary"></i>Historial de órdenes</span>
        <span class="small text-secondary"><?= count($ordenes) ?> orden(es)</span>
    </div>
    <div class="tabla-scroll">
        <table class="table table-alex mb-0">
            <thead>
                <tr>
                    <th>Orden</th>
                    <th>Recepción</th>
                    <th>Falla reportada</th>
                    <th>Técnico</th>
                    <th>Estado</th>
                    <th class="text-end">Costo servicio</th>
                    <th class="text-end"></th>
                </tr>
            </thead>
            <tbody>
                <?php if ($ordenes === []): ?>
                    <tr><td colspan="7" class="text-center text-secondary py-4">El equipo aún no tiene órdenes de servicio.</td></tr>
                <?php endif; ?>
                <?php foreach ($ordenes as $orden): ?>
                    <tr>
                        <td class="fw-semibold"><?= e($orden['numero_orden']) ?></td>
                        <td class="small"><?= e(formato_fecha($orden['fecha_recepcion'], true)) ?></td>
                        <td class="small"><?= e(mb_strimwidth($orden['falla_reportada'], 0, 70, '…')) ?></td>
                        <td class="small"><?= e($orden['tecnico'] ?: 'Sin asignar') ?></td>
                        <td>
                            <span class="badge badge-estado text-bg-<?= clase_estado($orden['estado']) ?>">
                                <?= e(etiqueta_estado($orden['estado'])) ?>
                            </span>
                        </td>
                        <td class="text-end fw-semibold"><?= e(formato_moneda($orden['costo_servicio'])) ?></td>
                        <td class="text-end">
                            <a class="btn btn-sm btn-outline-primary"
                               href="<?= url('ordenes-servicio', ['accion' => 'ver', 'id' => (int) $orden['id_orden']]) ?>">
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
