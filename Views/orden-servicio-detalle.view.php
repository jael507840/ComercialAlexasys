<?php
/** Módulo 12: detalle de la orden de servicio. */
parcial('head-admin', ['titulo' => $titulo, 'subtitulo' => $subtitulo]);
$editable = puede_escribir('ordenes-servicio');
$cerrada = in_array($orden['estado'], ['Entregado', 'Anulado'], true);
$tieneIntervenciones = $intervenciones !== [];
$saldo = (float) $estadoPago['saldo'];
$totalRepuestos = 0.0;
foreach ($repuestos as $repuesto) {
    $totalRepuestos += (float) $repuesto['subtotal'];
}
?>

<div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-4">
    <div>
        <h1 class="titulo-pagina h3 mb-1">Orden <?= e($orden['numero_orden']) ?></h1>
        <p class="text-secondary mb-0 small">
            <?= e(formato_fecha($orden['fecha_recepcion'], true)) ?> ·
            <?= e($orden['cliente']) ?> (CI <?= e($orden['ci']) ?>) ·
            <?= e(trim(($orden['marca'] ?? '') . ' ' . ($orden['modelo'] ?? ''))) ?>
        </p>
    </div>
    <div class="d-flex flex-wrap gap-2 no-imprimir">
        <?php if (!$cerrada && puede_escribir('detalle-servicio')): ?>
            <a class="btn btn-outline-primary btn-sm" href="<?= url('detalle-servicio', ['accion' => 'nuevo', 'orden' => (int) $orden['id_orden']]) ?>">
                <i class="fa-solid fa-clipboard-check me-1"></i> Intervención
            </a>
        <?php endif; ?>
        <?php if (!$cerrada && puede_escribir('repuestos-servicio')): ?>
            <a class="btn btn-outline-primary btn-sm" href="<?= url('repuestos-servicio', ['accion' => 'nuevo', 'orden' => (int) $orden['id_orden']]) ?>">
                <i class="fa-solid fa-gears me-1"></i> Repuesto
            </a>
        <?php endif; ?>
        <?php if ($saldo > 0.009 && $orden['estado'] !== 'Anulado' && puede_escribir('pagos-servicio')): ?>
            <a class="btn btn-success btn-sm" href="<?= url('pagos-servicio', ['accion' => 'nuevo', 'orden' => (int) $orden['id_orden']]) ?>">
                <i class="fa-solid fa-hand-holding-dollar me-1"></i> Pago
            </a>
        <?php endif; ?>
        <?php if ($orden['estado'] === 'Terminado' || $orden['estado'] === 'Entregado'): ?>
            <?php if (puede_escribir('garantias')): ?>
                <a class="btn btn-outline-warning btn-sm" href="<?= url('garantias', ['accion' => 'nuevo', 'orden' => (int) $orden['id_orden']]) ?>">
                    <i class="fa-solid fa-shield-halved me-1"></i> Garantía
                </a>
            <?php endif; ?>
        <?php endif; ?>
        <button class="btn btn-outline-secondary btn-sm" type="button" onclick="window.print()">
            <i class="fa-solid fa-print me-1"></i> Imprimir
        </button>
        <a class="btn btn-outline-secondary btn-sm" href="<?= url('ordenes-servicio') ?>">
            <i class="fa-solid fa-arrow-left me-1"></i> Volver
        </a>
    </div>
</div>

<?php if ($errores !== []): ?>
    <div class="alert alert-danger">
        <div class="fw-semibold mb-1"><i class="fa-solid fa-circle-exclamation me-1"></i> No se actualizó la orden</div>
        <ul class="mb-0 ps-3"><?php foreach ($errores as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul>
    </div>
<?php endif; ?>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="metrica py-3">
            <div class="etiqueta">Total de la orden</div>
            <div class="valor fs-5"><?= e(formato_moneda($estadoPago['total'])) ?></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="metrica py-3">
            <div class="etiqueta">Pagado</div>
            <div class="valor fs-5 text-success"><?= e(formato_moneda($estadoPago['pagado'])) ?></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="metrica py-3">
            <div class="etiqueta">Saldo</div>
            <div class="valor fs-5 <?= $saldo > 0.009 ? 'text-danger' : 'text-success' ?>"><?= e(formato_moneda($saldo)) ?></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="metrica py-3">
            <div class="etiqueta">Estado</div>
            <div class="mt-1">
                <span class="badge badge-estado text-bg-<?= clase_estado($orden['estado']) ?>">
                    <?= e(etiqueta_estado($orden['estado'])) ?>
                </span>
                <span class="badge badge-estado text-bg-<?= $estadoPago['estado'] === 'Pagado' ? 'success' : ($estadoPago['estado'] === 'Parcial' ? 'warning' : 'secondary') ?>">
                    <?= e($estadoPago['estado']) ?>
                </span>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-7">
        <div class="card h-100">
            <div class="card-header"><i class="fa-solid fa-laptop-medical me-2 text-primary"></i>Datos del equipo y recepción</div>
            <div class="card-body">
                <dl class="row mb-0 small">
                    <dt class="col-4 text-secondary fw-normal">Equipo</dt>
                    <dd class="col-8"><?= e(($orden['tipo_equipo'] ?? '') . ' · ' . trim(($orden['marca'] ?? '') . ' ' . ($orden['modelo'] ?? ''))) ?></dd>
                    <dt class="col-4 text-secondary fw-normal">Número de serie</dt>
                    <dd class="col-8"><?= e($orden['numero_serie'] ?: '—') ?></dd>
                    <dt class="col-4 text-secondary fw-normal">Receptor</dt>
                    <dd class="col-8"><?= e($orden['receptor']) ?></dd>
                    <dt class="col-4 text-secondary fw-normal">Técnico</dt>
                    <dd class="col-8"><?= e($orden['tecnico'] ?: 'Sin asignar') ?></dd>
                    <dt class="col-4 text-secondary fw-normal">Accesorios</dt>
                    <dd class="col-8"><?= e($orden['accesorios_recibidos'] ?: 'Ninguno registrado') ?></dd>
                    <dt class="col-4 text-secondary fw-normal">Entrega</dt>
                    <dd class="col-8"><?= e($orden['fecha_entrega'] ? formato_fecha($orden['fecha_entrega'], true) : 'Pendiente') ?></dd>
                </dl>
                <hr>
                <div class="mb-2">
                    <div class="small text-secondary">Falla reportada</div>
                    <div><?= nl2br(e($orden['falla_reportada'])) ?></div>
                </div>
                <?php if (!empty($orden['observaciones'])): ?>
                    <div>
                        <div class="small text-secondary">Observaciones</div>
                        <div><?= nl2br(e($orden['observaciones'])) ?></div>
                    </div>
                <?php endif; ?>
                <div class="mt-3">
                    <a class="btn btn-sm btn-outline-secondary" href="<?= url('equipos', ['accion' => 'ver', 'id' => (int) $orden['id_equipo']]) ?>">
                        <i class="fa-solid fa-eye me-1"></i> Historial del equipo
                    </a>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card h-100">
            <div class="card-header"><i class="fa-solid fa-pen-to-square me-2 text-primary"></i>Gestión de la orden</div>
            <div class="card-body">
                <?php if ($editable): ?>
                    <form method="post" action="<?= url('ordenes-servicio', ['accion' => 'ver', 'id' => (int) $orden['id_orden']]) ?>" novalidate>
                        <?= csrf_field() ?>
                        <input type="hidden" name="operacion" value="actualizar">
                        <input type="hidden" name="id" value="<?= (int) $orden['id_orden'] ?>">
                        <div class="mb-2">
                            <label class="form-label small mb-1" for="estado">Estado</label>
                            <select class="form-select form-select-sm" id="estado" name="estado">
                                <?php foreach (OrdenServicio::ESTADOS as $opcion): ?>
                                    <option value="<?= e($opcion) ?>" <?= $orden['estado'] === $opcion ? 'selected' : '' ?>>
                                        <?= e(etiqueta_estado($opcion)) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small mb-1" for="id_usuario_tecnico">Técnico</label>
                            <select class="form-select form-select-sm" id="id_usuario_tecnico" name="id_usuario_tecnico">
                                <option value="0">Sin asignar</option>
                                <?php foreach ($tecnicos as $tecnico): ?>
                                    <option value="<?= (int) $tecnico['id_usuario'] ?>"
                                        <?= (int) $orden['id_usuario_tecnico'] === (int) $tecnico['id_usuario'] ? 'selected' : '' ?>>
                                        <?= e(nombre_completo($tecnico)) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small mb-1" for="costo_servicio">Costo de mano de obra (Bs)</label>
                            <input class="form-control form-control-sm text-end" id="costo_servicio" name="costo_servicio"
                                   type="number" min="0" step="0.01"
                                   value="<?= e(number_format((float) $orden['costo_servicio'], 2, '.', '')) ?>"
                                   <?= $tieneIntervenciones ? 'readonly' : '' ?>>
                            <div class="form-text">
                                <?= $tieneIntervenciones
                                    ? 'Calculado automáticamente como la suma de las intervenciones.'
                                    : 'Editable mientras no existan intervenciones registradas.' ?>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small mb-1" for="observaciones">Observaciones</label>
                            <textarea class="form-control form-control-sm" id="observaciones" name="observaciones" rows="2" maxlength="2000"><?= e($orden['observaciones'] ?? '') ?></textarea>
                        </div>
                        <button class="btn btn-sm btn-primary" type="submit">
                            <i class="fa-solid fa-floppy-disk me-1"></i> Guardar cambios
                        </button>
                    </form>
                <?php else: ?>
                    <p class="small text-secondary mb-0">Su perfil no tiene permiso para modificar esta orden.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fa-solid fa-clipboard-check me-2 text-primary"></i>Intervenciones</span>
        <div class="d-flex align-items-center gap-2">
            <span class="small text-secondary"><?= count($intervenciones) ?> registro(s)</span>
            <?php if (!$cerrada && puede_escribir('detalle-servicio')): ?>
                <a class="btn btn-sm btn-outline-primary" href="<?= url('detalle-servicio', ['accion' => 'nuevo', 'orden' => (int) $orden['id_orden']]) ?>">
                    <i class="fa-solid fa-plus"></i>
                </a>
            <?php endif; ?>
        </div>
    </div>
    <div class="tabla-scroll">
        <table class="table table-alex mb-0">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Técnico</th>
                    <th>Diagnóstico</th>
                    <th>Trabajo realizado</th>
                    <th>Resultado</th>
                    <th class="text-end">Costo</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($intervenciones === []): ?>
                    <tr><td colspan="6" class="text-center text-secondary py-4">Aún no hay intervenciones registradas.</td></tr>
                <?php endif; ?>
                <?php foreach ($intervenciones as $intervencion): ?>
                    <tr>
                        <td class="small"><?= e(formato_fecha($intervencion['fecha_intervencion'], true)) ?></td>
                        <td class="small"><?= e($intervencion['tecnico']) ?></td>
                        <td class="small"><?= e($intervencion['diagnostico']) ?></td>
                        <td class="small"><?= e($intervencion['trabajo_realizado']) ?></td>
                        <td class="small text-secondary"><?= e($intervencion['resultado_prueba'] ?: '—') ?></td>
                        <td class="text-end fw-semibold"><?= e(formato_moneda($intervencion['costo'])) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-7">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fa-solid fa-gears me-2 text-primary"></i>Repuestos utilizados</span>
                <div class="d-flex align-items-center gap-2">
                    <span class="small text-secondary"><?= count($repuestos) ?></span>
                    <?php if (!$cerrada && puede_escribir('repuestos-servicio')): ?>
                        <a class="btn btn-sm btn-outline-primary" href="<?= url('repuestos-servicio', ['accion' => 'nuevo', 'orden' => (int) $orden['id_orden']]) ?>">
                            <i class="fa-solid fa-plus"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="tabla-scroll">
                <table class="table table-alex mb-0">
                    <thead>
                        <tr><th>Producto</th><th class="text-end">Cantidad</th><th class="text-end">Costo</th><th class="text-end">Subtotal</th></tr>
                    </thead>
                    <tbody>
                        <?php if ($repuestos === []): ?>
                            <tr><td colspan="4" class="text-center text-secondary py-4">No se usaron repuestos.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($repuestos as $repuesto): ?>
                            <tr>
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
                    <tfoot>
                        <tr>
                            <td colspan="3" class="text-end fw-bold">Total repuestos</td>
                            <td class="text-end fw-bold"><?= e(formato_moneda($totalRepuestos)) ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fa-solid fa-hand-holding-dollar me-2 text-primary"></i>Pagos</span>
                <div class="d-flex align-items-center gap-2">
                    <span class="small text-secondary"><?= count($pagos) ?></span>
                    <?php if ($saldo > 0.009 && $orden['estado'] !== 'Anulado' && puede_escribir('pagos-servicio')): ?>
                        <a class="btn btn-sm btn-outline-success" href="<?= url('pagos-servicio', ['accion' => 'nuevo', 'orden' => (int) $orden['id_orden']]) ?>">
                            <i class="fa-solid fa-plus"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="tabla-scroll">
                <table class="table table-alex mb-0">
                    <thead><tr><th>Fecha</th><th>Método</th><th class="text-end">Monto</th></tr></thead>
                    <tbody>
                        <?php if ($pagos === []): ?>
                            <tr><td colspan="3" class="text-center text-secondary py-4">Sin pagos registrados.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($pagos as $pago): ?>
                            <tr>
                                <td class="small"><?= e(formato_fecha($pago['fecha_pago'], true)) ?></td>
                                <td class="small"><?= e($pago['metodo_pago']) ?></td>
                                <td class="text-end fw-semibold"><?= e(formato_moneda($pago['monto'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fa-solid fa-shield-halved me-2 text-primary"></i>Garantías del servicio</span>
        <?php if (puede_escribir('garantias') && in_array($orden['estado'], ['Terminado', 'Entregado'], true)): ?>
            <a class="btn btn-sm btn-outline-warning" href="<?= url('garantias', ['accion' => 'nuevo', 'orden' => (int) $orden['id_orden']]) ?>">
                <i class="fa-solid fa-plus me-1"></i> Registrar garantía
            </a>
        <?php endif; ?>
    </div>
    <div class="tabla-scroll">
        <table class="table table-alex mb-0">
            <thead>
                <tr><th>Descripción</th><th>Inicio</th><th>Fin</th><th>Estado</th><th>Condiciones</th></tr>
            </thead>
            <tbody>
                <?php if ($garantias === []): ?>
                    <tr><td colspan="5" class="text-center text-secondary py-4">La orden no tiene garantías registradas.</td></tr>
                <?php endif; ?>
                <?php foreach ($garantias as $garantia): ?>
                    <tr>
                        <td class="small fw-semibold"><?= e($garantia['descripcion']) ?></td>
                        <td class="small"><?= e(formato_fecha($garantia['fecha_inicio'])) ?></td>
                        <td class="small"><?= e(formato_fecha($garantia['fecha_fin'])) ?></td>
                        <td>
                            <span class="badge badge-estado text-bg-<?= $garantia['vigencia'] === 'Vigente' ? 'success' : 'secondary' ?>">
                                <?= e($garantia['vigencia']) ?>
                            </span>
                        </td>
                        <td class="small text-secondary"><?= e($garantia['condiciones'] ?: '—') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php parcial('pie-admin'); ?>
