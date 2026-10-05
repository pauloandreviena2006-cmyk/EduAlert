<?php
/**
 * EduAlert JJP - recursos/views/calificaciones/index.php
 * Consulta de calificaciones con filtros
 */
?>
<div class="row g-3 mb-4 align-items-center">
    <div class="col">
        <h2 class="fw-bold text-primary mb-1"><i class="bi bi-clipboard-data me-2 text-accent"></i>Calificaciones</h2>
        <p class="text-muted mb-0 fs-7">Consulta de notas por estudiante, curso y periodo</p>
    </div>
    <div class="col-auto">
        <a href="<?= url('calificaciones/registro') ?>" class="btn btn-accent"><i class="bi bi-pencil-square me-1"></i>Registrar notas</a>
    </div>
</div>

<div class="card mb-4 border-0 shadow-sm">
    <div class="card-body py-3">
        <form method="get" action="<?= url('calificaciones') ?>" class="row g-2 align-items-end">
            <div class="col-12 col-md-4 col-xl-3">
                <label class="form-label">Buscar</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" name="q" class="form-control" placeholder="Código, DNI o estudiante..." value="<?= e($q) ?>">
                </div>
            </div>
            <div class="col-6 col-md-3 col-xl-2">
                <label class="form-label">Grado</label>
                <select name="grado_id" class="form-select">
                    <option value="0">Todos</option>
                    <?php foreach ($grados as $g): ?>
                        <option value="<?= (int)$g['id'] ?>" <?= $grado_id == $g['id'] ? 'selected' : '' ?>><?= e($g['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-6 col-md-3 col-xl-2">
                <label class="form-label">Sección</label>
                <select name="seccion_id" class="form-select">
                    <option value="0">Todas</option>
                    <?php foreach ($secciones as $s): ?>
                        <option value="<?= (int)$s['id'] ?>" <?= $seccion_id == $s['id'] ? 'selected' : '' ?>><?= e($s['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-6 col-md-3 col-xl-2">
                <label class="form-label">Curso</label>
                <select name="curso_id" class="form-select">
                    <option value="0">Todos</option>
                    <?php foreach ($cursos as $c): ?>
                        <option value="<?= (int)$c['id'] ?>" <?= $curso_id == $c['id'] ? 'selected' : '' ?>><?= e($c['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-6 col-md-3 col-xl-2">
                <label class="form-label">Periodo</label>
                <select name="periodo_id" class="form-select">
                    <?php foreach ($periodos as $p): ?>
                        <option value="<?= (int)$p['id'] ?>" <?= $periodo_id == $p['id'] ? 'selected' : '' ?>><?= e($p['nombre'] . ' ' . $p['anio']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-auto d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="bi bi-funnel me-1"></i>Filtrar</button>
                <a href="<?= url('calificaciones') ?>" class="btn btn-outline-secondary"><i class="bi bi-x-lg"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-datatable align-middle mb-0">
                <thead>
                    <tr><th>Estudiante</th><th>Curso</th><th>Grado / Sección</th><th>Periodo</th><th class="text-center">Nota</th><th class="text-center">Estado</th></tr>
                </thead>
                <tbody>
                    <?php if (empty($calificaciones)): ?>
                        <tr><td colspan="6"><div class="empty-state"><i class="bi bi-inbox"></i>No se encontraron calificaciones con los filtros aplicados.</div></td></tr>
                    <?php else: ?>
                        <?php $__aprob = (float)setting('promedio_aprobatorio', 11); ?>
                        <?php foreach ($calificaciones as $c): ?>
                            <tr>
                                <td>
                                    <div class="fw-semibold"><?= e($c['apellidos'] . ', ' . $c['nombres']) ?></div>
                                    <div class="text-muted fs-8"><?= e($c['codigo']) ?> · DNI <?= e($c['dni']) ?></div>
                                </td>
                                <td><?= e($c['curso_nombre']) ?></td>
                                <td><?= e($c['grado_nombre']) ?> "<?= e($c['seccion_nombre']) ?>"</td>
                                <td><span class="badge bg-primary-subtle text-primary-emphasis rounded-pill"><?= e($c['periodo_nombre']) ?></span></td>
                                <td class="text-center">
                                    <span class="badge fs-6 <?= (float)$c['nota'] >= $__aprob ? 'text-success' : 'text-danger' ?>"><?= f_numero($c['nota'], 1) ?></span>
                                </td>
                                <td class="text-center">
                                    <?= (float)$c['nota'] >= $__aprob
                                        ? '<span class="badge bg-success-subtle text-success-emphasis rounded-pill">Aprobado</span>'
                                        : '<span class="badge bg-danger-subtle text-danger-emphasis rounded-pill">Desaprobado</span>' ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>