<?php
/**
 * ============================================================
 *  EduAlert JJP - Sistema de Alerta Temprana Académica
 *  Institución Educativa Juan Jiménez Pimentel - Tarapoto
 *  ------------------------------------------------------------
 *  Archivo: config/config.php
 *  Descripción: Configuración global del sistema
 * ============================================================
 */

// ---- Rutas del sistema ----
define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');
define('VIEWS_PATH', BASE_PATH . '/resources/views');
define('STORAGE_PATH', BASE_PATH . '/storage');
define('LOG_PATH', STORAGE_PATH . '/logs');

// ---- URL base (se autodetecta o se edita manualmente) ----
// Ejemplo para XAMPP en htdocs:  http://localhost/edualert/public
// Si usas el servidor integrado de PHP: http://localhost:8000
if (!defined('BASE_URL')) {
    $__script = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/public'));
    // Quita "/index.php" o "/server.php" del final si existiera
    if (in_array(basename($__script), ['public', 'index.php', 'server.php']) === false && $__script !== '/') {
        $__script = dirname($__script);
    }
    if (isset($_SERVER['HTTP_HOST']) && isset($_SERVER['SCRIPT_NAME'])) {
        $__scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        define('BASE_URL', $__scheme . '://' . $_SERVER['HTTP_HOST'] . rtrim($__script, '/'));
    } else {
        define('BASE_URL', 'http://localhost/edualert/public');
    }
}

// ---- Base de datos (XAMPP por defecto) ----
define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3306');
define('DB_NAME', 'edualert_jjp');
define('DB_USER', 'root');
define('DB_PASS', '');

// ---- Aplicación ----
define('APP_NAME', 'EduAlert JJP');
define('APP_INSTITUCION', 'Institución Educativa Juan Jiménez Pimentel');
define('APP_VERSION', '1.0.0');
define('APP_KEY', 'cambiar-esta-clave-por-una-segura-2026'); // Clave para cifrado de confirmaciones
define('APP_DEBUG', false);   // En producción: false. En desarrollo: true muestra errores
define('SESSION_NAME', 'edualert_session');
define('SESSION_LIFETIME', 7200); // 2 horas

// ---- Zona horaria ----
date_default_timezone_set('America/Lima');

// ---- Errores ----
error_reporting(E_ALL);
if (defined('APP_DEBUG') && APP_DEBUG) {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
} else {
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
    ini_set('error_log', LOG_PATH . '/app.log');
}