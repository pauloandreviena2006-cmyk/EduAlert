<?php
/**
 * EduAlert JJP - recursos/views/reportes/index.php
 * Consulta y exportación de reportes (PDF / Excel / CSV)
 */
$exportar = fn(string $formato) => url('reportes/exportar') . '?' . http_build_query([
    'tipo' => $tipo, 'formato' => $formato,
    'periodo_id' => (int)$periodo_id, 'seccion_id' => (int)$seccion_id, 'grado_id' => (int)$grado_id,
]);
?>
<div class="row g-3 mb-4 align-items-center">
    <div class="col">
        <h2 class="fw-bold text-primary mb-1"><i class="bi bi-file-earmark-bar-graph me-2 text-accent"></i>Reportes</h2>
        <p class="text-muted mb-0 fs-7">Informes con datos reales del sistema · Exportación a PDF, Excel y CSV</p>
    </div>
    <div class="col-auto d-flex gap-2">
        <!-- Selector de formato con botón propio -->
        <select id="formatoExportar" class="form-select" style="width:auto">
            <option value="pdf" selected>PDF</option>
            <option value="xls">Excel (XLS)</option>
            <option value="csv">CSV</option>
        </select>
        <a href="#" id="btnExportar" class="btn btn-accent"><i class="bi bi-download me-1"></i>Exportar</a>
    </div>
</div>

<div class="card mb-4 border-0 shadow-sm">
    <div class="card-body py-3">
        <form method="get" action="<?= url('reportes') ?>" class="row g-2 align-items-end">
            <div class="col-12 col-md-4 col-xl-3">
                <label class="form-label">Tipo de reporte</label>
                <select name="tipo" class="form-select">
                    <?php foreach ($tipos as $clave => $etiqueta): ?>
                        <option value="<?= e($clave) ?>" <?= $tipo === $clave ? 'selected' : '' ?>><?= e($etiqueta) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-6 col-md-4 col-xl-3">
                <label class="form-label">Periodo académico</label>
                <select name="periodo_id" class="form-select">
                    <?php foreach ($periodos as $p): ?>
                        <option value="<?= (int)$p['id'] ?>" <?= (int)$periodo_id === (int)$p['id'] ? 'selected' : '' ?>><?= e($p['nombre'] . ' ' . $p['anio']) ?><?= (int)$p['activo'] === 1 ? ' (activo)' : '' ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-6 col-md-4 col-xl-3">
                <label class="form-label">Sección</label>
                <select name="seccion_id" class="form-select">
                    <option value="0">Todas</option>
                    <?php foreach ($secciones as $s): ?>
                        <option value="<?= (int)$s['id'] ?>" <?= (int)$seccion_id === (int)$s['id'] ? 'selected' : '' ?>><?= e($s['grado_nombre'] . ' "' . $s['nombre'] . '"') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary"><i class="bi bi-funnel me-1"></i>Generar reporte</button>
                <a href="<?= url('reportes') ?>" class="btn btn-outline-secondary"><i class="bi bi-x-lg"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="fw-semibold"><i class="bi bi-<?= $tipo === 'alertas' || $tipo === 'seguimientos' ? 'bell' : 'journal-text' ?> me-2 text-accent"></i><?= e($tipos[$tipo]) ?>
            <span class="text-muted fs-7 ms-2"><?= $periodo ? 'Periodo: ' . e($periodo['nombre'] . ' ' . $periodo['anio']) : 'Todos los periodos' ?></span>
        </div>
        <span class="badge bg-light text-dark rounded-pill"><?= count($filas) ?> registro<?= count($filas) === 1 ? '' : 's' ?></span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-datatable align-middle mb-0">
                <thead>
                    <tr>
                        <?php foreach ($headers as $etiqueta): ?>
                            <th><?= e($etiqueta) ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($filas)): ?>
                        <tr><td colspan="<?= count($headers) ?>"><div class="empty-state"><i class="bi bi-inbox"></i>No hay datos para el reporte con los filtros seleccionados.</div></td></tr>
                    <?php else: ?>
                        <?php foreach ($filas as $fila): ?>
                            <tr>
                                <?php foreach (array_keys($headers) as $col): ?>
                                    <td><?= e($fila[$col] ?? '-') ?></td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
$(function () {
    $('#btnExportar').on('click', function (e) {
        e.preventDefault();
        const formato = $('#formatoExportar').val();
        window.location.href = '<?= $exportar('__F__') ?>'.replace('__F__', formato);
    });
});
</script>