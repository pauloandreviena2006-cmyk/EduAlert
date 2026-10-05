<?php
/**
 * EduAlert JJP - app/Models/Docente.php
 */

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

class Docente extends Model
{
    protected string $tabla = 'docentes';

    public static function listar(string $busqueda = '', int $limite = 1000): array
    {
        $sql = 'SELECT d.*, u.email AS user_email, u.estado AS user_estado
                FROM docentes d
                LEFT JOIN users u ON u.id = d.user_id
                WHERE 1=1';
        $params = [];
        if ($busqueda !== '') {
            $sql .= ' AND (d.nombres LIKE ? OR d.apellidos LIKE ? OR d.dni LIKE ? OR d.codigo LIKE ?)';
            $b = "%$busqueda%";
            $params[] = $b; $params[] = $b; $params[] = $b; $params[] = $b;
        }
        $sql .= ' ORDER BY d.apellidos ASC LIMIT ' . (int)$limite;
        return Database::select($sql, $params);
    }

    public static function cursosAsignados(int $docenteId, ?int $periodoId = null): array
    {
        $sql = 'SELECT dc.*, c.nombre AS curso_nombre, s.nombre AS seccion_nombre, s.turno, g.nombre AS grado_nombre
                FROM docentes_cursos dc
                INNER JOIN cursos c ON c.id = dc.curso_id
                INNER JOIN secciones s ON s.id = dc.seccion_id
                INNER JOIN grados g ON g.id = s.grado_id
                WHERE dc.docente_id = ?';
        $params = [$docenteId];
        if ($periodoId) { $sql .= ' AND dc.periodo_id = ?'; $params[] = $periodoId; }
        $sql .= ' ORDER BY g.orden, c.nombre';
        return Database::select($sql, $params);
    }
}