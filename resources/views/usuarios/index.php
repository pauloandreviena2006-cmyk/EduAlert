<?php
/**
 * EduAlert JJP - recursos/views/usuarios/index.php
 */
$yo = (int)current_user_id();
?>
<div class="row g-3 mb-4 align-items-center">
    <div class="col">
        <h2 class="fw-bold text-primary mb-1"><i class="bi bi-people-fill me-2 text-accent"></i>Usuarios del sistema</h2>
        <p class="text-muted mb-0 fs-7">Cuentas de acceso por rol (administración)</p>
    </div>
    <div class="col-auto">
        <a href="<?= url('usuarios/crear') ?>" class="btn btn-accent"><i class="bi bi-person-plus-fill me-1"></i>Nuevo usuario</a>
    </div>
</div>

<div class="card mb-4 border-0 shadow-sm">
    <div class="card-body py-3">
        <form method="get" action="<?= url('usuarios') ?>" class="row g-2 align-items-end">
            <div class="col-12 col-md-5 col-xl-4">
                <label class="form-label">Buscar</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" name="q" class="form-control" placeholder="Nombre, apellido o correo..." value="<?= e($q) ?>">
                </div>
            </div>
            <div class="col-12 col-md-4 col-xl-3">
                <label class="form-label">Rol</label>
                <select name="rol_id" class="form-select">
                    <option value="0">Todos</option>
                    <?php foreach ($roles as $r): ?>
                        <option value="<?= (int)$r['id'] ?>" <?= $rol_id == $r['id'] ? 'selected' : '' ?>><?= e($r['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-auto d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="bi bi-funnel me-1"></i>Filtrar</button>
                <a href="<?= url('usuarios') ?>" class="btn btn-outline-secondary"><i class="bi bi-x-lg"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-datatable align-middle mb-0">
                <thead>
                    <tr><th>Usuario</th><th>Correo</th><th>Rol</th><th>Teléfono</th><th>Estado</th><th class="no-sort">Acciones</th></tr>
                </thead>
                <tbody>
                    <?php if (empty($usuarios)): ?>
                        <tr><td colspan="6"><div class="empty-state"><i class="bi bi-inbox"></i>No se encontraron usuarios con los filtros aplicados.</div></td></tr>
                    <?php else: ?>
                        <?php foreach ($usuarios as $u): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="avatar-sm"><?= e(mb_substr($u['nombres'], 0, 1) . mb_substr($u['apellidos'], 0, 1)) ?></span>
                                        <div>
                                            <div class="fw-semibold"><?= e($u['apellidos'] . ', ' . $u['nombres']) ?></div>
                                            <?php if ($yo === (int)$u['id']): ?><span class="badge bg-light text-dark border">Usted</span><?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td><?= e($u['email']) ?></td>
                                <td><span class="badge bg-primary-subtle text-primary-emphasis rounded-pill"><?= e($u['rol_nombre']) ?></span></td>
                                <td class="fs-7"><?= e($u['telefono'] ?? '-') ?></td>
                                <td>
                                    <span class="badge bg-<?= (int)$u['estado'] === 1 ? 'success' : 'secondary' ?>-subtle text-<?= (int)$u['estado'] === 1 ? 'success' : 'secondary' ?>-emphasis border rounded-pill"><?= (int)$u['estado'] === 1 ? 'Activo' : 'Inactivo' ?></span>
                                </td>
                                <td>
                                    <?php if ($yo !== (int)$u['id']): ?>
                                        <div class="d-flex gap-1">
                                            <a href="<?= url('usuarios/editar/' . $u['id']) ?>" class="btn btn-sm btn-outline-primary" title="Editar"><i class="bi bi-pencil"></i></a>
                                            <form method="post" action="<?= url('usuarios/estado/' . $u['id']) ?>" data-confirm='{"titulo":"<?= (int)$u['estado'] === 1 ? 'Desactivar usuario' : 'Reactivar usuario' ?>","texto":"¿Desea <?= (int)$u['estado'] === 1 ? 'desactivar' : 'reactivar' ?> a <?= e($u['nombres'] . ' ' . $u['apellidos']) ?>?"}'>
                                                <?= csrf_field() ?>
                                                <button type="submit" class="btn btn-sm btn-outline-<?= (int)$u['estado'] === 1 ? 'danger' : 'success' ?>" title="<?= (int)$u['estado'] === 1 ? 'Desactivar' : 'Reactivar' ?>"><i class="bi bi-power"></i></button>
                                            </form>
                                            <form method="post" action="<?= url('usuarios/eliminar/' . $u['id']) ?>" data-confirm='{"titulo":"Eliminar usuario","texto":"¿Desea eliminar a <?= e($u['nombres'] . ' ' . $u['apellidos']) ?>? Esta acción no se puede deshacer."}'>
                                                <?= csrf_field() ?>
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar"><i class="bi bi-trash"></i></button>
                                            </form>
                                        </div>
                                    <?php else: ?>
                                        <a href="<?= url('perfil') ?>" class="btn btn-sm btn-outline-accent"><i class="bi bi-person-gear me-1"></i>Mi perfil</a>
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