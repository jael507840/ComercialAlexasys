<?php
/**
 * Usuarios del sistema y asignación de roles.
 * Reglas de negocio: credenciales con password_hash, usuario único,
 * estado Activo/Inactivo y al menos un rol por usuario.
 */

class Usuario extends Model
{
    protected string $tabla = 'usuarios';
    protected string $clavePrimaria = 'id_usuario';

    public function listar(): array
    {
        $sql = 'SELECT u.*, GROUP_CONCAT(r.nombre ORDER BY r.nombre SEPARATOR ", ") AS roles
                FROM usuarios u
                LEFT JOIN usuario_rol ur ON ur.id_usuario = u.id_usuario
                LEFT JOIN roles r ON r.id_rol = ur.id_rol
                GROUP BY u.id_usuario
                ORDER BY u.apellidos, u.nombres';
        return $this->db->query($sql)->fetchAll();
    }

    public function porUsuario(string $usuario): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM usuarios WHERE usuario = :usuario LIMIT 1');
        $stmt->execute([':usuario' => $usuario]);
        $fila = $stmt->fetch();
        return $fila ?: null;
    }

    public function activoPorUsuario(string $usuario): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM usuarios WHERE usuario = :usuario AND estado = 'Activo' LIMIT 1"
        );
        $stmt->execute([':usuario' => $usuario]);
        $fila = $stmt->fetch();
        return $fila ?: null;
    }

    public function rolesDe(int $idUsuario): array
    {
        $stmt = $this->db->prepare(
            'SELECT r.nombre FROM roles r
             INNER JOIN usuario_rol ur ON ur.id_rol = r.id_rol
             WHERE ur.id_usuario = :id ORDER BY r.nombre'
        );
        $stmt->execute([':id' => $idUsuario]);
        return array_column($stmt->fetchAll(), 'nombre');
    }

    public function crear(array $datos, array $roles): int
    {
        if ($roles === []) {
            throw new DomainException('Debe asignar al menos un rol al usuario.');
        }
        try {
            $this->db->beginTransaction();
            $id = $this->insertar($datos);
            $this->asignarRoles($id, $roles);
            $this->db->commit();
            return $id;
        } catch (PDOException $e) {
            $this->db->rollBack();
            if ((string) $e->getCode() === '23000') {
                throw new DomainException('El nombre de usuario ya está registrado. Elija otro.', 0, $e);
            }
            throw $e;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    public function actualizar(int $id, array $datos, ?array $roles = null): bool
    {
        try {
            $this->db->beginTransaction();
            $ok = $this->actualizarRegistro($id, $datos);
            if ($roles !== null) {
                if ($roles === []) {
                    throw new DomainException('Debe asignar al menos un rol al usuario.');
                }
                $this->asignarRoles($id, $roles);
            }
            $this->db->commit();
            return $ok;
        } catch (PDOException $e) {
            $this->db->rollBack();
            if ((string) $e->getCode() === '23000') {
                throw new DomainException('El nombre de usuario ya está registrado por otro usuario.', 0, $e);
            }
            throw $e;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    public function asignarRoles(int $idUsuario, array $roles): void
    {
        $stmt = $this->db->prepare('DELETE FROM usuario_rol WHERE id_usuario = :id');
        $stmt->execute([':id' => $idUsuario]);

        $insert = $this->db->prepare(
            'INSERT INTO usuario_rol (id_usuario, id_rol) VALUES (:usuario, :rol)'
        );
        foreach (array_unique(array_map('intval', $roles)) as $idRol) {
            $insert->execute([':usuario' => $idUsuario, ':rol' => $idRol]);
        }
    }

    public function cambiarEstado(int $id, string $estado): bool
    {
        if (!in_array($estado, ['Activo', 'Inactivo'], true)) {
            throw new DomainException('Estado de usuario no válido.');
        }
        if ($id === (int) (usuario_actual()['id_usuario'] ?? 0) && $estado === 'Inactivo') {
            throw new DomainException('No puede desactivar su propio usuario.');
        }
        return $this->actualizarRegistro($id, ['estado' => $estado]);
    }

    public function cambiarPassword(int $id, string $hash): bool
    {
        return $this->actualizarRegistro($id, ['password_hash' => $hash]);
    }

    /** Usuarios con rol Técnico activos, para asignar órdenes de servicio. */
    public function tecnicos(): array
    {
        $sql = "SELECT DISTINCT u.id_usuario, u.nombres, u.apellidos
                FROM usuarios u
                INNER JOIN usuario_rol ur ON ur.id_usuario = u.id_usuario
                INNER JOIN roles r ON r.id_rol = ur.id_rol
                WHERE r.nombre = 'Tecnico' AND u.estado = 'Activo'
                ORDER BY u.nombres, u.apellidos";
        return $this->db->query($sql)->fetchAll();
    }

    public function contarActivos(): int
    {
        return $this->contar("estado = 'Activo'");
    }
}
