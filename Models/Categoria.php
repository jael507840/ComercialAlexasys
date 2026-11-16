<?php
/**
 * Categorías del catálogo de productos.
 */

class Categoria extends Model
{
    protected string $tabla = 'categorias';
    protected string $clavePrimaria = 'id_categoria';

    public function listar(string $busqueda = ''): array
    {
        $sql = 'SELECT c.*, COUNT(p.id_producto) AS total_productos
                FROM categorias c
                LEFT JOIN productos p ON p.id_categoria = c.id_categoria';
        $parametros = [];
        if ($busqueda !== '') {
            $sql .= ' WHERE c.nombre LIKE :q1 OR c.descripcion LIKE :q2';
            $parametros[':q1'] = '%' . $busqueda . '%';
            $parametros[':q2'] = '%' . $busqueda . '%';
        }
        $sql .= ' GROUP BY c.id_categoria ORDER BY c.nombre';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($parametros);
        return $stmt->fetchAll();
    }

    public function activas(): array
    {
        return $this->db->query(
            "SELECT * FROM categorias WHERE estado = 'Activo' ORDER BY nombre"
        )->fetchAll();
    }

    public function crear(array $datos): int
    {
        try {
            return $this->insertar($datos);
        } catch (PDOException $e) {
            if ((string) $e->getCode() === '23000') {
                throw new DomainException('Ya existe una categoría con ese nombre.', 0, $e);
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
                throw new DomainException('Ya existe otra categoría con ese nombre.', 0, $e);
            }
            throw $e;
        }
    }

    public function cambiarEstado(int $id, string $estado): bool
    {
        if (!in_array($estado, ['Activo', 'Inactivo'], true)) {
            throw new DomainException('Estado de categoría no válido.');
        }
        return $this->actualizarRegistro($id, ['estado' => $estado]);
    }

    public function totalProductos(int $id): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM productos WHERE id_categoria = :id');
        $stmt->execute([':id' => $id]);
        return (int) $stmt->fetchColumn();
    }
}
