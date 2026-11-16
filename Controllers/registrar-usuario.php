<?php
/**
 * Módulo 2: registro de usuarios y asignación de roles.
 * Solo accesible para el perfil Gerente.
 */

requiere_gerente();
csrf_verificar();

$usuarioModelo = new Usuario();
$rolModelo = new Rol();
$rolesDisponibles = $rolModelo->listar();
$errores = [];

$formulario = [
    'nombres'   => '',
    'apellidos' => '',
    'usuario'   => '',
    'telefono'  => '',
    'estado'    => 'Activo',
    'roles'     => [],
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
    if ($usuarioModelo->porUsuario($formulario['usuario']) !== null) {
        $errores[] = 'El nombre de usuario ya está registrado.';
    }
    if (strlen($password) < 8 || !preg_match('/[A-Za-z]/', $password) || !preg_match('/[0-9]/', $password)) {
        $errores[] = 'La contraseña debe tener al menos 8 caracteres e incluir letras y números.';
    }
    if ($password !== $confirmar) {
        $errores[] = 'La confirmación de la contraseña no coincide.';
    }
    if ($formulario['roles'] === []) {
        $errores[] = 'Debe asignar al menos un rol al usuario.';
    }

    if ($errores === []) {
        try {
            $usuarioModelo->crear([
                'nombres'       => $formulario['nombres'],
                'apellidos'     => $formulario['apellidos'],
                'usuario'       => $formulario['usuario'],
                'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                'telefono'      => $formulario['telefono'] !== '' ? $formulario['telefono'] : null,
                'estado'        => $formulario['estado'],
            ], $formulario['roles']);
            flash('success', 'El usuario "' . $formulario['usuario'] . '" fue registrado correctamente.');
            redirect('registrar-usuario');
        } catch (DomainException $e) {
            $errores[] = $e->getMessage();
        }
    }
}

renderizar('registrar-usuario', [
    'titulo'           => 'Usuarios y roles',
    'subtitulo'        => 'Registro de usuarios, asignación de roles y control de acceso',
    'usuarios'         => $usuarioModelo->listar(),
    'roles'            => $rolesDisponibles,
    'errores'          => $errores,
    'formulario'       => $formulario,
    'totalActivos'     => $usuarioModelo->contarActivos(),
]);
