<?php
/**
 * EduAlert JJP - public/server.php
 * Router para el servidor integrado de PHP (desarrollo):
 *     php -S localhost:8000 -t public public/server.php
 */

// Servir archivos estáticos directamente
$ruta = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$archivo = __DIR__ . $ruta;
if ($ruta !== '/' && is_file($archivo)) {
    return false;
}

// Enrutar al front controller
$_SERVER['SCRIPT_NAME'] = '/index.php';
$_GET['url'] = ltrim($ruta, '/');
require __DIR__ . '/index.php';