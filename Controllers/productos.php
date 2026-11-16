<?php
/**
 * Módulo 5: productos.
 *
 * Importante: stock_actual NO se edita desde este formulario. Toda variación
 * de existencias se realiza en el módulo de inventario (ajustes) o mediante
 * compras, ventas y repuestos, generando siempre un movimiento asociado.
 */

requiere_permiso('productos');
csrf_verificar();

$productoModelo = new Producto();
$categoriaModelo = new Categoria();
$errores = [];
$accion = (string) get('accion', 'listar');
$id = (int) (get('id', 0) ?: post('id', 0));

$formulario = [
    'id_producto'  => 0,
    'id_categoria' => 0,
    'codigo'       => '',
    'nombre'       => '',
    'descripcion'  => '',
    'marca'        => '',
    'modelo'       => '',
    'imagen'       => null,
    'precio_venta' => '0.00',
    'stock_minimo' => '0',
    'estado'       => 'Activo',
];
$productoEdicion = null;

if (es_post()) {
    $operacion = (string) post('operacion');

    if (in_array($operacion, ['crear', 'actualizar'], true)) {
        requiere_escritura('productos');
        $formulario = [
            'id_producto'  => (int) post('id'),
            'id_categoria' => (int) post('id_categoria'),
            'codigo'       => texto_seguro(post('codigo'), 30),
            'nombre'       => texto_seguro(post('nombre'), 120),
            'descripcion'  => texto_seguro(post('descripcion'), 2000),
            'marca'        => texto_seguro(post('marca'), 60),
            'modelo'       => texto_seguro(post('modelo'), 60),
            'imagen'       => null,
            'precio_venta' => (string) post('precio_venta'),
            'stock_minimo' => (string) post('stock_minimo'),
            'estado'       => post('estado') === 'Inactivo' ? 'Inactivo' : 'Activo',
        ];

        if ($formulario['codigo'] === '' || !preg_match('/^[A-Za-z0-9._-]{2,30}$/', $formulario['codigo'])) {
            $errores[] = 'El código es obligatorio (2 a 30 caracteres alfanuméricos).';
        }
        if ($formulario['nombre'] === '') {
            $errores[] = 'El nombre del producto es obligatorio.';
        }
        if ($formulario['id_categoria'] <= 0 || !$categoriaModelo->existe($formulario['id_categoria'])) {
            $errores[] = 'Seleccione una categoría válida.';
        }
        if (!numero_no_negativo($formulario['precio_venta'])) {
            $errores[] = 'El precio de venta debe ser un número mayor o igual a cero.';
        }
        if (!numero_no_negativo($formulario['stock_minimo']) || (int) $formulario['stock_minimo'] < 0) {
            $errores[] = 'El stock mínimo debe ser un número entero mayor o igual a cero.';
        }
        $existente = $productoModelo->porCodigo($formulario['codigo']);
        if ($existente !== null && (int) $existente['id_producto'] !== $formulario['id_producto']) {
            $errores[] = 'El código del producto ya está registrado.';
        }

        if ($errores === []) {
            try {
                $datos = [
                    'id_categoria' => $formulario['id_categoria'],
                    'codigo'       => $formulario['codigo'],
                    'nombre'       => $formulario['nombre'],
                    'descripcion'  => $formulario['descripcion'] !== '' ? $formulario['descripcion'] : null,
                    'marca'        => $formulario['marca'] !== '' ? $formulario['marca'] : null,
                    'modelo'       => $formulario['modelo'] !== '' ? $formulario['modelo'] : null,
                    'precio_venta' => round((float) $formulario['precio_venta'], 2),
                    'stock_minimo' => (int) $formulario['stock_minimo'],
                    'estado'       => $formulario['estado'],
                ];

                $nuevaImagen = guardar_imagen_producto($_FILES['imagen'] ?? []);
                if ($nuevaImagen !== null) {
                    $datos['imagen'] = $nuevaImagen;
                }

                if ($operacion === 'crear') {
                    $datos['stock_actual'] = 0;
                    $productoModelo->crear($datos);
                    flash('success', 'Producto registrado. Registre una compra o un ajuste de inventario para ingresar existencias.');
                } else {
                    if ($formulario['id_producto'] <= 0) {
                        throw new DomainException('Producto no válido para actualizar.');
                    }
                    $productoModelo->actualizar($formulario['id_producto'], $datos);
                    flash('success', 'Producto actualizado correctamente.');
                }
                redirect('productos');
            } catch (DomainException $e) {
                $errores[] = $e->getMessage();
            }
        }
        $accion = $operacion === 'actualizar' ? 'editar' : 'listar';
    }

    if ($operacion === 'estado') {
        requiere_escritura('productos');
        try {
            $nuevoEstado = post('estado') === 'Inactivo' ? 'Inactivo' : 'Activo';
            $productoModelo->cambiarEstado((int) post('id'), $nuevoEstado);
            flash('success', 'Estado del producto actualizado.');
        } catch (DomainException $e) {
            flash('danger', $e->getMessage());
        }
        redirect('productos');
    }
}

if ($accion === 'editar' && $id > 0) {
    $productoEdicion = $productoModelo->porId($id);
    if (!$productoEdicion) {
        flash('danger', 'El producto solicitado no existe.');
        redirect('productos');
    }
    if ($errores === []) {
        $formulario = array_merge($formulario, $productoEdicion);
    }
}

$busqueda = texto_seguro(get('q', ''), 80);
$idCategoria = (int) get('categoria', 0);
$soloBajo = (bool) get('bajo', 0);

renderizar('productos', [
    'titulo'           => 'Productos',
    'subtitulo'        => 'Catálogo, precios, costos de adquisición y existencias',
    'productos'        => $productoModelo->listar($busqueda, $idCategoria, $soloBajo),
    'categorias'       => $categoriaModelo->listar(),
    'busqueda'         => $busqueda,
    'idCategoria'      => $idCategoria,
    'soloBajo'         => $soloBajo,
    'productoEdicion'  => $productoEdicion,
    'formulario'       => $formulario,
    'errores'          => $errores,
    'resumen'          => $productoModelo->resumenInventario(),
]);
