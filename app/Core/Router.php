<?php
/**
 * EduAlert JJP - app/Core/Router.php
 * Enrutador: carga rutas definidas en routes/web.php y despacha
 */

namespace App\Core;

class Router
{
    private array $rutas = [];

    public function __construct()
    {
        $this->cargarRutas();
    }

    private function cargarRutas(): void
    {
        $archivo = BASE_PATH . '/routes/web.php';
        if (file_exists($archivo)) {
            $rutas = require $archivo;
            $this->rutas = is_array($rutas) ? $rutas : [];
        }
    }

    /** Obtiene la URL actual sin query string */
    private function urlActual(): string
    {
        $url = trim($_GET['url'] ?? '', '/');
        if ($url === '' && !empty($_SERVER['PATH_INFO'])) {
            $url = trim($_SERVER['PATH_INFO'], '/');
        }
        if (function_exists('route_url_strip')) {
            $url = route_url_strip($url);
        }
        return $url;
    }

    public function despachar(): void
    {
        $url = $this->urlActual();
        $metodo = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        // Las rutas pueden definir un método concreto o '*' (cualquiera)
        foreach ($this->rutas as $ruta) {
            $patronRuta = trim($ruta['url'], '/');
            if (!$this->coincide($patronRuta, $url)) continue;

            if (($ruta['metodo'] ?? '*') !== '*' && strtoupper($ruta['metodo']) !== $metodo) {
                continue;
            }

            $parametros = $this->extraerParametros($patronRuta, $url);
            $this->ejecutar($ruta, $parametros);
            return;
        }

        // Ruta no encontrada
        $this->error404();
    }

    private function coincide(string $patron, string $url): bool
    {
        if ($patron === '' ) return $url === '';
        $patronRegex = preg_replace('/\{([a-zA-Z_]+)\}/', '([^/]+)', $patron);
        return (bool)preg_match('#^' . $patronRegex . '$#', $url);
    }

    private function extraerParametros(string $patron, string $url): array
    {
        $nombres = [];
        preg_match_all('/\{([a-zA-Z_]+)\}/', $patron, $nombres);
        $patronRegex = preg_replace('/\{([a-zA-Z_]+)\}/', '([^/]+)', $patron);
        preg_match('#^' . $patronRegex . '$#', $url, $valores);
        array_shift($valores);
        $resultado = [];
        foreach ($nombres[1] as $i => $nombre) {
            $resultado[$nombre] = $valores[$i] ?? null;
        }
        return $resultado;
    }

    private function ejecutar(array $ruta, array $parametros): void
    {
        try {
            // Middleware de instalación
            if (($ruta['instalacion'] ?? false) !== true && !$this->sistemaInstalado()) {
                redirect('instalacion');
            }

            [$controlador, $accion] = explode('@', $ruta['handler']);
            $claseControlador = 'App\\Controllers\\' . $controlador;

            if (!class_exists($claseControlador)) {
                $this->error404();
                return;
            }

            // Middleware de autenticación
            if (($ruta['auth'] ?? true) && !Auth::check()) {
                $_SESSION['_redirect_after_login'] = url($this->urlActual());
                flash_set('error', 'Debe iniciar sesión para acceder al sistema.');
                redirect('login');
            }

            // Middleware de permisos
            if (($ruta['permiso'] ?? null) && !Auth::can($ruta['permiso'])) {
                $this->error403();
                return;
            }

            // Middleware CSRF en POST
            if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
                $token = $_POST['_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
                if (!hash_equals(csrf_token(), (string)$token)) {
                    http_response_code(419);
                    if ($this->esPeticionAjax()) {
                        json_response(['error' => 'La sesión expiró o el token de seguridad no es válido. Recargue la página.'], 419);
                    }
                    flash_set('error', 'Token de seguridad inválido. Intente nuevamente.');
                    redirect('login');
                }
            }

            $controladorObj = new $claseControlador();
            if (!method_exists($controladorObj, $accion)) {
                $this->error404();
                return;
            }

            call_user_func_array([$controladorObj, $accion], array_values($parametros));
        } catch (\Exception $e) {
            log_error('Error en ruta: ' . $e->getMessage() . ' | ' . $e->getFile() . ':' . $e->getLine());
            if (APP_DEBUG) {
                throw $e;
            }
            $this->error500();
        }
    }

    private function sistemaInstalado(): bool
    {
        return file_exists(STORAGE_PATH . '/.instalado');
    }

    private function esPeticionAjax(): bool
    {
        return (($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest') ||
               str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json');
    }

    public static function error404(): void
    {
        http_response_code(404);
        view('errores/404', ['page_title' => 'Página no encontrada'], 'guest');
        exit;
    }

    public static function error403(): void
    {
        http_response_code(403);
        view('errores/403', ['page_title' => 'Acceso denegado'], 'main');
        exit;
    }

    public static function error500(): void
    {
        http_response_code(500);
        view('errores/500', ['page_title' => 'Error interno'], 'guest');
        exit;
    }
}