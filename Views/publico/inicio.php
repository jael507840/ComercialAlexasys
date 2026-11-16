<?php /** Página pública: Inicio. */ ?>
<section class="hero">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-7">
                <span class="badge rounded-pill text-bg-info text-dark mb-3 px-3 py-2">
                    <i class="fa-solid fa-bolt me-1"></i> Tecnología y soporte en un solo lugar
                </span>
                <h1 class="mb-3">Equipos informáticos y servicio técnico de confianza</h1>
                <p class="lead mb-4">
                    En Alex@sys vendemos computadoras, accesorios y repuestos, y realizamos
                    mantenimiento, diagnóstico y reparación con garantía.
                </p>
                <div class="d-flex flex-wrap gap-2">
                    <a class="btn btn-light btn-lg fw-semibold" href="<?= url('index', ['pagina' => 'productos']) ?>">
                        <i class="fa-solid fa-box-open me-2"></i> Ver productos
                    </a>
                    <a class="btn btn-outline-light btn-lg" href="<?= url('index', ['pagina' => 'servicios']) ?>">
                        <i class="fa-solid fa-screwdriver-wrench me-2"></i> Servicios técnicos
                    </a>
                </div>
                <div class="row g-3 mt-4 text-white">
                    <div class="col-6 col-md-3">
                        <div class="fs-4 fw-bold">Ventas</div>
                        <div class="small text-info-emphasis">Comprobante interno</div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="fs-4 fw-bold">Stock</div>
                        <div class="small text-info-emphasis">Control en tiempo real</div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="fs-4 fw-bold">Servicio</div>
                        <div class="small text-info-emphasis">Diagnóstico y reparación</div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="fs-4 fw-bold">Garantía</div>
                        <div class="small text-info-emphasis">Verificación por equipo</div>
                    </div>
                </div>
            </div>
            <div class="col-lg-5 d-none d-lg-block">
                <div class="bg-white bg-opacity-10 border border-light border-opacity-25 rounded-4 p-3">
                    <img class="img-fluid rounded-3" src="<?= asset('imagen/nosotros/tienda.jpg') ?>" alt="Tienda Alex@sys">
                </div>
            </div>
        </div>
    </div>
</section>

<section class="seccion">
    <div class="container">
        <div class="text-center mb-4">
            <h2 class="seccion-titulo h3">¿Qué necesita hoy?</h2>
            <p class="seccion-subtitulo mx-auto">
                Cubrimos las necesidades de estudiantes, oficinas y hogares con equipos y soporte técnico.
            </p>
        </div>
        <div class="row g-4">
            <?php
            $accesos = [
                ['productos.svg', 'Equipos y accesorios', 'Computadoras de escritorio, laptops, monitores, teclados y más.', 'productos'],
                ['servicio.svg', 'Servicio técnico', 'Mantenimiento preventivo, diagnóstico y reparación de equipos.', 'servicios'],
                ['garantias.svg', 'Garantías', 'Consulte la vigencia de la garantía de su equipo o reparación.', 'servicios'],
                ['clientes.svg', 'Atención al cliente', 'Estamos para orientarle en la compra y el cuidado de su equipo.', 'contactos'],
            ];
            foreach ($accesos as [$icono, $tituloTarjeta, $texto, $destino]): ?>
                <div class="col-md-6 col-lg-3">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body p-4">
                            <img class="icono-tarjeta mb-3" src="<?= asset('imagen/iconos/' . $icono) ?>" alt="">
                            <h3 class="h6 fw-bold"><?= e($tituloTarjeta) ?></h3>
                            <p class="small text-secondary mb-3"><?= e($texto) ?></p>
                            <a class="small fw-semibold" href="<?= url('index', ['pagina' => $destino]) ?>">
                                Más información <i class="fa-solid fa-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="seccion pt-0">
    <div class="container">
        <div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-3">
            <div>
                <h2 class="seccion-titulo h3 mb-1">Productos destacados</h2>
                <p class="seccion-subtitulo mb-0">Disponibilidad sujeta al stock registrado en tienda.</p>
            </div>
            <a class="btn btn-outline-primary" href="<?= url('index', ['pagina' => 'productos']) ?>">Ver catálogo completo</a>
        </div>
        <div class="row g-4">
            <?php if ($productos === []): ?>
                <div class="col-12"><div class="alert alert-info mb-0">Aún no hay productos disponibles para mostrar.</div></div>
            <?php endif; ?>
            <?php foreach ($productos as $producto): ?>
                <div class="col-6 col-lg-4">
                    <div class="card tarjeta-producto h-100 shadow-sm border-0">
                        <img src="<?= asset($producto['imagen'] ?: 'imagen/productos/componentes.jpg') ?>"
                             alt="<?= e($producto['nombre']) ?>">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start gap-2">
                                <div>
                                    <div class="small text-secondary"><?= e($producto['categoria'] ?? '') ?></div>
                                    <h3 class="h6 fw-bold mb-1"><?= e($producto['nombre']) ?></h3>
                                    <div class="small text-secondary"><?= e(trim(($producto['marca'] ?? '') . ' ' . ($producto['modelo'] ?? ''))) ?></div>
                                </div>
                                <span class="badge text-bg-light border"><?= e($producto['codigo']) ?></span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <span class="fs-5 fw-bold text-primary"><?= e(formato_moneda($producto['precio_venta'])) ?></span>
                                <?php if ((int) $producto['stock_actual'] > 0): ?>
                                    <span class="badge text-bg-success">Disponible</span>
                                <?php else: ?>
                                    <span class="badge text-bg-secondary">Consultar</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="seccion pt-0">
    <div class="container">
        <div class="banner-servicio">
            <div class="row align-items-center g-4">
                <div class="col-lg-8">
                    <h2 class="h3 fw-bold mb-2">Su equipo en manos de técnicos responsables</h2>
                    <p class="mb-0 text-info-emphasis">
                        Registramos cada equipo recibido, la falla reportada, el diagnóstico, los repuestos
                        utilizados y la garantía del trabajo realizado.
                    </p>
                </div>
                <div class="col-lg-4 text-lg-end">
                    <a class="btn btn-light fw-semibold" href="<?= url('index', ['pagina' => 'contactos']) ?>">
                        <i class="fa-solid fa-phone me-2"></i> Contactar a la tienda
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
