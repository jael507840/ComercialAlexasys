<?php
/**
 * Módulo 3: clientes.
 * Registro, listado, búsqueda por CI/nombre/teléfono, actualización,
 * desactivación lógica e historial de ventas, equipos y servicios.
 */

requiere_permiso('clientes');
csrf_verificar();

$clienteModelo = new Cliente();
$errores = [];
$accion = (string) get('accion', 'listar');
$id = (int) (get('id', 0) ?: post('id', 0));

$formulario = [
    'id_cliente' => 0,
    'ci'         => '',
    'nombres'    => '',
    'apellidos'  => '',
    'telefono'   => '',
    'direccion'  => '',
    'correo'     => '',
    'estado'     => 'Activo',
];

// ---------------------------------------------------------------------------
// Operaciones POST
// ---------------------------------------------------------------------------
if (es_post()) {
    $operacion = (string) post('operacion');

    if (in_array($operacion, ['crear', 'actualizar'], true)) {
        requiere_escritura('clientes');

        $formulario = [
            'id_cliente' => (int) post('id'),
            'ci'         => texto_seguro(post('ci'), 20),
            'nombres'    => texto_seguro(post('nombres'), 100),
            'apellidos'  => texto_seguro(post('apellidos'), 100),
            'telefono'   => texto_seguro(post('telefono'), 20),
            'direccion'  => texto_seguro(post('direccion'), 200),
            'correo'     => texto_seguro(post('correo'), 120),
            'estado'     => post('estado') === 'Inactivo' ? 'Inactivo' : 'Activo',
        ];

        if ($formulario['ci'] === '' || !preg_match('/^[A-Za-z0-9-]{3,20}$/', $formulario['ci'])) {
            $errores[] = 'La cédula de identidad es obligatoria (3 a 20 caracteres alfanuméricos).';
        }
        if ($formulario['nombres'] === '') {
            $errores[] = 'Los nombres son obligatorios.';
        }
        if ($formulario['apellidos'] === '') {
            $errores[] = 'Los apellidos son obligatorios.';
        }
        if (!correo_valido($formulario['correo'])) {
            $errores[] = 'El correo electrónico no tiene un formato válido.';
        }
        $existente = $clienteModelo->porCi($formulario['ci']);
        if ($existente !== null && (int) $existente['id_cliente'] !== $formulario['id_cliente']) {
            $errores[] = 'La cédula de identidad ya está registrada para otro cliente.';
        }

        if ($errores === []) {
            $datos = [
                'ci'        => $formulario['ci'],
                'nombres'   => $formulario['nombres'],
                'apellidos' => $formulario['apellidos'],
                'telefono'  => $formulario['telefono'] !== '' ? $formulario['telefono'] : null,
                'direccion' => $formulario['direccion'] !== '' ? $formulario['direccion'] : null,
                'correo'    => $formulario['correo'] !== '' ? $formulario['correo'] : null,
                'estado'    => $formulario['estado'],
            ];
            try {
                if ($operacion === 'crear') {
                    $clienteModelo->crear($datos);
                    flash('success', 'Cliente registrado correctamente.');
                } else {
                    if ($formulario['id_cliente'] <= 0) {
                        throw new DomainException('Cliente no válido para actualizar.');
                    }
                    $clienteModelo->actualizar($formulario['id_cliente'], $datos);
                    flash('success', 'Datos del cliente actualizados correctamente.');
                }
                redirect('clientes');
            } catch (DomainException $e) {
                $errores[] = $e->getMessage();
            }
        }
        $accion = $operacion === 'actualizar' ? 'editar' : 'listar';
    }

    if ($operacion === 'estado') {
        requiere_escritura('clientes');
        $nuevoEstado = post('estado') === 'Inactivo' ? 'Inactivo' : 'Activo';
        try {
            $clienteModelo->cambiarEstado((int) post('id'), $nuevoEstado);
            flash('success', 'El estado del cliente se actualizó a ' . $nuevoEstado . '.');
        } catch (DomainException $e) {
            flash('danger', $e->getMessage());
        }
        redirect('clientes');
    }
}

// ---------------------------------------------------------------------------
// Consultas GET
// ---------------------------------------------------------------------------
$busqueda = texto_seguro(get('q', ''), 80);
$clienteEdicion = null;

if ($accion === 'editar' && $id > 0) {
    $clienteEdicion = $clienteModelo->porId($id);
    if (!$clienteEdicion) {
        flash('danger', 'El cliente solicitado no existe.');
        redirect('clientes');
    }
    if ($errores === []) {
        $formulario = array_merge($formulario, $clienteEdicion);
    }
}

if ($accion === 'ver' && $id > 0) {
    $cliente = $clienteModelo->porId($id);
    if (!$cliente) {
        flash('danger', 'El cliente solicitado no existe.');
        redirect('clientes');
    }
    renderizar('cliente-detalle', [
        'titulo'    => 'Cliente: ' . nombre_completo($cliente),
        'subtitulo' => 'Historial de ventas, equipos y servicios técnicos',
        'cliente'   => $cliente,
        'ventas'    => $clienteModelo->historialVentas($id),
        'equipos'   => $clienteModelo->historialEquipos($id),
        'servicios' => $clienteModelo->historialServicios($id),
    ]);
    return;
}

renderizar('clientes', [
    'titulo'         => 'Clientes',
    'subtitulo'      => 'Registro y consulta de clientes de la tienda',
    'clientes'       => $clienteModelo->listar($busqueda),
    'busqueda'       => $busqueda,
    'clienteEdicion' => $clienteEdicion,
    'formulario'     => $formulario,
    'errores'        => $errores,
    'totalClientes'  => $clienteModelo->contar(),
    'totalActivos'   => $clienteModelo->contarActivos(),
]);
