<?php
/**
 * EduAlert JJP - app/Models/Estudiante.php
 */

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

class Estudiante extends Model
{
    protected string $tabla = 'estudiantes';

    /** Listado con filtros para el módulo estudiantes */
    public static function listar(string $busqueda = '', int $gradoId = 0, int $seccionId = 0, ?int $periodoId = null, string $estado = '', int $limite = 1000, int $offset = 0, string $orden = 'e.apellidos ASC'): array
    {
        $sql = 'SELECT e.*, g.nombre AS grado_nombre, s.nombre AS seccion_nombre, s.turno AS seccion_turno,
                       m.id AS matricula_id, m.periodo_id, m.grado_id, m.seccion_id,
                       nr.nombre AS riesgo_nombre, nr.color AS riesgo_color, e.puntaje_riesgo,
                       (SELECT COUNT(*) FROM apoderados a WHERE a.estudiante_id = e.id) AS tiene_apoderado
                FROM estudiantes e
                LEFT JOIN matriculas m ON m.estudiante_id = e.id AND m.id = (
                    SELECT m2.id FROM matriculas m2 WHERE m2.estudiante_id = e.id
                    ORDER BY m2.periodo_id DESC LIMIT 1
                )
                LEFT JOIN grados g ON g.id = m.grado_id
                LEFT JOIN secciones s ON s.id = m.seccion_id
                LEFT JOIN niveles_riesgo nr ON nr.id = e.nivel_riesgo_id
                WHERE 1=1';
        $params = [];
        if ($busqueda !== '') {
            $sql .= ' AND (e.codigo LIKE ? OR e.dni LIKE ? OR e.nombres LIKE ? OR e.apellidos LIKE ?)';
            $b = "%$busqueda%";
            $params[] = $b; $params[] = $b; $params[] = $b; $params[] = $b;
        }
        if ($gradoId > 0) { $sql .= ' AND m.grado_id = ?'; $params[] = $gradoId; }
        if ($seccionId > 0) { $sql .= ' AND m.seccion_id = ?'; $params[] = $seccionId; }
        if ($periodoId) { $sql .= ' AND m.periodo_id = ?'; $params[] = $periodoId; }
        if ($estado === '') {} elseif ($estado === 'activo') $sql .= ' AND e.estado = 1';
        elseif ($estado === 'inactivo') $sql .= ' AND e.estado = 0';
        $sql .= ' ORDER BY ' . $orden . ' LIMIT ' . (int)$limite . ' OFFSET ' . (int)$offset;
        return Database::select($sql, $params);
    }

    public static function contarFiltros(string $busqueda = '', int $gradoId = 0, int $seccionId = 0, ?int $periodoId = null, string $estado = ''): int
    {
        $sql = 'SELECT COUNT(DISTINCT e.id) AS total
                FROM estudiantes e
                LEFT JOIN matriculas m ON m.estudiante_id = e.id AND m.id = (
                    SELECT m2.id FROM matriculas m2 WHERE m2.estudiante_id = e.id ORDER BY m2.periodo_id DESC LIMIT 1
                )
                WHERE 1=1';
        $params = [];
        if ($busqueda !== '') {
            $sql .= ' AND (e.codigo LIKE ? OR e.dni LIKE ? OR e.nombres LIKE ? OR e.apellidos LIKE ?)';
            $b = "%$busqueda%"; $params[] = $b; $params[] = $b; $params[] = $b; $params[] = $b;
        }
        if ($gradoId > 0) { $sql .= ' AND m.grado_id = ?'; $params[] = $gradoId; }
        if ($seccionId > 0) { $sql .= ' AND m.seccion_id = ?'; $params[] = $seccionId; }
        if ($periodoId) { $sql .= ' AND m.periodo_id = ?'; $params[] = $periodoId; }
        if ($estado === 'activo') $sql .= ' AND e.estado = 1';
        elseif ($estado === 'inactivo') $sql .= ' AND e.estado = 0';
        return (int)(Database::selectOne($sql, $params)['total'] ?? 0);
    }

    /** Ficha completa de un estudiante */
    public static function ficha(int $id): ?array
    {
        return Database::selectOne(
            'SELECT e.*, a.nombres AS apo_nombres, a.apellidos AS apo_apellidos, a.dni AS apo_dni,
                    a.telefono AS apo_telefono, a.parentesco AS apo_parentesco, a.ocupacion AS apo_ocupacion,
                    nr.nombre AS riesgo_nombre, nr.color AS riesgo_color
             FROM estudiantes e
             LEFT JOIN apoderados a ON a.estudiante_id = e.id
             LEFT JOIN niveles_riesgo nr ON nr.id = e.nivel_riesgo_id
             WHERE e.id = ?', [$id]
        );
    }

    public static function matriculaActiva(int $estudianteId, ?int $periodoId = null): ?array
    {
        $sql = 'SELECT m.*, g.nombre AS grado_nombre, s.nombre AS seccion_nombre, s.turno
                FROM matriculas m
                INNER JOIN grados g ON g.id = m.grado_id
                INNER JOIN secciones s ON s.id = m.seccion_id
                WHERE m.estudiante_id = ?';
        $params = [$estudianteId];
        if ($periodoId) { $sql .= ' AND m.periodo_id = ?'; $params[] = $periodoId; }
        $sql .= ' ORDER BY m.periodo_id DESC LIMIT 1';
        return Database::selectOne($sql, $params);
    }

    public static function proximoCodigo(): string
    {
        $anio = setting_val('anio_lectivo', date('Y'));
        $fila = Database::selectOne(
            "SELECT MAX(CAST(SUBSTRING_INDEX(codigo, '-', -1) AS UNSIGNED)) AS max FROM estudiantes WHERE codigo LIKE ?",
            [$anio . '-%']
        );
        $nuevo = (int)($fila['max'] ?? 0) + 1;
        return $anio . '-' . str_pad((string)$nuevo, 4, '0', STR_PAD_LEFT);
    }
}