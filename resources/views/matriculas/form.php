<?php
/**
 * EduAlert JJP - recursos/views/matriculas/form.php
 */
$modo = $modo ?? 'crear';
$esEditar = $modo === 'editar';
$m = $matricula ?? null;
$titulo = $esEditar ? 'Editar matrícula' : 'Nueva matrícula';
$rutaPost = $esEditar ? 'matriculas/editar/' . $m['id'] : 'matriculas/crear';
?>
<div class="row g-3 mb-4 align-items-center">
    <div class="col">
        <h2 class="fw-bold text-primary mb-1"><i class="bi bi-mortarboard me-2 text-accent"></i><?= $titulo ?></h2>
    </div>
    <div class="col-auto">
        <a href="<?= url('matriculas') ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Volver</a>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-12 col-lg-7">
        <form method="post" action="<?= url($rutaPost) ?>" class="card border-0 shadow-sm">
            <?= csrf_field() ?>
            <div class="card-header bg-white py-3 fw-semibold"><i class="bi bi-person-check me-2 text-accent"></i>Datos de la matrícula</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label">Estudiante *</label>
                        <select name="estudiante_id" class="form-select select2" required <?= $esEditar ? 'disabled' : '' ?>>
                            <option value="">Seleccione al estudiante...</option>
                            <?php foreach ($estudiantes as $e): ?>
                                <option value="<?= (int)$e['id'] ?>" <?= (int)old('estudiante_id', $m['estudiante_id'] ?? '') === (int)$e['id'] ? 'selected' : '' ?>><?= e($e['apellidos'] . ', ' . $e['nombres']) ?> (<?= e($e['dni']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                        <?php if ($esEditar): ?><input type="hidden" name="estudiante_id" value="<?= (int)$m['estudiante_id'] ?>"><?php endif; ?>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">Periodo académico *</label>
                        <select name="periodo_id" class="form-select" required>
                            <option value="">Seleccione...</option>
                            <?php foreach ($periodos as $p): ?>
                                <option value="<?= (int)$p['id'] ?>" <?= (int)old('periodo_id', $m['periodo_id'] ?? '') === (int)$p['id'] ? 'selected' : '' ?>><?= e($p['nombre'] . ' ' . $p['anio']) ?><?= (int)$p['activo'] === 1 ? ' (activo)' : '' ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">Fecha de matrícula *</label>
                        <input type="date" name="fecha" class="form-control" value="<?= old('fecha', $m['fecha'] ?? date('Y-m-d')) ?>" required>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">Grado *</label>
                        <select name="grado_id" id="campoGrado" class="form-select" required>
                            <option value="">Seleccione...</option>
                            <?php foreach ($grados as $g): ?>
                                <option value="<?= (int)$g['id'] ?>" <?= (int)old('grado_id', $m['grado_id'] ?? '') === (int)$g['id'] ? 'selected' : '' ?>><?= e($g['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">Sección *</label>
                        <select name="seccion_id" id="campoSeccion" class="form-select" required>
                            <option value="">Seleccione...</option>
                            <?php foreach ($secciones as $s): ?>
                                <option value="<?= (int)$s['id'] ?>" data-grado="<?= (int)$s['grado_id'] ?>" <?= (int)old('seccion_id', $m['seccion_id'] ?? '') === (int)$s['id'] ? 'selected' : '' ?>><?= e($s['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-white text-end">
                <a href="<?= url('matriculas') ?>" class="btn btn-outline-secondary me-2">Cancelar</a>
                <button type="submit" class="btn btn-accent"><i class="bi bi-check2-circle me-1"></i><?= $esEditar ? 'Guardar cambios' : 'Registrar matrícula' ?></button>
            </div>
        </form>
    </div>
</div>

<script>
$(function () {
    const $grado = $('#campoGrado');
    const $seccion = $('#campoSeccion');
    const todas = $seccion.find('option').toArray().map(function (o) { return o; });
    const seccionSel = $seccion.val();
    function filtrarSecciones() {
        const gid = $grado.val();
        const html = ['<option value="">Seleccione...</option>'];
        todas.forEach(function (o) {
            if (!gid || $(o).data('grado') === parseInt(gid, 10)) html.push(o.outerHTML);
        });
        $seccion.html(html.join(''));
        $seccion.val(seccionSel);
    }
    if ($grado.val()) filtrarSecciones();
    $grado.on('change', filtrarSecciones);
});
</script>