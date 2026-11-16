<?php
/** Módulo 4: categorías. */
parcial('head-admin', ['titulo' => $titulo, 'subtitulo' => $subtitulo]);
$editando = $categoriaEdicion !== null;
$abrirFormulario = $editando || $errores !== [];
$puedeEscribir = puede_escribir('categorias');
?>

<div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-4">
    <div>
        <h1 class="titulo-pagina h3 mb-1">Categorías</h1>
        <p class="text-secondary mb-0 small">Cada producto pertenece a una categoría del catálogo.</p>
    </div>
    <?php if ($puedeEscribir): ?>
        <button class="btn btn-primary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#panelCategoria">
            <i class="fa-solid fa-layer-group me-1"></i> <?= $editando ? 'Editar categoría' : 'Nueva categoría' ?>
        </button>
    <?php endif; ?>
</div>

<?php if ($errores !== []): ?>
    <div class="alert alert-danger">
        <ul class="mb-0 ps-3"><?php foreach ($errores as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul>
    </div>
<?php endif; ?>

<div class="collapse <?= $abrirFormulario ? 'show' : '' ?> mb-4" id="panelCategoria">
    <div class="card">
        <div class="card-header">
            <i class="fa-solid fa-layer-group me-2 text-primary"></i>
            <?= $editando ? 'Editar categoría' : 'Registrar categoría' ?>
        </div>
        <div class="card-body">
            <form method="post" action="<?= url('categorias') ?>" class="row g-3" novalidate>
                <?= csrf_field() ?>
                <input type="hidden" name="operacion" value="<?= $editando ? 'actualizar' : 'crear' ?>">
                <input type="hidden" name="id" value="<?= (int) $formulario['id_categoria'] ?>">
                <div class="col-md-4">
                    <label class="form-label requerido" for="nombre">Nombre</label>
                    <input class="form-control" id="nombre" name="nombre" maxlength="80" required
                           value="<?= e($formulario['nombre']) ?>">
                </div>
                <div class="col-md-5">
                    <label class="form-label" for="descripcion">Descripción</label>
                    <input class="form-control" id="descripcion" name="descripcion" maxlength="200"
                           value="<?= e($formulario['descripcion']) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="estado">Estado</label>
                    <select class="form-select" id="estado" name="estado">
                        <option value="Activo" <?= $formulario['estado'] === 'Activo' ? 'selected' : '' ?>>Activo</option>
                        <option value="Inactivo" <?= $formulario['estado'] === 'Inactivo' ? 'selected' : '' ?>>Inactivo</option>
                    </select>
                </div>
                <div class="col-12 d-flex gap-2">
                    <button class="btn btn-primary" type="submit">
                        <i class="fa-solid fa-floppy-disk me-1"></i> <?= $editando ? 'Guardar cambios' : 'Registrar' ?>
                    </button>
                    <?php if ($editando): ?>
                        <a class="btn btn-light border" href="<?= url('categorias') ?>">Cancelar</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
        <span><i class="fa-solid fa-list me-2 text-primary"></i>Categorías registradas</span>
        <form class="d-flex gap-2" method="get" action="<?= asset('index.php') ?>">
            <input type="hidden" name="ruta" value="categorias">
            <input class="form-control form-control-sm" type="search" name="q" value="<?= e($busqueda) ?>" placeholder="Buscar categoría">
            <button class="btn btn-sm btn-primary" type="submit"><i class="fa-solid fa-magnifying-glass"></i></button>
            <?php if ($busqueda !== ''): ?>
                <a class="btn btn-sm btn-outline-secondary" href="<?= url('categorias') ?>"><i class="fa-solid fa-rotate-left"></i></a>
            <?php endif; ?>
        </form>
    </div>
    <div class="tabla-scroll">
        <table class="table table-alex table-hover mb-0">
            <thead>
                <tr><th>#</th><th>Categoría</th><th>Descripción</th><th class="text-center">Productos</th><th>Estado</th><th class="text-end">Acciones</th></tr>
            </thead>
            <tbody>
                <?php if ($categorias === []): ?>
                    <tr><td colspan="6" class="text-center text-secondary py-4">No hay categorías registradas.</td></tr>
                <?php endif; ?>
                <?php foreach ($categorias as $categoria): ?>
                    <tr>
                        <td class="text-secondary"><?= (int) $categoria['id_categoria'] ?></td>
                        <td class="fw-semibold"><?= e($categoria['nombre']) ?></td>
                        <td class="small text-secondary"><?= e($categoria['descripcion'] ?: '—') ?></td>
                        <td class="text-center">
                            <a class="badge text-bg-light border" href="<?= url('productos', ['categoria' => (int) $categoria['id_categoria']]) ?>">
                                <?= (int) $categoria['total_productos'] ?>
                            </a>
                        </td>
                        <td><span class="badge badge-estado text-bg-<?= $categoria['estado'] === 'Activo' ? 'success' : 'secondary' ?>"><?= e($categoria['estado']) ?></span></td>
                        <td class="text-end">
                            <div class="acciones-tabla">
                                <?php if ($puedeEscribir): ?>
                                    <a class="btn btn-sm btn-outline-secondary" title="Editar" href="<?= url('categorias', ['accion' => 'editar', 'id' => (int) $categoria['id_categoria']]) ?>">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
                                    <form method="post" action="<?= url('categorias') ?>" class="d-inline"
                                          data-confirmar="¿Cambiar el estado de la categoría <?= e($categoria['nombre']) ?>?">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="operacion" value="estado">
                                        <input type="hidden" name="id" value="<?= (int) $categoria['id_categoria'] ?>">
                                        <input type="hidden" name="estado" value="<?= $categoria['estado'] === 'Activo' ? 'Inactivo' : 'Activo' ?>">
                                        <button class="btn btn-sm btn-outline-<?= $categoria['estado'] === 'Activo' ? 'danger' : 'success' ?>"
                                                title="<?= $categoria['estado'] === 'Activo' ? 'Desactivar' : 'Activar' ?>">
                                            <i class="fa-solid <?= $categoria['estado'] === 'Activo' ? 'fa-toggle-off' : 'fa-toggle-on' ?>"></i>
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
</div>

<?php parcial('pie-admin'); ?>
