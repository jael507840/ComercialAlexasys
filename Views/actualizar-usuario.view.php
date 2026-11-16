<?php
/** Módulo 2: edición de usuarios. */
parcial('head-admin', ['titulo' => $titulo, 'subtitulo' => $subtitulo]);
$esPropiaCuenta = (int) $usuario['id_usuario'] === (int) (usuario_actual()['id_usuario'] ?? 0);
?>

<div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-4">
    <div>
        <h1 class="titulo-pagina h3 mb-1">Editar usuario</h1>
        <p class="text-secondary mb-0 small">
            <?= e(nombre_completo($usuario)) ?> · <code><?= e($usuario['usuario']) ?></code>
        </p>
    </div>
    <a class="btn btn-outline-secondary btn-sm" href="<?= url('registrar-usuario') ?>">
        <i class="fa-solid fa-arrow-left me-1"></i> Volver a la lista
    </a>
</div>

<?php if ($errores !== []): ?>
    <div class="alert alert-danger">
        <div class="fw-semibold mb-1"><i class="fa-solid fa-circle-exclamation me-1"></i> Revise los datos</div>
        <ul class="mb-0 ps-3">
            <?php foreach ($errores as $error): ?><li><?= e($error) ?></li><?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header"><i class="fa-solid fa-user-pen me-2 text-primary"></i>Datos del usuario</div>
            <div class="card-body">
                <form method="post" action="<?= url('actualizar-usuario', ['id' => (int) $usuario['id_usuario']]) ?>" autocomplete="off" novalidate>
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" value="<?= (int) $usuario['id_usuario'] ?>">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label requerido" for="nombres">Nombres</label>
                            <input class="form-control" id="nombres" name="nombres" maxlength="100" required
                                   value="<?= e($formulario['nombres']) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label requerido" for="apellidos">Apellidos</label>
                            <input class="form-control" id="apellidos" name="apellidos" maxlength="100" required
                                   value="<?= e($formulario['apellidos']) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label requerido" for="usuario">Nombre de usuario</label>
                            <input class="form-control" id="usuario" name="usuario" maxlength="50" required
                                   pattern="[A-Za-z0-9._-]{4,50}" value="<?= e($formulario['usuario']) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="telefono">Teléfono</label>
                            <input class="form-control" id="telefono" name="telefono" maxlength="20"
                                   value="<?= e($formulario['telefono']) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="estado">Estado</label>
                            <select class="form-select" id="estado" name="estado" <?= $esPropiaCuenta ? 'disabled' : '' ?>>
                                <option value="Activo" <?= $formulario['estado'] === 'Activo' ? 'selected' : '' ?>>Activo</option>
                                <option value="Inactivo" <?= $formulario['estado'] === 'Inactivo' ? 'selected' : '' ?>>Inactivo</option>
                            </select>
                            <?php if ($esPropiaCuenta): ?>
                                <input type="hidden" name="estado" value="<?= e($formulario['estado']) ?>">
                                <div class="form-text">No puede desactivar su propia cuenta.</div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <hr class="my-4">
                    <h2 class="h6 fw-bold">Roles asignados</h2>
                    <p class="small text-secondary">El usuario accede a los módulos permitidos por la unión de sus roles.</p>
                    <?php foreach ($roles as $rol): ?>
                        <?php
                        $esRolGerente = $rol['nombre'] === 'Gerente';
                        $bloqueado = $esPropiaCuenta && $esRolGerente;
                        ?>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="roles[]"
                                   id="rol_<?= (int) $rol['id_rol'] ?>" value="<?= (int) $rol['id_rol'] ?>"
                                   <?= in_array((int) $rol['id_rol'], $formulario['roles'], true) ? 'checked' : '' ?>
                                   <?= $bloqueado ? 'disabled' : '' ?>>
                            <label class="form-check-label" for="rol_<?= (int) $rol['id_rol'] ?>">
                                <span class="fw-semibold"><?= e($rol['nombre']) ?></span>
                                <span class="d-block small text-secondary"><?= e($rol['descripcion'] ?? '') ?></span>
                            </label>
                            <?php if ($bloqueado): ?>
                                <input type="hidden" name="roles[]" value="<?= (int) $rol['id_rol'] ?>">
                                <div class="form-text">Su propio rol Gerente no puede retirarse desde esta cuenta.</div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>

                    <hr class="my-4">
                    <h2 class="h6 fw-bold">Cambio de contraseña <span class="text-secondary fw-normal">(opcional)</span></h2>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="password">Nueva contraseña</label>
                            <input class="form-control" id="password" name="password" type="password" minlength="8">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="password_confirmar">Confirmar contraseña</label>
                            <input class="form-control" id="password_confirmar" name="password_confirmar" type="password" minlength="8">
                        </div>
                    </div>
                    <div class="form-text mt-2">Deje ambos campos vacíos para conservar la contraseña actual.</div>

                    <div class="d-flex gap-2 mt-4">
                        <button class="btn btn-primary" type="submit">
                            <i class="fa-solid fa-floppy-disk me-1"></i> Guardar cambios
                        </button>
                        <a class="btn btn-light border" href="<?= url('registrar-usuario') ?>">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card">
            <div class="card-header"><i class="fa-solid fa-circle-info me-2 text-primary"></i>Información de la cuenta</div>
            <div class="card-body">
                <dl class="row small mb-0">
                    <dt class="col-5 text-secondary">Identificador</dt>
                    <dd class="col-7">#<?= (int) $usuario['id_usuario'] ?></dd>
                    <dt class="col-5 text-secondary">Registro</dt>
                    <dd class="col-7"><?= e(formato_fecha($usuario['fecha_registro'], true)) ?></dd>
                    <dt class="col-5 text-secondary">Estado actual</dt>
                    <dd class="col-7">
                        <span class="badge badge-estado text-bg-<?= $usuario['estado'] === 'Activo' ? 'success' : 'secondary' ?>">
                            <?= e($usuario['estado']) ?>
                        </span>
                    </dd>
                </dl>
                <div class="alert alert-warning small mt-3 mb-0">
                    <i class="fa-solid fa-triangle-exclamation me-1"></i>
                    Las contraseñas se almacenan con <code>password_hash()</code>. Nunca se muestra la contraseña original.
                </div>
            </div>
        </div>
    </div>
</div>

<?php parcial('pie-admin'); ?>
