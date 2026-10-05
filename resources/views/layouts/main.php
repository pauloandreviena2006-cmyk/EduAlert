<?php
/**
 * EduAlert JJP - resources/views/layouts/main.php
 * Layout principal con sidebar, topbar y contenido
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($page_title ?? 'Sistema') ?> | EduAlert JJP</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🎓</text></svg>">
    <link rel="stylesheet" href="<?= asset('vendor/bootstrap/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= asset('vendor/icons/bootstrap-icons.min.css') ?>">
    <link rel="stylesheet" href="<?= asset('vendor/datatables/dataTables.bootstrap5.min.css') ?>">
    <link rel="stylesheet" href="<?= asset('vendor/sweetalert2/sweetalert2.min.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
</head>
<body>
<?php $__menu = menu_items(); $__usuario = current_user(); ?>
<div class="layout">
    <!-- ===== SIDEBAR ===== -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <div class="brand-icon"><i class="bi bi-bell-fill"></i></div>
            <div class="brand-text">
                <div class="brand-name">EduAlert <span>JJP</span></div>
                <div class="brand-sub">Alerta Temprana Académica</div>
            </div>
            <button class="btn-close d-lg-none" id="sidebarClose" type="button"></button>
        </div>
        <div class="sidebar-institution">
            <i class="bi bi-building"></i>
            <span>I.E. Juan Jiménez Pimentel</span>
        </div>
        <nav class="sidebar-nav">
            <?php foreach ($__menu as $clave => $item): ?>
                <?php if (isset($item['sub'])): ?>
                    <div class="nav-group">
                        <div class="nav-group-title"><i class="bi <?= e($item['icon']) ?>"></i> <span><?= e($item['label']) ?></span></div>
                        <?php foreach ($item['sub'] as $sub): ?>
                            <a href="<?= url($sub['url']) ?>" class="nav-link <?= ($_GET['url'] ?? '') === '' ? '' : (str_starts_with($_GET['url'], $sub['url']) ? 'active' : '') ?>">
                                <span class="link-dot"></span><?= e($sub['label']) ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <a href="<?= url($item['url']) ?>" class="nav-link <?= (($_GET['url'] ?? '') === $item['url']) || (($_GET['url'] ?? '') !== '' && str_starts_with($_GET['url'], $item['url'] . '/')) ? 'active' : '' ?>">
                        <i class="bi <?= e($item['icon']) ?>"></i>
                        <span><?= e($item['label']) ?></span>
                        <?php if ($item['url'] === 'alertas'): ?>
                            <?php $__alertasNuevas = \App\Models\Alerta::contar("estado = 'Nueva'"); if ($__alertasNuevas > 0): ?>
                                <span class="badge-nav"><?= $__alertasNuevas ?></span>
                            <?php endif; ?>
                        <?php endif; ?>
                    </a>
                <?php endif; ?>
            <?php endforeach; ?>
        </nav>
        <div class="sidebar-footer">
            <div class="version">v<?= e(APP_VERSION) ?> · Tesis</div>
        </div>
    </aside>
    <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

    <!-- ===== CONTENIDO ===== -->
    <div class="main">
        <!-- TOPBAR -->
        <header class="topbar">
            <button class="btn-icon" id="sidebarToggle"><i class="bi bi-list"></i></button>
            <div class="topbar-title">
                <h1><?= e($page_title ?? '') ?></h1>
                <?php if (!empty($periodo ?? null)): ?>
                    <span class="periodo-badge"><i class="bi bi-calendar3"></i> <?= e($periodo['nombre'] . ' ' . $periodo['anio']) ?></span>
                <?php endif; ?>
            </div>
            <div class="topbar-actions">
                <div class="dropdown">
                    <?php $__noLeidas = \App\Models\Notificacion::noLeidas((int)current_user_id()); ?>
                    <button class="btn-icon position-relative" data-bs-toggle="dropdown">
                        <i class="bi bi-bell"></i>
                        <?php if ($__noLeidas > 0): ?>
                            <span class="notif-dot"><?= $__noLeidas > 9 ? '9+' : $__noLeidas ?></span>
                        <?php endif; ?>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end notif-menu">
                        <div class="notif-header">
                            <span>Notificaciones</span>
                            <form method="post" action="<?= url('notificaciones/marcar-todas') ?>" class="d-inline">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-sm btn-link">Marcar todas</button>
                            </form>
                        </div>
                        <?php $__notifs = \App\Models\Notificacion::paraUsuario((int)current_user_id(), 6); ?>
                        <?php if (empty($__notifs)): ?>
                            <div class="notif-empty"><i class="bi bi-check2-circle"></i> No tienes notificaciones</div>
                        <?php else: ?>
                            <?php foreach ($__notifs as $__n): ?>
                                <a class="notif-item <?= (int)$__n['leido'] === 0 ? 'unread' : '' ?>" href="<?= $__n['url'] ? e($__n['url']) : url('notificaciones') ?>">
                                    <div class="notif-icon"><i class="bi bi-<?= $__n['tipo'] === 'alerta' ? 'bell-fill text-danger' : 'info-circle-fill text-primary' ?>"></i></div>
                                    <div class="notif-body">
                                        <div class="notif-title"><?= e($__n['titulo']) ?></div>
                                        <div class="notif-msg"><?= e(mb_substr($__n['mensaje'] ?? '', 0, 90)) ?>...</div>
                                        <div class="notif-time"><?= f_fecha_hora($__n['created_at']) ?></div>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                            <a class="notif-footer" href="<?= url('notificaciones') ?>">Ver todas las notificaciones</a>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="dropdown">
                    <button class="btn-icon" data-bs-toggle="dropdown"><i class="bi bi-person-circle fs-5"></i></button>
                    <div class="dropdown-menu dropdown-menu-end user-menu">
                        <div class="user-menu-header">
                            <div class="avatar"><?= e(mb_substr($__usuario['nombres'] ?? 'U', 0, 1)) ?><?= e(mb_substr($__usuario['apellidos'] ?? '', 0, 1)) ?></div>
                            <div>
                                <div class="fw-semibold"><?= e(($__usuario['nombres'] ?? '') . ' ' . ($__usuario['apellidos'] ?? '')) ?></div>
                                <div class="text-muted small"><?= e($__usuario['rol_nombre'] ?? '') ?></div>
                            </div>
                        </div>
                        <a class="dropdown-item" href="<?= url('perfil') ?>"><i class="bi bi-person me-2"></i>Mi perfil</a>
                        <a class="dropdown-item text-danger" href="<?= url('logout') ?>"><i class="bi bi-box-arrow-right me-2"></i>Cerrar sesión</a>
                    </div>
                </div>
            </div>
        </header>

        <!-- FLASH MESSAGES -->
        <?php foreach (flash_all() as $__tipo => $__mensajes): ?>
            <?php foreach ($__mensajes as $__msg): ?>
                <div class="flash-alert flash-<?= e($__tipo) ?>" role="alert">
                    <i class="bi bi-<?= $__tipo === 'success' ? 'check-circle' : ($__tipo === 'error' ? 'x-circle' : 'info-circle') ?>"></i>
                    <?= e($__msg) ?>
                    <button type="button" class="btn-close" onclick="this.parentElement.remove()"></button>
                </div>
            <?php endforeach; ?>
        <?php endforeach; ?>

        <!-- CONTENIDO -->
        <main class="content">
            <?= $__contenido ?>
        </main>

        <footer class="footer">
            EduAlert JJP v<?= e(APP_VERSION) ?> · Sistema de Alerta Temprana Académica · <?= e(APP_INSTITUCION) ?> · <?= date('Y') ?>
        </footer>
    </div>
</div>

<!-- Modales globales -->
<div class="modal fade" id="modalConfirm" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalConfirmTitle">Confirmar acción</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="modalConfirmBody">¿Está seguro?</div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="modalConfirmBtn">Sí, confirmar</button>
            </div>
        </div>
    </div>
</div>

<script src="<?= asset('vendor/jquery/jquery.min.js') ?>"></script>
<script src="<?= asset('vendor/bootstrap/bootstrap.bundle.min.js') ?>"></script>
<script src="<?= asset('vendor/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= asset('vendor/datatables/dataTables.bootstrap5.min.js') ?>"></script>
<script src="<?= asset('vendor/chartjs/chart.umd.min.js') ?>"></script>
<script src="<?= asset('vendor/sweetalert2/sweetalert2.min.js') ?>"></script>
<script>window.BASE_URL = '<?= e(BASE_URL) ?>';</script>
<script src="<?= asset('js/app.js') ?>"></script>
</body>
</html>