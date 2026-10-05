<?php
/**
 * EduAlert JJP - resources/views/auth/login.php
 * Página de inicio de sesión profesional
 */
?>
<div class="auth-card">
    <div class="auth-body">
        <div class="auth-logo">
            <div class="brand-icon"><i class="bi bi-bell-fill"></i></div>
        </div>
        <div class="auth-title">EduAlert <span>JJP</span></div>
        <div class="auth-subtitle">Sistema de Alerta Temprana Académica</div>
        <div class="auth-institution">
            <i class="bi bi-building me-1"></i>
            Institución Educativa Juan Jiménez Pimentel · Tarapoto
        </div>

        <form method="post" action="<?= url('login') ?>" novalidate>
            <?= csrf_field() ?>
            <div class="mb-3">
                <label class="form-label" for="email">Correo electrónico</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                    <input type="email" class="form-control" id="email" name="email"
                           value="<?= e($_SESSION['_old']['email'] ?? '') ?>" placeholder="usuario@edualert.test"
                           required autofocus>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label" for="password">Contraseña</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                    <input type="password" class="form-control" id="password" name="password"
                           placeholder="••••••••" required>
                    <span class="input-group-text password-toggle" data-target="#password" title="Mostrar/ocultar">
                        <i class="bi bi-eye"></i>
                    </span>
                </div>
            </div>
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="recordar" value="1" id="recordar">
                    <label class="form-check-label fs-7" for="recordar">Recordar sesión</label>
                </div>
                <a href="<?= url('olvide') ?>" class="fs-7 text-accent text-decoration-none">¿Olvidaste tu contraseña?</a>
            </div>
            <button type="submit" class="btn btn-primary w-100 py-2">
                <i class="bi bi-box-arrow-in-right me-2"></i>Iniciar sesión
            </button>
        </form>

        <div class="demo-accounts">
            <div class="fs-7 fw-semibold text-muted mb-2">
                <i class="bi bi-info-circle me-1"></i>Cuentas de demostración (tesis) — clic para llenar
            </div>
            <button type="button" class="btn-demo" onclick="llenarDemo('admin')">
                <span><i class="bi bi-person-gear me-2"></i>Administrador</span>
                <span class="role-tag" style="background:#0f2744">admin@edualert.test</span>
            </button>
            <button type="button" class="btn-demo" onclick="llenarDemo('director')">
                <span><i class="bi bi-person-badge me-2"></i>Director</span>
                <span class="role-tag" style="background:#7c3aed">director@edualert.test</span>
            </button>
            <button type="button" class="btn-demo" onclick="llenarDemo('docente')">
                <span><i class="bi bi-person-workspace me-2"></i>Docente</span>
                <span class="role-tag" style="background:#0891b2">docente@edualert.test</span>
            </button>
            <button type="button" class="btn-demo" onclick="llenarDemo('tutor')">
                <span><i class="bi bi-person-heart me-2"></i>Tutor</span>
                <span class="role-tag" style="background:#d97706">tutor@edualert.test</span>
            </button>
            <button type="button" class="btn-demo" onclick="llenarDemo('auxiliar')">
                <span><i class="bi bi-person-check me-2"></i>Auxiliar</span>
                <span class="role-tag" style="background:#059669">auxiliar@edualert.test</span>
            </button>
        </div>
    </div>
    <div class="auth-footer">
        Todas las cuentas demo usan la contraseña: <b>12345678</b>
    </div>
</div>