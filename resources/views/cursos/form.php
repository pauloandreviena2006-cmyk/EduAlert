<?php
/**
 * EduAlert JJP - recursos/views/cursos/form.php
 */
$modo = $modo ?? 'crear';
$esEditar = $modo === 'editar';
$c = $curso ?? null;
$titulo = $esEditar ? 'Editar curso' : 'Nuevo curso';
$rutaPost = $esEditar ? 'cursos/editar/' . $c['id'] : 'cursos/crear';
$areas = ['Comunicación', 'Matemática', 'Ciencia y Tecnología', 'Ciencias Sociales', 'Educación Física', 'Inglés', 'Arte y Cultura', 'Educación Religiosa', 'Educación para el Trabajo'];
?>
<div class="row g-3 mb-4 align-items-center">
    <div class="col">
        <h2 class="fw-bold text-primary mb-1"><i class="bi bi-journal-bookmark me-2 text-accent"></i><?= $titulo ?></h2>
    </div>
    <div class="col-auto">
        <a href="<?= url('cursos') ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Volver</a>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-12 col-lg-7">
        <form method="post" action="<?= url($rutaPost) ?>" class="card border-0 shadow-sm">
            <?= csrf_field() ?>
            <div class="card-header bg-white py-3 fw-semibold"><i class="bi bi-book me-2 text-accent"></i>Datos del curso</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label">Nombre del curso *</label>
                        <input type="text" name="nombre" class="form-control" maxlength="100" placeholder="Ej. Matemática" value="<?= old('nombre', $c['nombre'] ?? '') ?>" required>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">Área curricular</label>
                        <select name="area" class="form-select">
                            <option value="">Seleccione...</option>
                            <?php $__area = old('area', $c['area'] ?? ''); ?>
                            <?php foreach ($areas as $a): ?>
                                <option value="<?= $a ?>" <?= $__area === $a ? 'selected' : '' ?>><?= $a ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">Descripción</label>
                        <input type="text" name="descripcion" class="form-control" maxlength="200" placeholder="Breve descripción" value="<?= old('descripcion', $c['descripcion'] ?? '') ?>">
                    </div>
                </div>
            </div>
            <div class="card-footer bg-white text-end">
                <a href="<?= url('cursos') ?>" class="btn btn-outline-secondary me-2">Cancelar</a>
                <button type="submit" class="btn btn-accent"><i class="bi bi-check2-circle me-1"></i><?= $esEditar ? 'Guardar cambios' : 'Registrar curso' ?></button>
            </div>
        </form>
    </div>
</div>