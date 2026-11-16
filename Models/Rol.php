<?php
/**
 * Roles del sistema (Gerente, Vendedor, Tecnico).
 */

class Rol extends Model
{
    protected string $tabla = 'roles';
    protected string $clavePrimaria = 'id_rol';

    public function listar(): array
    {
        $sql = 'SELECT r.*, COUNT(ur.id_usuario) AS total_usuarios
                FROM roles r
                LEFT JOIN usuario_rol ur ON ur.id_rol = r.id_rol
                GROUP BY r.id_rol
                ORDER BY r.id_rol';
        return $this->db->query($sql)->fetchAll();
    }

    public function porNombre(string $nombre): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM roles WHERE nombre = :nombre LIMIT 1');
        $stmt->execute([':nombre' => $nombre]);
        $fila = $stmt->fetch();
        return $fila ?: null;
    }
}
