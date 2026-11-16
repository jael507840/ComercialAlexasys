<?php /** Página pública: Contactos. */ ?>
<section class="bg-white border-bottom">
    <div class="container py-5">
        <nav aria-label="Ruta de navegación">
            <ol class="breadcrumb small mb-2">
                <li class="breadcrumb-item"><a href="<?= url('index') ?>">Inicio</a></li>
                <li class="breadcrumb-item active" aria-current="page">Contactos</li>
            </ol>
        </nav>
        <h1 class="seccion-titulo h2 mb-2">Contactos</h1>
        <p class="seccion-subtitulo mb-0">
            Comuníquese con Alex@sys para consultar productos, servicios técnicos o el estado de su equipo.
        </p>
    </div>
</section>

<section class="seccion">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-6 col-lg-3">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="icono-servicio mb-3"><i class="fa-solid fa-location-dot"></i></div>
                        <h2 class="h6 fw-bold">Dirección</h2>
                        <p class="small text-secondary mb-0">
                            Av. Principal, zona central<br>Oruro - Bolivia
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="icono-servicio mb-3"><i class="fa-solid fa-phone"></i></div>
                        <h2 class="h6 fw-bold">Teléfono</h2>
                        <p class="small text-secondary mb-0">
                            +591 700 00000<br>
                            Atención en horario de tienda
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="icono-servicio mb-3"><i class="fa-solid fa-envelope"></i></div>
                        <h2 class="h6 fw-bold">Correo</h2>
                        <p class="small text-secondary mb-2">contacto@alexsys.bo</p>
                        <a class="small fw-semibold" href="mailto:contacto@alexsys.bo">
                            Escribir correo <i class="fa-solid fa-arrow-up-right-from-square ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="icono-servicio mb-3"><i class="fa-regular fa-clock"></i></div>
                        <h2 class="h6 fw-bold">Horario</h2>
                        <p class="small text-secondary mb-0">
                            Lunes a Sábado<br>
                            08:30 - 19:00
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mt-4">
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <h2 class="h5 fw-bold">Envíanos tu consulta</h2>
                        <p class="small text-secondary">
                            Completa todos los campos para enviar tu consulta. Si el correo de la tienda no está disponible,
                            conservaremos el mensaje registrado.
                        </p>
                        <?php if (!empty($erroresContacto)): ?>
                            <div class="alert alert-danger" role="alert">
                                <p class="fw-semibold mb-1">Revisa los siguientes campos:</p>
                                <ul class="mb-0">
                                    <?php foreach ($erroresContacto as $error): ?>
                                        <li><?= e($error) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                        <?php parcial('mensajes'); ?>
                        <form method="post" action="<?= e(url('index', ['pagina' => 'contactos'])) ?>">
                            <?= csrf_field() ?>
                            <div class="mb-3">
                                <label class="form-label fw-semibold" for="contacto-nombre">Nombre</label>
                                <input
                                    class="form-control"
                                    id="contacto-nombre"
                                    name="nombre"
                                    type="text"
                                    value="<?= e($formularioContacto['nombre'] ?? '') ?>"
                                    maxlength="100"
                                    pattern="[A-Za-zÁÉÍÓÚÜÑáéíóúüñ]+( +[A-Za-zÁÉÍÓÚÜÑáéíóúüñ]+)*"
                                    title="Usa solo letras y espacios."
                                    autocomplete="name"
                                    required
                                >
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold" for="contacto-correo">Correo electrónico</label>
                                <input
                                    class="form-control"
                                    id="contacto-correo"
                                    name="correo"
                                    type="email"
                                    value="<?= e($formularioContacto['correo'] ?? '') ?>"
                                    maxlength="254"
                                    autocomplete="email"
                                    required
                                >
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold" for="contacto-mensaje">Consulta</label>
                                <textarea
                                    class="form-control"
                                    id="contacto-mensaje"
                                    name="mensaje"
                                    rows="5"
                                    minlength="10"
                                    maxlength="3000"
                                    required
                                ><?= e($formularioContacto['mensaje'] ?? '') ?></textarea>
                                <div class="form-text">De 10 a 3000 caracteres.</div>
                            </div>
                            <button class="btn btn-primary" type="submit">
                                <i class="fa-solid fa-paper-plane me-2"></i>Enviar consulta
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm h-100 bg-primary text-white">
                    <div class="card-body p-4 d-flex flex-column">
                        <h2 class="h5 fw-bold">Acceso al sistema</h2>
                        <p class="small text-white-50 mb-4">
                            El personal de la tienda accede al panel administrativo para registrar ventas,
                            compras, inventario, órdenes de servicio y garantías.
                        </p>
                        <a class="btn btn-light fw-semibold mt-auto align-self-start" href="<?= url('login') ?>">
                            <i class="fa-solid fa-right-to-bracket me-2"></i> Iniciar sesión
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="alert alert-light border mt-4 mb-0 small text-secondary">
            <i class="fa-solid fa-circle-info me-1"></i>
            Los datos de contacto mostrados son referenciales para la demostración del sistema y deben
            actualizarse con la información real de la tienda antes de su publicación.
        </div>
    </div>
</section>
