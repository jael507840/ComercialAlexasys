<?php
/**
 * Detalle de ventas: líneas vendidas y consultas auxiliares.
 */

class DetalleVenta extends Model
{
    protected string $tabla = 'detalle_venta';
    protected string $clavePrimaria = 'id_detalle_venta';

    public function porVenta(int $idVenta): array
    {
        return (new Venta())->detalle($idVenta);
    }

    public function porIdConProducto(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT dv.*, p.codigo, p.nombre, p.marca, p.modelo,
                    v.numero_comprobante, v.fecha_venta, v.id_cliente,
                    CONCAT(c.nombres, " ", c.apellidos) AS cliente, c.ci
             FROM detalle_venta dv
             INNER JOIN productos p ON p.id_producto = dv.id_producto
             INNER JOIN ventas v ON v.id_venta = dv.id_venta
             INNER JOIN clientes c ON c.id_cliente = v.id_cliente
             WHERE dv.id_detalle_venta = :id LIMIT 1'
        );
        $stmt->execute([':id' => $id]);
        $fila = $stmt->fetch();
        return $fila ?: null;
    }

    public function vendidosPorProducto(?string $desde = null, ?string $hasta = null): array
    {
        $sql = 'SELECT p.id_producto, p.codigo, p.nombre, SUM(dv.cantidad) AS unidades,
                       SUM(dv.subtotal) AS importe
                FROM detalle_venta dv
                INNER JOIN productos p ON p.id_producto = dv.id_producto
                INNER JOIN ventas v ON v.id_venta = dv.id_venta
                WHERE v.estado = \'Confirmada\'';
        $parametros = [];
        if ($desde !== null && $desde !== '') {
            $sql .= ' AND DATE(v.fecha_venta) >= :desde';
            $parametros[':desde'] = $desde;
        }
        if ($hasta !== null && $hasta !== '') {
            $sql .= ' AND DATE(v.fecha_venta) <= :hasta';
            $parametros[':hasta'] = $hasta;
        }
        $sql .= ' GROUP BY p.id_producto, p.codigo, p.nombre ORDER BY unidades DESC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($parametros);
        return $stmt->fetchAll();
    }
}
