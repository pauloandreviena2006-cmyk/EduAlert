<?php
/**
 * EduAlert JJP - app/Controllers/NotificacionesController.php
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Notificacion;

class NotificacionesController extends Controller
{
    public function index(): void
    {
        $userId = current_user_id();
        $this->vista('notificaciones/index', [
            'page_title' => 'Notificaciones',
            'active' => 'notificaciones',
            'notificaciones' => Notificacion::paraUsuario($userId, 100),
            'no_leidas' => Notificacion::noLeidas($userId),
        ]);
    }

    public function marcar(string $id): void
    {
        Notificacion::marcarLeida((int)$id, (int)current_user_id());
        $this->json(['ok' => true]);
    }

    public function marcarTodas(): void
    {
        Notificacion::marcarTodasLeidas((int)current_user_id());
        flash_set('success', 'Todas las notificaciones fueron marcadas como leídas.');
        redirect('notificaciones');
    }
}