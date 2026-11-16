<?php /** Pie del sitio público. */ ?>
<footer class="pie">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-4">
                <span class="marca-logo-fondo mb-3">
                    <img class="marca-logo" src="<?= asset('imagen/logo/logo1.jfif') ?>" alt="Alex@sys · Tienda de tecnología">
                </span>
                <p class="small mb-2">
                    Tienda comercial de equipos informáticos y servicio técnico especializado.
                    Ventas, mantenimiento, reparación y garantías con atención personalizada.
                </p>
                <p class="small mb-0">Oruro, Bolivia</p>
            </div>
            <div class="col-6 col-lg-2">
                <h6 class="text-white text-uppercase small fw-bold mb-3">Sitio</h6>
                <ul class="list-unstyled small">
                    <li class="mb-2"><a href="<?= url('index') ?>">Inicio</a></li>
                    <li class="mb-2"><a href="<?= url('index', ['pagina' => 'nosotros']) ?>">Nosotros</a></li>
                    <li class="mb-2"><a href="<?= url('index', ['pagina' => 'productos']) ?>">Productos</a></li>
                    <li class="mb-2"><a href="<?= url('index', ['pagina' => 'servicios']) ?>">Servicios</a></li>
                    <li><a href="<?= url('index', ['pagina' => 'contactos']) ?>">Contactos</a></li>
                </ul>
            </div>
            <div class="col-6 col-lg-2">
                <h6 class="text-white text-uppercase small fw-bold mb-3">Servicios</h6>
                <ul class="list-unstyled small">
                    <li class="mb-2">Mantenimiento</li>
                    <li class="mb-2">Reparación</li>
                    <li class="mb-2">Impresoras</li>
                    <li class="mb-2">Repuestos</li>
                    <li>Garantías</li>
                </ul>
            </div>
            <div class="col-lg-4">
                <h6 class="text-white text-uppercase small fw-bold mb-3">Contacto</h6>
                <ul class="list-unstyled small">
                    <li class="mb-2"><i class="fa-solid fa-location-dot me-2"></i>Av. Principal, Oruro - Bolivia</li>
                    <li class="mb-2"><i class="fa-solid fa-phone me-2"></i>+591 700 00000</li>
                    <li class="mb-2"><i class="fa-solid fa-envelope me-2"></i>contacto@alexsys.bo</li>
                    <li><i class="fa-regular fa-clock me-2"></i>Lun a Sáb · 08:30 - 19:00</li>
                </ul>
                <a class="btn btn-sm btn-outline-light mt-2" href="<?= url('login') ?>">
                    <i class="fa-solid fa-lock me-1"></i> Acceso al sistema
                </a>
            </div>
        </div>
        <hr class="border-secondary my-4">
        <div class="d-flex flex-wrap justify-content-between gap-2 small">
            <span>&copy; <?= date('Y') ?> Alex@sys. Todos los derechos reservados.</span>
            <span>Sistema de gestión · PHP 8 + MySQL</span>
        </div>
    </div>
</footer>
<script src="<?= asset('assets/vendor/bootstrap.bundle.min.js') ?>"></script>
<script src="<?= asset('assets/js/app.js') ?>"></script>
</body>
</html>
