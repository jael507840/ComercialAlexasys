<?php
/**
 * Compras de mercadería.
 * Toda compra genera entradas de inventario y actualiza el stock
 * dentro de una única transacción.
 */

class Compra extends Model
{
    protected string $tabla = 'compras';
    protected string $clavePrimaria = 'id_compra';

    /**
     * @param array<int, array{id_producto:int, cantidad:int, costo_unitario:float}> $items
     * @return int id de la compra registrada
     */
    public function registrar(
        int $idProveedor,
        int $idUsuario,
        string $fechaCompra,
        ?string $numeroComprobante,
        ?string $observacion,
        array $items
    ): int {
        if ($items === []) {
            throw new DomainException('La compra debe incluir al menos un producto.');
        }

        $productoModelo = new Producto();
        $movimiento = new MovimientoInventario();
        $productosVistos = [];
        $total = 0.0;

        try {
            $this->db->beginTransaction();

            $idCompra = $this->insertar([
                'id_proveedor'       => $idProveedor,
                'id_usuario'         => $idUsuario,
                'numero_comprobante' => 'TMP-' . bin2hex(random_bytes(6)),
                'fecha_compra'       => $fechaCompra,
                'total_compra'       => 0,
                'observacion'        => $observacion !== null && trim($observacion) !== '' ? trim($observacion) : null,
                'estado'             => 'Registrada',
            ]);

            foreach ($items as $item) {
                $idProducto = (int) ($item['id_producto'] ?? 0);
                $cantidad = (int) ($item['cantidad'] ?? 0);
                $costo = round((float) ($item['costo_unitario'] ?? 0), 2);

                if ($idProducto <= 0 || $cantidad <= 0 || $costo < 0) {
                    throw new DomainException('Los datos de uno de los productos de la compra no son válidos.');
                }
                if (isset($productosVistos[$idProducto])) {
                    throw new DomainException('No repita el mismo producto en la compra; consolide la cantidad.');
                }
                $productosVistos[$idProducto] = true;

                $producto = $productoModelo->porId($idProducto);
                if (!$producto) {
                    throw new DomainException('Uno de los productos seleccionados no existe.');
                }

                $subtotal = round($cantidad * $costo, 2);
                $total += $subtotal;

                $this->insertarDetalle($idCompra, $idProducto, $cantidad, $costo, $subtotal);

                $movimiento->registrar(
                    $idProducto,
                    $idUsuario,
                    'Entrada',
                    $cantidad,
                    'Compra',
                    $idCompra,
                    'Ingreso por compra ' . ($numeroComprobante ?: ('#' . $idCompra))
                );
            }

            $numero = $numeroComprobante !== null && trim($numeroComprobante) !== ''
                ? trim($numeroComprobante)
                : 'C-' . str_pad((string) $idCompra, 6, '0', STR_PAD_LEFT);

            $this->actualizarRegistro($idCompra, [
                'numero_comprobante' => $numero,
                'total_compra'       => round($total, 2),
            ]);

            $this->db->commit();
            return $idCompra;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            if ((string) $e->getCode() === '23000' && $e instanceof PDOException) {
                throw new DomainException('El número de comprobante ya existe. Verifique el dato.', 0, $e);
            }
            throw $e;
        }
    }

    private function insertarDetalle(int $idCompra, int $idProducto, int $cantidad, float $costo, float $subtotal): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO detalle_compra (id_compra, id_producto, cantidad, costo_unitario, subtotal)
             VALUES (:compra, :producto, :cantidad, :costo, :subtotal)'
        );
        $stmt->execute([
            ':compra'   => $idCompra,
            ':producto' => $idProducto,
            ':cantidad' => $cantidad,
            ':costo'    => $costo,
            ':subtotal' => $subtotal,
        ]);
    }

    public function listar(?string $desde = null, ?string $hasta = null, int $idProveedor = 0, string $busqueda = ''): array
    {
        $sql = 'SELECT c.*, p.nombre AS proveedor,
                       CONCAT(u.nombres, " ", u.apellidos) AS usuario,
                       (SELECT COUNT(*) FROM detalle_compra dc WHERE dc.id_compra = c.id_compra) AS total_items
                FROM compras c
                INNER JOIN proveedores p ON p.id_proveedor = c.id_proveedor
                INNER JOIN usuarios u ON u.id_usuario = c.id_usuario
                WHERE 1 = 1';
        $parametros = [];
        if ($desde !== null && $desde !== '') {
            $sql .= ' AND DATE(c.fecha_compra) >= :desde';
            $parametros[':desde'] = $desde;
        }
        if ($hasta !== null && $hasta !== '') {
            $sql .= ' AND DATE(c.fecha_compra) <= :hasta';
            $parametros[':hasta'] = $hasta;
        }
        if ($idProveedor > 0) {
            $sql .= ' AND c.id_proveedor = :proveedor';
            $parametros[':proveedor'] = $idProveedor;
        }
        if ($busqueda !== '') {
            $sql .= ' AND (c.numero_comprobante LIKE :q1 OR p.nombre LIKE :q2)';
            $parametros[':q1'] = '%' . $busqueda . '%';
            $parametros[':q2'] = '%' . $busqueda . '%';
        }
        $sql .= ' ORDER BY c.fecha_compra DESC, c.id_compra DESC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($parametros);
        return $stmt->fetchAll();
    }

    public function porIdConRelaciones(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT c.*, p.nombre AS proveedor, p.nit, p.telefono,
                    CONCAT(u.nombres, " ", u.apellidos) AS usuario
             FROM compras c
             INNER JOIN proveedores p ON p.id_proveedor = c.id_proveedor
             INNER JOIN usuarios u ON u.id_usuario = c.id_usuario
             WHERE c.id_compra = :id LIMIT 1'
        );
        $stmt->execute([':id' => $id]);
        $fila = $stmt->fetch();
        return $fila ?: null;
    }

    public function detalle(int $id): array
    {
        $stmt = $this->db->prepare(
            'SELECT dc.*, p.codigo, p.nombre, p.marca, p.modelo
             FROM detalle_compra dc
             INNER JOIN productos p ON p.id_producto = dc.id_producto
             WHERE dc.id_compra = :id
             ORDER BY dc.id_detalle_compra'
        );
        $stmt->execute([':id' => $id]);
        return $stmt->fetchAll();
    }

    public function movimientos(int $id): array
    {
        $stmt = $this->db->prepare(
            'SELECT m.*, p.codigo, p.nombre AS producto,
                    CONCAT(u.nombres, " ", u.apellidos) AS usuario
             FROM movimientos_inventario m
             INNER JOIN productos p ON p.id_producto = m.id_producto
             INNER JOIN usuarios u ON u.id_usuario = m.id_usuario
             WHERE m.referencia_tipo = \'Compra\' AND m.referencia_id = :id
             ORDER BY m.id_movimiento'
        );
        $stmt->execute([':id' => $id]);
        return $stmt->fetchAll();
    }

    public function totalPeriodo(?string $desde = null, ?string $hasta = null): float
    {
        $sql = "SELECT COALESCE(SUM(total_compra), 0) FROM compras WHERE estado = 'Registrada'";
        $parametros = [];
        if ($desde !== null && $desde !== '') {
            $sql .= ' AND DATE(fecha_compra) >= :desde';
            $parametros[':desde'] = $desde;
        }
        if ($hasta !== null && $hasta !== '') {
            $sql .= ' AND DATE(fecha_compra) <= :hasta';
            $parametros[':hasta'] = $hasta;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($parametros);
        return (float) $stmt->fetchColumn();
    }

    public function contarPeriodo(?string $desde = null, ?string $hasta = null): int
    {
        $sql = "SELECT COUNT(*) FROM compras WHERE estado = 'Registrada'";
        $parametros = [];
        if ($desde !== null && $desde !== '') {
            $sql .= ' AND DATE(fecha_compra) >= :desde';
            $parametros[':desde'] = $desde;
        }
        if ($hasta !== null && $hasta !== '') {
            $sql .= ' AND DATE(fecha_compra) <= :hasta';
            $parametros[':hasta'] = $hasta;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($parametros);
        return (int) $stmt->fetchColumn();
    }
}
