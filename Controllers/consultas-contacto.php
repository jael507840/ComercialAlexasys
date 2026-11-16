<?php
/**
 * Bandeja de consultas enviadas desde el formulario público de contactos.
 * Permite al Gerente leer los mensajes aunque el servidor no tenga correo.
 */

requiere_permiso('consultas-contacto');
csrf_verificar();

$consultaModelo = new ConsultaContacto();
$busqueda = texto_seguro(get('q', ''), 80);

renderizar('consultas-contacto', [
    'titulo'     => 'Consultas de contacto',
    'subtitulo'  => 'Mensajes recibidos desde el sitio público',
    'consultas'  => $consultaModelo->listar($busqueda),
    'busqueda'   => $busqueda,
    'total'      => $consultaModelo->contar(),
    'pendientes' => $consultaModelo->contarPendientes(),
]);
