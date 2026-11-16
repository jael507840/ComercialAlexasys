<?php
/** Resultados tabulares de los reportes operativos. */
parcial('head-admin', ['titulo' => $titulo, 'subtitulo' => $subtitulo]);
$camposFecha = ['fecha_venta', 'fecha_compra', 'fecha_pago', 'fecha_recepcion', 'fecha_registro'];
$camposMoneda = [
    'precio_venta', 'total', 'pagado', 'saldo', 'total_compra', 'costo_adquisicion',
    'margen', 'monto',
];
$camposCantidad = ['items', 'total_items', 'stock_actual', 'stock_minimo', 'total_ordenes'];
?>

<div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-4">
    <div>
        <h1 class="titulo-pagina h3 mb-1">Reportes</h1>
        <p class="text-secondary mb-0 small">Seleccione el informe y aplique los filtros disponibles.</p>
    </div>
    <a class="btn btn-outline-secondary btn-sm" href="<?= url('index') ?>">
        <i class="fa-solid fa-arrow-left me-1"></i> Volver al panel
    </a>
</div>

<?php if ($errores !== []): ?>
    <div class="alert alert-danger">
        <ul class="mb-0"><?php foreach ($errores as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul>
    </div>
<?php endif; ?>

<div class="card mb-4">
    <div class="card-header"><i class="fa-solid fa-filter me-2 text-primary"></i>Informe y filtros</div>
    <div class="card-body">
        <form class="row g-2 align-items-end" method="get" action="<?= asset('index.php') ?>">
            <input type="hidden" name="ruta" value="reportes">
            <div class="col-md-4">
                <label class="form-label small mb-1" for="tipo_reporte">Informe</label>
                <select class="form-select form-select-sm" id="tipo_reporte" name="tipo_reporte"
                        onchange="this.form.submit()">
                    <?php foreach ($reportesDisponibles as $clave => $nombre): ?>
                        <option value="<?= e($clave) ?>" <?= $tipoReporte === $clave ? 'selected' : '' ?>><?= e($nombre) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php if ($puedeFiltrarFechas): ?>
                <div class="col-md-2">
                    <label class="form-label small mb-1" for="desde">Desde</label>
                    <input class="form-control form-control-sm" type="date" id="desde" name="desde" value="<?= e($filtros['desde']) ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label small mb-1" for="hasta">Hasta</label>
                    <input class="form-control form-control-sm" type="date" id="hasta" name="hasta" value="<?= e($filtros['hasta']) ?>">
                </div>
            <?php endif; ?>

            <?php if (in_array($tipoReporte, ['clientes', 'garantias'], true)): ?>
                <div class="col-md-4">
                    <label class="form-label small mb-1" for="q">Búsqueda</label>
                    <input class="form-control form-control-sm" id="q" name="q" maxlength="80"
                           value="<?= e($filtros['busqueda']) ?>"
                           placeholder="<?= $tipoReporte === 'garantias' ? 'Cliente, CI, serie, comprobante u orden' : 'Nombre, CI o contacto' ?>">
                </div>
            <?php endif; ?>

            <?php if (in_array($tipoReporte, ['productos', 'inventario'], true)): ?>
                <div class="col-md-3">
                    <label class="form-label small mb-1" for="categoria">Categoría</label>
                    <select class="form-select form-select-sm" id="categoria" name="categoria">
                        <option value="0">Todas</option>
                        <?php foreach ($categorias as $categoria): ?>
                            <option value="<?= (int) $categoria['id_categoria'] ?>"
                                <?= $filtros['id_categoria'] === (int) $categoria['id_categoria'] ? 'selected' : '' ?>>
                                <?= e($categoria['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endif; ?>

            <?php if ($tipoReporte === 'inventario'): ?>
                <div class="col-md-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="bajo" name="bajo" value="1"
                               <?= $filtros['solo_bajo'] ? 'checked' : '' ?>>
                        <label class="form-check-label small" for="bajo">Solo stock bajo</label>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($tipoReporte === 'ventas'): ?>
                <div class="col-md-3">
                    <label class="form-label small mb-1" for="cliente">Cliente</label>
                    <select class="form-select form-select-sm" id="cliente" name="cliente">
                        <option value="0">Todos</option>
                        <?php foreach ($clientes as $cliente): ?>
                            <option value="<?= (int) $cliente['id_cliente'] ?>"
                                <?= $filtros['id_cliente'] === (int) $cliente['id_cliente'] ? 'selected' : '' ?>>
                                <?= e(trim($cliente['nombres'] . ' ' . $cliente['apellidos'])) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endif; ?>

            <?php if ($tipoReporte === 'compras'): ?>
                <div class="col-md-3">
                    <label class="form-label small mb-1" for="proveedor">Proveedor</label>
                    <select class="form-select form-select-sm" id="proveedor" name="proveedor">
                        <option value="0">Todos</option>
                        <?php foreach ($proveedores as $proveedor): ?>
                            <option value="<?= (int) $proveedor['id_proveedor'] ?>"
                                <?= $filtros['id_proveedor'] === (int) $proveedor['id_proveedor'] ? 'selected' : '' ?>>
                                <?= e($proveedor['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endif; ?>

            <?php if (in_array($tipoReporte, ['ventas', 'servicios'], true)): ?>
                <div class="col-md-2">
                    <label class="form-label small mb-1" for="estado">Estado</label>
                    <select class="form-select form-select-sm" id="estado" name="estado">
                        <option value="">Todos</option>
                        <?php
                        $estados = $tipoReporte === 'ventas'
                            ? ['Confirmada', 'Anulada']
                            : OrdenServicio::ESTADOS;
                        ?>
                        <?php foreach ($estados as $estadoOpcion): ?>
                            <option value="<?= e($estadoOpcion) ?>" <?= $filtros['estado'] === $estadoOpcion ? 'selected' : '' ?>>
                                <?= e(etiqueta_estado($estadoOpcion)) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php elseif ($tipoReporte === 'garantias'): ?>
                <div class="col-md-2">
                    <label class="form-label small mb-1" for="estado">Vigencia</label>
                    <select class="form-select form-select-sm" id="estado" name="estado">
                        <option value="">Todas</option>
                        <?php foreach (['Vigente', 'Vencida', 'Anulada'] as $estadoOpcion): ?>
                            <option value="<?= e($estadoOpcion) ?>" <?= $filtros['estado'] === $estadoOpcion ? 'selected' : '' ?>>
                                <?= e($estadoOpcion) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small mb-1" for="tipo">Tipo</label>
                    <select class="form-select form-select-sm" id="tipo" name="tipo">
                        <option value="">Todos</option>
                        <?php foreach (['Producto', 'Servicio'] as $tipoOpcion): ?>
                            <option value="<?= e($tipoOpcion) ?>" <?= $filtros['tipo'] === $tipoOpcion ? 'selected' : '' ?>>
                                <?= e($tipoOpcion) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endif; ?>

            <div class="col-md-3 d-flex gap-2">
                <button class="btn btn-sm btn-primary flex-grow-1" type="submit">
                    <i class="fa-solid fa-play me-1"></i>Generar
                </button>
                <a class="btn btn-sm btn-outline-secondary" href="<?= url('reportes', ['tipo_reporte' => $tipoReporte]) ?>"
                   title="Limpiar filtros"><i class="fa-solid fa-rotate-left"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fa-solid fa-table-list me-2 text-primary"></i><?= e($reportesDisponibles[$tipoReporte]) ?></span>
        <span class="small text-secondary"><?= count($datos) ?> resultado(s)</span>
    </div>
    <div class="tabla-scroll">
        <table class="table table-alex table-hover mb-0">
            <thead>
                <tr><?php foreach ($columnas as $etiqueta): ?><th><?= e($etiqueta) ?></th><?php endforeach; ?></tr>
            </thead>
            <tbody>
                <?php if ($datos === []): ?>
                    <tr><td colspan="<?= count($columnas) ?>" class="text-center text-secondary py-4">
                        <?php if ($errores === []): ?>No hay datos para mostrar con los filtros seleccionados.<?php else: ?>Corrija los filtros para generar el informe.<?php endif; ?>
                    </td></tr>
                <?php endif; ?>
                <?php foreach ($datos as $fila): ?>
                    <tr>
                        <?php foreach ($columnas as $campo => $etiqueta): ?>
                            <?php
                            $valor = $fila[$campo] ?? null;
                            if ($valor === null || $valor === '') {
                                $contenido = '—';
                            } elseif ($campo === 'margen_porcentaje') {
                                $contenido = number_format((float) $valor, 2, ',', '.') . '%';
                            } elseif (in_array($campo, $camposMoneda, true)) {
                                $contenido = formato_moneda($valor);
                            } elseif (in_array($campo, $camposFecha, true)) {
                                $contenido = formato_fecha((string) $valor, str_contains((string) $valor, ':'));
                            } elseif (in_array($campo, $camposCantidad, true)) {
                                $contenido = (string) (int) $valor;
                            } else {
                                $contenido = (string) $valor;
                            }
                            ?>
                            <td class="<?= in_array($campo, $camposMoneda, true) || in_array($campo, $camposCantidad, true) ? 'text-end' : '' ?>">
                                <?= e($contenido) ?>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white small text-secondary">
        Los resultados provienen de los registros actuales del sistema. Algunas consultas limitan la lista a 300 filas.
    </div>
</div>

<?php parcial('pie-admin'); ?>
