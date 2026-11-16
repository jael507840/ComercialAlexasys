<?php
/** Módulo 2: usuarios y roles. */
parcial('head-admin', ['titulo' => $titulo, 'subtitulo' => $subtitulo]);
$usuarioSesion = (int) (usuario_actual()['id_usuario'] ?? 0);
?>

<div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-4">
    <div>
        <h1 class="titulo-pagina h3 mb-1">Usuarios y roles</h1>
        <p class="text-secondary mb-0 small">
            Cada usuario debe tener al menos un rol. Los permisos del sistema se derivan del rol asignado.
        </p>
    </div>
    <span class="badge text-bg-light border py-2 px-3">
        <i class="fa-solid fa-user-shield me-1 text-primary"></i>
        <?= (int) $totalActivos ?> usuario(s) activo(s)
    </span>
</div>

<?php if ($errores !== []): ?>
    <div class="alert alert-danger">
        <div class="fw-semibold mb-1"><i class="fa-solid fa-circle-exclamation me-1"></i> Revise los datos del formulario</div>
        <ul class="mb-0 ps-3">
            <?php foreach ($errores as $error): ?><li><?= e($error) ?></li><?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header"><i class="fa-solid fa-user-plus me-2 text-primary"></i>Registrar usuario</div>
            <div class="card-body">
                <form method="post" action="<?= url('registrar-usuario') ?>" autocomplete="off" novalidate>
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label requerido" for="nombres">Nombres</label>
                        <input class="form-control" id="nombres" name="nombres" maxlength="100" required
                               value="<?= e($formulario['nombres']) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label requerido" for="apellidos">Apellidos</label>
                        <input class="form-control" id="apellidos" name="apellidos" maxlength="100" required
                               value="<?= e($formulario['apellidos']) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label requerido" for="usuario">Nombre de usuario</label>
                        <input class="form-control" id="usuario" name="usuario" maxlength="50" required
                               pattern="[A-Za-z0-9._-]{4,50}" value="<?= e($formulario['usuario']) ?>">
                        <div class="form-text">Entre 4 y 50 caracteres: letras, números, punto, guion o guion bajo.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="telefono">Teléfono</label>
                        <input class="form-control" id="telefono" name="telefono" maxlength="20"
                               value="<?= e($formulario['telefono']) ?>">
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label requerido" for="password">Contraseña</label>
                            <input class="form-control" id="password" name="password" type="password" minlength="8" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label requerido" for="password_confirmar">Confirmar</label>
                            <input class="form-control" id="password_confirmar" name="password_confirmar" type="password" minlength="8" required>
                        </div>
                    </div>
                    <div class="form-text mb-3 mt-n2">Mínimo 8 caracteres, con letras y números.</div>
                    <div class="mb-3">
                        <label class="form-label requerido">Roles</label>
                        <?php foreach ($roles as $rol): ?>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="roles[]"
                                       id="rol_<?= (int) $rol['id_rol'] ?>" value="<?= (int) $rol['id_rol'] ?>"
                                       <?= in_array((int) $rol['id_rol'], $formulario['roles'], true) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="rol_<?= (int) $rol['id_rol'] ?>">
                                    <span class="fw-semibold"><?= e($rol['nombre']) ?></span>
                                    <span class="d-block small text-secondary"><?= e($rol['descripcion'] ?? '') ?></span>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="estado">Estado</label>
                        <select class="form-select" id="estado" name="estado">
                            <option value="Activo" <?= $formulario['estado'] === 'Activo' ? 'selected' : '' ?>>Activo</option>
                            <option value="Inactivo" <?= $formulario['estado'] === 'Inactivo' ? 'selected' : '' ?>>Inactivo</option>
                        </select>
                    </div>
                    <button class="btn btn-primary w-100" type="submit">
                        <i class="fa-solid fa-floppy-disk me-1"></i> Registrar usuario
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fa-solid fa-users me-2 text-primary"></i>Usuarios registrados</span>
                <span class="small text-secondary"><?= count($usuarios) ?> registro(s)</span>
            </div>
            <div class="tabla-scroll">
                <table class="table table-alex table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Usuario</th>
                            <th>Nombre completo</th>
                            <th>Roles</th>
                            <th>Estado</th>
                            <th>Registro</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($usuarios === []): ?>
                            <tr><td colspan="6" class="text-center text-secondary py-4">No hay usuarios registrados.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($usuarios as $fila): ?>
                            <tr>
                                <td>
                                    <div class="fw-semibold"><?= e($fila['usuario']) ?></div>
                                    <?php if ((int) $fila['id_usuario'] === $usuarioSesion): ?>
                                        <span class="badge text-bg-primary" style="font-size:.65rem">Usted</span>
                                    <?php endif; ?>
                                </td>
                                <td class="small"><?= e(nombre_completo($fila)) ?></td>
                                <td class="small"><?= e($fila['roles'] ?? '—') ?></td>
                                <td>
                                    <span class="badge badge-estado text-bg-<?= $fila['estado'] === 'Activo' ? 'success' : 'secondary' ?>">
                                        <?= e($fila['estado']) ?>
                                    </span>
                                </td>
                                <td class="small text-secondary"><?= e(formato_fecha($fila['fecha_registro'])) ?></td>
                                <td class="text-end">
                                    <a class="btn btn-sm btn-outline-primary" href="<?= url('actualizar-usuario', ['id' => (int) $fila['id_usuario']]) ?>">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header"><i class="fa-solid fa-key me-2 text-warning"></i>Matriz de permisos por rol</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="border rounded-3 p-3 h-100">
                            <div class="fw-bold text-primary">Gerente</div>
                            <div class="small text-secondary">Acceso total: usuarios, compras, precios, reportes y todos los módulos operativos.</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="border rounded-3 p-3 h-100">
                            <div class="fw-bold text-primary">Vendedor</div>
                            <div class="small text-secondary">Clientes, ventas, pagos de ventas, equipos, garantías y consultas de catálogo e inventario.</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="border rounded-3 p-3 h-100">
                            <div class="fw-bold text-primary">Técnico</div>
                            <div class="small text-secondary">Equipos, órdenes de servicio, intervenciones, repuestos, pagos de servicio y garantías.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php parcial('pie-admin'); ?>
