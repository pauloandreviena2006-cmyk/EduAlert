<?php
/**
 * ============================================================
 *  EduAlert JJP - Sistema de Alerta Temprana Académica
 *  ------------------------------------------------------------
 *  Archivo: app/helpers.php
 *  Descripción: Funciones auxiliares globales del sistema
 * ============================================================
 */

/** SPL Autoloader para clases del sistema */
spl_autoload_register(function ($clase) {
    $prefijos = ['App\\' => APP_PATH . '/'];
    foreach ($prefijos as $prefijo => $ruta) {
        if (strpos($clase, $prefijo) === 0) {
            $archivo = $ruta . str_replace('\\', '/', substr($clase, strlen($prefijo))) . '.php';
            if (file_exists($archivo)) {
                require_once $archivo;
                return;
            }
        }
    }
    // Carga de clases en app/Core, app/Models, app/Controllers, app/Services, app/Middleware
    foreach (['Core', 'Middleware', 'Models', 'Controllers', 'Services'] as $dir) {
        $archivo = APP_PATH . '/' . $dir . '/' . $clase . '.php';
        if (file_exists($archivo)) {
            require_once $archivo;
            return;
        }
    }
});

/** Escapa HTML (protección XSS) */
function e($valor): string
{
    return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
}

/** Devuelve una URL absoluta del sistema */
function url(string $ruta = ''): string
{
    return BASE_URL . '/' . ltrim($ruta, '/');
}

/** Redirige a una ruta del sistema */
function redirect(string $ruta): void
{
    header('Location: ' . url($ruta));
    exit;
}

/** Devuelve ruta de un asset */
function asset(string $ruta): string
{
    return url('assets/' . ltrim($ruta, '/'));
}

/** Renderiza una vista dentro de un layout */
function view(string $vista, array $datos = [], string $layout = 'main'): void
{
    $datos['__vista'] = $vista;
    extract($datos, EXTR_SKIP);
    $__archivoVista = VIEWS_PATH . '/' . $vista . '.php';
    if (!file_exists($__archivoVista)) {
        http_response_code(500);
        echo 'Vista no encontrada: ' . e($vista);
        return;
    }
    ob_start();
    require $__archivoVista;
    $__contenido = ob_get_clean();
    $__layoutFile = VIEWS_PATH . '/layouts/' . $layout . '.php';
    if (file_exists($__layoutFile)) {
        require $__layoutFile;
    } else {
        echo $__contenido;
    }
}

/** Responde JSON */
function json_response($datos, int $codigo = 200): void
{
    http_response_code($codigo);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($datos, JSON_UNESCAPED_UNICODE);
    exit;
}

/** Errores antiguos (compatibilidad para vistas) */
function old(string $campo, $defecto = ''): string
{
    return isset($_SESSION['_old'][$campo]) ? e($_SESSION['_old'][$campo]) : e($defecto);
}

/** Mensajes flash: set + get */
function flash_set(string $tipo, string $mensaje): void
{
    $_SESSION['_flash'][$tipo][] = $mensaje;
}
function flash_get(string $tipo): array
{
    $valores = $_SESSION['_flash'][$tipo] ?? [];
    unset($_SESSION['_flash'][$tipo]);
    return $valores;
}
function flash_all(): array
{
    $valores = $_SESSION['_flash'] ?? [];
    unset($_SESSION['_flash']);
    return $valores;
}

/** Token CSRF */
function csrf_token(): string
{
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf'];
}
function csrf_field(): string
{
    return '<input type="hidden" name="_token" value="' . e(csrf_token()) . '">';
}

/** Usuario autenticado */
function current_user(): ?array
{
    return \App\Core\Auth::user();
}
function current_user_id(): ?int
{
    $u = current_user();
    return $u ? (int)$u['id'] : null;
}

/** Verifica si tiene permiso */
if (!function_exists('can')) {
    function can(string $permiso): bool
    {
        return \App\Core\Auth::can($permiso);
    }
}
/** Verifica rol */
function has_role(string $rol): bool
{
    $u = current_user();
    return $u && ($u['rol_slug'] ?? '') === $rol;
}

/** Formatos */
function f_fecha(?string $fecha, string $formato = 'd/m/Y'): string
{
    if (!$fecha) return '-';
    $t = strtotime($fecha);
    return $t ? date($formato, $t) : '-';
}
function f_fecha_hora(?string $fecha): string
{
    return f_fecha($fecha, 'd/m/Y H:i');
}
function f_numero($valor, int $decimales = 2): string
{
    return number_format((float)$valor, $decimales, '.', ' ');
}
function f_porcentaje($valor): string
{
    return number_format((float)$valor, 1, '.', ' ') . '%';
}

/** Registro en log */
function log_error(string $mensaje, array $contexto = []): void
{
    if (!is_dir(LOG_PATH)) @mkdir(LOG_PATH, 0777, true);
    $linea = '[' . date('Y-m-d H:i:s') . '] ' . $mensaje . ' ' . json_encode($contexto, JSON_UNESCAPED_UNICODE) . PHP_EOL;
    @file_put_contents(LOG_PATH . '/app.log', $linea, FILE_APPEND);
}

/** Auditoría rápida */
function auditoria(string $modulo, string $accion, string $descripcion): void
{
    try {
        \App\Services\Auditor::registrar($modulo, $accion, $descripcion);
    } catch (\Throwable $e) {
        log_error('Auditoría falló: ' . $e->getMessage());
    }
}

/** Notificación rápida */
function notificar(array $roles, string $titulo, string $mensaje, string $tipo = 'alerta', ?string $url = null): void
{
    try {
        \App\Services\Notificador::crear($roles, $titulo, $mensaje, $tipo, $url);
    } catch (\Throwable $e) {
        log_error('Notificación falló: ' . $e->getMessage());
    }
}

/** Parámetro de configuración */
function setting(string $clave, $defecto = null)
{
    return \App\Models\Configuracion::get($clave, $defecto);
}

/** Parámetro de configuración (alias global: funciona en cualquier namespace por fallback a \setting_val) */
if (!function_exists('setting_val')) {
    function setting_val(string $clave, $defecto = null)
    {
        return \App\Models\Configuracion::get($clave, $defecto);
    }
}

/** Menú lateral según permisos */
function menu_items(): array
{
    $items = [
        'dashboard' => ['label' => 'Dashboard', 'icon' => 'bi-speedometer2', 'url' => 'dashboard', 'permiso' => 'dashboard.ver'],
        'estudiantes' => ['label' => 'Estudiantes', 'icon' => 'bi-people', 'url' => 'estudiantes', 'permiso' => 'estudiantes.ver'],
        'docentes' => ['label' => 'Docentes', 'icon' => 'bi-person-workspace', 'url' => 'docentes', 'permiso' => 'docentes.ver'],
        'academico' => [
            'label' => 'Gestión Académica',
            'icon' => 'bi-journal-bookmark',
            'url' => '#',
            'permiso' => null,
            'sub' => [
                ['label' => 'Periodos', 'url' => 'periodos', 'permiso' => 'periodos.gestion'],
                ['label' => 'Grados', 'url' => 'grados', 'permiso' => 'grados.gestion'],
                ['label' => 'Secciones', 'url' => 'secciones', 'permiso' => 'secciones.gestion'],
                ['label' => 'Cursos', 'url' => 'cursos', 'permiso' => 'cursos.gestion'],
                ['label' => 'Matrículas', 'url' => 'matriculas', 'permiso' => 'matriculas.gestion'],
                ['label' => 'Calificaciones', 'url' => 'calificaciones', 'permiso' => 'calificaciones.gestion'],
                ['label' => 'Asistencia', 'url' => 'asistencia', 'permiso' => 'asistencia.gestion'],
            ],
        ],
        'alertas' => ['label' => 'Alertas Tempranas', 'icon' => 'bi-bell-fill', 'url' => 'alertas', 'permiso' => 'alertas.ver'],
        'seguimiento' => ['label' => 'Seguimiento', 'icon' => 'bi-clipboard2-pulse', 'url' => 'seguimiento', 'permiso' => 'seguimiento.gestion'],
        'reportes' => ['label' => 'Reportes', 'icon' => 'bi-file-earmark-bar-graph', 'url' => 'reportes', 'permiso' => 'reportes.ver'],
        'notificaciones' => ['label' => 'Notificaciones', 'icon' => 'bi-chat-dots', 'url' => 'notificaciones', 'permiso' => 'notificaciones.ver'],
        'usuarios' => ['label' => 'Usuarios', 'icon' => 'bi-person-gear', 'url' => 'usuarios', 'permiso' => 'usuarios.gestion'],
        'auditoria' => ['label' => 'Auditoría', 'icon' => 'bi-shield-check', 'url' => 'auditoria', 'permiso' => 'auditoria.ver'],
        'configuracion' => ['label' => 'Configuración', 'icon' => 'bi-gear', 'url' => 'configuracion', 'permiso' => 'configuracion.gestion'],
    ];

    $menu = [];
    foreach ($items as $clave => $item) {
        // Permiso null = siempre visible (ej. grupo académico se filtra por subitems)
        if (isset($item['sub'])) {
            $subs = array_filter($item['sub'], fn($s) => can($s['permiso']));
            // Si el usuario es admin o al menos hay un subitem permitido
            if (has_role('admin') || count($subs) > 0) {
                $item['sub'] = array_values($subs);
                $menu[$clave] = $item;
            }
            continue;
        }
        if ($item['permiso'] === null || can($item['permiso']) || has_role('admin')) {
            $menu[$clave] = $item;
        }
    }
    return $menu;
}

/** Convertir "bimestre" a texto corto */
function periodo_corto(string $nombre): string
{
    return $nombre;
}

/** Nombre completo de una persona desde dos campos */
function nombre_completo(array $fila, $prefijo = ''): string
{
    $n = $prefijo ? $fila[$prefijo . '_nombres'] ?? '' : $fila['nombres'] ?? '';
    $a = $prefijo ? $fila[$prefijo . '_apellidos'] ?? '' : $fila['apellidos'] ?? '';
    if (!$n && !$a) return $fila['nombre'] ?? '-';
    return trim(($n . ' ' . $a));
}

/** Badge de estado genérico */
function badge_estado(string $estado): string
{
    $colores = [
        'Activo' => 'success', 'activo' => 'success', 'Activa' => 'success',
        'Inactivo' => 'secondary', 'inactivo' => 'secondary',
        'Nueva' => 'info', 'En revisión' => 'warning', 'En seguimiento' => 'primary',
        'Atendida' => 'success', 'Cerrada' => 'secondary',
        'Abierta' => 'success', 'Cerrado' => 'secondary',
        'Abierto' => 'success',
        'Presente' => 'success', 'Falta' => 'danger', 'Tardanza' => 'warning', 'Justificado' => 'secondary',
        'Bajo' => 'success', 'Medio' => 'warning', 'Alto' => 'danger',
        'Bimestre' => 'primary',
    ];
    $color = $colores[$estado] ?? 'secondary';
    return '<span class="badge bg-' . $color . '-subtle text-' . $color . '-emphasis border border-' . $color . '-subtle rounded-pill">' . e($estado) . '</span>';
}