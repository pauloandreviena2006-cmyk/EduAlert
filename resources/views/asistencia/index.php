<?php
/**
 * EduAlert JJP - recursos/views/asistencia/index.php
 * Resumen de asistencia por sección en el periodo
 */
?>
<div class="row g-3 mb-4 align-items-center">
    <div class="col">
        <h2 class="fw-bold text-primary mb-1"><i class="bi bi-calendar-check me-2 text-accent"></i>Control de asistencia</h2>
        <p class="text-muted mb-0 fs-7">Resumen mensual de asistencia por sección</p>
    </div>
    <div class="col-auto d-flex gap-2 flex-wrap">
        <a href="<?= url('asistencia/dia') ?>" class="btn btn-outline-accent"><i class="bi bi-calendar-day me-1"></i>Por día</a>
        <a href="<?= url('asistencia/registro') ?>" class="btn btn-accent"><i class="bi bi-pencil-square me-1"></i>Registrar asistencia</a>
    </div>
</div>

<div class="card mb-4 border-0 shadow-sm">
    <div class="card-body py-3">
        <form method="get" action="<?= url('asistencia') ?>" class="row g-2 align-items-end">
            <div class="col-6 col-md-3">
                <label class="form-label">Periodo</label>
                <select name="periodo_id" class="form-select">
                    <?php foreach ($periodos as $p): ?>
                        <option value="<?= (int)$p['id'] ?>" <?= $periodo_id == $p['id'] ? 'selected' : '' ?>><?= e($p['nombre'] . ' ' . $p['anio']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label">Grado</label>
                <select name="grado_id" class="form-select">
                    <option value="0">Todos</option>
                    <?php foreach ($grados as $g): ?>
                        <option value="<?= (int)$g['id'] ?>" <?= $grado_id == $g['id'] ? 'selected' : '' ?>><?= e($g['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label">Sección</label>
                <select name="seccion_id" class="form-select">
                    <option value="0">Todas</option>
                    <?php foreach ($secciones as $s): ?>
                        <option value="<?= (int)$s['id'] ?>" <?= $seccion_id == $s['id'] ? 'selected' : '' ?>><?= e($s['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label">Fecha referencia</label>
                <input type="date" name="fecha" class="form-control" value="<?= e($fecha) ?>">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary"><i class="bi bi-funnel me-1"></i>Filtrar</button>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-datatable align-middle mb-0">
                <thead>
                    <tr>
                        <th>Sección</th>
                        <th class="text-center">Estudiantes</th>
                        <th class="text-center"><span class="text-success"><i class="bi bi-check-circle me-1"></i>Presentes</span></th>
                        <th class="text-center"><span class="text-warning"><i class="bi bi-clock me-1"></i>Tardanzas</span></th>
                        <th class="text-center"><span class="text-danger"><i class="bi bi-x-circle me-1"></i>Faltas</span></th>
                        <th class="text-center"><span class="text-secondary"><i class="bi bi-file-earmark-check me-1"></i>Justificados</span></th>
                        <th class="text-center">Asistencia</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($resumen)): ?>
                        <tr><td colspan="7"><div class="empty-state"><i class="bi bi-inbox"></i>No hay registros de asistencia para los filtros seleccionados.</div></td></tr>
                    <?php else: ?>
                        <?php foreach ($resumen as $r): ?>
                            <?php $__total = (int)$r['presentes'] + (int)$r['tardanzas'] + (int)$r['faltas'] + (int)$r['justificados']; ?>
                            <?php $__pct = $__total > 0 ? round((((int)$r['presentes'] + (int)$r['tardanzas']) / $__total) * 100, 1) : 100; ?>
                            <tr>
                                <td class="fw-semibold"><?= e($r['grado_nombre']) ?> "<?= e($r['seccion_nombre']) ?>"</td>
                                <td class="text-center"><?= (int)$r['estudiantes'] ?></td>
                                <td class="text-center fw-semibold text-success"><?= (int)$r['presentes'] ?></td>
                                <td class="text-center fw-semibold text-warning"><?= (int)$r['tardanzas'] ?></td>
                                <td class="text-center fw-semibold text-danger"><?= (int)$r['faltas'] ?></td>
                                <td class="text-center fw-semibold text-secondary"><?= (int)$r['justificados'] ?></td>
                                <td class="text-center">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="progress flex-grow-1" style="height:8px;min-width:70px">
                                            <div class="progress-bar bg-<?= $__pct >= 90 ? 'success' : ($__pct >= 75 ? 'warning' : 'danger') ?>" style="width:<?= $__pct ?>%"></div>
                                        </div>
                                        <span class="fs-7 fw-bold"><?= f_numero($__pct, 1) ?>%</span>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>