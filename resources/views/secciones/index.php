<?php
/**
 * EduAlert JJP - recursos/views/secciones/index.php
 */
$gradoFiltro = (int)($_GET['grado_id'] ?? 0);
?>
<div class="row g-3 mb-4 align-items-center">
    <div class="col">
        <h2 class="fw-bold text-primary mb-1"><i class="bi bi-layout-sidebar me-2 text-accent"></i>Secciones</h2>
        <p class="text-muted mb-0 fs-7">Secciones por grado académico y turno</p>
    </div>
    <div class="col-auto">
        <a href="<?= url('secciones/crear') ?>" class="btn btn-accent"><i class="bi bi-plus-lg me-1"></i>Nueva sección</a>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-datatable align-middle mb-0">
                <thead>
                    <tr><th>Grado</th><th>Sección</th><th>Turno</th><th>Orden</th><th class="no-sort">Acciones</th></tr>
                </thead>
                <tbody>
                    <?php if (empty($secciones)): ?>
                        <tr><td colspan="5"><div class="empty-state"><i class="bi bi-inbox"></i>No hay secciones registradas.</div></td></tr>
                    <?php else: ?>
                        <?php foreach ($secciones as $s): ?>
                            <tr>
                                <td class="fw-semibold"><?= e($s['grado_nombre']) ?></td>
                                <td><span class="badge bg-primary-subtle text-primary-emphasis border border-primary-subtle rounded-pill fs-6 px-3">"<?= e($s['nombre']) ?>"</span></td>
                                <td><?= badge_estado($s['turno']) ?></td>
                                <td class="text-muted"><?= (int)$s['orden'] ?></td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="<?= url('secciones/editar/' . $s['id']) ?>" class="btn btn-sm btn-outline-primary" title="Editar"><i class="bi bi-pencil"></i></a>
                                        <a href="<?= url('matriculas?seccion_id=' . $s['id']) ?>" class="btn btn-sm btn-outline-accent" title="Ver matrículas"><i class="bi bi-people"></i></a>
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