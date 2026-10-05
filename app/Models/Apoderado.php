<?php
/**
 * EduAlert JJP - app/Models/Apoderado.php
 */

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

class Apoderado extends Model
{
    protected string $tabla = 'apoderados';

    public static function porEstudiante(int $estudianteId): array
    {
        return Database::select('SELECT * FROM apoderados WHERE estudiante_id = ?', [$estudianteId]);
    }

    public static function reemplazar(int $estudianteId, array $datos): void
    {
        Database::delete('apoderados', 'estudiante_id = ?', [$estudianteId]);
        if (!empty($datos['nombres']) && !empty($datos['apellidos'])) {
            Database::insert('apoderados', [
                'estudiante_id' => $estudianteId,
                'nombres' => $datos['nombres'],
                'apellidos' => $datos['apellidos'],
                'dni' => $datos['dni'] ?? null,
                'telefono' => $datos['telefono'] ?? null,
                'parentesco' => $datos['parentesco'] ?? 'Padre',
                'ocupacion' => $datos['ocupacion'] ?? null,
            ]);
        }
    }
}