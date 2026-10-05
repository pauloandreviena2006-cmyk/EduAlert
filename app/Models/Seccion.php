<?php
/**
 * EduAlert JJP - app/Models/Seccion.php
 */

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

class Seccion extends Model
{
    protected string $tabla = 'secciones';

    public static function listarConGrado(): array
    {
        return Database::select(
            'SELECT s.*, g.nombre AS grado_nombre, g.orden
             FROM secciones s INNER JOIN grados g ON g.id = s.grado_id
             ORDER BY g.orden, s.nombre'
        );
    }

    public static function porGrado(int $gradoId): array
    {
        return Database::select('SELECT * FROM secciones WHERE grado_id = ? ORDER BY nombre', [$gradoId]);
    }
}