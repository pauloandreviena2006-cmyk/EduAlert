<?php
/**
 * EduAlert JJP - app/Controllers/PasswordController.php
 * Recuperación y restablecimiento de contraseña
 */

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Models\Usuario;

class PasswordController extends Controller
{
    public function olvideForm(): void
    {
        $this->vista('auth/olvide', ['page_title' => 'Recuperar contraseña'], 'guest');
    }

    public function olvide(): void
    {
        $email = strtolower(trim((string)($_POST['email'] ?? '')));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            flash_set('error', 'Ingrese un correo electrónico válido.');
            redirect('olvide');
        }
        $usuario = Usuario::porEmail($email);
        $token = bin2hex(random_bytes(32));
        Database::insert('password_resets', ['email' => $email, 'token' => $token]);
        // En entorno de tesis (localhost) mostramos el enlace directamente.
        // En producción se enviaría por correo electrónico (PHPMailer/mail()).
        $link = url('restablecer/' . $token);
        log_error('Solicitud de recuperación para ' . $email . ' -> ' . $link);
        if ($usuario) {
            $this->vista('auth/olvide', [
                'page_title' => 'Recuperar contraseña',
                'enlace' => $link,
                'email' => $email,
            ], 'guest');
        } else {
            flash_set('error', 'No encontramos una cuenta con ese correo electrónico.');
            redirect('olvide');
        }
    }

    public function resetForm(string $token): void
    {
        $fila = Database::selectOne(
            'SELECT * FROM password_resets WHERE token = ? AND usado = 0 AND created_at > DATE_SUB(NOW(), INTERVAL 2 HOUR)',
            [$token]
        );
        if (!$fila) {
            flash_set('error', 'El enlace de recuperación no es válido o ha expirado.');
            redirect('olvide');
        }
        $this->vista('auth/reset', ['page_title' => 'Nueva contraseña', 'token' => $token], 'guest');
    }

    public function reset(string $token): void
    {
        $fila = Database::selectOne(
            'SELECT * FROM password_resets WHERE token = ? AND usado = 0 AND created_at > DATE_SUB(NOW(), INTERVAL 2 HOUR)',
            [$token]
        );
        if (!$fila) {
            flash_set('error', 'El enlace de recuperación no es válido o ha expirado.');
            redirect('olvide');
        }
        $password = (string)($_POST['password'] ?? '');
        $confirmacion = (string)($_POST['password_confirmation'] ?? '');
        if (strlen($password) < 8) {
            flash_set('error', 'La contraseña debe tener al menos 8 caracteres.');
            redirect('restablecer/' . $token);
        }
        if ($password !== $confirmacion) {
            flash_set('error', 'Las contraseñas no coinciden.');
            redirect('restablecer/' . $token);
        }
        $usuario = Usuario::porEmail($fila['email']);
        if (!$usuario) {
            flash_set('error', 'No se encontró la cuenta de usuario.');
            redirect('olvide');
        }
        Usuario::actualizar((int)$usuario['id'], ['password' => password_hash($password, PASSWORD_BCRYPT)]);
        Database::update('password_resets', ['usado' => 1], 'id = ?', [1 => $fila['id']]);
        auditoria('Acceso', 'Cambio de contraseña', 'Contraseña restablecida para ' . $usuario['email']);
        flash_set('success', 'Contraseña actualizada correctamente. Ya puede iniciar sesión.');
        redirect('login');
    }
}