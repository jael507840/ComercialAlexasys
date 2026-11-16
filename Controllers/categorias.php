<?php
/**
 * Módulo 4: categorías del catálogo.
 */

requiere_permiso('categorias');
csrf_verificar();

$categoriaModelo = new Categoria();
$errores = [];
$accion = (string) get('accion', 'listar');
$id = (int) (get('id', 0) ?: post('id', 0));
$formulario = ['id_categoria' => 0, 'nombre' => '', 'descripcion' => '', 'estado' => 'Activo'];
$categoriaEdicion = null;

if (es_post()) {
    $operacion = (string) post('operacion');

    if (in_array($operacion, ['crear', 'actualizar'], true)) {
        requiere_escritura('categorias');
        $formulario = [
            'id_categoria' => (int) post('id'),
            'nombre'       => texto_seguro(post('nombre'), 80),
            'descripcion'  => texto_seguro(post('descripcion'), 200),
            'estado'       => post('estado') === 'Inactivo' ? 'Inactivo' : 'Activo',
        ];
        if ($formulario['nombre'] === '') {
            $errores[] = 'El nombre de la categoría es obligatorio.';
        }
        if ($errores === []) {
            $datos = [
                'nombre'      => $formulario['nombre'],
                'descripcion' => $formulario['descripcion'] !== '' ? $formulario['descripcion'] : null,
                'estado'      => $formulario['estado'],
            ];
            try {
                if ($operacion === 'crear') {
                    $categoriaModelo->crear($datos);
                    flash('success', 'Categoría registrada correctamente.');
                } else {
                    if ($formulario['id_categoria'] <= 0) {
                        throw new DomainException('Categoría no válida para actualizar.');
                    }
                    $categoriaModelo->actualizar($formulario['id_categoria'], $datos);
                    flash('success', 'Categoría actualizada correctamente.');
                }
                redirect('categorias');
            } catch (DomainException $e) {
                $errores[] = $e->getMessage();
            }
        }
        $accion = $operacion === 'actualizar' ? 'editar' : 'listar';
    }

    if ($operacion === 'estado') {
        requiere_escritura('categorias');
        $categoriaId = (int) post('id');
        $nuevoEstado = post('estado') === 'Inactivo' ? 'Inactivo' : 'Activo';
        try {
            if ($nuevoEstado === 'Inactivo' && $categoriaModelo->totalProductos($categoriaId) > 0) {
                throw new DomainException('No se puede desactivar una categoría que tiene productos asociados.');
            }
            $categoriaModelo->cambiarEstado($categoriaId, $nuevoEstado);
            flash('success', 'Estado de la categoría actualizado.');
        } catch (DomainException $e) {
            flash('danger', $e->getMessage());
        }
        redirect('categorias');
    }
}

if ($accion === 'editar' && $id > 0) {
    $categoriaEdicion = $categoriaModelo->porId($id);
    if (!$categoriaEdicion) {
        flash('danger', 'La categoría solicitada no existe.');
        redirect('categorias');
    }
    if ($errores === []) {
        $formulario = array_merge($formulario, $categoriaEdicion);
    }
}

$busqueda = texto_seguro(get('q', ''), 80);
renderizar('categorias', [
    'titulo'            => 'Categorías',
    'subtitulo'         => 'Organización del catálogo de productos',
    'categorias'        => $categoriaModelo->listar($busqueda),
    'busqueda'          => $busqueda,
    'categoriaEdicion'  => $categoriaEdicion,
    'formulario'        => $formulario,
    'errores'           => $errores,
]);
