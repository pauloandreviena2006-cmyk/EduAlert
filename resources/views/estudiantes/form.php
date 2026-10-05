<?php
/**
 * EduAlert JJP - recursos/views/estudiantes/form.php
 * Formulario de estudiante (crear/editar) + datos del apoderado + matrícula
 */
$modo = $modo ?? 'crear';
$esEditar = $modo === 'editar';
$est = $estudiante ?? null;
$mat = $matricula ?? null;
$titulo = $esEditar ? 'Editar estudiante' : 'Nuevo estudiante';
$rutaPost = $esEditar ? 'estudiantes/editar/' . $est['id'] : 'estudiantes/crear';
?>
<div class="row g-3 mb-4 align-items-center">
    <div class="col">
        <h2 class="fw-bold text-primary mb-1"><i class="bi bi-person-plus-fill me-2 text-accent"></i><?= $titulo ?></h2>
        <p class="text-muted mb-0 fs-7">Los campos marcados con * son obligatorios</p>
    </div>
    <div class="col-auto">
        <a href="<?= url('estudiantes') ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Volver</a>
    </div>
</div>

<form method="post" action="<?= url($rutaPost) ?>" class="row g-4">
    <?= csrf_field() ?>

    <!-- Datos personales -->
    <div class="col-12 col-xl-7">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white py-3 fw-semibold"><i class="bi bi-person-vcard me-2 text-accent"></i>Datos personales</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-12 col-md-4">
                        <label class="form-label">DNI *</label>
                        <input type="text" name="dni" class="form-control" maxlength="8" inputmode="numeric" pattern="[0-9]{8}" placeholder="00000000" value="<?= old('dni', $est['dni'] ?? '') ?>" required>
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label">Nombres *</label>
                        <input type="text" name="nombres" class="form-control" maxlength="80" placeholder="Ej. María José" value="<?= old('nombres', $est['nombres'] ?? '') ?>" required>
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label">Apellidos *</label>
                        <input type="text" name="apellidos" class="form-control" maxlength="80" placeholder="Ej. Rojas Torres" value="<?= old('apellidos', $est['apellidos'] ?? '') ?>" required>
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label">Fecha de nacimiento</label>
                        <input type="date" name="fecha_nacimiento" class="form-control" value="<?= old('fecha_nacimiento', $est['fecha_nacimiento'] ?? '') ?>">
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label">Sexo</label>
                        <select name="sexo" class="form-select">
                            <?php $__sexo = old('sexo', $est['sexo'] ?? 'M'); ?>
                            <option value="M" <?= $__sexo === 'M' ? 'selected' : '' ?>>Masculino</option>
                            <option value="F" <?= $__sexo === 'F' ? 'selected' : '' ?>>Femenino</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label">Teléfono</label>
                        <input type="text" name="telefono" class="form-control" maxlength="15" placeholder="9xx xxx xxx" value="<?= old('telefono', $est['telefono'] ?? '') ?>">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Correo electrónico</label>
                        <input type="email" name="email" class="form-control" placeholder="estudiante@correo.com" value="<?= old('email', $est['email'] ?? '') ?>">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Dirección</label>
                        <input type="text" name="direccion" class="form-control" maxlength="150" placeholder="Jr. ... Tarapoto" value="<?= old('direccion', $est['direccion'] ?? '') ?>">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Matrícula y apoderado -->
    <div class="col-12 col-xl-5">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 fw-semibold"><i class="bi bi-mortarboard me-2 text-accent"></i>Matrícula</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label">Grado *</label>
                        <select name="grado_id" id="campoGrado" class="form-select" required>
                            <option value="">Seleccione...</option>
                            <?php foreach ($grados as $g): ?>
                                <option value="<?= (int)$g['id'] ?>" <?= (int)old('grado_id', $mat['grado_id'] ?? '') === (int)$g['id'] ? 'selected' : '' ?>><?= e($g['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Sección *</label>
                        <select name="seccion_id" id="campoSeccion" class="form-select" required>
                            <option value="">Seleccione...</option>
                            <?php foreach ($secciones as $s): ?>
                                <option value="<?= (int)$s['id'] ?>" data-grado="<?= (int)$s['grado_id'] ?>" <?= (int)old('seccion_id', $mat['seccion_id'] ?? '') === (int)$s['id'] ? 'selected' : '' ?>><?= e($s['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 fw-semibold"><i class="bi bi-people me-2 text-accent"></i>Apoderado</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <label class="form-label">Nombres</label>
                        <input type="text" name="apo_nombres" class="form-control" maxlength="80" value="<?= old('apo_nombres', $est['apo_nombres'] ?? '') ?>">
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">Apellidos</label>
                        <input type="text" name="apo_apellidos" class="form-control" maxlength="80" value="<?= old('apo_apellidos', $est['apo_apellidos'] ?? '') ?>">
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label">DNI</label>
                        <input type="text" name="apo_dni" class="form-control" maxlength="8" inputmode="numeric" value="<?= old('apo_dni', $est['apo_dni'] ?? '') ?>">
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label">Parentesco</label>
                        <select name="apo_parentesco" class="form-select">
                            <?php $__parentesco = old('apo_parentesco', $est['apo_parentesco'] ?? 'Padre'); ?>
                            <?php foreach (['Padre', 'Madre', 'Tío(a)', 'Abuelo(a)', 'Hermano(a)', 'Otro'] as $__p): ?>
                                <option value="<?= $__p ?>" <?= $__parentesco === $__p ? 'selected' : '' ?>><?= $__p ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label">Teléfono</label>
                        <input type="text" name="apo_telefono" class="form-control" maxlength="15" placeholder="9xx xxx xxx" value="<?= old('apo_telefono', $est['apo_telefono'] ?? '') ?>">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Ocupación</label>
                        <input type="text" name="apo_ocupacion" class="form-control" maxlength="60" placeholder="Ej. Comerciante" value="<?= old('apo_ocupacion', $est['apo_ocupacion'] ?? '') ?>">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 text-end">
        <a href="<?= url('estudiantes') ?>" class="btn btn-outline-secondary me-2">Cancelar</a>
        <button type="submit" class="btn btn-accent btn-lg px-4"><i class="bi bi-check2-circle me-1"></i><?= $esEditar ? 'Guardar cambios' : 'Registrar estudiante' ?></button>
    </div>
</form>

<script>
$(function () {
    // Cascada grado -> sección (filtra las opciones locales y muestra las del grado elegido)
    const $grado = $('#campoGrado');
    const $seccion = $('#campoSeccion');
    const todas = $seccion.find('option').toArray();
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
    if (gidActual($grado)) filtrarSecciones();
    $grado.on('change', filtrarSecciones);
    function gidActual($sel) { return $sel.val() && $sel.val() !== ''; }
});
</script>