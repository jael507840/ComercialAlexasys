<?php
/**
 * Proveedores de mercadería.
 */

class Proveedor extends Model
{
    protected string $tabla = 'proveedores';
    protected string $clavePrimaria = 'id_proveedor';

    public function listar(string $busqueda = ''): array
    {
        $sql = 'SELECT p.*,
                    (SELECT COUNT(*) FROM compras c WHERE c.id_proveedor = p.id_proveedor) AS total_compras,
                    (SELECT COALESCE(SUM(c.total_compra), 0) FROM compras c
                     WHERE c.id_proveedor = p.id_proveedor AND c.estado = \'Registrada\') AS total_comprado
                FROM proveedores p';
        $parametros = [];
        if ($busqueda !== '') {
            $sql .= ' WHERE (p.nombre LIKE :q1 OR p.nit LIKE :q2 OR p.telefono LIKE :q3)';
            for ($i = 1; $i <= 3; $i++) {
                $parametros[':q' . $i] = '%' . $busqueda . '%';
            }
        }
        $sql .= ' ORDER BY p.nombre';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($parametros);
        return $stmt->fetchAll();
    }

    public function porNit(string $nit): ?array
    {
        if ($nit === '') {
            return null;
        }
        $stmt = $this->db->prepare(
            'SELECT * FROM proveedores WHERE nit = :nit LIMIT 1'
        );
        $stmt->execute([':nit' => $nit]);
        $fila = $stmt->fetch();
        return $fila ?: null;
    }

    public function activos(): array
    {
        return $this->db->query(
            "SELECT * FROM proveedores WHERE estado = 'Activo' ORDER BY nombre"
        )->fetchAll();
    }

    public function crear(array $datos): int
    {
        return $this->insertar($datos);
    }

    public function actualizar(int $id, array $datos): bool
    {
        return $this->actualizarRegistro($id, $datos);
    }

    public function cambiarEstado(int $id, string $estado): bool
    {
        if (!in_array($estado, ['Activo', 'Inactivo'], true)) {
            throw new DomainException('Estado de proveedor no válido.');
        }
        return $this->actualizarRegistro($id, ['estado' => $estado]);
    }

    public function compras(int $id): array
    {
        $stmt = $this->db->prepare(
            'SELECT c.*, u.nombres, u.apellidos
             FROM compras c
             INNER JOIN usuarios u ON u.id_usuario = c.id_usuario
             WHERE c.id_proveedor = :id
             ORDER BY c.fecha_compra DESC'
        );
        $stmt->execute([':id' => $id]);
        return $stmt->fetchAll();
    }
}
