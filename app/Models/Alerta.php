<?php
/**
 * EduAlert JJP - app/Models/Alerta.php
 */

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

class Alerta extends Model
{
    protected string $tabla = 'alertas';

    public const ESTADOS = ['Nueva', 'En revisión', 'En seguimiento', 'Atendida', 'Cerrada'];

    public static function listar(string $busqueda = '', ?int $nivelId = null, string $estado = '', int $gradoId = 0, ?int $periodoId = null, int $limite = 1000): array
    {
        $sql = 'SELECT al.*, e.codigo, e.dni, e.nombres, e.apellidos, e.estado AS estudiante_estado,
                       nr.nombre AS riesgo_nombre, nr.color AS riesgo_color,
                       g.nombre AS grado_nombre, s.nombre AS seccion_nombre,
                       u.nombres AS resp_nombres, u.apellidos AS resp_apellidos,
                       (SELECT COUNT(*) FROM seguimientos seg WHERE seg.alerta_id = al.id) AS total_seguimientos,
                       (SELECT COUNT(*) FROM alertas_motivos am WHERE am.alerta_id = al.id) AS total_motivos,
                       p.nombre AS periodo_nombre, p.anio AS periodo_anio
                FROM alertas al
                INNER JOIN estudiantes e ON e.id = al.estudiante_id
                INNER JOIN niveles_riesgo nr ON nr.id = al.nivel_riesgo_id
                LEFT JOIN matriculas m ON m.estudiante_id = al.estudiante_id AND m.periodo_id = al.periodo_id
                LEFT JOIN grados g ON g.id = m.grado_id
                LEFT JOIN secciones s ON s.id = m.seccion_id
                LEFT JOIN users u ON u.id = al.responsable_id
                LEFT JOIN periodos p ON p.id = al.periodo_id
                WHERE 1=1';
        $params = [];
        if ($busqueda !== '') {
            $sql .= ' AND (e.codigo LIKE ? OR e.dni LIKE ? OR e.nombres LIKE ? OR e.apellidos LIKE ?)';
            $b = "%$busqueda%"; $params[] = $b; $params[] = $b; $params[] = $b; $params[] = $b;
        }
        if ($nivelId) { $sql .= ' AND al.nivel_riesgo_id = ?'; $params[] = $nivelId; }
        if ($estado !== '' && $estado !== 'todos') { $sql .= ' AND al.estado = ?'; $params[] = $estado; }
        if ($gradoId > 0) { $sql .= ' AND m.grado_id = ?'; $params[] = $gradoId; }
        if ($periodoId) { $sql .= ' AND al.periodo_id = ?'; $params[] = $periodoId; }
        $sql .= ' ORDER BY
                    CASE al.estado WHEN "Nueva" THEN 0 WHEN "En revisión" THEN 1 WHEN "En seguimiento" THEN 2 WHEN "Atendida" THEN 3 ELSE 4 END,
                    al.fecha_generacion DESC
                  LIMIT ' . (int)$limite;
        return Database::select($sql, $params);
    }

    public static function detalle(int $id): ?array
    {
        return Database::selectOne(
            'SELECT al.*, e.codigo, e.dni, e.nombres, e.apellidos, e.sexo, e.fecha_nacimiento,
                    nr.nombre AS riesgo_nombre, nr.color AS riesgo_color, nr.descripcion AS riesgo_descripcion,
                    g.nombre AS grado_nombre, s.nombre AS seccion_nombre, s.turno,
                    u.nombres AS resp_nombres, u.apellidos AS resp_apellidos,
                    p.nombre AS periodo_nombre, p.anio AS periodo_anio, p.id AS periodo_id
             FROM alertas al
             INNER JOIN estudiantes e ON e.id = al.estudiante_id
             INNER JOIN niveles_riesgo nr ON nr.id = al.nivel_riesgo_id
             LEFT JOIN matriculas m ON m.estudiante_id = al.estudiante_id AND m.periodo_id = al.periodo_id
             LEFT JOIN grados g ON g.id = m.grado_id
             LEFT JOIN secciones s ON s.id = m.seccion_id
             LEFT JOIN users u ON u.id = al.responsable_id
             LEFT JOIN periodos p ON p.id = al.periodo_id
             WHERE al.id = ?', [$id]
        );
    }

    public static function motivos(int $alertaId): array
    {
        return Database::select('SELECT * FROM alertas_motivos WHERE alerta_id = ?', [$alertaId]);
    }

    public static function abiertaDeEstudiantePeriodo(int $estudianteId, int $periodoId): ?array
    {
        return Database::selectOne(
            'SELECT * FROM alertas WHERE estudiante_id = ? AND periodo_id = ?
             AND estado IN ("Nueva","En revisión","En seguimiento") ORDER BY id DESC LIMIT 1',
            [$estudianteId, $periodoId]
        );
    }

    public static function conteoPorEstado(?int $periodoId = null): array
    {
        $sql = 'SELECT estado, COUNT(*) AS total FROM alertas WHERE 1=1';
        $params = [];
        if ($periodoId) { $sql .= ' AND periodo_id = ?'; $params[] = $periodoId; }
        $sql .= ' GROUP BY estado';
        $filas = Database::select($sql, $params);
        $resultado = ['Nueva' => 0, 'En revisión' => 0, 'En seguimiento' => 0, 'Atendida' => 0, 'Cerrada' => 0];
        foreach ($filas as $f) $resultado[$f['estado']] = (int)$f['total'];
        return $resultado;
    }
}