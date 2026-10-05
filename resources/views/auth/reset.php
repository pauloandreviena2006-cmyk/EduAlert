<?php
/**
 * EduAlert JJP - resources/views/auth/reset.php
 */
?>
<div class="auth-card">
    <div class="auth-body">
        <div class="auth-logo">
            <div class="brand-icon" style="width:52px;height:52px"><i class="bi bi-shield-lock"></i></div>
        </div>
        <div class="auth-title" style="font-size:1.3rem">Nueva <span>contraseña</span></div>
        <div class="auth-subtitle mb-3">Ingrese su nueva contraseña de acceso</div>

        <form method="post" action="<?= url('restablecer/' . e($token)) ?>">
            <?= csrf_field() ?>
            <div class="mb-3">
                <label class="form-label" for="password">Nueva contraseña</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                    <input type="password" class="form-control" id="password" name="password" minlength="8" required placeholder="Mínimo 8 caracteres">
                    <span class="input-group-text password-toggle" data-target="#password"><i class="bi bi-eye"></i></span>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label" for="password2">Confirmar contraseña</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                    <input type="password" class="form-control" id="password2" name="password_confirmation" required placeholder="Repita la contraseña">
                </div>
            </div>
            <button type="submit" class="btn btn-primary w-100 py-2">
                <i class="bi bi-check-circle me-2"></i>Guardar nueva contraseña
            </button>
        </form>
    </div>
    <div class="auth-footer">EduAlert JJP · Alerta Temprana Académica</div>
</div>