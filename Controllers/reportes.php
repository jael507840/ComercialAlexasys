<?php
/**
 * Módulo de consultas e informes operativos.
 */

requiere_permiso('reportes');

$reporteModelo = new Reporte();
$categoriaModelo = new Categoria();
$clienteModelo = new Cliente();
$proveedorModelo = new Proveedor();
$errores = [];
$reportesDisponibles = [
    'ventas' => 'Ventas',
    'compras' => 'Compras',
    'clientes' => 'Clientes',
    'productos' => 'Productos',
    'costos' => 'Costos y márgenes',
    'pagos_ventas' => 'Pagos de ventas',
    'pagos_servicios' => 'Pagos de servicios',
    'servicios' => 'Órdenes de servicio',
    'equipos' => 'Equipos de clientes',
    'garantias' => 'Garantías',
    'inventario' => 'Inventario',
];

$tipoReporte = (string) get('tipo_reporte', 'ventas');
if (!array_key_exists($tipoReporte, $reportesDisponibles)) {
    $tipoReporte = 'ventas';
}

$filtros = [
    'desde' => texto_seguro(get('desde', ''), 10),
    'hasta' => texto_seguro(get('hasta', ''), 10),
    'busqueda' => texto_seguro(get('q', ''), 80),
    'id_cliente' => (int) get('cliente', 0),
    'id_proveedor' => (int) get('proveedor', 0),
    'id_categoria' => (int) get('categoria', 0),
    'estado' => (string) get('estado', ''),
    'tipo' => (string) get('tipo', ''),
    'solo_bajo' => (bool) get('bajo', 0),
];

$usaFechas = in_array($tipoReporte, [
    'ventas', 'compras', 'pagos_ventas', 'pagos_servicios', 'servicios', 'equipos',
], true);
if ($usaFechas) {
    foreach (['desde', 'hasta'] as $campoFecha) {
        if ($filtros[$campoFecha] !== '' && !fecha_valida($filtros[$campoFecha])) {
            $errores[] = 'Indique fechas válidas para el periodo.';
            break;
        }
    }
    if ($filtros['desde'] !== '' && $filtros['hasta'] !== ''
        && $filtros['desde'] > $filtros['hasta']) {
        $errores[] = 'La fecha inicial no puede ser posterior a la fecha final.';
    }
}

if ($tipoReporte === 'ventas'
    && !in_array($filtros['estado'], ['', 'Confirmada', 'Anulada'], true)) {
    $filtros['estado'] = '';
}
if ($tipoReporte === 'servicios'
    && !in_array($filtros['estado'], OrdenServicio::ESTADOS, true)) {
    $filtros['estado'] = '';
}
if ($tipoReporte === 'garantias') {
    if (!in_array($filtros['estado'], ['', 'Vigente', 'Vencida', 'Anulada'], true)) {
        $filtros['estado'] = '';
    }
    if (!in_array($filtros['tipo'], ['', 'Producto', 'Servicio'], true)) {
        $filtros['tipo'] = '';
    }
}

$datos = [];
if ($errores === []) {
    $desde = $filtros['desde'] !== '' ? $filtros['desde'] : null;
    $hasta = $filtros['hasta'] !== '' ? $filtros['hasta'] : null;
    switch ($tipoReporte) {
        case 'clientes':
            $datos = $reporteModelo->reporteClientes($filtros['busqueda']);
            break;
        case 'productos':
            $datos = $reporteModelo->reporteProductos($filtros['id_categoria']);
            break;
        case 'ventas':
            $datos = $reporteModelo->reporteVentas(
                $desde,
                $hasta,
                $filtros['id_cliente'],
                $filtros['estado']
            );
            break;
        case 'compras':
            $datos = $reporteModelo->reporteCompras($desde, $hasta, $filtros['id_proveedor']);
            break;
        case 'costos':
            $datos = $reporteModelo->reporteCostos();
            break;
        case 'pagos_ventas':
            $datos = $reporteModelo->reportePagosVentas($desde, $hasta);
            break;
        case 'pagos_servicios':
            $datos = $reporteModelo->reportePagosServicios($desde, $hasta);
            break;
        case 'servicios':
            $datos = $reporteModelo->reporteServicios($desde, $hasta, $filtros['estado']);
            break;
        case 'equipos':
            $datos = $reporteModelo->reporteEquipos($desde, $hasta);
            break;
        case 'garantias':
            $datos = $reporteModelo->reporteGarantias(
                $filtros['estado'],
                $filtros['tipo'],
                $filtros['busqueda']
            );
            break;
        case 'inventario':
            $datos = $reporteModelo->reporteInventario(
                $filtros['id_categoria'],
                $filtros['solo_bajo']
            );
            break;
    }
}

$columnasPorReporte = [
    'clientes' => [
        'ci' => 'CI', 'nombres' => 'Nombres', 'apellidos' => 'Apellidos',
        'telefono' => 'Teléfono', 'correo' => 'Correo', 'estado' => 'Estado',
    ],
    'productos' => [
        'codigo' => 'Código', 'nombre' => 'Producto', 'categoria' => 'Categoría',
        'marca' => 'Marca', 'modelo' => 'Modelo', 'precio_venta' => 'Precio',
        'stock_actual' => 'Stock', 'stock_minimo' => 'Mínimo', 'estado' => 'Estado',
    ],
    'ventas' => [
        'numero_comprobante' => 'Comprobante', 'fecha_venta' => 'Fecha',
        'cliente' => 'Cliente', 'vendedor' => 'Vendedor', 'total' => 'Total',
        'pagado' => 'Pagado', 'saldo' => 'Saldo', 'estado' => 'Estado', 'items' => 'Líneas',
    ],
    'compras' => [
        'numero_comprobante' => 'Comprobante', 'fecha_compra' => 'Fecha',
        'proveedor' => 'Proveedor', 'total_compra' => 'Total', 'estado' => 'Estado',
        'total_items' => 'Líneas',
    ],
    'costos' => [
        'codigo' => 'Código', 'nombre' => 'Producto', 'categoria' => 'Categoría',
        'costo_adquisicion' => 'Costo adquisición', 'precio_venta' => 'Precio venta',
        'margen' => 'Margen', 'margen_porcentaje' => 'Margen %', 'stock_actual' => 'Stock',
    ],
    'pagos_ventas' => [
        'fecha_pago' => 'Fecha', 'numero_comprobante' => 'Comprobante',
        'cliente' => 'Cliente', 'metodo_pago' => 'Método', 'referencia' => 'Referencia',
        'monto' => 'Monto', 'usuario' => 'Registrado por', 'estado' => 'Estado',
    ],
    'pagos_servicios' => [
        'fecha_pago' => 'Fecha', 'numero_orden' => 'Orden', 'cliente' => 'Cliente',
        'metodo_pago' => 'Método', 'referencia' => 'Referencia', 'monto' => 'Monto',
        'usuario' => 'Registrado por', 'estado' => 'Estado',
    ],
    'servicios' => [
        'numero_orden' => 'Orden', 'fecha_recepcion' => 'Recepción', 'cliente' => 'Cliente',
        'marca' => 'Marca', 'modelo' => 'Modelo', 'tecnico' => 'Técnico',
        'estado' => 'Estado', 'total' => 'Total', 'pagado' => 'Pagado', 'saldo' => 'Saldo',
    ],
    'equipos' => [
        'fecha_registro' => 'Registro', 'cliente' => 'Cliente', 'ci' => 'CI',
        'tipo_equipo' => 'Tipo', 'marca' => 'Marca', 'modelo' => 'Modelo',
        'numero_serie' => 'N.º serie', 'total_ordenes' => 'Órdenes',
    ],
    'garantias' => [
        'tipo_garantia' => 'Tipo', 'cliente' => 'Cliente', 'ci' => 'CI',
        'producto' => 'Producto', 'numero_serie' => 'N.º serie',
        'numero_comprobante' => 'Comprobante', 'numero_orden' => 'Orden',
        'fecha_inicio' => 'Inicio', 'fecha_fin' => 'Vencimiento',
        'vigencia' => 'Vigencia', 'descripcion' => 'Descripción',
    ],
    'inventario' => [
        'codigo' => 'Código', 'nombre' => 'Producto', 'categoria' => 'Categoría',
        'marca' => 'Marca', 'stock_actual' => 'Stock', 'stock_minimo' => 'Mínimo',
        'precio_venta' => 'Precio', 'costo_adquisicion' => 'Costo adquisición',
        'estado' => 'Estado',
    ],
];

$puedeFiltrarFechas = $usaFechas;
$categorias = $categoriaModelo->activas();
$clientes = $clienteModelo->listar();
$proveedores = $proveedorModelo->activos();

// Totales que se muestran en la parte superior y en el PDF.
$definicionesTotales = [
    'ventas' => [
        ['campo' => 'total', 'etiqueta' => 'Total vendido', 'tipo' => 'moneda'],
        ['campo' => 'pagado', 'etiqueta' => 'Pagado', 'tipo' => 'moneda'],
        ['campo' => 'saldo', 'etiqueta' => 'Saldo', 'tipo' => 'moneda'],
    ],
    'compras' => [
        ['campo' => 'total_compra', 'etiqueta' => 'Total compras', 'tipo' => 'moneda'],
    ],
    'costos' => [
        ['campo' => 'margen', 'etiqueta' => 'Margen potencial', 'tipo' => 'moneda'],
    ],
    'pagos_ventas' => [
        ['campo' => 'monto', 'etiqueta' => 'Total cobrado', 'tipo' => 'moneda'],
    ],
    'pagos_servicios' => [
        ['campo' => 'monto', 'etiqueta' => 'Total cobrado', 'tipo' => 'moneda'],
    ],
    'servicios' => [
        ['campo' => 'total', 'etiqueta' => 'Total servicios', 'tipo' => 'moneda'],
        ['campo' => 'pagado', 'etiqueta' => 'Pagado', 'tipo' => 'moneda'],
        ['campo' => 'saldo', 'etiqueta' => 'Saldo', 'tipo' => 'moneda'],
    ],
    'productos' => [
        ['campo' => 'stock_actual', 'etiqueta' => 'Unidades en stock', 'tipo' => 'cantidad'],
    ],
    'inventario' => [
        ['campo' => 'stock_actual', 'etiqueta' => 'Unidades en stock', 'tipo' => 'cantidad'],
    ],
];

$resumen = ['registros' => count($datos), 'totales' => []];
foreach ($definicionesTotales[$tipoReporte] ?? [] as $definicion) {
    $suma = 0.0;
    foreach ($datos as $fila) {
        $suma += (float) ($fila[$definicion['campo']] ?? 0);
    }
    $resumen['totales'][] = [
        'etiqueta' => $definicion['etiqueta'],
        'valor' => $definicion['tipo'] === 'moneda'
            ? formato_moneda($suma)
            : number_format($suma, 0, ',', '.'),
    ];
}

$filtrosTexto = [];
if ($filtros['desde'] !== '' || $filtros['hasta'] !== '') {
    $filtrosTexto[] = 'Periodo: ' . ($filtros['desde'] !== '' ? formato_fecha($filtros['desde']) : 'inicio')
        . ' al ' . ($filtros['hasta'] !== '' ? formato_fecha($filtros['hasta']) : 'hoy');
}
if ($filtros['busqueda'] !== '') {
    $filtrosTexto[] = 'Búsqueda: ' . $filtros['busqueda'];
}
if ($filtros['id_cliente'] > 0) {
    foreach ($clientes as $cliente) {
        if ((int) $cliente['id_cliente'] === $filtros['id_cliente']) {
            $filtrosTexto[] = 'Cliente: ' . trim($cliente['nombres'] . ' ' . $cliente['apellidos']);
            break;
        }
    }
}
if ($filtros['id_proveedor'] > 0) {
    foreach ($proveedores as $proveedor) {
        if ((int) $proveedor['id_proveedor'] === $filtros['id_proveedor']) {
            $filtrosTexto[] = 'Proveedor: ' . $proveedor['nombre'];
            break;
        }
    }
}
if ($filtros['id_categoria'] > 0) {
    foreach ($categorias as $categoria) {
        if ((int) $categoria['id_categoria'] === $filtros['id_categoria']) {
            $filtrosTexto[] = 'Categoría: ' . $categoria['nombre'];
            break;
        }
    }
}
if ($filtros['estado'] !== '') {
    $filtrosTexto[] = 'Estado: ' . etiqueta_estado($filtros['estado']);
}
if ($filtros['tipo'] !== '') {
    $filtrosTexto[] = 'Tipo: ' . $filtros['tipo'];
}
if ($filtros['solo_bajo']) {
    $filtrosTexto[] = 'Solo stock bajo';
}
if ($filtrosTexto === []) {
    $filtrosTexto[] = 'Sin filtros adicionales';
}

$usuarioPdf = usuario_actual() ?? [];
$nombreUsuario = trim((string) (($usuarioPdf['nombres'] ?? '') . ' ' . ($usuarioPdf['apellidos'] ?? '')));

$variablesReporte = [
    'titulo' => 'Reportes',
    'subtitulo' => 'Consultas e informes de la operación',
    'reportesDisponibles' => $reportesDisponibles,
    'tipoReporte' => $tipoReporte,
    'filtros' => $filtros,
    'errores' => $errores,
    'datos' => $datos,
    'columnas' => $columnasPorReporte[$tipoReporte],
    'puedeFiltrarFechas' => $puedeFiltrarFechas,
    'categorias' => $categorias,
    'clientes' => $clientes,
    'proveedores' => $proveedores,
    'resumen' => $resumen,
    'filtrosTexto' => $filtrosTexto,
];

// Exportación del informe actual a PDF con Dompdf (instalado por Composer).
if (strtolower((string) get('formato', '')) === 'pdf') {
    if ($errores !== []) {
        flash('danger', 'Corrija los filtros antes de exportar el informe.');
        redirect('reportes', ['tipo_reporte' => $tipoReporte]);
    }

    $autoload = ROOT_PATH . '/vendor/autoload.php';
    if (!is_file($autoload)) {
        flash('danger', 'Para exportar a PDF instale las dependencias con "composer install".');
        redirect('reportes', ['tipo_reporte' => $tipoReporte]);
    }
    require_once $autoload;

    try {
        $html = contenido_vista('reportes-pdf', $variablesReporte + [
            'usuario' => $nombreUsuario !== '' ? $nombreUsuario : 'Usuario del sistema',
            'generadoEn' => date('d/m/Y H:i'),
        ]);

        $opciones = new \Dompdf\Options();
        $opciones->set('defaultFont', 'DejaVu Sans');
        $opciones->set('isRemoteEnabled', false);
        $opciones->set('isHtml5ParserEnabled', true);
        $opciones->set('chroot', ROOT_PATH);

        $dompdf = new \Dompdf\Dompdf($opciones);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        $nombreArchivo = 'Alexsys-' . $tipoReporte . '-' . date('Ymd-His') . '.pdf';
        if (ob_get_length() !== false) {
            ob_end_clean();
        }
        $dompdf->stream($nombreArchivo, ['Attachment' => true]);
        exit;
    } catch (Throwable $e) {
        error_log('[Alex@sys] Error al generar el PDF de reportes: ' . $e->getMessage());
        flash('danger', 'No se pudo generar el PDF. Revise el log de errores.');
        redirect('reportes', ['tipo_reporte' => $tipoReporte]);
    }
}

renderizar('reportes', $variablesReporte);
