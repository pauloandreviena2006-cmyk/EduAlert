<?php
/**
 * EduAlert JJP - recursos/views/cursos/index.php
 */
?>
<div class="row g-3 mb-4 align-items-center">
    <div class="col">
        <h2 class="fw-bold text-primary mb-1"><i class="bi bi-journal-bookmark me-2 text-accent"></i>Cursos</h2>
        <p class="text-muted mb-0 fs-7">Áreas curriculares de la institución</p>
    </div>
    <div class="col-auto d-flex gap-2">
        <a href="<?= url('cursos/asignaciones') ?>" class="btn btn-outline-accent"><i class="bi bi-diagram-3 me-1"></i>Asignar docentes</a>
        <a href="<?= url('cursos/crear') ?>" class="btn btn-accent"><i class="bi bi-plus-lg me-1"></i>Nuevo curso</a>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-datatable align-middle mb-0">
                <thead>
                    <tr><th>Curso</th><th>Área</th><th>Descripción</th><th>Estado</th><th class="no-sort">Acciones</th></tr>
                </thead>
                <tbody>
                    <?php if (empty($cursos)): ?>
                        <tr><td colspan="5"><div class="empty-state"><i class="bi bi-inbox"></i>No hay cursos registrados.</div></td></tr>
                    <?php else: ?>
                        <?php foreach ($cursos as $c): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="avatar-sm bg-accent-subtle text-accent-emphasis"><i class="bi bi-book"></i></span>
                                        <span class="fw-semibold"><?= e($c['nombre']) ?></span>
                                    </div>
                                </td>
                                <td><?= $c['area'] ? '<span class="badge bg-light text-dark rounded-pill">' . e($c['area']) . '</span>' : '-' ?></td>
                                <td class="text-muted fs-7"><?= e(mb_substr((string)$c['descripcion'], 0, 80)) ?><?= mb_strlen((string)$c['descripcion']) > 80 ? '...' : '' ?></td>
                                <td><span class="badge bg-success-subtle text-success-emphasis border rounded-pill">Activo</span></td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="<?= url('cursos/editar/' . $c['id']) ?>" class="btn btn-sm btn-outline-primary" title="Editar"><i class="bi bi-pencil"></i></a>
                                        <a href="<?= url('calificaciones?curso_id=' . $c['id']) ?>" class="btn btn-sm btn-outline-accent" title="Ver calificaciones"><i class="bi bi-clipboard-data"></i></a>
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