<?php
/**
 * EduAlert JJP - recursos/views/matriculas/index.php
 */
?>
<div class="row g-3 mb-4 align-items-center">
    <div class="col">
        <h2 class="fw-bold text-primary mb-1"><i class="bi bi-mortarboard me-2 text-accent"></i>Matrículas</h2>
        <p class="text-muted mb-0 fs-7">Inscripciones de estudiantes por periodo, grado y sección</p>
    </div>
    <div class="col-auto">
        <a href="<?= url('matriculas/crear') ?>" class="btn btn-accent"><i class="bi bi-plus-lg me-1"></i>Nueva matrícula</a>
    </div>
</div>

<div class="card mb-4 border-0 shadow-sm">
    <div class="card-body py-3">
        <form method="get" action="<?= url('matriculas') ?>" class="row g-2 align-items-end">
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
                <label class="form-label">Periodo</label>
                <select name="periodo_id" class="form-select">
                    <option value="0">Todos</option>
                    <?php foreach ($periodos as $p): ?>
                        <option value="<?= (int)$p['id'] ?>" <?= $periodo_id == $p['id'] ? 'selected' : '' ?>><?= e($p['nombre'] . ' ' . $p['anio']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-auto d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="bi bi-funnel me-1"></i>Filtrar</button>
                <a href="<?= url('matriculas') ?>" class="btn btn-outline-secondary"><i class="bi bi-x-lg"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-datatable align-middle mb-0">
                <thead>
                    <tr><th>Estudiante</th><th>Periodo</th><th>Grado / Sección</th><th>Turno</th><th>Fecha</th><th>Estado</th><th class="no-sort">Acciones</th></tr>
                </thead>
                <tbody>
                    <?php if (empty($matriculas)): ?>
                        <tr><td colspan="7"><div class="empty-state"><i class="bi bi-inbox"></i>No se encontraron matrículas con los filtros aplicados.</div></td></tr>
                    <?php else: ?>
                        <?php foreach ($matriculas as $m): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="avatar-sm"><?= e(mb_substr($m['nombres'], 0, 1) . mb_substr($m['apellidos'], 0, 1)) ?></span>
                                        <div>
                                            <div class="fw-semibold"><?= e($m['apellidos'] . ', ' . $m['nombres']) ?></div>
                                            <div class="text-muted fs-8"><?= e($m['codigo']) ?> · DNI <?= e($m['dni']) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="badge bg-primary-subtle text-primary-emphasis rounded-pill"><?= e($m['periodo_nombre'] . ' ' . $m['periodo_anio'] ?? $m['periodo_nombre']) ?></span></td>
                                <td><?= e($m['grado_nombre']) ?> "<?= e($m['seccion_nombre']) ?>"</td>
                                <td><?= e($m['turno']) ?></td>
                                <td class="fs-7"><?= f_fecha($m['fecha']) ?></td>
                                <td>
                                    <span class="badge bg-<?= (int)$m['estado'] === 1 ? 'success' : 'secondary' ?>-subtle text-<?= (int)$m['estado'] === 1 ? 'success' : 'secondary' ?>-emphasis rounded-pill"><?= (int)$m['estado'] === 1 ? 'Vigente' : 'Retirada' ?></span>
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="<?= url('matriculas/editar/' . $m['id']) ?>" class="btn btn-sm btn-outline-primary" title="Editar"><i class="bi bi-pencil"></i></a>
                                        <a href="<?= url('estudiantes/ver/' . $m['estudiante_id']) ?>" class="btn btn-sm btn-outline-accent" title="Ver ficha"><i class="bi bi-eye"></i></a>
                                        <form method="post" action="<?= url('matriculas/estado/' . $m['id']) ?>" data-confirm='{"titulo":"<?= (int)$m['estado'] === 1 ? 'Retirar matrícula' : 'Reactivar matrícula' ?>","texto":"¿Desea <?= (int)$m['estado'] === 1 ? 'retirar' : 'reactivar' ?> la matrícula de <?= e($m['nombres'] . ' ' . $m['apellidos']) ?>?"}'>
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-sm btn-outline-<?= (int)$m['estado'] === 1 ? 'danger' : 'success' ?>" title="<?= (int)$m['estado'] === 1 ? 'Retirar' : 'Reactivar' ?>"><i class="bi bi-power"></i></button>
                                        </form>
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