<?php
/** Módulo 11: equipos de clientes. */
parcial('head-admin', ['titulo' => $titulo, 'subtitulo' => $subtitulo]);
$editando = $equipoEdicion !== null;
$abrirFormulario = $editando || $errores !== [];
$puedeEscribir = puede_escribir('equipos');
?>

<div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-4">
    <div>
        <h1 class="titulo-pagina h3 mb-1">Equipos de clientes</h1>
        <p class="text-secondary mb-0 small">Equipos que ingresan al servicio técnico, con o sin compra en la tienda.</p>
    </div>
    <?php if ($puedeEscribir): ?>
        <button class="btn btn-primary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#panelEquipo">
            <i class="fa-solid fa-laptop-medical me-1"></i>
            <?= $editando ? 'Editar equipo' : 'Nuevo equipo' ?>
        </button>
    <?php endif; ?>
</div>

<?php if ($errores !== []): ?>
    <div class="alert alert-danger">
        <div class="fw-semibold mb-1"><i class="fa-solid fa-circle-exclamation me-1"></i> Revise los datos del equipo</div>
        <ul class="mb-0 ps-3"><?php foreach ($errores as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul>
    </div>
<?php endif; ?>

<?php if ($puedeEscribir): ?>
<div class="collapse <?= $abrirFormulario ? 'show' : '' ?> mb-4" id="panelEquipo">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="fa-solid fa-laptop-medical me-2 text-primary"></i><?= $editando ? 'Editar equipo' : 'Registrar equipo' ?></span>
            <?php if ($editando): ?>
                <a class="btn btn-sm btn-light border" href="<?= url('equipos') ?>">
                    <i class="fa-solid fa-xmark me-1"></i> Cancelar edición
                </a>
            <?php endif; ?>
        </div>
        <div class="card-body">
            <form method="post" action="<?= url('equipos') ?>" novalidate>
                <?= csrf_field() ?>
                <input type="hidden" name="operacion" value="<?= $editando ? 'actualizar' : 'crear' ?>">
                <input type="hidden" name="id" value="<?= (int) $formulario['id_equipo'] ?>">
                <div class="row g-3">
                    <div class="col-md-5">
                        <label class="form-label requerido" for="id_cliente">Cliente</label>
                        <select class="form-select" id="id_cliente" name="id_cliente" required>
                            <option value="0">Seleccione un cliente…</option>
                            <?php foreach ($clientes as $cliente): ?>
                                <option value="<?= (int) $cliente['id_cliente'] ?>"
                                    <?= (int) $formulario['id_cliente'] === (int) $cliente['id_cliente'] ? 'selected' : '' ?>>
                                    <?= e($cliente['ci'] . ' · ' . nombre_completo($cliente)) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label requerido" for="tipo_equipo">Tipo de equipo</label>
                        <select class="form-select" id="tipo_equipo" name="tipo_equipo" required>
                            <?php foreach (EquipoCliente::TIPOS as $tipo): ?>
                                <option value="<?= e($tipo) ?>" <?= $formulario['tipo_equipo'] === $tipo ? 'selected' : '' ?>><?= e($tipo) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="id_producto">Producto asociado (opcional)</label>
                        <select class="form-select" id="id_producto" name="id_producto">
                            <option value="0">Sin producto asociado</option>
                            <?php foreach ($productos as $producto): ?>
                                <option value="<?= (int) $producto['id_producto'] ?>"
                                    <?= (int) $formulario['id_producto'] === (int) $producto['id_producto'] ? 'selected' : '' ?>>
                                    <?= e($producto['codigo'] . ' · ' . $producto['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text">Vincule el equipo a un producto vendido en la tienda.</div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="marca">Marca</label>
                        <input class="form-control" id="marca" name="marca" maxlength="60"
                               value="<?= e($formulario['marca']) ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="modelo">Modelo</label>
                        <input class="form-control" id="modelo" name="modelo" maxlength="60"
                               value="<?= e($formulario['modelo']) ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="numero_serie">Número de serie</label>
                        <input class="form-control" id="numero_serie" name="numero_serie" maxlength="60"
                               value="<?= e($formulario['numero_serie']) ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="descripcion">Descripción</label>
                        <input class="form-control" id="descripcion" name="descripcion" maxlength="255"
                               value="<?= e($formulario['descripcion']) ?>">
                    </div>
                </div>
                <div class="mt-3 d-flex gap-2">
                    <button class="btn btn-primary" type="submit">
                        <i class="fa-solid fa-floppy-disk me-1"></i>
                        <?= $editando ? 'Guardar cambios' : 'Registrar equipo' ?>
                    </button>
                    <button class="btn btn-light border" type="button" data-bs-toggle="collapse" data-bs-target="#panelEquipo">
                        Cerrar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="card mb-4">
    <div class="card-header"><i class="fa-solid fa-filter me-2 text-primary"></i>Filtros</div>
    <div class="card-body">
        <form class="row g-2 align-items-end" method="get" action="<?= asset('index.php') ?>">
            <input type="hidden" name="ruta" value="equipos">
            <div class="col-md-5">
                <label class="form-label small mb-1">Buscar</label>
                <input class="form-control form-control-sm" type="search" name="q" value="<?= e($busqueda) ?>"
                       placeholder="Serie, marca, modelo, CI o cliente">
            </div>
            <div class="col-md-4">
                <label class="form-label small mb-1">Cliente</label>
                <select class="form-select form-select-sm" name="cliente">
                    <option value="0">Todos los clientes</option>
                    <?php foreach ($clientes as $cliente): ?>
                        <option value="<?= (int) $cliente['id_cliente'] ?>" <?= $idCliente === (int) $cliente['id_cliente'] ? 'selected' : '' ?>>
                            <?= e($cliente['ci'] . ' · ' . nombre_completo($cliente)) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button class="btn btn-sm btn-primary flex-grow-1" type="submit"><i class="fa-solid fa-filter me-1"></i>Filtrar</button>
                <a class="btn btn-sm btn-outline-secondary" href="<?= url('equipos') ?>"><i class="fa-solid fa-rotate-left"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fa-solid fa-laptop-medical me-2 text-primary"></i>Equipos registrados</span>
        <span class="small text-secondary"><?= count($equipos) ?> resultado(s)</span>
    </div>
    <div class="tabla-scroll">
        <table class="table table-alex table-hover mb-0">
            <thead>
                <tr>
                    <th>Equipo</th>
                    <th>Serie</th>
                    <th>Cliente</th>
                    <th>Tipo</th>
                    <th class="text-center">Órdenes</th>
                    <th>Registrado</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($equipos === []): ?>
                    <tr><td colspan="7" class="text-center text-secondary py-4">No se encontraron equipos con los filtros aplicados.</td></tr>
                <?php endif; ?>
                <?php foreach ($equipos as $equipo): ?>
                    <tr>
                        <td>
                            <a class="fw-semibold" href="<?= url('equipos', ['accion' => 'ver', 'id' => (int) $equipo['id_equipo']]) ?>">
                                <?= e(trim(($equipo['marca'] ?? '') . ' ' . ($equipo['modelo'] ?? '')) ?: 'Equipo sin marca') ?>
                            </a>
                            <div class="small text-secondary"><?= e($equipo['descripcion'] ?: 'Sin descripción') ?></div>
                        </td>
                        <td class="small"><?= e($equipo['numero_serie'] ?: '—') ?></td>
                        <td class="small">
                            <?= e(nombre_completo($equipo)) ?>
                            <div class="text-secondary" style="font-size:.72rem">CI <?= e($equipo['ci']) ?></div>
                        </td>
                        <td class="small"><?= e($equipo['tipo_equipo']) ?></td>
                        <td class="text-center"><span class="badge text-bg-light border"><?= (int) $equipo['total_ordenes'] ?></span></td>
                        <td class="small"><?= e(formato_fecha($equipo['fecha_registro'])) ?></td>
                        <td class="text-end">
                            <div class="acciones-tabla">
                                <a class="btn btn-sm btn-outline-primary" title="Ver historial"
                                   href="<?= url('equipos', ['accion' => 'ver', 'id' => (int) $equipo['id_equipo']]) ?>">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                                <?php if ($puedeEscribir): ?>
                                    <a class="btn btn-sm btn-outline-secondary" title="Editar"
                                       href="<?= url('equipos', ['accion' => 'editar', 'id' => (int) $equipo['id_equipo']]) ?>">
                                        <i class="fa-solid fa-pen-to-square"></i>
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
