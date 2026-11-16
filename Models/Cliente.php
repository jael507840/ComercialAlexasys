<?php
/**
 * Clientes de la tienda comercial Alex@sys.
 */

class Cliente extends Model
{
    protected string $tabla = 'clientes';
    protected string $clavePrimaria = 'id_cliente';

    public function listar(string $busqueda = ''): array
    {
        $sql = 'SELECT c.*,
                    (SELECT COUNT(*) FROM ventas v WHERE v.id_cliente = c.id_cliente) AS total_ventas,
                    (SELECT COUNT(*) FROM equipos_cliente e WHERE e.id_cliente = c.id_cliente) AS total_equipos
                FROM clientes c';
        $parametros = [];
        if ($busqueda !== '') {
            $sql .= ' WHERE (c.ci LIKE :q1 OR c.nombres LIKE :q2 OR c.apellidos LIKE :q3
                        OR c.telefono LIKE :q4 OR c.correo LIKE :q5)';
            for ($i = 1; $i <= 5; $i++) {
                $parametros[':q' . $i] = '%' . $busqueda . '%';
            }
        }
        $sql .= ' ORDER BY c.apellidos, c.nombres';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($parametros);
        return $stmt->fetchAll();
    }

    public function activos(): array
    {
        return $this->db->query(
            "SELECT * FROM clientes WHERE estado = 'Activo' ORDER BY apellidos, nombres"
        )->fetchAll();
    }

    public function porCi(string $ci): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM clientes WHERE ci = :ci LIMIT 1');
        $stmt->execute([':ci' => $ci]);
        $fila = $stmt->fetch();
        return $fila ?: null;
    }

    public function crear(array $datos): int
    {
        try {
            return $this->insertar($datos);
        } catch (PDOException $e) {
            if ((string) $e->getCode() === '23000') {
                throw new DomainException('La cédula de identidad ya está registrada para otro cliente.', 0, $e);
            }
            throw $e;
        }
    }

    public function actualizar(int $id, array $datos): bool
    {
        try {
            return $this->actualizarRegistro($id, $datos);
        } catch (PDOException $e) {
            if ((string) $e->getCode() === '23000') {
                throw new DomainException('La cédula de identidad ya pertenece a otro cliente.', 0, $e);
            }
            throw $e;
        }
    }

    public function cambiarEstado(int $id, string $estado): bool
    {
        if (!in_array($estado, ['Activo', 'Inactivo'], true)) {
            throw new DomainException('Estado de cliente no válido.');
        }
        return $this->actualizarRegistro($id, ['estado' => $estado]);
    }

    /** Historial de ventas con total pagado y saldo. */
    public function historialVentas(int $idCliente): array
    {
        $sql = "SELECT v.*, COALESCE(p.pagado, 0) AS pagado,
                       (v.total - COALESCE(p.pagado, 0)) AS saldo
                FROM ventas v
                LEFT JOIN (
                    SELECT id_venta, SUM(monto) AS pagado
                    FROM pagos_venta WHERE estado = 'Confirmado' GROUP BY id_venta
                ) p ON p.id_venta = v.id_venta
                WHERE v.id_cliente = :id
                ORDER BY v.fecha_venta DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $idCliente]);
        return $stmt->fetchAll();
    }

    public function historialEquipos(int $idCliente): array
    {
        $sql = "SELECT e.*,
                    (SELECT COUNT(*) FROM ordenes_servicio o WHERE o.id_equipo = e.id_equipo) AS total_ordenes
                FROM equipos_cliente e
                WHERE e.id_cliente = :id
                ORDER BY e.fecha_registro DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $idCliente]);
        return $stmt->fetchAll();
    }

    public function historialServicios(int $idCliente): array
    {
        $sql = "SELECT o.*, e.marca, e.modelo, e.numero_serie, e.tipo_equipo
                FROM ordenes_servicio o
                INNER JOIN equipos_cliente e ON e.id_equipo = o.id_equipo
                WHERE e.id_cliente = :id
                ORDER BY o.fecha_recepcion DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $idCliente]);
        return $stmt->fetchAll();
    }

    public function contarActivos(): int
    {
        return $this->contar("estado = 'Activo'");
    }
}
