<?php
/**
 * EduAlert JJP - recursos/views/periodos/index.php
 */
?>
<div class="row g-3 mb-4 align-items-center">
    <div class="col">
        <h2 class="fw-bold text-primary mb-1"><i class="bi bi-calendar3 me-2 text-accent"></i>Periodos académicos</h2>
        <p class="text-muted mb-0 fs-7">Periodos/trimestres en los que se evalúa a los estudiantes</p>
    </div>
    <div class="col-auto">
        <a href="<?= url('periodos/crear') ?>" class="btn btn-accent"><i class="bi bi-plus-lg me-1"></i>Nuevo periodo</a>
    </div>
</div>

<div class="row g-4">
    <?php if (empty($periodos)): ?>
        <div class="col-12"><div class="card border-0 shadow-sm"><div class="card-body"><div class="empty-state"><i class="bi bi-inbox"></i>No hay periodos registrados.</div></div></div></div>
    <?php else: ?>
        <?php foreach ($periodos as $p): ?>
            <div class="col-12 col-md-6 col-xl-4">
                <div class="card border-0 shadow-sm h-100 <?= (int)$p['activo'] === 1 ? 'border-start border-4 border-success' : '' ?>">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h4 class="fw-bold text-primary mb-0"><?= e($p['nombre']) ?></h4>
                                <div class="text-muted fs-7">Año lectivo <?= e($p['anio']) ?></div>
                            </div>
                            <span class="display-6 text-accent"><i class="bi bi-calendar2-week"></i></span>
                        </div>
                        <div class="fs-7 mb-3">
                            <div><i class="bi bi-play-circle me-1 text-success"></i>Inicio: <?= f_fecha($p['fecha_inicio']) ?></div>
                            <div><i class="bi bi-stop-circle me-1 text-danger"></i>Fin: <?= f_fecha($p['fecha_fin']) ?></div>
                        </div>
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <?php if ((int)$p['activo'] === 1): ?>
                                <span class="badge bg-success-subtle text-success-emphasis rounded-pill"><i class="bi bi-check-circle me-1"></i>Periodo activo</span>
                            <?php endif; ?>
                            <span class="badge bg-<?= (int)$p['estado'] === 1 ? 'success' : 'secondary' ?>-subtle text-<?= (int)$p['estado'] === 1 ? 'success' : 'secondary' ?>-emphasis rounded-pill"><?= (int)$p['estado'] === 1 ? 'Habilitado' : 'Deshabilitado' ?></span>
                        </div>
                        <div class="d-flex gap-2 flex-wrap">
                            <a href="<?= url('periodos/editar/' . $p['id']) ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil me-1"></i>Editar</a>
                            <?php if ((int)$p['activo'] !== 1): ?>
                                <form method="post" action="<?= url('periodos/activar/' . $p['id']) ?>" data-confirm='{"titulo":"Activar periodo","texto":"¿Desea activar <?= e($p['nombre'] . ' ' . $p['anio']) ?>? Pasará a ser el periodo en uso."}'>
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-sm btn-outline-success"><i class="bi bi-check2-square me-1"></i>Activar</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>