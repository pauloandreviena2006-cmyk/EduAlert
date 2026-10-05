<?php
/**
 * EduAlert JJP - app/Models/Matricula.php
 */

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

class Matricula extends Model
{
    protected string $tabla = 'matriculas';

    public static function listar(string $busqueda = '', int $gradoId = 0, int $seccionId = 0, ?int $periodoId = null, int $limite = 1000): array
    {
        $sql = 'SELECT m.*, e.codigo, e.dni, e.nombres, e.apellidos, e.estado AS estudiante_estado,
                       g.nombre AS grado_nombre, s.nombre AS seccion_nombre, s.turno, p.nombre AS periodo_nombre
                FROM matriculas m
                INNER JOIN estudiantes e ON e.id = m.estudiante_id
                INNER JOIN grados g ON g.id = m.grado_id
                INNER JOIN secciones s ON s.id = m.seccion_id
                INNER JOIN periodos p ON p.id = m.periodo_id
                WHERE 1=1';
        $params = [];
        if ($busqueda !== '') {
            $sql .= ' AND (e.codigo LIKE ? OR e.dni LIKE ? OR e.nombres LIKE ? OR e.apellidos LIKE ?)';
            $b = "%$busqueda%"; $params[] = $b; $params[] = $b; $params[] = $b; $params[] = $b;
        }
        if ($gradoId > 0) { $sql .= ' AND m.grado_id = ?'; $params[] = $gradoId; }
        if ($seccionId > 0) { $sql .= ' AND m.seccion_id = ?'; $params[] = $seccionId; }
        if ($periodoId) { $sql .= ' AND m.periodo_id = ?'; $params[] = $periodoId; }
        $sql .= ' ORDER BY p.anio DESC, p.id DESC, e.apellidos ASC LIMIT ' . (int)$limite;
        return Database::select($sql, $params);
    }

    public static function estudiantesDeSeccion(int $seccionId, int $periodoId): array
    {
        return Database::select(
            'SELECT m.id AS matricula_id, e.id AS estudiante_id, e.codigo, e.dni, e.nombres, e.apellidos
             FROM matriculas m INNER JOIN estudiantes e ON e.id = m.estudiante_id
             WHERE m.seccion_id = ? AND m.periodo_id = ? AND m.estado = 1
             ORDER BY e.apellidos ASC', [$seccionId, $periodoId]
        );
    }

    public static function porPeriodoYSeccion(int $periodoId, int $seccionId)
    {
        return self::estudiantesDeSeccion($seccionId, $periodoId);
    }
}