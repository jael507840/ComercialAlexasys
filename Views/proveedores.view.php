<?php
/** Módulo 6: proveedores. */
parcial('head-admin', ['titulo' => $titulo, 'subtitulo' => $subtitulo]);
$editando = $proveedorEdicion !== null;
$abrirFormulario = $editando || $errores !== [];
$puedeEscribir = puede_escribir('proveedores');
?>

<div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-4">
    <div>
        <h1 class="titulo-pagina h3 mb-1">Proveedores</h1>
        <p class="text-secondary mb-0 small">Datos de contacto y compras registradas por proveedor.</p>
    </div>
    <?php if ($puedeEscribir): ?>
        <button class="btn btn-primary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#panelProveedor">
            <i class="fa-solid fa-truck-field me-1"></i>
            <?= $editando ? 'Editar proveedor' : 'Nuevo proveedor' ?>
        </button>
    <?php endif; ?>
</div>

<?php if ($errores !== []): ?>
    <div class="alert alert-danger">
        <div class="fw-semibold mb-1"><i class="fa-solid fa-circle-exclamation me-1"></i> Revise los datos del proveedor</div>
        <ul class="mb-0 ps-3"><?php foreach ($errores as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul>
    </div>
<?php endif; ?>

<div class="collapse <?= $abrirFormulario ? 'show' : '' ?> mb-4" id="panelProveedor">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>
                <i class="fa-solid <?= $editando ? 'fa-truck-fast' : 'fa-truck-medical' ?> me-2 text-primary"></i>
                <?= $editando ? 'Editar proveedor' : 'Registrar proveedor' ?>
            </span>
            <?php if ($editando): ?>
                <a class="btn btn-sm btn-light border" href="<?= url('proveedores') ?>">
                    <i class="fa-solid fa-xmark me-1"></i> Cancelar edición
                </a>
            <?php endif; ?>
        </div>
        <div class="card-body">
            <form method="post" action="<?= url('proveedores') ?>" novalidate>
                <?= csrf_field() ?>
                <input type="hidden" name="operacion" value="<?= $editando ? 'actualizar' : 'crear' ?>">
                <input type="hidden" name="id" value="<?= (int) $formulario['id_proveedor'] ?>">
                <div class="row g-3">
                    <div class="col-md-5">
                        <label class="form-label requerido" for="nombre">Razón social / nombre</label>
                        <input class="form-control" id="nombre" name="nombre" maxlength="120" required
                               value="<?= e($formulario['nombre']) ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="nit">NIT</label>
                        <input class="form-control" id="nit" name="nit" maxlength="20"
                               value="<?= e($formulario['nit']) ?>">
                        <div class="form-text">Identificación tributaria (opcional).</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="telefono">Teléfono</label>
                        <input class="form-control" id="telefono" name="telefono" maxlength="20"
                               value="<?= e($formulario['telefono']) ?>">
                    </div>
                    <div class="col-md-5">
                        <label class="form-label" for="correo">Correo electrónico</label>
                        <input class="form-control" id="correo" name="correo" type="email" maxlength="120"
                               value="<?= e($formulario['correo']) ?>">
                    </div>
                    <div class="col-md-5">
                        <label class="form-label" for="direccion">Dirección</label>
                        <input class="form-control" id="direccion" name="direccion" maxlength="200"
                               value="<?= e($formulario['direccion']) ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label" for="estado">Estado</label>
                        <select class="form-select" id="estado" name="estado">
                            <option value="Activo" <?= $formulario['estado'] === 'Activo' ? 'selected' : '' ?>>Activo</option>
                            <option value="Inactivo" <?= $formulario['estado'] === 'Inactivo' ? 'selected' : '' ?>>Inactivo</option>
                        </select>
                    </div>
                </div>
                <div class="mt-3 d-flex gap-2">
                    <button class="btn btn-primary" type="submit">
                        <i class="fa-solid fa-floppy-disk me-1"></i>
                        <?= $editando ? 'Guardar cambios' : 'Registrar proveedor' ?>
                    </button>
                    <button class="btn btn-light border" type="button" data-bs-toggle="collapse" data-bs-target="#panelProveedor">
                        Cerrar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="metrica py-3">
            <div class="etiqueta">Proveedores</div>
            <div class="valor fs-4"><?= (int) $totalProveedores ?></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="metrica py-3">
            <div class="etiqueta">Activos</div>
            <div class="valor fs-4 text-success"><?= (int) $totalActivos ?></div>
        </div>
    </div>
    <div class="col-md-6">
        <form class="d-flex gap-2 h-100 align-items-center" method="get" action="<?= asset('index.php') ?>">
            <input type="hidden" name="ruta" value="proveedores">
            <input class="form-control" type="search" name="q" value="<?= e($busqueda) ?>"
                   placeholder="Buscar por nombre, NIT o teléfono" aria-label="Buscar proveedor">
            <button class="btn btn-primary" type="submit"><i class="fa-solid fa-magnifying-glass"></i></button>
            <?php if ($busqueda !== ''): ?>
                <a class="btn btn-outline-secondary" href="<?= url('proveedores') ?>" title="Limpiar búsqueda">
                    <i class="fa-solid fa-rotate-left"></i>
                </a>
            <?php endif; ?>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fa-solid fa-truck-field me-2 text-primary"></i>Listado de proveedores</span>
        <span class="small text-secondary"><?= count($proveedores) ?> resultado(s)</span>
    </div>
    <div class="tabla-scroll">
        <table class="table table-alex table-hover mb-0">
            <thead>
                <tr>
                    <th>Proveedor</th>
                    <th>NIT</th>
                    <th>Contacto</th>
                    <th class="text-center">Compras</th>
                    <th class="text-end">Total comprado</th>
                    <th>Estado</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($proveedores === []): ?>
                    <tr>
                        <td colspan="7" class="text-center text-secondary py-4">
                            <?= $busqueda !== '' ? 'No se encontraron proveedores con ese criterio.' : 'Aún no hay proveedores registrados.' ?>
                        </td>
                    </tr>
                <?php endif; ?>
                <?php foreach ($proveedores as $proveedor): ?>
                    <tr>
                        <td>
                            <a class="fw-semibold" href="<?= url('proveedores', ['accion' => 'ver', 'id' => (int) $proveedor['id_proveedor']]) ?>">
                                <?= e($proveedor['nombre']) ?>
                            </a>
                            <div class="small text-secondary"><?= e($proveedor['direccion'] ?: 'Sin dirección registrada') ?></div>
                        </td>
                        <td class="small"><?= e($proveedor['nit'] ?: '—') ?></td>
                        <td class="small">
                            <?= e($proveedor['telefono'] ?: '—') ?>
                            <div class="text-secondary"><?= e($proveedor['correo'] ?: '') ?></div>
                        </td>
                        <td class="text-center">
                            <span class="badge text-bg-light border"><?= (int) $proveedor['total_compras'] ?></span>
                        </td>
                        <td class="text-end fw-semibold"><?= e(formato_moneda($proveedor['total_comprado'])) ?></td>
                        <td>
                            <span class="badge badge-estado text-bg-<?= $proveedor['estado'] === 'Activo' ? 'success' : 'secondary' ?>">
                                <?= e($proveedor['estado']) ?>
                            </span>
                        </td>
                        <td class="text-end">
                            <div class="acciones-tabla">
                                <a class="btn btn-sm btn-outline-primary" title="Ver historial"
                                   href="<?= url('proveedores', ['accion' => 'ver', 'id' => (int) $proveedor['id_proveedor']]) ?>">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                                <?php if ($puedeEscribir): ?>
                                    <a class="btn btn-sm btn-outline-secondary" title="Editar"
                                       href="<?= url('proveedores', ['accion' => 'editar', 'id' => (int) $proveedor['id_proveedor']]) ?>">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
                                    <form method="post" action="<?= url('proveedores') ?>" class="d-inline"
                                          data-confirmar="¿Cambiar el estado del proveedor <?= e($proveedor['nombre']) ?>?">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="operacion" value="estado">
                                        <input type="hidden" name="id" value="<?= (int) $proveedor['id_proveedor'] ?>">
                                        <input type="hidden" name="estado" value="<?= $proveedor['estado'] === 'Activo' ? 'Inactivo' : 'Activo' ?>">
                                        <button class="btn btn-sm btn-outline-<?= $proveedor['estado'] === 'Activo' ? 'danger' : 'success' ?>"
                                                title="<?= $proveedor['estado'] === 'Activo' ? 'Desactivar' : 'Activar' ?>">
                                            <i class="fa-solid <?= $proveedor['estado'] === 'Activo' ? 'fa-toggle-off' : 'fa-toggle-on' ?>"></i>
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

<div class="alert alert-light border mt-4 mb-0 small text-secondary">
    <i class="fa-solid fa-circle-info me-1"></i>
    Un proveedor inactivo conserva su historial de compras, pero no se ofrece al registrar nuevas compras.
</div>

<?php parcial('pie-admin'); ?>
