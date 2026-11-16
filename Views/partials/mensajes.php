<?php
/** Muestra los mensajes flash de la sesión. */
$flashes = obtener_flashes();
if ($flashes === []) {
    return;
}
$iconos = [
    'success' => 'fa-circle-check',
    'danger'  => 'fa-circle-exclamation',
    'warning' => 'fa-triangle-exclamation',
    'info'    => 'fa-circle-info',
];
?>
<div class="mensajes">
    <?php foreach ($flashes as $flash): ?>
        <?php $tipo = in_array($flash['tipo'], ['success', 'danger', 'warning', 'info'], true) ? $flash['tipo'] : 'info'; ?>
        <div class="alert alert-<?= e($tipo) ?> alert-dismissible alerta-auto d-flex align-items-start gap-2" role="alert">
            <i class="fa-solid <?= e($iconos[$tipo]) ?> mt-1"></i>
            <div class="flex-grow-1"><?= e($flash['mensaje']) ?></div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    <?php endforeach; ?>
</div>
