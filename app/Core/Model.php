<?php
/**
 * EduAlert JJP - app/Core/Model.php
 * Modelo base: helpers comunes para acceso a datos
 */

namespace App\Core;

abstract class Model
{
    protected string $tabla = '';

    public static function tabla(): string
    {
        $instancia = new static();
        return $instancia->tabla;
    }

    public static function buscar(int $id): ?array
    {
        return Database::selectOne('SELECT * FROM ' . static::tabla() . ' WHERE id = ?', [$id]);
    }

    public static function todos(string $orden = 'id DESC'): array
    {
        return Database::select('SELECT * FROM ' . static::tabla() . ' ORDER BY ' . $orden);
    }

    public static function crear(array $datos): int
    {
        return Database::insert(static::tabla(), $datos);
    }

    public static function actualizar(int $id, array $datos): int
    {
        return Database::update(static::tabla(), $datos, 'id = ?', [1 => $id]);
    }

    public static function eliminar(int $id): int
    {
        return Database::delete(static::tabla(), 'id = ?', [$id]);
    }

    public static function donde(string $where, array $params = []): array
    {
        return Database::select('SELECT * FROM ' . static::tabla() . ' WHERE ' . $where, $params);
    }

    public static function primero(string $where, array $params = []): ?array
    {
        return Database::selectOne('SELECT * FROM ' . static::tabla() . ' WHERE ' . $where . ' LIMIT 1', $params);
    }

    public static function contar(string $where = '1=1', array $params = []): int
    {
        return Database::count(static::tabla(), $where, $params);
    }
}