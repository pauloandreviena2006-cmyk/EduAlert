<?php
/**
 * EduAlert JJP - resources/views/auth/olvide.php
 */
?>
<div class="auth-card">
    <div class="auth-body">
        <div class="auth-logo">
            <div class="brand-icon" style="width:52px;height:52px"><i class="bi bi-key"></i></div>
        </div>
        <div class="auth-title" style="font-size:1.3rem">Recuperar <span>contraseña</span></div>
        <div class="auth-subtitle mb-3">Ingrese su correo para generar un enlace de recuperación</div>

        <?php if (!empty($enlace ?? null)): ?>
            <div class="alert alert-success">
                <i class="bi bi-check-circle me-2"></i>
                <b>Enlace generado</b> (entorno local de tesis — en producción se enviaría por correo).
            </div>
            <div class="mb-3">
                <label class="form-label">Tu enlace de recuperación</label>
                <div class="input-group">
                    <input type="text" class="form-control" readonly value="<?= e($enlace) ?>" id="enlaceRecuperacion">
                    <button type="button" class="btn btn-accent" onclick="navigator.clipboard.writeText(document.getElementById('enlaceRecuperacion').value);this.textContent='¡Copiado!';setTimeout(()=>this.textContent='Copiar',2000)">Copiar</button>
                </div>
                <div class="fs-8 text-muted mt-1">Abre el enlace para establecer una nueva contraseña.</div>
            </div>
            <a href="<?= url('login') ?>" class="btn btn-primary w-100 py-2">
                <i class="bi bi-arrow-left me-2"></i>Volver al inicio de sesión
            </a>
        <?php else: ?>
            <form method="post" action="<?= url('olvide') ?>">
                <?= csrf_field() ?>
                <div class="mb-3">
                    <label class="form-label" for="email">Correo electrónico</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                        <input type="email" class="form-control" id="email" name="email" required placeholder="usuario@edualert.test">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary w-100 py-2">
                    <i class="bi bi-envelope-arrow-up me-2"></i>Generar enlace
                </button>
                <a href="<?= url('login') ?>" class="btn btn-link w-100 mt-2 fs-7 text-decoration-none">
                    <i class="bi bi-arrow-left me-1"></i>Volver al inicio de sesión
                </a>
            </form>
        <?php endif; ?>
    </div>
    <div class="auth-footer">EduAlert JJP · Alerta Temprana Académica</div>
</div>