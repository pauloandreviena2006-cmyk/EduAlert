<?php
/**
 * EduAlert JJP - recursos/views/usuarios/form.php
 */
$modo = $modo ?? 'crear';
$esEditar = $modo === 'editar';
$u = $usuario ?? null;
$titulo = $esEditar ? 'Editar usuario' : 'Nuevo usuario';
$rutaPost = $esEditar ? 'usuarios/editar/' . $u['id'] : 'usuarios/crear';
?>
<div class="row g-3 mb-4 align-items-center">
    <div class="col">
        <h2 class="fw-bold text-primary mb-1"><i class="bi bi-person-plus-fill me-2 text-accent"></i><?= $titulo ?></h2>
        <p class="text-muted mb-0 fs-7">Cuenta de acceso al sistema</p>
    </div>
    <div class="col-auto">
        <a href="<?= url('usuarios') ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Volver</a>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-12 col-lg-7">
        <form method="post" action="<?= url($rutaPost) ?>" class="card border-0 shadow-sm">
            <?= csrf_field() ?>
            <div class="card-header bg-white py-3 fw-semibold"><i class="bi bi-person-vcard me-2 text-accent"></i>Datos de la cuenta</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <label class="form-label">Nombres *</label>
                        <input type="text" name="nombres" class="form-control" maxlength="80" value="<?= old('nombres', $u['nombres'] ?? '') ?>" required>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">Apellidos *</label>
                        <input type="text" name="apellidos" class="form-control" maxlength="80" value="<?= old('apellidos', $u['apellidos'] ?? '') ?>" required>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">Correo electrónico *</label>
                        <input type="email" name="email" class="form-control" placeholder="usuario@edualert.test" value="<?= old('email', $u['email'] ?? '') ?>" required>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">Teléfono</label>
                        <input type="text" name="telefono" class="form-control" maxlength="15" placeholder="9xx xxx xxx" value="<?= old('telefono', $u['telefono'] ?? '') ?>">
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">Rol *</label>
                        <select name="rol_id" class="form-select" required>
                            <option value="">Seleccione...</option>
                            <?php foreach ($roles as $r): ?>
                                <option value="<?= (int)$r['id'] ?>" <?= (int)old('rol_id', $u['rol_id'] ?? '') === (int)$r['id'] ? 'selected' : '' ?>><?= e($r['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label"><?= $esEditar ? 'Nueva contraseña (opcional)' : 'Contraseña *' ?></label>
                        <input type="password" name="password" class="form-control" minlength="8" placeholder="<?= $esEditar ? 'Dejar en blanco para mantener' : 'Mínimo 8 caracteres' ?>" <?= $esEditar ? '' : 'required' ?>>
                        <?php if ($esEditar): ?><div class="form-text">Solo se actualiza si escribe una nueva.</div><?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-white text-end">
                <a href="<?= url('usuarios') ?>" class="btn btn-outline-secondary me-2">Cancelar</a>
                <button type="submit" class="btn btn-accent px-4"><i class="bi bi-check2-circle me-1"></i><?= $esEditar ? 'Guardar cambios' : 'Registrar usuario' ?></button>
            </div>
        </form>
    </div>
</div>