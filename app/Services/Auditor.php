<?php
/**
 * EduAlert JJP - app/Services/Auditor.php
 * Registro de auditoría de acciones importantes del sistema
 */

namespace App\Services;

use App\Core\Auth;
use App\Core\Database;
use App\Models\Auditoria;

class Auditor
{
    public static function registrar(string $modulo, string $accion, string $descripcion): void
    {
        $userId = Auth::check() ? (int)($_SESSION['usuario_id'] ?? 0) : null;
        Auditoria::crear([
            'user_id' => $userId ?: null,
            'modulo' => $modulo,
            'accion' => $accion,
            'descripcion' => mb_substr($descripcion, 0, 250),
            'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
        ]);
    }
}