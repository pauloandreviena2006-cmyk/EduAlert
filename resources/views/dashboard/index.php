<?php
/**
 * EduAlert JJP - resources/views/dashboard/index.php
 * Dashboard institucional con estadísticas y 5 gráficos desde BD real
 */
?>
<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon icon-blue"><i class="bi bi-people-fill"></i></div>
            <div class="stat-info">
                <div class="stat-value"><?= number_format($total_estudiantes) ?></div>
                <div class="stat-label">Total de estudiantes</div>
                <div class="stat-extra">Activos en el sistema</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon icon-red"><i class="bi bi-exclamation-triangle-fill"></i></div>
            <div class="stat-info">
                <div class="stat-value"><?= (int)($nivel_map['Alto']['total'] ?? 0) ?></div>
                <div class="stat-label">Riesgo alto</div>
                <div class="stat-extra">Requieren intervención</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon icon-amber"><i class="bi bi-exclamation-circle-fill"></i></div>
            <div class="stat-info">
                <div class="stat-value"><?= (int)($nivel_map['Medio']['total'] ?? 0) ?></div>
                <div class="stat-label">Riesgo medio</div>
                <div class="stat-extra">En monitoreo</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon icon-green"><i class="bi bi-check-circle-fill"></i></div>
            <div class="stat-info">
                <div class="stat-value"><?= (int)($nivel_map['Bajo']['total'] ?? 0) + (int)$riesgos['sin_riesgo'] ?></div>
                <div class="stat-label">Sin riesgo</div>
                <div class="stat-extra">Desempeño adecuado</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon icon-cyan"><i class="bi bi-graph-up-arrow"></i></div>
            <div class="stat-info">
                <div class="stat-value"><?= $promedio_general !== null ? f_numero($promedio_general, 1) : '—' ?></div>
                <div class="stat-label">Promedio general</div>
                <div class="stat-extra"><?= e($periodo['nombre'] ?? 'Periodo activo') ?></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon icon-red"><i class="bi bi-calendar-x-fill"></i></div>
            <div class="stat-info">
                <div class="stat-value"><?= number_format($total_inasistencias) ?></div>
                <div class="stat-label">Inasistencias</div>
                <div class="stat-extra">Periodo activo</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon icon-purple"><i class="bi bi-bell-fill"></i></div>
            <div class="stat-info">
                <div class="stat-value"><?= number_format($alertas_nuevas) ?></div>
                <div class="stat-label">Alertas pendientes</div>
                <div class="stat-extra">Estado: Nueva</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon icon-green"><i class="bi bi-clipboard2-pulse-fill"></i></div>
            <div class="stat-info">
                <div class="stat-value"><?= number_format($seguimientos) ?></div>
                <div class="stat-label">Seguimientos</div>
                <div class="stat-extra">Acciones de intervención</div>
            </div>
        </div>
    </div>
</div>

<?php if (can('alertas.generar')): ?>
    <div class="card mb-4 border-0 shadow-sm">
        <div class="card-body d-flex flex-wrap align-items-center gap-3">
            <div class="flex-grow-1">
                <h6 class="fw-bold text-primary mb-1"><i class="bi bi-lightning-charge-fill me-2 text-warning"></i>Motor de alertas tempranas</h6>
                <div class="fs-7 text-muted">
                    Ejecuta la evaluación académica de todos los estudiantes del periodo
                    <?= e(($periodo['nombre'] ?? '') . ' ' . ($periodo['anio'] ?? '')) ?>: promedios, asistencia,
                    cursos desaprobados y tendencia. Las alertas se generan automáticamente con los parámetros configurados.
                </div>
            </div>
            <form method="post" action="<?= url('dashboard/actualizar-evaluacion') ?>" data-confirm='{"titulo":"¿Ejecutar evaluación de riesgo?","texto":"Se recalculará el riesgo de todos los estudiantes del periodo activo y se generarán/actualizarán alertas.","confirmar":"Sí, ejecutar","color":"#0e9f9d","icono":"info"}'>
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-accent px-4 py-2">
                    <i class="bi bi-lightning-charge me-2"></i>Ejecutar evaluación de riesgo
                </button>
            </form>
        </div>
    </div>
<?php endif; ?>

<div class="row g-3">
    <div class="col-12 col-lg-7">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center">
                <h5><i class="bi bi-pie-chart-fill me-2 text-accent"></i>Distribución por nivel de riesgo</h5>
            </div>
            <div class="card-body">
                <div class="chart-box"><canvas id="chartRiesgo"></canvas></div>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-5">
        <div class="card h-100">
            <div class="card-header">
                <h5><i class="bi bi-bar-chart-fill me-2 text-accent"></i>Rendimiento por grado</h5>
            </div>
            <div class="card-body">
                <div class="chart-box"><canvas id="chartGrados"></canvas></div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mt-0">
    <div class="col-12 col-lg-4">
        <div class="card h-100">
            <div class="card-header">
                <h5><i class="bi bi-bell-fill me-2 text-danger"></i>Alertas por periodo</h5>
            </div>
            <div class="card-body">
                <div class="chart-box"><canvas id="chartAlertas"></canvas></div>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-4">
        <div class="card h-100">
            <div class="card-header">
                <h5><i class="bi bi-activity me-2 text-primary"></i>Evolución del rendimiento</h5>
            </div>
            <div class="card-body">
                <div class="chart-box"><canvas id="chartEvolucion"></canvas></div>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-4">
        <div class="card h-100">
            <div class="card-header">
                <h5><i class="bi bi-calendar-x me-2 text-warning"></i>Inasistencias por grado</h5>
            </div>
            <div class="card-body">
                <div class="chart-box"><canvas id="chartAsistencia"></canvas></div>
            </div>
        </div>
    </div>
</div>

<script>
$(function () {
    $.getJSON(BASE_URL + '/dashboard/datos', function (d) {
        const colores = { success: '#10b981', warning: '#f59e0b', danger: '#e11d48' };

        // Gráfico 1: Dona de riesgo
        new Chart(document.getElementById('chartRiesgo'), {
            type: 'doughnut',
            data: {
                labels: d.grafico1.map(x => x.label),
                datasets: [{
                    data: d.grafico1.map(x => x.value),
                    backgroundColor: d.grafico1.map(x => colores[x.color] || '#3b82f6'),
                    borderWidth: 2, borderColor: '#fff'
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom', labels: { font: { size: 12 } } } }
            }
        });

        // Gráfico 2: Barras rendimiento por grado
        new Chart(document.getElementById('chartGrados'), {
            type: 'bar',
            data: {
                labels: d.grafico2.map(x => x.label.replace('° de Secundaria', '°')),
                datasets: [{
                    label: 'Promedio (0-20)', data: d.grafico2.map(x => x.value),
                    backgroundColor: 'rgba(14,159,157,.75)', borderRadius: 8,
                    borderColor: '#0e9f9d', borderWidth: 1
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                scales: { y: { min: 0, max: 20, ticks: { stepSize: 5 } } },
                plugins: { legend: { display: false } }
            }
        });

        // Gráfico 3: Barras alertas por periodo
        new Chart(document.getElementById('chartAlertas'), {
            type: 'bar',
            data: {
                labels: d.grafico3.map(x => x.label),
                datasets: [{
                    label: 'Alertas', data: d.grafico3.map(x => x.value),
                    backgroundColor: 'rgba(225,29,72,.7)', borderRadius: 8
                }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
        });

        // Gráfico 4: Línea evolución
        new Chart(document.getElementById('chartEvolucion'), {
            type: 'line',
            data: {
                labels: d.grafico4.map(x => x.label),
                datasets: [{
                    label: 'Promedio general', data: d.grafico4.map(x => x.value),
                    borderColor: '#2563eb', backgroundColor: 'rgba(37,99,235,.1)',
                    fill: true, tension: .35, pointRadius: 4, pointBackgroundColor: '#2563eb'
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                scales: { y: { min: 0, max: 20, ticks: { stepSize: 5 } } },
                plugins: { legend: { display: false } }
            }
        });

        // Gráfico 5: Barras agrupadas inasistencias
        new Chart(document.getElementById('chartAsistencia'), {
            type: 'bar',
            data: {
                labels: d.grafico5.map(x => x.label.replace('° de Secundaria', '°')),
                datasets: [
                    { label: 'Faltas', data: d.grafico5.map(x => x.faltas), backgroundColor: '#e11d48', borderRadius: 6 },
                    { label: 'Tardanzas', data: d.grafico5.map(x => x.tardanzas), backgroundColor: '#f59e0b', borderRadius: 6 }
                ]
            },
            options: { responsive: true, maintainAspectRatio: false, scales: { x: { stacked: false } } }
        });
    });
});
</script>