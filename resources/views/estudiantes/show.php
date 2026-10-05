<?php
/**
 * EduAlert JJP - recursos/views/estudiantes/show.php
 * Ficha completa del estudiante: datos, matrícula, riesgo, notas, asistencia, alertas y seguimiento
 */
$riesgoIcono = (($est['riesgo_color'] ?? 'secondary') === 'danger') ? 'exclamation-triangle' : ((($est['riesgo_color'] ?? '') === 'warning') ? 'exclamation-circle' : 'check-circle');
?>
<div class="row g-3 mb-4 align-items-center">
    <div class="col">
        <h2 class="fw-bold text-primary mb-1"><i class="bi bi-person-badge me-2 text-accent"></i>Ficha del estudiante</h2>
        <p class="text-muted mb-0 fs-7">Expediente académico integral</p>
    </div>
    <div class="col-auto d-flex gap-2 flex-wrap">
        <?php if (can('estudiantes.editar')): ?>
            <a href="<?= url('estudiantes/editar/' . $est['id']) ?>" class="btn btn-outline-primary"><i class="bi bi-pencil me-1"></i>Editar</a>
        <?php endif; ?>
        <a href="<?= url('estudiantes') ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Volver</a>
    </div>
</div>

<div class="row g-4">
    <!-- ===== Columna principal ===== -->
    <div class="col-12 col-xl-8">
        <!-- Tarjeta de identidad -->
        <div class="card border-0 shadow-sm mb-4 overflow-hidden">
            <div class="card-body p-0">
                <div class="profile-hero">
                    <div class="profile-avatar"><?= e(mb_substr($est['nombres'], 0, 1) . mb_substr($est['apellidos'], 0, 1)) ?></div>
                    <div class="flex-grow-1">
                        <h3 class="fw-bold mb-1"><?= e($est['apellidos'] . ', ' . $est['nombres']) ?></h3>
                        <div class="d-flex flex-wrap gap-2 fs-7">
                            <span class="badge bg-primary"><i class="bi bi-person-vcard me-1"></i><?= e($est['codigo']) ?></span>
                            <span class="badge bg-secondary"><i class="bi bi-card-text me-1"></i>DNI: <?= e($est['dni']) ?></span>
                            <span class="badge bg-info"><i class="bi bi-gender-ambiguous me-1"></i><?= $est['sexo'] === 'F' ? 'Femenino' : 'Masculino' ?></span>
                            <span class="badge bg-light text-dark"><i class="bi bi-calendar me-1"></i><?= f_fecha($est['fecha_nacimiento']) ?></span>
                        </div>
                    </div>
                    <div class="text-end">
                        <?php if (!empty($est['riesgo_nombre'])): ?>
                            <span class="badge bg-<?= e($est['riesgo_color']) ?>-subtle text-<?= e($est['riesgo_color']) ?>-emphasis border border-<?= e($est['riesgo_color']) ?>-subtle rounded-pill fs-7">
                                <i class="bi bi-<?= $riesgoIcono ?> me-1"></i><?= e($est['riesgo_nombre']) ?>
                            </span>
                            <div class="fs-7 text-muted mt-1">Puntaje: <b><?= (int)$est['puntaje_riesgo'] ?> pts</b></div>
                        <?php else: ?>
                            <span class="badge bg-secondary-subtle text-secondary-emphasis rounded-pill">Sin riesgo evaluado</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Matrícula actual -->
        <?php if ($matricula): ?>
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 fw-semibold"><i class="bi bi-mortarboard me-2 text-accent"></i>Matrícula vigente</div>
                <div class="card-body">
                    <div class="row text-center g-3">
                        <div class="col-4"><div class="display-6 fw-bold text-primary"><?= e($matricula['grado_nombre']) ?></div><div class="text-muted fs-7">Grado</div></div>
                        <div class="col-4"><div class="display-6 fw-bold text-accent">"<?= e($matricula['seccion_nombre']) ?>"</div><div class="text-muted fs-7">Sección · <?= e($matricula['turno']) ?></div></div>
                        <div class="col-4"><div class="display-6 fw-bold text-secondary"><?= e(($matricula['periodo_nombre'] ?? '') ?: ((string)(\App\Models\Periodo::activo()['nombre'] ?? '') . ' ' . (string)(\App\Models\Periodo::activo()['anio'] ?? ''))) ?></div><div class="text-muted fs-7">Periodo</div></div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Calificaciones -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="fw-semibold"><i class="bi bi-clipboard-data me-2 text-accent"></i>Calificaciones del periodo</div>
                <form method="get" class="d-flex gap-2 align-items-center">
                    <select name="periodo_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="0">Todos los periodos</option>
                        <?php foreach ($periodos as $p): ?>
                            <option value="<?= (int)$p['id'] ?>" <?= (int)$periodo_filtro === (int)$p['id'] ? 'selected' : '' ?>><?= e($p['nombre'] . ' ' . $p['anio']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </form>
            </div>
            <div class="card-body p-0">
                <?php if (empty($calificaciones)): ?>
                    <div class="empty-state"><i class="bi bi-inbox"></i>No hay calificaciones registradas para el periodo seleccionado.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light"><tr><th>Curso</th><th>Competencia</th><th class="text-center">Nota</th><th class="text-center">Estado</th></tr></thead>
                            <tbody>
                                <?php $__aprob = (float)setting('promedio_aprobatorio', 11); ?>
                                <?php foreach ($calificaciones as $c): ?>
                                    <tr>
                                        <td class="fw-semibold"><?= e($c['curso_nombre']) ?></td>
                                        <td class="text-muted fs-7"><?= e($c['competencia'] ?? '-') ?></td>
                                        <td class="text-center"><span class="badge fs-6 <?= (float)$c['nota'] >= $__aprob ? 'text-success' : 'text-danger' ?>"><?= f_numero($c['nota'], 1) ?></span></td>
                                        <td class="text-center"><?= (float)$c['nota'] >= $__aprob ? '<span class="badge bg-success-subtle text-success-emphasis rounded-pill">Aprobado</span>' : '<span class="badge bg-danger-subtle text-danger-emphasis rounded-pill">Desaprobado</span>' ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Alertas -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 fw-semibold"><i class="bi bi-bell-fill me-2 text-danger"></i>Alertas académicas</div>
            <div class="card-body p-0">
                <?php if (empty($alertas)): ?>
                    <div class="empty-state"><i class="bi bi-check2-circle"></i>Este estudiante no tiene alertas registradas.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light"><tr><th>Periodo</th><th>Motivos</th><th>Estado</th><th>Fecha</th><th></th></tr></thead>
                            <tbody>
                                <?php foreach ($alertas as $al): ?>
                                    <tr>
                                        <td><?= e($al['periodo_nombre'] ?? '-') ?></td>
                                        <td class="text-muted fs-7"><?= e(mb_substr($al['descripcion'] ?? '', 0, 70)) ?>...</td>
                                        <td><?= badge_estado($al['estado']) ?></td>
                                        <td class="fs-7"><?= f_fecha_hora($al['fecha_generacion']) ?></td>
                                        <td><a href="<?= url('alertas/ver/' . $al['id']) ?>" class="btn btn-sm btn-outline-accent"><i class="bi bi-eye"></i></a></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Seguimientos -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 fw-semibold"><i class="bi bi-clipboard2-pulse me-2 text-accent"></i>Seguimientos realizados</div>
            <div class="card-body">
                <?php if (empty($seguimientos)): ?>
                    <div class="empty-state small"><i class="bi bi-inbox"></i>Sin seguimientos registrados.</div>
                <?php else: ?>
                    <div class="timeline">
                        <?php foreach ($seguimientos as $seg): ?>
                            <div class="timeline-item">
                                <div class="timeline-dot"></div>
                                <div class="timeline-content">
                                    <div class="d-flex justify-content-between flex-wrap">
                                        <span class="badge bg-primary-subtle text-primary-emphasis rounded-pill"><?= e($seg['tipo_intervencion']) ?></span>
                                        <span class="text-muted fs-8"><?= f_fecha_hora($seg['fecha']) ?></span>
                                    </div>
                                    <p class="mb-1 mt-2"><?= e($seg['descripcion']) ?></p>
                                    <?php if (!empty($seg['compromiso'])): ?><div class="fs-8 text-muted"><i class="bi bi-hand-thumbs-up me-1"></i><?= e($seg['compromiso']) ?></div><?php endif; ?>
                                    <div class="fs-8 text-muted mt-1">Responsable: <b><?= e(trim(($seg['resp_nombres'] ?? '') . ' ' . ($seg['resp_apellidos'] ?? ''))) ?></b></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- ===== Columna lateral ===== -->
    <div class="col-12 col-xl-4">
        <!-- Datos generales -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 fw-semibold"><i class="bi bi-person-vcard me-2 text-accent"></i>Datos generales</div>
            <div class="card-body fs-7">
                <dl class="row mb-0">
                    <dt class="col-5 text-muted">DNI</dt><dd class="col-7"><?= e($est['dni']) ?></dd>
                    <dt class="col-5 text-muted">Fecha nac.</dt><dd class="col-7"><?= f_fecha($est['fecha_nacimiento']) ?></dd>
                    <dt class="col-5 text-muted">Sexo</dt><dd class="col-7"><?= $est['sexo'] === 'F' ? 'Femenino' : 'Masculino' ?></dd>
                    <dt class="col-5 text-muted">Teléfono</dt><dd class="col-7"><?= e($est['telefono'] ?? '-') ?></dd>
                    <dt class="col-5 text-muted">Correo</dt><dd class="col-7 text-break"><?= e($est['email'] ?? '-') ?></dd>
                    <dt class="col-5 text-muted">Dirección</dt><dd class="col-7"><?= e($est['direccion'] ?? '-') ?></dd>
                    <dt class="col-5 text-muted">Estado</dt><dd class="col-7"><?= (int)$est['estado'] === 1 ? '<span class="badge bg-success-subtle text-success-emphasis rounded-pill">Activo</span>' : '<span class="badge bg-secondary-subtle text-secondary-emphasis rounded-pill">Inactivo</span>' ?></dd>
                </dl>
            </div>
        </div>

        <!-- Apoderado -->
        <?php if (!empty($est['apo_nombres'])): ?>
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 fw-semibold"><i class="bi bi-people me-2 text-accent"></i>Apoderado</div>
                <div class="card-body fs-7">
                    <div class="fw-semibold"><?= e($est['apo_nombres'] . ' ' . $est['apo_apellidos']) ?></div>
                    <div class="text-muted"><?= e($est['apo_parentesco'] ?? '') ?> · <?= e($est['apo_ocupacion'] ?? '') ?></div>
                    <div class="mt-2"><i class="bi bi-card-text me-1"></i>DNI: <?= e($est['apo_dni'] ?? '-') ?></div>
                    <div><i class="bi bi-telephone me-1"></i><?= e($est['apo_telefono'] ?? '-') ?></div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Evolución por periodo -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 fw-semibold"><i class="bi bi-graph-up me-2 text-accent"></i>Evolución por periodo</div>
            <div class="card-body">
                <canvas id="evolucionChart" height="220"></canvas>
                <div class="table-responsive mt-3">
                    <table class="table table-sm align-middle mb-0 fs-8">
                        <thead><tr><th>Periodo</th><th class="text-center">Promedio</th><th class="text-center">Asistencia</th></tr></thead>
                        <tbody>
                            <?php foreach (array_reverse($promedios) as $pr): ?>
                                <?php $asr = $asistencia_resumen[$pr['periodo_id']] ?? null; ?>
                                <tr>
                                    <td><?= e($pr['periodo_nombre'] . ' ' . $pr['anio']) ?></td>
                                    <td class="text-center fw-semibold"><?= f_numero($pr['promedio'], 1) ?></td>
                                    <td class="text-center"><?= $asr ? f_porcentaje($asr['asistencia_pct']) : '-' ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(function () {
    if (window.Chart) {
        const url = window.BASE_URL + '/estudiantes/evolucion/<?= (int)$est['id'] ?>';
        $.getJSON(url, function (data) {
            const ctx = document.getElementById('evolucionChart');
            if (!ctx || ctx._chart) return;
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: data.promedios.map(function (r) { return r.label; }),
                    datasets: [
                        { label: 'Promedio', data: data.promedios.map(function (r) { return r.value; }), borderColor: '#0e9f9d', backgroundColor: 'rgba(14,159,157,.12)', fill: true, tension: .35, pointRadius: 4 },
                        { label: 'Asistencia %', data: data.asistencia.map(function (r) { return r.value; }), borderColor: '#f59e0b', backgroundColor: 'rgba(245,158,11,.10)', fill: true, tension: .35, pointRadius: 4, yAxisID: 'y1' }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'bottom' } },
                    scales: {
                        y: { beginAtZero: true, max: 20, title: { display: true, text: 'Promedio' } },
                        y1: { position: 'right', beginAtZero: true, max: 100, grid: { drawOnChartArea: false }, title: { display: true, text: 'Asistencia %' } }
                    }
                }
            });
        });
    }
});
</script>