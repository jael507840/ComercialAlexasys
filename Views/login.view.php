<?php
/** Vista de acceso al sistema. Variables: $errores, $usuarioIngresado. */
$errores = $errores ?? [];
$usuarioIngresado = $usuarioIngresado ?? '';
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Acceso al sistema · Alex@sys</title>
    <link rel="icon" type="image/svg+xml" href="<?= asset('imagen/logo/favicon.svg') ?>">
    <link rel="stylesheet" href="<?= asset('assets/vendor/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= asset('assets/vendor/fontawesome.min.css') ?>">
    <link rel="stylesheet" href="<?= asset('assets/css/estilos.css') ?>">
</head>
<body class="pagina-login">
<div class="tarjeta-login">
    <div class="row g-0">
        <div class="col-lg-5 panel-login-marca d-flex flex-column">
            <span class="marca-logo-fondo mb-4">
                <img class="marca-logo" src="<?= asset('imagen/logo/logo1.jfif') ?>" alt="Alex@sys · Tienda de tecnología">
            </span>
            <h1 class="h3 fw-bold mb-3">Panel de gestión</h1>
            <p class="mb-4 text-info-emphasis">Controle clientes, ventas, inventario y servicio técnico desde un solo lugar.</p>
            <ul class="mt-auto">
                <li><i class="fa-solid fa-circle-check mt-1"></i><span>Ventas con control de stock y pagos</span></li>
                <li><i class="fa-solid fa-circle-check mt-1"></i><span>Compras y costos de adquisición</span></li>
                <li><i class="fa-solid fa-circle-check mt-1"></i><span>Órdenes de servicio y repuestos</span></li>
                <li><i class="fa-solid fa-circle-check mt-1"></i><span>Garantías verificables por cliente o equipo</span></li>
                <li><i class="fa-solid fa-circle-check mt-1"></i><span>Reportes con información real</span></li>
            </ul>
            <a class="btn btn-outline-light btn-sm mt-4 align-self-start" href="<?= url('index') ?>?pagina=inicio">
                <i class="fa-solid fa-arrow-left me-1"></i> Volver al sitio
            </a>
        </div>
        <div class="col-lg-7 bg-white">
            <div class="p-4 p-md-5">
                <h2 class="h4 fw-bold mb-1">Iniciar sesión</h2>
                <p class="text-secondary mb-4">Ingrese sus credenciales de usuario del sistema.</p>

                <?php if ($errores !== []): ?>
                    <div class="alert alert-danger">
                        <div class="fw-semibold mb-1"><i class="fa-solid fa-circle-exclamation me-1"></i> No fue posible ingresar</div>
                        <ul class="mb-0 ps-3">
                            <?php foreach ($errores as $error): ?>
                                <li><?= e($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <?php parcial('mensajes'); ?>

                <form method="post" action="<?= url('login') ?>" autocomplete="off" novalidate>
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label requerido" for="usuario">Usuario</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa-solid fa-user"></i></span>
                            <input class="form-control" id="usuario" name="usuario" type="text"
                                   value="<?= e($usuarioIngresado) ?>" maxlength="50" required autofocus>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label requerido" for="password">Contraseña</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
                            <input class="form-control" id="password" name="password" type="password"
                                   maxlength="100" required>
                            <button class="btn btn-outline-secondary" type="button" id="verPassword"
                                    aria-label="Mostrar u ocultar contraseña">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    <button class="btn btn-primary w-100 py-2" type="submit">
                        <i class="fa-solid fa-right-to-bracket me-2"></i> Ingresar al sistema
                    </button>
                </form>

                <?php if (config('demo.activo')): ?>
                    <div class="alert alert-warning small mt-4 mb-0">
                        <div class="fw-semibold mb-1"><i class="fa-solid fa-flask me-1"></i> Usuarios de demostración</div>
                        <div class="row g-1 mt-1">
                            <div class="col-sm-4"><code>gerente</code> / Admin@123</div>
                            <div class="col-sm-4"><code>vendedor</code> / Venta@123</div>
                            <div class="col-sm-4"><code>tecnico</code> / Tecnico@123</div>
                        </div>
                        <div class="mt-2">Cambie estas contraseñas antes de operar el sistema en producción.</div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<script src="<?= asset('assets/vendor/bootstrap.bundle.min.js') ?>"></script>
<script>
    document.getElementById('verPassword')?.addEventListener('click', function () {
        const campo = document.getElementById('password');
        const icono = this.querySelector('i');
        const visible = campo.type === 'text';
        campo.type = visible ? 'password' : 'text';
        icono.className = visible ? 'fa-solid fa-eye' : 'fa-solid fa-eye-slash';
    });
</script>
</body>
</html>
