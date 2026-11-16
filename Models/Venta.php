<?php
/**
 * Ventas de productos.
 * Valida stock, registra cabecera y detalle, genera salidas de
 * inventario y confirma toda la operación en una transacción.
 */

class Venta extends Model
{
    protected string $tabla = 'ventas';
    protected string $clavePrimaria = 'id_venta';

    /**
     * @param array<int, array{id_producto:int, cantidad:int, precio_unitario:float, descuento:float}> $items
     */
    public function registrar(
        int $idCliente,
        int $idUsuario,
        string $fechaVenta,
        ?string $numeroComprobante,
        float $descuentoGlobal,
        ?string $observacion,
        array $items
    ): int {
        if ($items === []) {
            throw new DomainException('La venta debe incluir al menos un producto.');
        }
        if ($descuentoGlobal < 0) {
            throw new DomainException('El descuento no puede ser negativo.');
        }

        $productoModelo = new Producto();
        $movimiento = new MovimientoInventario();
        $productosVistos = [];
        $lineas = [];
        $subtotalVenta = 0.0;

        foreach ($items as $item) {
            $idProducto = (int) ($item['id_producto'] ?? 0);
            $cantidad = (int) ($item['cantidad'] ?? 0);
            $precio = round((float) ($item['precio_unitario'] ?? 0), 2);
            $descuentoLinea = round((float) ($item['descuento'] ?? 0), 2);

            if ($idProducto <= 0 || $cantidad <= 0 || $precio < 0 || $descuentoLinea < 0) {
                throw new DomainException('Los datos de uno de los productos de la venta no son válidos.');
            }
            if (isset($productosVistos[$idProducto])) {
                throw new DomainException('No repita el mismo producto en la venta; consolide la cantidad.');
            }
            $productosVistos[$idProducto] = true;

            $producto = $productoModelo->porId($idProducto);
            if (!$producto || $producto['estado'] !== 'Activo') {
                throw new DomainException('Uno de los productos seleccionados no está disponible para la venta.');
            }
            if ($cantidad > (int) $producto['stock_actual']) {
                throw new DomainException(
                    'Stock insuficiente de "' . $producto['nombre'] . '". Disponible: '
                    . (int) $producto['stock_actual'] . '.'
                );
            }
            $brutoLinea = round($cantidad * $precio, 2);
            if ($descuentoLinea > $brutoLinea) {
                throw new DomainException('El descuento de una línea supera su importe bruto.');
            }
            $subtotalLinea = round($brutoLinea - $descuentoLinea, 2);
            $subtotalVenta += $subtotalLinea;
            $lineas[] = compact('idProducto', 'cantidad', 'precio', 'descuentoLinea', 'subtotalLinea');
        }

        $subtotalVenta = round($subtotalVenta, 2);
        if ($descuentoGlobal > $subtotalVenta) {
            throw new DomainException('El descuento general no puede superar el subtotal de la venta.');
        }
        $total = round($subtotalVenta - $descuentoGlobal, 2);

        try {
            $this->db->beginTransaction();

            $idVenta = $this->insertar([
                'id_cliente'         => $idCliente,
                'id_usuario'         => $idUsuario,
                'fecha_venta'        => $fechaVenta,
                'numero_comprobante' => 'TMP-' . bin2hex(random_bytes(6)),
                'subtotal'           => $subtotalVenta,
                'descuento'          => round($descuentoGlobal, 2),
                'total'              => $total,
                'observacion'        => $observacion !== null && trim($observacion) !== '' ? trim($observacion) : null,
                'estado'             => 'Confirmada',
            ]);

            $numero = $numeroComprobante !== null && trim($numeroComprobante) !== ''
                ? trim($numeroComprobante)
                : 'V-' . str_pad((string) $idVenta, 6, '0', STR_PAD_LEFT);

            foreach ($lineas as $linea) {
                $this->insertarDetalle(
                    $idVenta,
                    $linea['idProducto'],
                    $linea['cantidad'],
                    $linea['precio'],
                    $linea['descuentoLinea'],
                    $linea['subtotalLinea']
                );

                $movimiento->registrar(
                    $linea['idProducto'],
                    $idUsuario,
                    'Salida',
                    $linea['cantidad'],
                    'Venta',
                    $idVenta,
                    'Salida por venta ' . $numero
                );
            }

            $this->actualizarRegistro($idVenta, [
                'numero_comprobante' => $numero,
            ]);

            $this->db->commit();
            return $idVenta;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            if ($e instanceof PDOException && (string) $e->getCode() === '23000') {
                throw new DomainException('El número de comprobante de venta ya existe. Verifique el dato.', 0, $e);
            }
            throw $e;
        }
    }

    private function insertarDetalle(int $idVenta, int $idProducto, int $cantidad, float $precio, float $descuento, float $subtotal): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO detalle_venta (id_venta, id_producto, cantidad, precio_unitario, descuento, subtotal)
             VALUES (:venta, :producto, :cantidad, :precio, :descuento, :subtotal)'
        );
        $stmt->execute([
            ':venta'     => $idVenta,
            ':producto'  => $idProducto,
            ':cantidad'  => $cantidad,
            ':precio'    => $precio,
            ':descuento' => $descuento,
            ':subtotal'  => $subtotal,
        ]);
    }

    public function listar(?string $desde = null, ?string $hasta = null, string $busqueda = '', string $estado = ''): array
    {
        $sql = 'SELECT v.*, CONCAT(c.nombres, " ", c.apellidos) AS cliente, c.ci,
                       CONCAT(u.nombres, " ", u.apellidos) AS vendedor,
                       COALESCE(pg.pagado, 0) AS pagado,
                       (v.total - COALESCE(pg.pagado, 0)) AS saldo
                FROM ventas v
                INNER JOIN clientes c ON c.id_cliente = v.id_cliente
                INNER JOIN usuarios u ON u.id_usuario = v.id_usuario
                LEFT JOIN (
                    SELECT id_venta, SUM(monto) AS pagado
                    FROM pagos_venta WHERE estado = \'Confirmado\' GROUP BY id_venta
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
        if ($busqueda !== '') {
            $sql .= ' AND (v.numero_comprobante LIKE :q1 OR c.ci LIKE :q2 OR c.nombres LIKE :q3 OR c.apellidos LIKE :q4)';
            for ($i = 1; $i <= 4; $i++) {
                $parametros[':q' . $i] = '%' . $busqueda . '%';
            }
        }
        if (in_array($estado, ['Confirmada', 'Anulada'], true)) {
            $sql .= ' AND v.estado = :estado';
            $parametros[':estado'] = $estado;
        }
        $sql .= ' ORDER BY v.fecha_venta DESC, v.id_venta DESC LIMIT 300';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($parametros);
        return $stmt->fetchAll();
    }

    public function porIdConRelaciones(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT v.*, CONCAT(c.nombres, " ", c.apellidos) AS cliente, c.ci, c.telefono,
                    c.direccion, CONCAT(u.nombres, " ", u.apellidos) AS vendedor
             FROM ventas v
             INNER JOIN clientes c ON c.id_cliente = v.id_cliente
             INNER JOIN usuarios u ON u.id_usuario = v.id_usuario
             WHERE v.id_venta = :id LIMIT 1'
        );
        $stmt->execute([':id' => $id]);
        $fila = $stmt->fetch();
        return $fila ?: null;
    }

    public function detalle(int $id): array
    {
        $stmt = $this->db->prepare(
            'SELECT dv.*, p.codigo, p.nombre, p.marca, p.modelo,
                    (SELECT COUNT(*) FROM garantias g WHERE g.id_detalle_venta = dv.id_detalle_venta) AS tiene_garantia
             FROM detalle_venta dv
             INNER JOIN productos p ON p.id_producto = dv.id_producto
             WHERE dv.id_venta = :id
             ORDER BY dv.id_detalle_venta'
        );
        $stmt->execute([':id' => $id]);
        return $stmt->fetchAll();
    }

    public function totalPagado(int $id): float
    {
        $stmt = $this->db->prepare(
            "SELECT COALESCE(SUM(monto), 0) FROM pagos_venta
             WHERE id_venta = :id AND estado = 'Confirmado'"
        );
        $stmt->execute([':id' => $id]);
        return (float) $stmt->fetchColumn();
    }

    public function movimientos(int $id): array
    {
        $stmt = $this->db->prepare(
            'SELECT m.*, p.codigo, p.nombre AS producto,
                    CONCAT(u.nombres, " ", u.apellidos) AS usuario
             FROM movimientos_inventario m
             INNER JOIN productos p ON p.id_producto = m.id_producto
             INNER JOIN usuarios u ON u.id_usuario = m.id_usuario
             WHERE m.referencia_tipo = \'Venta\' AND m.referencia_id = :id
             ORDER BY m.id_movimiento'
        );
        $stmt->execute([':id' => $id]);
        return $stmt->fetchAll();
    }

    /** @return array{total:float, pagado:float, saldo:float, estado:string} */
    public function estadoPago(int $id): array
    {
        $venta = $this->porId($id);
        if (!$venta) {
            throw new DomainException('La venta indicada no existe.');
        }
        $total = (float) $venta['total'];
        $pagado = $this->totalPagado($id);
        $saldo = round($total - $pagado, 2);

        if ($venta['estado'] === 'Anulada') {
            $estado = 'Anulada';
        } elseif ($saldo <= 0.009) {
            $estado = 'Pagado';
        } elseif ($pagado > 0) {
            $estado = 'Parcial';
        } else {
            $estado = 'Pendiente';
        }

        return ['total' => $total, 'pagado' => $pagado, 'saldo' => $saldo, 'estado' => $estado];
    }

    public function porCliente(int $idCliente): array
    {
        $sql = 'SELECT v.*, CONCAT(c.nombres, " ", c.apellidos) AS cliente, c.ci,
                       COALESCE(pg.pagado, 0) AS pagado,
                       (v.total - COALESCE(pg.pagado, 0)) AS saldo
                FROM ventas v
                INNER JOIN clientes c ON c.id_cliente = v.id_cliente
                LEFT JOIN (
                    SELECT id_venta, SUM(monto) AS pagado
                    FROM pagos_venta WHERE estado = \'Confirmado\' GROUP BY id_venta
                ) pg ON pg.id_venta = v.id_venta
                WHERE v.id_cliente = :id
                ORDER BY v.fecha_venta DESC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $idCliente]);
        return $stmt->fetchAll();
    }

    /** Ventas confirmadas con saldo pendiente de cobro. */
    public function conSaldo(int $limite = 100): array
    {
        $limite = max(1, min(500, $limite));
        $sql = 'SELECT v.id_venta, v.numero_comprobante, v.fecha_venta, v.total,
                       CONCAT(c.nombres, " ", c.apellidos) AS cliente, c.ci,
                       COALESCE(pg.pagado, 0) AS pagado,
                       (v.total - COALESCE(pg.pagado, 0)) AS saldo
                FROM ventas v
                INNER JOIN clientes c ON c.id_cliente = v.id_cliente
                LEFT JOIN (
                    SELECT id_venta, SUM(monto) AS pagado
                    FROM pagos_venta WHERE estado = \'Confirmado\' GROUP BY id_venta
                ) pg ON pg.id_venta = v.id_venta
                WHERE v.estado = \'Confirmada\'
                HAVING saldo > 0.009
                ORDER BY v.fecha_venta ASC, v.id_venta ASC
                LIMIT ' . $limite;
        return $this->db->query($sql)->fetchAll();
    }

    public function resumen(?string $desde = null, ?string $hasta = null): array
    {
        $sql = "SELECT COUNT(*) AS total_ventas, COALESCE(SUM(total), 0) AS monto_total
                FROM ventas WHERE estado = 'Confirmada'";
        $parametros = [];
        if ($desde !== null && $desde !== '') {
            $sql .= ' AND DATE(fecha_venta) >= :desde';
            $parametros[':desde'] = $desde;
        }
        if ($hasta !== null && $hasta !== '') {
            $sql .= ' AND DATE(fecha_venta) <= :hasta';
            $parametros[':hasta'] = $hasta;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($parametros);
        return $stmt->fetch() ?: ['total_ventas' => 0, 'monto_total' => 0];
    }

    public function contarConfirmadas(): int
    {
        return $this->contar("estado = 'Confirmada'");
    }
}
