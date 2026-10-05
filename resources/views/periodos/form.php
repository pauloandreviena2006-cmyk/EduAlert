<?php
/**
 * EduAlert JJP - recursos/views/periodos/form.php
 */
$modo = $modo ?? 'crear';
$esEditar = $modo === 'editar';
$p = $periodo ?? null;
$titulo = $esEditar ? 'Editar periodo' : 'Nuevo periodo';
$rutaPost = $esEditar ? 'periodos/editar/' . $p['id'] : 'periodos/crear';
$sugeridos = ['1er Bimestre', '2do Bimestre', '3er Bimestre', '4to Bimestre'];
?>
<div class="row g-3 mb-4 align-items-center">
    <div class="col">
        <h2 class="fw-bold text-primary mb-1"><i class="bi bi-calendar3 me-2 text-accent"></i><?= $titulo ?></h2>
    </div>
    <div class="col-auto">
        <a href="<?= url('periodos') ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Volver</a>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-12 col-lg-6">
        <form method="post" action="<?= url($rutaPost) ?>" class="card border-0 shadow-sm">
            <?= csrf_field() ?>
            <div class="card-header bg-white py-3 fw-semibold"><i class="bi bi-calendar2-week me-2 text-accent"></i>Datos del periodo</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <label class="form-label">Nombre *</label>
                        <input type="text" name="nombre" class="form-control" maxlength="40" placeholder="Ej. 1er Bimestre" value="<?= old('nombre', $p['nombre'] ?? '') ?>" required>
                        <div class="form-text">Sugeridos: <?= implode(' · ', $sugeridos) ?></div>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">Año lectivo *</label>
                        <input type="number" name="anio" class="form-control" min="2000" max="2100" value="<?= old('anio', $p['anio'] ?? date('Y')) ?>" required>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">Fecha de inicio *</label>
                        <input type="date" name="fecha_inicio" class="form-control" value="<?= old('fecha_inicio', $p['fecha_inicio'] ?? '') ?>" required>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">Fecha de fin *</label>
                        <input type="date" name="fecha_fin" class="form-control" value="<?= old('fecha_fin', $p['fecha_fin'] ?? '') ?>" required>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-white text-end">
                <a href="<?= url('periodos') ?>" class="btn btn-outline-secondary me-2">Cancelar</a>
                <button type="submit" class="btn btn-accent"><i class="bi bi-check2-circle me-1"></i><?= $esEditar ? 'Guardar cambios' : 'Registrar periodo' ?></button>
            </div>
        </form>
    </div>
</div>