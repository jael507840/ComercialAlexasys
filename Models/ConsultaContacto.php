<?php
/**
 * Registro de consultas enviadas desde la página pública de contacto.
 */
class ConsultaContacto extends Model
{
    protected string $tabla = 'consultas_contacto';
    protected string $clavePrimaria = 'id_consulta';

    public function registrar(array $datos): int
    {
        return $this->insertar([
            'nombre' => $datos['nombre'],
            'correo' => $datos['correo'],
            'mensaje' => $datos['mensaje'],
        ]);
    }

    public function marcarNotificacionEnviada(int $id): void
    {
        $stmt = $this->db->prepare(
            'UPDATE consultas_contacto SET notificacion_enviada = 1 WHERE id_consulta = :id'
        );
        $stmt->execute([':id' => $id]);
    }

    /** Consultas recibidas, de la más reciente a la más antigua. */
    public function listar(string $busqueda = '', int $limite = 200): array
    {
        $sql = 'SELECT * FROM consultas_contacto WHERE 1 = 1';
        $parametros = [];
        if ($busqueda !== '') {
            $sql .= ' AND (nombre LIKE :q1 OR correo LIKE :q2 OR mensaje LIKE :q3)';
            for ($i = 1; $i <= 3; $i++) {
                $parametros[':q' . $i] = '%' . $busqueda . '%';
            }
        }
        $limite = max(1, min(500, $limite));
        $sql .= ' ORDER BY fecha_registro DESC, id_consulta DESC LIMIT ' . $limite;
        $stmt = $this->db->prepare($sql);
        $stmt->execute($parametros);
        return $stmt->fetchAll();
    }

    /** Mensajes cuyo correo de aviso todavía no pudo enviarse. */
    public function contarPendientes(): int
    {
        return $this->contar('notificacion_enviada = 0');
    }
}
