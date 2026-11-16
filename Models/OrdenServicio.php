<?php
/**
 * Órdenes de servicio técnico.
 *
 * Regla de costos:
 *  - costo_servicio = mano de obra facturada.
 *  - Si la orden tiene intervenciones registradas, costo_servicio se
 *    recalcula como la suma de detalle_servicio.costo para evitar
 *    duplicar importes. Si no tiene intervenciones, puede editarse
 *    manualmente (por ejemplo, un mantenimiento simple).
 *  - Total de la orden = costo_servicio + repuestos_servicio.subtotal.
 */

class OrdenServicio extends Model
{
    protected string $tabla = 'ordenes_servicio';
    protected string $clavePrimaria = 'id_orden';

    public const ESTADOS = [
        'Recibido', 'En diagnostico', 'En reparacion',
        'Esperando repuesto', 'Terminado', 'Entregado', 'Anulado',
    ];

    public function crear(
        int $idEquipo,
        int $idUsuarioRecepcion,
        ?int $idUsuarioTecnico,
        string $fallaReportada,
        ?string $accesorios,
        string $fechaRecepcion,
        float $costoServicio,
        ?string $observaciones
    ): int {
        if (trim($fallaReportada) === '') {
            throw new DomainException('Debe registrar la falla reportada por el cliente.');
        }
        if ($costoServicio < 0) {
            throw new DomainException('El costo de servicio no puede ser negativo.');
        }
        if ($idUsuarioTecnico !== null) {
            $this->validarTecnico($idUsuarioTecnico);
        }

        try {
            $this->db->beginTransaction();

            $idOrden = $this->insertar([
                'id_equipo'            => $idEquipo,
                'id_usuario_recepcion' => $idUsuarioRecepcion,
                'id_usuario_tecnico'   => $idUsuarioTecnico,
                'numero_orden'         => 'TMP-' . bin2hex(random_bytes(6)),
                'fecha_recepcion'      => $fechaRecepcion,
                'fecha_entrega'        => null,
                'falla_reportada'      => trim($fallaReportada),
                'accesorios_recibidos' => $accesorios !== null && trim($accesorios) !== '' ? trim($accesorios) : null,
                'estado'               => 'Recibido',
                'costo_servicio'       => round($costoServicio, 2),
                'observaciones'        => $observaciones !== null && trim($observaciones) !== '' ? trim($observaciones) : null,
            ]);

            $this->actualizarRegistro($idOrden, [
                'numero_orden' => 'OS-' . str_pad((string) $idOrden, 6, '0', STR_PAD_LEFT),
            ]);

            $this->db->commit();
            return $idOrden;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    private function validarTecnico(int $idTecnico): void
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM usuarios u
             INNER JOIN usuario_rol ur ON ur.id_usuario = u.id_usuario
             INNER JOIN roles r ON r.id_rol = ur.id_rol
             WHERE u.id_usuario = :id AND u.estado = 'Activo' AND r.nombre = 'Tecnico'"
        );
        $stmt->execute([':id' => $idTecnico]);
        if ((int) $stmt->fetchColumn() === 0) {
            throw new DomainException('El técnico seleccionado no existe o no está activo.');
        }
    }

    public function listar(
        string $busqueda = '',
        string $estado = '',
        int $idTecnico = 0,
        ?string $desde = null,
        ?string $hasta = null
    ): array {
        $sql = $this->consultaBase() . ' WHERE 1 = 1';
        $parametros = [];
        if ($busqueda !== '') {
            $sql .= ' AND (o.numero_orden LIKE :q1 OR c.ci LIKE :q2 OR c.nombres LIKE :q3
                        OR c.apellidos LIKE :q4 OR e.numero_serie LIKE :q5 OR e.marca LIKE :q6)';
            for ($i = 1; $i <= 6; $i++) {
                $parametros[':q' . $i] = '%' . $busqueda . '%';
            }
        }
        if (in_array($estado, self::ESTADOS, true)) {
            $sql .= ' AND o.estado = :estado';
            $parametros[':estado'] = $estado;
        }
        if ($idTecnico > 0) {
            $sql .= ' AND o.id_usuario_tecnico = :tecnico';
            $parametros[':tecnico'] = $idTecnico;
        }
        if ($desde !== null && $desde !== '') {
            $sql .= ' AND DATE(o.fecha_recepcion) >= :desde';
            $parametros[':desde'] = $desde;
        }
        if ($hasta !== null && $hasta !== '') {
            $sql .= ' AND DATE(o.fecha_recepcion) <= :hasta';
            $parametros[':hasta'] = $hasta;
        }
        $sql .= ' ORDER BY o.fecha_recepcion DESC, o.id_orden DESC LIMIT 300';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($parametros);
        return $stmt->fetchAll();
    }

    private function consultaBase(): string
    {
        return 'SELECT o.*, e.marca, e.modelo, e.numero_serie, e.tipo_equipo, e.id_cliente,
                       CONCAT(c.nombres, " ", c.apellidos) AS cliente, c.ci, c.telefono,
                       CONCAT(ur.nombres, " ", ur.apellidos) AS receptor,
                       CONCAT(ut.nombres, " ", ut.apellidos) AS tecnico,
                       COALESCE(r.repuestos_total, 0) AS repuestos_total,
                       (o.costo_servicio + COALESCE(r.repuestos_total, 0)) AS total,
                       COALESCE(pg.pagado, 0) AS pagado,
                       (o.costo_servicio + COALESCE(r.repuestos_total, 0) - COALESCE(pg.pagado, 0)) AS saldo
                FROM ordenes_servicio o
                INNER JOIN equipos_cliente e ON e.id_equipo = o.id_equipo
                INNER JOIN clientes c ON c.id_cliente = e.id_cliente
                INNER JOIN usuarios ur ON ur.id_usuario = o.id_usuario_recepcion
                LEFT JOIN usuarios ut ON ut.id_usuario = o.id_usuario_tecnico
                LEFT JOIN (
                    SELECT id_orden, SUM(subtotal) AS repuestos_total
                    FROM repuestos_servicio GROUP BY id_orden
                ) r ON r.id_orden = o.id_orden
                LEFT JOIN (
                    SELECT id_orden, SUM(monto) AS pagado
                    FROM pagos_servicio WHERE estado = \'Confirmado\' GROUP BY id_orden
                ) pg ON pg.id_orden = o.id_orden';
    }

    public function porIdConRelaciones(int $id): ?array
    {
        $stmt = $this->db->prepare($this->consultaBase() . ' WHERE o.id_orden = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $fila = $stmt->fetch();
        return $fila ?: null;
    }

    public function actualizar(int $id, array $datos): bool
    {
        $orden = $this->porId($id);
        if (!$orden) {
            throw new DomainException('La orden indicada no existe.');
        }

        $estado = (string) ($datos['estado'] ?? $orden['estado']);
        if (!in_array($estado, self::ESTADOS, true)) {
            throw new DomainException('Estado de orden no válido.');
        }

        $idTecnico = $datos['id_usuario_tecnico'] ?? null;
        $idTecnico = $idTecnico !== null && (int) $idTecnico > 0 ? (int) $idTecnico : null;
        if ($idTecnico !== null) {
            $this->validarTecnico($idTecnico);
        }

        $stmt = $this->db->prepare('SELECT COUNT(*) FROM detalle_servicio WHERE id_orden = :id');
        $stmt->execute([':id' => $id]);
        $tieneIntervenciones = (int) $stmt->fetchColumn() > 0;

        $costo = $orden['costo_servicio'];
        if (!$tieneIntervenciones && isset($datos['costo_servicio'])) {
            $costo = round((float) $datos['costo_servicio'], 2);
            if ($costo < 0) {
                throw new DomainException('El costo de servicio no puede ser negativo.');
            }
        }

        $fechaEntrega = $estado === 'Entregado'
            ? ($datos['fecha_entrega'] ?? null) ?: date('Y-m-d H:i:s')
            : null;
        if ($estado === 'Entregado' && !empty($datos['fecha_entrega'])) {
            $fechaEntrega = $datos['fecha_entrega'];
        }

        return $this->actualizarRegistro($id, [
            'id_usuario_tecnico' => $idTecnico,
            'estado'             => $estado,
            'fecha_entrega'      => $fechaEntrega,
            'costo_servicio'     => $costo,
            'observaciones'      => isset($datos['observaciones'])
                ? (trim((string) $datos['observaciones']) !== '' ? trim((string) $datos['observaciones']) : null)
                : $orden['observaciones'],
        ]);
    }

    public function cambiarEstado(int $id, string $estado): bool
    {
        return $this->actualizar($id, ['estado' => $estado]);
    }

    /** Recalcula la mano de obra a partir de las intervenciones registradas. */
    public function recalcularCosto(int $id): void
    {
        $stmt = $this->db->prepare(
            'UPDATE ordenes_servicio
             SET costo_servicio = (
                SELECT COALESCE(SUM(costo), 0) FROM detalle_servicio WHERE id_orden = :id
             )
             WHERE id_orden = :id2'
        );
        $stmt->execute([':id' => $id, ':id2' => $id]);
    }

    public function total(int $id): float
    {
        $stmt = $this->db->prepare(
            'SELECT o.costo_servicio + COALESCE(SUM(r.subtotal), 0)
             FROM ordenes_servicio o
             LEFT JOIN repuestos_servicio r ON r.id_orden = o.id_orden
             WHERE o.id_orden = :id
             GROUP BY o.id_orden'
        );
        $stmt->execute([':id' => $id]);
        return (float) $stmt->fetchColumn();
    }

    public function totalPagado(int $id): float
    {
        $stmt = $this->db->prepare(
            "SELECT COALESCE(SUM(monto), 0) FROM pagos_servicio
             WHERE id_orden = :id AND estado = 'Confirmado'"
        );
        $stmt->execute([':id' => $id]);
        return (float) $stmt->fetchColumn();
    }

    /** @return array{total:float, pagado:float, saldo:float, estado:string} */
    public function estadoPago(int $id): array
    {
        $orden = $this->porId($id);
        if (!$orden) {
            throw new DomainException('La orden indicada no existe.');
        }
        $total = round($this->total($id), 2);
        $pagado = $this->totalPagado($id);
        $saldo = round($total - $pagado, 2);

        if ($orden['estado'] === 'Anulado') {
            $estado = 'Anulado';
        } elseif ($total <= 0.009) {
            $estado = 'Sin costo';
        } elseif ($saldo <= 0.009) {
            $estado = 'Pagado';
        } elseif ($pagado > 0) {
            $estado = 'Parcial';
        } else {
            $estado = 'Pendiente';
        }

        return ['total' => $total, 'pagado' => $pagado, 'saldo' => $saldo, 'estado' => $estado];
    }

    public function activasCount(): int
    {
        return $this->contar("estado IN ('Recibido','En diagnostico','En reparacion','Esperando repuesto','Terminado')");
    }

    /** Órdenes no anuladas con saldo pendiente de cobro. */
    public function conSaldo(int $limite = 100): array
    {
        $limite = max(1, min(500, $limite));
        $sql = $this->consultaBase() . ' WHERE o.estado <> \'Anulado\'
                HAVING saldo > 0.009
                ORDER BY o.fecha_recepcion ASC, o.id_orden ASC
                LIMIT ' . $limite;
        return $this->db->query($sql)->fetchAll();
    }

    /** Órdenes abiertas asignadas a un técnico. */
    public function listarDelTecnico(int $idUsuario, int $limite = 10): array
    {
        if ($idUsuario <= 0) {
            return [];
        }
        $limite = max(1, min(100, $limite));
        $sql = $this->consultaBase() . ' WHERE o.id_usuario_tecnico = :tecnico
                AND o.estado NOT IN (\'Entregado\', \'Anulado\')
                ORDER BY o.fecha_recepcion ASC, o.id_orden ASC
                LIMIT ' . $limite;
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':tecnico' => $idUsuario]);
        return $stmt->fetchAll();
    }

    public function contarDelTecnico(int $idUsuario): int
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM ordenes_servicio
             WHERE id_usuario_tecnico = :tecnico
               AND estado NOT IN ('Entregado', 'Anulado')"
        );
        $stmt->execute([':tecnico' => $idUsuario]);
        return (int) $stmt->fetchColumn();
    }

    /** Cantidad de órdenes por estado (excluye anuladas). */
    public function conteoPorEstado(): array
    {
        $filas = $this->db->query(
            "SELECT estado, COUNT(*) AS cantidad FROM ordenes_servicio
             WHERE estado <> 'Anulado' GROUP BY estado"
        )->fetchAll();
        $conteo = array_fill_keys(self::ESTADOS, 0);
        foreach ($filas as $fila) {
            $conteo[$fila['estado']] = (int) $fila['cantidad'];
        }
        unset($conteo['Anulado']);
        return $conteo;
    }

    public function resumen(?string $desde = null, ?string $hasta = null): array
    {
        $sql = 'SELECT COUNT(*) AS total_ordenes,
                       COALESCE(SUM(costo_servicio), 0) AS mano_obra
                FROM ordenes_servicio WHERE estado <> \'Anulado\'';
        $parametros = [];
        if ($desde !== null && $desde !== '') {
            $sql .= ' AND DATE(fecha_recepcion) >= :desde';
            $parametros[':desde'] = $desde;
        }
        if ($hasta !== null && $hasta !== '') {
            $sql .= ' AND DATE(fecha_recepcion) <= :hasta';
            $parametros[':hasta'] = $hasta;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($parametros);
        $resumen = $stmt->fetch() ?: [];

        $stmtPagos = $this->db->prepare(
            "SELECT COALESCE(SUM(monto), 0) FROM pagos_servicio WHERE estado = 'Confirmado'"
        );
        $stmtPagos->execute();
        $resumen['pagos'] = (float) $stmtPagos->fetchColumn();
        return $resumen;
    }
}
