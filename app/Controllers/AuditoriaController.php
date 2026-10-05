<?php
/**
 * EduAlert JJP - app/Controllers/AuditoriaController.php
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Auditoria;

class AuditoriaController extends Controller
{
    public function index(): void
    {
        $modulo = (string)($_GET['modulo'] ?? '');
        $busqueda = trim((string)($_GET['q'] ?? ''));
        $this->vista('auditoria/index', [
            'page_title' => 'Auditoría del sistema',
            'active' => 'auditoria',
            'registros' => Auditoria::filtrar($modulo, $busqueda),
            'modulos' => Auditoria::modulos(),
            'modulo' => $modulo,
            'q' => $busqueda,
        ]);
    }
}