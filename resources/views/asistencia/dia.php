<?php
/**
 * EduAlert JJP - recursos/views/asistencia/dia.php
 * Consulta de asistencia registrada por día
 */
?>
<div class="row g-3 mb-4 align-items-center">
    <div class="col">
        <h2 class="fw-bold text-primary mb-1"><i class="bi bi-calendar-day me-2 text-accent"></i>Asistencia por día</h2>
        <p class="text-muted mb-0 fs-7">Consulta la asistencia registrada de una sección en una fecha</p>
    </div>
    <div class="col-auto">
        <a href="<?= url('asistencia') ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Volver</a>
    </div>
</div>

<form method="get" action="<?= url('asistencia/dia') ?>" class="card mb-4 border-0 shadow-sm">
    <div class="card-body py-3">
        <div class="row g-2 align-items-end">
            <div class="col-12 col-md-4">
                <label class="form-label">Sección *</label>
                <select name="seccion_id" class="form-select">
                    <option value="0">Seleccione...</option>
                    <?php foreach ($secciones as $s): ?>
                        <option value="<?= (int)$s['id'] ?>" <?= $seccion_id == $s['id'] ? 'selected' : '' ?>><?= e($s['grado_nombre'] . ' "' . $s['nombre'] . '" (' . $s['turno'] . ')') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 col-md-3">
                <label class="form-label">Fecha *</label>
                <input type="date" name="fecha" class="form-control" value="<?= e($fecha) ?>" required>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary"><i class="bi bi-search me-1"></i>Consultar</button>
            </div>
        </div>
    </div>
</form>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-datatable align-middle mb-0">
                <thead>
                    <tr><th>#</th><th>Estudiante</th><th>Asistencia</th><th>Observación</th></tr>
                </thead>
                <tbody>
                    <?php if (empty($registros)): ?>
                        <tr><td colspan="4"><div class="empty-state"><i class="bi bi-inbox"></i>No hay asistencia registrada para la sección y fecha seleccionadas.</div></td></tr>
                    <?php else: ?>
                        <?php foreach ($registros as $i => $r): ?>
                            <tr>
                                <td class="text-muted"><?= $i + 1 ?></td>
                                <td class="fw-semibold"><?= e($r['apellidos'] . ', ' . $r['nombres']) ?><div class="text-muted fs-8"><?= e($r['codigo']) ?></div></td>
                                <td><?= badge_estado($r['estado']) ?></td>
                                <td class="text-muted fs-7"><?= e($r['observacion'] ?? '-') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>