<?php
/**
 * EduAlert JJP - app/Controllers/PerfilController.php
 * Perfil del usuario autenticado
 */

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Models\Usuario;

class PerfilController extends Controller
{
    public function index(): void
    {
        $usuario = Usuario::conRol((int)Auth::user()['id']);
        $this->vista('perfil/index', ['page_title' => 'Mi perfil', 'perfil' => $usuario]);
    }

    public function actualizar(): void
    {
        $id = (int)Auth::user()['id'];
        $nombres = trim((string)($_POST['nombres'] ?? ''));
        $apellidos = trim((string)($_POST['apellidos'] ?? ''));
        $telefono = trim((string)($_POST['telefono'] ?? ''));
        $email = strtolower(trim((string)($_POST['email'] ?? '')));

        if ($nombres === '' || $apellidos === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            flash_set('error', 'Verifique los datos del perfil.');
            redirect('perfil');
        }
        $duplicado = Database::selectOne('SELECT id FROM users WHERE email = ? AND id <> ?', [$email, $id]);
        if ($duplicado) {
            flash_set('error', 'El correo electrónico ya está en uso por otro usuario.');
            redirect('perfil');
        }
        Usuario::actualizar($id, [
            'nombres' => $nombres,
            'apellidos' => $apellidos,
            'telefono' => $telefono,
            'email' => $email,
        ]);
        // Refrescar sesión
        $usuario = Usuario::conRol($id);
        Auth::setUsuario($usuario);
        $_SESSION['permisos'] = Auth::cargarPermisos((int)$usuario['rol_id']);
        auditoria('Perfil', 'Actualización', 'Perfil actualizado: ' . $nombres . ' ' . $apellidos);
        flash_set('success', 'Perfil actualizado correctamente.');
        redirect('perfil');
    }

    public function password(): void
    {
        $id = (int)Auth::user()['id'];
        $actual = (string)($_POST['password_actual'] ?? '');
        $nueva = (string)($_POST['password'] ?? '');
        $confirmacion = (string)($_POST['password_confirmation'] ?? '');

        $usuario = Usuario::buscar($id);
        if (!$usuario || !password_verify($actual, $usuario['password'])) {
            flash_set('error', 'La contraseña actual es incorrecta.');
            redirect('perfil');
        }
        if (strlen($nueva) < 8) {
            flash_set('error', 'La nueva contraseña debe tener al menos 8 caracteres.');
            redirect('perfil');
        }
        if ($nueva !== $confirmacion) {
            flash_set('error', 'Las contraseñas no coinciden.');
            redirect('perfil');
        }
        Usuario::actualizar($id, ['password' => password_hash($nueva, PASSWORD_BCRYPT)]);
        auditoria('Perfil', 'Cambio de contraseña', 'El usuario cambió su contraseña');
        flash_set('success', 'Contraseña cambiada correctamente.');
        redirect('perfil');
    }
}