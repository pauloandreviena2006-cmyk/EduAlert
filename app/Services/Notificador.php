<?php
/**
 * EduAlert JJP - app/Services/Notificador.php
 * Sistema de notificaciones internas
 */

namespace App\Services;

use App\Core\Database;
use App\Models\Notificacion;
use App\Models\Usuario;

class Notificador
{
    /** Crea una notificación para un listado de roles */
    public static function crearPorRoles(array $roles, string $titulo, string $mensaje, string $tipo = 'sistema', ?string $url = null): int
    {
        $usuarios = Database::select(
            'SELECT u.id FROM users u INNER JOIN roles r ON r.id = u.rol_id
             WHERE u.estado = 1 AND r.slug IN (' . implode(',', array_fill(0, count($roles), '?')) . ')',
            $roles
        );
        $total = 0;
        foreach ($usuarios as $u) {
            Notificacion::crear([
                'user_id' => (int)$u['id'],
                'tipo' => $tipo,
                'titulo' => $titulo,
                'mensaje' => $mensaje,
                'url' => $url,
                'leido' => 0,
            ]);
            $total++;
        }
        return $total;
    }

    public static function crearParaUsuario(int $userId, string $titulo, string $mensaje, string $tipo = 'sistema', ?string $url = null): int
    {
        return Notificacion::crear([
            'user_id' => $userId,
            'tipo' => $tipo,
            'titulo' => $titulo,
            'mensaje' => $mensaje,
            'url' => $url,
            'leido' => 0,
        ]);
    }
}