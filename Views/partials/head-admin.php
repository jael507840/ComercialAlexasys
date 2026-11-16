<?php
/**
 * Cabecera del panel administrativo.
 * Variables opcionales: $titulo, $subtitulo.
 */
$titulo = $titulo ?? 'Panel';
$subtitulo = $subtitulo ?? 'Resumen general del sistema';
$actual = ruta_actual();
$usuario = usuario_actual() ?? [];
$nombreUsuario = trim(($usuario['nombres'] ?? '') . ' ' . ($usuario['apellidos'] ?? ''));
$rolesUsuario = roles_usuario();

$menu = [
    'Principal' => [
        ['index', 'Dashboard', 'dashboard.svg'],
    ],
    'Comercial' => [
        ['clientes', 'Clientes', 'clientes.svg'],
        ['ventas', 'Ventas', 'ventas.svg'],
        ['pagos-venta', 'Pagos de ventas', 'pagos.svg'],
    ],
    'Catálogo e inventario' => [
        ['categorias', 'Categorías', 'categorias.svg'],
        ['productos', 'Productos', 'productos.svg'],
        ['proveedores', 'Proveedores', 'proveedores.svg'],
        ['compras', 'Compras', 'compras.svg'],
        ['inventario', 'Inventario', 'inventario.svg'],
    ],
    'Servicio técnico' => [
        ['equipos', 'Equipos de clientes', 'equipos.svg'],
        ['ordenes-servicio', 'Órdenes de servicio', 'servicio.svg'],
        ['detalle-servicio', 'Intervenciones', 'intervenciones.svg'],
        ['repuestos-servicio', 'Repuestos usados', 'repuestos.svg'],
        ['pagos-servicio', 'Pagos de servicios', 'pagos.svg'],
    ],
    'Control' => [
        ['garantias', 'Garantías', 'garantias.svg'],
        ['reportes', 'Reportes', 'reportes.svg'],
        ['registrar-usuario', 'Usuarios y roles', 'usuarios.svg'],
    ],
];
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($titulo) ?> · Alex@sys</title>
    <link rel="icon" type="image/svg+xml" href="<?= asset('imagen/logo/favicon.svg') ?>">
    <link rel="stylesheet" href="<?= asset('assets/vendor/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= asset('assets/vendor/fontawesome.min.css') ?>">
    <link rel="stylesheet" href="<?= asset('assets/css/estilos.css') ?>">
</head>
<body>
<div class="layout-admin">
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-marca">
            <a href="<?= url('index') ?>" class="d-block">
                <span class="marca-logo-fondo">
                    <img class="marca-logo" src="<?= asset('imagen/logo/logo1.jfif') ?>" alt="Alex@sys · Tienda de tecnología">
                </span>
            </a>
        </div>
        <nav class="py-2">
            <?php foreach ($menu as $grupo => $enlaces): ?>
                <?php
                $visibles = array_values(array_filter($enlaces, static fn (array $enlace): bool => puede($enlace[0])));
                if ($visibles === []) {
                    continue;
                }
                ?>
                <div class="menu-titulo"><?= e($grupo) ?></div>
                <ul class="nav flex-column">
                    <?php foreach ($visibles as [$ruta, $texto, $icono]): ?>
                        <li class="nav-item">
                            <a class="nav-link <?= $actual === $ruta ? 'active' : '' ?>" href="<?= url($ruta) ?>">
                                <img class="menu-icono" src="<?= asset('imagen/iconos/' . $icono) ?>" alt="" width="20" height="20">
                                <span><?= e($texto) ?></span>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endforeach; ?>
        </nav>
        <div class="px-3 py-3 mt-auto small text-secondary border-top border-secondary border-opacity-25">
            <div class="d-flex justify-content-between">
                <span>Alex@sys v<?= e(APP_VERSION) ?></span>
                <a class="link-light" href="<?= url('index') ?>?pagina=inicio" title="Ver sitio público">
                    <i class="fa-solid fa-globe"></i>
                </a>
            </div>
            <?php if (config('demo.activo')): ?>
                <div class="mt-2 text-warning-emphasis bg-warning-subtle rounded p-2" style="font-size:.72rem">
                    <i class="fa-solid fa-flask me-1"></i> Entorno de demostración
                </div>
            <?php endif; ?>
        </div>
    </aside>
    <div class="telon-sidebar" id="telonSidebar"></div>

    <div class="contenido-admin">
        <header class="topbar">
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-outline-secondary btn-sm d-lg-none" id="btnMenu" type="button" aria-label="Abrir menú">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <div class="flex-grow-1 min-w-0">
                    <div class="migaja">
                        <a class="text-secondary" href="<?= url('index') ?>">Alex@sys</a>
                        <i class="fa-solid fa-angle-right mx-1 small"></i>
                        <?= e($titulo) ?>
                    </div>
                    <div class="fw-bold text-truncate"><?= e($subtitulo) ?></div>
                </div>
                <div class="d-none d-md-block text-end small text-secondary">
                    <div><?= e(date('d/m/Y')) ?></div>
                    <div><?= e(date('H:i')) ?></div>
                </div>
                <div class="dropdown">
                    <button class="btn btn-light border d-flex align-items-center gap-2" type="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                        <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-primary text-white fw-bold"
                              style="width:34px;height:34px;font-size:.8rem">
                            <?= e(mb_strtoupper(mb_substr($usuario['nombres'] ?? 'U', 0, 1) . mb_substr($usuario['apellidos'] ?? '', 0, 1))) ?>
                        </span>
                        <span class="d-none d-sm-inline text-start" style="line-height:1.1">
                            <span class="d-block small fw-semibold"><?= e($nombreUsuario) ?></span>
                            <span class="d-block text-secondary" style="font-size:.7rem"><?= e(implode(', ', $rolesUsuario)) ?></span>
                        </span>
                        <i class="fa-solid fa-chevron-down small text-secondary"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                        <li><h6 class="dropdown-header"><?= e($usuario['usuario'] ?? '') ?></h6></li>
                        <li><a class="dropdown-item" href="<?= url('index') ?>?pagina=inicio"><i class="fa-solid fa-globe me-2"></i>Sitio público</a></li>
                        <?php if (puede('registrar-usuario')): ?>
                            <li><a class="dropdown-item" href="<?= url('registrar-usuario') ?>"><i class="fa-solid fa-users-gear me-2"></i>Usuarios y roles</a></li>
                        <?php endif; ?>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="post" action="<?= url('logout') ?>" class="px-2">
                                <?= csrf_field() ?>
                                <button class="btn btn-sm btn-outline-danger w-100" type="submit">
                                    <i class="fa-solid fa-right-from-bracket me-1"></i> Cerrar sesión
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </header>

        <main class="contenido-principal">
            <?php parcial('mensajes'); ?>
