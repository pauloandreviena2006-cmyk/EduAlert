<?php
/**
 * EduAlert JJP - app/Models/NivelRiesgo.php
 */

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

class NivelRiesgo extends Model
{
    protected string $tabla = 'niveles_riesgo';

    /** Determina el nivel de riesgo según puntaje (en orden de menor a mayor rango) */
    public static function nivelPorPuntaje(int $puntaje): ?array
    {
        $niveles = Database::select('SELECT * FROM niveles_riesgo ORDER BY min_puntaje ASC');
        $seleccionado = null;
        foreach ($niveles as $n) {
            if ($puntaje >= (int)$n['min_puntaje'] && $puntaje <= (int)$n['max_puntaje']) {
                $seleccionado = $n;
            }
        }
        return $seleccionado;
    }

    public static function conteoEstudiantes(): array
    {
        $filas = Database::select(
            'SELECT nr.nombre, nr.color, COUNT(e.id) AS total
             FROM niveles_riesgo nr LEFT JOIN estudiantes e ON e.nivel_riesgo_id = nr.id
             GROUP BY nr.id, nr.nombre, nr.color ORDER BY nr.min_puntaje'
        );
        // Asegurar que "sin riesgo" (null) esté considerado
        $sin = Database::selectOne("SELECT COUNT(*) AS total FROM estudiantes WHERE nivel_riesgo_id IS NULL");
        return ['niveles' => $filas, 'sin_riesgo' => (int)($sin['total'] ?? 0)];
    }
}