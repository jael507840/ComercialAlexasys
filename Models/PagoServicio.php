<?php
/**
 * Pagos de servicios técnicos.
 * Separados de pagos_venta. SALDO = TOTAL DE LA ORDEN - PAGOS CONFIRMADOS.
 */

class PagoServicio extends Model
{
    protected string $tabla = 'pagos_servicio';
    protected string $clavePrimaria = 'id_pago_servicio';

    public const METODOS = ['Efectivo', 'Transferencia', 'QR', 'Tarjeta'];

    public function registrar(
        int $idOrden,
        int $idUsuario,
        float $monto,
        string $metodoPago,
        ?string $referencia,
        ?string $observacion,
        ?string $fechaPago = null
    ): int {
        $monto = round($monto, 2);
        if ($monto <= 0) {
            throw new DomainException('El monto del pago debe ser mayor que cero.');
        }
        if (!in_array($metodoPago, self::METODOS, true)) {
            throw new DomainException('Método de pago no válido.');
        }

        $ordenModelo = new OrdenServicio();
        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare(
                'SELECT id_orden, estado, costo_servicio FROM ordenes_servicio
                 WHERE id_orden = :id FOR UPDATE'
            );
            $stmt->execute([':id' => $idOrden]);
            $orden = $stmt->fetch();
            if (!$orden) {
                throw new DomainException('La orden de servicio indicada no existe.');
            }
            if ($orden['estado'] === 'Anulado') {
                throw new DomainException('No se pueden registrar pagos de una orden anulada.');
            }

            $total = $ordenModelo->total($idOrden);
            $pagado = $ordenModelo->totalPagado($idOrden);
            $saldo = round($total - $pagado, 2);

            if ($total <= 0.009) {
                throw new DomainException('La orden no tiene un costo definido para cobrar.');
            }
            if ($saldo <= 0.009) {
                throw new DomainException('La orden ya se encuentra pagada en su totalidad.');
            }
            if ($monto > $saldo + 0.009) {
                throw new DomainException(
                    'El pago supera el saldo pendiente. Saldo actual: '
                    . number_format($saldo, 2, ',', '.') . '.'
                );
            }

            $id = $this->insertar([
                'id_orden'    => $idOrden,
                'id_usuario'  => $idUsuario,
                'fecha_pago'  => $fechaPago !== null && $fechaPago !== '' ? $fechaPago : date('Y-m-d H:i:s'),
                'monto'       => $monto,
                'metodo_pago' => $metodoPago,
                'referencia'  => $referencia !== null && trim($referencia) !== '' ? trim($referencia) : null,
                'observacion' => $observacion !== null && trim($observacion) !== '' ? trim($observacion) : null,
                'estado'      => 'Confirmado',
            ]);

            $this->db->commit();
            return $id;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    public function listar(int $idOrden = 0, ?string $desde = null, ?string $hasta = null): array
    {
        $sql = 'SELECT ps.*, o.numero_orden, o.costo_servicio,
                       CONCAT(c.nombres, " ", c.apellidos) AS cliente,
                       CONCAT(u.nombres, " ", u.apellidos) AS usuario
                FROM pagos_servicio ps
                INNER JOIN ordenes_servicio o ON o.id_orden = ps.id_orden
                INNER JOIN equipos_cliente e ON e.id_equipo = o.id_equipo
                INNER JOIN clientes c ON c.id_cliente = e.id_cliente
                INNER JOIN usuarios u ON u.id_usuario = ps.id_usuario
                WHERE 1 = 1';
        $parametros = [];
        if ($idOrden > 0) {
            $sql .= ' AND ps.id_orden = :orden';
            $parametros[':orden'] = $idOrden;
        }
        if ($desde !== null && $desde !== '') {
            $sql .= ' AND DATE(ps.fecha_pago) >= :desde';
            $parametros[':desde'] = $desde;
        }
        if ($hasta !== null && $hasta !== '') {
            $sql .= ' AND DATE(ps.fecha_pago) <= :hasta';
            $parametros[':hasta'] = $hasta;
        }
        $sql .= ' ORDER BY ps.fecha_pago DESC, ps.id_pago_servicio DESC LIMIT 300';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($parametros);
        return $stmt->fetchAll();
    }

    public function porOrden(int $idOrden): array
    {
        return $this->listar($idOrden);
    }

    public function totalConfirmado(int $idOrden): float
    {
        return (new OrdenServicio())->totalPagado($idOrden);
    }

    public function resumen(?string $desde = null, ?string $hasta = null): array
    {
        $sql = "SELECT COUNT(*) AS cantidad, COALESCE(SUM(monto), 0) AS total
                FROM pagos_servicio WHERE estado = 'Confirmado'";
        $parametros = [];
        if ($desde !== null && $desde !== '') {
            $sql .= ' AND DATE(fecha_pago) >= :desde';
            $parametros[':desde'] = $desde;
        }
        if ($hasta !== null && $hasta !== '') {
            $sql .= ' AND DATE(fecha_pago) <= :hasta';
            $parametros[':hasta'] = $hasta;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($parametros);
        return $stmt->fetch() ?: ['cantidad' => 0, 'total' => 0];
    }
}
