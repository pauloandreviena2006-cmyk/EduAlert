<?php
/**
 * EduAlert JJP - app/Models/Auditoria.php
 */

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

class Auditoria extends Model
{
    protected string $tabla = 'auditoria';

    public static function listar(int $limite = 500): array
    {
        return Database::select(
            'SELECT au.*, u.nombres, u.apellidos, r.nombre AS rol_nombre
             FROM auditoria au
             LEFT JOIN users u ON u.id = au.user_id
             LEFT JOIN roles r ON r.id = u.rol_id
             ORDER BY au.created_at DESC LIMIT ' . (int)$limite
        );
    }

    public static function filtrar(string $modulo = '', string $busqueda = '', int $limite = 500): array
    {
        $sql = 'SELECT au.*, u.nombres, u.apellidos, r.nombre AS rol_nombre
                FROM auditoria au
                LEFT JOIN users u ON u.id = au.user_id
                LEFT JOIN roles r ON r.id = u.rol_id
                WHERE 1=1';
        $params = [];
        if ($modulo !== '' && $modulo !== 'todos') { $sql .= ' AND au.modulo = ?'; $params[] = $modulo; }
        if ($busqueda !== '') {
            $sql .= ' AND (au.descripcion LIKE ? OR au.accion LIKE ? OR u.nombres LIKE ? OR u.apellidos LIKE ?)';
            $b = "%$busqueda%"; $params[] = $b; $params[] = $b; $params[] = $b; $params[] = $b;
        }
        $sql .= ' ORDER BY au.created_at DESC LIMIT ' . (int)$limite;
        return Database::select($sql, $params);
    }

    public static function modulos(): array
    {
        return array_column(Database::select('SELECT DISTINCT modulo FROM auditoria ORDER BY modulo'), 'modulo');
    }
}