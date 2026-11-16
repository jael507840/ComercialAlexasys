<?php
/** Módulo 5: productos. */
parcial('head-admin', ['titulo' => $titulo, 'subtitulo' => $subtitulo]);
$editando = $productoEdicion !== null;
$abrirFormulario = $editando || $errores !== [];
$puedeEscribir = puede_escribir('productos');
$bajoStock = array_filter($productos, static fn (array $p): bool => (int) $p['stock_actual'] <= (int) $p['stock_minimo']);
?>

<div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-4">
    <div>
        <h1 class="titulo-pagina h3 mb-1">Productos</h1>
        <p class="text-secondary mb-0 small">Precios de venta, costo de la última compra y existencias reales.</p>
    </div>
    <?php if ($puedeEscribir): ?>
        <button class="btn btn-primary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#panelProducto">
            <i class="fa-solid fa-box me-1"></i> <?= $editando ? 'Editar producto' : 'Nuevo producto' ?>
        </button>
    <?php endif; ?>
</div>

<?php if ($errores !== []): ?>
    <div class="alert alert-danger">
        <div class="fw-semibold mb-1"><i class="fa-solid fa-circle-exclamation me-1"></i> Revise los datos del producto</div>
        <ul class="mb-0 ps-3"><?php foreach ($errores as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul>
    </div>
<?php endif; ?>

<div class="collapse <?= $abrirFormulario ? 'show' : '' ?> mb-4" id="panelProducto">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="fa-solid fa-box-open me-2 text-primary"></i><?= $editando ? 'Editar producto' : 'Registrar producto' ?></span>
            <?php if ($editando): ?><a class="btn btn-sm btn-light border" href="<?= url('productos') ?>">Cancelar edición</a><?php endif; ?>
        </div>
        <div class="card-body">
            <form method="post" action="<?= url('productos') ?>" enctype="multipart/form-data" novalidate>
                <?= csrf_field() ?>
                <input type="hidden" name="operacion" value="<?= $editando ? 'actualizar' : 'crear' ?>">
                <input type="hidden" name="id" value="<?= (int) $formulario['id_producto'] ?>">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label requerido" for="codigo">Código</label>
                        <input class="form-control" id="codigo" name="codigo" maxlength="30" required
                               pattern="[A-Za-z0-9._-]{2,30}" value="<?= e($formulario['codigo']) ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label requerido" for="nombre">Nombre</label>
                        <input class="form-control" id="nombre" name="nombre" maxlength="120" required
                               value="<?= e($formulario['nombre']) ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label requerido" for="id_categoria">Categoría</label>
                        <select class="form-select" id="id_categoria" name="id_categoria" required>
                            <option value="0">Seleccione…</option>
                            <?php foreach ($categorias as $categoria): ?>
                                <option value="<?= (int) $categoria['id_categoria'] ?>"
                                    <?= (int) $formulario['id_categoria'] === (int) $categoria['id_categoria'] ? 'selected' : '' ?>>
                                    <?= e($categoria['nombre']) ?><?= $categoria['estado'] === 'Inactivo' ? ' (inactiva)' : '' ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="marca">Marca</label>
                        <input class="form-control" id="marca" name="marca" maxlength="60" value="<?= e($formulario['marca']) ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="modelo">Modelo</label>
                        <input class="form-control" id="modelo" name="modelo" maxlength="60" value="<?= e($formulario['modelo']) ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label requerido" for="precio_venta">Precio venta (Bs)</label>
                        <input class="form-control" id="precio_venta" name="precio_venta" type="number" step="0.01" min="0" required
                               value="<?= e($formulario['precio_venta']) ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label" for="stock_minimo">Stock mínimo</label>
                        <input class="form-control" id="stock_minimo" name="stock_minimo" type="number" step="1" min="0"
                               value="<?= e($formulario['stock_minimo']) ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label" for="estado">Estado</label>
                        <select class="form-select" id="estado" name="estado">
                            <option value="Activo" <?= $formulario['estado'] === 'Activo' ? 'selected' : '' ?>>Activo</option>
                            <option value="Inactivo" <?= $formulario['estado'] === 'Inactivo' ? 'selected' : '' ?>>Inactivo</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="imagenProducto">Imagen del producto</label>
                        <input class="form-control" id="imagenProducto" name="imagen" type="file" accept=".jpg,.jpeg,.png,.webp">
                        <div class="form-text">JPG, PNG o WEBP · máximo 2 MB.</div>
                    </div>
                    <div class="col-md-4">
                        <?php if (!empty($formulario['imagen'])): ?>
                            <img id="vistaImagenProducto" class="rounded border bg-white"
                                 style="height:80px;width:120px;object-fit:contain"
                                 src="<?= asset($formulario['imagen']) ?>" alt="Imagen actual">
                        <?php else: ?>
                            <img id="vistaImagenProducto" class="rounded border bg-white d-none"
                                 style="height:80px;width:120px;object-fit:contain" alt="Vista previa">
                        <?php endif; ?>
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="descripcion">Descripción</label>
                        <textarea class="form-control" id="descripcion" name="descripcion" rows="2" maxlength="2000"><?= e($formulario['descripcion']) ?></textarea>
                    </div>
                </div>

                <?php if ($editando): ?>
                    <div class="alert alert-info small mt-3 mb-0">
                        <i class="fa-solid fa-circle-info me-1"></i>
                        El stock actual es <strong><?= (int) ($productoEdicion['stock_actual'] ?? 0) ?></strong> unidad(es).
                        Las existencias se modifican mediante compras, ventas, repuestos o ajustes de inventario.
                    </div>
                <?php else: ?>
                    <div class="alert alert-info small mt-3 mb-0">
                        <i class="fa-solid fa-circle-info me-1"></i>
                        El producto se crea con stock 0. Para ingresar existencias registre una compra o un ajuste de inventario.
                    </div>
                <?php endif; ?>

                <div class="mt-3 d-flex gap-2">
                    <button class="btn btn-primary" type="submit">
                        <i class="fa-solid fa-floppy-disk me-1"></i> <?= $editando ? 'Guardar cambios' : 'Registrar producto' ?>
                    </button>
                    <button class="btn btn-light border" type="button" data-bs-toggle="collapse" data-bs-target="#panelProducto">Cerrar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="metrica py-3">
            <div class="etiqueta">Productos activos</div>
            <div class="valor fs-4"><?= (int) ($resumen['total_productos'] ?? 0) ?></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="metrica py-3">
            <div class="etiqueta">Unidades en stock</div>
            <div class="valor fs-4"><?= (int) ($resumen['total_unidades'] ?? 0) ?></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="metrica py-3">
            <div class="etiqueta">Valor a precio venta</div>
            <div class="valor fs-5"><?= e(formato_moneda($resumen['valor_venta'] ?? 0)) ?></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="metrica py-3">
            <div class="etiqueta">Valor a costo</div>
            <div class="valor fs-5"><?= e(formato_moneda($resumen['valor_costo'] ?? 0)) ?></div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <form class="row g-2 align-items-end" method="get" action="<?= asset('index.php') ?>">
            <input type="hidden" name="ruta" value="productos">
            <div class="col-md-5">
                <label class="form-label small mb-1">Buscar</label>
                <input class="form-control form-control-sm" type="search" name="q" value="<?= e($busqueda) ?>"
                       placeholder="Código, nombre, marca o modelo">
            </div>
            <div class="col-md-3">
                <label class="form-label small mb-1">Categoría</label>
                <select class="form-select form-select-sm" name="categoria">
                    <option value="0">Todas</option>
                    <?php foreach ($categorias as $categoria): ?>
                        <option value="<?= (int) $categoria['id_categoria'] ?>" <?= $idCategoria === (int) $categoria['id_categoria'] ? 'selected' : '' ?>>
                            <?= e($categoria['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <div class="form-check mt-4">
                    <input class="form-check-input" type="checkbox" id="bajo" name="bajo" value="1" <?= $soloBajo ? 'checked' : '' ?>>
                    <label class="form-check-label small" for="bajo">Solo stock bajo</label>
                </div>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button class="btn btn-sm btn-primary flex-grow-1" type="submit"><i class="fa-solid fa-filter me-1"></i>Filtrar</button>
                <a class="btn btn-sm btn-outline-secondary" href="<?= url('productos') ?>"><i class="fa-solid fa-rotate-left"></i></a>
            </div>
        </form>
    </div>
    <div class="tabla-scroll">
        <table class="table table-alex table-hover mb-0">
            <thead>
                <tr>
                    <th>Producto</th><th>Categoría</th>
                    <th class="text-end">Precio</th><th class="text-end">Costo</th>
                    <th class="text-center">Stock</th><th>Estado</th><th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($productos === []): ?>
                    <tr><td colspan="7" class="text-center text-secondary py-4">No se encontraron productos.</td></tr>
                <?php endif; ?>
                <?php foreach ($productos as $producto): ?>
                    <?php
                    $stock = (int) $producto['stock_actual'];
                    $minimo = (int) $producto['stock_minimo'];
                    $claseStock = $stock === 0 ? 'secondary' : ($stock <= $minimo ? 'danger' : 'success');
                    $costo = $producto['costo_adquisicion'] !== null ? (float) $producto['costo_adquisicion'] : null;
                    ?>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <img src="<?= asset($producto['imagen'] ?: 'imagen/productos/componentes.jpg') ?>"
                                     alt="" class="rounded border bg-white" style="width:42px;height:42px;object-fit:contain">
                                <div>
                                    <div class="fw-semibold small"><?= e($producto['nombre']) ?></div>
                                    <div class="text-secondary" style="font-size:.74rem">
                                        <?= e($producto['codigo']) ?> · <?= e(trim(($producto['marca'] ?? '') . ' ' . ($producto['modelo'] ?? ''))) ?>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="small"><?= e($producto['categoria']) ?></td>
                        <td class="text-end fw-semibold"><?= e(formato_moneda($producto['precio_venta'])) ?></td>
                        <td class="text-end small">
                            <?= $costo !== null ? e(formato_moneda($costo)) : '<span class="text-secondary">Sin compra</span>' ?>
                        </td>
                        <td class="text-center">
                            <span class="badge text-bg-<?= $claseStock ?>"><?= $stock ?></span>
                            <div class="text-secondary" style="font-size:.7rem">mín. <?= $minimo ?></div>
                        </td>
                        <td><span class="badge badge-estado text-bg-<?= $producto['estado'] === 'Activo' ? 'success' : 'secondary' ?>"><?= e($producto['estado']) ?></span></td>
                        <td class="text-end">
                            <div class="acciones-tabla">
                                <a class="btn btn-sm btn-outline-primary" title="Movimientos"
                                   href="<?= url('inventario', ['producto' => (int) $producto['id_producto']]) ?>">
                                    <i class="fa-solid fa-arrow-right-arrow-left"></i>
                                </a>
                                <?php if ($puedeEscribir): ?>
                                    <a class="btn btn-sm btn-outline-secondary" title="Editar"
                                       href="<?= url('productos', ['accion' => 'editar', 'id' => (int) $producto['id_producto']]) ?>">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
                                    <form method="post" action="<?= url('productos') ?>" class="d-inline"
                                          data-confirmar="¿Cambiar el estado de <?= e($producto['nombre']) ?>?">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="operacion" value="estado">
                                        <input type="hidden" name="id" value="<?= (int) $producto['id_producto'] ?>">
                                        <input type="hidden" name="estado" value="<?= $producto['estado'] === 'Activo' ? 'Inactivo' : 'Activo' ?>">
                                        <button class="btn btn-sm btn-outline-<?= $producto['estado'] === 'Activo' ? 'danger' : 'success' ?>"
                                                title="<?= $producto['estado'] === 'Activo' ? 'Desactivar' : 'Activar' ?>">
                                            <i class="fa-solid <?= $producto['estado'] === 'Activo' ? 'fa-toggle-off' : 'fa-toggle-on' ?>"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white small text-secondary">
        <i class="fa-solid fa-circle-info me-1"></i>
        <?= count($productos) ?> producto(s) listado(s).
        <?php if ($soloBajo || $bajoStock !== []): ?>
            · Stock bajo o agotado: <strong><?= count($bajoStock) ?></strong> en el listado actual.
        <?php endif; ?>
    </div>
</div>

<?php parcial('pie-admin'); ?>
