<?php
/**
 * EduAlert JJP - app/Controllers/AuthController.php
 * Autenticación: login, logout
 */

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;

class AuthController extends Controller
{
    public function loginForm(): void
    {
        if (Auth::check()) {
            redirect('dashboard');
        }
        $this->vista('auth/login', [
            'page_title' => 'Iniciar sesión',
            'mensaje' => '',
        ], 'guest');
    }

    public function login(): void
    {
        $email = trim((string)($_POST['email'] ?? ''));
        $password = (string)($_POST['password'] ?? '');
        $recordar = isset($_POST['recordar']) && $_POST['recordar'] === '1';

        if ($email === '' || $password === '') {
            flash_set('error', 'Ingrese su correo electrónico y contraseña.');
            $this->volverLogin($email);
        }

        $resultado = Auth::intentar($email, $password, $recordar);

        if (!$resultado['ok']) {
            flash_set('error', $resultado['mensaje']);
            auditoria('Acceso', 'Login fallido', "Intento de acceso fallido para: $email");
            $this->volverLogin($email);
        }

        $usuario = $resultado['usuario'];
        auditoria('Acceso', 'Login', 'Inicio de sesión: ' . $usuario['nombres'] . ' ' . $usuario['apellidos'] . ' (' . $usuario['rol_nombre'] . ')');

        // Redirección según rol
        $destino = $_SESSION['_redirect_after_login'] ?? null;
        unset($_SESSION['_redirect_after_login']);
        if ($destino && str_starts_with($destino, BASE_URL)) {
            header('Location: ' . $destino);
            exit;
        }
        redirect('dashboard');
    }

    public function logout(): void
    {
        if (Auth::check()) {
            $u = Auth::user();
            auditoria('Acceso', 'Logout', 'Cierre de sesión: ' . ($u['nombres'] ?? '') . ' ' . ($u['apellidos'] ?? ''));
        }
        Auth::logout();
        flash_set('success', 'Sesión cerrada correctamente.');
        redirect('login');
    }

    private function volverLogin(string $email): void
    {
        $_SESSION['_old']['email'] = $email;
        redirect('login');
    }
}