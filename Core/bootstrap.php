<?php
/**
 * Núcleo de arranque de Alex@sys.
 *
 * Centraliza configuración, autoload, sesión, manejo de errores,
 * conexión PDO, seguridad (CSRF, permisos) y funciones auxiliares.
 */

declare(strict_types=1);

define('ROOT_PATH', dirname(__DIR__));
define('APP_NOMBRE', 'Alex@sys');
define('APP_VERSION', '1.0.0');

$config = require ROOT_PATH . '/config/configuracion.php';
$GLOBALS['config'] = $config;

date_default_timezone_set($config['app']['zona_horaria'] ?? 'America/La_Paz');

// ---------------------------------------------------------------------------
// Errores: se registran en logs y nunca se muestra el detalle SQL al usuario.
// ---------------------------------------------------------------------------
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', ROOT_PATH . '/logs/php-error.log');

// ---------------------------------------------------------------------------
// Autoload simple para Core y Models (sin frameworks externos).
// ---------------------------------------------------------------------------
spl_autoload_register(static function (string $clase): void {
    $carpetas = ['Core', 'Core/Database', 'Models'];
    foreach ($carpetas as $carpeta) {
        $archivo = ROOT_PATH . '/' . $carpeta . '/' . $clase . '.php';
        if (is_file($archivo)) {
            require_once $archivo;
            return;
        }
    }
});

// ---------------------------------------------------------------------------
// URL base del proyecto (funciona en htdocs/MVC1 o en la raíz del host).
// ---------------------------------------------------------------------------
if (PHP_SAPI === 'cli') {
    define('BASE_URL', '');
} else {
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['SERVER_PORT'] ?? '') === '443');
    $host  = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $base  = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
    define('BASE_URL', ($https ? 'https' : 'http') . '://' . $host . $base);
}

// ---------------------------------------------------------------------------
// Sesión segura.
// ---------------------------------------------------------------------------
if (PHP_SAPI === 'cli') {
    $_SESSION = $_SESSION ?? [];
} elseif (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
        'secure'   => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
    ]);
    session_name('ALEXSYSID');
    session_start();
}

// ---------------------------------------------------------------------------
// Funciones de configuración y utilidades generales.
// ---------------------------------------------------------------------------
function config(string $ruta, mixed $porDefecto = null): mixed
{
    $valor = $GLOBALS['config'] ?? [];
    foreach (explode('.', $ruta) as $parte) {
        if (!is_array($valor) || !array_key_exists($parte, $valor)) {
            return $porDefecto;
        }
        $valor = $valor[$parte];
    }
    return $valor;
}

function e(mixed $valor): string
{
    return htmlspecialchars((string) ($valor ?? ''), ENT_QUOTES, 'UTF-8');
}

function url(string $ruta = 'index', array $parametros = []): string
{
    $parametros = array_merge(['ruta' => $ruta], $parametros);
    return BASE_URL . '/index.php?' . http_build_query($parametros);
}

function asset(string $ruta): string
{
    $url = BASE_URL . '/' . ltrim($ruta, '/');
    // Evita que el navegador conserve versiones antiguas de CSS y JS.
    if (preg_match('#^assets/(css|js)/#', $ruta)) {
        $archivo = ROOT_PATH . '/' . ltrim($ruta, '/');
        if (is_file($archivo)) {
            $url .= '?v=' . filemtime($archivo);
        }
    }
    return $url;
}

function redirect(string $ruta = 'index', array $parametros = []): never
{
    header('Location: ' . url($ruta, $parametros));
    exit;
}

function es_post(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

function post(string $campo, mixed $porDefecto = ''): mixed
{
    $valor = $_POST[$campo] ?? $porDefecto;
    return is_string($valor) ? trim($valor) : $valor;
}

function get(string $campo, mixed $porDefecto = ''): mixed
{
    $valor = $_GET[$campo] ?? $porDefecto;
    return is_string($valor) ? trim($valor) : $valor;
}

// ---------------------------------------------------------------------------
// Mensajes flash y CSRF.
// ---------------------------------------------------------------------------
function flash(string $tipo, string $mensaje): void
{
    $_SESSION['flash'][] = ['tipo' => $tipo, 'mensaje' => $mensaje];
}

function obtener_flashes(): array
{
    $mensajes = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $mensajes;
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function csrf_verificar(): void
{
    if (!es_post()) {
        return;
    }
    $enviado = (string) ($_POST['csrf'] ?? '');
    $guardado = (string) ($_SESSION['csrf_token'] ?? '');
    if ($guardado === '' || $enviado === '' || !hash_equals($guardado, $enviado)) {
        flash('danger', 'La sesión o el formulario expiró. Vuelva a intentar la operación.');
        redirect((string) ($_GET['ruta'] ?? 'index'));
    }
}

// ---------------------------------------------------------------------------
// Autenticación y autorización.
// ---------------------------------------------------------------------------
function esta_autenticado(): bool
{
    return !empty($_SESSION['usuario']['id_usuario']);
}

function usuario_actual(): ?array
{
    return $_SESSION['usuario'] ?? null;
}

function roles_usuario(): array
{
    return $_SESSION['usuario']['roles'] ?? [];
}

function tiene_rol(string $rol): bool
{
    return in_array($rol, roles_usuario(), true);
}

function es_gerente(): bool
{
    return tiene_rol('Gerente');
}

function modulos_permitidos(): array
{
    return [
        'Gerente' => ['*'],
        'Vendedor' => [
            'index', 'clientes', 'categorias', 'productos', 'inventario',
            'ventas', 'pagos-venta', 'equipos', 'garantias', 'reportes', 'logout',
        ],
        'Tecnico' => [
            'index', 'clientes', 'productos', 'inventario', 'equipos',
            'ordenes-servicio', 'detalle-servicio', 'repuestos-servicio',
            'pagos-servicio', 'garantias', 'reportes', 'logout',
        ],
    ];
}

function puede(string $modulo): bool
{
    if (!esta_autenticado()) {
        return false;
    }
    if (es_gerente()) {
        return true;
    }
    foreach (roles_usuario() as $rol) {
        $permitidos = modulos_permitidos()[$rol] ?? [];
        if (in_array('*', $permitidos, true) || in_array($modulo, $permitidos, true)) {
            return true;
        }
    }
    return false;
}

function modulos_escritura(): array
{
    return [
        'Gerente' => ['*'],
        'Vendedor' => ['clientes', 'ventas', 'pagos-venta', 'equipos', 'garantias'],
        'Tecnico' => [
            'clientes', 'equipos', 'ordenes-servicio', 'detalle-servicio',
            'repuestos-servicio', 'pagos-servicio', 'garantias',
        ],
    ];
}

function puede_escribir(string $modulo): bool
{
    if (!esta_autenticado()) {
        return false;
    }
    if (es_gerente()) {
        return true;
    }
    foreach (roles_usuario() as $rol) {
        $permitidos = modulos_escritura()[$rol] ?? [];
        if (in_array('*', $permitidos, true) || in_array($modulo, $permitidos, true)) {
            return true;
        }
    }
    return false;
}

function requiere_login(): void
{
    if (!esta_autenticado()) {
        flash('warning', 'Debe iniciar sesión para acceder al sistema.');
        redirect('login');
    }
}

function requiere_permiso(string $modulo): void
{
    requiere_login();
    if (!puede($modulo)) {
        flash('danger', 'No tiene permisos para acceder al módulo solicitado.');
        redirect('index');
    }
}

function requiere_escritura(string $modulo): void
{
    if (!puede_escribir($modulo)) {
        flash('danger', 'Su perfil no tiene autorización para realizar esta operación.');
        redirect($modulo);
    }
}

function requiere_gerente(): void
{
    if (!es_gerente()) {
        flash('danger', 'Operación exclusiva del perfil Gerente.');
        redirect('index');
    }
}

function iniciar_sesion_usuario(array $usuario, array $roles): void
{
    if (PHP_SAPI !== 'cli') {
        session_regenerate_id(true);
    }
    $_SESSION['usuario'] = [
        'id_usuario' => (int) $usuario['id_usuario'],
        'nombres'    => $usuario['nombres'],
        'apellidos'  => $usuario['apellidos'],
        'usuario'    => $usuario['usuario'],
        'roles'      => $roles,
    ];
    $_SESSION['ultimo_acceso'] = time();
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function cerrar_sesion_usuario(): void
{
    unset($_SESSION['usuario'], $_SESSION['ultimo_acceso'], $_SESSION['csrf_token']);
    if (PHP_SAPI !== 'cli') {
        session_regenerate_id(true);
    }
}

// Control de inactividad.
if (esta_autenticado()) {
    $limite = (int) config('seguridad.minutos_inactividad', 60) * 60;
    $ultimo = (int) ($_SESSION['ultimo_acceso'] ?? time());
    if ($limite > 0 && (time() - $ultimo) > $limite) {
        cerrar_sesion_usuario();
        flash('warning', 'La sesión se cerró por inactividad. Vuelva a iniciar sesión.');
    } else {
        $_SESSION['ultimo_acceso'] = time();
    }
}

// ---------------------------------------------------------------------------
// Formato y presentación.
// ---------------------------------------------------------------------------
function formato_moneda(mixed $valor): string
{
    return config('app.moneda', 'Bs') . ' ' . number_format((float) $valor, 2, ',', '.');
}

function formato_fecha(?string $fecha, bool $conHora = false): string
{
    if ($fecha === null || $fecha === '' || str_starts_with($fecha, '0000-00-00')) {
        return '—';
    }
    $timestamp = strtotime($fecha);
    if ($timestamp === false) {
        return '—';
    }
    return date($conHora ? 'd/m/Y H:i' : 'd/m/Y', $timestamp);
}

function etiqueta_estado(string $estado): string
{
    $mapa = [
        'Activo' => 'Activo', 'Inactivo' => 'Inactivo',
        'Registrada' => 'Registrada', 'Anulada' => 'Anulada',
        'Confirmada' => 'Confirmada', 'Confirmado' => 'Confirmado',
        'Recibido' => 'Recibido', 'En diagnostico' => 'En diagnóstico',
        'En reparacion' => 'En reparación', 'Esperando repuesto' => 'Esperando repuesto',
        'Terminado' => 'Terminado', 'Entregado' => 'Entregado',
        'Vigente' => 'Vigente', 'Vencida' => 'Vencida',
    ];
    return $mapa[$estado] ?? $estado;
}

function clase_estado(string $estado): string
{
    return match ($estado) {
        'Activo', 'Registrada', 'Confirmada', 'Confirmado', 'Entregado', 'Vigente' => 'success',
        'Inactivo', 'Anulada', 'Anulado', 'Vencida' => 'secondary',
        'Recibido' => 'info',
        'En diagnostico', 'En reparacion' => 'warning',
        'Esperando repuesto' => 'danger',
        'Terminado' => 'primary',
        default => 'secondary',
    };
}

function fecha_valida(string $fecha): bool
{
    if ($fecha === '') {
        return false;
    }
    $d = DateTime::createFromFormat('Y-m-d', $fecha);
    return $d !== false && $d->format('Y-m-d') === $fecha;
}

function fecha_hora_valida(string $fecha): bool
{
    if ($fecha === '') {
        return false;
    }
    $d = DateTime::createFromFormat('Y-m-d\TH:i', $fecha);
    return $d !== false;
}

function entero_positivo(mixed $valor): bool
{
    return filter_var($valor, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) !== false;
}

function numero_no_negativo(mixed $valor): bool
{
    return is_numeric($valor) && (float) $valor >= 0;
}

function correo_valido(string $correo): bool
{
    return $correo === '' || filter_var($correo, FILTER_VALIDATE_EMAIL) !== false;
}

function texto_seguro(mixed $valor, int $max = 500): string
{
    $texto = trim((string) $valor);
    $texto = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/u', '', $texto) ?? '';
    return mb_substr($texto, 0, $max, 'UTF-8');
}

function nombre_completo(array $fila): string
{
    return trim(($fila['nombres'] ?? '') . ' ' . ($fila['apellidos'] ?? ''));
}

function fecha_actual(): string
{
    return date('Y-m-d');
}

function ruta_actual(): string
{
    return (string) ($_GET['ruta'] ?? 'index');
}

// ---------------------------------------------------------------------------
// Renderizado de vistas y parciales.
// ---------------------------------------------------------------------------
function renderizar(string $vista, array $variables = []): void
{
    $archivo = ROOT_PATH . '/Views/' . $vista . '.view.php';
    if (!is_file($archivo)) {
        throw new RuntimeException('No se encontró la vista solicitada: ' . $vista);
    }
    // El nombre del parámetro no puede ser $datos: varias vistas reciben una
    // clave llamada "datos" y EXTR_SKIP impediría que llegue a la plantilla.
    extract($variables, EXTR_SKIP);
    require $archivo;
}

function parcial(string $nombre, array $variables = []): void
{
    $archivo = ROOT_PATH . '/Views/partials/' . $nombre . '.php';
    if (!is_file($archivo)) {
        throw new RuntimeException('No se encontró la plantilla solicitada: ' . $nombre);
    }
    extract($variables, EXTR_SKIP);
    require $archivo;
}

/**
 * Guarda una imagen subida dentro de imagen/productos y devuelve su ruta
 * relativa. Solo acepta JPG, PNG y WEBP de hasta 2 MB.
 */
function guardar_imagen_producto(array $archivo): ?string
{
    if (($archivo['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if (($archivo['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        throw new DomainException('No se pudo recibir la imagen del producto.');
    }
    if (($archivo['size'] ?? 0) > 2 * 1024 * 1024) {
        throw new DomainException('La imagen supera el tamaño máximo permitido (2 MB).');
    }

    $info = @getimagesize($archivo['tmp_name']);
    if ($info === false) {
        throw new DomainException('El archivo enviado no es una imagen válida.');
    }
    $permitidos = [
        IMAGETYPE_JPEG => 'jpg',
        IMAGETYPE_PNG  => 'png',
        IMAGETYPE_WEBP => 'webp',
    ];
    if (!isset($permitidos[$info[2]])) {
        throw new DomainException('Formato de imagen no permitido. Use JPG, PNG o WEBP.');
    }

    $carpeta = ROOT_PATH . '/imagen/productos';
    if (!is_dir($carpeta) && !mkdir($carpeta, 0775, true) && !is_dir($carpeta)) {
        throw new RuntimeException('No se pudo crear la carpeta de imágenes.');
    }

    $nombre = 'producto-' . date('Ymd-His') . '-' . bin2hex(random_bytes(4)) . '.' . $permitidos[$info[2]];
    $destino = $carpeta . '/' . $nombre;
    if (!move_uploaded_file($archivo['tmp_name'], $destino)) {
        throw new RuntimeException('No se pudo guardar la imagen del producto.');
    }

    return 'imagen/productos/' . $nombre;
}

// ---------------------------------------------------------------------------
// Manejo global de excepciones (sin exponer SQL ni rutas al usuario final).
// ---------------------------------------------------------------------------
set_exception_handler(static function (Throwable $ex): void {
    error_log('[Alex@sys] ' . $ex::class . ': ' . $ex->getMessage() . ' en '
        . $ex->getFile() . ':' . $ex->getLine());

    if (PHP_SAPI === 'cli') {
        fwrite(STDERR, 'ERROR: ' . $ex->getMessage() . PHP_EOL);
        return;
    }

    http_response_code(500);
    $mensaje = 'Ocurrió un error interno al procesar la solicitud. '
        . 'El incidente fue registrado para su revisión.';
    echo '<!doctype html><html lang="es"><head><meta charset="utf-8">'
        . '<meta name="viewport" content="width=device-width, initial-scale=1">'
        . '<title>Error - Alex@sys</title>'
        . '<link rel="stylesheet" href="' . asset('assets/vendor/bootstrap.min.css') . '"></head>'
        . '<body class="bg-light"><div class="container py-5"><div class="card shadow-sm mx-auto" style="max-width:640px">'
        . '<div class="card-body p-4 text-center"><h1 class="h4 text-danger mb-3">'
        . '<i class="fa-solid fa-triangle-exclamation"></i> Error del sistema</h1>'
        . '<p class="mb-3">' . e($mensaje) . '</p>'
        . '<a class="btn btn-primary" href="' . BASE_URL . '/index.php">Volver al inicio</a>'
        . '</div></div></div></body></html>';
});
