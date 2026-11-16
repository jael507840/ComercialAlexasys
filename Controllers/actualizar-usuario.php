<?php
/**
 * Módulo 2: actualización de usuarios, roles, estado y contraseña.
 */

requiere_gerente();
csrf_verificar();

$usuarioModelo = new Usuario();
$rolModelo = new Rol();
$rolesDisponibles = $rolModelo->listar();
$errores = [];

$id = (int) (get('id', 0) ?: post('id', 0));
$usuario = $usuarioModelo->porId($id);
if (!$usuario) {
    flash('danger', 'El usuario solicitado no existe.');
    redirect('registrar-usuario');
}

$rolesActuales = $usuarioModelo->rolesDe($id);
$formulario = [
    'nombres'   => $usuario['nombres'],
    'apellidos' => $usuario['apellidos'],
    'usuario'   => $usuario['usuario'],
    'telefono'  => (string) ($usuario['telefono'] ?? ''),
    'estado'    => $usuario['estado'],
    'roles'     => array_map(
        static fn (string $nombre): int => (int) ($rolModelo->porNombre($nombre)['id_rol'] ?? 0),
        $rolesActuales
    ),
];

if (es_post()) {
    $formulario = [
        'nombres'   => texto_seguro(post('nombres'), 100),
        'apellidos' => texto_seguro(post('apellidos'), 100),
        'usuario'   => texto_seguro(post('usuario'), 50),
        'telefono'  => texto_seguro(post('telefono'), 20),
        'estado'    => post('estado') === 'Inactivo' ? 'Inactivo' : 'Activo',
        'roles'     => array_map('intval', (array) ($_POST['roles'] ?? [])),
    ];
    $password = (string) post('password');
    $confirmar = (string) post('password_confirmar');

    if ($formulario['nombres'] === '') {
        $errores[] = 'Los nombres son obligatorios.';
    }
    if ($formulario['apellidos'] === '') {
        $errores[] = 'Los apellidos son obligatorios.';
    }
    if (!preg_match('/^[A-Za-z0-9._-]{4,50}$/', $formulario['usuario'])) {
        $errores[] = 'El usuario debe tener entre 4 y 50 caracteres (letras, números, punto, guion o guion bajo).';
    }
    $existente = $usuarioModelo->porUsuario($formulario['usuario']);
    if ($existente !== null && (int) $existente['id_usuario'] !== $id) {
        $errores[] = 'El nombre de usuario ya pertenece a otra cuenta.';
    }
    if ($formulario['roles'] === []) {
        $errores[] = 'Debe asignar al menos un rol.';
    }

    $esPropiaCuenta = $id === (int) (usuario_actual()['id_usuario'] ?? 0);
    if ($esPropiaCuenta && $formulario['estado'] === 'Inactivo') {
        $errores[] = 'No puede desactivar su propia cuenta.';
    }

    // Evitar que el gerente se quite a sí mismo el rol que le da acceso total.
    $rolGerente = $rolModelo->porNombre('Gerente');
    if ($esPropiaCuenta && $rolGerente !== null && !in_array((int) $rolGerente['id_rol'], $formulario['roles'], true)) {
        $errores[] = 'No puede retirar su propio rol Gerente; otro gerente debe realizar el cambio.';
    }

    if ($password !== '' || $confirmar !== '') {
        if (strlen($password) < 8 || !preg_match('/[A-Za-z]/', $password) || !preg_match('/[0-9]/', $password)) {
            $errores[] = 'La nueva contraseña debe tener al menos 8 caracteres e incluir letras y números.';
        }
        if ($password !== $confirmar) {
            $errores[] = 'La confirmación de la nueva contraseña no coincide.';
        }
    }

    if ($errores === []) {
        try {
            $datos = [
                'nombres'   => $formulario['nombres'],
                'apellidos' => $formulario['apellidos'],
                'usuario'   => $formulario['usuario'],
                'telefono'  => $formulario['telefono'] !== '' ? $formulario['telefono'] : null,
                'estado'    => $formulario['estado'],
            ];
            if ($password !== '') {
                $datos['password_hash'] = password_hash($password, PASSWORD_DEFAULT);
            }
            $usuarioModelo->actualizar($id, $datos, $formulario['roles']);

            // Si el usuario editó su propia cuenta, refrescar los datos de sesión.
            if ($esPropiaCuenta) {
                $usuarioActualizado = $usuarioModelo->porId($id);
                iniciar_sesion_usuario($usuarioActualizado, $usuarioModelo->rolesDe($id));
            }

            flash('success', 'Los datos del usuario fueron actualizados.');
            redirect('registrar-usuario');
        } catch (DomainException $e) {
            $errores[] = $e->getMessage();
        }
    }
}

renderizar('actualizar-usuario', [
    'titulo'     => 'Editar usuario',
    'subtitulo'  => 'Actualización de datos, roles y credenciales',
    'usuario'    => $usuario,
    'roles'      => $rolesDisponibles,
    'errores'    => $errores,
    'formulario' => $formulario,
]);
