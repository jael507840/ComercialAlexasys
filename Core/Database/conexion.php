<?php
/**
 * Conexión única (patrón singleton) a MySQL mediante PDO.
 *
 * Todos los modelos obtienen la misma instancia a través de
 * Conexion::obtener(), evitando conexiones independientes.
 */

final class Conexion
{
    private static ?PDO $pdo = null;

    private function __construct()
    {
    }

    public static function obtener(): PDO
    {
        if (self::$pdo instanceof PDO) {
            return self::$pdo;
        }

        $config = $GLOBALS['config']['db'] ?? [];
        $host   = $config['host'] ?? '127.0.0.1';
        $puerto = (int) ($config['puerto'] ?? 3306);
        $nombre = $config['nombre'] ?? 'dbalexasys';
        $charset = $config['charset'] ?? 'utf8mb4';

        $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=%s', $host, $puerto, $nombre, $charset);

        $opciones = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::ATTR_STRINGIFY_FETCHES  => false,
        ];

        try {
            self::$pdo = new PDO(
                $dsn,
                $config['usuario'] ?? 'root',
                $config['password'] ?? '',
                $opciones
            );
            self::$pdo->exec("SET time_zone = '-04:00'");
        } catch (PDOException $e) {
            error_log('[Alex@sys] Error de conexión: ' . $e->getMessage());
            throw new RuntimeException(
                'No se pudo establecer conexión con la base de datos. Verifique que MySQL esté activo y la configuración.',
                0,
                $e
            );
        }

        return self::$pdo;
    }

    /** Solo para pruebas automatizadas. */
    public static function reiniciar(): void
    {
        self::$pdo = null;
    }

    private function __clone()
    {
    }
}
