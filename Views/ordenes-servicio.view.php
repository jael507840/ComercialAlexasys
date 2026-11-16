<?php
/** Módulo 12: listado de órdenes de servicio. */
parcial('head-admin', ['titulo' => $titulo, 'subtitulo' => $subtitulo]);
$puedeEscribir = puede_escribir('ordenes-servicio');
$totalOrdenes = (int) ($resumen['total_ordenes'] ?? 0);
$manoObra = (float) ($resumen['mano_obra'] ?? 0);
?>

<div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-4">
    <div>
        <h1 class="titulo-pagina h3 mb-1">Órdenes de servicio</h1>
        <p class="text-secondary mb-0 small">Recepción, diagnóstico, reparación y entrega de equipos de clientes.</p>
    </div>
    <?php if ($puedeEscribir): ?>
        <a class="btn btn-primary btn-sm" href="<?= url('ordenes-servicio', ['accion' => 'nuevo']) ?>">
            <i class="fa-solid fa-plus me-1"></i> Nueva orden
        </a>
    <?php endif; ?>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="metrica py-3">
            <div class="etiqueta">Órdenes del periodo</div>
            <div class="valor fs-4"><?= $totalOrdenes ?></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="metrica py-3">
            <div class="etiqueta">Mano de obra facturada</div>
            <div class="valor fs-5"><?= e(formato_moneda($manoObra)) ?></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="metrica py-3">
            <div class="etiqueta">Resultados listados</div>
            <div class="valor fs-4"><?= count($ordenes) ?></div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header"><i class="fa-solid fa-filter me-2 text-primary"></i>Filtros</div>
    <div class="card-body">
        <form class="row g-2 align-items-end" method="get" action="<?= asset('index.php') ?>">
            <input type="hidden" name="ruta" value="ordenes-servicio">
            <div class="col-md-3">
                <label class="form-label small mb-1">Buscar</label>
                <input class="form-control form-control-sm" type="search" name="q" value="<?= e($busqueda) ?>"
                       placeholder="Orden, CI, cliente, serie o marca">
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">Estado</label>
                <select class="form-select form-select-sm" name="estado">
                    <option value="">Todos</option>
                    <?php foreach (OrdenServicio::ESTADOS as $opcion): ?>
                        <option value="<?= e($opcion) ?>" <?= $estado === $opcion ? 'selected' : '' ?>>
                            <?= e(etiqueta_estado($opcion)) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">Técnico</label>
                <select class="form-select form-select-sm" name="tecnico">
                    <option value="0">Todos</option>
                    <?php foreach ($tecnicos as $tecnico): ?>
                        <option value="<?= (int) $tecnico['id_usuario'] ?>" <?= $idTecnico === (int) $tecnico['id_usuario'] ? 'selected' : '' ?>>
                            <?= e(nombre_completo($tecnico)) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">Desde</label>
                <input class="form-control form-control-sm" type="date" name="desde" value="<?= e($desde) ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">Hasta</label>
                <input class="form-control form-control-sm" type="date" name="hasta" value="<?= e($hasta) ?>">
            </div>
            <div class="col-md-1 d-flex gap-1">
                <button class="btn btn-sm btn-primary flex-grow-1" type="submit" title="Filtrar"><i class="fa-solid fa-filter"></i></button>
                <a class="btn btn-sm btn-outline-secondary" href="<?= url('ordenes-servicio') ?>" title="Limpiar"><i class="fa-solid fa-rotate-left"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fa-solid fa-screwdriver-wrench me-2 text-primary"></i>Órdenes registradas</span>
        <span class="small text-secondary"><?= count($ordenes) ?> resultado(s)</span>
    </div>
    <div class="tabla-scroll">
        <table class="table table-alex table-hover mb-0">
            <thead>
                <tr>
                    <th>Orden</th>
                    <th>Recepción</th>
                    <th>Cliente / equipo</th>
                    <th>Técnico</th>
                    <th>Estado</th>
                    <th class="text-end">Total</th>
                    <th class="text-end">Saldo</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($ordenes === []): ?>
                    <tr><td colspan="8" class="text-center text-secondary py-4">No se encontraron órdenes con los filtros aplicados.</td></tr>
                <?php endif; ?>
                <?php foreach ($ordenes as $orden): ?>
                    <?php $saldo = (float) $orden['saldo']; ?>
                    <tr>
                        <td class="fw-semibold"><?= e($orden['numero_orden']) ?></td>
                        <td class="small"><?= e(formato_fecha($orden['fecha_recepcion'], true)) ?></td>
                        <td class="small">
                            <?= e($orden['cliente']) ?>
                            <div class="text-secondary" style="font-size:.72rem">
                                <?= e(trim(($orden['marca'] ?? '') . ' ' . ($orden['modelo'] ?? ''))) ?>
                                <?= $orden['numero_serie'] ? '· ' . e($orden['numero_serie']) : '' ?>
                            </div>
                        </td>
                        <td class="small"><?= e($orden['tecnico'] ?: 'Sin asignar') ?></td>
                        <td>
                            <span class="badge badge-estado text-bg-<?= clase_estado($orden['estado']) ?>">
                                <?= e(etiqueta_estado($orden['estado'])) ?>
                            </span>
                        </td>
                        <td class="text-end fw-semibold"><?= e(formato_moneda($orden['total'])) ?></td>
                        <td class="text-end <?= $saldo > 0.009 ? 'text-danger fw-semibold' : 'text-success' ?>">
                            <?= e(formato_moneda($saldo)) ?>
                        </td>
                        <td class="text-end">
                            <div class="acciones-tabla">
                                <a class="btn btn-sm btn-outline-primary" title="Ver detalle"
                                   href="<?= url('ordenes-servicio', ['accion' => 'ver', 'id' => (int) $orden['id_orden']]) ?>">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                                <?php if ($puedeEscribir && puede_escribir('pagos-servicio') && $saldo > 0.009 && $orden['estado'] !== 'Anulado'): ?>
                                    <a class="btn btn-sm btn-outline-success" title="Registrar pago"
                                       href="<?= url('pagos-servicio', ['accion' => 'nuevo', 'orden' => (int) $orden['id_orden']]) ?>">
                                        <i class="fa-solid fa-hand-holding-dollar"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php parcial('pie-admin'); ?>
