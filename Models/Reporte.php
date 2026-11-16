<?php
/**
 * Consultas de reportes y resumen del panel.
 * Todas las cifras provienen de MySQL (no hay datos simulados).
 */

class Reporte extends Model
{
    protected string $tabla = 'ventas';
    protected string $clavePrimaria = 'id_venta';

    public function dashboard(): array
    {
        $db = $this->db;
        $hoy = date('Y-m-d');
        $inicioMes = date('Y-m-01');

        $clientes = (int) $db->query("SELECT COUNT(*) FROM clientes WHERE estado = 'Activo'")->fetchColumn();
        $productos = (int) $db->query("SELECT COUNT(*) FROM productos WHERE estado = 'Activo'")->fetchColumn();
        $bajoStock = (int) $db->query(
            "SELECT COUNT(*) FROM productos WHERE estado = 'Activo' AND stock_actual <= stock_minimo"
        )->fetchColumn();

        $stmt = $db->prepare(
            "SELECT COUNT(*) AS cantidad, COALESCE(SUM(total), 0) AS monto
             FROM ventas WHERE estado = 'Confirmada' AND DATE(fecha_venta) = :hoy"
        );
        $stmt->execute([':hoy' => $hoy]);
        $ventasHoy = $stmt->fetch() ?: ['cantidad' => 0, 'monto' => 0];

        $stmt = $db->prepare(
            "SELECT COUNT(*) AS cantidad, COALESCE(SUM(total), 0) AS monto
             FROM ventas WHERE estado = 'Confirmada' AND DATE(fecha_venta) >= :inicio"
        );
        $stmt->execute([':inicio' => $inicioMes]);
        $ventasMes = $stmt->fetch() ?: ['cantidad' => 0, 'monto' => 0];

        $stmt = $db->prepare(
            "SELECT COALESCE(SUM(monto), 0) FROM pagos_venta
             WHERE estado = 'Confirmado' AND DATE(fecha_pago) = :hoy"
        );
        $stmt->execute([':hoy' => $hoy]);
        $pagosVentaHoy = (float) $stmt->fetchColumn();

        $stmt = $db->prepare(
            "SELECT COALESCE(SUM(monto), 0) FROM pagos_servicio
             WHERE estado = 'Confirmado' AND DATE(fecha_pago) = :hoy"
        );
        $stmt->execute([':hoy' => $hoy]);
        $pagosServicioHoy = (float) $stmt->fetchColumn();

        $ordenesActivas = (int) $db->query(
            "SELECT COUNT(*) FROM ordenes_servicio
             WHERE estado IN ('Recibido','En diagnostico','En reparacion','Esperando repuesto','Terminado')"
        )->fetchColumn();
        $garantias = (int) $db->query(
            "SELECT COUNT(*) FROM garantias WHERE estado = 'Vigente' AND fecha_fin >= CURDATE()"
        )->fetchColumn();

        $stmt = $db->prepare(
            "SELECT COALESCE(SUM(monto), 0) FROM pagos_venta
             WHERE estado = 'Confirmado' AND DATE(fecha_pago) >= :inicio"
        );
        $stmt->execute([':inicio' => $inicioMes]);
        $pagosVentaMes = (float) $stmt->fetchColumn();

        $stmt = $db->prepare(
            "SELECT COALESCE(SUM(monto), 0) FROM pagos_servicio
             WHERE estado = 'Confirmado' AND DATE(fecha_pago) >= :inicio"
        );
        $stmt->execute([':inicio' => $inicioMes]);
        $pagosServicioMes = (float) $stmt->fetchColumn();

        $stmt = $db->prepare(
            "SELECT COUNT(*) FROM clientes WHERE DATE(fecha_registro) >= :inicio"
        );
        $stmt->execute([':inicio' => $inicioMes]);
        $clientesNuevosMes = (int) $stmt->fetchColumn();

        $stmt = $db->prepare(
            "SELECT COUNT(*) FROM ordenes_servicio
             WHERE estado <> 'Anulado' AND DATE(fecha_recepcion) = :hoy"
        );
        $stmt->execute([':hoy' => $hoy]);
        $ordenesHoy = (int) $stmt->fetchColumn();

        $ordenesPorEntregar = (int) $db->query(
            "SELECT COUNT(*) FROM ordenes_servicio WHERE estado = 'Terminado'"
        )->fetchColumn();

        $garantiasProximas = (new Garantia())->proximasAVencer(30);

        return [
            'clientes'           => $clientes,
            'productos'          => $productos,
            'bajo_stock'         => $bajoStock,
            'ventas_hoy'         => $ventasHoy,
            'ventas_mes'         => $ventasMes,
            'pagos_venta_hoy'    => $pagosVentaHoy,
            'pagos_servicio_hoy' => $pagosServicioHoy,
            'pagos_venta_mes'    => $pagosVentaMes,
            'pagos_servicio_mes' => $pagosServicioMes,
            'clientes_nuevos_mes' => $clientesNuevosMes,
            'ordenes_activas'    => $ordenesActivas,
            'ordenes_hoy'        => $ordenesHoy,
            'ordenes_por_entregar' => $ordenesPorEntregar,
            'garantias'          => $garantias,
            'garantias_por_vencer' => count($garantiasProximas),
            'ventas_recientes'   => $this->ultimasVentas(5),
            'ordenes_recientes'  => $this->ultimasOrdenes(5),
            'stock_bajo'         => $this->stockBajo(5),
            'serie_ventas'       => $this->ventasUltimosDias(7),
            'garantias_proximas' => $garantiasProximas,
            'ventas_pendientes'  => (new Venta())->conSaldo(5),
            'ordenes_pendientes' => (new OrdenServicio())->conSaldo(5),
        ];
    }

    public function ultimasVentas(int $limite = 5): array
    {
        $limite = max(1, min(50, $limite));
        $sql = "SELECT v.id_venta, v.numero_comprobante, v.fecha_venta, v.total,
                       CONCAT(c.nombres, ' ', c.apellidos) AS cliente,
                       COALESCE((SELECT SUM(pv.monto) FROM pagos_venta pv
                                 WHERE pv.id_venta = v.id_venta AND pv.estado = 'Confirmado'), 0) AS pagado
                FROM ventas v
                INNER JOIN clientes c ON c.id_cliente = v.id_cliente
                WHERE v.estado = 'Confirmada'
                ORDER BY v.fecha_venta DESC LIMIT {$limite}";
        return $this->db->query($sql)->fetchAll();
    }

    public function ultimasOrdenes(int $limite = 5): array
    {
        $limite = max(1, min(50, $limite));
        $sql = "SELECT o.id_orden, o.numero_orden, o.fecha_recepcion, o.estado,
                       CONCAT(c.nombres, ' ', c.apellidos) AS cliente,
                       CONCAT(e.marca, ' ', e.modelo) AS equipo
                FROM ordenes_servicio o
                INNER JOIN equipos_cliente e ON e.id_equipo = o.id_equipo
                INNER JOIN clientes c ON c.id_cliente = e.id_cliente
                ORDER BY o.fecha_recepcion DESC LIMIT {$limite}";
        return $this->db->query($sql)->fetchAll();
    }

    public function stockBajo(int $limite = 5): array
    {
        $limite = max(1, min(50, $limite));
        $sql = "SELECT p.id_producto, p.codigo, p.nombre, p.stock_actual, p.stock_minimo
                FROM productos p
                WHERE p.estado = 'Activo' AND p.stock_actual <= p.stock_minimo
                ORDER BY (CAST(p.stock_actual AS SIGNED) - CAST(p.stock_minimo AS SIGNED)) ASC, p.nombre LIMIT {$limite}";
        return $this->db->query($sql)->fetchAll();
    }

    /** Serie de ventas de los últimos N días para el gráfico del panel. */
    public function ventasUltimosDias(int $dias = 7): array
    {
        $dias = max(2, min(31, $dias));
        $inicio = date('Y-m-d', strtotime('-' . ($dias - 1) . ' days'));
        $stmt = $this->db->prepare(
            "SELECT DATE(fecha_venta) AS fecha, COUNT(*) AS cantidad, COALESCE(SUM(total), 0) AS monto
             FROM ventas
             WHERE estado = 'Confirmada' AND DATE(fecha_venta) >= :inicio
             GROUP BY DATE(fecha_venta) ORDER BY fecha"
        );
        $stmt->execute([':inicio' => $inicio]);
        $mapa = [];
        foreach ($stmt->fetchAll() as $fila) {
            $mapa[$fila['fecha']] = $fila;
        }

        $serie = [];
        for ($i = $dias - 1; $i >= 0; $i--) {
            $fecha = date('Y-m-d', strtotime('-' . $i . ' days'));
            $serie[] = [
                'fecha'    => $fecha,
                'etiqueta' => date('d/m', strtotime($fecha)),
                'cantidad' => (int) ($mapa[$fecha]['cantidad'] ?? 0),
                'monto'    => (float) ($mapa[$fecha]['monto'] ?? 0),
            ];
        }
        return $serie;
    }

    public function reporteClientes(string $busqueda = ''): array
    {
        return (new Cliente())->listar($busqueda);
    }

    public function reporteProductos(int $idCategoria = 0): array
    {
        return (new Producto())->listar('', $idCategoria);
    }

    public function reporteVentas(?string $desde = null, ?string $hasta = null, int $idCliente = 0, string $estado = ''): array
    {
        $sql = 'SELECT v.*, CONCAT(c.nombres, " ", c.apellidos) AS cliente, c.ci,
                       CONCAT(u.nombres, " ", u.apellidos) AS vendedor,
                       COALESCE(pg.pagado, 0) AS pagado,
                       (v.total - COALESCE(pg.pagado, 0)) AS saldo,
                       (SELECT COUNT(*) FROM detalle_venta dv WHERE dv.id_venta = v.id_venta) AS items
                FROM ventas v
                INNER JOIN clientes c ON c.id_cliente = v.id_cliente
                INNER JOIN usuarios u ON u.id_usuario = v.id_usuario
                LEFT JOIN (
                    SELECT id_venta, SUM(monto) AS pagado FROM pagos_venta
                    WHERE estado = \'Confirmado\' GROUP BY id_venta
                ) pg ON pg.id_venta = v.id_venta
                WHERE 1 = 1';
        $parametros = [];
        if ($desde !== null && $desde !== '') {
            $sql .= ' AND DATE(v.fecha_venta) >= :desde';
            $parametros[':desde'] = $desde;
        }
        if ($hasta !== null && $hasta !== '') {
            $sql .= ' AND DATE(v.fecha_venta) <= :hasta';
            $parametros[':hasta'] = $hasta;
        }
        if ($idCliente > 0) {
            $sql .= ' AND v.id_cliente = :cliente';
            $parametros[':cliente'] = $idCliente;
        }
        if (in_array($estado, ['Confirmada', 'Anulada'], true)) {
            $sql .= ' AND v.estado = :estado';
            $parametros[':estado'] = $estado;
        }
        $sql .= ' ORDER BY v.fecha_venta DESC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($parametros);
        return $stmt->fetchAll();
    }

    public function reporteCompras(?string $desde = null, ?string $hasta = null, int $idProveedor = 0): array
    {
        return (new Compra())->listar($desde, $hasta, $idProveedor);
    }

    public function reporteCostos(): array
    {
        $sql = 'SELECT p.codigo, p.nombre, p.marca, c.nombre AS categoria,
                       uc.costo AS costo_adquisicion, p.precio_venta, p.stock_actual,
                       (p.precio_venta - COALESCE(uc.costo, 0)) AS margen,
                       CASE WHEN p.precio_venta > 0
                            THEN ROUND(((p.precio_venta - COALESCE(uc.costo, 0)) / p.precio_venta) * 100, 2)
                            ELSE 0 END AS margen_porcentaje
                FROM productos p
                INNER JOIN categorias c ON c.id_categoria = p.id_categoria
                LEFT JOIN (
                    SELECT dc.id_producto,
                           SUBSTRING_INDEX(GROUP_CONCAT(dc.costo_unitario
                               ORDER BY co.fecha_compra DESC, dc.id_detalle_compra DESC), ",", 1) AS costo
                    FROM detalle_compra dc
                    INNER JOIN compras co ON co.id_compra = dc.id_compra
                    GROUP BY dc.id_producto
                ) uc ON uc.id_producto = p.id_producto
                WHERE p.estado = \'Activo\'
                ORDER BY c.nombre, p.nombre';
        return $this->db->query($sql)->fetchAll();
    }

    public function reportePagosVentas(?string $desde = null, ?string $hasta = null): array
    {
        return (new PagoVenta())->listar(0, $desde, $hasta);
    }

    public function reportePagosServicios(?string $desde = null, ?string $hasta = null): array
    {
        return (new PagoServicio())->listar(0, $desde, $hasta);
    }

    public function reporteServicios(?string $desde = null, ?string $hasta = null, string $estado = ''): array
    {
        return (new OrdenServicio())->listar('', $estado, 0, $desde, $hasta);
    }

    public function reporteEquipos(?string $desde = null, ?string $hasta = null): array
    {
        $sql = 'SELECT e.*, CONCAT(c.nombres, " ", c.apellidos) AS cliente, c.ci, c.telefono,
                       (SELECT COUNT(*) FROM ordenes_servicio o WHERE o.id_equipo = e.id_equipo) AS total_ordenes
                FROM equipos_cliente e
                INNER JOIN clientes c ON c.id_cliente = e.id_cliente
                WHERE 1 = 1';
        $parametros = [];
        if ($desde !== null && $desde !== '') {
            $sql .= ' AND DATE(e.fecha_registro) >= :desde';
            $parametros[':desde'] = $desde;
        }
        if ($hasta !== null && $hasta !== '') {
            $sql .= ' AND DATE(e.fecha_registro) <= :hasta';
            $parametros[':hasta'] = $hasta;
        }
        $sql .= ' ORDER BY e.fecha_registro DESC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($parametros);
        return $stmt->fetchAll();
    }

    public function reporteGarantias(string $estado = '', string $tipo = '', string $busqueda = ''): array
    {
        return (new Garantia())->listar($estado, $tipo, $busqueda);
    }

    public function reporteInventario(int $idCategoria = 0, bool $soloBajo = false): array
    {
        return (new Producto())->listar('', $idCategoria, $soloBajo);
    }
}
