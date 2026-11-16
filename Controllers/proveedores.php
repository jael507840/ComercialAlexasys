<?php
/**
 * Módulo 6: proveedores.
 * Registro, búsqueda, actualización, activación/desactivación e historial
 * de compras realizadas a cada proveedor.
 */

requiere_permiso('proveedores');
csrf_verificar();

$proveedorModelo = new Proveedor();
$errores = [];
$accion = (string) get('accion', 'listar');
$id = (int) (get('id', 0) ?: post('id', 0));

$formulario = [
    'id_proveedor' => 0,
    'nombre'       => '',
    'nit'          => '',
    'telefono'     => '',
    'direccion'    => '',
    'correo'       => '',
    'estado'       => 'Activo',
];
$proveedorEdicion = null;

if (es_post()) {
    $operacion = (string) post('operacion');

    if (in_array($operacion, ['crear', 'actualizar'], true)) {
        requiere_escritura('proveedores');

        $formulario = [
            'id_proveedor' => (int) post('id'),
            'nombre'       => texto_seguro(post('nombre'), 120),
            'nit'          => texto_seguro(post('nit'), 20),
            'telefono'     => texto_seguro(post('telefono'), 20),
            'direccion'    => texto_seguro(post('direccion'), 200),
            'correo'       => texto_seguro(post('correo'), 120),
            'estado'       => post('estado') === 'Inactivo' ? 'Inactivo' : 'Activo',
        ];

        if ($formulario['nombre'] === '') {
            $errores[] = 'La razón social o nombre del proveedor es obligatorio.';
        }
        if (!correo_valido($formulario['correo'])) {
            $errores[] = 'El correo electrónico del proveedor no tiene un formato válido.';
        }
        $existente = $proveedorModelo->porNit($formulario['nit']);
        if ($existente !== null && (int) $existente['id_proveedor'] !== $formulario['id_proveedor']) {
            $errores[] = 'El NIT indicado ya está registrado para otro proveedor.';
        }

        if ($errores === []) {
            $datos = [
                'nombre'    => $formulario['nombre'],
                'nit'       => $formulario['nit'] !== '' ? $formulario['nit'] : null,
                'telefono'  => $formulario['telefono'] !== '' ? $formulario['telefono'] : null,
                'direccion' => $formulario['direccion'] !== '' ? $formulario['direccion'] : null,
                'correo'    => $formulario['correo'] !== '' ? $formulario['correo'] : null,
                'estado'    => $formulario['estado'],
            ];
            try {
                if ($operacion === 'crear') {
                    $proveedorModelo->crear($datos);
                    flash('success', 'Proveedor registrado correctamente.');
                } else {
                    if ($formulario['id_proveedor'] <= 0) {
                        throw new DomainException('Proveedor no válido para actualizar.');
                    }
                    $proveedorModelo->actualizar($formulario['id_proveedor'], $datos);
                    flash('success', 'Datos del proveedor actualizados correctamente.');
                }
                redirect('proveedores');
            } catch (DomainException $e) {
                $errores[] = $e->getMessage();
            }
        }
        $accion = $operacion === 'actualizar' ? 'editar' : 'listar';
    }

    if ($operacion === 'estado') {
        requiere_escritura('proveedores');
        $nuevoEstado = post('estado') === 'Inactivo' ? 'Inactivo' : 'Activo';
        try {
            $proveedorModelo->cambiarEstado((int) post('id'), $nuevoEstado);
            flash('success', 'El estado del proveedor se actualizó a ' . $nuevoEstado . '.');
        } catch (DomainException $e) {
            flash('danger', $e->getMessage());
        }
        redirect('proveedores');
    }
}

if ($accion === 'editar' && $id > 0) {
    $proveedorEdicion = $proveedorModelo->porId($id);
    if (!$proveedorEdicion) {
        flash('danger', 'El proveedor solicitado no existe.');
        redirect('proveedores');
    }
    if ($errores === []) {
        $formulario = array_merge($formulario, $proveedorEdicion);
    }
}

if ($accion === 'ver' && $id > 0) {
    $proveedor = $proveedorModelo->porId($id);
    if (!$proveedor) {
        flash('danger', 'El proveedor solicitado no existe.');
        redirect('proveedores');
    }
    renderizar('proveedor-detalle', [
        'titulo'     => 'Proveedor: ' . $proveedor['nombre'],
        'subtitulo'  => 'Historial de compras y datos de contacto',
        'proveedor'  => $proveedor,
        'compras'    => $proveedorModelo->compras($id),
    ]);
    return;
}

$busqueda = texto_seguro(get('q', ''), 80);
renderizar('proveedores', [
    'titulo'            => 'Proveedores',
    'subtitulo'         => 'Empresas y personas que abastecen el inventario',
    'proveedores'       => $proveedorModelo->listar($busqueda),
    'busqueda'          => $busqueda,
    'proveedorEdicion'  => $proveedorEdicion,
    'formulario'        => $formulario,
    'errores'           => $errores,
    'totalProveedores'  => $proveedorModelo->contar(),
    'totalActivos'      => $proveedorModelo->contar("estado = 'Activo'"),
]);
