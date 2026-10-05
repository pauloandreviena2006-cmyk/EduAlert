<?php
/**
 * EduAlert JJP - recursos/views/grados/index.php
 */
?>
<div class="row g-3 mb-4 align-items-center">
    <div class="col">
        <h2 class="fw-bold text-primary mb-1"><i class="bi bi-mortarboard me-2 text-accent"></i>Grados académicos</h2>
        <p class="text-muted mb-0 fs-7">Niveles de educación secundaria</p>
    </div>
    <div class="col-auto">
        <a href="<?= url('grados/crear') ?>" class="btn btn-accent"><i class="bi bi-plus-lg me-1"></i>Nuevo grado</a>
    </div>
</div>

<div class="row g-4">
    <?php if (empty($grados)): ?>
        <div class="col-12"><div class="card border-0 shadow-sm"><div class="card-body"><div class="empty-state"><i class="bi bi-inbox"></i>No hay grados registrados.</div></div></div></div>
    <?php else: ?>
        <?php foreach ($grados as $g): ?>
            <div class="col-12 col-md-6 col-xl-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <span class="badge bg-secondary-subtle text-secondary-emphasis rounded-pill mb-1"><?= e($g['nivel'] ?? 'Secundaria') ?></span>
                                <h4 class="fw-bold text-primary mb-0"><?= e($g['nombre']) ?></h4>
                            </div>
                            <span class="display-6 text-accent"><i class="bi bi-easel"></i></span>
                        </div>
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <span class="badge bg-accent-subtle text-accent-emphasis rounded-pill"><i class="bi bi-layout-sidebar me-1"></i><?= (int)$g['total_secciones'] ?> secciones</span>
                            <span class="badge bg-light text-dark rounded-pill"><i class="bi bi-sort-numeric-up me-1"></i>Orden <?= (int)$g['orden'] ?></span>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="<?= url('grados/editar/' . $g['id']) ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil me-1"></i>Editar</a>
                            <a href="<?= url('secciones?grado_id=' . $g['id']) ?>" class="btn btn-sm btn-outline-accent"><i class="bi bi-geo-alt me-1"></i>Secciones</a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>