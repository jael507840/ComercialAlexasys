<?php
/**
 * Configuración general del sistema Alex@sys.
 *
 * En un servidor de producción este archivo debe ubicarse fuera del
 * directorio público (DocumentRoot) y ajustar las credenciales.
 */

return [
    'app' => [
        'nombre'      => 'Alex@sys',
        'descripcion' => 'Sistema de Información Web para la Gestión de Clientes, Ventas, Inventario y Servicio Técnico',
        'version'     => '1.0.0',
        'entorno'     => 'desarrollo', // desarrollo | produccion
        'zona_horaria' => 'America/La_Paz',
        'moneda'      => 'Bs',
        'items_por_pagina' => 15,
    ],

    'db' => [
        'host'     => '127.0.0.1',
        'puerto'   => 3306,
        'nombre'   => 'dbAlexasys',
        'usuario'  => 'root',
        'password' => '',
        'charset'  => 'utf8mb4',
    ],

    'contacto' => [
        'correo_destino' => 'contacto@example.com',
    ],

    'seguridad' => [
        'max_intentos_login'   => 5,
        'minutos_bloqueo'      => 15,
        'minutos_inactividad'  => 60,
        'dias_garantia_producto' => 365,
        'dias_garantia_servicio' => 60,
    ],

    // Datos de demostración: deben cambiarse antes de usar en producción.
    'demo' => [
        'activo' => true,
        'aviso'  => 'Los usuarios y datos incluidos son de demostración. Cambie las contraseñas antes de operar el sistema.',
    ],
];
