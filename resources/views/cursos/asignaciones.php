<?php
/**
 * EduAlert JJP - recursos/views/cursos/asignaciones.php
 * Matriz de asignación docente ↔ curso ↔ sección por periodo
 */
?>
<div class="row g-3 mb-4 align-items-center">
    <div class="col">
        <h2 class="fw-bold text-primary mb-1"><i class="bi bi-diagram-3 me-2 text-accent"></i>Asignación docente - curso</h2>
        <p class="text-muted mb-0 fs-7">Vincule a cada docente con el curso y la sección que dicta en el periodo</p>
    </div>
    <div class="col-auto">
        <a href="<?= url('cursos') ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Volver</a>
    </div>
</div>

<form method="get" class="card mb-4 border-0 shadow-sm">
    <div class="card-body py-3">
        <div class="row g-2 align-items-end">
            <div class="col-12 col-md-4">
                <label class="form-label">Periodo académico</label>
                <select name="periodo_id" class="form-select" onchange="this.form.submit()">
                    <?php foreach ($periodos as $p): ?>
                        <option value="<?= (int)$p['id'] ?>" <?= (int)$periodo_id === (int)$p['id'] ? 'selected' : '' ?>><?= e($p['nombre'] . ' ' . $p['anio']) ?><?= (int)$p['activo'] === 1 ? ' (activo)' : '' ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary"><i class="bi bi-arrow-repeat me-1"></i>Cargar</button>
            </div>
            <div class="col-12 col-md fs-7 text-muted">
                Periodo seleccionado: <b><?= $periodo ? e($periodo['nombre'] . ' ' . $periodo['anio']) : '-' ?></b>
            </div>
        </div>
    </div>
</form>

<?php if ($periodo && !empty($cursos) && !empty($secciones)): ?>
    <form method="post" action="<?= url('cursos/asignaciones') ?>" class="card border-0 shadow-sm">
        <?= csrf_field() ?>
        <input type="hidden" name="periodo_id" value="<?= (int)$periodo_id ?>">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-bordered align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="fw-semibold" style="min-width:200px">Curso</th>
                            <?php foreach ($secciones as $s): ?>
                                <th class="text-center fw-semibold" style="min-width:150px">
                                    <?= e($s['grado_nombre']) ?><br>"<?= e($s['nombre']) ?>" <span class="text-muted fs-8">· <?= e($s['turno']) ?></span>
                                </th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cursos as $c): ?>
                            <tr>
                                <td class="fw-semibold">
                                    <?= e($c['nombre']) ?>
                                    <?php if ($c['area']): ?><div class="text-muted fs-8"><?= e($c['area']) ?></div><?php endif; ?>
                                </td>
                                <?php foreach ($secciones as $s): ?>
                                    <?php $__doc = $mapa[$c['id'] . '-' . $s['id']] ?? 0; ?>
                                    <td class="p-1">
                                        <select name="asignacion[<?= (int)$c['id'] ?>][<?= (int)$s['id'] ?>]" class="form-select form-select-sm border-0">
                                            <option value="0">— Sin asignar —</option>
                                            <?php foreach ($docentes as $d): ?>
                                                <option value="<?= (int)$d['id'] ?>" <?= (int)$__doc === (int)$d['id'] ? 'selected' : '' ?>><?= e($d['apellidos'] . ', ' . $d['nombres']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white text-end">
            <button type="submit" class="btn btn-accent px-4"><i class="bi bi-check2-circle me-1"></i>Guardar asignaciones</button>
        </div>
    </form>
<?php elseif ($periodo): ?>
    <div class="card border-0 shadow-sm"><div class="card-body"><div class="empty-state"><i class="bi bi-inbox"></i>Registre primero cursos y secciones para asignar docentes.</div></div></div>
<?php endif; ?>