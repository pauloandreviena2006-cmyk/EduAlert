<?php
/**
 * EduAlert JJP - app/Models/Asistencia.php
 */

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

class Asistencia extends Model
{
    protected string $tabla = 'asistencias';

    public static function resumenEstudiantePeriodo(int $estudianteId, int $periodoId): array
    {
        $fila = Database::selectOne(
            'SELECT
                SUM(CASE WHEN a.estado="Presente" THEN 1 ELSE 0 END) AS presentes,
                SUM(CASE WHEN a.estado="Tardanza" THEN 1 ELSE 0 END) AS tardanzas,
                SUM(CASE WHEN a.estado="Falta" THEN 1 ELSE 0 END) AS faltas,
                SUM(CASE WHEN a.estado="Justificado" THEN 1 ELSE 0 END) AS justificados,
                COUNT(*) AS total
             FROM asistencias a
             INNER JOIN matriculas m ON m.id = a.matricula_id
             WHERE m.estudiante_id = ? AND m.periodo_id = ?', [$estudianteId, $periodoId]
        );
        $total = (int)($fila['total'] ?? 0);
        $presentes = (int)($fila['presentes'] ?? 0);
        $tardanzas = (int)($fila['tardanzas'] ?? 0);
        $pct = $total > 0 ? round((($presentes + $tardanzas) / $total) * 100, 2) : 100.0;
        return [
            'presentes' => $presentes,
            'tardanzas' => $tardanzas,
            'faltas' => (int)($fila['faltas'] ?? 0),
            'justificados' => (int)($fila['justificados'] ?? 0),
            'total' => $total,
            'asistencia_pct' => $pct,
            'inasistencia_pct' => round(100 - $pct, 2),
        ];
    }

    public static function deEstudiante(int $estudianteId, string $desde = '', string $hasta = ''): array
    {
        $sql = 'SELECT a.*, p.nombre AS periodo_nombre FROM asistencias a
                INNER JOIN matriculas m ON m.id = a.matricula_id
                INNER JOIN periodos p ON p.id = m.periodo_id
                WHERE m.estudiante_id = ?';
        $params = [$estudianteId];
        if ($desde !== '') { $sql .= ' AND a.fecha >= ?'; $params[] = $desde; }
        if ($hasta !== '') { $sql .= ' AND a.fecha <= ?'; $params[] = $hasta; }
        $sql .= ' ORDER BY a.fecha DESC LIMIT 200';
        return Database::select($sql, $params);
    }

    public static function resumenPorGrado(?int $periodoId): array
    {
        return Database::select(
            'SELECT g.nombre AS grado,
                SUM(CASE WHEN a.estado="Falta" THEN 1 ELSE 0 END) AS faltas,
                SUM(CASE WHEN a.estado="Tardanza" THEN 1 ELSE 0 END) AS tardanzas
             FROM asistencias a
             INNER JOIN matriculas m ON m.id = a.matricula_id
             INNER JOIN grados g ON g.id = m.grado_id
             WHERE m.periodo_id = ?
             GROUP BY g.id, g.nombre ORDER BY g.orden', [$periodoId]
        );
    }
}