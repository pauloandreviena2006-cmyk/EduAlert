<?php
/**
 * EduAlert JJP - app/Models/Usuario.php
 */

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

class Usuario extends Model
{
    protected string $tabla = 'users';

    public static function conRol(int $id): ?array
    {
        return Database::selectOne(
            'SELECT u.*, r.nombre AS rol_nombre, r.slug AS rol_slug
             FROM users u INNER JOIN roles r ON r.id = u.rol_id WHERE u.id = ?', [$id]
        );
    }

    public static function listarConRol(string $busqueda = '', int $rolId = 0): array
    {
        $sql = 'SELECT u.*, r.nombre AS rol_nombre, r.slug AS rol_slug
                FROM users u INNER JOIN roles r ON r.id = u.rol_id WHERE 1=1';
        $params = [];
        if ($busqueda !== '') {
            $sql .= ' AND (u.nombres LIKE ? OR u.apellidos LIKE ? OR u.email LIKE ?)';
            $params[] = "%$busqueda%"; $params[] = "%$busqueda%"; $params[] = "%$busqueda%";
        }
        if ($rolId > 0) {
            $sql .= ' AND u.rol_id = ?';
            $params[] = $rolId;
        }
        $sql .= ' ORDER BY u.nombres ASC';
        return Database::select($sql, $params);
    }

    public static function porEmail(string $email): ?array
    {
        return Database::selectOne('SELECT * FROM users WHERE email = ?', [strtolower(trim($email))]);
    }
}