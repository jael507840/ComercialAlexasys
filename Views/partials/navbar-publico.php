<?php
/** Navegación del sitio público. */
$paginaActual = (string) ($_GET['pagina'] ?? 'inicio');
$enlaces = [
    'inicio'    => ['Inicio', url('index')],
    'nosotros'  => ['Nosotros', url('index', ['pagina' => 'nosotros'])],
    'productos' => ['Productos', url('index', ['pagina' => 'productos'])],
    'servicios' => ['Servicios', url('index', ['pagina' => 'servicios'])],
    'contactos' => ['Contactos', url('index', ['pagina' => 'contactos'])],
];
?>
<nav class="navbar navbar-expand-lg navbar-alex sticky-top">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="<?= url('index') ?>">
            <img class="marca-logo" src="<?= asset('imagen/logo/logo1.jfif') ?>" alt="Alex@sys · Tienda de tecnología">
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menuPublico"
                aria-controls="menuPublico" aria-expanded="false" aria-label="Abrir menú">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="menuPublico">
            <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
                <?php foreach ($enlaces as $clave => [$texto, $destino]): ?>
                    <li class="nav-item">
                        <a class="nav-link <?= $paginaActual === $clave ? 'active' : '' ?>" href="<?= e($destino) ?>">
                            <?= e($texto) ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
            <div class="d-flex gap-2">
                <?php if (esta_autenticado()): ?>
                    <a class="btn btn-primary" href="<?= url('index') ?>">
                        <i class="fa-solid fa-gauge-high me-1"></i> Panel
                    </a>
                <?php else: ?>
                    <a class="btn btn-outline-primary" href="<?= url('login') ?>">
                        <i class="fa-solid fa-right-to-bracket me-1"></i> Acceso
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>
