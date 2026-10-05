<?php
/**
 * EduAlert JJP - app/Core/Auth.php
 * Autenticación, sesiones, roles y permisos
 */

namespace App\Core;

class Auth
{
    public static function inicioSesion(): void
    {
        $nombreSesion = defined('SESSION_NAME') ? SESSION_NAME : 'edualert_session';
        if (session_status() === PHP_SESSION_NONE) {
            session_name($nombreSesion);
            session_set_cookie_params([
                'lifetime' => defined('SESSION_LIFETIME') ? SESSION_LIFETIME : 7200,
                'path' => '/',
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
            session_start();
        }
    }

    public static function check(): bool
    {
        self::inicioSesion();
        return isset($_SESSION['usuario_id']);
    }

    public static function user(): ?array
    {
        self::inicioSesion();
        if (!isset($_SESSION['usuario_id'])) return null;
        if (isset($_SESSION['usuario'])) return $_SESSION['usuario'];
        return null;
    }

    /** Almacena los datos del usuario en sesión */
    public static function setUsuario(array $usuario): void
    {
        self::inicioSesion();
        $_SESSION['usuario_id'] = (int)$usuario['id'];
        $_SESSION['usuario'] = $usuario;
    }

    public static function logout(): void
    {
        self::inicioSesion();
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
        // Recordar sesión: eliminar cookie
        if (isset($_COOKIE['edualert_remember'])) {
            setcookie('edualert_remember', '', time() - 3600, '/');
        }
    }

    /** Verifica permiso (rol admin tiene todos) */
    public static function can(string $permiso): bool
    {
        $usuario = self::user();
        if (!$usuario) return false;
        if (($usuario['rol_slug'] ?? '') === 'admin') return true;

        // Permisos guardados en el usuario (JOIN con rol_permisos en login)
        $permisos = $_SESSION['permisos'] ?? [];
        return in_array($permiso, $permisos, true) || in_array('*', $permisos, true);
    }

    /** Carga los permisos del rol del usuario */
    public static function cargarPermisos(int $rolId): array
    {
        $filas = Database::select(
            'SELECT p.nombre FROM rol_permisos rp INNER JOIN permisos p ON p.id = rp.permiso_id WHERE rp.rol_id = ?',
            [$rolId]
        );
        $permisos = array_column($filas, 'nombre');
        if (in_array('*', $permisos, true)) {
            // Admin: todos los permisos
            $todos = Database::select('SELECT nombre FROM permisos');
            return array_column($todos, 'nombre');
        }
        return $permisos;
    }

    /** Intento de login con control de bloqueos */
    public static function intentar(string $email, string $password, bool $recordar = false): array
    {
        $email = strtolower(trim($email));
        $usuario = Database::selectOne('SELECT * FROM users WHERE email = ?', [$email]);

        if (!$usuario) {
            return ['ok' => false, 'mensaje' => 'Credenciales incorrectas. Verifique su correo y contraseña.'];
        }

        if ((int)$usuario['estado'] === 0) {
            return ['ok' => false, 'mensaje' => 'Su cuenta está desactivada. Contacte al administrador.'];
        }

        // Control de intentos fallidos
        $maxIntentos = (int)setting_val('max_intentos_login', 5);
        $minutosBloqueo = (int)setting_val('minutos_bloqueo', 15);
        if ((int)$usuario['intentos_fallidos'] >= $maxIntentos) {
            $bloqueado = strtotime((string)$usuario['bloqueado_hasta']);
            if ($bloqueado > time()) {
                $restante = ceil(($bloqueado - time()) / 60);
                return ['ok' => false, 'mensaje' => "Demasiados intentos fallidos. Cuenta bloqueada por $restante minuto(s)."];
            }
            // Reinicia contador
            Database::update('users', ['intentos_fallidos' => 0, 'bloqueado_hasta' => null], 'id = ?', [1 => $usuario['id']]);
        }

        if (!password_verify($password, $usuario['password'])) {
            Database::update('users', [
                'intentos_fallidos' => (int)$usuario['intentos_fallidos'] + 1,
                'bloqueado_hasta' => date('Y-m-d H:i:s', time() + $minutosBloqueo * 60),
            ], 'id = ?', [1 => $usuario['id']]);
            return ['ok' => false, 'mensaje' => 'Credenciales incorrectas. Verifique su correo y contraseña.'];
        }

        // Login exitoso
        Database::update('users', [
            'intentos_fallidos' => 0,
            'bloqueado_hasta' => null,
            'ultimo_acceso' => date('Y-m-d H:i:s'),
        ], 'id = ?', [1 => $usuario['id']]);

        $usuario = Database::selectOne(
            'SELECT u.*, r.nombre AS rol_nombre, r.slug AS rol_slug
             FROM users u INNER JOIN roles r ON r.id = u.rol_id WHERE u.id = ?',
            [$usuario['id']]
        );

        session_regenerate_id(true);
        self::setUsuario($usuario);
        $_SESSION['permisos'] = self::cargarPermisos((int)$usuario['rol_id']);

        if ($recordar) {
            $token = bin2hex(random_bytes(32));
            $hash = hash('sha256', $token);
            Database::update('users', ['remember_token' => $hash], 'id = ?', [1 => $usuario['id']]);
            setcookie('edualert_remember', $usuario['id'] . '|' . $token, time() + 30 * 24 * 3600, '/', '', false, true);
        }

        return ['ok' => true, 'usuario' => $usuario];
    }

    /** Restaura sesión por cookie de recordar */
    public static function restaurarDesdeCookie(): void
    {
        if (self::check()) return;
        if (empty($_COOKIE['edualert_remember'])) return;
        [$id, $token] = explode('|', $_COOKIE['edualert_remember'], 2);
        $usuario = Database::selectOne(
            'SELECT u.*, r.nombre AS rol_nombre, r.slug AS rol_slug
             FROM users u INNER JOIN roles r ON r.id = u.rol_id WHERE u.id = ?',
            [(int)$id]
        );
        if ($usuario && $usuario['remember_token'] && hash_equals((string)$usuario['remember_token'], hash('sha256', $token))
            && (int)$usuario['estado'] === 1) {
            session_regenerate_id(true);
            self::setUsuario($usuario);
            $_SESSION['permisos'] = self::cargarPermisos((int)$usuario['rol_id']);
        } else {
            setcookie('edualert_remember', '', time() - 3600, '/');
        }
    }
}

/** Acceso a settings evitando dependencia circular con helper setting() */
function setting_val(string $clave, $defecto = null)
{
    require_once APP_PATH . '/Models/Configuracion.php';
    return \App\Models\Configuracion::get($clave, $defecto);
}