<?php /** Página pública: Nosotros. */ ?>
<section class="bg-white border-bottom">
    <div class="container py-5">
        <nav aria-label="Ruta de navegación">
            <ol class="breadcrumb small mb-2">
                <li class="breadcrumb-item"><a href="<?= url('index') ?>">Inicio</a></li>
                <li class="breadcrumb-item active" aria-current="page">Nosotros</li>
            </ol>
        </nav>
        <h1 class="seccion-titulo h2 mb-2">Sobre Alex@sys</h1>
        <p class="seccion-subtitulo mb-0">
            Una tienda comercial dedicada a la venta de equipos informáticos y a la prestación
            de servicios técnicos en la ciudad de Oruro.
        </p>
    </div>
</section>

<section class="seccion">
    <div class="container">
        <div class="row g-5 align-items-center">
            <div class="col-lg-6">
                <img class="img-fluid rounded-4 shadow-sm" src="<?= asset('imagen/nosotros/tienda.jpg') ?>" alt="Interior de la tienda Alex@sys">
            </div>
            <div class="col-lg-6">
                <h2 class="h4 fw-bold mb-3">Nuestra historia</h2>
                <p class="text-secondary">
                    Alex@sys nace para atender la creciente demanda de equipos informáticos y soporte
                    técnico confiable. La tienda comercializa computadoras de escritorio, laptops,
                    accesorios, componentes y repuestos, y además realiza mantenimiento, diagnóstico
                    y reparación de computadoras e impresoras.
                </p>
                <p class="text-secondary mb-0">
                    Con el crecimiento de las operaciones surgió la necesidad de organizar la información:
                    ventas, comprobantes, existencias, costos de adquisición, equipos recibidos y garantías.
                    Este sistema web centraliza esos procesos para brindar una atención más rápida y segura.
                </p>
            </div>
        </div>

        <div class="row g-4 mt-4">
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body p-4">
                        <img class="icono-nosotros mb-3" src="<?= asset('imagen/iconos/mision.svg') ?>" alt="Misión de Alex@sys">
                        <h3 class="h6 fw-bold">Misión</h3>
                        <p class="small text-secondary mb-0">
                            Proveer equipos informáticos y servicio técnico de calidad, con atención
                            personalizada y garantía, contribuyendo al buen desempeño de nuestros clientes.
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body p-4">
                        <img class="icono-nosotros mb-3" src="<?= asset('imagen/iconos/vision.png') ?>" alt="Visión de Alex@sys">
                        <h3 class="h6 fw-bold">Visión</h3>
                        <p class="small text-secondary mb-0">
                            Ser la tienda de referencia en Oruro por la confiabilidad de nuestros equipos,
                            la transparencia en las ventas y la calidad del servicio técnico.
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body p-4">
                        <img class="icono-nosotros mb-3" src="<?= asset('imagen/iconos/valores.svg') ?>" alt="Valores de Alex@sys">
                        <h3 class="h6 fw-bold">Valores</h3>
                        <p class="small text-secondary mb-0">
                            Responsabilidad, honestidad, orden en la información, respeto al cliente
                            y compromiso con la calidad del trabajo realizado.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="seccion bg-white border-top">
    <div class="container">
        <div class="row g-4 align-items-center">
            <div class="col-lg-4">
                <img class="img-fluid rounded-4 shadow-sm mb-4" src="<?= asset('imagen/nosotros/equipo.jpg') ?>" alt="Equipo de trabajo">
                <img class="img-fluid rounded-4 shadow-sm" src="<?= asset('imagen/nosotros/atencion.jpg') ?>" alt="Atención al cliente">
            </div>
            <div class="col-lg-8">
                <h2 class="h4 fw-bold mb-3">Cómo trabajamos</h2>
                <div class="row g-3">
                    <?php
                    $pasos = [
                        ['1', 'Recepción', 'Registramos al cliente y el equipo, junto con la falla reportada y los accesorios recibidos.'],
                        ['2', 'Diagnóstico', 'El técnico evalúa el equipo y documenta el diagnóstico en la orden de servicio.'],
                        ['3', 'Intervención', 'Se realiza el trabajo y se registran los repuestos utilizados, descontándolos del inventario.'],
                        ['4', 'Entrega y garantía', 'Se entrega el equipo, se registra el pago y se emite la garantía correspondiente.'],
                    ];
                    foreach ($pasos as [$numero, $tituloPaso, $texto]): ?>
                        <div class="col-md-6">
                            <div class="d-flex gap-3">
                                <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-primary text-white fw-bold flex-shrink-0"
                                     style="width:40px;height:40px"><?= e($numero) ?></div>
                                <div>
                                    <h3 class="h6 fw-bold mb-1"><?= e($tituloPaso) ?></h3>
                                    <p class="small text-secondary mb-0"><?= e($texto) ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="seccion">
    <div class="container">
        <div class="row g-4 align-items-center">
            <div class="col-lg-5">
                <h2 class="h4 fw-bold mb-3">Visítanos</h2>
                <p class="text-secondary mb-0">
                    Encuéntranos en Oruro. Será un gusto atenderte con tus consultas sobre
                    nuestros productos y servicios técnicos.
                </p>
            </div>
            <div class="col-lg-7">
                <img
                    class="img-fluid w-100 rounded-4 shadow-sm"
                    src="<?= asset('imagen/nosotros/ubicacion.PNG') ?>"
                    alt="Ubicación de la tienda Alex@sys"
                    loading="lazy"
                >
            </div>
        </div>
    </div>
</section>
