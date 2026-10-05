<?php
/**
 * EduAlert JJP - public/index.php
 * Front controller del sistema
 */

// Redirigir a instalación si está presente
if (file_exists(__DIR__ . '/../instalar.php') && !file_exists(__DIR__ . '/../storage/.instalado')) {
    // El instalador web standalone se puede usar; dejamos pasar al instalador integrado
}

require_once __DIR__ . '/../config/config.php';
require_once APP_PATH . '/helpers.php';

use App\Core\Auth;
use App\Core\Database;
use App\Core\Router;

// Configurar base de datos
Database::configurar([
    'host' => DB_HOST,
    'port' => DB_PORT,
    'nombre' => DB_NAME,
    'user' => DB_USER,
    'pass' => DB_PASS,
]);

// Iniciar sesión y restaurar "recordar sesión"
Auth::inicioSesion();
try {
    Auth::restaurarDesdeCookie();
} catch (\Throwable $e) {
    // Si la BD aún no existe (instalación pendiente), ignorar silenciosamente
    log_error('Recordar sesión omitido: ' . $e->getMessage());
}

// Despachar rutas
$router = new Router();
$router->despachar();