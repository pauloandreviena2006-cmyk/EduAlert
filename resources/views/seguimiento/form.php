<?php
/**
 * EduAlert JJP - recursos/views/seguimiento/form.php
 * Registro de seguimiento/intervención sobre una alerta
 */
$al = $alerta;
?>
<div class="row g-3 mb-4 align-items-center">
    <div class="col">
        <h2 class="fw-bold text-primary mb-1"><i class="bi bi-clipboard2-pulse me-2 text-accent"></i>Registrar seguimiento</h2>
        <p class="text-muted mb-0 fs-7">Intervención para la alerta #<?= (int)$al['id'] ?> de <?= e($al['apellidos'] . ', ' . $al['nombres']) ?> (<?= e($al['codigo']) ?>)</p>
    </div>
    <div class="col-auto">
        <a href="<?= url('alertas/ver/' . $al['id']) ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Volver a la alerta</a>
    </div>
</div>

<div class="row g-4">
    <div class="col-12 col-lg-8">
        <form method="post" action="<?= url('seguimiento/crear/' . $al['id']) ?>" class="card border-0 shadow-sm">
            <?= csrf_field() ?>
            <div class="card-header bg-white py-3 fw-semibold"><i class="bi bi-journal-plus me-2 text-accent"></i>Datos de la intervención</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <label class="form-label">Fecha *</label>
                        <input type="date" name="fecha" class="form-control" value="<?= old('fecha', date('Y-m-d')) ?>" required>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">Tipo de intervención *</label>
                        <select name="tipo_intervencion" class="form-select" required>
                            <option value="">Seleccione...</option>
                            <?php foreach ($tipos as $t): ?>
                                <option value="<?= e($t) ?>" <?= old('tipo_intervencion') === $t ? 'selected' : '' ?>><?= e($t) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Descripción *</label>
                        <textarea name="descripcion" class="form-control" rows="3" maxlength="500" placeholder="Detalle de lo realizado en la intervención..." required><?= old('descripcion') ?></textarea>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">Acuerdo alcanzado</label>
                        <input type="text" name="acuerdo" class="form-control" maxlength="255" placeholder="Ej. Reunión con apoderado acordada" value="<?= old('acuerdo') ?>">
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">Compromiso del estudiante</label>
                        <input type="text" name="compromiso" class="form-control" maxlength="255" placeholder="Ej. Mejorar asistencia a clases" value="<?= old('compromiso') ?>">
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">Próxima revisión</label>
                        <input type="date" name="proxima_revision" class="form-control" value="<?= old('proxima_revision') ?>">
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">Estado resultante de la alerta</label>
                        <select name="estado" class="form-select">
                            <?php foreach (\App\Models\Alerta::ESTADOS as $es): ?>
                                <option value="<?= e($es) ?>" <?= $es === 'En seguimiento' ? 'selected' : '' ?>><?= e($es) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text">Al registrar la intervención, la alerta pasa a este estado.</div>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-white text-end">
                <a href="<?= url('alertas/ver/' . $al['id']) ?>" class="btn btn-outline-secondary me-2">Cancelar</a>
                <button type="submit" class="btn btn-accent px-4"><i class="bi bi-check2-circle me-1"></i>Registrar seguimiento</button>
            </div>
        </form>
    </div>

    <div class="col-12 col-lg-4">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 fw-semibold"><i class="bi bi-bell me-2 text-danger"></i>Alerta asociada</div>
            <div class="card-body fs-7">
                <div class="mb-3 text-center">
                    <span class="badge bg-<?= e($al['riesgo_color']) ?>-subtle text-<?= e($al['riesgo_color']) ?>-emphasis border border-<?= e($al['riesgo_color']) ?>-subtle rounded-pill fs-6">
                        <i class="bi bi-<?= $al['riesgo_color'] === 'danger' ? 'exclamation-triangle' : ($al['riesgo_color'] === 'warning' ? 'exclamation-circle' : 'check-circle') ?> me-1"></i><?= e($al['riesgo_nombre']) ?>
                    </span>
                </div>
                <div class="mb-2 text-center"><?= badge_estado($al['estado']) ?></div>
                <dl class="row mb-0">
                    <dt class="col-5 text-muted">Puntaje</dt><dd class="col-7 fw-bold"><?= (int)$al['puntaje'] ?> pts</dd>
                    <dt class="col-5 text-muted">Grado / Sección</dt><dd class="col-7"><?= e($al['grado_nombre'] ?? '-') ?> "<?= e($al['seccion_nombre'] ?? '-') ?>"</dd>
                    <dt class="col-5 text-muted">Periodo</dt><dd class="col-7"><?= e($al['periodo_nombre']) ?> <?= e($al['periodo_anio']) ?></dd>
                    <dt class="col-5 text-muted">Generada</dt><dd class="col-7"><?= f_fecha_hora($al['fecha_generacion']) ?></dd>
                </dl>
            </div>
        </div>
        <div class="alert alert-light border fs-7">
            <i class="bi bi-lightbulb me-1 text-accent"></i>
            <b>Sugerencia:</b> registre la intervención con detalles concretos y acuerdos verificables. Esto permite medir la evolución del estudiante en los reportes.
        </div>
    </div>
</div>