<?php
/**
 * Equipos propiedad del cliente.
 * Permite registrar equipos comprados en Alex@sys o en otro lugar.
 */

class EquipoCliente extends Model
{
    protected string $tabla = 'equipos_cliente';
    protected string $clavePrimaria = 'id_equipo';

    public const TIPOS = ['Computadora de escritorio', 'Laptop', 'Impresora', 'Otro'];

    public function listar(string $busqueda = '', int $idCliente = 0): array
    {
        $sql = 'SELECT e.*, CONCAT(c.nombres, " ", c.apellidos) AS cliente, c.ci, c.telefono,
                       (SELECT COUNT(*) FROM ordenes_servicio o WHERE o.id_equipo = e.id_equipo) AS total_ordenes
                FROM equipos_cliente e
                INNER JOIN clientes c ON c.id_cliente = e.id_cliente
                WHERE 1 = 1';
        $parametros = [];
        if ($busqueda !== '') {
            $sql .= ' AND (e.numero_serie LIKE :q1 OR e.marca LIKE :q2 OR e.modelo LIKE :q3
                        OR c.ci LIKE :q4 OR c.nombres LIKE :q5 OR c.apellidos LIKE :q6)';
            for ($i = 1; $i <= 6; $i++) {
                $parametros[':q' . $i] = '%' . $busqueda . '%';
            }
        }
        if ($idCliente > 0) {
            $sql .= ' AND e.id_cliente = :cliente';
            $parametros[':cliente'] = $idCliente;
        }
        $sql .= ' ORDER BY e.fecha_registro DESC, e.id_equipo DESC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($parametros);
        return $stmt->fetchAll();
    }

    public function porCliente(int $idCliente): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM equipos_cliente WHERE id_cliente = :id ORDER BY fecha_registro DESC'
        );
        $stmt->execute([':id' => $idCliente]);
        return $stmt->fetchAll();
    }

    public function porIdConRelaciones(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT e.*, CONCAT(c.nombres, " ", c.apellidos) AS cliente, c.ci, c.telefono, c.correo,
                    p.codigo AS producto_codigo, p.nombre AS producto
             FROM equipos_cliente e
             INNER JOIN clientes c ON c.id_cliente = e.id_cliente
             LEFT JOIN productos p ON p.id_producto = e.id_producto
             WHERE e.id_equipo = :id LIMIT 1'
        );
        $stmt->execute([':id' => $id]);
        $fila = $stmt->fetch();
        return $fila ?: null;
    }

    public function porSerie(string $serie): ?array
    {
        $serie = trim($serie);
        if ($serie === '') {
            return null;
        }
        $stmt = $this->db->prepare('SELECT * FROM equipos_cliente WHERE numero_serie = :serie LIMIT 1');
        $stmt->execute([':serie' => $serie]);
        $fila = $stmt->fetch();
        return $fila ?: null;
    }

    public function buscarPorSerie(string $serie): array
    {
        $stmt = $this->db->prepare(
            'SELECT e.*, CONCAT(c.nombres, " ", c.apellidos) AS cliente, c.ci, c.telefono
             FROM equipos_cliente e
             INNER JOIN clientes c ON c.id_cliente = e.id_cliente
             WHERE e.numero_serie LIKE :serie
             ORDER BY e.fecha_registro DESC'
        );
        $stmt->execute([':serie' => '%' . $serie . '%']);
        return $stmt->fetchAll();
    }

    public function crear(array $datos): int
    {
        return $this->insertar($datos);
    }

    public function actualizar(int $id, array $datos): bool
    {
        return $this->actualizarRegistro($id, $datos);
    }

    public function ordenes(int $idEquipo): array
    {
        $stmt = $this->db->prepare(
            'SELECT o.*, CONCAT(u.nombres, " ", u.apellidos) AS tecnico
             FROM ordenes_servicio o
             LEFT JOIN usuarios u ON u.id_usuario = o.id_usuario_tecnico
             WHERE o.id_equipo = :id
             ORDER BY o.fecha_recepcion DESC'
        );
        $stmt->execute([':id' => $idEquipo]);
        return $stmt->fetchAll();
    }
}
