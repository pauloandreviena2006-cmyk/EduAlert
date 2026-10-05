<?php
/**
 * EduAlert JJP - recursos/views/perfil/index.php
 * Perfil y contraseña del usuario autenticado
 */
$p = $perfil;
?>
<div class="row g-3 mb-4 align-items-center">
    <div class="col">
        <h2 class="fw-bold text-primary mb-1"><i class="bi bi-person-gear me-2 text-accent"></i>Mi perfil</h2>
        <p class="text-muted mb-0 fs-7">Datos de su cuenta de acceso</p>
    </div>
    <div class="col-auto">
        <a href="<?= url('') ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Volver al inicio</a>
    </div>
</div>

<div class="row g-4">
    <div class="col-12 col-lg-4">
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body">
                <div class="profile-avatar mx-auto mb-3" style="width:90px;height:90px;font-size:2rem"><?= e(mb_substr($p['nombres'], 0, 1) . mb_substr($p['apellidos'], 0, 1)) ?></div>
                <h4 class="fw-bold mb-1"><?= e($p['nombres'] . ' ' . $p['apellidos']) ?></h4>
                <div class="text-muted fs-7 mb-3"><?= e($p['email']) ?></div>
                <span class="badge bg-primary-subtle text-primary-emphasis rounded-pill fs-7"><i class="bi bi-person-badge me-1"></i><?= e($p['rol_nombre']) ?></span>
                <hr>
                <dl class="row text-start fs-7 mb-0">
                    <dt class="col-5 text-muted">Teléfono</dt><dd class="col-7"><?= e($p['telefono'] ?? '-') ?></dd>
                    <dt class="col-5 text-muted">Alta</dt><dd class="col-7"><?= f_fecha_hora($p['created_at'] ?? null) ?></dd>
                </dl>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 fw-semibold"><i class="bi bi-person-vcard me-2 text-accent"></i>Actualizar datos</div>
            <div class="card-body">
                <form method="post" action="<?= url('perfil') ?>">
                    <?= csrf_field() ?>
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label">Nombres *</label>
                            <input type="text" name="nombres" class="form-control" maxlength="80" value="<?= e($p['nombres'] ?? '') ?>" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Apellidos *</label>
                            <input type="text" name="apellidos" class="form-control" maxlength="80" value="<?= e($p['apellidos'] ?? '') ?>" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Correo electrónico *</label>
                            <input type="email" name="email" class="form-control" value="<?= e($p['email'] ?? '') ?>" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Teléfono</label>
                            <input type="text" name="telefono" class="form-control" maxlength="15" placeholder="9xx xxx xxx" value="<?= e($p['telefono'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="text-end mt-3">
                        <button type="submit" class="btn btn-accent px-4"><i class="bi bi-check2-circle me-1"></i>Guardar cambios</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 fw-semibold"><i class="bi bi-key me-2 text-accent"></i>Cambiar contraseña</div>
            <div class="card-body">
                <form method="post" action="<?= url('perfil/password') ?>">
                    <?= csrf_field() ?>
                    <div class="row g-3">
                        <div class="col-12 col-md-4">
                            <label class="form-label">Contraseña actual *</label>
                            <input type="password" name="password_actual" class="form-control" required autocomplete="current-password">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label">Nueva contraseña *</label>
                            <input type="password" name="password" class="form-control" minlength="8" required autocomplete="new-password">
                            <div class="form-text">Mínimo 8 caracteres.</div>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label">Confirmar nueva *</label>
                            <input type="password" name="password_confirmation" class="form-control" minlength="8" required autocomplete="new-password">
                        </div>
                    </div>
                    <div class="text-end mt-3">
                        <button type="submit" class="btn btn-outline-primary px-4"><i class="bi bi-shield-lock me-1"></i>Cambiar contraseña</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>