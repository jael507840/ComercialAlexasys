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
}
