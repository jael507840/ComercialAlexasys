<?php
/**
 * Garantías de productos vendidos y de trabajos de servicio técnico.
 *
 * Restricción lógica: la garantía se origina en un detalle de venta
 * (tipo Producto) O en una orden de servicio (tipo Servicio), nunca en
 * ambas fuentes ni sin fuente. La vigencia se determina por fechas.
 */

class Garantia extends Model
{
    protected string $tabla = 'garantias';
    protected string $clavePrimaria = 'id_garantia';

    public function crear(
        ?int $idDetalleVenta,
        ?int $idOrden,
        string $fechaInicio,
        string $fechaFin,
        string $tipoGarantia,
        string $descripcion,
        ?string $condiciones
    ): int {
        $tieneVenta = $idDetalleVenta !== null && $idDetalleVenta > 0;
        $tieneServicio = $idOrden !== null && $idOrden > 0;

        if ($tieneVenta === $tieneServicio) {
            throw new DomainException('La garantía debe originarse en una venta o en una orden de servicio, no en ambas.');
        }
        if (!in_array($tipoGarantia, ['Producto', 'Servicio'], true)) {
            throw new DomainException('Tipo de garantía no válido.');
        }
        if ($tieneVenta && $tipoGarantia !== 'Producto') {
            throw new DomainException('Una garantía de venta debe ser de tipo Producto.');
        }
        if ($tieneServicio && $tipoGarantia !== 'Servicio') {
            throw new DomainException('Una garantía de orden de servicio debe ser de tipo Servicio.');
        }
        if (!fecha_valida($fechaInicio) || !fecha_valida($fechaFin)) {
            throw new DomainException('Las fechas de la garantía no son válidas.');
        }
        if ($fechaFin < $fechaInicio) {
            throw new DomainException('La fecha de vencimiento no puede ser anterior a la fecha de inicio.');
        }
        if (trim($descripcion) === '') {
            throw new DomainException('Debe describir el alcance de la garantía.');
        }

        if ($tieneVenta) {
            $stmt = $this->db->prepare('SELECT COUNT(*) FROM detalle_venta WHERE id_detalle_venta = :id');
            $stmt->execute([':id' => $idDetalleVenta]);
            if ((int) $stmt->fetchColumn() === 0) {
                throw new DomainException('El detalle de venta indicado no existe.');
            }
        } else {
            $stmt = $this->db->prepare('SELECT COUNT(*) FROM ordenes_servicio WHERE id_orden = :id');
            $stmt->execute([':id' => $idOrden]);
            if ((int) $stmt->fetchColumn() === 0) {
                throw new DomainException('La orden de servicio indicada no existe.');
            }
        }
        if ($this->existePorOrigen($tieneVenta ? (int) $idDetalleVenta : 0, $tieneServicio ? (int) $idOrden : 0)) {
            throw new DomainException('Este origen ya tiene una garantía registrada.');
        }

        return $this->insertar([
            'id_detalle_venta' => $tieneVenta ? $idDetalleVenta : null,
            'id_orden'         => $tieneServicio ? $idOrden : null,
            'fecha_inicio'     => $fechaInicio,
            'fecha_fin'        => $fechaFin,
            'tipo_garantia'    => $tipoGarantia,
            'descripcion'      => trim($descripcion),
            'condiciones'      => $condiciones !== null && trim($condiciones) !== '' ? trim($condiciones) : null,
            'estado'           => 'Vigente',
        ]);
    }

    public function existePorOrigen(int $idDetalleVenta, int $idOrden): bool
    {
        if (($idDetalleVenta > 0) === ($idOrden > 0)) {
            throw new DomainException('Indique exactamente un origen para la garantía.');
        }
        $columna = $idDetalleVenta > 0 ? 'id_detalle_venta' : 'id_orden';
        $id = $idDetalleVenta > 0 ? $idDetalleVenta : $idOrden;
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM garantias WHERE {$columna} = :id");
        $stmt->execute([':id' => $id]);
        return (int) $stmt->fetchColumn() > 0;
    }

    /** Marca como Vencidas las garantías cuya fecha_fin ya pasó. */
    public function sincronizarVencidas(): int
    {
        $stmt = $this->db->prepare(
            "UPDATE garantias SET estado = 'Vencida'
             WHERE estado = 'Vigente' AND fecha_fin < CURDATE()"
        );
        $stmt->execute();
        return $stmt->rowCount();
    }

    public function listar(string $estado = '', string $tipo = '', string $busqueda = ''): array
    {
        $sql = 'SELECT g.*,
                    CASE
                        WHEN g.estado = \'Anulada\' THEN \'Anulada\'
                        WHEN g.fecha_fin < CURDATE() THEN \'Vencida\'
                        ELSE \'Vigente\'
                    END AS vigencia,
                    dv.id_producto, p.codigo, p.nombre AS producto,
                    v.id_venta, v.numero_comprobante,
                    o.numero_orden, e.id_equipo, e.numero_serie, e.marca, e.modelo,
                    COALESCE(cv.id_cliente, co.id_cliente) AS id_cliente,
                    COALESCE(CONCAT(cv.nombres, " ", cv.apellidos), CONCAT(co.nombres, " ", co.apellidos)) AS cliente,
                    COALESCE(cv.ci, co.ci) AS ci,
                    COALESCE(cv.telefono, co.telefono) AS telefono
                FROM garantias g
                LEFT JOIN detalle_venta dv ON dv.id_detalle_venta = g.id_detalle_venta
                LEFT JOIN ventas v ON v.id_venta = dv.id_venta
                LEFT JOIN clientes cv ON cv.id_cliente = v.id_cliente
                LEFT JOIN productos p ON p.id_producto = dv.id_producto
                LEFT JOIN ordenes_servicio o ON o.id_orden = g.id_orden
                LEFT JOIN equipos_cliente e ON e.id_equipo = o.id_equipo
                LEFT JOIN clientes co ON co.id_cliente = e.id_cliente
                WHERE 1 = 1';
        $parametros = [];

        if ($busqueda !== '') {
            $sql .= ' AND (cv.ci LIKE :q1 OR cv.nombres LIKE :q2 OR cv.apellidos LIKE :q3
                        OR co.ci LIKE :q4 OR co.nombres LIKE :q5 OR co.apellidos LIKE :q6
                        OR e.numero_serie LIKE :q7 OR v.numero_comprobante LIKE :q8
                        OR o.numero_orden LIKE :q9 OR p.nombre LIKE :q10)';
            for ($i = 1; $i <= 10; $i++) {
                $parametros[':q' . $i] = '%' . $busqueda . '%';
            }
        }
        if ($tipo === 'Producto' || $tipo === 'Servicio') {
            $sql .= ' AND g.tipo_garantia = :tipo';
            $parametros[':tipo'] = $tipo;
        }
        if ($estado === 'Vigente') {
            $sql .= ' AND g.estado <> \'Anulada\' AND g.fecha_fin >= CURDATE()';
        } elseif ($estado === 'Vencida') {
            $sql .= ' AND g.estado <> \'Anulada\' AND g.fecha_fin < CURDATE()';
        } elseif ($estado === 'Anulada') {
            $sql .= ' AND g.estado = \'Anulada\'';
        }

        $sql .= ' ORDER BY g.fecha_fin DESC, g.id_garantia DESC LIMIT 300';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($parametros);
        return $stmt->fetchAll();
    }

    public function porIdConRelaciones(int $id): ?array
    {
        $filas = $this->listar();
        foreach ($filas as $fila) {
            if ((int) $fila['id_garantia'] === $id) {
                return $fila;
            }
        }
        return null;
    }

    public function porCliente(int $idCliente): array
    {
        $todas = $this->listar();
        return array_values(array_filter(
            $todas,
            static fn (array $g): bool => (int) ($g['id_cliente'] ?? 0) === $idCliente
        ));
    }

    public function porEquipo(int $idEquipo): array
    {
        $todas = $this->listar();
        return array_values(array_filter(
            $todas,
            static fn (array $g): bool => (int) ($g['id_equipo'] ?? 0) === $idEquipo
        ));
    }

    public function porSerie(string $serie): array
    {
        return $this->listar('', '', $serie);
    }

    public function porVenta(int $idVenta): array
    {
        $todas = $this->listar();
        return array_values(array_filter(
            $todas,
            static fn (array $g): bool => (int) ($g['id_venta'] ?? 0) === $idVenta
        ));
    }

    public function porOrden(int $idOrden): array
    {
        $todas = $this->listar();
        return array_values(array_filter(
            $todas,
            static fn (array $g): bool => (int) ($g['id_orden'] ?? 0) === $idOrden
        ));
    }

    /**
     * Consulta de verificación de garantía por datos del cliente,
     * número de serie, comprobante de venta u orden de servicio.
     */
    public function verificar(string $criterio): array
    {
        return $this->listar('', '', $criterio);
    }

    public function anular(int $id): bool
    {
        $garantia = $this->porId($id);
        if (!$garantia) {
            throw new DomainException('La garantía indicada no existe.');
        }
        if ($garantia['estado'] === 'Anulada') {
            throw new DomainException('La garantía ya se encuentra anulada.');
        }
        return $this->actualizarRegistro($id, ['estado' => 'Anulada']);
    }

    public function contarVigentes(): int
    {
        $stmt = $this->db->query(
            "SELECT COUNT(*) FROM garantias
             WHERE estado = 'Vigente' AND fecha_fin >= CURDATE()"
        );
        return (int) $stmt->fetchColumn();
    }

    public function proximasAVencer(int $dias = 30): array
    {
        $stmt = $this->db->prepare(
            "SELECT g.*, DATEDIFF(g.fecha_fin, CURDATE()) AS dias_restantes
             FROM garantias g
             WHERE g.estado = 'Vigente'
               AND g.fecha_fin BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL :dias DAY)
             ORDER BY g.fecha_fin"
        );
        $stmt->bindValue(':dias', $dias, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
