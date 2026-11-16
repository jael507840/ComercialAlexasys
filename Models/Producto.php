<?php
/**
 * Productos e inventario.
 * El costo de adquisición se obtiene de la última compra registrada.
 */

class Producto extends Model
{
    protected string $tabla = 'productos';
    protected string $clavePrimaria = 'id_producto';

    public function listar(string $busqueda = '', int $idCategoria = 0, bool $soloBajoStock = false): array
    {
        $sql = 'SELECT p.*, c.nombre AS categoria,
                    (SELECT dc.costo_unitario
                     FROM detalle_compra dc
                     INNER JOIN compras co ON co.id_compra = dc.id_compra
                     WHERE dc.id_producto = p.id_producto
                     ORDER BY co.fecha_compra DESC, dc.id_detalle_compra DESC
                     LIMIT 1) AS costo_adquisicion
                FROM productos p
                INNER JOIN categorias c ON c.id_categoria = p.id_categoria
                WHERE 1 = 1';
        $parametros = [];
        if ($busqueda !== '') {
            $sql .= ' AND (p.codigo LIKE :q1 OR p.nombre LIKE :q2 OR p.marca LIKE :q3 OR p.modelo LIKE :q4)';
            for ($i = 1; $i <= 4; $i++) {
                $parametros[':q' . $i] = '%' . $busqueda . '%';
            }
        }
        if ($idCategoria > 0) {
            $sql .= ' AND p.id_categoria = :categoria';
            $parametros[':categoria'] = $idCategoria;
        }
        if ($soloBajoStock) {
            $sql .= ' AND p.stock_actual <= p.stock_minimo';
        }
        $sql .= ' ORDER BY p.id_categoria, p.nombre';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($parametros);
        return $stmt->fetchAll();
    }

    public function activos(): array
    {
        return $this->db->query(
            "SELECT p.*, c.nombre AS categoria,
                    (SELECT dc.costo_unitario
                     FROM detalle_compra dc
                     INNER JOIN compras co ON co.id_compra = dc.id_compra
                     WHERE dc.id_producto = p.id_producto
                     ORDER BY co.fecha_compra DESC, dc.id_detalle_compra DESC
                     LIMIT 1) AS costo_adquisicion
             FROM productos p
             INNER JOIN categorias c ON c.id_categoria = p.id_categoria
             WHERE p.estado = 'Activo' ORDER BY p.id_categoria, p.nombre"
        )->fetchAll();
    }

    public function disponibles(): array
    {
        return $this->db->query(
            "SELECT p.*, c.nombre AS categoria FROM productos p
             INNER JOIN categorias c ON c.id_categoria = p.id_categoria
             WHERE p.estado = 'Activo' AND p.stock_actual > 0 ORDER BY p.id_categoria, p.nombre"
        )->fetchAll();
    }

    /**
     * Productos para la portada: mezcla equilibrada entre categorías,
     * de modo que no se muestren solo equipos de una misma sección.
     */
    public function destacados(int $limite = 6): array
    {
        $porCategoria = [];
        foreach ($this->disponibles() as $producto) {
            $porCategoria[(int) $producto['id_categoria']][] = $producto;
        }

        $seleccion = [];
        $indices = array_fill_keys(array_keys($porCategoria), 0);
        while (count($seleccion) < $limite) {
            $agregado = false;
            foreach ($porCategoria as $idCategoria => $items) {
                $i = $indices[$idCategoria];
                if ($i >= count($items)) {
                    continue;
                }
                $seleccion[] = $items[$i];
                $indices[$idCategoria] = $i + 1;
                $agregado = true;
                if (count($seleccion) >= $limite) {
                    break;
                }
            }
            if (!$agregado) {
                break;
            }
        }
        return $seleccion;
    }

    public function porCodigo(string $codigo): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM productos WHERE codigo = :codigo LIMIT 1');
        $stmt->execute([':codigo' => $codigo]);
        $fila = $stmt->fetch();
        return $fila ?: null;
    }

    public function crear(array $datos): int
    {
        try {
            return $this->insertar($datos);
        } catch (PDOException $e) {
            if ((string) $e->getCode() === '23000') {
                throw new DomainException('El código del producto ya existe.', 0, $e);
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
                throw new DomainException('El código del producto ya pertenece a otro producto.', 0, $e);
            }
            throw $e;
        }
    }

    public function cambiarEstado(int $id, string $estado): bool
    {
        if (!in_array($estado, ['Activo', 'Inactivo'], true)) {
            throw new DomainException('Estado de producto no válido.');
        }
        return $this->actualizarRegistro($id, ['estado' => $estado]);
    }

    /** Costo de la última compra; null si el producto nunca fue comprado. */
    public function costoAdquisicion(int $id): ?float
    {
        $sql = 'SELECT dc.costo_unitario
                FROM detalle_compra dc
                INNER JOIN compras co ON co.id_compra = dc.id_compra
                WHERE dc.id_producto = :id
                ORDER BY co.fecha_compra DESC, dc.id_detalle_compra DESC
                LIMIT 1';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        $valor = $stmt->fetchColumn();
        return $valor === false ? null : (float) $valor;
    }

    public function bajoStock(): array
    {
        $sql = "SELECT p.*, c.nombre AS categoria
                FROM productos p
                INNER JOIN categorias c ON c.id_categoria = p.id_categoria
                WHERE p.estado = 'Activo' AND p.stock_actual <= p.stock_minimo
                ORDER BY (CAST(p.stock_actual AS SIGNED) - CAST(p.stock_minimo AS SIGNED)), p.nombre";
        return $this->db->query($sql)->fetchAll();
    }

    public function contarActivos(): int
    {
        return $this->contar("estado = 'Activo'");
    }

    public function contarBajoStock(): int
    {
        return $this->contar("estado = 'Activo' AND stock_actual <= stock_minimo");
    }

    /** Resumen valorizado del inventario (venta y costo de adquisición). */
    public function resumenInventario(): array
    {
        $sql = 'SELECT COUNT(*) AS total_productos,
                       COALESCE(SUM(p.stock_actual), 0) AS total_unidades,
                       COALESCE(SUM(p.stock_actual * p.precio_venta), 0) AS valor_venta
                FROM productos p WHERE p.estado = \'Activo\'';
        $resumen = $this->db->query($sql)->fetch() ?: [];

        $costo = $this->db->query(
            'SELECT COALESCE(SUM(p.stock_actual * COALESCE(uc.costo, 0)), 0) AS valor_costo
             FROM productos p
             LEFT JOIN (
                SELECT dc.id_producto,
                       SUBSTRING_INDEX(GROUP_CONCAT(dc.costo_unitario ORDER BY co.fecha_compra DESC, dc.id_detalle_compra DESC), ",", 1) AS costo
                FROM detalle_compra dc
                INNER JOIN compras co ON co.id_compra = dc.id_compra
                GROUP BY dc.id_producto
             ) uc ON uc.id_producto = p.id_producto
             WHERE p.estado = \'Activo\''
        )->fetch() ?: [];

        return array_merge($resumen, $costo);
    }

    public function ajustarStock(int $id, int $nuevoStock): void
    {
        $this->actualizarRegistro($id, ['stock_actual' => $nuevoStock]);
    }
}
