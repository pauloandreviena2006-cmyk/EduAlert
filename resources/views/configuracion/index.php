<?php
/**
 * EduAlert JJP - recursos/views/configuracion/index.php
 * Parámetros configurables del sistema y del motor de alertas
 */
// Definición de campos: grupo => [clave => [etiqueta, ayuda, tipo]]
$grupos = [
    'institucion' => [
        'titulo' => 'Datos de la institución',
        'icono' => 'bi-buildings',
        'campos' => [
            'institucion_nombre' => ['Nombre de la institución', 'Se muestra en el login y los reportes.', 'text'],
            'institucion_director' => ['Director(a)', 'Nombre del director para los reportes.', 'text'],
            'institucion_ciudad' => ['Ciudad / ubicación', '', 'text'],
            'anio_lectivo' => ['Año lectivo activo', 'Usado para los códigos de los estudiantes.', 'number'],
        ],
    ],
    'rendimiento' => [
        'titulo' => 'Rendimiento académico',
        'icono' => 'bi-clipboard-data',
        'campos' => [
            'promedio_aprobatorio' => ['Nota mínima aprobatoria', 'De 0 a 20.', 'number'],
            'riesgo_bajo_min' => ['Promedio mínimo para riesgo bajo', 'Promedios a partir de este valor se consideran sin riesgo.', 'number'],
            'riesgo_medio_min' => ['Promedio mínimo para riesgo medio', 'Promedios entre este valor y el límite superior se consideran de riesgo medio.', 'number'],
            'tendencia_umbral' => ['Umbral de descenso', 'Diferencia de promedio que indica tendencia descendente.', 'number'],
            'semanas_retrospectivas_tendencia' => ['Semanas para evaluar tendencia', '', 'number'],
        ],
    ],
    'puntaje' => [
        'titulo' => 'Puntajes del motor de riesgo',
        'icono' => 'bi-dial-high',
        'campos' => [
            'puntos_riesgo_medio' => ['Puntos por rendimiento medio', '', 'number'],
            'puntos_riesgo_alto' => ['Puntos por rendimiento alto', '', 'number'],
            'puntos_asistencia_media' => ['Puntos por asistencia preventiva (80-89%)', '', 'number'],
            'puntos_asistencia_alta' => ['Puntos por asistencia baja (&lt;80%)', '', 'number'],
            'puntos_curso_1' => ['Puntos por 1 curso desaprobado', '', 'number'],
            'puntos_curso_2' => ['Puntos por 2 cursos desaprobados', '', 'number'],
            'puntos_curso_3' => ['Puntos por 3 o más cursos desaprobados', '', 'number'],
            'puntos_tendencia' => ['Puntos por tendencia descendente', '', 'number'],
        ],
    ],
    'asistencia' => [
        'titulo' => 'Asistencia',
        'icono' => 'bi-calendar-check',
        'campos' => [
            'asistencia_normal_min' => ['Asistencia normal mínima (%)', 'Porcentaje a partir del cual se considera asistencia normal.', 'number'],
            'asistencia_preventiva_min' => ['Asistencia preventiva mínima (%)', 'Porcentaje mínimo para alerta preventiva.', 'number'],
            'dias_alerta_inasistencia' => ['Faltas en un mes para alerta', '', 'number'],
            'tardanzas_alerta' => ['Tardanzas para generar alerta', '', 'number'],
        ],
    ],
    'seguridad' => [
        'titulo' => 'Seguridad',
        'icono' => 'bi-shield-lock',
        'campos' => [
            'max_intentos_login' => ['Máximo de intentos de login', 'Intentos fallidos antes de bloquear el acceso.', 'number'],
            'minutos_bloqueo' => ['Minutos de bloqueo', 'Tiempo de bloqueo tras intentos fallidos.', 'number'],
        ],
    ],
];
?>
<div class="row g-3 mb-4 align-items-center">
    <div class="col">
        <h2 class="fw-bold text-primary mb-1"><i class="bi bi-gear me-2 text-accent"></i>Configuración del sistema</h2>
        <p class="text-muted mb-0 fs-7">Umbrales, puntajes y parámetros del motor de alertas tempranas</p>
    </div>
</div>

<form method="post" action="<?= url('configuracion') ?>">
    <?= csrf_field() ?>
    <div class="row g-4">
        <?php foreach ($grupos as $claveGrupo => $grupo): ?>
            <div class="col-12 col-xl-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white py-3 fw-semibold"><i class="bi <?= e($grupo['icono']) ?> me-2 text-accent"></i><?= e($grupo['titulo']) ?></div>
                    <div class="card-body">
                        <div class="row g-3">
                            <?php foreach ($grupo['campos'] as $clave => [$etiqueta, $ayuda, $tipo]): ?>
                                <div class="col-12 <?= $tipo === 'number' ? 'col-md-6' : '' ?>">
                                    <label class="form-label"><?= e($etiqueta) ?></label>
                                    <input type="<?= $tipo === 'number' ? 'number' : 'text' ?>" step="<?= $tipo === 'number' ? 'any' : '' ?>" name="<?= e($clave) ?>" class="form-control" value="<?= e($parametros[$clave] ?? '') ?>">
                                    <?php if ($ayuda): ?><div class="form-text"><?= $ayuda ?></div><?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <!-- Niveles de riesgo (información) -->
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 fw-semibold"><i class="bi bi-signal me-2 text-accent"></i>Niveles de riesgo vigentes</div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead><tr><th>Nivel</th><th>Rango de puntaje</th><th>Descripción</th></tr></thead>
                            <tbody>
                                <?php foreach ($niveles as $n): ?>
                                    <tr>
                                        <td><span class="badge bg-<?= e($n['color']) ?>-subtle text-<?= e($n['color']) ?>-emphasis border border-<?= e($n['color']) ?>-subtle rounded-pill"><?= e($n['nombre']) ?></span></td>
                                        <td><?= (int)$n['min_puntaje'] ?> - <?= (int)$n['max_puntaje'] ?> pts</td>
                                        <td class="text-muted fs-7"><?= e($n['descripcion'] ?? '') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="form-text mt-2">Los niveles se asignan automáticamente según el puntaje total calculado por el motor de alertas.</div>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-4">
        <div>
            <?php if ((string)$demo_data === '1'): ?>
                <form method="post" action="<?= url('configuracion/limpiar-demo') ?>" data-confirm='{"titulo":"Eliminar datos demo","texto":"Se eliminarán todos los estudiantes, matrículas, calificaciones, asistencias, alertas y seguimientos de demostración. ¿Desea continuar?"}' class="d-inline">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-outline-danger"><i class="bi bi-eraser me-1"></i>Eliminar datos demo</button>
                </form>
            <?php else: ?>
                <span class="badge bg-light text-dark border rounded-pill"><i class="bi bi-check-circle me-1 text-success"></i>Sistema sin datos demo</span>
            <?php endif; ?>
        </div>
        <button type="submit" class="btn btn-accent btn-lg px-4"><i class="bi bi-save me-1"></i>Guardar configuración</button>
    </div>
</form>