<?php
/**
 * EduAlert JJP - app/Models/Grado.php
 */

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

class Grado extends Model
{
    protected string $tabla = 'grados';

    public static function conSecciones(): array
    {
        return Database::select(
            'SELECT g.*, COUNT(s.id) AS total_secciones
             FROM grados g LEFT JOIN secciones s ON s.grado_id = g.id
             GROUP BY g.id ORDER BY g.orden'
        );
    }
}