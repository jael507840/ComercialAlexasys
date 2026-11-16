<?php
/**
 * Pagos de ventas.
 * Se mantienen separados de los pagos de servicio técnico.
 * SALDO = TOTAL - SUMA DE PAGOS CONFIRMADOS.
 * No se aceptan pagos mayores al saldo ni montos negativos.
 */

class PagoVenta extends Model
{
    protected string $tabla = 'pagos_venta';
    protected string $clavePrimaria = 'id_pago_venta';

    public const METODOS = ['Efectivo', 'Transferencia', 'QR', 'Tarjeta'];

    public function registrar(
        int $idVenta,
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

        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare(
                'SELECT id_venta, total, estado FROM ventas WHERE id_venta = :id FOR UPDATE'
            );
            $stmt->execute([':id' => $idVenta]);
            $venta = $stmt->fetch();
            if (!$venta) {
                throw new DomainException('La venta indicada no existe.');
            }
            if ($venta['estado'] !== 'Confirmada') {
                throw new DomainException('No se pueden registrar pagos de una venta anulada.');
            }

            $stmtPagos = $this->db->prepare(
                "SELECT COALESCE(SUM(monto), 0) FROM pagos_venta
                 WHERE id_venta = :id AND estado = 'Confirmado'"
            );
            $stmtPagos->execute([':id' => $idVenta]);
            $pagado = (float) $stmtPagos->fetchColumn();
            $saldo = round((float) $venta['total'] - $pagado, 2);

            if ($saldo <= 0.009) {
                throw new DomainException('La venta ya se encuentra pagada en su totalidad.');
            }
            if ($monto > $saldo + 0.009) {
                throw new DomainException(
                    'El pago supera el saldo pendiente. Saldo actual: '
                    . number_format($saldo, 2, ',', '.') . '.'
                );
            }

            $id = $this->insertar([
                'id_venta'    => $idVenta,
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

    public function listar(int $idVenta = 0, ?string $desde = null, ?string $hasta = null): array
    {
        $sql = 'SELECT pv.*, v.numero_comprobante, v.total AS total_venta,
                       CONCAT(c.nombres, " ", c.apellidos) AS cliente,
                       CONCAT(u.nombres, " ", u.apellidos) AS usuario
                FROM pagos_venta pv
                INNER JOIN ventas v ON v.id_venta = pv.id_venta
                INNER JOIN clientes c ON c.id_cliente = v.id_cliente
                INNER JOIN usuarios u ON u.id_usuario = pv.id_usuario
                WHERE 1 = 1';
        $parametros = [];
        if ($idVenta > 0) {
            $sql .= ' AND pv.id_venta = :venta';
            $parametros[':venta'] = $idVenta;
        }
        if ($desde !== null && $desde !== '') {
            $sql .= ' AND DATE(pv.fecha_pago) >= :desde';
            $parametros[':desde'] = $desde;
        }
        if ($hasta !== null && $hasta !== '') {
            $sql .= ' AND DATE(pv.fecha_pago) <= :hasta';
            $parametros[':hasta'] = $hasta;
        }
        $sql .= ' ORDER BY pv.fecha_pago DESC, pv.id_pago_venta DESC LIMIT 300';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($parametros);
        return $stmt->fetchAll();
    }

    public function porVenta(int $idVenta): array
    {
        return $this->listar($idVenta);
    }

    public function totalConfirmado(int $idVenta): float
    {
        return (new Venta())->totalPagado($idVenta);
    }

    public function resumen(?string $desde = null, ?string $hasta = null): array
    {
        $sql = "SELECT COUNT(*) AS cantidad, COALESCE(SUM(monto), 0) AS total
                FROM pagos_venta WHERE estado = 'Confirmado'";
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
