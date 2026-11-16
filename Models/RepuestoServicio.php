<?php
/**
 * Repuestos utilizados en el servicio técnico.
 * Cada repuesto consume inventario mediante un movimiento de Salida
 * dentro de la misma transacción.
 */

class RepuestoServicio extends Model
{
    protected string $tabla = 'repuestos_servicio';
    protected string $clavePrimaria = 'id_repuesto_servicio';

    public function registrar(int $idOrden, int $idProducto, int $cantidad, int $idUsuario): int
    {
        if ($cantidad <= 0) {
            throw new DomainException('La cantidad del repuesto debe ser mayor que cero.');
        }

        $ordenModelo = new OrdenServicio();
        $orden = $ordenModelo->porId($idOrden);
        if (!$orden) {
            throw new DomainException('La orden de servicio indicada no existe.');
        }
        if (in_array($orden['estado'], ['Entregado', 'Anulado'], true)) {
            throw new DomainException('No se pueden agregar repuestos a una orden entregada o anulada.');
        }

        $productoModelo = new Producto();
        $producto = $productoModelo->porId($idProducto);
        if (!$producto) {
            throw new DomainException('El producto seleccionado no existe.');
        }
        if ($producto['estado'] !== 'Activo') {
            throw new DomainException('El producto seleccionado está inactivo.');
        }

        $costo = $productoModelo->costoAdquisicion($idProducto);
        if ($costo === null) {
            // Sin compras registradas se usa el precio de venta como referencia.
            $costo = (float) $producto['precio_venta'];
        }
        $costo = round($costo, 2);
        $subtotal = round($cantidad * $costo, 2);

        $movimiento = new MovimientoInventario();
        try {
            $this->db->beginTransaction();
            $id = $this->insertar([
                'id_orden'       => $idOrden,
                'id_producto'    => $idProducto,
                'cantidad'       => $cantidad,
                'costo_unitario' => $costo,
                'subtotal'       => $subtotal,
            ]);
            $movimiento->registrar(
                $idProducto,
                $idUsuario,
                'Salida',
                $cantidad,
                'Repuesto',
                $id,
                'Repuesto utilizado en la orden ' . $orden['numero_orden']
            );
            $this->db->commit();
            return $id;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    public function porOrden(int $idOrden): array
    {
        $stmt = $this->db->prepare(
            'SELECT r.*, p.codigo, p.nombre, p.marca, p.modelo
             FROM repuestos_servicio r
             INNER JOIN productos p ON p.id_producto = r.id_producto
             WHERE r.id_orden = :id
             ORDER BY r.id_repuesto_servicio'
        );
        $stmt->execute([':id' => $idOrden]);
        return $stmt->fetchAll();
    }

    public function totalPorOrden(int $idOrden): float
    {
        $stmt = $this->db->prepare(
            'SELECT COALESCE(SUM(subtotal), 0) FROM repuestos_servicio WHERE id_orden = :id'
        );
        $stmt->execute([':id' => $idOrden]);
        return (float) $stmt->fetchColumn();
    }

    public function listar(?string $desde = null, ?string $hasta = null): array
    {
        $sql = 'SELECT r.*, p.codigo, p.nombre, o.numero_orden
                FROM repuestos_servicio r
                INNER JOIN productos p ON p.id_producto = r.id_producto
                INNER JOIN ordenes_servicio o ON o.id_orden = r.id_orden
                WHERE 1 = 1';
        $parametros = [];
        if ($desde !== null && $desde !== '') {
            $sql .= ' AND DATE(o.fecha_recepcion) >= :desde';
            $parametros[':desde'] = $desde;
        }
        if ($hasta !== null && $hasta !== '') {
            $sql .= ' AND DATE(o.fecha_recepcion) <= :hasta';
            $parametros[':hasta'] = $hasta;
        }
        $sql .= ' ORDER BY r.id_repuesto_servicio DESC LIMIT 200';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($parametros);
        return $stmt->fetchAll();
    }
}
