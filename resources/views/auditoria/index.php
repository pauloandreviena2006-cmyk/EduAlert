<?php
/**
 * EduAlert JJP - recursos/views/auditoria/index.php
 */
?>
<div class="row g-3 mb-4 align-items-center">
    <div class="col">
        <h2 class="fw-bold text-primary mb-1"><i class="bi bi-shield-check me-2 text-accent"></i>Auditoría del sistema</h2>
        <p class="text-muted mb-0 fs-7">Registro de todas las acciones realizadas en el sistema</p>
    </div>
    <div class="col-auto">
        <span class="badge bg-light text-dark border rounded-pill"><i class="bi bi-file-earmark-text me-1"></i><?= count($registros) ?> eventos</span>
    </div>
</div>

<div class="card mb-4 border-0 shadow-sm">
    <div class="card-body py-3">
        <form method="get" action="<?= url('auditoria') ?>" class="row g-2 align-items-end">
            <div class="col-12 col-md-4 col-xl-3">
                <label class="form-label">Módulo</label>
                <select name="modulo" class="form-select">
                    <option value="todos">Todos</option>
                    <?php foreach ($modulos as $m): ?>
                        <option value="<?= e($m) ?>" <?= $modulo === $m ? 'selected' : '' ?>><?= e($m) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 col-md-5 col-xl-4">
                <label class="form-label">Buscar</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" name="q" class="form-control" placeholder="Acción, descripción o usuario..." value="<?= e($q) ?>">
                </div>
            </div>
            <div class="col-auto d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="bi bi-funnel me-1"></i>Filtrar</button>
                <a href="<?= url('auditoria') ?>" class="btn btn-outline-secondary"><i class="bi bi-x-lg"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-datatable align-middle mb-0">
                <thead>
                    <tr><th>Fecha / Hora</th><th>Usuario</th><th>Rol</th><th>Módulo</th><th>Acción</th><th>Descripción</th></tr>
                </thead>
                <tbody>
                    <?php if (empty($registros)): ?>
                        <tr><td colspan="6"><div class="empty-state"><i class="bi bi-inbox"></i>No hay eventos de auditoría con los filtros aplicados.</div></td></tr>
                    <?php else: ?>
                        <?php foreach ($registros as $r): ?>
                            <tr>
                                <td class="fs-7 text-nowrap"><?= f_fecha_hora($r['created_at']) ?></td>
                                <td class="fw-semibold"><?= e(trim(($r['nombres'] ?? '') . ' ' . ($r['apellidos'] ?? ''))) ?: '<span class="text-muted">Sistema</span>' ?></td>
                                <td><span class="badge bg-secondary-subtle text-secondary-emphasis rounded-pill"><?= e($r['rol_nombre'] ?? '-') ?></span></td>
                                <td><span class="badge bg-primary-subtle text-primary-emphasis rounded-pill"><?= e($r['modulo']) ?></span></td>
                                <td class="fw-semibold"><?= e($r['accion']) ?></td>
                                <td class="text-muted fs-7"><?= e($r['descripcion']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>