<?php
/** Módulo: consultas recibidas desde el formulario público de contactos. */
parcial('head-admin', ['titulo' => $titulo, 'subtitulo' => $subtitulo]);
?>

<div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-4">
    <div>
        <h1 class="titulo-pagina h3 mb-1">Consultas de contacto</h1>
        <p class="text-secondary mb-0 small">
            Mensajes enviados desde la página pública. Responde al correo del visitante o llámalo por teléfono.
        </p>
    </div>
    <a class="btn btn-outline-secondary btn-sm" href="<?= url('index', ['pagina' => 'contactos']) ?>" target="_blank" rel="noopener">
        <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Ver formulario público
    </a>
</div>

<div class="row g-3 mb-4">
    <div class="col-sm-6 col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="small text-secondary">Total de consultas</div>
                <div class="fs-3 fw-bold text-primary"><?= (int) $total ?></div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="small text-secondary">Sin aviso por correo</div>
                <div class="fs-3 fw-bold text-warning-emphasis"><?= (int) $pendientes ?></div>
                <div class="small text-secondary">El mensaje igual quedó guardado.</div>
            </div>
        </div>
    </div>
    <div class="col-sm-12 col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="small text-secondary">Buzón configurado</div>
                <div class="fw-semibold text-break"><?= e((string) config('contacto.correo_destino', '')) ?></div>
                <div class="small text-secondary">Se cambia en <code>config/configuracion.php</code>.</div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center gap-2">
        <span class="fw-semibold"><i class="fa-solid fa-envelope-open-text me-2 text-primary"></i>Mensajes recibidos</span>
        <form class="d-flex gap-2" method="get" action="<?= asset('index.php') ?>">
            <input type="hidden" name="ruta" value="consultas-contacto">
            <input class="form-control form-control-sm" type="search" name="q" value="<?= e($busqueda) ?>"
                   placeholder="Nombre, correo o mensaje" aria-label="Buscar consultas">
            <button class="btn btn-sm btn-primary" type="submit" title="Buscar">
                <i class="fa-solid fa-magnifying-glass"></i>
            </button>
            <?php if ($busqueda !== ''): ?>
                <a class="btn btn-sm btn-outline-secondary" href="<?= url('consultas-contacto') ?>" title="Limpiar búsqueda">
                    <i class="fa-solid fa-rotate-left"></i>
                </a>
            <?php endif; ?>
        </form>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th scope="col">Fecha</th>
                    <th scope="col">Visitante</th>
                    <th scope="col">Consulta</th>
                    <th scope="col">Aviso por correo</th>
                    <th scope="col" class="text-end">Responder</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($consultas === []): ?>
                    <tr>
                        <td colspan="5" class="text-center text-secondary py-4">
                            <i class="fa-regular fa-envelope me-2"></i>
                            <?= $busqueda !== '' ? 'No hay consultas que coincidan con la búsqueda.' : 'Todavía no se recibieron consultas.' ?>
                        </td>
                    </tr>
                <?php endif; ?>
                <?php foreach ($consultas as $consulta): ?>
                    <tr>
                        <td class="text-nowrap small text-secondary">
                            <?= e(date('d/m/Y H:i', strtotime((string) $consulta['fecha_registro']))) ?>
                        </td>
                        <td>
                            <div class="fw-semibold"><?= e($consulta['nombre']) ?></div>
                            <a class="small text-break" href="mailto:<?= e($consulta['correo']) ?>"><?= e($consulta['correo']) ?></a>
                        </td>
                        <td class="small" style="min-width:260px;max-width:520px">
                            <div class="text-break"><?= nl2br(e($consulta['mensaje'])) ?></div>
                        </td>
                        <td>
                            <?php if ((int) $consulta['notificacion_enviada'] === 1): ?>
                                <span class="badge text-bg-success">Enviado</span>
                            <?php else: ?>
                                <span class="badge text-bg-warning text-dark">Pendiente</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end">
                            <a class="btn btn-sm btn-outline-primary"
                               href="mailto:<?= e($consulta['correo']) ?>?subject=<?= rawurlencode('Respuesta de Alex@sys') ?>"
                               title="Responder por correo">
                                <i class="fa-solid fa-reply me-1"></i> Responder
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white small text-secondary">
        <i class="fa-solid fa-circle-info me-1"></i>
        Los mensajes se guardan en la tabla <code>consultas_contacto</code>. Si el servidor no puede enviar
        correo, la consulta permanece disponible en esta bandeja.
    </div>
</div>
