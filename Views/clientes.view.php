<?php
/** Módulo 3: clientes. */
parcial('head-admin', ['titulo' => $titulo, 'subtitulo' => $subtitulo]);
$editando = $clienteEdicion !== null;
$abrirFormulario = $editando || $errores !== [];
$puedeEscribir = puede_escribir('clientes');
?>

<div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-4">
    <div>
        <h1 class="titulo-pagina h3 mb-1">Clientes</h1>
        <p class="text-secondary mb-0 small">Registro, búsqueda por CI, nombre o teléfono, e historial de atención.</p>
    </div>
    <?php if ($puedeEscribir): ?>
        <button class="btn btn-primary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#panelCliente"
                aria-expanded="<?= $abrirFormulario ? 'true' : 'false' ?>">
            <i class="fa-solid fa-user-plus me-1"></i>
            <?= $editando ? 'Editar cliente' : 'Nuevo cliente' ?>
        </button>
    <?php endif; ?>
</div>

<?php if ($errores !== []): ?>
    <div class="alert alert-danger">
        <div class="fw-semibold mb-1"><i class="fa-solid fa-circle-exclamation me-1"></i> Revise los datos del cliente</div>
        <ul class="mb-0 ps-3"><?php foreach ($errores as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul>
    </div>
<?php endif; ?>

<div class="collapse <?= $abrirFormulario ? 'show' : '' ?> mb-4" id="panelCliente">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>
                <i class="fa-solid <?= $editando ? 'fa-user-pen' : 'fa-user-plus' ?> me-2 text-primary"></i>
                <?= $editando ? 'Editar cliente' : 'Registrar nuevo cliente' ?>
            </span>
            <?php if ($editando): ?>
                <a class="btn btn-sm btn-light border" href="<?= url('clientes') ?>">
                    <i class="fa-solid fa-xmark me-1"></i> Cancelar edición
                </a>
            <?php endif; ?>
        </div>
        <div class="card-body">
            <form method="post" action="<?= url('clientes') ?>" novalidate>
                <?= csrf_field() ?>
                <input type="hidden" name="operacion" value="<?= $editando ? 'actualizar' : 'crear' ?>">
                <input type="hidden" name="id" value="<?= (int) $formulario['id_cliente'] ?>">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label requerido" for="ci">Cédula de identidad</label>
                        <input class="form-control" id="ci" name="ci" maxlength="20" required
                               pattern="[A-Za-z0-9-]{3,20}" value="<?= e($formulario['ci']) ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label requerido" for="nombres">Nombres</label>
                        <input class="form-control" id="nombres" name="nombres" maxlength="100" required
                               value="<?= e($formulario['nombres']) ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label requerido" for="apellidos">Apellidos</label>
                        <input class="form-control" id="apellidos" name="apellidos" maxlength="100" required
                               value="<?= e($formulario['apellidos']) ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="telefono">Teléfono</label>
                        <input class="form-control" id="telefono" name="telefono" maxlength="20"
                               value="<?= e($formulario['telefono']) ?>">
                    </div>
                    <div class="col-md-5">
                        <label class="form-label" for="correo">Correo electrónico</label>
                        <input class="form-control" id="correo" name="correo" type="email" maxlength="120"
                               value="<?= e($formulario['correo']) ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="direccion">Dirección</label>
                        <input class="form-control" id="direccion" name="direccion" maxlength="200"
                               value="<?= e($formulario['direccion']) ?>">
                    </div>
                    <div class="col-md-3">
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
                        <?= $editando ? 'Guardar cambios' : 'Registrar cliente' ?>
                    </button>
                    <button class="btn btn-light border" type="button" data-bs-toggle="collapse" data-bs-target="#panelCliente">
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
            <div class="etiqueta">Clientes registrados</div>
            <div class="valor fs-4"><?= (int) $totalClientes ?></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="metrica py-3">
            <div class="etiqueta">Clientes activos</div>
            <div class="valor fs-4 text-success"><?= (int) $totalActivos ?></div>
        </div>
    </div>
    <div class="col-md-6">
        <form class="d-flex gap-2 h-100 align-items-center" method="get" action="<?= asset('index.php') ?>">
            <input type="hidden" name="ruta" value="clientes">
            <input class="form-control" type="search" name="q" value="<?= e($busqueda) ?>"
                   placeholder="Buscar por CI, nombre o teléfono" aria-label="Buscar cliente">
            <button class="btn btn-primary" type="submit"><i class="fa-solid fa-magnifying-glass"></i></button>
            <?php if ($busqueda !== ''): ?>
                <a class="btn btn-outline-secondary" href="<?= url('clientes') ?>" title="Limpiar búsqueda">
                    <i class="fa-solid fa-rotate-left"></i>
                </a>
            <?php endif; ?>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fa-solid fa-users me-2 text-primary"></i>Listado de clientes</span>
        <span class="small text-secondary"><?= count($clientes) ?> resultado(s)</span>
    </div>
    <div class="tabla-scroll">
        <table class="table table-alex table-hover mb-0">
            <thead>
                <tr>
                    <th>CI</th>
                    <th>Cliente</th>
                    <th>Contacto</th>
                    <th class="text-center">Ventas</th>
                    <th class="text-center">Equipos</th>
                    <th>Estado</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($clientes === []): ?>
                    <tr>
                        <td colspan="7" class="text-center text-secondary py-4">
                            <?= $busqueda !== '' ? 'No se encontraron clientes con ese criterio.' : 'Aún no hay clientes registrados.' ?>
                        </td>
                    </tr>
                <?php endif; ?>
                <?php foreach ($clientes as $cliente): ?>
                    <tr>
                        <td class="fw-semibold"><?= e($cliente['ci']) ?></td>
                        <td>
                            <a class="fw-semibold" href="<?= url('clientes', ['accion' => 'ver', 'id' => (int) $cliente['id_cliente']]) ?>">
                                <?= e(nombre_completo($cliente)) ?>
                            </a>
                            <div class="small text-secondary"><?= e($cliente['correo'] ?: 'Sin correo registrado') ?></div>
                        </td>
                        <td class="small">
                            <?= e($cliente['telefono'] ?: '—') ?>
                            <div class="text-secondary"><?= e($cliente['direccion'] ?: '') ?></div>
                        </td>
                        <td class="text-center"><span class="badge text-bg-light border"><?= (int) $cliente['total_ventas'] ?></span></td>
                        <td class="text-center"><span class="badge text-bg-light border"><?= (int) $cliente['total_equipos'] ?></span></td>
                        <td>
                            <span class="badge badge-estado text-bg-<?= $cliente['estado'] === 'Activo' ? 'success' : 'secondary' ?>">
                                <?= e($cliente['estado']) ?>
                            </span>
                        </td>
                        <td class="text-end">
                            <div class="acciones-tabla">
                                <a class="btn btn-sm btn-outline-primary" title="Ver historial"
                                   href="<?= url('clientes', ['accion' => 'ver', 'id' => (int) $cliente['id_cliente']]) ?>">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                                <?php if ($puedeEscribir): ?>
                                    <a class="btn btn-sm btn-outline-secondary" title="Editar"
                                       href="<?= url('clientes', ['accion' => 'editar', 'id' => (int) $cliente['id_cliente']]) ?>">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
                                    <form method="post" action="<?= url('clientes') ?>" class="d-inline"
                                          data-confirmar="¿Cambiar el estado de <?= e(nombre_completo($cliente)) ?> a <?= $cliente['estado'] === 'Activo' ? 'Inactivo' : 'Activo' ?>?">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="operacion" value="estado">
                                        <input type="hidden" name="id" value="<?= (int) $cliente['id_cliente'] ?>">
                                        <input type="hidden" name="estado" value="<?= $cliente['estado'] === 'Activo' ? 'Inactivo' : 'Activo' ?>">
                                        <button class="btn btn-sm btn-outline-<?= $cliente['estado'] === 'Activo' ? 'danger' : 'success' ?>"
                                                title="<?= $cliente['estado'] === 'Activo' ? 'Desactivar' : 'Activar' ?>">
                                            <i class="fa-solid <?= $cliente['estado'] === 'Activo' ? 'fa-user-slash' : 'fa-user-check' ?>"></i>
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
    Los clientes se desactivan de forma lógica: su historial de ventas, equipos y servicios se conserva
    para no perder información comercial.
</div>

<?php parcial('pie-admin'); ?>
