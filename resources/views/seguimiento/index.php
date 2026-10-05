<?php
/**
 * EduAlert JJP - recursos/views/seguimiento/index.php
 */
$estadosFiltro = ['En seguimiento', 'En revisión', 'Atendida'];
?>
<div class="row g-3 mb-4 align-items-center">
    <div class="col">
        <h2 class="fw-bold text-primary mb-1"><i class="bi bi-clipboard2-pulse me-2 text-accent"></i>Seguimiento académico</h2>
        <p class="text-muted mb-0 fs-7">Intervenciones registradas sobre las alertas de los estudiantes</p>
    </div>
    <div class="col-auto">
        <a href="<?= url('alertas') ?>" class="btn btn-outline-accent"><i class="bi bi-bell me-1"></i>Ver alertas</a>
    </div>
</div>

<div class="card mb-4 border-0 shadow-sm">
    <div class="card-body py-3">
        <form method="get" action="<?= url('seguimiento') ?>" class="row g-2 align-items-end">
            <div class="col-12 col-md-5 col-xl-4">
                <label class="form-label">Buscar</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" name="q" class="form-control" placeholder="Estudiante o código..." value="<?= e($q) ?>">
                </div>
            </div>
            <div class="col-12 col-md-4 col-xl-3">
                <label class="form-label">Estado de la intervención</label>
                <select name="estado" class="form-select">
                    <option value="">Todos</option>
                    <?php foreach ($estadosFiltro as $es): ?>
                        <option value="<?= e($es) ?>" <?= $estado === $es ? 'selected' : '' ?>><?= e($es) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-auto d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="bi bi-funnel me-1"></i>Filtrar</button>
                <a href="<?= url('seguimiento') ?>" class="btn btn-outline-secondary"><i class="bi bi-x-lg"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-datatable align-middle mb-0">
                <thead>
                    <tr><th>Estudiante</th><th>Intervención</th><th>Descripción</th><th>Responsable</th><th>Fecha</th><th>Estado</th><th class="no-sort">Acciones</th></tr>
                </thead>
                <tbody>
                    <?php if (empty($seguimientos)): ?>
                        <tr><td colspan="7"><div class="empty-state"><i class="bi bi-inbox"></i>No se encontraron intervenciones con los filtros aplicados.</div></td></tr>
                    <?php else: ?>
                        <?php foreach ($seguimientos as $s): ?>
                            <tr>
                                <td>
                                    <div class="fw-semibold"><?= e($s['apellidos'] . ', ' . $s['nombres']) ?></div>
                                    <div class="text-muted fs-8"><?= e($s['codigo']) ?></div>
                                </td>
                                <td><span class="badge bg-primary-subtle text-primary-emphasis rounded-pill"><?= e($s['tipo_intervencion']) ?></span></td>
                                <td class="text-muted fs-7"><?= e(mb_substr((string)$s['descripcion'], 0, 70)) ?><?= mb_strlen((string)$s['descripcion']) > 70 ? '...' : '' ?></td>
                                <td class="fs-7"><?= e(trim(($s['resp_nombres'] ?? '') . ' ' . ($s['resp_apellidos'] ?? ''))) ?></td>
                                <td class="fs-7"><?= f_fecha_hora($s['fecha']) ?></td>
                                <td><?= badge_estado($s['alerta_estado']) ?></td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="<?= url('alertas/ver/' . $s['alerta_id']) ?>" class="btn btn-sm btn-outline-accent" title="Ver alerta"><i class="bi bi-bell"></i></a>
                                        <form method="post" action="<?= url('seguimiento/eliminar/' . $s['id']) ?>" data-confirm='{"titulo":"Eliminar seguimiento","texto":"¿Desea eliminar esta intervención? Esta acción no se puede deshacer."}'>
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar"><i class="bi bi-trash"></i></button>
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