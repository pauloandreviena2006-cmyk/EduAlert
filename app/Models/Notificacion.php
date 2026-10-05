<?php
/**
 * EduAlert JJP - app/Models/Notificacion.php
 */

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

class Notificacion extends Model
{
    protected string $tabla = 'notificaciones';

    public static function paraUsuario(int $userId, int $limite = 10): array
    {
        return Database::select(
            'SELECT * FROM notificaciones WHERE user_id = ? ORDER BY created_at DESC LIMIT ' . (int)$limite,
            [$userId]
        );
    }

    public static function noLeidas(int $userId): int
    {
        return (int)(Database::selectOne('SELECT COUNT(*) AS total FROM notificaciones WHERE user_id = ? AND leido = 0', [$userId])['total'] ?? 0);
    }

    public static function marcarLeida(int $id, int $userId): void
    {
        Database::update('notificaciones', ['leido' => 1], 'id = ? AND user_id = ?', [1 => $id, 2 => $userId]);
    }

    public static function marcarTodasLeidas(int $userId): void
    {
        Database::update('notificaciones', ['leido' => 1], 'user_id = ?', [1 => $userId]);
    }
}