<?php
/**
 * EduAlert JJP - app/Models/Calificacion.php
 */

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

class Calificacion extends Model
{
    protected string $tabla = 'calificaciones';

    /** Calificaciones de un estudiante en un periodo (para ficha) */
    public static function deEstudiante(int $estudianteId, ?int $periodoId = null): array
    {
        $sql = 'SELECT c.*, cu.nombre AS curso_nombre, d.nombres AS docente_nombres, d.apellidos AS docente_apellidos
                FROM calificaciones c
                INNER JOIN matriculas m ON m.id = c.matricula_id
                INNER JOIN cursos cu ON cu.id = c.curso_id
                LEFT JOIN docentes d ON d.id = c.docente_id
                WHERE m.estudiante_id = ?';
        $params = [$estudianteId];
        if ($periodoId) { $sql .= ' AND c.periodo_id = ?'; $params[] = $periodoId; }
        $sql .= ' ORDER BY cu.nombre';
        return Database::select($sql, $params);
    }

    /** Promedio de un estudiante por periodo */
    public static function promediosPorPeriodo(int $estudianteId): array
    {
        return Database::select(
            'SELECT p.id AS periodo_id, p.nombre AS periodo_nombre, p.anio,
                    ROUND(AVG(c.nota), 2) AS promedio, COUNT(c.id) AS cursos
             FROM calificaciones c
             INNER JOIN periodos p ON p.id = c.periodo_id
             INNER JOIN matriculas m ON m.id = c.matricula_id
             WHERE m.estudiante_id = ?
             GROUP BY p.id, p.nombre, p.anio ORDER BY p.id', [$estudianteId]
        );
    }

    public static function promedioEstudiantePeriodo(int $estudianteId, int $periodoId): ?float
    {
        $fila = Database::selectOne(
            'SELECT ROUND(AVG(c.nota), 2) AS prom
             FROM calificaciones c INNER JOIN matriculas m ON m.id = c.matricula_id
             WHERE m.estudiante_id = ? AND c.periodo_id = ?', [$estudianteId, $periodoId]
        );
        return $fila['prom'] !== null ? (float)$fila['prom'] : null;
    }

    public static function desaprobadosEstudiantePeriodo(int $estudianteId, int $periodoId, float $aprobatorio): int
    {
        $fila = Database::selectOne(
            'SELECT COUNT(*) AS total
             FROM calificaciones c INNER JOIN matriculas m ON m.id = c.matricula_id
             WHERE m.estudiante_id = ? AND c.periodo_id = ? AND c.nota < ?', [$estudianteId, $periodoId, $aprobatorio]
        );
        return (int)($fila['total'] ?? 0);
    }

    /** Gráfico: rendimiento por grado (periodo activo) */
    public static function rendimientoPorGrado(?int $periodoId): array
    {
        return Database::select(
            'SELECT g.nombre AS grado, ROUND(AVG(c.nota), 2) AS promedio
             FROM calificaciones c
             INNER JOIN matriculas m ON m.id = c.matricula_id
             INNER JOIN grados g ON g.id = m.grado_id
             WHERE c.periodo_id = ?
             GROUP BY g.id, g.nombre ORDER BY g.orden', [$periodoId]
        );
    }
}