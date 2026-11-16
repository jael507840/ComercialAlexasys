<?php
/**
 * Controlador de autenticación.
 * Valida credenciales con password_verify, aplica límite de intentos
 * y bloquea el acceso de usuarios inactivos.
 */

if (esta_autenticado()) {
    redirect('index');
}

csrf_verificar();

$usuarioModelo = new Usuario();
$errores = [];
$usuarioIngresado = '';

if (es_post()) {
    $usuarioIngresado = texto_seguro(post('usuario'), 50);
    $password = (string) post('password');

    // Bloqueo temporal por intentos fallidos.
    $bloqueoHasta = (int) ($_SESSION['login_bloqueo_hasta'] ?? 0);
    if ($bloqueoHasta > time()) {
        $restantes = (int) ceil(($bloqueoHasta - time()) / 60);
        $errores[] = 'Demasiados intentos fallidos. Intente nuevamente en ' . max(1, $restantes) . ' minuto(s).';
    } elseif ($usuarioIngresado === '' || $password === '') {
        $errores[] = 'Ingrese su usuario y contraseña.';
    } else {
        $registro = $usuarioModelo->activoPorUsuario($usuarioIngresado);
        $valido = $registro !== null && password_verify($password, (string) $registro['password_hash']);

        if (!$valido) {
            $_SESSION['login_intentos'] = (int) ($_SESSION['login_intentos'] ?? 0) + 1;
            $maximo = (int) config('seguridad.max_intentos_login', 5);
            if ($_SESSION['login_intentos'] >= $maximo) {
                $_SESSION['login_bloqueo_hasta'] = time() + ((int) config('seguridad.minutos_bloqueo', 15) * 60);
                $_SESSION['login_intentos'] = 0;
                $errores[] = 'Se alcanzó el número máximo de intentos. El acceso se bloqueó temporalmente.';
            } else {
                $restantes = $maximo - (int) $_SESSION['login_intentos'];
                $errores[] = 'Usuario o contraseña incorrectos. Intentos restantes: ' . max(0, $restantes) . '.';
            }
        } else {
            $roles = $usuarioModelo->rolesDe((int) $registro['id_usuario']);
            if ($roles === []) {
                $errores[] = 'Su usuario no tiene un rol asignado. Comuníquese con el gerente del sistema.';
            } else {
                unset($_SESSION['login_intentos'], $_SESSION['login_bloqueo_hasta']);
                iniciar_sesion_usuario($registro, $roles);
                flash('success', 'Bienvenido(a), ' . nombre_completo($registro) . '.');
                redirect('index');
            }
        }
    }
}

renderizar('login', [
    'titulo' => 'Acceso al sistema',
    'errores' => $errores,
    'usuarioIngresado' => $usuarioIngresado,
]);
