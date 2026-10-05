<?php
/**
 * EduAlert JJP - app/Core/Database.php
 * Conexión PDO singleton y helpers de consultas seguras (prepared statements)
 */

namespace App\Core;

use PDO;
use PDOException;
use PDOStatement;

class Database
{
    private static ?PDO $instancia = null;
    private static array $config = [];

    public static function configurar(array $config): void
    {
        self::$config = $config;
    }

    public static function conexion(): PDO
    {
        if (self::$instancia === null) {
            self::$instancia = self::crearConexion(self::$config);
        }
        return self::$instancia;
    }

    /** Conexión directa al servidor MySQL sin seleccionar base (uso: instalador) */
    public static function pdoServidor(): PDO
    {
        $cfg = self::$config;
        $dsn = sprintf(
            'mysql:host=%s;port=%s;charset=utf8mb4',
            $cfg['host'] ?? '127.0.0.1',
            $cfg['port'] ?? '3306'
        );
        try {
            return new PDO($dsn, $cfg['user'] ?? 'root', $cfg['pass'] ?? '', [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $e) {
            log_error('Error de conexión al servidor MySQL: ' . $e->getMessage());
            throw new \RuntimeException('No se pudo conectar al servidor MySQL. Verifique que MySQL esté activo en XAMPP y que los datos de conexión sean correctos.', 0, $e);
        }
    }

    private static function crearConexion(array $cfg): PDO
    {
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
            $cfg['host'] ?? '127.0.0.1',
            $cfg['port'] ?? '3306',
            $cfg['nombre'] ?? 'edualert_jjp'
        );
        try {
            return new PDO($dsn, $cfg['user'] ?? 'root', $cfg['pass'] ?? '', [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $e) {
            log_error('Error de conexión BD: ' . $e->getMessage());
            throw new \RuntimeException('No se pudo conectar a la base de datos. Verifique que MySQL esté activo y que la base <b>edualert_jjp</b> exista (ejecute el instalador).', 0, $e);
        }
    }

    /** Verifica si se puede conectar (sin lanzar excepciones) */
    public static function puedeConectar(bool $conBase = true): bool
    {
        try {
            $pdo = $conBase ? self::conexion() : self::pdoServidor();
            $pdo->query('SELECT 1');
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /** Ejecuta una consulta preparada y devuelve el statement */
    public static function query(string $sql, array $params = []): PDOStatement
    {
        $stmt = self::conexion()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /** Obtiene todas las filas */
    public static function select(string $sql, array $params = []): array
    {
        return self::query($sql, $params)->fetchAll();
    }

    /** Obtiene una fila */
    public static function selectOne(string $sql, array $params = []): ?array
    {
        $filas = self::query($sql, $params)->fetchAll();
        return $filas[0] ?? null;
    }

    /** Inserta y devuelve el ID */
    public static function insert(string $tabla, array $datos): int
    {
        $columnas = array_keys($datos);
        $marcadores = array_map(fn($c) => ':' . $c, $columnas);
        $sql = 'INSERT INTO ' . $tabla . ' (' . implode(', ', $columnas) . ') VALUES (' . implode(', ', $marcadores) . ')';
        $stmt = self::conexion()->prepare($sql);
        foreach ($datos as $c => $v) {
            $stmt->bindValue(':' . $c, $v);
        }
        $stmt->execute();
        return (int)self::conexion()->lastInsertId();
    }

    /** Actualiza y devuelve nº de filas afectadas
     *  Soporta WHERE con marcadores posicionales (?) o nombrados (:clave).
     *  PDO no permite mezclar :nombre con ? en un mismo statement, por eso
     *  los ? del WHERE se convierten a :wpN automáticamente. */
    public static function update(string $tabla, array $datos, string $where, array $whereParams = []): int
    {
        $sets = array_map(fn($c) => $c . ' = :' . $c, array_keys($datos));

        // Convertir ? posicionales del WHERE en :wpN (en orden)
        $posOrden = [];
        $where = preg_replace_callback('/\?/', function () use (&$posOrden) {
            $posOrden[] = ':wp' . (count($posOrden) + 1);
            return end($posOrden);
        }, $where);

        $sql = 'UPDATE ' . $tabla . ' SET ' . implode(', ', $sets) . ' WHERE ' . $where;
        $stmt = self::conexion()->prepare($sql);
        foreach ($datos as $c => $v) {
            $stmt->bindValue(':' . $c, $v);
        }
        // Valores del WHERE: los posicionales se enlazan por orden; los nombrados por su nombre
        $vals = array_values($whereParams);
        foreach ($posOrden as $idx => $ph) {
            $stmt->bindValue($ph, $vals[$idx] ?? null);
        }
        foreach ($whereParams as $c => $v) {
            if (is_string($c)) {
                $stmt->bindValue($c, $v);
            }
        }
        $stmt->execute();
        return $stmt->rowCount();
    }

    /** Elimina registros */
    public static function delete(string $tabla, string $where, array $params = []): int
    {
        $sql = 'DELETE FROM ' . $tabla . ' WHERE ' . $where;
        return self::query($sql, $params)->rowCount();
    }

    /** Conteo */
    public static function count(string $tabla, string $where = '1=1', array $params = []): int
    {
        $sql = 'SELECT COUNT(*) AS total FROM ' . $tabla . ' WHERE ' . $where;
        return (int)self::selectOne($sql, $params)['total'] ?? 0;
    }

    public static function lastId(): int
    {
        return (int)self::conexion()->lastInsertId();
    }

    public static function begin(): void
    {
        self::conexion()->beginTransaction();
    }
    public static function commit(): void
    {
        self::conexion()->commit();
    }
    public static function rollback(): void
    {
        self::conexion()->rollBack();
    }

    public static function desconectar(): void
    {
        self::$instancia = null;
    }
}