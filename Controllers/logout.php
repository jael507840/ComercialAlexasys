<?php
/**
 * Cierre de sesión. Acepta POST con token CSRF y, como respaldo,
 * solicitudes GET directas desde el enlace del menú.
 */

if (es_post()) {
    csrf_verificar();
}

cerrar_sesion_usuario();
session_regenerate_id(true);
flash('info', 'Su sesión se cerró correctamente.');
redirect('login');
