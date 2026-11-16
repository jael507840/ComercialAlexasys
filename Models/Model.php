<?php
/**
 * Modelo base: encapsula PDO y operaciones comunes.
 * Las consultas específicas pertenecen a cada modelo.
 */

abstract class Model
{
    protected PDO $db;
    protected string $tabla = '';
    protected string $clavePrimaria = 'id';

    public function __construct()
    {
        $this->db = Conexion::obtener();
    }

    public function todos(string $orden = ''): array
    {
        $sql = "SELECT * FROM {$this->tabla}";
        if ($orden !== '') {
            $sql .= " ORDER BY {$orden}";
        }
        return $this->db->query($sql)->fetchAll();
    }

    public function porId(int $id): ?array
    {
        $sql = "SELECT * FROM {$this->tabla} WHERE {$this->clavePrimaria} = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        $fila = $stmt->fetch();
        return $fila ?: null;
    }

    public function existe(int $id): bool
    {
        $sql = "SELECT COUNT(*) FROM {$this->tabla} WHERE {$this->clavePrimaria} = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function contar(string $condicion = '1 = 1', array $parametros = []): int
    {
        $sql = "SELECT COUNT(*) FROM {$this->tabla} WHERE {$condicion}";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($parametros);
        return (int) $stmt->fetchColumn();
    }

    protected function insertar(array $datos): int
    {
        if ($datos === []) {
            throw new InvalidArgumentException('No se recibieron datos para insertar.');
        }
        $columnas = array_keys($datos);
        $marcadores = array_map(static fn (string $c): string => ':' . $c, $columnas);
        $sql = "INSERT INTO {$this->tabla} (" . implode(', ', $columnas) . ') VALUES ('
            . implode(', ', $marcadores) . ')';
        $stmt = $this->db->prepare($sql);
        foreach ($datos as $columna => $valor) {
            $stmt->bindValue(':' . $columna, $valor);
        }
        $stmt->execute();
        return (int) $this->db->lastInsertId();
    }

    protected function actualizarRegistro(int $id, array $datos): bool
    {
        if ($datos === []) {
            return false;
        }
        $asignaciones = [];
        foreach (array_keys($datos) as $columna) {
            $asignaciones[] = "{$columna} = :{$columna}";
        }
        $sql = "UPDATE {$this->tabla} SET " . implode(', ', $asignaciones)
            . " WHERE {$this->clavePrimaria} = :__id";
        $stmt = $this->db->prepare($sql);
        foreach ($datos as $columna => $valor) {
            $stmt->bindValue(':' . $columna, $valor);
        }
        $stmt->bindValue(':__id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function ultimoError(): string
    {
        return (string) $this->db->errorInfo()[2];
    }
}
