<?php
/** Listado y consulta de garantías. */
parcial('head-admin', ['titulo' => $titulo, 'subtitulo' => $subtitulo]);
$puedeEscribir = puede_escribir('garantias');
?>

<div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-4">
    <div>
        <h1 class="titulo-pagina h3 mb-1">Garantías</h1>
        <p class="text-secondary mb-0 small">Consulte su vigencia por cliente, producto u orden de servicio.</p>
    </div>
    <a class="btn btn-outline-secondary btn-sm" href="<?= url('index') ?>">
        <i class="fa-solid fa-arrow-left me-1"></i> Volver al panel
    </a>
</div>

<div class="card mb-4">
    <div class="card-header"><i class="fa-solid fa-filter me-2 text-primary"></i>Buscar garantías</div>
    <div class="card-body">
        <form class="row g-2 align-items-end" method="get" action="<?= asset('index.php') ?>">
            <input type="hidden" name="ruta" value="garantias">
            <div class="col-md-5">
                <label class="form-label small mb-1" for="q">Cliente, CI, serie, comprobante u orden</label>
                <input class="form-control form-control-sm" id="q" name="q" value="<?= e($busqueda) ?>" maxlength="80">
            </div>
            <div class="col-md-3">
                <label class="form-label small mb-1" for="tipo">Origen</label>
                <select class="form-select form-select-sm" id="tipo" name="tipo">
                    <option value="">Todos</option>
                    <option value="Producto" <?= $tipo === 'Producto' ? 'selected' : '' ?>>Producto</option>
                    <option value="Servicio" <?= $tipo === 'Servicio' ? 'selected' : '' ?>>Servicio</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1" for="estado">Vigencia</label>
                <select class="form-select form-select-sm" id="estado" name="estado">
                    <option value="">Todas</option>
                    <?php foreach (['Vigente', 'Vencida', 'Anulada'] as $opcion): ?>
                        <option value="<?= e($opcion) ?>" <?= $estado === $opcion ? 'selected' : '' ?>><?= e($opcion) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button class="btn btn-sm btn-primary flex-grow-1" type="submit">
                    <i class="fa-solid fa-magnifying-glass me-1"></i>Buscar
                </button>
                <a class="btn btn-sm btn-outline-secondary" href="<?= url('garantias') ?>" title="Limpiar filtros">
                    <i class="fa-solid fa-rotate-left"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fa-solid fa-shield-halved me-2 text-primary"></i>Garantías registradas</span>
        <span class="small text-secondary"><?= count($garantias) ?> registro(s), máximo 300</span>
    </div>
    <div class="tabla-scroll">
        <table class="table table-alex table-hover mb-0">
            <thead>
                <tr>
                    <th>Tipo</th>
                    <th>Cliente</th>
                    <th>Origen</th>
                    <th>Descripción</th>
                    <th>Vigencia</th>
                    <th>Vence</th>
                    <?php if ($puedeEscribir): ?><th class="text-end">Acción</th><?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if ($garantias === []): ?>
                    <tr><td colspan="<?= $puedeEscribir ? 7 : 6 ?>" class="text-center text-secondary py-4">
                        No se encontraron garantías con estos filtros.
                    </td></tr>
                <?php endif; ?>
                <?php foreach ($garantias as $garantia): ?>
                    <?php
                    $vigencia = (string) $garantia['vigencia'];
                    $clase = $vigencia === 'Vigente' ? 'success' : ($vigencia === 'Anulada' ? 'secondary' : 'warning');
                    $origen = $garantia['tipo_garantia'] === 'Producto'
                        ? ($garantia['numero_comprobante'] ?? 'Venta')
                        : ($garantia['numero_orden'] ?? 'Orden');
                    ?>
                    <tr>
                        <td><span class="badge text-bg-light border"><?= e($garantia['tipo_garantia']) ?></span></td>
                        <td class="small">
                            <div class="fw-semibold"><?= e($garantia['cliente'] ?? '—') ?></div>
                            <div class="text-secondary">CI <?= e($garantia['ci'] ?? '—') ?></div>
                        </td>
                        <td class="small">
                            <div class="fw-semibold"><?= e($origen) ?></div>
                            <div class="text-secondary">
                                <?= e($garantia['tipo_garantia'] === 'Producto'
                                    ? ($garantia['producto'] ?? '')
                                    : trim(($garantia['marca'] ?? '') . ' ' . ($garantia['modelo'] ?? ''))) ?>
                                <?= !empty($garantia['numero_serie']) ? ' · ' . e($garantia['numero_serie']) : '' ?>
                            </div>
                        </td>
                        <td class="small"><?= e($garantia['descripcion']) ?></td>
                        <td><span class="badge text-bg-<?= e($clase) ?>"><?= e($vigencia) ?></span></td>
                        <td class="small"><?= e(formato_fecha($garantia['fecha_fin'])) ?></td>
                        <?php if ($puedeEscribir): ?>
                            <td class="text-end">
                                <?php if ($vigencia !== 'Anulada'): ?>
                                    <form method="post" action="<?= url('garantias') ?>" class="d-inline"
                                          onsubmit="return confirm('¿Desea anular esta garantía?');">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="operacion" value="anular">
                                        <input type="hidden" name="id_garantia" value="<?= (int) $garantia['id_garantia'] ?>">
                                        <button class="btn btn-sm btn-outline-danger" type="submit" title="Anular garantía">
                                            <i class="fa-solid fa-ban"></i>
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <span class="text-secondary">—</span>
                                <?php endif; ?>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php parcial('pie-admin'); ?>
