<?php
/**
 * EduAlert JJP - recursos/views/alertas/index.php
 */
?>
<div class="row g-3 mb-4 align-items-center">
    <div class="col">
        <h2 class="fw-bold text-primary mb-1"><i class="bi bi-bell-fill me-2 text-danger"></i>Alertas académicas</h2>
        <p class="text-muted mb-0 fs-7">Alertas generadas por el motor de riesgo temprano</p>
    </div>
    <div class="col-auto d-flex gap-2 flex-wrap">
        <?php if (can('alertas.generar')): ?>
            <form method="post" action="<?= url('alertas/generar') ?>" data-confirm='{"titulo":"Generar alertas","texto":"Se ejecutará el motor de alertas para todos los estudiantes del periodo. ¿Desea continuar?"}'>
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-outline-accent"><i class="bi bi-lightning-charge me-1"></i>Ejecutar motor de alertas</button>
            </form>
        <?php endif; ?>
        <a href="<?= url('alertas/exportar?' . http_build_query(array_filter(['q' => $q, 'nivel_id' => $nivel_id, 'estado' => $estado, 'grado_id' => $grado_id, 'periodo_id' => $periodo_id, 'formato' => 'csv']))) ?>" class="btn btn-outline-secondary" <?= can('reportes.exportar') ? '' : 'disabled' ?>><i class="bi bi-download me-1"></i>Exportar</a>
    </div>
</div>

<div class="card mb-4 border-0 shadow-sm">
    <div class="card-body py-3">
        <form method="get" action="<?= url('alertas') ?>" class="row g-2 align-items-end">
            <div class="col-12 col-md-4 col-xl-3">
                <label class="form-label">Buscar</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" name="q" class="form-control" placeholder="Código, DNI o estudiante..." value="<?= e($q) ?>">
                </div>
            </div>
            <div class="col-6 col-md-3 col-xl-2">
                <label class="form-label">Nivel de riesgo</label>
                <select name="nivel_id" class="form-select">
                    <option value="0">Todos</option>
                    <?php foreach ($niveles as $n): ?>
                        <option value="<?= (int)$n['id'] ?>" <?= $nivel_id == $n['id'] ? 'selected' : '' ?>><?= e($n['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-6 col-md-3 col-xl-2">
                <label class="form-label">Estado</label>
                <select name="estado" class="form-select">
                    <option value="">Todos</option>
                    <?php foreach ($estados as $es): ?>
                        <option value="<?= e($es) ?>" <?= $estado === $es ? 'selected' : '' ?>><?= e($es) ?></option>
                    <?php endforeach; ?>
                </select>
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
                <a href="<?= url('alertas') ?>" class="btn btn-outline-secondary"><i class="bi bi-x-lg"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-datatable align-middle mb-0">
                <thead>
                    <tr><th>Estudiante</th><th>Grado / Sección</th><th>Riesgo</th><th>Puntaje</th><th>Motivos</th><th>Estado</th><th>Fecha</th><th class="no-sort">Acciones</th></tr>
                </thead>
                <tbody>
                    <?php if (empty($alertas)): ?>
                        <tr><td colspan="8"><div class="empty-state"><i class="bi bi-bell-slash"></i>No se encontraron alertas con los filtros aplicados.<br><span class="fs-8">Ejecute el «Motor de alertas» para generar nuevas.</span></div></td></tr>
                    <?php else: ?>
                        <?php foreach ($alertas as $a): ?>
                            <tr>
                                <td>
                                    <div class="fw-semibold"><?= e($a['apellidos'] . ', ' . $a['nombres']) ?></div>
                                    <div class="text-muted fs-8"><?= e($a['codigo']) ?> · DNI <?= e($a['dni']) ?></div>
                                </td>
                                <td><?= e($a['grado_nombre'] ?? '-') ?> "<?= e($a['seccion_nombre'] ?? '-') ?>"<div class="text-muted fs-8"><?= e($a['periodo_nombre'] ?? '') ?> <?= e($a['periodo_anio'] ?? '') ?></div></td>
                                <td>
                                    <span class="badge bg-<?= e($a['riesgo_color']) ?>-subtle text-<?= e($a['riesgo_color']) ?>-emphasis border border-<?= e($a['riesgo_color']) ?>-subtle rounded-pill">
                                        <i class="bi bi-<?= $a['riesgo_color'] === 'danger' ? 'exclamation-triangle' : ($a['riesgo_color'] === 'warning' ? 'exclamation-circle' : 'check-circle') ?> me-1"></i><?= e($a['riesgo_nombre']) ?>
                                    </span>
                                </td>
                                <td class="fw-bold text-center"><?= (int)$a['puntaje'] ?></td>
                                <td><span class="badge bg-light text-dark rounded-pill"><?= (int)$a['total_motivos'] ?> motivo<?= (int)$a['total_motivos'] === 1 ? '' : 's' ?></span></td>
                                <td><?= badge_estado($a['estado']) ?></td>
                                <td class="fs-7"><?= f_fecha_hora($a['fecha_generacion']) ?></td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="<?= url('alertas/ver/' . $a['id']) ?>" class="btn btn-sm btn-outline-accent" title="Ver detalle"><i class="bi bi-eye"></i></a>
                                        <?php if (can('seguimiento.gestion')): ?>
                                            <a href="<?= url('seguimiento/crear/' . $a['id']) ?>" class="btn btn-sm btn-outline-primary" title="Registrar seguimiento"><i class="bi bi-clipboard2-pulse"></i></a>
                                        <?php endif; ?>
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