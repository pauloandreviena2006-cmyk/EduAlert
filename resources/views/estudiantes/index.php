<?php
/**
 * EduAlert JJP - recursos/views/estudiantes/index.php
 * Listado de estudiantes con buscador, filtros, paginación y exportación
 */
?>
<div class="row g-3 mb-4 align-items-center">
    <div class="col">
        <h2 class="fw-bold text-primary mb-1"><i class="bi bi-people-fill me-2 text-accent"></i>Estudiantes</h2>
        <p class="text-muted mb-0 fs-7">Gestión del padrón de estudiantes de la institución</p>
    </div>
    <div class="col-auto d-flex gap-2 flex-wrap">
        <a href="<?= url('estudiantes/crear') ?>" class="btn btn-accent" <?= can('estudiantes.crear') ? '' : 'disabled' ?>>
            <i class="bi bi-person-plus-fill me-1"></i>Nuevo estudiante
        </a>
        <a href="<?= url('estudiantes/exportar?' . http_build_query(array_filter(['q' => $q, 'grado_id' => $grado_id, 'seccion_id' => $seccion_id, 'estado' => $estado, 'formato' => 'csv']))) ?>" class="btn btn-outline-accent" <?= can('estudiantes.exportar') ? '' : 'disabled' ?>>
            <i class="bi bi-download me-1"></i>Exportar
        </a>
    </div>
</div>

<!-- FILTROS -->
<div class="card mb-4 border-0 shadow-sm">
    <div class="card-body py-3">
        <form method="get" action="<?= url('estudiantes') ?>" class="row g-2 align-items-end">
            <div class="col-12 col-md-4 col-xl-3">
                <label class="form-label">Buscar</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" name="q" class="form-control" placeholder="Código, DNI o nombre..." value="<?= e($q) ?>">
                </div>
            </div>
            <div class="col-6 col-md-3 col-xl-2">
                <label class="form-label">Grado</label>
                <select name="grado_id" id="filtroGrado" class="form-select">
                    <option value="0">Todos</option>
                    <?php foreach ($grados as $g): ?>
                        <option value="<?= (int)$g['id'] ?>" <?= $grado_id == $g['id'] ? 'selected' : '' ?>><?= e($g['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-6 col-md-3 col-xl-2">
                <label class="form-label">Sección</label>
                <select name="seccion_id" id="filtroSeccion" class="form-select">
                    <option value="0">Todas</option>
                    <?php foreach ($secciones as $s): ?>
                        <option value="<?= (int)$s['id'] ?>" <?= $seccion_id == $s['id'] ? 'selected' : '' ?>><?= e($s['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-6 col-md-3 col-xl-2">
                <label class="form-label">Periodo</label>
                <select name="periodo_id" class="form-select">
                    <option value="0">Último</option>
                    <?php foreach ($periodos as $p): ?>
                        <option value="<?= (int)$p['id'] ?>" <?= $periodo_id == $p['id'] ? 'selected' : '' ?>><?= e($p['nombre'] . ' ' . $p['anio']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-6 col-md-2 col-xl-1">
                <label class="form-label">Estado</label>
                <select name="estado" class="form-select">
                    <option value="">Todos</option>
                    <option value="activo" <?= $estado === 'activo' ? 'selected' : '' ?>>Activos</option>
                    <option value="inactivo" <?= $estado === 'inactivo' ? 'selected' : '' ?>>Inactivos</option>
                </select>
            </div>
            <div class="col-auto d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="bi bi-funnel me-1"></i>Filtrar</button>
                <a href="<?= url('estudiantes') ?>" class="btn btn-outline-secondary"><i class="bi bi-x-lg"></i></a>
            </div>
        </form>
    </div>
</div>

<!-- TABLA -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-datatable align-middle mb-0" data-paginado>
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Estudiante</th>
                        <th>DNI</th>
                        <th>Grado / Sección</th>
                        <th>Nivel de riesgo</th>
                        <th>Puntaje</th>
                        <th class="no-sort">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($estudiantes)): ?>
                        <tr><td colspan="7"><div class="empty-state"><i class="bi bi-inbox"></i>No se encontraron estudiantes con los filtros aplicados.</div></td></tr>
                    <?php else: ?>
                        <?php foreach ($estudiantes as $e): ?>
                            <tr>
                                <td><span class="badge bg-primary-subtle text-primary-emphasis border border-primary-subtle rounded-pill"><?= e($e['codigo']) ?></span></td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="avatar-sm"><?= e(mb_substr($e['nombres'], 0, 1) . mb_substr($e['apellidos'], 0, 1)) ?></span>
                                        <div>
                                            <div class="fw-semibold"><?= e($e['apellidos'] . ', ' . $e['nombres']) ?></div>
                                            <div class="text-muted fs-8"><?= $e['sexo'] === 'F' ? 'Femenino' : 'Masculino' ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td><?= e($e['dni']) ?></td>
                                <td>
                                    <div><?= e($e['grado_nombre'] ?? '-') ?> "<?= e($e['seccion_nombre'] ?? '-') ?>"</div>
                                    <div class="text-muted fs-8"><?= e($e['seccion_turno'] ?? '') ?></div>
                                </td>
                                <td>
                                    <?php if (!empty($e['riesgo_nombre'])): ?>
                                        <span class="badge bg-<?= e($e['riesgo_color']) ?>-subtle text-<?= e($e['riesgo_color']) ?>-emphasis border border-<?= e($e['riesgo_color']) ?>-subtle rounded-pill">
                                            <i class="bi bi-<?= $e['riesgo_color'] === 'danger' ? 'exclamation-triangle' : ($e['riesgo_color'] === 'warning' ? 'exclamation-circle' : 'check-circle') ?> me-1"></i><?= e($e['riesgo_nombre']) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle rounded-pill">Sin evaluar</span>
                                    <?php endif; ?>
                                </td>
                                <td class="fw-bold"><?= $e['puntaje_riesgo'] !== null ? $e['puntaje_riesgo'] : '—' ?></td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="<?= url('estudiantes/ver/' . $e['id']) ?>" class="btn btn-sm btn-outline-accent" title="Ver ficha"><i class="bi bi-eye"></i></a>
                                        <?php if (can('estudiantes.editar')): ?>
                                            <a href="<?= url('estudiantes/editar/' . $e['id']) ?>" class="btn btn-sm btn-outline-primary" title="Editar"><i class="bi bi-pencil"></i></a>
                                        <?php endif; ?>
                                        <?php if (can('estudiantes.eliminar')): ?>
                                            <form method="post" action="<?= url('estudiantes/estado/' . $e['id']) ?>" data-confirm='{"titulo":"<?= (int)$e['estado'] === 1 ? 'Desactivar estudiante' : 'Reactivar estudiante' ?>","texto":"¿Desea <?= (int)$e['estado'] === 1 ? 'desactivar' : 'reactivar' ?> a <?= e($e['nombres'] . ' ' . $e['apellidos']) ?>?"}'>
                                                <?= csrf_field() ?>
                                                <button type="submit" class="btn btn-sm btn-outline-<?= (int)$e['estado'] === 1 ? 'danger' : 'success' ?>" title="<?= (int)$e['estado'] === 1 ? 'Desactivar' : 'Reactivar' ?>">
                                                    <i class="bi bi-power"></i>
                                                </button>
                                            </form>
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
    <?php if ($total_paginas > 1): ?>
        <div class="card-body border-top py-3">
            <nav>
                <ul class="pagination justify-content-center mb-0">
                    <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                        <li class="page-item <?= $i === $pagina ? 'active' : '' ?>">
                            <a class="page-link" href="<?= url('estudiantes?' . http_build_query(array_filter(['q' => $q, 'grado_id' => $grado_id, 'seccion_id' => $seccion_id, 'periodo_id' => $periodo_id, 'estado' => $estado, 'pagina' => $i]))) ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
            <div class="text-center text-muted fs-8 mt-2"><?= number_format($total) ?> estudiantes encontrados · Página <?= $pagina ?> de <?= $total_paginas ?></div>
        </div>
    <?php endif; ?>
</div>

<script>
$(function () {
    // Filtro secciones en cascada según grado (evita combinaciones inválidas)
    const $grado = $('#filtroGrado');
    const $seccion = $('#filtroSeccion');
    const original = $seccion.html();
    $grado.on('change', function () {
        const gid = $(this).val();
        if (!gid || gid === '0') { $seccion.html(original); return; }
        const url = window.BASE_URL + '/secciones/por-grado/' + gid;
        $.getJSON(url, function (data) {
            let html = '<option value="0">Todas</option>';
            data.forEach(function (s) { html += '<option value="' + s.id + '">' + s.nombre + '</option>'; });
            $seccion.html(html);
            $seccion.val('<?= (int)$seccion_id ?>');
        });
    });
});
</script>