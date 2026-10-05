<?php
/**
 * EduAlert JJP - recursos/views/calificaciones/form.php
 * Registro masivo de calificaciones por sección, curso y periodo
 */
$hayEstudiantes = !empty($estudiantes);
?>
<div class="row g-3 mb-4 align-items-center">
    <div class="col">
        <h2 class="fw-bold text-primary mb-1"><i class="bi bi-pencil-square me-2 text-accent"></i>Registro de calificaciones</h2>
        <p class="text-muted mb-0 fs-7">Seleccione la sección, el curso y el periodo para ingresar las notas</p>
    </div>
    <div class="col-auto">
        <a href="<?= url('calificaciones') ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Volver</a>
    </div>
</div>

<form method="get" action="<?= url('calificaciones/registro') ?>" class="card mb-4 border-0 shadow-sm">
    <div class="card-body py-3">
        <div class="row g-2 align-items-end">
            <div class="col-12 col-md-3">
                <label class="form-label">Sección *</label>
                <select name="seccion_id" class="form-select">
                    <option value="0">Seleccione...</option>
                    <?php foreach ($secciones as $s): ?>
                        <option value="<?= (int)$s['id'] ?>" <?= $seccion_id == $s['id'] ? 'selected' : '' ?>><?= e($s['grado_nombre'] . ' "' . $s['nombre'] . '" (' . $s['turno'] . ')') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 col-md-3">
                <label class="form-label">Curso *</label>
                <select name="curso_id" class="form-select">
                    <option value="0">Seleccione...</option>
                    <?php foreach ($cursos as $c): ?>
                        <option value="<?= (int)$c['id'] ?>" <?= $curso_id == $c['id'] ? 'selected' : '' ?>><?= e($c['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 col-md-3">
                <label class="form-label">Periodo *</label>
                <select name="periodo_id" class="form-select">
                    <?php foreach ($periodos as $p): ?>
                        <option value="<?= (int)$p['id'] ?>" <?= $periodo_id == $p['id'] ? 'selected' : '' ?>><?= e($p['nombre'] . ' ' . $p['anio']) ?><?= (int)$p['activo'] === 1 ? ' (activo)' : '' ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary"><i class="bi bi-arrow-repeat me-1"></i>Cargar estudiantes</button>
            </div>
        </div>
    </div>
</form>

<?php if (!$hayEstudiantes && $seccion_id > 0 && $curso_id > 0): ?>
    <div class="card border-0 shadow-sm"><div class="card-body"><div class="empty-state"><i class="bi bi-inbox"></i>No hay estudiantes matriculados en la sección seleccionada.</div></div></div>
<?php elseif ($hayEstudiantes): ?>
    <form method="post" action="<?= url('calificaciones/registro') ?>" class="card border-0 shadow-sm">
        <?= csrf_field() ?>
        <input type="hidden" name="seccion_id" value="<?= (int)$seccion_id ?>">
        <input type="hidden" name="curso_id" value="<?= (int)$curso_id ?>">
        <input type="hidden" name="periodo_id" value="<?= (int)$periodo_id ?>">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="fw-semibold">
                <i class="bi bi-journal-bookmark me-2 text-accent"></i><?= e($curso['nombre'] ?? 'Curso') ?>
                <?php if ($docente): ?>
                    <span class="badge bg-light text-dark ms-2"><i class="bi bi-person-workspace me-1"></i><?= e($docente['apellidos'] . ', ' . $docente['nombres']) ?></span>
                <?php endif; ?>
            </div>
            <span class="text-muted fs-7"><?= count($estudiantes) ?> estudiantes</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr><th>#</th><th>Estudiante</th><th class="text-center" style="width:140px">Nota (0-20)</th><th class="text-center" style="width:200px">Competencia</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($estudiantes as $i => $e): ?>
                            <tr>
                                <td class="text-muted"><?= $i + 1 ?></td>
                                <td>
                                    <div class="fw-semibold"><?= e($e['apellidos'] . ', ' . $e['nombres']) ?></div>
                                    <div class="text-muted fs-8"><?= e($e['codigo']) ?></div>
                                </td>
                                <td>
                                    <input type="number" name="notas[<?= (int)$e['matricula_id'] ?>]" class="form-control text-center nota-input" min="0" max="20" step="0.1" value="<?= isset($notas[$e['matricula_id']]) ? f_numero($notas[$e['matricula_id']], 1) : '' ?>" placeholder="—">
                                </td>
                                <td>
                                    <select name="competencia[<?= (int)$e['matricula_id'] ?>]" class="form-select form-select-sm">
                                        <?php $competencias = ['Logros de aprendizaje', 'Adecuado', 'En proceso', 'En inicio']; ?>
                                        <?php foreach ($competencias as $c): ?>
                                            <option value="<?= $c ?>"><?= $c ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="text-muted fs-8">La nota mínima aprobatoria es <b><?= f_numero(setting('promedio_aprobatorio', 11), 1) ?></b> (configurable).</div>
            <button type="submit" class="btn btn-accent px-4"><i class="bi bi-save me-1"></i>Guardar calificaciones</button>
        </div>
    </form>
<?php else: ?>
    <div class="card border-0 shadow-sm"><div class="card-body"><div class="empty-state"><i class="bi bi-arrow-up-circle"></i>Seleccione sección, curso y periodo y presione «Cargar estudiantes».</div></div></div>
<?php endif; ?>

<script>
$(function () {
    // Saltar al siguiente input de nota al presionar Enter (flujo rápido de registro)
    $('.nota-input').on('keydown', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            const $inputs = $('.nota-input');
            const idx = $inputs.index(this);
            if (idx < $inputs.length - 1) $inputs.eq(idx + 1).focus().select();
        }
    });
});
</script>