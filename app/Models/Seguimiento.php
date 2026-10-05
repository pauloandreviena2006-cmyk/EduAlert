<?php
/**
 * EduAlert JJP - app/Models/Seguimiento.php
 */

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

class Seguimiento extends Model
{
    protected string $tabla = 'seguimientos';

    public static function deAlerta(int $alertaId): array
    {
        return Database::select(
            'SELECT seg.*, u.nombres AS resp_nombres, u.apellidos AS resp_apellidos
             FROM seguimientos seg
             LEFT JOIN users u ON u.id = seg.responsable_id
             WHERE seg.alerta_id = ? ORDER BY seg.fecha DESC, seg.id DESC', [$alertaId]
        );
    }

    public static function listar(string $busqueda = '', string $estado = '', int $limite = 1000): array
    {
        $sql = 'SELECT seg.*, e.nombres, e.apellidos, e.codigo, u.nombres AS resp_nombres, u.apellidos AS resp_apellidos,
                       al.estado AS alerta_estado, al.id AS alerta_id, nr.color AS riesgo_color, nr.nombre AS riesgo_nombre
                FROM seguimientos seg
                INNER JOIN alertas al ON al.id = seg.alerta_id
                INNER JOIN estudiantes e ON e.id = al.estudiante_id
                LEFT JOIN users u ON u.id = seg.responsable_id
                LEFT JOIN niveles_riesgo nr ON nr.id = al.nivel_riesgo_id
                WHERE 1=1';
        $params = [];
        if ($busqueda !== '') {
            $sql .= ' AND (e.nombres LIKE ? OR e.apellidos LIKE ? OR e.codigo LIKE ?)';
            $b = "%$busqueda%"; $params[] = $b; $params[] = $b; $params[] = $b;
        }
        if ($estado !== '' && $estado !== 'todos') { $sql .= ' AND seg.estado = ?'; $params[] = $estado; }
        $sql .= ' ORDER BY seg.fecha DESC, seg.id DESC LIMIT ' . (int)$limite;
        return Database::select($sql, $params);
    }
}