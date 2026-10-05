<?php
/**
 * EduAlert JJP - app/Models/Periodo.php
 */

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

class Periodo extends Model
{
    protected string $tabla = 'periodos';

    public static function activo(): ?array
    {
        return Database::selectOne('SELECT * FROM periodos WHERE activo = 1 AND estado = 1 LIMIT 1');
    }

    public static function anteriorAl(?int $periodoId): ?array
    {
        if (!$periodoId) return null;
        return Database::selectOne(
            'SELECT * FROM periodos WHERE id < ? ORDER BY id DESC LIMIT 1', [$periodoId]
        );
    }

    public static function listarOrdenados(): array
    {
        return Database::select('SELECT * FROM periodos ORDER BY anio DESC, id DESC');
    }

    public static function activar(int $id): void
    {
        Database::update('periodos', ['activo' => 0], 'activo = 1');
        Database::update('periodos', ['activo' => 1], 'id = ?', [1 => $id]);
    }
}