<?php
/**
 * Intervenciones / detalle del servicio técnico.
 * Conserva el historial completo de diagnósticos y trabajos.
 */

class DetalleServicio extends Model
{
    protected string $tabla = 'detalle_servicio';
    protected string $clavePrimaria = 'id_detalle_servicio';

    public function registrar(
        int $idOrden,
        int $idTecnico,
        string $fechaIntervencion,
        string $diagnostico,
        string $trabajoRealizado,
        ?string $resultadoPrueba,
        float $costo
    ): int {
        if (trim($diagnostico) === '' || trim($trabajoRealizado) === '') {
            throw new DomainException('El diagnóstico y el trabajo realizado son obligatorios.');
        }
        if ($costo < 0) {
            throw new DomainException('El costo de la intervención no puede ser negativo.');
        }

        $ordenModelo = new OrdenServicio();
        $orden = $ordenModelo->porId($idOrden);
        if (!$orden) {
            throw new DomainException('La orden de servicio indicada no existe.');
        }
        if (in_array($orden['estado'], ['Entregado', 'Anulado'], true)) {
            throw new DomainException('No se pueden agregar intervenciones a una orden entregada o anulada.');
        }

        try {
            $this->db->beginTransaction();
            $id = $this->insertar([
                'id_orden'           => $idOrden,
                'id_usuario_tecnico' => $idTecnico,
                'fecha_intervencion' => $fechaIntervencion,
                'diagnostico'        => trim($diagnostico),
                'trabajo_realizado'  => trim($trabajoRealizado),
                'resultado_prueba'   => $resultadoPrueba !== null && trim($resultadoPrueba) !== ''
                    ? trim($resultadoPrueba) : null,
                'costo'              => round($costo, 2),
            ]);
            $ordenModelo->recalcularCosto($idOrden);
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
            'SELECT ds.*, CONCAT(u.nombres, " ", u.apellidos) AS tecnico
             FROM detalle_servicio ds
             INNER JOIN usuarios u ON u.id_usuario = ds.id_usuario_tecnico
             WHERE ds.id_orden = :id
             ORDER BY ds.fecha_intervencion DESC, ds.id_detalle_servicio DESC'
        );
        $stmt->execute([':id' => $idOrden]);
        return $stmt->fetchAll();
    }

    public function listar(?string $desde = null, ?string $hasta = null): array
    {
        $sql = 'SELECT ds.*, o.numero_orden, CONCAT(u.nombres, " ", u.apellidos) AS tecnico,
                       CONCAT(c.nombres, " ", c.apellidos) AS cliente
                FROM detalle_servicio ds
                INNER JOIN ordenes_servicio o ON o.id_orden = ds.id_orden
                INNER JOIN equipos_cliente e ON e.id_equipo = o.id_equipo
                INNER JOIN clientes c ON c.id_cliente = e.id_cliente
                INNER JOIN usuarios u ON u.id_usuario = ds.id_usuario_tecnico
                WHERE 1 = 1';
        $parametros = [];
        if ($desde !== null && $desde !== '') {
            $sql .= ' AND DATE(ds.fecha_intervencion) >= :desde';
            $parametros[':desde'] = $desde;
        }
        if ($hasta !== null && $hasta !== '') {
            $sql .= ' AND DATE(ds.fecha_intervencion) <= :hasta';
            $parametros[':hasta'] = $hasta;
        }
        $sql .= ' ORDER BY ds.fecha_intervencion DESC LIMIT 200';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($parametros);
        return $stmt->fetchAll();
    }
}
