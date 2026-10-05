<?php
/**
 * EduAlert JJP - recursos/views/grados/form.php
 */
$modo = $modo ?? 'crear';
$esEditar = $modo === 'editar';
$g = $grado ?? null;
$titulo = $esEditar ? 'Editar grado' : 'Nuevo grado';
$rutaPost = $esEditar ? 'grados/editar/' . $g['id'] : 'grados/crear';
?>
<div class="row g-3 mb-4 align-items-center">
    <div class="col">
        <h2 class="fw-bold text-primary mb-1"><i class="bi bi-mortarboard me-2 text-accent"></i><?= $titulo ?></h2>
    </div>
    <div class="col-auto">
        <a href="<?= url('grados') ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Volver</a>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-12 col-lg-6">
        <form method="post" action="<?= url($rutaPost) ?>" class="card border-0 shadow-sm">
            <?= csrf_field() ?>
            <div class="card-header bg-white py-3 fw-semibold"><i class="bi bi-easel me-2 text-accent"></i>Datos del grado</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label">Nombre *</label>
                        <input type="text" name="nombre" class="form-control" maxlength="40" placeholder="Ej. Primer Grado" value="<?= old('nombre', $g['nombre'] ?? '') ?>" required>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">Nivel</label>
                        <select name="nivel" class="form-select">
                            <?php $__nivel = old('nivel', $g['nivel'] ?? 'Secundaria'); ?>
                            <option value="Secundaria" <?= $__nivel === 'Secundaria' ? 'selected' : '' ?>>Secundaria</option>
                            <option value="Primaria" <?= $__nivel === 'Primaria' ? 'selected' : '' ?>>Primaria</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">Orden de visualización</label>
                        <input type="number" name="orden" class="form-control" min="1" max="20" value="<?= old('orden', $g['orden'] ?? 1) ?>">
                    </div>
                </div>
            </div>
            <div class="card-footer bg-white text-end">
                <a href="<?= url('grados') ?>" class="btn btn-outline-secondary me-2">Cancelar</a>
                <button type="submit" class="btn btn-accent"><i class="bi bi-check2-circle me-1"></i><?= $esEditar ? 'Guardar cambios' : 'Registrar grado' ?></button>
            </div>
        </form>
    </div>
</div>