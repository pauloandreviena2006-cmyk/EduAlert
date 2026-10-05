<?php
/**
 * EduAlert JJP - recursos/views/docentes/index.php
 */
?>
<div class="row g-3 mb-4 align-items-center">
    <div class="col">
        <h2 class="fw-bold text-primary mb-1"><i class="bi bi-person-workspace me-2 text-accent"></i>Docentes</h2>
        <p class="text-muted mb-0 fs-7">Planilla docente de la institución</p>
    </div>
    <div class="col-auto">
        <a href="<?= url('docentes/crear') ?>" class="btn btn-accent" <?= can('docentes.gestion') ? '' : 'disabled' ?>>
            <i class="bi bi-person-plus-fill me-1"></i>Nuevo docente
        </a>
    </div>
</div>

<div class="card mb-4 border-0 shadow-sm">
    <div class="card-body py-3">
        <form method="get" action="<?= url('docentes') ?>" class="row g-2 align-items-end">
            <div class="col-12 col-md-4 col-xl-3">
                <label class="form-label">Buscar</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" name="q" class="form-control" placeholder="Nombre, DNI o código..." value="<?= e($q) ?>">
                </div>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary"><i class="bi bi-funnel me-1"></i>Filtrar</button>
                <a href="<?= url('docentes') ?>" class="btn btn-outline-secondary"><i class="bi bi-x-lg"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-datatable align-middle mb-0">
                <thead>
                    <tr><th>Código</th><th>Docente</th><th>DNI</th><th>Especialidad</th><th>Contacto</th><th>Estado</th><th class="no-sort">Acciones</th></tr>
                </thead>
                <tbody>
                    <?php if (empty($docentes)): ?>
                        <tr><td colspan="7"><div class="empty-state"><i class="bi bi-inbox"></i>No se encontraron docentes.</div></td></tr>
                    <?php else: ?>
                        <?php foreach ($docentes as $d): ?>
                            <tr>
                                <td><span class="badge bg-primary-subtle text-primary-emphasis border border-primary-subtle rounded-pill"><?= e($d['codigo']) ?></span></td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="avatar-sm"><?= e(mb_substr($d['nombres'], 0, 1) . mb_substr($d['apellidos'], 0, 1)) ?></span>
                                        <div>
                                            <div class="fw-semibold"><?= e($d['apellidos'] . ', ' . $d['nombres']) ?></div>
                                            <div class="text-muted fs-8"><?= e($d['especialidad'] ?? '-') ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td><?= e($d['dni']) ?></td>
                                <td><?= e($d['especialidad'] ?? '-') ?></td>
                                <td class="fs-8">
                                    <?php if (!empty($d['email'])): ?><div><i class="bi bi-envelope me-1"></i><?= e($d['email']) ?></div><?php endif; ?>
                                    <?php if (!empty($d['telefono'])): ?><div class="text-muted"><i class="bi bi-telephone me-1"></i><?= e($d['telefono']) ?></div><?php endif; ?>
                                </td>
                                <td>
                                    <?php $__activo = isset($d['user_estado']) ? (int)$d['user_estado'] : (int)$d['estado']; ?>
                                    <span class="badge bg-<?= $__activo === 1 ? 'success' : 'secondary' ?>-subtle text-<?= $__activo === 1 ? 'success' : 'secondary' ?>-emphasis border rounded-pill"><?= $__activo === 1 ? 'Activo' : 'Inactivo' ?></span>
                                </td>
                                <td>
                                    <?php if (can('docentes.gestion')): ?>
                                        <div class="d-flex gap-1">
                                            <a href="<?= url('docentes/editar/' . $d['id']) ?>" class="btn btn-sm btn-outline-primary" title="Editar"><i class="bi bi-pencil"></i></a>
                                            <form method="post" action="<?= url('docentes/estado/' . $d['id']) ?>" data-confirm='{"titulo":"Cambiar estado","texto":"¿Desea <?= $__activo === 1 ? 'desactivar' : 'reactivar' ?> a <?= e($d['nombres'] . ' ' . $d['apellidos']) ?>?"}'>
                                                <?= csrf_field() ?>
                                                <button type="submit" class="btn btn-sm btn-outline-<?= $__activo === 1 ? 'danger' : 'success' ?>" title="<?= $__activo === 1 ? 'Desactivar' : 'Reactivar' ?>"><i class="bi bi-power"></i></button>
                                            </form>
                                        </div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>