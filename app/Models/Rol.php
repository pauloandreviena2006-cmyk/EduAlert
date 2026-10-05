<?php
/**
 * EduAlert JJP - app/Models/Rol.php
 */

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

class Rol extends Model
{
    protected string $tabla = 'roles';

    public static function conPermisos(int $rolId): array
    {
        return array_column(
            Database::select('SELECT p.nombre FROM rol_permisos rp INNER JOIN permisos p ON p.id=rp.permiso_id WHERE rp.rol_id=?', [$rolId]),
            'nombre'
        );
    }

    public static function reemplazarPermisos(int $rolId, array $permisos): void
    {
        Database::delete('rol_permisos', 'rol_id = ?', [$rolId]);
        foreach ($permisos as $p) {
            $permiso = Database::selectOne('SELECT id FROM permisos WHERE nombre = ?', [$p]);
            if ($permiso) {
                try { Database::insert('rol_permisos', ['rol_id' => $rolId, 'permiso_id' => $permiso['id']]); } catch (\Throwable $e) {}
            }
        }
    }
}