<?php
/**
 * Controlador de inicio.
 * - Usuario autenticado sin parámetro "pagina": panel con datos reales.
 * - Visitante o "pagina" indicada: sitio público (Inicio, Nosotros,
 *   Productos, Servicios, Contactos).
 */

if (esta_autenticado() && !isset($_GET['pagina'])) {
    $reporte = new Reporte();
    $datos = $reporte->dashboard();
    $usuarioPanel = usuario_actual() ?? [];

    $perfil = es_gerente()
        ? 'Gerente'
        : (tiene_rol('Tecnico') ? 'Tecnico' : (tiene_rol('Vendedor') ? 'Vendedor' : 'Usuario'));

    $ordenModelo = new OrdenServicio();
    if ($perfil === 'Tecnico') {
        $idUsuario = (int) ($usuarioPanel['id_usuario'] ?? 0);
        $datos['mis_ordenes'] = $ordenModelo->listarDelTecnico($idUsuario, 8);
        $datos['mis_ordenes_activas'] = $ordenModelo->contarDelTecnico($idUsuario);
    }
    if ($perfil === 'Gerente' || $perfil === 'Tecnico') {
        $datos['ordenes_por_estado'] = $ordenModelo->conteoPorEstado();
    }

    renderizar('dashboard', [
        'titulo'       => 'Dashboard',
        'subtitulo'    => 'Resumen de la tienda y del servicio técnico',
        'datos'        => $datos,
        'perfil'       => $perfil,
        'usuarioPanel' => $usuarioPanel,
    ]);
    return;
}

$pagina = (string) get('pagina', 'inicio');
$paginasValidas = ['inicio', 'nosotros', 'productos', 'servicios', 'contactos'];
if (!in_array($pagina, $paginasValidas, true)) {
    $pagina = 'inicio';
}

$erroresContacto = [];
$formularioContacto = ['nombre' => '', 'correo' => '', 'mensaje' => ''];
if ($pagina === 'contactos' && es_post()) {
    csrf_verificar();

    $limitesContacto = ['nombre' => 100, 'correo' => 254, 'mensaje' => 3000];
    foreach ($limitesContacto as $campo => $limite) {
        $valor = post($campo);
        if (!is_string($valor) || !mb_check_encoding($valor, 'UTF-8')) {
            $erroresContacto[] = 'El campo ' . $campo . ' contiene un valor no válido.';
            continue;
        }

        $valor = trim($valor);
        if (mb_strlen($valor, 'UTF-8') > $limite) {
            $erroresContacto[] = 'El campo ' . $campo . ' no puede superar ' . $limite . ' caracteres.';
        }
        $formularioContacto[$campo] = texto_seguro($valor, $limite);
    }

    if (!preg_match('/\A[\p{L}]+(?: +[\p{L}]+)*\z/u', $formularioContacto['nombre'])) {
        $erroresContacto[] = 'Escriba un nombre usando solo letras y espacios.';
    }
    if (!filter_var($formularioContacto['correo'], FILTER_VALIDATE_EMAIL)) {
        $erroresContacto[] = 'Escriba un correo electrónico válido.';
    }
    $longitudMensaje = mb_strlen($formularioContacto['mensaje'], 'UTF-8');
    if ($longitudMensaje < 10 || $longitudMensaje > 3000) {
        $erroresContacto[] = 'La consulta debe tener entre 10 y 3000 caracteres.';
    }

    if ($erroresContacto === []) {
        $consultaModelo = new ConsultaContacto();
        $idConsulta = $consultaModelo->registrar($formularioContacto);
        $correoDestino = (string) config('contacto.correo_destino', '');
        if (!filter_var($correoDestino, FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException('La dirección de destino del formulario de contacto no es válida.');
        }

        $asunto = 'Consulta desde el sitio web - Alex@sys';
        $cuerpo = "Nombre: {$formularioContacto['nombre']}\n"
            . "Correo: {$formularioContacto['correo']}\n\n"
            . "Consulta:\n{$formularioContacto['mensaje']}\n";
        $cabeceras = [
            'From: Alex@sys <' . $correoDestino . '>',
            'Reply-To: ' . $formularioContacto['correo'],
            'Content-Type: text/plain; charset=UTF-8',
        ];

        if (function_exists('mail') && @mail($correoDestino, $asunto, $cuerpo, implode("\r\n", $cabeceras))) {
            $consultaModelo->marcarNotificacionEnviada($idConsulta);
            flash('success', 'Correo enviado correctamente. La tienda recibió tu consulta.');
        } else {
            error_log('[Alex@sys] Consulta de contacto guardada, pero no se pudo enviar el correo. ID: ' . $idConsulta);
            flash('warning', 'Tu consulta quedó guardada, pero el correo no pudo enviarse. Contacta a la tienda por teléfono para confirmar su recepción.');
        }

        redirect('index', ['pagina' => 'contactos']);
    }
}

$productoModelo = new Producto();
$categoriaModelo = new Categoria();

$productos = [];
$categorias = $categoriaModelo->activas();
$busqueda = texto_seguro(get('q', ''), 80);
$idCategoria = (int) get('categoria', 0);

if ($pagina === 'inicio') {
    $productos = $productoModelo->destacados(6);
} elseif ($pagina === 'productos') {
    $productos = $productoModelo->listar($busqueda, $idCategoria);
    $productos = array_values(array_filter(
        $productos,
        static fn (array $p): bool => $p['estado'] === 'Activo'
    ));
}

$titulos = [
    'inicio'    => 'Inicio',
    'nosotros'  => 'Nosotros',
    'productos' => 'Productos',
    'servicios' => 'Servicios',
    'contactos' => 'Contactos',
];

renderizar('index', [
    'titulo'     => $titulos[$pagina] ?? 'Inicio',
    'pagina'     => $pagina,
    'productos'  => $productos,
    'categorias' => $categorias,
    'busqueda'   => $busqueda,
    'idCategoria' => $idCategoria,
    'erroresContacto' => $erroresContacto,
    'formularioContacto' => $formularioContacto,
]);
