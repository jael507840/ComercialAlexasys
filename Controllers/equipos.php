<?php
/**
 * Módulo 11: equipos del cliente.
 * Registro y mantenimiento de los equipos que los clientes traen
 * al servicio técnico, con su historial de órdenes.
 */

requiere_permiso('equipos');
csrf_verificar();

$equipoModelo = new EquipoCliente();
$clienteModelo = new Cliente();
$productoModelo = new Producto();

$errores = [];
$accion = (string) get('accion', 'listar');
$id = (int) (get('id', 0) ?: post('id', 0));

$formulario = [
    'id_equipo'    => 0,
    'id_cliente'   => (int) get('cliente', 0),
    'id_producto'  => 0,
    'tipo_equipo'  => 'Laptop',
    'marca'        => '',
    'modelo'       => '',
    'numero_serie' => '',
    'descripcion'  => '',
];
$equipoEdicion = null;

if (es_post()) {
    $operacion = (string) post('operacion');

    if (in_array($operacion, ['crear', 'actualizar'], true)) {
        requiere_escritura('equipos');

        $formulario = [
            'id_equipo'    => (int) post('id'),
            'id_cliente'   => (int) post('id_cliente'),
            'id_producto'  => (int) post('id_producto'),
            'tipo_equipo'  => (string) post('tipo_equipo'),
            'marca'        => texto_seguro(post('marca'), 60),
            'modelo'       => texto_seguro(post('modelo'), 60),
            'numero_serie' => texto_seguro(post('numero_serie'), 60),
            'descripcion'  => texto_seguro(post('descripcion'), 255),
        ];

        $cliente = $formulario['id_cliente'] > 0 ? $clienteModelo->porId($formulario['id_cliente']) : null;
        if (!$cliente) {
            $errores[] = 'Seleccione un cliente válido.';
        } elseif ($cliente['estado'] !== 'Activo') {
            $errores[] = 'El cliente está inactivo; no se pueden registrar equipos a su nombre.';
        }
        if (!in_array($formulario['tipo_equipo'], EquipoCliente::TIPOS, true)) {
            $errores[] = 'Seleccione un tipo de equipo válido.';
        }
        if ($formulario['id_producto'] > 0 && !$productoModelo->porId($formulario['id_producto'])) {
            $errores[] = 'El producto asociado no existe.';
        }
        if ($formulario['marca'] === '' && $formulario['modelo'] === '') {
            $errores[] = 'Indique al menos la marca o el modelo del equipo.';
        }
        if ($formulario['numero_serie'] !== '') {
            $existente = $equipoModelo->porSerie($formulario['numero_serie']);
            if ($existente !== null && (int) $existente['id_equipo'] !== $formulario['id_equipo']) {
                $errores[] = 'El número de serie ya está registrado para otro equipo.';
            }
        }

        if ($errores === []) {
            $datos = [
                'id_cliente'   => $formulario['id_cliente'],
                'id_producto'  => $formulario['id_producto'] > 0 ? $formulario['id_producto'] : null,
                'tipo_equipo'  => $formulario['tipo_equipo'],
                'marca'        => $formulario['marca'] !== '' ? $formulario['marca'] : null,
                'modelo'       => $formulario['modelo'] !== '' ? $formulario['modelo'] : null,
                'numero_serie' => $formulario['numero_serie'] !== '' ? $formulario['numero_serie'] : null,
                'descripcion'  => $formulario['descripcion'] !== '' ? $formulario['descripcion'] : null,
            ];
            try {
                if ($operacion === 'crear') {
                    $equipoModelo->crear($datos);
                    flash('success', 'Equipo registrado correctamente.');
                } else {
                    if ($formulario['id_equipo'] <= 0) {
                        throw new DomainException('Equipo no válido para actualizar.');
                    }
                    $equipoModelo->actualizar($formulario['id_equipo'], $datos);
                    flash('success', 'Datos del equipo actualizados correctamente.');
                }
                redirect('equipos');
            } catch (DomainException $e) {
                $errores[] = $e->getMessage();
            }
        }
        $accion = $operacion === 'actualizar' ? 'editar' : 'listar';
    }
}

if ($accion === 'editar' && $id > 0) {
    $equipoEdicion = $equipoModelo->porId($id);
    if (!$equipoEdicion) {
        flash('danger', 'El equipo solicitado no existe.');
        redirect('equipos');
    }
    if ($errores === []) {
        $formulario = array_merge($formulario, $equipoEdicion);
    }
}

if ($accion === 'ver' && $id > 0) {
    $equipo = $equipoModelo->porIdConRelaciones($id);
    if (!$equipo) {
        flash('danger', 'El equipo solicitado no existe.');
        redirect('equipos');
    }
    renderizar('equipo-detalle', [
        'titulo'    => 'Equipo: ' . trim(($equipo['marca'] ?? '') . ' ' . ($equipo['modelo'] ?? '')),
        'subtitulo' => 'Datos del equipo e historial de órdenes de servicio',
        'equipo'    => $equipo,
        'ordenes'   => $equipoModelo->ordenes($id),
    ]);
    return;
}

$busqueda = texto_seguro(get('q', ''), 80);
$idCliente = (int) get('cliente', 0);

renderizar('equipos', [
    'titulo'     => 'Equipos de clientes',
    'subtitulo'  => 'Equipos registrados para el servicio técnico',
    'equipos'    => $equipoModelo->listar($busqueda, $idCliente),
    'clientes'   => $clienteModelo->listar(),
    'productos'  => $productoModelo->activos(),
    'busqueda'   => $busqueda,
    'idCliente'  => $idCliente,
    'equipoEdicion' => $equipoEdicion,
    'formulario' => $formulario,
    'errores'    => $errores,
]);
