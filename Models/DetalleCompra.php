<?php
/**
 * Detalle de compras: consultas de líneas y costos de adquisición.
 */

class DetalleCompra extends Model
{
    protected string $tabla = 'detalle_compra';
    protected string $clavePrimaria = 'id_detalle_compra';

    public function porCompra(int $idCompra): array
    {
        return (new Compra())->detalle($idCompra);
    }

    public function porProducto(int $idProducto, int $limite = 20): array
    {
        $sql = 'SELECT dc.*, c.numero_comprobante, c.fecha_compra, p.nombre AS proveedor
                FROM detalle_compra dc
                INNER JOIN compras c ON c.id_compra = dc.id_compra
                INNER JOIN proveedores p ON p.id_proveedor = c.id_proveedor
                WHERE dc.id_producto = :id
                ORDER BY c.fecha_compra DESC, dc.id_detalle_compra DESC
                LIMIT ' . max(1, min(100, $limite));
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $idProducto]);
        return $stmt->fetchAll();
    }

    public function costoPromedio(int $idProducto): float
    {
        $stmt = $this->db->prepare(
            'SELECT COALESCE(SUM(subtotal) / NULLIF(SUM(cantidad), 0), 0)
             FROM detalle_compra WHERE id_producto = :id'
        );
        $stmt->execute([':id' => $idProducto]);
        return (float) $stmt->fetchColumn();
    }
}
