<?php
/**
 * EduAlert JJP - recursos/views/alertas/show.php
 * Detalle de una alerta: motivos, asignación, estados, seguimientos
 */
$al = $alerta;
?>
<div class="row g-3 mb-4 align-items-center">
    <div class="col">
        <h2 class="fw-bold text-primary mb-1"><i class="bi bi-bell-fill me-2 text-danger"></i>Detalle de alerta <span class="text-muted fs-5">#<?= (int)$al['id'] ?></span></h2>
        <p class="text-muted mb-0 fs-7"><?= f_fecha_hora($al['fecha_generacion']) ?> · <?= e($al['periodo_nombre']) ?> <?= e($al['periodo_anio']) ?></p>
    </div>
    <div class="col-auto d-flex gap-2 flex-wrap">
        <a href="<?= url('estudiantes/ver/' . $al['estudiante_id']) ?>" class="btn btn-outline-accent"><i class="bi bi-person-badge me-1"></i>Ficha del estudiante</a>
        <a href="<?= url('alertas') ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Volver</a>
    </div>
</div>

<div class="row g-4">
    <div class="col-12 col-xl-8">
        <!-- Estudiante y riesgo -->
        <div class="card border-0 shadow-sm mb-4 overflow-hidden">
            <div class="card-body p-0">
                <div class="profile-hero alert-danger-hero">
                    <div class="profile-avatar"><?= e(mb_substr($al['nombres'], 0, 1) . mb_substr($al['apellidos'], 0, 1)) ?></div>
                    <div class="flex-grow-1">
                        <h3 class="fw-bold mb-1"><?= e($al['apellidos'] . ', ' . $al['nombres']) ?></h3>
                        <div class="d-flex flex-wrap gap-2 fs-7">
                            <span class="badge bg-primary"><i class="bi bi-person-vcard me-1"></i><?= e($al['codigo']) ?></span>
                            <span class="badge bg-secondary">DNI: <?= e($al['dni']) ?></span>
                            <span class="badge bg-info"><?= e($al['grado_nombre'] ?? '-') ?> "<?= e($al['seccion_nombre'] ?? '-') ?>"</span>
                        </div>
                    </div>
                    <div class="text-end">
                        <span class="badge bg-<?= e($al['riesgo_color']) ?> rounded-pill fs-6 px-3 py-2">
                            <i class="bi bi-<?= $al['riesgo_color'] === 'danger' ? 'exclamation-triangle' : ($al['riesgo_color'] === 'warning' ? 'exclamation-circle' : 'check-circle') ?> me-1"></i><?= e($al['riesgo_nombre']) ?>
                        </span>
                        <div class="fs-7 text-white-50 mt-1">Puntaje de riesgo: <b class="text-white"><?= (int)$al['puntaje'] ?> pts</b></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Motivos -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 fw-semibold"><i class="bi bi-exclamation-octagon me-2 text-danger"></i>Motivos de la alerta (<?= count($motivos) ?>)</div>
            <div class="card-body">
                <?php if (empty($motivos)): ?>
                    <div class="empty-state small"><i class="bi bi-inbox"></i>Sin motivos detallados.</div>
                <?php else: ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($motivos as $m): ?>
                            <li class="list-group-item px-0">
                                <div class="d-flex align-items-start gap-2">
                                    <span class="badge bg-danger-subtle text-danger-emphasis rounded-circle mt-1"><i class="bi bi-exclamation"></i></span>
                                    <div>
                                        <?php $etiquetasMotivo = [
                            'promedio_bajo' => 'Rendimiento por debajo del mínimo',
                            'promedio' => 'Rendimiento académico',
                            'cursos_desaprobados' => 'Cursos desaprobados',
                            'asistencia' => 'Asistencia irregular',
                            'tardanzas' => 'Tardanzas reiteradas',
                            'inasistencia' => 'Inasistencias injustificadas',
                            'tendencia' => 'Tendencia descendente',
                            'sin_riesgo' => 'Sin riesgo detectado',
                        ]; ?>
                        <div class="fw-semibold"><?= e($etiquetasMotivo[$m['tipo']] ?? 'Motivo detectado') ?></div>
                                        <?php if (!empty($m['detalle'])): ?><div class="text-muted fs-7"><?= e($m['detalle']) ?></div><?php endif; ?>
                                    </div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>

        <!-- Seguimientos -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="fw-semibold"><i class="bi bi-clipboard2-pulse me-2 text-accent"></i>Seguimientos (<?= count($seguimientos) ?>)</div>
                <?php if (can('seguimiento.gestion')): ?>
                    <a href="<?= url('seguimiento/crear/' . $al['id']) ?>" class="btn btn-sm btn-accent"><i class="bi bi-plus-lg me-1"></i>Nuevo seguimiento</a>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <?php if (empty($seguimientos)): ?>
                    <div class="empty-state small"><i class="bi bi-inbox"></i>Aún no se registran intervenciones. Registre el primer seguimiento.</div>
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
                                    <?php if (!empty($seg['acuerdo'])): ?><div class="fs-8 text-muted"><i class="bi bi-journal-check me-1"></i><b>Acuerdo:</b> <?= e($seg['acuerdo']) ?></div><?php endif; ?>
                                    <?php if (!empty($seg['compromiso'])): ?><div class="fs-8 text-muted"><i class="bi bi-hand-thumbs-up me-1"></i><b>Compromiso:</b> <?= e($seg['compromiso']) ?></div><?php endif; ?>
                                    <?php if (!empty($seg['proxima_revision'])): ?><div class="fs-8 text-muted"><i class="bi bi-calendar-event me-1"></i><b>Próxima revisión:</b> <?= f_fecha($seg['proxima_revision']) ?></div><?php endif; ?>
                                    <div class="fs-8 text-muted mt-1">Responsable: <b><?= e(trim(($seg['resp_nombres'] ?? '') . ' ' . ($seg['resp_apellidos'] ?? ''))) ?></b></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-12 col-xl-4">
        <!-- Estado y acciones -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 fw-semibold"><i class="bi bi-sliders me-2 text-accent"></i>Gestión de la alerta</div>
            <div class="card-body">
                <div class="mb-3 text-center">
                    <?= badge_estado($al['estado']) ?>
                </div>
                <?php if (can('alertas.gestion')): ?>
                    <form method="post" action="<?= url('alertas/estado/' . $al['id']) ?>" class="mb-3">
                        <?= csrf_field() ?>
                        <label class="form-label">Cambiar estado</label>
                        <div class="input-group">
                            <select name="estado" class="form-select">
                                <?php foreach (\App\Models\Alerta::ESTADOS as $__es): ?>
                                    <option value="<?= $__es ?>" <?= $al['estado'] === $__es ? 'selected' : '' ?>><?= $__es ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" class="btn btn-primary"><i class="bi bi-arrow-repeat"></i></button>
                        </div>
                    </form>
                    <form method="post" action="<?= url('alertas/asignar/' . $al['id']) ?>">
                        <?= csrf_field() ?>
                        <label class="form-label">Asignar responsable</label>
                        <div class="input-group">
                            <select name="responsable_id" class="form-select">
                                <option value="">Seleccione...</option>
                                <?php foreach ($usuarios as $u): ?>
                                    <option value="<?= (int)$u['id'] ?>" <?= (int)($al['responsable_id'] ?? 0) === (int)$u['id'] ? 'selected' : '' ?>><?= e($u['apellidos'] . ', ' . $u['nombres']) ?> (<?= e($u['rol_nombre']) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" class="btn btn-accent"><i class="bi bi-person-check"></i></button>
                        </div>
                    </form>
                <?php endif; ?>
                <?php if (!empty($al['resp_nombres'])): ?>
                    <div class="alert alert-light border mb-0 mt-3 fs-7">
                        <i class="bi bi-person-badge me-1"></i><b>Responsable actual:</b> <?= e(trim($al['resp_nombres'] . ' ' . $al['resp_apellidos'])) ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Descripción del nivel -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 fw-semibold"><i class="bi bi-info-circle me-2 text-accent"></i>Descripción del riesgo</div>
            <div class="card-body fs-7">
                <span class="badge bg-<?= e($al['riesgo_color']) ?>-subtle text-<?= e($al['riesgo_color']) ?>-emphasis rounded-pill mb-2"><?= e($al['riesgo_nombre']) ?></span>
                <p class="mb-0 text-muted"><?= e($al['riesgo_descripcion'] ?? 'Nivel de riesgo asignado según el puntaje obtenido por el motor de alertas.') ?></p>
            </div>
        </div>

        <!-- Promedio por periodo -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 fw-semibold"><i class="bi bi-graph-up me-2 text-accent"></i>Promedio histórico</div>
            <div class="card-body">
                <?php if (empty($promedios)): ?>
                    <div class="empty-state small"><i class="bi bi-inbox"></i>Sin calificaciones registradas.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0 fs-8">
                            <thead><tr><th>Periodo</th><th class="text-center">Promedio</th></tr></thead>
                            <tbody>
                                <?php foreach ($promedios as $pr): ?>
                                    <tr>
                                        <td><?= e($pr['nombre'] . ' ' . $pr['anio']) ?></td>
                                        <td class="text-center fw-bold"><?= f_numero($pr['prom'], 1) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>