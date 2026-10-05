<?php
/**
 * EduAlert JJP - recursos/views/docentes/form.php
 */
$modo = $modo ?? 'crear';
$esEditar = $modo === 'editar';
$d = $docente ?? null;
$titulo = $esEditar ? 'Editar docente' : 'Nuevo docente';
$rutaPost = $esEditar ? 'docentes/editar/' . $d['id'] : 'docentes/crear';
$especialidades = ['Matemática', 'Comunicación', 'Ciencia y Tecnología', 'Historia y Geografía', 'Educación Física', 'Inglés', 'Arte y Cultura', 'Educación Religiosa', 'Tutoría'];
?>
<div class="row g-3 mb-4 align-items-center">
    <div class="col">
        <h2 class="fw-bold text-primary mb-1"><i class="bi bi-person-plus-fill me-2 text-accent"></i><?= $titulo ?></h2>
        <p class="text-muted mb-0 fs-7">Registro del personal docente</p>
    </div>
    <div class="col-auto">
        <a href="<?= url('docentes') ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Volver</a>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <form method="post" action="<?= url($rutaPost) ?>" class="card border-0 shadow-sm">
            <?= csrf_field() ?>
            <div class="card-header bg-white py-3 fw-semibold"><i class="bi bi-person-vcard me-2 text-accent"></i>Datos del docente</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-12 col-md-4">
                        <label class="form-label">DNI *</label>
                        <input type="text" name="dni" class="form-control" maxlength="8" inputmode="numeric" pattern="[0-9]{8}" value="<?= old('dni', $d['dni'] ?? '') ?>" required>
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label">Nombres *</label>
                        <input type="text" name="nombres" class="form-control" maxlength="80" value="<?= old('nombres', $d['nombres'] ?? '') ?>" required>
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label">Apellidos *</label>
                        <input type="text" name="apellidos" class="form-control" maxlength="80" value="<?= old('apellidos', $d['apellidos'] ?? '') ?>" required>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">Especialidad</label>
                        <select name="especialidad" class="form-select">
                            <option value="">Seleccione...</option>
                            <?php $__esp = old('especialidad', $d['especialidad'] ?? ''); ?>
                            <?php foreach ($especialidades as $esp): ?>
                                <option value="<?= $esp ?>" <?= $__esp === $esp ? 'selected' : '' ?>><?= $esp ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">Teléfono</label>
                        <input type="text" name="telefono" class="form-control" maxlength="15" placeholder="9xx xxx xxx" value="<?= old('telefono', $d['telefono'] ?? '') ?>">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Correo electrónico</label>
                        <input type="email" name="email" class="form-control" placeholder="docente@correo.com" value="<?= old('email', $d['email'] ?? '') ?>">
                    </div>
                </div>
            </div>
            <div class="card-footer bg-white text-end">
                <a href="<?= url('docentes') ?>" class="btn btn-outline-secondary me-2">Cancelar</a>
                <button type="submit" class="btn btn-accent px-4"><i class="bi bi-check2-circle me-1"></i><?= $esEditar ? 'Guardar cambios' : 'Registrar docente' ?></button>
            </div>
        </form>
    </div>
</div>