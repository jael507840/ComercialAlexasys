<?php
/**
 * Movimientos de inventario.
 *
 * Regla: toda variación de stock genera un movimiento.
 * referencia_tipo + referencia_id es una referencia polimórfica al
 * documento origen (Compra, Venta, Repuesto, Ajuste, Apertura).
 * El índice único uq_mov_ref impide registrar dos veces el mismo
 * documento para el mismo producto (protección ante doble envío).
 *
 * Para los ajustes manuales se registra tipo Entrada o Salida con
 * referencia_tipo = 'Ajuste', de modo que el stock siempre se pueda
 * verificar como SUM(Entradas) - SUM(Salidas).
 */

class MovimientoInventario extends Model
{
    protected string $tabla = 'movimientos_inventario';
    protected string $clavePrimaria = 'id_movimiento';

    public const TIPOS = ['Entrada', 'Salida'];
    public const REFERENCIAS = ['Compra', 'Venta', 'Repuesto', 'Ajuste', 'Apertura'];

    /**
     * Registra el movimiento y actualiza el stock.
     * Debe invocarse dentro de una transacción abierta por el proceso
     * que lo origina (compra, venta, repuesto o ajuste).
     */
    public function registrar(
        int $idProducto,
        int $idUsuario,
        string $tipo,
        int $cantidad,
        string $referenciaTipo,
        ?int $referenciaId,
        string $observacion = ''
    ): int {
        if (!in_array($tipo, self::TIPOS, true)) {
            throw new DomainException('Tipo de movimiento no válido.');
        }
        if (!in_array($referenciaTipo, self::REFERENCIAS, true)) {
            throw new DomainException('Tipo de referencia de inventario no válido.');
        }
        if ($cantidad <= 0) {
            throw new DomainException('La cantidad del movimiento debe ser mayor que cero.');
        }

        $stmt = $this->db->prepare(
            'SELECT id_producto, nombre, stock_actual FROM productos WHERE id_producto = :id FOR UPDATE'
        );
        $stmt->execute([':id' => $idProducto]);
        $producto = $stmt->fetch();
        if (!$producto) {
            throw new DomainException('El producto indicado no existe.');
        }

        $stock = (int) $producto['stock_actual'];
        if ($tipo === 'Salida' && $stock < $cantidad) {
            throw new DomainException(
                'Stock insuficiente de "' . $producto['nombre'] . '". Disponible: ' . $stock
                . ', solicitado: ' . $cantidad . '.'
            );
        }

        $nuevoStock = $tipo === 'Entrada' ? $stock + $cantidad : $stock - $cantidad;
        $actualizar = $this->db->prepare(
            'UPDATE productos SET stock_actual = :stock WHERE id_producto = :id'
        );
        $actualizar->execute([':stock' => $nuevoStock, ':id' => $idProducto]);

        try {
            return $this->insertar([
                'id_producto'     => $idProducto,
                'id_usuario'      => $idUsuario,
                'fecha_movimiento' => date('Y-m-d H:i:s'),
                'tipo_movimiento' => $tipo,
                'cantidad'        => $cantidad,
                'referencia_tipo' => $referenciaTipo,
                'referencia_id'   => $referenciaId,
                'observacion'     => $observacion !== '' ? $observacion : null,
            ]);
        } catch (PDOException $e) {
            if ((string) $e->getCode() === '23000') {
                throw new DomainException(
                    'El movimiento ya fue registrado para este documento y producto.',
                    0,
                    $e
                );
            }
            throw $e;
        }
    }

    /** Ajuste manual: fija el stock a un nuevo valor y registra el movimiento. */
    public function ajustar(int $idProducto, int $nuevoStock, int $idUsuario, string $observacion): void
    {
        if ($nuevoStock < 0) {
            throw new DomainException('El stock no puede ser negativo.');
        }
        if (trim($observacion) === '') {
            throw new DomainException('Debe indicar el motivo del ajuste de inventario.');
        }

        try {
            $this->db->beginTransaction();
            $stmt = $this->db->prepare(
                'SELECT stock_actual, nombre FROM productos WHERE id_producto = :id FOR UPDATE'
            );
            $stmt->execute([':id' => $idProducto]);
            $producto = $stmt->fetch();
            if (!$producto) {
                throw new DomainException('El producto indicado no existe.');
            }
            $actual = (int) $producto['stock_actual'];
            $diferencia = $nuevoStock - $actual;

            if ($diferencia === 0) {
                throw new DomainException('El nuevo stock es igual al stock actual; no se generó movimiento.');
            }

            $this->registrar(
                $idProducto,
                $idUsuario,
                $diferencia > 0 ? 'Entrada' : 'Salida',
                abs($diferencia),
                'Ajuste',
                null,
                'Ajuste de ' . $actual . ' a ' . $nuevoStock . '. ' . $observacion
            );
            $this->db->commit();
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    public function listar(int $idProducto = 0, string $tipo = '', string $referencia = '', ?string $desde = null, ?string $hasta = null): array
    {
        $sql = 'SELECT m.*, p.codigo, p.nombre AS producto,
                       CONCAT(u.nombres, " ", u.apellidos) AS usuario
                FROM movimientos_inventario m
                INNER JOIN productos p ON p.id_producto = m.id_producto
                INNER JOIN usuarios u ON u.id_usuario = m.id_usuario
                WHERE 1 = 1';
        $parametros = [];
        if ($idProducto > 0) {
            $sql .= ' AND m.id_producto = :producto';
            $parametros[':producto'] = $idProducto;
        }
        if (in_array($tipo, ['Entrada', 'Salida', 'Ajuste'], true)) {
            $sql .= ' AND m.tipo_movimiento = :tipo';
            $parametros[':tipo'] = $tipo;
        }
        if ($referencia !== '') {
            $sql .= ' AND m.referencia_tipo = :referencia';
            $parametros[':referencia'] = $referencia;
        }
        if ($desde !== null && $desde !== '') {
            $sql .= ' AND DATE(m.fecha_movimiento) >= :desde';
            $parametros[':desde'] = $desde;
        }
        if ($hasta !== null && $hasta !== '') {
            $sql .= ' AND DATE(m.fecha_movimiento) <= :hasta';
            $parametros[':hasta'] = $hasta;
        }
        $sql .= ' ORDER BY m.fecha_movimiento DESC, m.id_movimiento DESC LIMIT 300';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($parametros);
        return $stmt->fetchAll();
    }

    public function porProducto(int $idProducto): array
    {
        return $this->listar($idProducto);
    }

    /** Compara el stock almacenado con la suma de movimientos de cada producto. */
    public function verificacionStock(): array
    {
        $sql = 'SELECT p.id_producto, p.codigo, p.nombre, p.stock_actual,
                       COALESCE(SUM(CASE WHEN m.tipo_movimiento = \'Entrada\' THEN m.cantidad ELSE 0 END), 0) AS entradas,
                       COALESCE(SUM(CASE WHEN m.tipo_movimiento = \'Salida\' THEN m.cantidad ELSE 0 END), 0) AS salidas,
                       CAST(p.stock_actual AS SIGNED)
                         - (CAST(COALESCE(SUM(CASE WHEN m.tipo_movimiento = \'Entrada\' THEN m.cantidad ELSE 0 END), 0) AS SIGNED)
                          - CAST(COALESCE(SUM(CASE WHEN m.tipo_movimiento = \'Salida\' THEN m.cantidad ELSE 0 END), 0) AS SIGNED)) AS diferencia
                FROM productos p
                LEFT JOIN movimientos_inventario m ON m.id_producto = p.id_producto
                GROUP BY p.id_producto, p.codigo, p.nombre, p.stock_actual
                ORDER BY ABS(CAST(p.stock_actual AS SIGNED)
                          - (CAST(COALESCE(SUM(CASE WHEN m.tipo_movimiento = \'Entrada\' THEN m.cantidad ELSE 0 END), 0) AS SIGNED)
                           - CAST(COALESCE(SUM(CASE WHEN m.tipo_movimiento = \'Salida\' THEN m.cantidad ELSE 0 END), 0) AS SIGNED))) DESC,
                         p.nombre';
        return $this->db->query($sql)->fetchAll();
    }

    public function resumen(?string $desde = null, ?string $hasta = null): array
    {
        $sql = 'SELECT
                    COALESCE(SUM(CASE WHEN tipo_movimiento = \'Entrada\' THEN cantidad ELSE 0 END), 0) AS entradas,
                    COALESCE(SUM(CASE WHEN tipo_movimiento = \'Salida\' THEN cantidad ELSE 0 END), 0) AS salidas,
                    COUNT(*) AS total_movimientos
                FROM movimientos_inventario WHERE 1 = 1';
        $parametros = [];
        if ($desde !== null && $desde !== '') {
            $sql .= ' AND DATE(fecha_movimiento) >= :desde';
            $parametros[':desde'] = $desde;
        }
        if ($hasta !== null && $hasta !== '') {
            $sql .= ' AND DATE(fecha_movimiento) <= :hasta';
            $parametros[':hasta'] = $hasta;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($parametros);
        return $stmt->fetch() ?: [];
    }
}
