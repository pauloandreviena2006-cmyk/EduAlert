<?php
/**
 * EduAlert JJP - recursos/views/secciones/form.php
 */
$modo = $modo ?? 'crear';
$esEditar = $modo === 'editar';
$s = $seccion ?? null;
$titulo = $esEditar ? 'Editar sección' : 'Nueva sección';
$rutaPost = $esEditar ? 'secciones/editar/' . $s['id'] : 'secciones/crear';
?>
<div class="row g-3 mb-4 align-items-center">
    <div class="col">
        <h2 class="fw-bold text-primary mb-1"><i class="bi bi-layout-sidebar me-2 text-accent"></i><?= $titulo ?></h2>
    </div>
    <div class="col-auto">
        <a href="<?= url('secciones') ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Volver</a>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-12 col-lg-6">
        <form method="post" action="<?= url($rutaPost) ?>" class="card border-0 shadow-sm">
            <?= csrf_field() ?>
            <div class="card-header bg-white py-3 fw-semibold"><i class="bi bi-geo-alt me-2 text-accent"></i>Datos de la sección</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label">Grado *</label>
                        <select name="grado_id" class="form-select" required>
                            <option value="">Seleccione...</option>
                            <?php foreach ($grados as $g): ?>
                                <option value="<?= (int)$g['id'] ?>" <?= (int)old('grado_id', $s['grado_id'] ?? '') === (int)$g['id'] ? 'selected' : '' ?>><?= e($g['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">Letra / nombre *</label>
                        <select name="nombre" class="form-select" required>
                            <?php $__nombre = old('nombre', $s['nombre'] ?? ''); ?>
                            <?php foreach (['A', 'B', 'C', 'D', 'E'] as $letra): ?>
                                <option value="<?= $letra ?>" <?= $__nombre === $letra ? 'selected' : '' ?>>Sección "<?= $letra ?>"</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">Turno</label>
                        <select name="turno" class="form-select">
                            <?php $__turno = old('turno', $s['turno'] ?? 'Mañana'); ?>
                            <option value="Mañana" <?= $__turno === 'Mañana' ? 'selected' : '' ?>>Mañana</option>
                            <option value="Tarde" <?= $__turno === 'Tarde' ? 'selected' : '' ?>>Tarde</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-white text-end">
                <a href="<?= url('secciones') ?>" class="btn btn-outline-secondary me-2">Cancelar</a>
                <button type="submit" class="btn btn-accent"><i class="bi bi-check2-circle me-1"></i><?= $esEditar ? 'Guardar cambios' : 'Registrar sección' ?></button>
            </div>
        </form>
    </div>
</div>