<?php
/**
 * EduAlert JJP - recursos/views/asistencia/form.php
 * Registro masivo de asistencia por sección y fecha
 */
$hayEstudiantes = !empty($estudiantes);
?>
<div class="row g-3 mb-4 align-items-center">
    <div class="col">
        <h2 class="fw-bold text-primary mb-1"><i class="bi bi-pencil-square me-2 text-accent"></i>Registro de asistencia</h2>
        <p class="text-muted mb-0 fs-7">Marque la asistencia de cada estudiante para la fecha seleccionada</p>
    </div>
    <div class="col-auto">
        <a href="<?= url('asistencia') ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Volver</a>
    </div>
</div>

<form method="get" action="<?= url('asistencia/registro') ?>" class="card mb-4 border-0 shadow-sm">
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
                <label class="form-label">Periodo</label>
                <select name="periodo_id" class="form-select">
                    <?php foreach ($periodos as $p): ?>
                        <option value="<?= (int)$p['id'] ?>" <?= $periodo_id == $p['id'] ? 'selected' : '' ?>><?= e($p['nombre'] . ' ' . $p['anio']) ?><?= (int)$p['activo'] === 1 ? ' (activo)' : '' ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 col-md-3">
                <label class="form-label">Fecha *</label>
                <input type="date" name="fecha" class="form-control" value="<?= e($fecha) ?>" required>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary"><i class="bi bi-arrow-repeat me-1"></i>Cargar estudiantes</button>
            </div>
        </div>
    </div>
</form>

<?php if ($hayEstudiantes): ?>
    <form method="post" action="<?= url('asistencia/registro') ?>" class="card border-0 shadow-sm">
        <?= csrf_field() ?>
        <input type="hidden" name="seccion_id" value="<?= (int)$seccion_id ?>">
        <input type="hidden" name="periodo_id" value="<?= (int)$periodo_id ?>">
        <input type="hidden" name="fecha" value="<?= e($fecha) ?>">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="fw-semibold"><i class="bi bi-calendar-event me-2 text-accent"></i><?= f_fecha($fecha, 'l, d \d\e F \d\e Y') ?> · <?= count($estudiantes) ?> estudiantes</div>
            <div class="d-flex gap-1">
                <button type="button" class="btn btn-sm btn-outline-success" onclick="marcarTodos('Presente')"><i class="bi bi-check-circle me-1"></i>Todos presentes</button>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="marcarTodos('Falta')"><i class="bi bi-x-circle me-1"></i>Todos faltan</button>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr><th>#</th><th>Estudiante</th><th class="text-center">Asistencia</th><th>Observación</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($estudiantes as $i => $e): ?>
                            <?php $__ex = $existentes[$e['matricula_id']] ?? null; ?>
                            <?php $__estado = $__ex['estado'] ?? 'Presente'; ?>
                            <tr>
                                <td class="text-muted"><?= $i + 1 ?></td>
                                <td>
                                    <div class="fw-semibold"><?= e($e['apellidos'] . ', ' . $e['nombres']) ?></div>
                                    <div class="text-muted fs-8"><?= e($e['codigo']) ?></div>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm estado-grupo" role="group" style="width:100%">
                                        <?php foreach (['Presente', 'Tardanza', 'Falta', 'Justificado'] as $__est): ?>
                                            <input type="radio" class="btn-check" name="estado[<?= (int)$e['matricula_id'] ?>]" id="est-<?= (int)$e['matricula_id'] ?>-<?= $__est ?>" value="<?= $__est ?>" <?= $__estado === $__est ? 'checked' : '' ?>>
                                            <label class="btn btn-outline-<?= $__est === 'Presente' ? 'success' : ($__est === 'Tardanza' ? 'warning' : ($__est === 'Falta' ? 'danger' : 'secondary')) ?>" for="est-<?= (int)$e['matricula_id'] ?>-<?= $__est ?>"><?= $__est[0] ?></label>
                                        <?php endforeach; ?>
                                    </div>
                                </td>
                                <td>
                                    <input type="text" name="observacion[<?= (int)$e['matricula_id'] ?>]" class="form-control form-control-sm" maxlength="150" placeholder="Justificación..." value="<?= e($__ex['observacion'] ?? '') ?>">
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white text-end">
            <button type="submit" class="btn btn-accent px-4"><i class="bi bi-save me-1"></i>Guardar asistencia</button>
        </div>
    </form>
<?php else: ?>
    <div class="card border-0 shadow-sm"><div class="card-body"><div class="empty-state"><i class="bi bi-arrow-up-circle"></i>Seleccione sección y fecha y presione «Cargar estudiantes».</div></div></div>
<?php endif; ?>

<script>
function marcarTodos(estado) {
    document.querySelectorAll('.estado-grupo').forEach(function (grupo) {
        const radio = grupo.querySelector('input[value="' + estado + '"]');
        if (radio) radio.checked = true;
    });
}
</script>