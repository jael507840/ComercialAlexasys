<?php /** Página pública: Servicios. */ ?>
<section class="bg-white border-bottom">
    <div class="container py-5">
        <nav aria-label="Ruta de navegación">
            <ol class="breadcrumb small mb-2">
                <li class="breadcrumb-item"><a href="<?= url('index') ?>">Inicio</a></li>
                <li class="breadcrumb-item active" aria-current="page">Servicios</li>
            </ol>
        </nav>
        <h1 class="seccion-titulo h2 mb-2">Servicios técnicos</h1>
        <p class="seccion-subtitulo mb-0">
            Mantenimiento, diagnóstico y reparación de computadoras e impresoras con registro
            completo del trabajo y garantía del servicio.
        </p>
    </div>
</section>

<section class="seccion">
    <div class="container">
        <div class="row g-4">
            <?php
            $servicios = [
                [
                    'imagen' => 'imagen/servicios/mantenimiento.jpg',
                    'titulo' => 'Mantenimiento preventivo',
                    'texto'  => 'Limpieza interna, cambio de pasta térmica, revisión de ventiladores y optimización del sistema para prolongar la vida útil del equipo.',
                    'items'  => ['Computadoras de escritorio', 'Laptops', 'Limpieza y ajuste de software'],
                ],
                [
                    'imagen' => 'imagen/servicios/reparacion.jpg',
                    'titulo' => 'Diagnóstico y reparación',
                    'texto'  => 'Evaluación de fallas de hardware y software, reemplazo de componentes y pruebas de funcionamiento antes de la entrega.',
                    'items'  => ['Equipos que no encienden', 'Cambio de disco o memoria', 'Reparación de conector de carga'],
                ],
                [
                    'imagen' => 'imagen/servicios/impresoras.jpg',
                    'titulo' => 'Mantenimiento de impresoras',
                    'texto'  => 'Configuración, limpieza de cabezales, revisión de sistema continuo y solución de problemas de impresión.',
                    'items'  => ['Impresoras multifuncionales', 'Sistema continuo', 'Configuración WiFi'],
                ],
                [
                    'imagen' => 'imagen/servicios/configuracion-laptop.jpg',
                    'titulo' => 'Configuración de laptops',
                    'texto'  => 'Instalación y configuración del sistema, controladores y programas necesarios para dejar su equipo listo para trabajar.',
                    'items'  => ['Instalación de sistema', 'Controladores', 'Configuración inicial'],
                ],
                [
                    'imagen' => 'imagen/servicios/reemplazo-ram-solido.jpg',
                    'titulo' => 'Actualización de componentes',
                    'texto'  => 'Mejoramos el rendimiento de su equipo con ampliaciones de memoria RAM y unidades de almacenamiento sólido.',
                    'items'  => ['Ampliación de memoria', 'Instalación de SSD', 'Pruebas de funcionamiento'],
                ],
            ];
            foreach ($servicios as $servicio): ?>
                <div class="col-lg-4">
                    <div class="tarjeta-servicio">
                        <img src="<?= asset($servicio['imagen']) ?>" alt="<?= e($servicio['titulo']) ?>">
                        <div class="p-4">
                            <h2 class="h5 fw-bold"><?= e($servicio['titulo']) ?></h2>
                            <p class="small text-secondary"><?= e($servicio['texto']) ?></p>
                            <ul class="small text-secondary ps-3 mb-0">
                                <?php foreach ($servicio['items'] as $item): ?>
                                    <li class="mb-1"><?= e($item) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="row g-4 mt-4">
            <div class="col-lg-7">
                <h2 class="h4 fw-bold mb-3">¿Cómo se registra un servicio?</h2>
                <div class="list-group list-group-flush">
                    <div class="list-group-item px-0 py-3">
                        <div class="fw-semibold"><i class="fa-solid fa-1 me-2 text-primary"></i>Recepción del equipo</div>
                        <div class="small text-secondary">Se registra el cliente, el equipo, la falla reportada y los accesorios recibidos.</div>
                    </div>
                    <div class="list-group-item px-0 py-3">
                        <div class="fw-semibold"><i class="fa-solid fa-2 me-2 text-primary"></i>Diagnóstico técnico</div>
                        <div class="small text-secondary">El técnico documenta el diagnóstico y el trabajo a realizar en la orden de servicio.</div>
                    </div>
                    <div class="list-group-item px-0 py-3">
                        <div class="fw-semibold"><i class="fa-solid fa-3 me-2 text-primary"></i>Intervención y repuestos</div>
                        <div class="small text-secondary">Se registran los trabajos realizados y los repuestos utilizados, con su costo.</div>
                    </div>
                    <div class="list-group-item px-0 py-3">
                        <div class="fw-semibold"><i class="fa-solid fa-4 me-2 text-primary"></i>Entrega y garantía</div>
                        <div class="small text-secondary">Se realizan las pruebas, se registra el pago y se entrega el equipo con su garantía.</div>
                    </div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="banner-servicio h-100 d-flex flex-column justify-content-center">
                    <h2 class="h4 fw-bold">Consulta antes de traer su equipo</h2>
                    <p class="text-info-emphasis small mb-3">
                        Puede comunicarse con la tienda para orientar la revisión de su computadora
                        o impresora. Conserve siempre su comprobante de compra y su documento de garantía.
                    </p>
                    <a class="btn btn-light fw-semibold align-self-start" href="<?= url('index', ['pagina' => 'contactos']) ?>">
                        <i class="fa-solid fa-location-dot me-2"></i> Datos de contacto
                    </a>
                </div>
            </div>
        </div>

        <div class="alert alert-primary mt-4 mb-0">
            <i class="fa-solid fa-shield-halved me-2"></i>
            <strong>Garantías.</strong> Los trabajos de servicio técnico cuentan con garantía registrada.
            Para verificarla se requiere el número de orden o los datos del cliente y del equipo.
        </div>
    </div>
</section>
